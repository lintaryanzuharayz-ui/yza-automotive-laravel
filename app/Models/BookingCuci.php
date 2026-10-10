<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookingCuci extends Model {
 protected $table='booking_cuci';protected $primaryKey='id_booking_cuci';protected $guarded=['id_booking_cuci','id_pelanggan','id_petugas','id_transaksi','status','harga'];
 protected function casts():array{return ['tanggal'=>'date','harga'=>'decimal:2','created_at'=>'datetime','updated_at'=>'datetime'];}
 public function pelanggan(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}
 public function kendaraan(){return $this->belongsTo(KendaraanPelanggan::class,'id_kendaraan_pelanggan','id_kendaraan_pelanggan');}
 public function paket(){return $this->belongsTo(PaketCuci::class,'id_paket','id_paket');}
 public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');}
 public function transaksi(){return $this->belongsTo(Transaksi::class,'id_transaksi','id_transaksi');}
}
