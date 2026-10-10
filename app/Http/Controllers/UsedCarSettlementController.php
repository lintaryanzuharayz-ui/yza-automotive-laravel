<?php
namespace App\Http\Controllers;
use App\Models\{PembelianMobilBekas,PengajuanMobil};
use App\Services\{BookingMobilService,PembelianMobilBekasService};
use Illuminate\Http\Request;
use Throwable;
class UsedCarSettlementController extends Controller {
 public function purchase(Request $r,PengajuanMobil $pengajuan_mobil,PembelianMobilBekasService $svc){try{$inspection=(int)$pengajuan_mobil->inspeksi()->firstOrFail()->id_inspeksi;$actor=(int)app(BookingMobilService::class)->actorPetugasId($r->user()->id_user);$svc->createFromApproved($pengajuan_mobil->id_pengajuan,$inspection,$actor,'Pembelian dari '.$pengajuan_mobil->kode_pengajuan);return back()->with('success','Pembelian mobil bekas dibuat.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function finalize(Request $r,PembelianMobilBekas $pembelian_mobil,PembelianMobilBekasService $svc){try{$svc->finalize($pembelian_mobil->id_pembelian);return back()->with('success','Mobil masuk inventory.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
