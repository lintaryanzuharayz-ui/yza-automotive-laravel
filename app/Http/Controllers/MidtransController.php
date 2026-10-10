<?php
namespace App\Http\Controllers;
use App\Models\{PembayaranGateway,Transaksi};
use App\Services\MidtransPaymentService;
use Illuminate\Http\Request;
use Throwable;
class MidtransController extends Controller
{
 public function create(Request $request,Transaksi $transaksi,MidtransPaymentService $service){try{$attempt=$service->createAttempt($transaksi,$request->user());return redirect()->away($attempt->redirect_url);}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function returned(){return view('payments.midtrans-return');}
 public function sync(Request $request,PembayaranGateway $gateway,MidtransPaymentService $service){try{$service->sync($gateway,$request->user());return back()->with('status','Status pembayaran Midtrans sudah diperiksa dari server.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function notification(Request $request,MidtransPaymentService $service,\App\Services\MembershipPaymentService $membershipService){try{$payload=$request->all();if(str_starts_with((string)($payload['order_id']??''),'YZA-MBR-'))$membershipService->processNotification($payload);else$service->processNotification($payload);return response()->json(['ok'=>true]);}catch(Throwable $e){return response()->json(['ok'=>false,'message'=>'Notification ditolak.'],400);}}
}
