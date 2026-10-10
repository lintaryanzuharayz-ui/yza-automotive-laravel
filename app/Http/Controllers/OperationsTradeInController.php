<?php
namespace App\Http\Controllers;
use App\Models\TradeIn;
use App\Services\TradeInService;
use Throwable;
class OperationsTradeInController extends Controller {
 public function index(){return view('operations.trade-in.index',['rows'=>TradeIn::with(['pelanggan','pengajuan','mobilDibeli','transaksi'])->latest()->paginate(20)]);}
 public function show(TradeIn $trade_in){$trade_in->load(['pelanggan','pengajuan','mobilDibeli','transaksi']);return view('operations.trade-in.show',['trade'=>$trade_in]);}
 public function finalize(TradeIn $trade_in,TradeInService $svc){try{$svc->finalize($trade_in->id_trade_in);return back()->with('success','Trade-In difinalisasi.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function cancel(TradeIn $trade_in,TradeInService $svc){try{$svc->cancel($trade_in->id_trade_in);return back()->with('success','Trade-In dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
