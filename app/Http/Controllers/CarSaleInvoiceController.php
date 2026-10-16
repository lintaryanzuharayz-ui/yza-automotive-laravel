<?php
namespace App\Http\Controllers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
class CarSaleInvoiceController extends Controller
{
 public function show(Request $request, Transaksi $transaksi)
 {
  abort_unless($transaksi->jenis_transaksi === 'penjualan_mobil', 404);
  if ($request->user()->roleName() === 'pelanggan') abort_unless((int) $transaksi->id_pelanggan === (int) $request->user()->pelanggan?->id_pelanggan, 403);
  $transaksi->load(['pelanggan','petugas','pembayarans','penjualanMobil.mobil','penjualanMobil.booking']);
  $verifiedPayments = $transaksi->pembayarans->where('arah', 'masuk')->where('status', 'terverifikasi')->values();
  $paid = (float) $verifiedPayments->sum('jumlah');
  $totalBill = (float) $transaksi->total;
  $remaining = max(0, $totalBill - $paid);
  $paymentStatusLabel = match ($transaksi->status) {
   'lunas' => 'Pembayaran terverifikasi',
   'dp' => 'Pembayaran sebagian terverifikasi',
   'dibatalkan' => 'Transaksi dibatalkan',
   default => 'Pembayaran belum diterima',
  };
  $paymentMessage = match ($transaksi->status) {
   'lunas' => 'Pembayaran telah diterima dan diverifikasi.',
   'dp' => 'Sebagian pembayaran telah diterima dan diverifikasi. Dokumen ini bukan bukti pelunasan.',
   'dibatalkan' => 'Transaksi dibatalkan. Dokumen ini bukan bukti pelunasan.',
   default => 'Pembayaran belum diterima atau diverifikasi. Dokumen ini merupakan invoice tagihan dan bukan bukti pelunasan.',
  };
  $view = $request->boolean('modal') ? 'invoices.partials.car-sale' : 'invoices.car-sale';
  return view($view, compact('transaksi', 'verifiedPayments', 'paid', 'totalBill', 'remaining', 'paymentStatusLabel', 'paymentMessage'));
 }
}
