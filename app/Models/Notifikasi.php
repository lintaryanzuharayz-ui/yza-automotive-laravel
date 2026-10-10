<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notifikasi extends Model {
    public $timestamps=false; protected $table='notifikasi'; protected $primaryKey='id_notifikasi'; protected $guarded=['id_notifikasi'];
    protected function casts(): array { return ['is_read'=>'boolean','created_at'=>'datetime']; }
}
