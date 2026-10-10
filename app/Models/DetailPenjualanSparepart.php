<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DetailPenjualanSparepart extends Model {
 public $timestamps=false; protected $table='detail_penjualan_sparepart'; protected $primaryKey='id_detail';
 protected $guarded=['id_detail','harga_satuan','subtotal'];
 protected function casts():array{return ['jumlah'=>'integer','harga_satuan'=>'decimal:2','subtotal'=>'decimal:2'];}
 public function penjualan(){return $this->belongsTo(PenjualanSparepart::class,'id_penjualan_sparepart','id_penjualan_sparepart');}
 public function sparepart(){return $this->belongsTo(Sparepart::class,'id_sparepart','id_sparepart');}
}
