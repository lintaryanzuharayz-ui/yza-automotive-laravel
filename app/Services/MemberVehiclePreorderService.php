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
        return app(CatalogVehicleIdentityService::class)->key($car);
    }

    public function create(Pelanggan $customer, Mobil $car, ?string $requestedColor = null): MemberVehiclePreorder
    {
        return DB::transaction(function () use ($customer, $car, $requestedColor): MemberVehiclePreorder {
            $customer = Pelanggan::lockForUpdate()->findOrFail($customer->getKey());
            app(ActiveMemberCustomerService::class)->assertEligibleLocked($customer);
            if ($car->exists) {
                $fresh = Mobil::query()->lockForUpdate()->findOrFail($car->getKey());
                $car = $fresh;
            }

            $color = app(VehicleColorStockService::class)->selected($car, $requestedColor);
            if ($color['stock'] > 0) throw new RuntimeException('Warna tersedia. Silakan lanjutkan pembelian.');

            $key = $this->productKey($car);
            if (\Illuminate\Support\Facades\Schema::hasIndex('pre_order_mobil_member', 'uq_preorder_customer_product')
                && MemberVehiclePreorder::where('id_pelanggan', $customer->getKey())->where('product_key', $key)->where('warna', '<>', $color['label'])->exists()) {
                throw new RuntimeException('Penyimpanan Pre-Order beberapa warna memerlukan migration stok per warna. Tidak ada unit atau transaksi yang dibuat.');
            }
            $existing = MemberVehiclePreorder::query()->where('id_pelanggan', $customer->id_pelanggan)->where('product_key', $key)->where('warna', $color['label'])->first();
            if ($existing && $existing->status !== 'dibatalkan') {
                throw new RuntimeException('Anda sudah terdaftar pada pre-order kendaraan ini.');
            }
            if ($existing) {
                $existing->forceFill(['status' => 'menunggu_stok', 'cancelled_at' => null, 'notified_at' => null, 'stock_available' => false, 'matched_mobil_id' => null, 'stock_checked_at' => null])->save();
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
                'warna' => $color['label'],
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
        // Compatibility entry point; the durable service revalidates exact stock after commit.
        return app(PreorderInventoryNotificationService::class)->queue($car, 'Pemeriksaan ketersediaan kendaraan') ? 1 : 0;
    }
}
