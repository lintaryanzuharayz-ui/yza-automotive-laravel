<?php

namespace App\Services;

use App\Models\{MemberVehiclePreorder, Mobil, Notifikasi, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PreorderManagementService
{
    public function authorize(User $actor, bool $write = false): void
    {
        abort_unless($actor->status === 'aktif' && $actor->roleKey() !== 'pelanggan'
            && (!$write || $actor->roleKey() !== 'owner')
            && $actor->hasPermission($write ? 'sales.manage' : 'sales.view'), 403);
    }

    public function source(MemberVehiclePreorder $preorder): ?Mobil
    {
        $car = app(CatalogVehicleIdentityService::class)->forPreorder($preorder);
        return $car && $this->matches($preorder, $car) ? $car : null;
    }

    private function equal($left, $right): bool
    {
        return mb_strtolower(trim((string) $left)) === mb_strtolower(trim((string) $right));
    }

    private function matches(MemberVehiclePreorder $preorder, Mobil $car): bool
    {
        return $this->equal($car->merk.' '.$car->model, $preorder->nama_mobil)
            && $this->equal($car->tipe, $preorder->varian) && (int) $car->tahun === (int) $preorder->tahun;
    }

    public function photo(MemberVehiclePreorder $preorder): string
    {
        $car = $this->source($preorder);
        $variant = $car ? app(VehicleGalleryService::class)->forColor($car, $preorder->warna) : [];
        $photo = $variant['images'][0]['src'] ?? null;
        if (in_array($photo, [asset('assets/images/hero/yza-hero-reference-v3.png'), asset('assets/images/hero/yza-hero-black-v2.png')], true)) $photo = null;
        return $photo ?: asset('assets/images/vehicle-photo-unavailable.svg');
    }

    public function detail(MemberVehiclePreorder $preorder, User $actor): array
    {
        $this->authorize($actor);
        $preorder->loadMissing('pelanggan.user', 'mobil');
        $customer = $preorder->pelanggan;
        $source = $this->source($preorder);
        $deliveryService = app(PreorderInventoryNotificationService::class);
        $history = $deliveryService->enabled() ? \App\Models\PreorderInventoryEvent::where('preorder_id', $preorder->getKey())->orderBy('id')->get()->map(function ($entry) {
            $notification = $entry->notification_id ? Notifikasi::find($entry->notification_id) : null;
            return ['kind' => $entry->kind, 'message' => $entry->payload['message'] ?? $entry->kind,
                'at' => $entry->payload['occurred_at'] ?? $entry->created_at?->toIso8601String(), 'actor' => $entry->actor_id ? (User::find($entry->actor_id)?->username ?: 'Petugas #'.$entry->actor_id.' (akun tidak tersedia)') : 'Sistem',
                'notification_status' => $notification ? ($notification->is_read ? 'Dibaca (waktu baca tidak disimpan)' : 'Belum dibaca') : null];
        })->all() : [];
        $stock = $this->matchingStock($preorder);
        $canStock = $actor->roleKey() !== 'owner' && $actor->hasPermission('inventory.view') && $actor->hasPermission('inventory.manage');
        $target = isset($stock['units'][0]) ? Mobil::find($stock['units'][0]['id']) : ($source?->exists && $this->equal($source->warna, $preorder->warna) ? $source : null);
        return [
            'id' => $preorder->getKey(), 'code' => 'PRE-'.str_pad((string) $preorder->getKey(), 5, '0', STR_PAD_LEFT),
            'vehicle' => $preorder->nama_mobil, 'variant' => $preorder->varian, 'year' => $preorder->tahun,
            'color' => $preorder->warna, 'image' => $this->photo($preorder),
            'model' => $source?->model, 'transmission' => $source?->transmisi, 'fuel' => $source?->bahan_bakar,
            'reference_price' => $preorder->harga_referensi === null ? null : 'Rp '.number_format((float) $preorder->harga_referensi, 0, ',', '.'),
            'customer' => ['name' => $customer?->nama_lengkap, 'code' => $customer?->kode_pelanggan,
                'phone' => $customer?->no_hp, 'email' => $customer?->user?->email],
            'customer_note' => null, 'status' => $preorder->status,
            'created_at' => $preorder->created_at?->toIso8601String(), 'notified_at' => $preorder->notified_at?->toIso8601String(),
            'cancelled_at' => $preorder->cancelled_at?->toIso8601String(), 'history' => $history, 'history_supported' => $deliveryService->enabled(),
            'stock' => $stock, 'automatic_delivery_enabled' => $deliveryService->enabled(),
            'member_eligible' => app(ActiveMemberCustomerService::class)->isEligible($customer),
            'stock_management_url' => $canStock && $source ? route('admin.mobil.index', array_filter(['catalog' => app(CatalogVehicleIdentityService::class)->key($source),
                'stock_mobil' => $target?->getKey(), 'preorder' => $preorder->getKey()])) : null,
            'stock_management_note' => $target ? 'Buka pengelolaan stok resmi kendaraan terkait.' : 'Belum ada unit fisik terhubung. Tambahkan atau tautkan unit asli di inventori resmi; nama yang mirip tidak cukup.',
            'notification_status' => $preorder->notified_at ? 'Pemberitahuan telah tercatat; lihat riwayat untuk status baca.' : 'Belum ada pemberitahuan stok yang dikirim.',
            'can_manage' => $actor->roleKey() !== 'owner' && $actor->hasPermission('sales.manage'),
            'can_act' => !$preorder->id_pesanan && !in_array($preorder->status, ['dibatalkan', 'dilanjutkan_ke_checkout'], true),
        ];
    }

    public function stock(MemberVehiclePreorder $preorder, User $actor): array
    {
        $this->authorize($actor);
        return $this->matchingStock($preorder);
    }

    public function matchingStock(MemberVehiclePreorder $preorder): array
    {
        $source = $this->source($preorder);
        $availability = new VehicleAvailabilityService();
        $matches = []; $seen = []; $reservedTotal = 0; $reservationGroups = [];
        if (in_array('', array_map(fn ($value) => trim((string) $value), [$preorder->nama_mobil, $preorder->varian, $preorder->tahun, $preorder->warna]), true)) {
            return ['checked_at' => now()->toIso8601String(), 'available' => false, 'units' => [], 'total' => 0,
                'message' => 'Spesifikasi permintaan belum lengkap. Kecocokan stok belum dapat dipastikan.',
                'note' => 'Tidak ada reservasi atau perubahan status.'];
        }
        // Match authoritative stored request fields before checking reservation-aware availability.
        $candidates = $source ? app(CatalogVehicleIdentityService::class)->physical($source) : collect();
        foreach ($candidates as $car) {
            if (!$car->nomor_rangka || !$car->nomor_mesin || !$car->transmisi || !$car->bahan_bakar || (float) $car->harga_jual <= 0) continue;
            if (!$this->matches($preorder, $car) || !$this->equal($car->warna, $preorder->warna)) continue;
            if ($source) {
                foreach (['transmisi', 'bahan_bakar', 'kondisi', 'is_limited_edition'] as $field) {
                    if ($source->$field !== null && !$this->equal($source->$field, $car->$field)) continue 2;
                }
            }
            $state = $availability->for($car);
            $group = app(VehicleInventoryService::class)->key($car);
            if (!isset($reservationGroups[$group])) { $reservedTotal += (int) ($state['active_reservation_count'] ?? 0); $reservationGroups[$group] = true; }
            if ($car->status !== 'tersedia') continue;
            if (!($state['can_purchase'] ?? false) || (int) ($state['stock'] ?? 0) < 1) continue;
            $aggregate = (bool) ($state['is_aggregate'] ?? false);
            $identity = $aggregate ? 'inventory:'.$state['inventory_id'] : 'unit:'.$car->getKey();
            if (isset($seen[$identity])) continue;
            $seen[$identity] = true;
            $matches[] = ['id' => $car->getKey(), 'code' => $car->kode_mobil, 'kind' => $aggregate ? 'Stok agregat' : 'Unit fisik',
                'stock' => $aggregate ? (int) $state['stock'] : 1,
                'reserved' => (int) ($state['active_reservation_count'] ?? 0),
                'vehicle' => trim($car->merk.' '.$car->model), 'variant' => $car->tipe, 'year' => $car->tahun,
                'color' => $car->warna, 'transmission' => $car->transmisi, 'fuel' => $car->bahan_bakar,
                'condition' => $car->kondisi, 'limited' => (bool) $car->is_limited_edition,
                'vin' => $aggregate ? null : $car->nomor_rangka,
                'price' => 'Rp '.number_format((float) $car->harga_jual, 0, ',', '.')];
        }
        return ['checked_at' => now()->toIso8601String(), 'available' => count($matches) > 0,
            'reserved_total' => $reservedTotal,
            'requested' => ['vehicle' => $preorder->nama_mobil, 'variant' => $preorder->varian, 'year' => $preorder->tahun, 'color' => $preorder->warna],
            'units' => $matches, 'total' => array_sum(array_column($matches, 'stock')),
            'message' => $matches ? 'Ditemukan stok dengan model, varian, tahun, dan warna yang cocok.' : 'Menunggu stok: belum ada unit eligible yang cocok dengan spesifikasi permintaan.',
            'note' => 'Pemeriksaan ini tidak melakukan reservasi. Ketersediaan diperiksa kembali saat checkout resmi.'];
    }

    public function act(MemberVehiclePreorder $preorder, User $actor, string $action, string $expected, ?string $reason = null): MemberVehiclePreorder
    {
        $this->authorize($actor, true);
        if (!in_array($action, ['notify', 'resend', 'cancel'], true)) throw ValidationException::withMessages(['action' => 'Tindakan tidak didukung.']);
        if ($action === 'resend' && mb_strlen(trim((string) $reason)) < 10) throw ValidationException::withMessages(['reason' => 'Alasan kirim ulang minimal 10 karakter wajib dicatat.']);
        return DB::transaction(function () use ($preorder, $actor, $action, $expected, $reason) {
            $preorder = MemberVehiclePreorder::with('pelanggan')->lockForUpdate()->findOrFail($preorder->getKey());
            $target = $action === 'cancel' ? 'dibatalkan' : 'notifikasi_terkirim';
            if ($action !== 'resend' && $preorder->status === $target && !$preorder->id_pesanan) return $preorder;
            if ($preorder->status !== $expected) throw ValidationException::withMessages(['expected_status' => 'Status telah berubah. Muat ulang detail sebelum melanjutkan.']);
            if ($preorder->id_pesanan || in_array($preorder->status, ['dibatalkan', 'dilanjutkan_ke_checkout'], true)) {
                throw ValidationException::withMessages(['action' => 'Permintaan ini tidak dapat diubah melalui tindak lanjut pre-order.']);
            }
            $userId = $preorder->pelanggan?->id_user;
            if (!$userId) throw ValidationException::withMessages(['action' => 'Akun member tidak tersedia untuk menerima pemberitahuan.']);
            if (in_array($action, ['notify', 'resend'], true)) {
                if (!app(ActiveMemberCustomerService::class)->isEligible($preorder->pelanggan)) throw ValidationException::withMessages(['action' => 'Membership/akun penerima tidak aktif atau belum terverifikasi.']);
                $stock = $this->stock($preorder, $actor);
                if (!$stock['available']) throw ValidationException::withMessages(['action' => $stock['message']]);
                $delivery = app(PreorderInventoryNotificationService::class);
                if ($action === 'resend' && !$delivery->enabled()) throw ValidationException::withMessages(['action' => 'Kirim ulang memerlukan penyimpanan riwayat/outbox yang aktif.']);
                if ($delivery->enabled()) {
                    if ($action === 'resend' && !$preorder->notified_at) throw ValidationException::withMessages(['action' => 'Belum ada pemberitahuan awal.']);
                    // One resend per current availability generation; a repeated HTTP retry cannot duplicate it.
                    $key = 'preorder:'.$preorder->getKey().':'.($action === 'resend' ? 'resend:' : 'availability:').max(1, (int) $preorder->stock_generation);
                    $delivery->deliver($preorder, $stock, $key, $actor->id_user);
                    if ($action === 'resend') $delivery->activity($preorder, 'resend_requested', ['message' => trim($reason)], $actor->id_user);
                    return $preorder->fresh();
                }
                $preorder->forceFill(['status' => 'notifikasi_terkirim', 'notified_at' => now()])->save();
                $title = 'Stok '.$preorder->nama_mobil.' tersedia'; $type = 'stok';
                $message = $preorder->nama_mobil.' / '.$preorder->varian.' / '.$preorder->tahun.' / '.$preorder->warna.'. Stok yang cocok ditemukan. Silakan periksa katalog dan lanjutkan checkout resmi. Unit belum direservasi.';
            } else {
                app(MemberVehiclePreorderService::class)->cancel($preorder->pelanggan, $preorder);
                $title = 'Pre-order dibatalkan oleh admin'; $type = 'info';
                $message = 'Pre-order #'.$preorder->getKey().' untuk '.$preorder->nama_mobil.' / '.$preorder->warna.' dibatalkan oleh admin. Hubungi YZA Automotive untuk informasi lebih lanjut.';
            }
            Notifikasi::firstOrCreate(['id_user' => $userId, 'tipe' => $type, 'referensi_tipe' => 'pre_order_mobil', 'referensi_id' => $preorder->getKey()],
                ['judul' => $title, 'pesan' => $message, 'is_read' => false, 'created_at' => now()]);
            app(MasterAuditService::class)->record($action, 'pre_order_mobil_member', (int) $preorder->getKey(), $expected.' → '.$target.'; actor '.$actor->id_user);
            return $preorder->fresh();
        });
    }
}
