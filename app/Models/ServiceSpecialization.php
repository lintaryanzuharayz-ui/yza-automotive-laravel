<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ServiceSpecialization extends Model { protected $table='service_specializations';protected $primaryKey='id_service_specialization';protected $guarded=['id_service_specialization'];protected function casts():array{return ['is_active'=>'boolean'];} }
