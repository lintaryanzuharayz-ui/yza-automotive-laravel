<?php

namespace App\Services;

use App\Models\{Mobil, VehicleInventory, VehicleInventoryHistory};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VehicleInventoryService
{
    public function key(Mobil $car): string
    {
        return implode('|', array_map(static fn ($value): string => (string) ($value ?? ''), [
            strtolower((string) $car->merk), strtolower((string) $car->model), strtolower((string) $car->tipe), (string) $car->tahun,
            strtolower((string) $car->warna), strtolower((string) $car->transmisi), strtolower((string) $car->bahan_bakar),
            strtolower((string) $car->kondisi), (bool) $car->is_limited_edition ? '1' : '0',
        ]));
    }

    public function find(Mobil $car): ?VehicleInventory
    {
        return VehicleInventory::where('inventory_key', $this->key($car))->first();
    }

    public function ensure(Mobil $car, int $initialStock = 1): VehicleInventory
    {
        return DB::transaction(function () use ($car, $initialStock): VehicleInventory {
            $inventory = VehicleInventory::lockForUpdate()->where('inventory_key', $this->key($car))->first();
            if ($inventory) return $inventory;
            $inventory = VehicleInventory::create(['inventory_key' => $this->key($car), 'id_mobil' => $car->id_mobil, 'stock_total' => $initialStock, 'sold_units' => 0]);
            $this->history($inventory, 'stok_masuk', $initialStock, 0, $initialStock, 0, 0, null, null, 'Inisialisasi unit fisik baru.');
            return $inventory;
        });
    }

    public function registerPhysicalUnit(Mobil $car): VehicleInventory
    {
        return DB::transaction(function () use ($car): VehicleInventory {
            $key = $this->key($car);
            $inventory = VehicleInventory::lockForUpdate()->where('inventory_key', $key)->first();
            if (!$inventory) return $this->ensure($car, 1);
            if ((int) $inventory->id_mobil === (int) $car->id_mobil) return $inventory;
            $before = (int) $inventory->stock_total;
            $inventory->update(['stock_total' => $before + 1]);
            $this->history($inventory, 'stok_masuk', 1, $before, $before + 1, (int) $inventory->sold_units, (int) $inventory->sold_units, 'mobil', $car->id_mobil, 'Unit fisik baru ditambahkan admin.');
            return $inventory->fresh();
        });
    }

    public function reserveCount(Mobil $car): int
    {
        $ids = Mobil::where('merk', $car->merk)->where('model', $car->model)->where('tipe', $car->tipe)->where('tahun', $car->tahun)
            ->where('warna', $car->warna)->where('transmisi', $car->transmisi)->where('bahan_bakar', $car->bahan_bakar)->where('kondisi', $car->kondisi)
            ->where('is_limited_edition', (bool) $car->is_limited_edition)->where('status', '!=', 'nonaktif')->pluck('id_mobil');
        if ($ids->isEmpty()) return 0;
        $bookings = DB::table('booking_mobil')->whereIn('id_mobil', $ids)->whereIn('status', ['menunggu', 'dikonfirmasi'])->count();
        $orders = DB::table('pesanan_mobil_member')->whereIn('id_mobil', $ids)->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])->count();
        return $bookings + $orders;
    }

    public function recordSale(Mobil $car, int $quantity, ?string $referenceType, ?int $referenceId, ?string $note = null): void
    {
        if ($quantity < 1) throw new RuntimeException('Jumlah penjualan harus positif.');
        $inventory = VehicleInventory::lockForUpdate()->where('inventory_key', $this->key($car))->first();
        if (!$inventory) return;
        $before = (int) $inventory->sold_units;
        $after = $before + $quantity;
        if ($after > (int) $inventory->stock_total) throw new RuntimeException('Penjualan melebihi stok operasional.');
        $inventory->update(['sold_units' => $after]);
        $this->history($inventory, 'penjualan', 0, (int) $inventory->stock_total, (int) $inventory->stock_total, $before, $after, $referenceType, $referenceId, $note);
    }

    public function adjust(Mobil $car, int $newTotal, string $reason, ?string $note = null): VehicleInventory
    {
        return DB::transaction(function () use ($car, $newTotal, $reason, $note): VehicleInventory {
            $inventory = VehicleInventory::lockForUpdate()->where('inventory_key', $this->key($car))->first();
            if (!$inventory) throw new RuntimeException('Kendaraan belum memiliki pencatatan inventory agregat.');
            $reserved = $this->reserveCount($car);
            $minimum = (int) $inventory->sold_units + $reserved;
            if ($newTotal < $minimum) throw new RuntimeException("Stok total tidak boleh kurang dari terjual + reservasi aktif ({$minimum} unit).");
            $before = (int) $inventory->stock_total;
            $inventory->update(['stock_total' => $newTotal]);
            $this->history($inventory, 'penyesuaian', $newTotal - $before, $before, $newTotal, (int) $inventory->sold_units, (int) $inventory->sold_units, 'admin', null, trim($reason.' '.($note ?: '')));
            return $inventory->fresh();
        });
    }

    private function history(VehicleInventory $inventory, string $type, int $change, int $stockBefore, int $stockAfter, int $soldBefore, int $soldAfter, ?string $referenceType, ?int $referenceId, ?string $note): void
    {
        VehicleInventoryHistory::create(['id_vehicle_inventory' => $inventory->id_vehicle_inventory, 'jenis' => $type, 'jumlah_perubahan' => $change, 'stock_before' => $stockBefore, 'stock_after' => $stockAfter, 'sold_before' => $soldBefore, 'sold_after' => $soldAfter, 'referensi_tipe' => $referenceType, 'referensi_id' => $referenceId, 'id_user' => auth()->id(), 'catatan' => $note, 'created_at' => now()]);
    }
}
