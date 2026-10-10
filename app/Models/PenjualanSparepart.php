<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PenjualanSparepart extends Model {
 protected $table='penjualan_sparepart'; protected $primaryKey='id_penjualan_sparepart';
 protected $guarded=['id_penjualan_sparepart','kode_penjualan','id_transaksi','id_pelanggan','id_petugas','status','subtotal','diskon','total'];
 protected function casts():array{return ['tanggal'=>'datetime','subtotal'=>'decimal:2','diskon'=>'decimal:2','total'=>'decimal:2'];}
 public function pelanggan(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}
 public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');}
 public function transaksi(){return $this->belongsTo(Transaksi::class,'id_transaksi','id_transaksi');}
 public function details(){return $this->hasMany(DetailPenjualanSparepart::class,'id_penjualan_sparepart','id_penjualan_sparepart');}
}
