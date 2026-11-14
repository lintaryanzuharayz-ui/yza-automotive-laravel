<?php
namespace App\Services;
use App\Models\{PembayaranGateway,Transaksi};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
class MidtransPaymentService
{
 public const TYPES=['servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in'];
 public function createAttempt(Transaksi $transaction,$user):PembayaranGateway
 {
  return DB::transaction(function () use ($transaction, $user) {
  $tx=Transaksi::lockForUpdate()->with('pelanggan.user')->findOrFail($transaction->id_transaksi);
  if(!$tx->id_pelanggan||!$tx->pelanggan||$tx->pelanggan->id_user!==$user->id_user||$user->roleName()!=='pelanggan')throw new RuntimeException('Transaksi bukan milik member ini.');
  if(!in_array($tx->jenis_transaksi,self::TYPES,true)||in_array($tx->status,['lunas','dibatalkan'],true))throw new RuntimeException('Transaksi tidak eligible untuk Midtrans.');
  app(GatewayPaymentGuard::class)->assertAttemptAllowed($tx);
  $paid=(float)$tx->pembayarans()->where('arah','masuk')->where('status','terverifikasi')->sum('jumlah');$remaining=(float)$tx->total-$paid;if($remaining<=0)throw new RuntimeException('Sisa tagihan kosong.');
  $active=PembayaranGateway::where('id_transaksi',$tx->id_transaksi)->whereIn('status_provider',['created','pending'])->latest()->first();if($active&&abs((float)$active->gross_amount-$remaining)<.01){$activeFinish=route('pelanggan.midtrans.returned',['order_id'=>$active->order_id]);if(!Schema::hasColumn('pembayaran_gateway','finish_url')||(string)$active->finish_url===$activeFinish)return$active;}
  $order='YZA-'.$tx->id_transaksi.'-'.strtoupper(Str::random(10));$finishUrl=route('pelanggan.midtrans.returned',['order_id'=>$order]);$attempt=new PembayaranGateway();$gatewayFields=['id_transaksi'=>$tx->id_transaksi,'provider'=>'midtrans','order_id'=>$order,'gross_amount'=>number_format($remaining,2,'.',''),'status_provider'=>'created','initiated_by'=>$user->id_user];if(Schema::hasColumn('pembayaran_gateway','finish_url'))$gatewayFields['finish_url']=$finishUrl;$attempt->forceFill($gatewayFields)->save();
  try{$data=app(MidtransClient::class)->createSnapTransaction(['transaction_details'=>['order_id'=>$order,'gross_amount'=>(int)round($remaining)],'customer_details'=>['first_name'=>$tx->pelanggan->nama_lengkap,'email'=>$tx->pelanggan->user?->email,'phone'=>$tx->pelanggan->no_hp],'callbacks'=>['finish'=>$finishUrl]]);$attempt->forceFill(['status_provider'=>'pending','snap_token'=>$data['token'],'redirect_url'=>$data['redirect_url'],'last_synced_at'=>now()])->save();return$attempt->fresh();}catch(\Throwable $e){$attempt->forceFill(['status_provider'=>'failure','last_synced_at'=>now()])->save();throw new RuntimeException('Midtrans Sandbox gagal membuat pembayaran.');}
  });
 }
 public function processNotification(array $data):PembayaranGateway
 {
  return app(MidtransGatewayProcessor::class)->notification($data, false);
 }
 public function sync(PembayaranGateway $gateway,$user):PembayaranGateway
 {
  $tx=$gateway->transaksi()->with('pelanggan')->firstOrFail();
  abort_unless(!$gateway->id_membership && $user->roleName()==='pelanggan' && (int)$tx->id_pelanggan===(int)$user->pelanggan?->id_pelanggan,403);
  return app(MidtransGatewayProcessor::class)->inquiry($gateway);
 }
 public function syncForAdmin(PembayaranGateway $gateway):PembayaranGateway
 {
  if ($gateway->id_membership) throw new RuntimeException('Gunakan rekonsiliasi membership yang sesuai.');
  return app(MidtransGatewayProcessor::class)->inquiry($gateway);
 }
}
