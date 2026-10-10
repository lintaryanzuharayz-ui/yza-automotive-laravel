<?php

namespace App\Console\Commands;

use App\Models\PromoMember;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class YzaPromoDemoCommand extends Command
{
    protected $signature = 'yza:promo-demo';
    protected $description = 'Membuat atau memperbarui katalog demo promo YZA Member';
    public function handle(): int
    {
        $start = Carbon::now('Asia/Jakarta')->startOfDay(); $end = $start->copy()->addYear();
        $items = [
            ['kode_promo' => 'YZA-SERVICE10', 'nama' => 'Promo Servis Member', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 10, 'berlaku_untuk' => 'servis', 'deskripsi' => 'Benefit katalog servis untuk YZA Member.'],
            ['kode_promo' => 'YZA-CARWASH20', 'nama' => 'Promo Car Wash Member', 'jenis_benefit' => 'nominal', 'nilai_benefit' => 20000, 'berlaku_untuk' => 'cuci_mobil', 'deskripsi' => 'Benefit katalog car wash untuk YZA Member.'],
            ['kode_promo' => 'YZA-PARTS5', 'nama' => 'Promo Spare Part Member', 'jenis_benefit' => 'persentase', 'nilai_benefit' => 5, 'berlaku_untuk' => 'penjualan_sparepart', 'deskripsi' => 'Benefit katalog spare part untuk YZA Member.'],
        ];
        foreach ($items as $item) PromoMember::updateOrCreate(['kode_promo' => $item['kode_promo']], $item + ['minimum_transaksi' => 0, 'maksimum_benefit' => null, 'mulai_at' => $start, 'selesai_at' => $end, 'syarat_ketentuan' => 'Promo tersedia sebagai benefit katalog YZA Member dan belum merupakan potongan transaksi.', 'status' => 'aktif']);
        $this->info('3 promo demo dibuat atau diperbarui secara idempotent.');
        return self::SUCCESS;
    }
}
