<?php

namespace App\Services;

use App\Models\Mobil;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VehicleAvailabilityService
{
    private array $cache = [];

    public function for(Mobil $car): array
    {
        $key = $this->identityKey($car);
        if (array_key_exists($key, $this->cache)) {
            return $this->withCurrentUnitState($car, $this->cache[$key]);
        }

        if (!$car->exists || !$car->getKey()) {
            return $this->cache[$key] = $this->withCurrentUnitState($car, $this->syntheticAvailability($car));
        }

        $units = Mobil::query()
            ->where('merk', $car->merk)->where('model', $car->model)->where('tipe', $car->tipe)
            ->where('tahun', $car->tahun)->where('warna', $car->warna)->where('transmisi', $car->transmisi)
            ->where('bahan_bakar', $car->bahan_bakar)->where('kondisi', $car->kondisi)
            ->where('is_limited_edition', (bool) $car->is_limited_edition)
            ->where('status', '!=', 'nonaktif')->get(['id_mobil', 'status']);

        $availableIds = $units->where('status', 'tersedia')->pluck('id_mobil')->values();
        $bookingIds = collect();
        $orderIds = collect();
        if ($availableIds->isNotEmpty()) {
            $bookingIds = $this->activeBookingIds($availableIds);
            $orderIds = $this->activeMemberOrderIds($availableIds);
        }

        $reservedIds = $bookingIds->merge($orderIds);
        $orderableIds = $availableIds->diff($reservedIds)->values();
        $base = $this->statusForStock($orderableIds->count());
        $base['is_limited_edition'] = (bool) $car->is_limited_edition;
        $base['active_booking_count'] = $bookingIds->unique()->count();
        $base['active_order_count'] = $orderIds->unique()->count();
        $base['active_reservation_count'] = $reservedIds->unique()->count();
        $base['orderable_ids'] = $orderableIds->map(fn ($id): int => (int) $id)->all();
        $this->cache[$key] = $base;
        return $this->withCurrentUnitState($car, $base);
    }

    public function decorate(iterable $cars): Collection
    {
        return collect($cars)->map(function (Mobil $car): Mobil {
            $car->setAttribute('availability', $this->for($car));
            return $car;
        });
    }

    private function withCurrentUnitState(Mobil $car, array $availability): array
    {
        $id = $car->getKey();
        $availability['can_purchase'] = (bool) ($id && in_array((int) $id, $availability['orderable_ids'] ?? [], true));
        return $availability;
    }

    private function syntheticAvailability(Mobil $car): array
    {
        return [
            'stock' => 0,
            'label' => 'STOK HABIS',
            'detail' => 'Pre-order tersedia',
            'status_class' => 'is-empty',
            'can_preorder' => true,
            'preorder_reason' => 'stok_habis',
            'is_limited_edition' => (bool) $car->is_limited_edition,
            'orderable_ids' => [],
        ];
    }

    private function statusForStock(int $stock): array
    {
        if ($stock === 0) return ['stock' => 0, 'label' => 'STOK HABIS', 'detail' => 'Pre-order tersedia', 'status_class' => 'is-empty', 'can_preorder' => true, 'preorder_reason' => 'stok_habis'];
        if ($stock === 1) return ['stock' => 1, 'label' => 'SISA 1 UNIT', 'detail' => 'Sisa 1 Unit', 'status_class' => 'is-last'];
        if ($stock <= 3) return ['stock' => $stock, 'label' => 'STOK MENIPIS · SISA '.$stock.' UNIT', 'detail' => 'Sisa '.$stock.' Unit', 'status_class' => 'is-low'];
        return ['stock' => $stock, 'label' => 'TERSEDIA · '.$stock.' UNIT', 'detail' => 'Stok '.$stock.' Unit', 'status_class' => 'is-available'];
    }

    private function identityKey(Mobil $car): string
    {
        return implode('|', array_map(static fn ($value): string => (string) ($value ?? ''), [strtolower((string) $car->merk), strtolower((string) $car->model), strtolower((string) $car->tipe), (string) $car->tahun, strtolower((string) $car->warna), strtolower((string) $car->transmisi), strtolower((string) $car->bahan_bakar), strtolower((string) $car->kondisi), (bool) $car->is_limited_edition ? '1' : '0']));
    }

    private function activeBookingIds(Collection $ids): Collection
    {
        if (!Schema::hasTable('booking_mobil')) return collect();
        return collect(DB::table('booking_mobil')->whereIn('id_mobil', $ids)->whereIn('status', ['menunggu', 'dikonfirmasi'])->pluck('id_mobil')->all());
    }

    private function activeMemberOrderIds(Collection $ids): Collection
    {
        if (!Schema::hasTable('pesanan_mobil_member')) return collect();
        return collect(DB::table('pesanan_mobil_member')->whereIn('id_mobil', $ids)->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])->pluck('id_mobil')->all());
    }
}
