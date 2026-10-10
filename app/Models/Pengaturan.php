<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Pengaturan extends Model { public $timestamps=false;protected $table='pengaturan';protected $primaryKey='id_pengaturan';protected $guarded=['id_pengaturan','kunci_pengaturan'];protected function casts():array{return ['id_pengaturan'=>'integer','updated_at'=>'datetime'];} }
