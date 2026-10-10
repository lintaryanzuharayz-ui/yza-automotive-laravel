<?php
namespace App\Http\Controllers;
use App\Http\Requests\{StoreDetailPenjualanSparepartRequest,StorePenjualanSparepartRequest,UpdateDetailPenjualanSparepartRequest};
use App\Models\{DetailPenjualanSparepart,PenjualanSparepart,Sparepart};
use App\Services\{InventoryService,PenjualanSparepartService};
use Illuminate\Http\Request;
use Throwable;
class OperationsPenjualanSparepartController extends Controller {
 public function index(Request $r){$rows=PenjualanSparepart::with(['pelanggan','petugas','transaksi'])->latest('created_at')->paginate(20);return view('operations.penjualan-sparepart.index',compact('rows'));}
 public function create(){return view('operations.penjualan-sparepart.create',['customers'=>\App\Models\Pelanggan::where('status','aktif')->orderBy('nama_lengkap')->get()]);}
 public function store(StorePenjualanSparepartRequest $r,PenjualanSparepartService $svc){try{$id=$svc->createDraft($r->validated()['id_pelanggan']??null,(int)($r->user()->petugas?->id_petugas??app(InventoryService::class)->actorPetugasId($r->user()->id_user)),$r->validated()['catatan']??'')->id_penjualan_sparepart;return redirect()->route($r->user()->roleName().'.penjualan-sparepart.show',$id)->with('success','Draft dibuat.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function show(PenjualanSparepart $penjualan_sparepart){$penjualan_sparepart->load(['pelanggan','petugas','transaksi','details.sparepart']);$promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','penjualan_sparepart'])->get();return view('operations.penjualan-sparepart.show',['sale'=>$penjualan_sparepart,'parts'=>Sparepart::where('status','aktif')->orderBy('nama_sparepart')->get(),'promos'=>$promos]);}
 public function addDetail(StoreDetailPenjualanSparepartRequest $r,PenjualanSparepart $penjualan_sparepart,PenjualanSparepartService $svc){try{$d=$r->validated();$svc->addDetail($penjualan_sparepart->id_penjualan_sparepart,$d['id_sparepart'],$d['jumlah']);return back()->with('success','Item draft tersimpan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function updateDetail(UpdateDetailPenjualanSparepartRequest $r,DetailPenjualanSparepart $detail_penjualan_sparepart,PenjualanSparepartService $svc){try{$svc->updateDetail($detail_penjualan_sparepart->id_detail,$r->validated()['jumlah']);return back()->with('success','Qty diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function deleteDetail(DetailPenjualanSparepart $detail_penjualan_sparepart,PenjualanSparepartService $svc){try{$svc->removeDetail($detail_penjualan_sparepart->id_detail);return back()->with('success','Item dihapus.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function finalize(Request $r,PenjualanSparepart $penjualan_sparepart,PenjualanSparepartService $svc){try{$actor=(int)($r->user()->petugas?->id_petugas??app(InventoryService::class)->actorPetugasId($r->user()->id_user));$tx=$svc->finalizeWithPromo($penjualan_sparepart->id_penjualan_sparepart,$actor,$r->integer('promo_id')?:null);return back()->with('success','Penjualan difinalisasi: '.$tx->kode_transaksi);}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function cancel(PenjualanSparepart $penjualan_sparepart,PenjualanSparepartService $svc){try{$svc->cancel($penjualan_sparepart->id_penjualan_sparepart);return back()->with('success','Draft dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
