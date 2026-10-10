<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DetailServis extends Model { public $timestamps=false; protected $table='detail_servis'; protected $primaryKey='id_detail_servis'; protected $guarded=['id_detail_servis','id_servis']; protected function casts():array{return ['biaya'=>'decimal:2'];} public function servis(){return $this->belongsTo(Servis::class,'id_servis','id_servis');} public function jenisServis(){return $this->belongsTo(JenisServis::class,'id_jenis_servis','id_jenis_servis');} }
