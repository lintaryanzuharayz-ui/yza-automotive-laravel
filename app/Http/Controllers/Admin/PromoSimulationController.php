<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PromoSimulation;
use App\Services\PromoSimulationService;
use Illuminate\Http\Request;
class PromoSimulationController extends Controller
{
 public function index(){return view('admin.promo-simulations.index',['items'=>PromoSimulation::latest()->paginate(15)]);}
 public function create(){return view('admin.promo-simulations.create');}
 public function store(Request $r,PromoSimulationService $svc){$d=$r->validate(['scope'=>'required|string|max:40','discount_type'=>'required|in:percentage,nominal','discount_value'=>'required|numeric|min:0','max_discount'=>'nullable|numeric|min:0','min_transaction'=>'nullable|numeric|min:0','average_price'=>'required|numeric|min:0','estimated_cost'=>'nullable|numeric|min:0','expected_transactions'=>'required|integer|min:1','expected_sales_uplift'=>'nullable|numeric|min:0','quota'=>'nullable|integer|min:1']);$result=$svc->simulate($d);$id='SPK-'.now()->format('YmdHis').'-'.strtoupper(bin2hex(random_bytes(3)));$item=PromoSimulation::create(['simulation_id'=>$id,'created_by'=>$r->user()->id_user,'scope'=>$d['scope'],'discount_type'=>$d['discount_type'],'discount_value'=>$d['discount_value'],'max_discount'=>$d['max_discount']??null,'min_transaction'=>$d['min_transaction']??0,'input_payload'=>$d,'result_payload'=>$result,'score'=>$result['final_score'],'status'=>$result['status'],'recommendation'=>implode(' ',$result['recommendations'])]);return redirect()->route('admin.promo-simulations.show',$item);}
 public function show(PromoSimulation $promoSimulation){$promo=\App\Models\PromoMember::where('approved_spk_simulation_id',$promoSimulation->simulation_id)->first();return view('admin.promo-simulations.show',['item'=>$promoSimulation,'promo'=>$promo]);}
}
