<?php

namespace App\Services;

use App\Models\{BookingMobil, BookingServis, MemberVehiclePreorder, Notifikasi, Pembayaran, PesananMobilMember, ServiceQualityControl, ServiceWorkOrderAssignment, TestDrive, User};
use Illuminate\Support\Facades\DB;

/** Observe committed business records; never change their status to produce a badge. */
class StaffNotificationService
{
    public function registerObservers(): void
    {
        foreach ([PesananMobilMember::class, MemberVehiclePreorder::class, ServiceWorkOrderAssignment::class, BookingServis::class,
            BookingMobil::class, TestDrive::class, Pembayaran::class, ServiceQualityControl::class] as $model) {
            $model::created(fn ($record) => $this->queue($model, (int) $record->getKey()));
        }
        PesananMobilMember::updated(function ($order) {
            if ($order->wasChanged('id_sales')) $this->queue(PesananMobilMember::class, (int) $order->getKey());
        });
        MemberVehiclePreorder::updated(function ($order) {
            if ($order->wasChanged('status') && $order->status === 'menunggu_stok') $this->queue(MemberVehiclePreorder::class, (int) $order->getKey());
        });
        Notifikasi::created(function ($notification) {
            if ($notification->tipe === 'stok' && $notification->referensi_tipe === 'sparepart') $this->queue(Notifikasi::class, (int) $notification->getKey());
        });
    }

    public function reconcilePendingOrders(): void
    {
        // Repair missing delivery only; firstOrCreate preserves existing read state.
        PesananMobilMember::where('status', 'menunggu_verifikasi')->orderBy('id_pesanan')
            ->each(fn ($order) => $this->emit(PesananMobilMember::class, (int) $order->getKey()));
        MemberVehiclePreorder::where('status', 'menunggu_stok')->orderBy('id_pre_order')
            ->each(fn ($order) => $this->emit(MemberVehiclePreorder::class, (int) $order->getKey()));
    }

    private function queue(string $model, int $id): void
    {
        DB::afterCommit(function () use ($model, $id) {
            try { $this->emit($model, $id); }
            catch (\Throwable $failure) { report($failure); }
        });
    }

    public function emit(string $model, int $id): void
    {
        DB::transaction(function () use ($model, $id) {
            $record = $model::lockForUpdate()->find($id);
            if (!$record) return;
            $reference = null; $type = 'info'; $title = ''; $message = ''; $recipient = fn (User $user) => false;
            switch ($model) {
                case PesananMobilMember::class:
                    if ($record->status !== 'menunggu_verifikasi') return;
                    $reference = 'pesanan_mobil_member'; $type = 'transaksi';
                    $title = 'Pesanan Mobil Baru';
                    $message = 'Pesanan '.$record->nomor_pesanan.' — '.trim($record->snapshot_merk.' '.$record->snapshot_model)
                        .' / '.($record->snapshot_warna ?: 'Warna belum tercatat').' — '.$record->nama_pembeli.'. Menunggu verifikasi admin.';
                    $recipient = fn (User $user) => $user->roleKey() === 'super_admin' || ($user->roleKey() === 'sales' && $user->hasPermission('sales.view') && (int) ($user->petugas?->id_petugas ?: -1) === (int) $record->id_sales);
                    break;
                case MemberVehiclePreorder::class:
                    if ($record->status !== 'menunggu_stok') return;
                    $reference = 'pre_order_mobil'; $type = 'transaksi'; $title = 'Pre-Order Mobil Menunggu Stok';
                    $message = $record->nama_mobil.' / '.($record->warna ?: 'Warna belum tercatat').' — pre-order #'.$id.' menunggu ketersediaan stok.';
                    $recipient = fn (User $user) => $user->roleKey() === 'super_admin';
                    break;
                case ServiceWorkOrderAssignment::class:
                    if ($record->unassigned_at || !$record->assigned_at || $record->assigned_at->isFuture() || !in_array($record->assignment_role, ['primary', 'assistant'], true)) return;
                    $reference = 'service_assignment'; $type = 'servis';
                    $title = 'Work Order ditugaskan'; $message = 'Ada penugasan Work Order aktif untuk Anda.';
                    $recipient = fn (User $user) => $user->roleKey() === 'mechanic' && $user->hasPermission('service.assigned') && (int) $user->petugas?->id_petugas === (int) $record->id_petugas && $user->petugas?->status === 'aktif';
                    break;
                case BookingServis::class:
                    $reference = 'booking_servis'; $type = 'booking';
                    $title = 'Booking Servis Baru'; $message = 'Ada booking servis baru yang memerlukan pemeriksaan jadwal.';
                    // Legacy admin delivery already exists in ServiceNotificationService.
                    $recipient = fn (User $user) => ($user->roleName() === 'super_admin' || $user->roleKey() === 'service_advisor') && $user->hasPermission('service.view');
                    break;
                case BookingMobil::class:
                case TestDrive::class:
                    $reference = $model === BookingMobil::class ? 'booking_mobil' : 'test_drive'; $type = 'booking';
                    $title = $model === BookingMobil::class ? 'Booking Mobil Baru' : 'Test Drive Baru';
                    $message = 'Ada jadwal layanan baru yang perlu ditindaklanjuti.';
                    $recipient = fn (User $user) => $user->roleKey() === 'operational_staff' && $user->hasPermission('service.view');
                    break;
                case Pembayaran::class:
                    if ($record->arah !== 'masuk' || $record->status !== 'menunggu') return;
                    $reference = 'pembayaran'; $type = 'transaksi';
                    $title = 'Pembayaran menunggu verifikasi'; $message = 'Ada pengajuan pembayaran yang perlu diperiksa. Dana belum dinyatakan terverifikasi.';
                    $recipient = fn (User $user) => ($user->roleKey() === 'finance' || $user->roleKey() === 'super_admin') && $user->hasPermission('finance.view');
                    break;
                case ServiceQualityControl::class:
                    if ($record->status !== 'in_progress') return;
                    $reference = 'servis'; $id = (int) $record->id_servis; $type = 'servis';
                    $title = 'Quality Control dimulai'; $message = 'Work Order telah masuk proses pemeriksaan Quality Control.';
                    $recipient = fn (User $user) => $user->roleKey() === 'quality_control' && $user->hasPermission('qc.manage');
                    break;
                case Notifikasi::class:
                    $sender = User::find($record->id_user);
                    if (!$sender || $sender->roleKey() !== 'super_admin') return;
                    $reference = 'sparepart'; $id = (int) $record->referensi_id; $type = 'stok';
                    $title = $record->judul; $message = $record->pesan;
                    $recipient = fn (User $user) => $user->roleKey() === 'warehouse_staff' && $user->hasPermission('inventory.view');
                    break;
            }
            if (!$reference) return;
            $users = User::with('role.permissions', 'petugas')->where('status', 'aktif')->get();
            foreach ($users as $user) {
                if (!$recipient($user)) continue;
                $key = ['id_user' => $user->id_user, 'referensi_tipe' => $reference, 'referensi_id' => $id, 'judul' => $title];
                if ($model === Notifikasi::class) $key['is_read'] = false;
                Notifikasi::firstOrCreate($key, ['tipe' => $type, 'pesan' => $message, 'is_read' => false, 'created_at' => now()]);
            }
        });
    }
}
