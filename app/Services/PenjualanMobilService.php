<?php

namespace App\Services;

use App\Models\{BookingMobil, Mobil, Pelanggan, PenjualanMobil, Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PenjualanMobilService
{
    private function code(string $table, string $column, string $prefix): string
    {
        do { $code = $prefix.'-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4))); }
        while (DB::table($table)->where($column, $code)->exists());
        return $code;
    }

    public function create(array $input, int $petugasId): PenjualanMobil
    {
        return DB::transaction(function () use ($input, $petugasId): PenjualanMobil {
            $customer = Pelanggan::whereKey((int)$input['id_pelanggan'])->where('status', 'aktif')->first();
            if (!$customer) throw new RuntimeException('Pelanggan tidak aktif.');
            $car = Mobil::lockForUpdate()->find((int)$input['id_mobil']);
            if (!$car || !in_array($car->status, ['tersedia', 'dibooking'], true)) throw new RuntimeException('Mobil tidak tersedia untuk dijual.');
            $price = (float)$car->harga_jual;
            $discount = (float)$input['diskon'];
            $dp = (float)$input['dp'];
            if ($discount < 0 || $discount > $price) throw new RuntimeException('Diskon tidak valid.');
            $total = $price - $discount;
            if ($dp < 0 || $dp > $total) throw new RuntimeException('DP tidak boleh negatif atau melebihi total.');
            $bookingId = !empty($input['id_booking']) ? (int)$input['id_booking'] : null;
            if ($car->status === 'dibooking') {
                $booking = BookingMobil::lockForUpdate()->whereKey($bookingId)->where('id_mobil', $car->id_mobil)->where('id_pelanggan', $customer->id_pelanggan)->where('status', 'dikonfirmasi')->first();
                if (!$booking) throw new RuntimeException('Unit dibooking pelanggan lain atau booking belum dikonfirmasi.');
            } else { $bookingId = null; }
            $note = trim((string)($input['catatan'] ?? ''));
            $tx = new Transaksi();
            $tx->forceFill(['kode_transaksi'=>$this->code('transaksi','kode_transaksi','TRX'),'id_pelanggan'=>$customer->id_pelanggan,'id_petugas'=>$petugasId,'jenis_transaksi'=>'penjualan_mobil','tanggal'=>now(),'subtotal'=>$price,'diskon'=>$discount,'total'=>$total,'status'=>'belum_bayar','catatan'=>$note])->save();
            $sale = new PenjualanMobil();
            $sale->forceFill(['kode_penjualan'=>$this->code('penjualan_mobil','kode_penjualan','SALE'),'id_transaksi'=>$tx->id_transaksi,'id_pelanggan'=>$customer->id_pelanggan,'id_mobil'=>$car->id_mobil,'id_petugas'=>$petugasId,'id_booking'=>$bookingId,'tanggal_penjualan'=>now(),'harga_mobil'=>$price,'diskon'=>$discount,'dp'=>$dp,'total_akhir'=>$total,'metode_pembelian'=>$input['metode_pembelian'],'status'=>'proses','catatan'=>$note])->save();
            $car->forceFill(['status'=>'dibooking'])->save();
            return $sale->fresh(['transaksi','mobil','booking','pelanggan']);
        });
    }
 public function createWithPromo(array $input,int $petugasId,?int $promoId=null):PenjualanMobil{$sale=$this->create($input,$petugasId);if($promoId!==null)app(PromoEngineService::class)->applyToTransaction($sale->transaksi,$promoId,'penjualan_mobil');return$sale->fresh(['transaksi','mobil','booking','pelanggan']);}
}
