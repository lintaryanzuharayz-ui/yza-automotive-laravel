<?php
namespace App\Http\Controllers\Pelanggan;
use App\Http\Controllers\Controller;
use App\Models\{PromoMember,Pelanggan};
use App\Services\PromoEngineService;
use Illuminate\Http\Request;
class PromoPreviewController extends Controller{public function index(Request $r,PromoEngineService $engine){$customer=$r->user()->pelanggan?:new Pelanggan(['id_pelanggan'=>0]);$scope=(string)$r->query('scope','semua');$subtotal=(float)$r->query('subtotal',0);$items=PromoMember::visible()->where(function($q)use($scope){$q->where('berlaku_untuk','semua')->orWhere('berlaku_untuk',$scope);})->get()->map(function(PromoMember $promo)use($engine,$subtotal,$scope,$customer){try{$result=$engine->calculate($promo,$subtotal,$scope,$customer);return['promo'=>$promo,'eligible'=>true,'result'=>$result,'reason'=>null];}catch(\Throwable $e){return['promo'=>$promo,'eligible'=>false,'result'=>null,'reason'=>$e->getMessage()];}})->values();return response()->json($items);}
 public function preview(Request $r,PromoEngineService $engine){$data=$r->validate(['promo_id'=>'required|integer','scope'=>'required|string','subtotal'=>'required|numeric|min:0']);$customer=$r->user()->pelanggan?:new Pelanggan(['id_pelanggan'=>0]);$promo=PromoMember::findOrFail($data['promo_id']);return response()->json($engine->calculate($promo,(float)$data['subtotal'],$data['scope'],$customer));}}
