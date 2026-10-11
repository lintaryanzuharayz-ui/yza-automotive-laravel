<?php
namespace App\Services;
use App\Models\{EventPembayaranGateway,PembayaranGateway,Transaksi};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use RuntimeException;
class MidtransPaymentService
{
 public const TYPES=['servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in'];
 public function createAttempt(Transaksi $transaction,$user):PembayaranGateway
 {
  $tx=Transaksi::lockForUpdate()->with('pelanggan.user')->findOrFail($transaction->id_transaksi);
  if(!$tx->id_pelanggan||!$tx->pelanggan||$tx->pelanggan->id_user!==$user->id_user||$user->roleName()!=='pelanggan')throw new RuntimeException('Transaksi bukan milik member ini.');
  if(!in_array($tx->jenis_transaksi,self::TYPES,true)||in_array($tx->status,['lunas','dibatalkan'],true))throw new RuntimeException('Transaksi tidak eligible untuk Midtrans.');
  app(GatewayPaymentGuard::class)->assertAttemptAllowed($tx);
  $paid=(float)$tx->pembayarans()->where('arah','masuk')->where('status','terverifikasi')->sum('jumlah');$remaining=(float)$tx->total-$paid;if($remaining<=0)throw new RuntimeException('Sisa tagihan kosong.');
  $active=PembayaranGateway::where('id_transaksi',$tx->id_transaksi)->whereIn('status_provider',['created','pending'])->latest()->first();if($active&&abs((float)$active->gross_amount-$remaining)<.01)return$active;
  $order='YZA-'.$tx->id_transaksi.'-'.strtoupper(Str::random(10));$attempt=new PembayaranGateway();$attempt->forceFill(['id_transaksi'=>$tx->id_transaksi,'provider'=>'midtrans','order_id'=>$order,'gross_amount'=>number_format($remaining,2,'.',''),'status_provider'=>'created','initiated_by'=>$user->id_user])->save();
  try{$data=app(MidtransClient::class)->createSnapTransaction(['transaction_details'=>['order_id'=>$order,'gross_amount'=>(int)round($remaining)],'customer_details'=>['first_name'=>$tx->pelanggan->nama_lengkap,'email'=>$tx->pelanggan->user?->email,'phone'=>$tx->pelanggan->no_hp],'callbacks'=>['finish'=>route('pelanggan.midtrans.returned',['order_id'=>$order])]]);$attempt->forceFill(['status_provider'=>'pending','snap_token'=>$data['token'],'redirect_url'=>$data['redirect_url'],'last_synced_at'=>now()])->save();return$attempt->fresh();}catch(\Throwable $e){$attempt->forceFill(['status_provider'=>'failure','last_synced_at'=>now()])->save();throw new RuntimeException('Midtrans Sandbox gagal membuat pembayaran.');}
 }
 public function processNotification(array $data,bool $verifySignature=true):PembayaranGateway
 {
  if($verifySignature&&!app(MidtransClient::class)->verifySignature($data))throw new RuntimeException('Signature Midtrans tidak valid.');
  $gateway=PembayaranGateway::where('order_id',$data['order_id']??'')->first();if(!$gateway)throw new RuntimeException('Order Midtrans tidak dikenal.');return$this->process($gateway,$data);
 }
 public function sync(PembayaranGateway $gateway,$user):PembayaranGateway
 {
  $tx=$gateway->transaksi()->with('pelanggan')->firstOrFail();abort_unless((int)$tx->id_pelanggan===(int)$user->pelanggan?->id_pelanggan,403);$data=app(MidtransClient::class)->getTransactionStatus($gateway->order_id);$data['order_id']=$gateway->order_id;$data['gross_amount']=$data['gross_amount']??$gateway->gross_amount;$data['status_code']=$data['status_code']??'200';$data['transaction_status']=$data['transaction_status']??'pending';return$this->process($gateway,$data,false);
 }
 public function syncForAdmin(PembayaranGateway $gateway):PembayaranGateway
 {
  $data=app(MidtransClient::class)->getTransactionStatus($gateway->order_id);$data['order_id']=$gateway->order_id;$data['gross_amount']=$data['gross_amount']??$gateway->gross_amount;$data['status_code']=$data['status_code']??'200';$data['transaction_status']=$data['transaction_status']??'pending';return $this->process($gateway,$data,false);
 }
 private function process(PembayaranGateway $gateway,array $data):PembayaranGateway
 {
  return DB::transaction(function()use($gateway,$data){$g=PembayaranGateway::lockForUpdate()->findOrFail($gateway->id_pembayaran_gateway);$status=(string)($data['transaction_status']??'pending');$code=(string)($data['status_code']??'');$providerId=$data['transaction_id']??null;$key='midtrans:'.$g->order_id.':'.$status.':'.$code.':'.($providerId??'none');if(!EventPembayaranGateway::where('event_key',$key)->exists()){$event=new EventPembayaranGateway();$event->forceFill(['id_pembayaran_gateway'=>$g->id_pembayaran_gateway,'event_key'=>$key,'transaction_status'=>$status,'status_code'=>$code,'fraud_status'=>$data['fraud_status']??null,'payment_type'=>$data['payment_type']??null,'provider_transaction_id'=>$providerId,'payload_hash'=>hash('sha256',json_encode([$g->order_id,$status,$code,$providerId])),'received_at'=>now()])->save();}$success=$status==='settlement'||($status==='capture'&&($data['fraud_status']??null)!=='deny');$final=['deny','cancel','expire','failure','refund','partial_refund','chargeback','partial_chargeback'];if(in_array($g->status_provider,['settlement','capture'],true)&&!$success)return$g->fresh();$tx=Transaksi::lockForUpdate()->findOrFail($g->id_transaksi);$paid=(float)$tx->pembayarans()->where('arah','masuk')->where('status','terverifikasi')->sum('jumlah');$expected=max(0,(float)$tx->total-$paid);$gross=(float)($data['gross_amount']??0);if(abs($gross-(float)$g->gross_amount)>=.01||($success&&abs($gross-$expected)>=.01)){$g->forceFill(['status_provider'=>'requires_review','last_synced_at'=>now()])->save();return$g->fresh();}$trustedTime=null;foreach(['settlement_time','transaction_time'] as $field){if(!empty($data[$field])){try{$trustedTime=Carbon::parse($data[$field]);break;}catch(\Throwable $e){}}}$processedAt=$trustedTime?:now();$g->forceFill(['status_provider'=>$success?($status==='capture'?'capture':'settlement'):(in_array($status,$final,true)?$status:'pending'),'payment_type'=>$data['payment_type']??$g->payment_type,'provider_transaction_id'=>$providerId,'last_synced_at'=>now()])->save();if($success&&$g->id_pembayaran===null){$p=app(PaymentService::class)->create($tx->id_transaksi,$tx->id_petugas,(string)$g->gross_amount,'transfer',null,'Midtrans Sandbox '.$g->order_id);$p=app(PaymentService::class)->verify($p->id_pembayaran,$tx->id_petugas,$trustedTime);$g->forceFill(['id_pembayaran'=>$p->id_pembayaran,'processed_at'=>$processedAt])->save();}return$g->fresh();});
 }
}
