<?php
namespace App\Http\Controllers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
class CarSalePaymentController extends PaymentController
{
 public function create(Request $request, Transaksi $transaksi){abort_unless($transaksi->jenis_transaksi==='penjualan_mobil',404);return view('payments.create',compact('transaksi'));}
}
