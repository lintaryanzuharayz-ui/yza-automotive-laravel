<?php
namespace App\Http\Controllers;
use App\Models\PembelianMobilBekas;
use Illuminate\Http\Request;
class UsedCarReceiptController extends Controller
{
    public function show(Request $r,PembelianMobilBekas $pembelian_mobil)
    {
        $pembelian_mobil->load(['transaksi.pembayarans','pelanggan','petugas','pengajuan','inspeksi','mobil']);
        if($r->user()->roleName()==='pelanggan') abort_unless((int)$pembelian_mobil->id_pelanggan===(int)$r->user()->pelanggan?->id_pelanggan,403);
        return view('used-car.receipt',['purchase'=>$pembelian_mobil]);
    }
}
