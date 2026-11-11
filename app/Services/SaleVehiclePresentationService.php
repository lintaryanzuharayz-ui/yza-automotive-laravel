<?php

namespace App\Services;

use App\Models\PenjualanMobil;

/** Read-only sale presentation: never overwrite a transaction to repair its display. */
class SaleVehiclePresentationService
{
    public function for(PenjualanMobil $sale): array
    {
        $sale->loadMissing(['mobil.foto','mobil.warnaPilihan.galeri','pelanggan','memberOrders']);
        $car = $sale->mobil;
        $orders = $sale->memberOrders;
        $order = $orders->count() === 1 ? $orders->first() : null;
        $bound = $order && (int) $order->id_mobil === (int) $sale->id_mobil
            && (int) $order->id_pelanggan === (int) $sale->id_pelanggan
            && $order->id_transaksi && (int) $order->id_transaksi === (int) $sale->id_transaksi;
        $warning = $orders->isNotEmpty() && !$bound ? 'Relasi pesanan dan penjualan perlu diverifikasi.' : null;
        $data = ['brand'=>$bound ? $order->snapshot_merk : $car?->merk,
            'model'=>$bound ? $order->snapshot_model : $car?->model,
            'variant'=>$bound ? $order->snapshot_tipe : $car?->tipe,
            'year'=>$bound ? $order->snapshot_tahun : $car?->tahun,
            'color'=>$bound ? $order->snapshot_warna : $car?->warna,
            'unit_code'=>$car?->kode_mobil, 'snapshot'=>(bool) $bound,
            'source'=>$bound ? 'Snapshot pesanan member' : 'Data unit terhubung saat ini; snapshot spesifikasi penjualan tidak tersedia.',
            'warning'=>$warning, 'order_number'=>$bound ? $order->nomor_pesanan : null,
            'order_status'=>$bound ? $order->status : null];
        $sameVehicle = $car !== null;
        foreach (['brand'=>'merk','model'=>'model','variant'=>'tipe','year'=>'tahun'] as $key=>$field) {
            $sameVehicle = $sameVehicle && mb_strtolower(trim((string) $car?->$field)) === mb_strtolower(trim((string) $data[$key]));
        }
        $photo = !$warning && $sameVehicle && $data['color']
            ? app(VehicleGalleryService::class)->verifiedColorImage($car, $data['color']) : null;
        $data['image'] = $photo ?: asset('assets/images/vehicle-photo-unavailable.svg');
        $data['has_photo'] = $photo !== null;
        $data['photo_source'] = !$photo ? 'Foto belum tersedia' : (str_contains($photo,'/storage/uploads/mobil/') ? 'Foto unit terhubung' : 'Foto referensi model/warna');
        $data['name'] = trim(($data['brand'] ?? '').' '.($data['model'] ?? '')) ?: 'Data kendaraan belum tersedia';
        $data['image_alt'] = ($photo ? '' : 'Foto belum tersedia: ').$data['name'].' · '.($data['color'] ?: 'Warna belum tercatat');
        if ($bound && in_array($order->status,['dibatalkan','ditolak'],true) && !in_array($sale->status,['batal','dibatalkan'],true)) {
            $data['warning'] = 'Status pesanan '.$order->status.'; status penjualan tercatat '.$sale->status.'. Perlu verifikasi petugas.';
        }
        return $data;
    }
}
