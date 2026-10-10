<?php
namespace App\Http\Controllers\Pelanggan;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKendaraanPelangganRequest;
use App\Http\Requests\UpdateKendaraanPelangganRequest;
use App\Models\KendaraanPelanggan;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class KendaraanController extends Controller
{
 private function customer(){ $p=auth()->user()->pelanggan; abort_unless($p,404); return $p; }
 public function index(): View { $p=$this->customer(); $ownership=app(\App\Services\VerifiedCarOwnershipService::class); $hasVerifiedPurchase=$ownership->customerHasVerifiedPurchase((int)$p->id_pelanggan); $items=$hasVerifiedPurchase ? $p->kendaraan()->where('status','aktif')->where(function($q){$q->whereNotNull('nomor_rangka')->orWhereNotNull('nomor_mesin');})->latest()->paginate(10) : $p->kendaraan()->whereRaw('1 = 0')->paginate(10); return view('pelanggan.kendaraan.index',compact('items','hasVerifiedPurchase')); }
 public function create(): View { return view('pelanggan.kendaraan.create',['item'=>new KendaraanPelanggan(['kilometer'=>0])]); }
 public function store(StoreKendaraanPelangganRequest $request, MasterAuditService $audit): RedirectResponse { $p=$this->customer(); $item=$p->kendaraan()->create($request->validated()+['status'=>'aktif']); $audit->record('create','kendaraan_pelanggan',$item->id_kendaraan_pelanggan,'Pelanggan menambahkan kendaraan'); return redirect()->route('pelanggan.kendaraan.show',$item)->with('success','Kendaraan berhasil ditambahkan.'); }
 public function show(KendaraanPelanggan $kendaraan): View { $p=$this->customer(); abort_unless((int)$kendaraan->id_pelanggan===(int)$p->id_pelanggan,404); return view('pelanggan.kendaraan.show',['item'=>$kendaraan]); }
 public function edit(KendaraanPelanggan $kendaraan): View { $p=$this->customer(); abort_unless((int)$kendaraan->id_pelanggan===(int)$p->id_pelanggan && $kendaraan->status==='aktif',404); return view('pelanggan.kendaraan.edit',['item'=>$kendaraan]); }
 public function update(UpdateKendaraanPelangganRequest $request, KendaraanPelanggan $kendaraan, MasterAuditService $audit): RedirectResponse { $p=$this->customer(); abort_unless((int)$kendaraan->id_pelanggan===(int)$p->id_pelanggan && $kendaraan->status==='aktif',404); $kendaraan->update($request->validated()); $audit->record('update','kendaraan_pelanggan',$kendaraan->id_kendaraan_pelanggan,'Pelanggan memperbarui kendaraan'); return redirect()->route('pelanggan.kendaraan.show',$kendaraan)->with('success','Kendaraan berhasil diperbarui.'); }
 public function deactivate(KendaraanPelanggan $kendaraan, MasterAuditService $audit): RedirectResponse { $p=$this->customer(); abort_unless((int)$kendaraan->id_pelanggan===(int)$p->id_pelanggan && $kendaraan->status==='aktif',404); $kendaraan->update(['status'=>'nonaktif']); $audit->record('status','kendaraan_pelanggan',$kendaraan->id_kendaraan_pelanggan,'Pelanggan menonaktifkan kendaraan'); return back()->with('success','Kendaraan dinonaktifkan.'); }
}
