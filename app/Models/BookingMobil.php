<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookingMobil extends Model {
 protected $table='booking_mobil';protected $primaryKey='id_booking';protected $guarded=['id_booking','id_pelanggan','status','id_petugas_konfirmasi'];
 protected function casts():array{return ['tanggal_booking'=>'datetime','tanggal_expired'=>'datetime'];}
 public function pelanggan(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}
 public function mobil(){return $this->belongsTo(Mobil::class,'id_mobil','id_mobil');}
 public function petugasKonfirmasi(){return $this->belongsTo(Petugas::class,'id_petugas_konfirmasi','id_petugas');}
 public function penjualan(){return $this->hasMany(PenjualanMobil::class,'id_booking','id_booking');}
}
