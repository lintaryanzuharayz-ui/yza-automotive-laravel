<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PromoRedemption extends Model { protected $table='promo_redemptions';protected $primaryKey='id_redemption';protected $guarded=['id_redemption'];protected function casts():array{return ['subtotal'=>'decimal:2','discount'=>'decimal:2','total_final'=>'decimal:2','used_at'=>'datetime'];}public function promo(){return $this->belongsTo(PromoMember::class,'id_promo','id_promo');}public function customer(){return $this->belongsTo(Pelanggan::class,'id_pelanggan','id_pelanggan');}public function transaction(){return $this->belongsTo(Transaksi::class,'id_transaksi','id_transaksi');}}
