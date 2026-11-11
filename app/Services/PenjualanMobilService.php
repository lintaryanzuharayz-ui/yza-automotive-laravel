<?php

namespace App\Services;

use App\Models\{BookingMobil, Mobil, Pelanggan, PenjualanMobil, Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PenjualanMobilService
{
    public function newSaleCode(): string
    {
        return $this->code('penjualan_mobil', 'kode_penjualan', 'SALE');
    }

    private function code(string $table, string $column, string $prefix): string
    {
        do { $code = $prefix.'-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4))); }
        while (DB::table($table)->where($column, $code)->exists());
        return $code;
    }

    public function create(array $input, int $petugasId): PenjualanMobil
    {
        return DB::transaction(function () use ($input, $petugasId): PenjualanMobil {
            if (!empty($input['id_pesanan'])) throw new RuntimeException('Lanjutkan transaksi pesanan melalui relasi penjualan existing.');
            $customer = Pelanggan::whereKey((int)$input['id_pelanggan'])->where('status', 'aktif')->first();
            if (!$customer) throw new RuntimeException('Pelanggan tidak aktif.');
            app(ActiveMemberCustomerService::class)->assertEligibleLocked($customer);
            $car = Mobil::lockForUpdate()->find((int)$input['id_mobil']);
            $inventoryService = app(VehicleInventoryService::class);
            $inventory = $car ? \App\Models\VehicleInventory::where('inventory_key', $inventoryService->key($car))->lockForUpdate()->first() : null;
            if (!$car || !in_array($car->status, ['tersedia', 'dibooking'], true)) throw new RuntimeException('Mobil tidak tersedia untuk dijual.');
            if (\App\Models\PesananMobilMember::where('id_mobil', $car->id_mobil)->where('id_pelanggan', $customer->id_pelanggan)
                    ->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])->exists()) {
                throw new RuntimeException('Pelanggan sudah memiliki pesanan atau penjualan aktif untuk kendaraan ini. Lanjutkan transaksi yang sudah ada.');
            }
            if ((PenjualanMobil::where('id_mobil', $car->id_mobil)->where('status', 'proses')->whereHas('transaksi', fn ($q) => $q->where('status', '<>', 'dibatalkan'))->exists()
                || \App\Models\PesananMobilMember::where('id_mobil', $car->id_mobil)->whereNotIn('status', ['ditolak','dibatalkan','diserahkan'])->exists())) throw new RuntimeException('Unit sudah memiliki pesanan atau penjualan aktif.');
            if ($car->status === 'tersedia') app(VehicleColorStockService::class)->assertOrderable($car);
            $price = (string)$car->harga_jual;
            $discount = (string)$input['diskon'];
            $dp = (string)$input['dp'];
            if ($discount < 0 || $discount > $price) throw new RuntimeException('Diskon tidak valid.');
            $total = (string) DB::selectOne('SELECT CAST(? - ? AS DECIMAL(15,2)) AS amount', [$price, $discount])->amount;
            if ($dp < 0 || $dp > $total) throw new RuntimeException('DP tidak boleh negatif atau melebihi total.');
            $bookingId = !empty($input['id_booking']) ? (int)$input['id_booking'] : null;
            if ($car->status === 'dibooking') {
                $booking = BookingMobil::lockForUpdate()->whereKey($bookingId)->where('id_mobil', $car->id_mobil)->where('id_pelanggan', $customer->id_pelanggan)->where('status', 'dikonfirmasi')->first();
                if (!$booking) throw new RuntimeException('Unit dibooking pelanggan lain atau booking belum dikonfirmasi.');
            } elseif ($inventory) {
                if (((int) $inventory->stock_total - (int) $inventory->sold_units - $inventoryService->reserveCount($car)) < 1) throw new RuntimeException('Stok kendaraan tidak tersedia.');
                if ($bookingId) {
                    $booking = BookingMobil::lockForUpdate()->whereKey($bookingId)->where('id_mobil', $car->id_mobil)->where('id_pelanggan', $customer->id_pelanggan)->where('status', 'dikonfirmasi')->first();
                    if (!$booking) throw new RuntimeException('Booking kendaraan tidak valid atau belum dikonfirmasi.');
                }
            } else { $bookingId = null; }
            $note = trim((string)($input['catatan'] ?? ''));
            $tx = new Transaksi();
            $tx->forceFill(['kode_transaksi'=>$this->code('transaksi','kode_transaksi','TRX'),'id_pelanggan'=>$customer->id_pelanggan,'id_petugas'=>$petugasId,'jenis_transaksi'=>'penjualan_mobil','tanggal'=>now(),'subtotal'=>$price,'diskon'=>$discount,'total'=>$total,'status'=>'belum_bayar','catatan'=>$note])->save();
            $sale = new PenjualanMobil();
            $sale->forceFill(['kode_penjualan'=>$input['_issued_sale_code'] ?? $this->newSaleCode(),'id_transaksi'=>$tx->id_transaksi,'id_pelanggan'=>$customer->id_pelanggan,'id_mobil'=>$car->id_mobil,'id_petugas'=>$petugasId,'id_booking'=>$bookingId,'tanggal_penjualan'=>now(),'harga_mobil'=>$price,'diskon'=>$discount,'dp'=>$dp,'total_akhir'=>$total,'metode_pembelian'=>$input['metode_pembelian'],'status'=>'proses','catatan'=>$note])->save();
            $car->forceFill(['status'=>'dibooking'])->save();
            return $sale->fresh(['transaksi','mobil','booking','pelanggan']);
        });
    }
    public function createWithPromo(array $input, int $petugasId, ?int $promoId = null): PenjualanMobil
    {
        return DB::transaction(function () use ($input, $petugasId, $promoId): PenjualanMobil {
            if (!empty($input['_issued_sale_code'])) {
                $existing = PenjualanMobil::where('kode_penjualan', $input['_issued_sale_code'])->first();
                if ($existing) {
                    if ((int) $existing->id_petugas !== $petugasId
                        || (int) $existing->id_pelanggan !== (int) $input['id_pelanggan']
                        || (int) $existing->id_mobil !== (int) $input['id_mobil']) {
                        throw new RuntimeException('Form sudah digunakan untuk transaksi lain.');
                    }
                    return $existing->load(['transaksi', 'mobil', 'booking', 'pelanggan']);
                }
            }
            $sale = $this->create($input, $petugasId);
            if ($promoId !== null) app(PromoEngineService::class)->applyToTransaction($sale->transaksi, $promoId, 'penjualan_mobil');
            $sale = $sale->fresh(['transaksi', 'mobil', 'booking', 'pelanggan']);
            return $sale;
        });
    }
}
