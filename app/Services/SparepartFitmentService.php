<?php

namespace App\Services;

use App\Models\{KendaraanPelanggan, Sparepart, SparepartFitment};
use RuntimeException;

class SparepartFitmentService
{
    public const VERIFIED_FIT = 'verified_fit';
    public const UNVERIFIED = 'unverified';
    public const VERIFIED_NOT_FIT = 'verified_not_fit';

    public function statusFor(Sparepart $part, ?KendaraanPelanggan $vehicle): array
    {
        $fitments = $part->relationLoaded('fitments') ? $part->fitments : $part->fitments()->get();
        if (!$vehicle) return ['status' => self::UNVERIFIED, 'label' => 'Belum Terverifikasi', 'matched' => null];
        $matched = $fitments->filter(fn ($fitment) => $this->matches($fitment, $vehicle));
        if ($matched->contains(fn ($fitment) => $fitment->status_verifikasi === self::VERIFIED_FIT)) return ['status' => self::VERIFIED_FIT, 'label' => 'Terverifikasi Cocok', 'matched' => $matched->firstWhere('status_verifikasi', self::VERIFIED_FIT)];
        if ($matched->contains(fn ($fitment) => $fitment->status_verifikasi === self::VERIFIED_NOT_FIT)) return ['status' => self::VERIFIED_NOT_FIT, 'label' => 'Terverifikasi Tidak Cocok', 'matched' => $matched->firstWhere('status_verifikasi', self::VERIFIED_NOT_FIT)];
        return ['status' => self::UNVERIFIED, 'label' => 'Belum Terverifikasi', 'matched' => null];
    }

    public function assertUsableForVehicle(Sparepart $part, ?KendaraanPelanggan $vehicle): void
    {
        // Legacy vehicles without engine identity remain readable. Identified
        // vehicles require an explicit verified fitment before new usage.
        // A model-only legacy vehicle remains readable. Enforce strict fitment once the
        // vehicle has a stable engine identity, which is the authoritative match key.
        if (!$vehicle || !$vehicle->nomor_mesin) return;
        $result = $this->statusFor($part, $vehicle);
        if ($result['status'] === self::VERIFIED_NOT_FIT) throw new RuntimeException('Sparepart terverifikasi tidak cocok dengan kendaraan ini.');
        if ($result['status'] !== self::VERIFIED_FIT) throw new RuntimeException('Kompatibilitas sparepart belum terverifikasi untuk kendaraan ini.');
    }

    private function matches(SparepartFitment $fitment, KendaraanPelanggan $vehicle): bool
    {
        if ($fitment->merk_kendaraan && strcasecmp(trim($fitment->merk_kendaraan), trim((string) $vehicle->merk)) !== 0) return false;
        if ($fitment->model_kendaraan && stripos((string) $vehicle->model, (string) $fitment->model_kendaraan) === false) return false;
        if ($fitment->tahun_mulai && (int) $vehicle->tahun < (int) $fitment->tahun_mulai) return false;
        if ($fitment->tahun_akhir && (int) $vehicle->tahun > (int) $fitment->tahun_akhir) return false;
        if ($fitment->kode_mesin && stripos((string) $vehicle->nomor_mesin, (string) $fitment->kode_mesin) === false) return false;
        return true;
    }
}
