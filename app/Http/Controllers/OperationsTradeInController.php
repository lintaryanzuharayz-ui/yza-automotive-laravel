<?php
namespace App\Http\Controllers;
use App\Models\{InspeksiMobil,PengajuanMobil,TradeIn};
use App\Services\TradeInService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;
class OperationsTradeInController extends Controller {
 public function index(Request $r):View
 {
  $search=trim((string)$r->input('q',''));$status=(string)$r->input('status','');$from=(string)$r->input('from','');$to=(string)$r->input('to','');$period=in_array((string)$r->input('period','month'),['month','all'],true)?(string)$r->input('period','month'):'month';
  $statuses=['diajukan','menunggu_inspeksi','dalam_inspeksi','penawaran_diberikan','disetujui','ditolak','selesai'];$perPage=in_array((int)$r->input('perPage',10),[10,20,50],true)?(int)$r->input('perPage',10):10;
  $query=PengajuanMobil::with(['pelanggan','kendaraan','inspeksi'])->when($status!=='',fn($q)=>$q->where('status',$status))->when($from!=='',fn($q)=>$q->whereDate('created_at','>=',$from))->when($to!=='',fn($q)=>$q->whereDate('created_at','<=',$to))->when($search!=='',function($q)use($search){$term="%{$search}%";$q->where(fn($w)=>$w->where('kode_pengajuan','like',$term)->orWhere('merk','like',$term)->orWhere('model','like',$term)->orWhere('nomor_polisi','like',$term)->orWhereHas('pelanggan',fn($p)=>$p->where('nama_lengkap','like',$term)));})->latest('created_at');
  $rows=$query->paginate($perPage)->withQueryString();$today=now(config('app.timezone'))->toDateString();$monthStart=now(config('app.timezone'))->startOfMonth()->toDateString();
  $submissionBase=PengajuanMobil::query();$total=$submissionBase->count();$waiting=(clone $submissionBase)->whereIn('status',['diajukan','menunggu_inspeksi'])->count();$inspectionScheduled=(clone $submissionBase)->where('status','dalam_inspeksi')->whereHas('inspeksi',fn($q)=>$q->whereNotNull('tanggal_inspeksi'))->count();$offers=(clone $submissionBase)->where('status','penawaran_diberikan')->count();$completed=TradeIn::where('status','selesai')->count();
  $brandQuery=(clone $submissionBase)->when($period==='month',fn($q)=>$q->whereDate('created_at','>=',$monthStart));$brands=$brandQuery->selectRaw('LOWER(TRIM(merk)) as brand, COUNT(*) as total')->whereNotNull('merk')->where('merk','<>','')->groupBy('brand')->orderByDesc('total')->get();$brandTotal=max(1,(int)$brands->sum('total'));
  $inspectionsToday=InspeksiMobil::whereDate('tanggal_inspeksi',$today)->count();$avgOffer=InspeksiMobil::whereNotNull('harga_penawaran')->avg('harga_penawaran');$tradeTotal=TradeIn::count();$conversion=$tradeTotal>0?round((TradeIn::where('status','selesai')->count()/$tradeTotal)*100):null;
  return view('operations.trade-in.index',['rows'=>$rows,'search'=>$search,'status'=>$status,'from'=>$from,'to'=>$to,'period'=>$period,'perPage'=>$perPage,'statuses'=>$statuses,'tradeStats'=>['total'=>$total,'waiting'=>$waiting,'inspection'=>$inspectionScheduled,'offers'=>$offers,'completed'=>$completed],'brands'=>$brands,'brandTotal'=>$brandTotal,'operational'=>['today'=>PengajuanMobil::whereDate('created_at',$today)->count(),'inspections'=>$inspectionsToday,'averageOffer'=>$avgOffer,'conversion'=>$conversion]]);
 }
 public function show(TradeIn $trade_in){$trade_in->load(['pelanggan','pengajuan','mobilDibeli','transaksi']);return view('operations.trade-in.show',['trade'=>$trade_in]);}
 public function finalize(TradeIn $trade_in,TradeInService $svc){try{$svc->finalize($trade_in->id_trade_in);return back()->with('success','Trade-In difinalisasi.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function cancel(TradeIn $trade_in,TradeInService $svc){try{$svc->cancel($trade_in->id_trade_in);return back()->with('success','Trade-In dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
