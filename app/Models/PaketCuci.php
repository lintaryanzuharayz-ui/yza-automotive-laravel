<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PaketCuci extends Model { protected $table='paket_cuci';protected $primaryKey='id_paket';protected $guarded=['id_paket'];protected function casts():array{return ['harga'=>'decimal:2','estimasi_menit'=>'integer','created_at'=>'datetime','updated_at'=>'datetime'];} public function bookingCuci():HasMany{return $this->hasMany(BookingCuci::class,'id_paket','id_paket');} }
