<?php

namespace App\Services;

use App\Models\{KendaraanPelanggan, Mobil, PenjualanMobil};
use Illuminate\Database\Eloquent\Builder;

class VerifiedCarOwnershipService
{
    public function verifiedVehiclesFor(int $customerId)
    {
        $sales = $this->completedSalesFor($customerId)->with('mobil')->get();
        $identifiers = $sales->flatMap(function ($sale): array {
            $car = $sale->mobil;
            if (!$car) return [];
            return array_values(array_filter([
                $car->nomor_rangka ? ['nomor_rangka', $car->nomor_rangka] : null,
                $car->nomor_mesin ? ['nomor_mesin', $car->nomor_mesin] : null,
            ]));
        });
        if ($identifiers->isEmpty()) return collect();
        return KendaraanPelanggan::query()
            ->where('id_pelanggan', $customerId)
            ->where('status', 'aktif')
            ->where(function (Builder $query) use ($identifiers): void {
                foreach ($identifiers as [$column, $value]) $query->orWhere($column, $value);
            })
            ->latest()
            ->get();
    }

    public function completedSalesFor(int $customerId): Builder
    {
        return PenjualanMobil::query()
            ->where('id_pelanggan', $customerId)
            ->where('status', 'selesai')
            ->whereHas('transaksi', fn (Builder $query) => $query
                ->where('jenis_transaksi', 'penjualan_mobil')
                ->where('status', 'lunas'))
            ->whereHas('memberOrder', fn (Builder $query) => $query->where('status', 'diserahkan'));
    }

    public function customerHasVerifiedPurchase(int $customerId): bool
    {
        return $this->completedSalesFor($customerId)
            ->whereHas('mobil', fn (Builder $query) => $query
                ->whereNotNull('nomor_rangka')
                ->whereNotNull('nomor_mesin'))
            ->exists();
    }

    public function vehicleBelongsToVerifiedPurchase(int $customerId, int $vehicleId): bool
    {
        $vehicle = KendaraanPelanggan::query()
            ->where('id_pelanggan', $customerId)
            ->where('status', 'aktif')
            ->find($vehicleId);

        if (!$vehicle || (!$vehicle->nomor_rangka && !$vehicle->nomor_mesin)) {
            return false;
        }

        return $this->completedSalesFor($customerId)
            ->whereHas('mobil', function (Builder $query) use ($vehicle): void {
                $query->where(function (Builder $match) use ($vehicle): void {
                    if ($vehicle->nomor_rangka) {
                        $match->where('nomor_rangka', $vehicle->nomor_rangka);
                    }
                    if ($vehicle->nomor_mesin) {
                        $method = $vehicle->nomor_rangka ? 'orWhere' : 'where';
                        $match->{$method}('nomor_mesin', $vehicle->nomor_mesin);
                    }
                });
            })
            ->exists();
    }

    public function syncVehicleFromCompletedSale(PenjualanMobil $sale): ?KendaraanPelanggan
    {
        $sale->loadMissing(['mobil', 'transaksi', 'memberOrder']);
        $car = $sale->mobil;
        if (!$car || !$sale->id_pelanggan || !$sale->transaksi || $sale->transaksi->status !== 'lunas' || $sale->status !== 'selesai' || $sale->memberOrder?->status !== 'diserahkan') {
            return null;
        }

        $existing = KendaraanPelanggan::query()
            ->where('id_pelanggan', $sale->id_pelanggan)
            ->where(function (Builder $query) use ($car): void {
                $query->where('nomor_rangka', $car->nomor_rangka)
                    ->orWhere('nomor_mesin', $car->nomor_mesin);
            })
            ->first();

        if ($existing) {
            if ($existing->status !== 'aktif') {
                $existing->update(['status' => 'aktif']);
            }
            return $existing;
        }

        $vehicle = new KendaraanPelanggan([
            'merk' => $car->merk,
            'model' => $car->model,
            'tipe' => $car->tipe,
            'tahun' => $car->tahun,
            'warna' => $car->warna,
            'transmisi' => $car->transmisi,
            'bahan_bakar' => $car->bahan_bakar,
            'nomor_rangka' => $car->nomor_rangka,
            'nomor_mesin' => $car->nomor_mesin,
            'kilometer' => $car->kilometer,
            'status' => 'aktif',
        ]);
        $vehicle->id_pelanggan = $sale->id_pelanggan;
        $vehicle->save();
        return $vehicle;
    }
}
