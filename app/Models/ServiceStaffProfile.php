<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceStaffProfile extends Model { protected $table='service_staff_profiles';protected $primaryKey='id_service_staff_profile';protected $guarded=['id_service_staff_profile'];protected function casts():array{return ['can_be_technician'=>'boolean','can_be_service_advisor'=>'boolean','is_bookable'=>'boolean','experience_years'=>'integer'];}public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');}public function specializations(){return $this->belongsToMany(ServiceSpecialization::class,'service_staff_specializations','id_service_staff_profile','id_service_specialization');}public function workingHours(){return $this->hasMany(TechnicianWorkingHour::class,'id_petugas','id_petugas');}}
