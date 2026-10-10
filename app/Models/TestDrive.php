<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TestDrive extends Model {
 protected $table='test_drive';protected $primaryKey='id_test_drive';protected $guarded=['id_test_drive','id_pelanggan','status','id_petugas'];
 protected function casts():array{return ['tanggal'=>'date','created_at'=>'datetime','updated_at'=>'datetime'];}
 public function pelanggan(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}
 public function mobil(){return $this->belongsTo(Mobil::class,'id_mobil','id_mobil');}
 public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');}
}
