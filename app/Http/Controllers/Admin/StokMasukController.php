<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Http\Requests\{StoreDetailStokMasukRequest,StoreStokMasukRequest}; use App\Models\{DetailStokMasuk,Sparepart,StokMasuk,Supplier}; use App\Services\InventoryService; use Illuminate\Http\Request; use Throwable;
class StokMasukController extends Controller {
 public function index(){ $rows=StokMasuk::with('supplier')->latest('tanggal')->paginate(15); return view('admin.inventory.stok-masuk.index',compact('rows')); }
 public function create(){return view('admin.inventory.stok-masuk.create',['suppliers'=>Supplier::where('status','aktif')->orderBy('nama_supplier')->get()]);}
 public function store(StoreStokMasukRequest $request,InventoryService $svc){$id=$svc->createReceipt($request->validated(),$svc->actorPetugasId($request->user()->id_user)); return redirect()->route('admin.stok-masuk.show',$id);}
 public function show(StokMasuk $stok_masuk){$stok_masuk->load(['supplier','petugas','detail.sparepart']); return view('admin.inventory.stok-masuk.show',compact('stok_masuk'));}
 public function cancel(StokMasuk $stok_masuk,InventoryService $svc){try{$svc->cancel($stok_masuk->id_stok_masuk);return back()->with('success','Draft dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function finalize(StokMasuk $stok_masuk,InventoryService $svc){try{$svc->finalize($stok_masuk->id_stok_masuk);return redirect()->route('admin.stok-masuk.show',$stok_masuk)->with('success','Stok diterima dan histori dicatat.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function addDetail(StoreDetailStokMasukRequest $request,StokMasuk $stok_masuk,InventoryService $svc){try{$svc->addDetail($stok_masuk->id_stok_masuk,$request->validated());return back()->with('success','Item ditambahkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function updateDetail(StoreDetailStokMasukRequest $request,DetailStokMasuk $detail_stok_masuk,InventoryService $svc){try{$svc->updateDetail($detail_stok_masuk->id_detail_stok_masuk,$request->validated());return back()->with('success','Item diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function removeDetail(DetailStokMasuk $detail_stok_masuk,InventoryService $svc){try{$svc->removeDetail($detail_stok_masuk->id_detail_stok_masuk);return back()->with('success','Item dihapus.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
