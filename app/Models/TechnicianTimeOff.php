<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TechnicianTimeOff extends Model { protected $table='technician_time_off';protected $primaryKey='id_technician_time_off';protected $guarded=['id_technician_time_off'];protected function casts():array{return ['date'=>'date'];}public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');} }
