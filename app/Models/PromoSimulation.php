<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class PromoSimulation extends Model { protected $table='promo_simulations'; protected $primaryKey='id_promo_simulation'; protected $guarded=['id_promo_simulation']; protected function casts():array{return ['input_payload'=>'array','result_payload'=>'array','discount_value'=>'decimal:2','max_discount'=>'decimal:2','min_transaction'=>'decimal:2','score'=>'integer'];} public function creator(){return $this->belongsTo(User::class,'created_by','id_user');} }
