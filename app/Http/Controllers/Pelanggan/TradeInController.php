<?php
namespace App\Http\Controllers\Pelanggan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTradeInRequest;
use App\Models\{Mobil,PengajuanMobil,TradeIn};
use App\Services\{BookingMobilService,TradeInService};
use Illuminate\Http\Request;
use Throwable;
class TradeInController extends Controller {
 private function customer(Request $r):int{return (int)$r->user()->pelanggan()->value('id_pelanggan');}
 public function index(Request $r){$rows=TradeIn::with(['pengajuan','mobilDibeli','transaksi'])->where('id_pelanggan',$this->customer($r))->latest()->paginate(15);return view('pelanggan.trade-in.index',compact('rows'));}
 public function create(Request $r){$customerModel=$r->user()->pelanggan;if(!$customerModel?->membership?->isActive()){if($r->filled('mobil'))$r->session()->put('trade_in.intent',['mobil'=>$r->integer('mobil')]);return redirect()->route('pelanggan.membership.activation');}$customer=$this->customer($r);$submission=PengajuanMobil::with('inspeksi')->where('id_pelanggan',$customer)->where('status','penawaran_diberikan')->findOrFail((int)$r->query('pengajuan'));$inspection=$submission->inspeksi()->firstOrFail();$cars=Mobil::where('status','tersedia')->where('harga_jual','>',0)->orderBy('merk')->get();$promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','trade_in'])->get();$selectedCarId=(int)($r->query('mobil')?:$r->session()->get('trade_in.target_mobil',0));return view('pelanggan.trade-in.create',compact('submission','inspection','cars','promos','selectedCarId'));}
 public function store(StoreTradeInRequest $req,TradeInService $svc){try{$d=$req->validated();$actor=(int)app(BookingMobilService::class)->actorPetugasId($req->user()->id_user);$t=$svc->createWithPromo($this->customer($req),(int)$d['id_pengajuan'],(int)$d['id_inspeksi'],(int)$d['id_mobil_dibeli'],$actor,'0',$d['catatan']??null,$req->integer('promo_id')?:null);return redirect()->route('pelanggan.trade-in.show',$t)->with('success','Trade-In berhasil dibuat.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function show(Request $r,TradeIn $trade_in){abort_unless((int)$trade_in->id_pelanggan===$this->customer($r),403);$trade_in->load(['pengajuan','mobilDibeli','transaksi.pembayarans']);return view('pelanggan.trade-in.show',['trade'=>$trade_in]);}
}
