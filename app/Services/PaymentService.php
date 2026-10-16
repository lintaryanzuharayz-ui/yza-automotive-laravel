<?php
namespace App\Services;
use App\Models\{BookingCuci,BookingMobil,BookingServis,Mobil,Pembayaran,PenjualanMobil,PenjualanSparepart,PesananMobilMember,Servis,Transaksi};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\CarbonInterface;
use RuntimeException;
class PaymentService{
 private function verificationFields(int $actorId,string $state,?CarbonInterface $verifiedAt=null):array{return Schema::hasColumn('pembayaran','verified_at')?['verified_by'=>$state==='terverifikasi'&&$actorId>0&&DB::table('petugas')->where('id_petugas',$actorId)->exists()?$actorId:null,'verified_at'=>$state==='terverifikasi'?($verifiedAt?:now()):null]:[];}
 private function code():string{do{$c='PAY-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4)));}while(Pembayaran::where('kode_pembayaran',$c)->exists());return$c;}
 private function paid(Transaksi $tx):string{return(string)DB::selectOne("SELECT CAST(COALESCE(SUM(jumlah),0) AS DECIMAL(15,2)) AS value FROM pembayaran WHERE id_transaksi=? AND arah='masuk' AND status='terverifikasi'",[$tx->id_transaksi])->value;}
 private function status(string $total,string $paid):string{if((float)$total<=0)return'lunas';if((float)$paid<=0)return'belum_bayar';return(float)$paid<(float)$total?'dp':'lunas';}
 private function sync(Transaksi $tx,string $status):void{if($status!=='lunas')return;if($tx->jenis_transaksi==='servis'){$s=Servis::lockForUpdate()->where('id_transaksi',$tx->id_transaksi)->first();if(!$s)return;if($s->status!=='selesai')$s->forceFill(['status'=>'selesai','tanggal_selesai'=>now()])->save();if($s->id_booking_servis)BookingServis::lockForUpdate()->whereKey($s->id_booking_servis)->where('status','diproses')->update(['status'=>'selesai']);app(ServiceNotificationService::class)->forService($s->fresh('pelanggan'),'Servis selesai','Pembayaran diterima. Servis '.$s->kode_servis.' telah selesai dan kendaraan dapat diambil sesuai konfirmasi bengkel.');}elseif($tx->jenis_transaksi==='cuci_mobil')BookingCuci::lockForUpdate()->where('id_transaksi',$tx->id_transaksi)->where('status','proses')->update(['status'=>'selesai']);elseif($tx->jenis_transaksi==='penjualan_sparepart')PenjualanSparepart::lockForUpdate()->where('id_transaksi',$tx->id_transaksi)->first();elseif($tx->jenis_transaksi==='penjualan_mobil'){$sale=PenjualanMobil::lockForUpdate()->where('id_transaksi',$tx->id_transaksi)->first();if(!$sale)return;$memberOrder=PesananMobilMember::lockForUpdate()->where('id_penjualan',$sale->id_penjualan)->first();if($memberOrder){$memberOrder->forceFill(['status'=>'lunas'])->save();app(ServiceNotificationService::class)->forMemberVehicleOrder($memberOrder->fresh('pelanggan'),'Pembayaran terverifikasi','Pembayaran kendaraan telah terverifikasi.');return;}$car=Mobil::lockForUpdate()->find($sale->id_mobil);$inventory= $car ? app(VehicleInventoryService::class)->find($car) : null;if(!$car||(!$inventory&&$car->status!=='dibooking'))throw new RuntimeException('Unit penjualan tidak lagi berstatus dibooking.');$sale->forceFill(['status'=>'selesai'])->save();if($inventory){app(VehicleInventoryService::class)->recordSale($car,1,'penjualan_mobil',$sale->id_penjualan,'Penjualan mobil lunas.');}else{$car->forceFill(['status'=>'terjual'])->save();}if($sale->id_booking){$booking=BookingMobil::lockForUpdate()->find($sale->id_booking);if($booking&&$booking->status==='dikonfirmasi')$booking->forceFill(['status'=>'selesai'])->save();}}app(PromoEngineService::class)->finalizeRedemption($tx);app(YzaPointService::class)->awardIfEligible($tx);}
 private function notifyVerified(Transaksi $tx):void{if($tx->jenis_transaksi!=='servis')return;$service=Servis::where('id_transaksi',$tx->id_transaksi)->first();if($service)app(ServiceNotificationService::class)->forService($service->fresh('pelanggan'),'Pembayaran diverifikasi','Pembayaran untuk '.$service->kode_servis.' telah diverifikasi oleh YZA Automotive.');}
 private function assertSupported(Transaksi $tx):void{if(!in_array($tx->jenis_transaksi,['servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in'],true))throw new RuntimeException('Jenis transaksi belum didukung payment manual.');}
 private function assertCarSaleStage(Transaksi $tx):void{if($tx->jenis_transaksi!=='penjualan_mobil')return;$order=PesananMobilMember::where('id_transaksi',$tx->id_transaksi)->first();if($order&&!in_array($order->status,['menunggu_pembayaran','lunas'],true))throw new RuntimeException('Pembayaran belum dibuka. Pesanan harus disetujui admin terlebih dahulu.');}
 public function create(int $transactionId,int $actorId,string $amount,string $method,?string $proof,?string $note):Pembayaran{return DB::transaction(function()use($transactionId,$actorId,$amount,$method,$proof,$note){$tx=Transaksi::lockForUpdate()->findOrFail($transactionId);$this->assertSupported($tx);if(in_array($tx->status,['dibatalkan','lunas'],true))throw new RuntimeException('Transaksi tidak tersedia untuk pembayaran baru.');if((float)$amount<=0)throw new RuntimeException('Jumlah pembayaran harus lebih dari 0.');$paid=$this->paid($tx);$state=$method==='tunai'?'terverifikasi':'menunggu';if($state==='terverifikasi'&&(float)$paid+(float)$amount>(float)$tx->total)throw new RuntimeException('Pembayaran melebihi total transaksi.');$p=new Pembayaran();$p->forceFill(array_merge(['kode_pembayaran'=>$this->code(),'id_transaksi'=>$tx->id_transaksi,'tanggal'=>now(),'arah'=>'masuk','metode'=>$method,'jumlah'=>$amount,'bukti_transfer'=>$proof,'status'=>$state,'catatan'=>$note],$this->verificationFields($actorId,$state)))->save();if($state==='terverifikasi'){$new=$this->status((string)$tx->total,(string)((float)$paid+(float)$amount));$tx->forceFill(['status'=>$new])->save();$this->notifyVerified($tx);$this->sync($tx,$new);}return$p;});}
 public function verify(int $paymentId,int $actorId,?CarbonInterface $verifiedAt=null):Pembayaran{return DB::transaction(function()use($paymentId,$actorId,$verifiedAt){$p=Pembayaran::lockForUpdate()->findOrFail($paymentId);if($p->status!=='menunggu')throw new RuntimeException('Pembayaran tidak menunggu verifikasi.');$tx=Transaksi::lockForUpdate()->findOrFail($p->id_transaksi);$this->assertSupported($tx);$paid=$this->paid($tx);if((float)$paid+(float)$p->jumlah>(float)$tx->total)throw new RuntimeException('Pembayaran menyebabkan overpayment.');$p->forceFill(array_merge(['status'=>'terverifikasi'],$this->verificationFields($actorId,'terverifikasi',$verifiedAt)))->save();$new=$this->status((string)$tx->total,(string)((float)$paid+(float)$p->jumlah));$tx->forceFill(['status'=>$new])->save();$this->notifyVerified($tx);$this->sync($tx,$new);return$p;});}
 public function reject(int $paymentId,int $actorId):Pembayaran
 {
  return DB::transaction(function()use($paymentId,$actorId){
   $p=Pembayaran::lockForUpdate()->findOrFail($paymentId);
   if($p->status!=='menunggu')throw new RuntimeException('Pembayaran tidak menunggu penolakan.');
   $p->forceFill(['status'=>'ditolak'])->save();
   $tx=Transaksi::lockForUpdate()->findOrFail($p->id_transaksi);
   if($tx->jenis_transaksi==='penjualan_mobil'){
    $sale=PenjualanMobil::lockForUpdate()->where('id_transaksi',$tx->id_transaksi)->first();
    $order=$sale?PesananMobilMember::lockForUpdate()->where('id_penjualan',$sale->id_penjualan)->first():null;
    if($order&&in_array($order->status,['menunggu_pembayaran','lunas'],true)){
     $order->forceFill(['status'=>'dibatalkan','catatan_admin'=>'Pembayaran ditolak; silakan pilih metode pembayaran kembali.'])->save();
     $tx->forceFill(['status'=>'dibatalkan'])->save();
     $sale->forceFill(['status'=>'proses'])->save();
     $car=Mobil::lockForUpdate()->find($order->id_mobil);
     if($car&&!app(VehicleInventoryService::class)->find($car))$car->forceFill(['status'=>'tersedia'])->save();
     app(ServiceNotificationService::class)->forMemberVehicleOrder(
      $order->fresh('pelanggan'),
      'Pembayaran ditolak',
      'Pembayaran pesanan '.$order->nomor_pesanan.' ditolak. Silakan pilih metode pembayaran kembali.'
     );
    }
   }
   return$p;
  });
 }
 public function summary(int $transactionId):array{$tx=Transaksi::findOrFail($transactionId);$paid=$this->paid($tx);return['paid'=>$paid,'remaining'=>(string)DB::selectOne('SELECT CAST(? - ? AS DECIMAL(15,2)) AS value',[$tx->total,$paid])->value,'status'=>$tx->status];}
}
