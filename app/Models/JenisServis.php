<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class JenisServis extends Model { protected $table='jenis_servis';protected $primaryKey='id_jenis_servis';protected $guarded=['id_jenis_servis'];protected function casts():array{return ['estimasi_harga'=>'decimal:2','created_at'=>'datetime','updated_at'=>'datetime'];} public function bookingServis(): HasMany{return $this->hasMany(BookingServis::class,'id_jenis_servis','id_jenis_servis');} public function detailServis(): HasMany{return $this->hasMany(DetailServis::class,'id_jenis_servis','id_jenis_servis');} }
