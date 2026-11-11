<?php

namespace App\Services;

use App\Models\{MemberVehiclePreorder, Mobil, Notifikasi, PreorderInventoryEvent, User};
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;
use RuntimeException;

/** Transactional outbox: inventory commit first, retryable notification transaction second. */
class PreorderInventoryNotificationService
{
    public function enabled(): bool { return Schema::hasTable('preorder_inventory_events'); }

    public function registerObservers(): void
    {
        Mobil::saved(function (Mobil $car) {
            if ($car->wasRecentlyCreated || $car->wasChanged(['status','merk','model','tipe','tahun','warna','transmisi','bahan_bakar','kondisi','is_limited_edition','nomor_rangka','nomor_mesin','showcase_slug'])) {
                $this->queue($car, 'vehicle_saved');
            }
        });
        MemberVehiclePreorder::created(fn ($preorder) => $this->activity($preorder, 'created', ['message' => 'Pre-order dibuat.']));
        MemberVehiclePreorder::updated(function ($preorder) {
            if ($preorder->wasChanged(['status','id_pesanan'])) $this->activity($preorder, 'status_changed', [
                'from' => $preorder->getRawOriginal('status'), 'to' => $preorder->status, 'order_id' => $preorder->id_pesanan,
                'message' => 'Status permintaan berubah menjadi '.$preorder->status.'.']);
        });
        foreach ([\App\Models\PesananMobilMember::class, \App\Models\BookingMobil::class, \App\Models\PenjualanMobil::class] as $model) {
            $model::saved(function ($record) {
                if ($record->wasRecentlyCreated || $record->wasChanged(['status','id_mobil'])) {
                    $car = Mobil::find($record->id_mobil);
                    if ($car) $this->queue($car, 'Kapasitas reservasi/penjualan berubah.');
                }
            });
        }
        \App\Models\Membership::updated(function ($membership) {
            if ($membership->wasChanged('status') && $membership->isActive() && $this->enabled()) {
                MemberVehiclePreorder::where('id_pelanggan', $membership->id_pelanggan)->where('status', 'menunggu_stok')
                    ->get()->each(function ($item) {
                        $car = Mobil::find($item->matched_mobil_id ?: $item->id_mobil);
                        if ($car) $this->queue($car, 'Membership kembali aktif; verifikasi ulang stok.');
                    });
            }
        });
    }

    public function activity(MemberVehiclePreorder $preorder, string $kind, array $payload, ?int $actor = null): ?PreorderInventoryEvent
    {
        if (!$this->enabled()) return null;
        return PreorderInventoryEvent::create(['event_key' => (string) Str::uuid(), 'kind' => $kind,
            'preorder_id' => $preorder->getKey(), 'mobil_id' => $preorder->id_mobil,
            'actor_id' => $actor ?? auth()->id(), 'payload' => $payload, 'status' => 'processed', 'processed_at' => now()]);
    }

    public function queue(Mobil $car, string $reason, ?string $key = null): ?int
    {
        if (!$this->enabled()) return null;
        $event = PreorderInventoryEvent::firstOrCreate(['event_key' => $key ?: (string) Str::uuid()], [
            'kind' => 'inventory_changed', 'mobil_id' => $car->getKey(), 'actor_id' => auth()->id(),
            'payload' => ['message' => $reason, 'product_key' => app(MemberVehiclePreorderService::class)->productKey($car)], 'status' => 'pending']);
        DB::afterCommit(function () use ($event) {
            try { $this->process($event->getKey()); } catch (\Throwable $failure) { report($failure); }
        });
        return (int) $event->getKey();
    }

    public function process(int $id): void
    {
        try {
            DB::transaction(function () use ($id) {
                $event = PreorderInventoryEvent::lockForUpdate()->findOrFail($id);
                if ($event->status === 'processed') return;
                $car = Mobil::find($event->mobil_id);
                if ($car) {
                    // Candidate lookup is by authoritative product relation, never a display name.
                    $keys = array_unique(array_filter([$event->payload['product_key'] ?? null, app(MemberVehiclePreorderService::class)->productKey($car)]));
                    $items = MemberVehiclePreorder::whereNull('id_pesanan')->whereIn('status', ['menunggu_stok','stok_tersedia','notifikasi_terkirim'])
                        ->where(fn ($query) => $query->whereIn('product_key', $keys)->orWhere('id_mobil', $car->getKey()))->orderBy('id_pre_order')->get();
                    foreach ($items as $item) {
                        $this->reconcile($item, $event);
                    }
                }
                $event->update(['status' => 'processed', 'attempts' => $event->attempts + 1, 'last_error' => null, 'processed_at' => now()]);
            }, 3);
        } catch (\Throwable $failure) {
            PreorderInventoryEvent::whereKey($id)->where('status', '!=', 'processed')->update([
                'status' => 'failed', 'attempts' => DB::raw('attempts + 1'), 'last_error' => mb_substr($failure->getMessage(), 0, 2000)]);
            throw $failure;
        }
    }

