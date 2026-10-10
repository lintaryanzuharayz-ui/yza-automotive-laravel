<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; use App\Http\Requests\AdjustStokRequest; use App\Models\{RiwayatStok,Sparepart}; use App\Services\InventoryService; use Illuminate\Http\Request; use Throwable;
class InventoryController extends Controller {
 public function stock(){ $parts=Sparepart::with('kategori')->orderBy('nama_sparepart')->paginate(20); return view('admin.inventory.stock',compact('parts')); }
 public function history(){ $history=RiwayatStok::with(['sparepart','petugas'])->latest('tanggal')->paginate(25); return view('admin.inventory.history',compact('history')); }
 public function adjustment(Sparepart $sparepart=null){ return view('admin.inventory.adjustment',['parts'=>Sparepart::where('status','aktif')->orderBy('nama_sparepart')->get(),'selected'=>$sparepart]); }
 public function adjust(AdjustStokRequest $request,InventoryService $svc){try{$data=$request->validated();$svc->adjust($data['id_sparepart'],$data['stok_baru'],$data['alasan'],$data['catatan'],$svc->actorPetugasId($request->user()->id_user));return redirect()->route('admin.inventory.stock')->with('success','Penyesuaian stok berhasil dicatat.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
}
