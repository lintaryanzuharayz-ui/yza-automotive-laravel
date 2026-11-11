<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TechnicianWorkingHour extends Model { protected $table='technician_working_hours';protected $primaryKey='id_technician_working_hour';protected $guarded=['id_technician_working_hour'];protected function casts():array{return ['is_working'=>'boolean','day_of_week'=>'integer'];}public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');} }
