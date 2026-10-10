<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TradeIn extends Model {
 protected $table='trade_in';protected $primaryKey='id_trade_in';protected $guarded=['id_trade_in','kode_trade_in'];protected function casts():array{return ['tanggal'=>'datetime','nilai_mobil_lama'=>'decimal:2','harga_mobil_dibeli'=>'decimal:2','diskon'=>'decimal:2','selisih_bayar'=>'decimal:2'];}
 public function pelanggan(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}public function pengajuan(){return $this->belongsTo(PengajuanMobil::class,'id_pengajuan','id_pengajuan');}public function inspeksi(){return $this->belongsTo(InspeksiMobil::class,'id_inspeksi','id_inspeksi');}public function mobilLamaMasuk(){return $this->belongsTo(Mobil::class,'id_mobil_lama_masuk','id_mobil');}public function mobilDibeli(){return $this->belongsTo(Mobil::class,'id_mobil_dibeli','id_mobil');}public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');}public function transaksi(){return $this->belongsTo(Transaksi::class,'id_transaksi','id_transaksi');}
}
