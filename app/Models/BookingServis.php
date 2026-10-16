<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookingServis extends Model {
 protected $table='booking_servis';protected $primaryKey='id_booking_servis';protected $guarded=['id_booking_servis','id_pelanggan','status'];
 protected function casts():array{return ['tanggal'=>'date','created_at'=>'datetime','updated_at'=>'datetime'];}
 public function pelanggan(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}
 public function kendaraan(){return $this->belongsTo(KendaraanPelanggan::class,'id_kendaraan_pelanggan','id_kendaraan_pelanggan');}
 public function jenisServis(){return $this->belongsTo(JenisServis::class,'id_jenis_servis','id_jenis_servis');}
 public function servis(){return $this->hasOne(Servis::class,'id_booking_servis','id_booking_servis');}
 public function preferredPetugas(){return $this->belongsTo(Petugas::class,'id_preferred_petugas','id_petugas');}
}