    private function reconcile(MemberVehiclePreorder $item, PreorderInventoryEvent $event): void
    {
        $item = MemberVehiclePreorder::lockForUpdate()->findOrFail($item->getKey());
        $stock = app(PreorderManagementService::class)->matchingStock($item);
        $reason = $event->payload['message'] ?? 'Perubahan inventori resmi';
        $this->activity($item, 'inventory_changed', ['message' => 'Inventori terkait: '.($reason === 'vehicle_saved' ? 'data kendaraan diperbarui.' : $reason),
            'event_id' => $event->id, 'occurred_at' => $event->created_at?->toIso8601String()], $event->actor_id);
        $before = (bool) $item->stock_available;
        $available = (bool) $stock['available'];
        $generation = (int) $item->stock_generation + ((!$before && $available) ? 1 : 0);
        $item->forceFill(['stock_available' => $available, 'stock_generation' => $generation,
            'stock_checked_at' => now(), 'matched_mobil_id' => $stock['units'][0]['id'] ?? null])->save();
        $this->activity($item, 'stock_checked', ['message' => $stock['message'], 'event_id' => $event->id,
            'eligible' => $stock['total'], 'was_available' => $before, 'available' => $available], $event->actor_id);
        if (!$available) {
            if (in_array($item->status, ['stok_tersedia','notifikasi_terkirim'], true)) $item->update(['status' => 'menunggu_stok']);
            return;
        }
        // An inactive member stays pending; a later retry/reconciliation may deliver once eligible.
        if (!app(ActiveMemberCustomerService::class)->isEligible($item->pelanggan)) {
            $this->activity($item, 'notification_blocked', ['message' => 'Pemberitahuan ditunda: membership/akun tidak eligible.'], $event->actor_id);
            return;
        }
        $deliveryKey = 'preorder:'.$item->getKey().':availability:'.$generation;
        $delivery = PreorderInventoryEvent::where('event_key', $deliveryKey)->first();
        if ($delivery && $delivery->status === 'processed') return;
        $this->deliver($item, $stock, $deliveryKey, $event->actor_id);
    }

    public function deliver(MemberVehiclePreorder $item, array $stock, string $key, ?int $actor = null): Notifikasi
    {
        return DB::transaction(function () use ($item, $key, $actor) {
            $item = MemberVehiclePreorder::lockForUpdate()->findOrFail($item->getKey());
            if ($item->id_pesanan || in_array($item->status, ['dibatalkan','dilanjutkan_ke_checkout'], true)) throw new RuntimeException('Permintaan sudah ditutup.');
            $stock = app(PreorderManagementService::class)->matchingStock($item);
            if (!$stock['available'] || !app(ActiveMemberCustomerService::class)->isEligible($item->pelanggan)) {
                throw new RuntimeException('Stok cocok atau membership aktif belum terverifikasi.');
            }
            $delivery = PreorderInventoryEvent::firstOrCreate(['event_key' => $key], [
                'kind' => 'notification_sent', 'preorder_id' => $item->getKey(), 'mobil_id' => $stock['units'][0]['id'],
                'actor_id' => $actor, 'payload' => ['message' => 'Pemberitahuan ketersediaan dibuat; stok bukan jaminan reservasi.'], 'status' => 'pending']);
            if ($delivery->notification_id) return Notifikasi::findOrFail($delivery->notification_id);
            $code = 'PRE-'.str_pad((string) $item->getKey(), 5, '0', STR_PAD_LEFT);
            // Adopt a recorded legacy first notification without resetting its read state.
            $legacy = str_ends_with($key, ':availability:1') && $item->notified_at && $item->status === 'notifikasi_terkirim'
                ? Notifikasi::where('id_user', $item->pelanggan->id_user)->where('tipe', 'stok')->where('referensi_tipe', 'pre_order_mobil')->where('referensi_id', $item->getKey())->latest('id_notifikasi')->first() : null;
            $notice = $legacy ?: Notifikasi::create(['id_user' => $item->pelanggan->id_user, 'tipe' => 'stok', 'referensi_tipe' => 'pre_order_mobil',
                'referensi_id' => $item->getKey(), 'judul' => 'Mobil Pre-Order Anda Kini Tersedia',
                'pesan' => $code.' — '.$item->nama_mobil.' / '.$item->varian.' / '.$item->tahun.' / '.$item->warna
                    .'. Silakan periksa detail dan lanjutkan pembelian resmi. Stok dapat berubah; pemberitahuan ini tidak mencadangkan unit.',
                'is_read' => false, 'created_at' => now()]);
            $item->forceFill(['status' => 'notifikasi_terkirim', 'notified_at' => now(), 'matched_mobil_id' => $stock['units'][0]['id']])->save();
            $delivery->update(['notification_id' => $notice->getKey(), 'status' => 'processed', 'processed_at' => now()]);
            return $notice;
        }, 3);
    }

    public function drain(): int
    {
        if (!$this->enabled()) return 0;
        $count = 0;
        PreorderInventoryEvent::where('kind', 'inventory_changed')->whereIn('status', ['pending','failed'])->orderBy('id')->chunkById(100, function ($events) use (&$count) {
            foreach ($events as $event) { try { $this->process($event->id); $count++; } catch (\Throwable $failure) { report($failure); } }
        });
        // Resume a previously blocked recipient after account/membership reactivation.
        MemberVehiclePreorder::where('status', 'menunggu_stok')->where('stock_available', true)->whereNull('id_pesanan')
            ->orderBy('id_pre_order')->chunkById(100, function ($items) {
                foreach ($items as $item) if (app(ActiveMemberCustomerService::class)->isEligible($item->pelanggan)) {
                    $car = Mobil::find($item->matched_mobil_id ?: $item->id_mobil);
                    if ($car) $this->queue($car, 'Penerima kembali eligible; stok diverifikasi ulang.');
                }
            }, 'id_pre_order');
        return $count;
    }
}
