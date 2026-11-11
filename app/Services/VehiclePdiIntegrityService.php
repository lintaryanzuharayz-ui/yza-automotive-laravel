<?php

namespace App\Services;

use App\Models\{Mobil, PdiPesananMobil, PesananMobilMember};
use RuntimeException;

class VehiclePdiIntegrityService
{
    public function assertPhysicalIdentity(PesananMobilMember $order): void
    {
        // This lock serializes use of a master VIN by different aggregate orders.
        $car = Mobil::lockForUpdate()->findOrFail($order->id_mobil);
        if (!filled($car->nomor_rangka) || !filled($car->nomor_mesin)) {
            throw new RuntimeException('VIN/nomor mesin unit fisik belum lengkap. PDI lulus dan serah-terima diblokir sampai identitas unit diverifikasi.');
        }
        $claimed = PdiPesananMobil::where('id_pesanan','<>',$order->id_pesanan)
            ->where('status','lulus')->where(fn($query)=>$query->where('nomor_rangka_snapshot',$car->nomor_rangka)
                ->orWhere('nomor_mesin_snapshot',$car->nomor_mesin))
            ->whereHas('pesanan',fn($query)=>$query->whereNotIn('status',['ditolak','dibatalkan']))->exists();
        if ($claimed) throw new RuntimeException('VIN ini sudah terikat pada PDI/pesanan lain. Stok agregat bukan izin memakai VIN yang sama untuk unit kedua.');
    }

    public function binding(PesananMobilMember $order): array
    {
        $car = Mobil::lockForUpdate()->findOrFail($order->id_mobil);
        $order->setRelation('mobil', $car);
        $order->load(['penjualan', 'transaksi']);
        $sale = $order->penjualan;
        if (!$sale || !$order->transaksi || (int) $sale->id_mobil !== (int) $order->id_mobil
            || (int) $sale->id_pelanggan !== (int) $order->id_pelanggan
            || (int) $sale->id_transaksi !== (int) $order->id_transaksi
            || (int) $order->transaksi->id_pelanggan !== (int) $order->id_pelanggan) {
            throw new RuntimeException('Identitas pesanan, transaksi, dan kendaraan PDI tidak sesuai.');
        }
        return [$order->only(['id_pesanan','id_mobil','id_pelanggan','id_penjualan','id_transaksi','snapshot_merk','snapshot_model','snapshot_tipe','snapshot_tahun','snapshot_warna']),
            $car->only(['nomor_rangka','nomor_mesin','merk','model','tipe','tahun','warna'])];
    }

    public function proof(PesananMobilMember $order, PdiPesananMobil $pdi): string
    {
        $checklist = $pdi->checklist ?? [];
        unset($checklist['_integrity'], $checklist['_history']);
        return hash('sha256', json_encode([$this->binding($order), $checklist,
            $pdi->only(['id_pesanan','id_petugas','status','result','nomor_rangka_snapshot','nomor_mesin_snapshot']),
            $pdi->diperiksa_at?->toISOString(), $pdi->completed_at?->toISOString()]));
    }

    public function history(PdiPesananMobil $pdi): array
    {
        $checklist = $pdi->checklist ?? [];
        $history = $checklist['_history'] ?? [];
        unset($checklist['_history']);
        if ($pdi->exists) $history[] = ['at' => now()->toISOString(), 'inspection' => $pdi->only([
            'id_petugas','status','result','started_at','diperiksa_at','completed_at','nomor_rangka_snapshot','nomor_mesin_snapshot','catatan',
            'mesin','eksterior','interior','rem','ban','kelistrikan','lampu','ac','dokumen','final_quality']), 'checklist' => $checklist];
        return $history;
    }

    public function assertPassed(PesananMobilMember $order): void
    {
        $this->assertPhysicalIdentity($order);
        $pdi = PdiPesananMobil::where('id_pesanan', $order->id_pesanan)->lockForUpdate()->first();
        if (!$pdi || $pdi->status !== 'lulus' || $pdi->result !== 'passed' || !$pdi->diperiksa_at || !$pdi->completed_at) throw new RuntimeException('PDI belum lengkap dan lulus.');
        app(InspectionIntegrityService::class)->inspector((int) $pdi->id_petugas, null, 'pdi', false);
        foreach (app(MemberVehiclePurchaseService::class)->checklistDefinition() as $section => $keys) {
            foreach ($keys as $key) if (!in_array(data_get($pdi->checklist, "$section.$key.result"), ['pass','na'], true)) throw new RuntimeException('Checklist PDI belum lengkap atau masih memiliki ISSUE.');
        }
        if (!hash_equals($this->proof($order, $pdi), (string) ($pdi->checklist['_integrity'] ?? ''))) throw new RuntimeException('PDI tidak lagi valid. Periksa ulang identitas kendaraan dan pesanan.');
    }
}
