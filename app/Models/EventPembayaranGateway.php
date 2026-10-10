<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EventPembayaranGateway extends Model { protected $table='event_pembayaran_gateway'; protected $primaryKey='id_event_gateway'; protected $guarded=[]; protected function casts():array{return ['received_at'=>'datetime'];} public function gateway(){return $this->belongsTo(PembayaranGateway::class,'id_pembayaran_gateway','id_pembayaran_gateway');} }
