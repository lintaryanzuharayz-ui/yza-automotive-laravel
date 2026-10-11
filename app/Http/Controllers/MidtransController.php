<?php
namespace App\Http\Controllers;
use App\Models\{PembayaranGateway,Transaksi};
use App\Services\MidtransPaymentService;
use Illuminate\Http\Request;
use Throwable;
class MidtransController extends Controller
{
 public function create(Request $request,Transaksi $transaksi,MidtransPaymentService $service){try{$attempt=$service->createAttempt($transaksi,$request->user());return redirect()->away($attempt->redirect_url);}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function returned(Request $request,MidtransPaymentService $service){$orderId=trim((string)$request->query('order_id'));if($orderId==='')return redirect()->route('pelanggan.pesanan-mobil.index')->withErrors('Referensi pembayaran Midtrans tidak ditemukan.');$gateway=\App\Models\PembayaranGateway::where('order_id',$orderId)->with('transaksi.pesananMobilMember')->first();if(!$gateway||!$gateway->transaksi?->pesananMobilMember)return redirect()->route('pelanggan.pesanan-mobil.index')->withErrors('Pesanan pembayaran tidak ditemukan.');$order=$gateway->transaksi->pesananMobilMember;abort_unless((int)$gateway->transaksi->id_pelanggan===(int)$request->user()->pelanggan?->id_pelanggan,403);try{$verified=$service->sync($gateway,$request->user());$message=in_array($verified->status_provider,['settlement','capture'],true)&&$verified->id_pembayaran?'Pembayaran berhasil diverifikasi.':'Status pembayaran sedang diproses oleh Midtrans.';return redirect()->route('pelanggan.pesanan-mobil.show',$order)->with('status',$message);}catch(\Throwable $e){return redirect()->route('pelanggan.pesanan-mobil.show',$order)->withErrors('Pembayaran belum dapat diverifikasi. Silakan coba lagi.');}}
 public function sync(Request $request,PembayaranGateway $gateway,MidtransPaymentService $service){try{$service->sync($gateway,$request->user());return back()->with('status','Status pembayaran Midtrans sudah diperiksa dari server.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function notification(Request $request,MidtransPaymentService $service,\App\Services\MembershipPaymentService $membershipService){try{$payload=$request->all();if(str_starts_with((string)($payload['order_id']??''),'YZA-MBR-'))$membershipService->processNotification($payload);else$service->processNotification($payload);return response()->json(['ok'=>true]);}catch(Throwable $e){return response()->json(['ok'=>false,'message'=>'Notification ditolak.'],400);}}
}
