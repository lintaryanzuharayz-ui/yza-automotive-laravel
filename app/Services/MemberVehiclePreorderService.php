<?php

namespace App\Services;

use App\Models\{MemberVehiclePreorder, Mobil, Notifikasi, Pelanggan};
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MemberVehiclePreorderService
{
    public function productKey(Mobil $car): string
    {
        return $car->showcase_slug ? 'showcase:'.$car->showcase_slug : 'mobil:'.$car->getKey();
    }

    public function create(Pelanggan $customer, Mobil $car): MemberVehiclePreorder
    {
        return DB::transaction(function () use ($customer, $car): MemberVehiclePreorder {
            if ($car->exists) {
                $fresh = Mobil::query()->lockForUpdate()->findOrFail($car->getKey());
                $availability = app(VehicleAvailabilityService::class)->for($fresh);
                if (($availability['can_purchase'] ?? false) || (($availability['stock'] ?? 0) > 0)) {
                    throw new RuntimeException('Stok tersedia. Silakan lanjutkan melalui Beli Sekarang.');
                }
                $car = $fresh;
            }

            $key = $this->productKey($car);
            $existing = MemberVehiclePreorder::query()->where('id_pelanggan', $customer->id_pelanggan)->where('product_key', $key)->first();
            if ($existing && $existing->status !== 'dibatalkan') {
                throw new RuntimeException('Anda sudah terdaftar pada pre-order kendaraan ini.');
            }
            if ($existing) {
                $existing->forceFill(['status' => 'menunggu_stok', 'cancelled_at' => null, 'notified_at' => null])->save();
                return $existing->fresh();
            }

            return MemberVehiclePreorder::create([
                'id_pelanggan' => $customer->id_pelanggan,
                'id_mobil' => $car->exists ? $car->id_mobil : null,
                'product_key' => $key,
                'showcase_slug' => $car->showcase_slug,
                'nama_mobil' => trim($car->merk.' '.$car->model),
                'varian' => $car->tipe,
                'tahun' => $car->tahun,
                'warna' => $car->warna,
                'harga_referensi' => $car->harga_jual,
                'status' => 'menunggu_stok',
            ]);
        });
    }

    public function cancel(Pelanggan $customer, MemberVehiclePreorder $preorder): void
    {
        abort_unless((int) $preorder->id_pelanggan === (int) $customer->id_pelanggan, 404);
        if ($preorder->status === 'dilanjutkan_ke_checkout') throw new RuntimeException('Pre-order yang sudah dilanjutkan ke checkout tidak dapat dibatalkan.');
        $preorder->forceFill(['status' => 'dibatalkan', 'cancelled_at' => now()])->save();
    }

    public function notifyForRestock(Mobil $car): int
    {
        if ($car->status !== 'tersedia' || !$car->getKey()) return 0;
        $availability = app(VehicleAvailabilityService::class)->for($car);
        if (($availability['stock'] ?? 0) < 1) return 0;
        $key = $this->productKey($car);
        $items = MemberVehiclePreorder::query()->with('pelanggan')->where('product_key', $key)->where('status', 'menunggu_stok')->lockForUpdate()->get();
        $count = 0;
        foreach ($items as $item) {
            $item->forceFill(['status' => 'notifikasi_terkirim', 'notified_at' => now()])->save();
            $userId = $item->pelanggan?->id_user;
            if ($userId) {
                Notifikasi::firstOrCreate([
                    'id_user' => $userId, 'tipe' => 'stok', 'referensi_tipe' => 'pre_order_mobil', 'referensi_id' => $item->id_pre_order,
                ], [
                    'judul' => 'Stok '.$item->nama_mobil.' tersedia',
                    'pesan' => 'Unit yang Anda tunggu sudah tersedia. Silakan buka katalog dan lanjutkan checkout sebelum unit dipesan member lain.',
                    'is_read' => false,
                ]);
            }
            $count++;
        }
        return $count;
    }
}
