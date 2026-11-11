<?php

namespace App\Services;

use App\Models\{Notifikasi, User, Servis, BookingServis, PesananMobilMember, MemberVehiclePreorder, Sparepart};
use Illuminate\Auth\Access\AuthorizationException;

class NotificationDestinationService
{
    public function for(User $user, Notifikasi $notification): string
    {
        $id = (int) $notification->referensi_id;
        $fallback = route($user->roleKey() === 'pelanggan' || $user->roleKey() === 'owner' || !$user->hasPermission('dashboard.view') ? 'notifications.index' : 'admin.notifikasi.index');
        if ($user->roleKey() === 'owner') return $fallback;
        if ($user->roleKey() === 'pelanggan') {
            $customer = $user->pelanggan?->id_pelanggan;
            if (!$customer) return $fallback;
            return match ($notification->referensi_tipe) {
                'servis' => Servis::whereKey($id)->where('id_pelanggan', $customer)->exists() ? route('pelanggan.servis.approval', $id) : $fallback,
                'booking_servis' => $this->memberBooking($id, (int) $customer, $fallback),
                'pesanan_mobil_member' => PesananMobilMember::whereKey($id)->where('id_pelanggan', $customer)->exists() ? route('pelanggan.pesanan-mobil.show', $id) : $fallback,
                'pre_order_mobil' => $this->memberPreorder($notification, (int) $customer, $fallback),
                default => $fallback,
            };
        }
        if ($notification->referensi_tipe === 'service_assignment') {
            $assignment = \App\Models\ServiceWorkOrderAssignment::find($id);
            if (!$assignment) return $fallback;
            $id = (int) $assignment->id_servis;
        }
        if (in_array($notification->referensi_tipe, ['servis', 'service_assignment'], true)) {
            $service = Servis::find($id);
            if (!$service) return $fallback;
            try { app(WorkOrderAuthorizationService::class)->authorize($user, $service, 'view'); }
            catch (AuthorizationException $exception) { return $fallback; }
            return route('admin.servis.show', $id);
        }
        if ($notification->referensi_tipe === 'booking_servis' && $user->hasPermission('service.view') && BookingServis::whereKey($id)->exists()) return route('admin.booking-servis.show', $id);
        if ($notification->referensi_tipe === 'sparepart' && $user->hasPermission('inventory.view') && Sparepart::whereKey($id)->exists()) return route('admin.sparepart.show', $id);
        if ($notification->referensi_tipe === 'pre_order_mobil' && $user->roleKey() === 'super_admin' && MemberVehiclePreorder::whereKey($id)->exists()) return route('admin.preorder-mobil.index');
        if ($notification->referensi_tipe === 'pembayaran' && $user->hasPermission('finance.view') && \App\Models\Pembayaran::whereKey($id)->exists()) return route('admin.payments.index');
        if ($notification->referensi_tipe === 'booking_mobil' && $user->hasPermission('service.view') && \App\Models\BookingMobil::whereKey($id)->exists()) return route('admin.booking.index');
        if ($notification->referensi_tipe === 'test_drive' && $user->hasPermission('service.view') && \App\Models\TestDrive::whereKey($id)->exists()) return route('admin.test-drive.index');
        if ($notification->referensi_tipe === 'pesanan_mobil_member') {
            $order = PesananMobilMember::find($id);
            if ($order && $user->roleKey() === 'super_admin') return route('admin.pesanan-mobil.index', ['detail' => $id]);
            if ($order && $user->roleKey() === 'sales' && $user->hasPermission('sales.view') && (int) $order->id_sales === (int) ($user->petugas?->id_petugas ?: -1)) return route('sales.orders.index');
        }
        return $fallback;
    }

    private function memberBooking(int $id, int $customer, string $fallback): string
    {
        $booking = BookingServis::whereKey($id)->where('id_pelanggan', $customer)->first();
        if (!$booking) return $fallback;
        $service = $booking->servis()->where('id_pelanggan', $customer)->first();
        return $service ? route('pelanggan.servis.approval', $service) : route('pelanggan.booking-servis.show', $booking);
    }

    private function memberPreorder(Notifikasi $notification, int $customer, string $fallback): string
    {
        $item = MemberVehiclePreorder::whereKey($notification->referensi_id)->where('id_pelanggan', $customer)->first();
        if (!$item) return $fallback;
        if ($notification->tipe === 'stok' && !in_array($item->status, ['dibatalkan','dilanjutkan_ke_checkout'], true)
            && app(ActiveMemberCustomerService::class)->isEligible($item->pelanggan)) {
            $stock = app(PreorderManagementService::class)->matchingStock($item);
            if ($stock['available']) return route('pelanggan.katalog-mobil.show', $stock['units'][0]['id']);
        }
        return route('pelanggan.preorder.index');
    }
}
