<?php
namespace App\Http\Controllers;
use App\Models\TradeIn;
use Illuminate\Http\Request;
class TradeInInvoiceController extends Controller {
 public function show(Request $r,TradeIn $trade_in){$trade_in->load(['pelanggan','pengajuan','mobilDibeli','transaksi.pembayarans']);if($r->user()->roleName()==='pelanggan')abort_unless((int)$trade_in->id_pelanggan===(int)$r->user()->pelanggan?->id_pelanggan,403);return view('trade-in.invoice',['trade'=>$trade_in]);}
}
