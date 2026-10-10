<?php
namespace App\Http\Controllers;
use App\Http\Requests\{StoreOutgoingPaymentRequest,PaymentActionRequest};
use App\Models\Transaksi;
use App\Services\OutgoingPaymentService;
use Illuminate\Http\Request;
use Throwable;
class OutgoingPaymentController extends Controller {
 public function create(Transaksi $transaksi){abort_unless($transaksi->jenis_transaksi==='pembelian_mobil',404);return view('payments.outgoing-create',compact('transaksi'));}
 public function store(StoreOutgoingPaymentRequest $r,Transaksi $transaksi,OutgoingPaymentService $svc){try{$d=$r->validated();$proof=$r->file('bukti_transfer')?->store('pembayaran','public');$svc->create($transaksi->id_transaksi,(int)($r->user()->petugas?->id_petugas ?? 0),(string)$d['jumlah'],$d['metode'],$proof,$d['catatan']??null);return back()->with('success','Pembayaran keluar tersimpan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function verify(PaymentActionRequest $r,$pembayaran,OutgoingPaymentService $svc){try{$svc->verify((int)$pembayaran,(int)($r->user()->petugas?->id_petugas ?? 0));return back()->with('success','Pembayaran keluar diverifikasi.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function reject(PaymentActionRequest $r,$pembayaran,OutgoingPaymentService $svc){try{$svc->reject((int)$pembayaran,(int)($r->user()->petugas?->id_petugas ?? 0));return back()->with('success','Pembayaran keluar ditolak.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
