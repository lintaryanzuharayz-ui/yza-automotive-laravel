<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenjualanMobilRequest;
use App\Models\{BookingMobil, Mobil, Pelanggan, PenjualanMobil};
use App\Services\{InventoryService, PenjualanMobilService};
use Illuminate\Http\Request;
use Throwable;

class OperationsPenjualanMobilController extends Controller
{
    public function index() { return view('operations.penjualan-mobil.index', ['rows'=>PenjualanMobil::with(['pelanggan','mobil','transaksi'])->latest('tanggal_penjualan')->paginate(20)]); }
    public function create() { return view('operations.penjualan-mobil.create', ['customers'=>Pelanggan::where('status','aktif')->orderBy('nama_lengkap')->get(), 'cars'=>Mobil::whereIn('status',['tersedia','dibooking'])->orderBy('merk')->get(), 'bookings'=>BookingMobil::with(['pelanggan','mobil'])->where('status','dikonfirmasi')->latest()->get(), 'promos'=>\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','penjualan_mobil'])->get()]); }
    public function store(StorePenjualanMobilRequest $request, PenjualanMobilService $service) { try { $data=$request->validated(); $id=$service->createWithPromo($data, (int)($request->user()->petugas?->id_petugas ?: app(InventoryService::class)->actorPetugasId($request->user()->id_user)), $request->integer('promo_id')?:null)->id_penjualan; return redirect()->route($request->user()->roleName().'.penjualan-mobil.show',$id)->with('success','Penjualan mobil dibuat dan unit dicadangkan.'); } catch(Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); } }
    public function show(PenjualanMobil $penjualan_mobil) { $penjualan_mobil->load(['pelanggan','mobil','booking','petugas','transaksi']); return view('operations.penjualan-mobil.show',['sale'=>$penjualan_mobil]); }
}
