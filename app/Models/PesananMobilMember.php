<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananMobilMember extends Model
{
    protected $table = 'pesanan_mobil_member';
    protected $primaryKey = 'id_pesanan';
    protected $guarded = ['id_pesanan', 'nomor_pesanan'];

    protected function casts(): array
    {
        return [
            'snapshot_harga' => 'decimal:2',
            'total_tagihan' => 'decimal:2',
            'syarat_disetujui' => 'boolean',
            'diputuskan_at' => 'datetime',
            'jadwal_pengambilan' => 'datetime',
            'jadwal_dikonfirmasi_at' => 'datetime',
            'diserahkan_at' => 'datetime',
        ];
    }

    public function pelanggan() { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function mobil() { return $this->belongsTo(Mobil::class, 'id_mobil', 'id_mobil'); }
    public function transaksi() { return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi'); }
    public function penjualan() { return $this->belongsTo(PenjualanMobil::class, 'id_penjualan', 'id_penjualan'); }
    public function pdi() { return $this->hasOne(PdiPesananMobil::class, 'id_pesanan', 'id_pesanan'); }
    public function serahTerima() { return $this->hasOne(SerahTerimaPesananMobil::class, 'id_pesanan', 'id_pesanan'); }
}
