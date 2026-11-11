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
            if ($inventory->histories()->where('referensi_tipe', 'mobil')->where('referensi_id', $car->id_mobil)->where('jenis', 'stok_masuk')->exists()) return $inventory;
            $before = (int) $inventory->stock_total;
            $inventory->update(['stock_total' => $before + 1]);
            $this->history($inventory, 'stok_masuk', 1, $before, $before + 1, (int) $inventory->sold_units, (int) $inventory->sold_units, 'mobil', $car->id_mobil, 'Unit fisik baru ditambahkan admin.');
            return $inventory->fresh();
        });
    }

    public function reserveCount(Mobil $car): int
    {
        return app(VehicleColorStockService::class)->state($car)['reserved_units'];
    }

    public function recordSale(Mobil $car, int $quantity, ?string $referenceType, ?int $referenceId, ?string $note = null): void
    {
        if ($quantity !== 1) throw new RuntimeException('Penjualan harus mengikat tepat satu unit fisik.');
        $inventory = VehicleInventory::lockForUpdate()->where('inventory_key', $this->key($car))->first();
        if (!$inventory) return;
        if ($referenceType && $referenceId && $inventory->histories()->where('jenis', 'penjualan')->where('referensi_tipe', $referenceType)->where('referensi_id', $referenceId)->exists()) return;
        $before = (int) $inventory->sold_units;
        $after = $before + $quantity;
        if ($after > (int) $inventory->stock_total) throw new RuntimeException('Penjualan melebihi stok operasional.');
        $inventory->update(['sold_units' => $after]);
        $this->history($inventory, 'penjualan', 0, (int) $inventory->stock_total, (int) $inventory->stock_total, $before, $after, $referenceType, $referenceId, $note);
    }

    public function adjust(Mobil $car, int $newTotal, string $reason, ?string $note = null): VehicleInventory
    {
        return DB::transaction(function () use ($car, $newTotal, $reason, $note): VehicleInventory {
            $car = Mobil::lockForUpdate()->findOrFail($car->getKey());
            $physical = app(VehicleColorStockService::class)->units($car)->filter(fn ($unit) =>
                $unit->status !== 'nonaktif' && mb_strtolower(trim($unit->warna)) === mb_strtolower(trim($car->warna))
                && app(VehicleColorStockService::class)->identified($unit))->count();
            if ($newTotal !== $physical) throw new RuntimeException('Penyesuaian angka harus sesuai jumlah unit beridentitas nyata ('.$physical.'). Tambahkan unit dengan VIN unik atau ubah status unit yang tidak terikat transaksi melalui popup.');
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

    public function assertEditable(Mobil $car, array $data): void
    {
        if (isset($data['status']) && $data['status'] !== $car->status && in_array($data['status'], ['dibooking', 'terjual'], true)) throw new RuntimeException('Status reservasi/penjualan hanya dapat diubah melalui transaksi resmi.');
        $protected = ['warna', 'nomor_rangka', 'nomor_mesin', 'merk', 'model', 'tipe', 'tahun', 'status', 'showcase_slug', 'catalog_identity', 'transmisi', 'bahan_bakar', 'kondisi', 'is_limited_edition'];
        $changed = collect($protected)->contains(fn ($field) => array_key_exists($field, $data) && ($field === 'is_limited_edition'
            ? (bool) $data[$field] !== (bool) $car->$field : (string) $data[$field] !== (string) $car->$field));
        if (!$changed) return;
        if (app(VehicleColorStockService::class)->sold($car) || app(VehicleColorStockService::class)->reservedIds(collect([$car->getKey()]))->isNotEmpty()) {
            throw new RuntimeException('Identitas, warna, spesifikasi dan status unit terjual/terikat transaksi tidak boleh diubah.');
        }
    }

    public function recordUnitChange(Mobil $car, string $reason, string $proof, array $before = []): void
    {
        $inventory = $this->find($car) ?: $this->ensure($car, 1);
        $this->history($inventory, 'verifikasi_unit', 0, $inventory->stock_total, $inventory->stock_total,
            $inventory->sold_units, $inventory->sold_units, 'mobil', $car->getKey(), json_encode([
                'alasan' => $reason, 'bukti' => $proof, 'before' => $before,
                'after' => $car->only(['warna', 'nomor_rangka', 'nomor_mesin', 'status']),
            ], JSON_UNESCAPED_UNICODE));
    }

    public function histories(Mobil $car): \Illuminate\Support\Collection
    {
        $ids = app(VehicleColorStockService::class)->units($car)->pluck('id_mobil');
        return VehicleInventoryHistory::whereIn('id_vehicle_inventory', VehicleInventory::whereIn('id_mobil', $ids)->pluck('id_vehicle_inventory'))
            ->orWhere(fn ($query) => $query->where('referensi_tipe', 'mobil')->whereIn('referensi_id', $ids))
            ->latest('id_vehicle_inventory_history')->limit(50)->get();
    }

    private function history(VehicleInventory $inventory, string $type, int $change, int $stockBefore, int $stockAfter, int $soldBefore, int $soldAfter, ?string $referenceType, ?int $referenceId, ?string $note): void
    {
        $entry = VehicleInventoryHistory::create(['id_vehicle_inventory' => $inventory->id_vehicle_inventory, 'jenis' => $type, 'jumlah_perubahan' => $change, 'stock_before' => $stockBefore, 'stock_after' => $stockAfter, 'sold_before' => $soldBefore, 'sold_after' => $soldAfter, 'referensi_tipe' => $referenceType, 'referensi_id' => $referenceId, 'id_user' => auth()->id(), 'catatan' => $note, 'created_at' => now()]);
        $car = Mobil::find($inventory->id_mobil);
        if ($car) app(PreorderInventoryNotificationService::class)->queue($car, $note ?: $type, 'inventory-history:'.$entry->getKey());
    }
}
