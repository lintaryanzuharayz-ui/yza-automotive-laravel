<?php
namespace App\Services;
use App\Models\{PembayaranGateway,Transaksi};
use RuntimeException;
class GatewayPaymentGuard
{
 public function supersedeActiveAttemptsForManual(Transaksi $tx): void
 {
  PembayaranGateway::where('id_transaksi', $tx->id_transaksi)
   ->whereIn('status_provider', ['created', 'pending'])
   ->update(['status_provider' => 'manual_selected', 'last_synced_at' => now()]);
 }

 public function assertManualAllowed(Transaksi $tx):void
 {
  if($tx->jenis_transaksi==='penjualan_mobil'){$order=\App\Models\PesananMobilMember::where('id_transaksi',$tx->id_transaksi)->first();if($order&&!in_array($order->status,['menunggu_pembayaran','lunas'],true))throw new RuntimeException('Pembayaran belum dibuka. Pesanan harus disetujui admin terlebih dahulu.');}
  if(PembayaranGateway::where('id_transaksi',$tx->id_transaksi)->whereIn('status_provider',['created','pending'])->exists()) throw new RuntimeException('Pembayaran Midtrans aktif. Selesaikan atau tunggu status gateway sebelum pembayaran manual.');
 }
 public function assertAttemptAllowed(Transaksi $tx):void
 {
  if($tx->pembayarans()->where('arah','masuk')->where('status','menunggu')->exists()) throw new RuntimeException('Masih ada pembayaran manual yang menunggu verifikasi.');
 }
}
