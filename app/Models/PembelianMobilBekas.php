<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PembelianMobilBekas extends Model
{
    protected $table = 'pembelian_mobil_bekas';
    protected $primaryKey = 'id_pembelian';
    protected $guarded = ['id_pembelian', 'kode_pembelian'];
    protected function casts(): array { return ['tanggal_pembelian'=>'datetime','harga_pembelian'=>'decimal:2']; }
    public function pengajuan(){ return $this->belongsTo(PengajuanMobil::class,'id_pengajuan','id_pengajuan'); }
    public function inspeksi(){ return $this->belongsTo(InspeksiMobil::class,'id_inspeksi','id_inspeksi'); }
    public function pelanggan(){ return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan'); }
    public function petugas(){ return $this->belongsTo(Petugas::class,'id_petugas','id_petugas'); }
    public function mobil(){ return $this->belongsTo(Mobil::class,'id_mobil','id_mobil'); }
    public function transaksi(){ return $this->belongsTo(Transaksi::class,'id_transaksi','id_transaksi'); }
}
