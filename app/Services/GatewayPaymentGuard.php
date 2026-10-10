<?php
namespace App\Services;
use App\Models\{PembayaranGateway,Transaksi};
use RuntimeException;
class GatewayPaymentGuard
{
 public function assertManualAllowed(Transaksi $tx):void
 {
  if(PembayaranGateway::where('id_transaksi',$tx->id_transaksi)->whereIn('status_provider',['created','pending'])->exists()) throw new RuntimeException('Pembayaran Midtrans aktif. Selesaikan atau tunggu status gateway sebelum pembayaran manual.');
 }
 public function assertAttemptAllowed(Transaksi $tx):void
 {
  if($tx->pembayarans()->where('arah','masuk')->where('status','menunggu')->exists()) throw new RuntimeException('Masih ada pembayaran manual yang menunggu verifikasi.');
 }
}
