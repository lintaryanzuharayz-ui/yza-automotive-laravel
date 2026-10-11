<?php
namespace App\Http\Controllers;
use App\Models\Transaksi;
use Illuminate\Http\Request;
class CarSaleInvoiceController extends Controller
{
 public function show(Request $request, Transaksi $transaksi){abort_unless($transaksi->jenis_transaksi==='penjualan_mobil',404);if($request->user()->roleName()==='pelanggan')abort_unless((int)$transaksi->id_pelanggan===(int)$request->user()->pelanggan?->id_pelanggan,403);$transaksi->load(['pelanggan','petugas','pembayarans','penjualanMobil.mobil','penjualanMobil.booking']);$paid=$transaksi->pembayarans->where('arah','masuk')->where('status','terverifikasi')->sum('jumlah');$view=$request->boolean('modal')?'invoices.partials.car-sale':'invoices.car-sale';return view($view,compact('transaksi','paid'));}
}
