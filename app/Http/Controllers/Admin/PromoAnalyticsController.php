<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\PromoMember;
use App\Services\PromoAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PromoAnalyticsController extends Controller{public function index(Request $r,PromoAnalyticsService $analytics):View{$items=PromoMember::latest('id_promo')->paginate(12);$stats=$analytics->dashboard();return view('admin.promo-analytics.index',compact('items','stats','analytics'));}public function show(PromoMember $promo_member,Request $r,PromoAnalyticsService $analytics):View{$data=$analytics->forPromo($promo_member,$r->query('from'),$r->query('to'));return view('admin.promo-analytics.show',['item'=>$promo_member,'data'=>$data]);}}
