<?php
namespace App\Http\Controllers;
use App\Http\Requests\{FinalizeServisRequest,StartServisRequest,StoreDetailServisRequest,StoreServisSparepartRequest,UpdateInspectionRequest}; use App\Models\{BookingServis,Servis,DetailServis,ServisSparepart}; use App\Services\{ServicePersonnelAvailabilityService,ServisService,ServisSparepartService}; use Illuminate\Http\Request; use Throwable;
class OperationsServisController extends Controller {
 public function index(Request $request){
  $stats = [
    'total_wo' => Servis::count(),
    'pemeriksaan' => Servis::where('status', 'pemeriksaan')->count(),
    'dikerjakan' => Servis::where('status', 'dikerjakan')->count(),
    'selesai' => Servis::where('status', 'selesai')->count(),
    'dibatalkan' => Servis::where('status', 'dibatalkan')->count(),
    'booking_antri' => BookingServis::whereIn('status', ['menunggu', 'diterima'])->count(),
  ];

  $q = Servis::with(['pelanggan','kendaraan','bookingServis.jenisServis','sparepartItems.sparepart','petugas','activeAssistants'])
    ->when($request->user()?->roleKey() === 'mechanic', fn($query) => $query->where('id_petugas', $request->user()->petugas?->id_petugas))
    ->when(request('q'), function($query, $v) {
      $term = '%'.$v.'%';
      $query->where(function($sub) use ($term) {
        $sub->where('kode_servis', 'like', $term)
          ->orWhereHas('pelanggan', fn($p) => $p->where('nama_lengkap', 'like', $term))
          ->orWhereHas('kendaraan', fn($k) => $k->where('nomor_polisi', 'like', $term)->orWhere('model', 'like', $term)->orWhere('merk', 'like', $term));
      });
    })
    ->when(request('status'), fn($query, $v) => $query->where('status', $v))
    ->latest('id_servis');

  return view('operations.servis.index', [
    'rows' => $q->paginate(20)->withQueryString(),
    'stats' => $stats,
  ]);
 }
 public function show(Servis $servis, \App\Services\WorkOrderStageService $stages){$prefix=request()->is('admin/*')?'admin':'petugas';return redirect()->route($stages->routeName($prefix,$stages->currentStage($servis)),$servis);}
 public function pemeriksaan(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'pemeriksaan',$stages);}
 public function persetujuan(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'persetujuan',$stages);}
 public function pengerjaan(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'pengerjaan',$stages);}
 public function qualityControl(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'quality-control',$stages);}
 public function finalisasi(Request $request, Servis $servis, \App\Services\WorkOrderStageService $stages){return $this->stagePage($request,$servis,'finalisasi',$stages);}
 private function stagePage(Request $request, Servis $servis, string $stage, \App\Services\WorkOrderStageService $stages){$prefix=$request->is('admin/*')?'admin':'petugas';if(!$stages->canAccess($servis,$stage)){return redirect()->route($stages->routeName($prefix,$stages->currentStage($servis)),$servis)->with('warning',$stages->redirectReason($stage));}$servis->load(['pelanggan','kendaraan.penjualan.mobil','petugas.serviceStaffProfile.specializations','serviceAdvisor.serviceStaffProfile.specializations','bookingServis.jenisServis','bookingServis.preferredPetugas','details.jenisServis','sparepartItems.sparepart','transaksi.pembayarans','findings','executionEvents','approvalRequests.finding','approvalRequests.items.sparepart','qualityControl.items']);$promos=\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','servis'])->get();$serviceTypes=\App\Models\JenisServis::where('status','aktif')->orderBy('nama_servis')->get();$spareparts=\App\Models\Sparepart::where('status','aktif')->where('stok','>',0)->orderBy('nama_sparepart')->get();$vehicle=$servis->kendaraan;$verifiedFitmentCount=$vehicle?\App\Models\SparepartFitment::where('status_verifikasi','verified_fit')->where(fn($q)=>$q->whereNull('merk_kendaraan')->orWhere('merk_kendaraan',$vehicle->merk))->where(fn($q)=>$q->whereNull('model_kendaraan')->orWhere('model_kendaraan','like','%'.$vehicle->model.'%'))->count():0;$workSources=app(\App\Services\WorkOrderSourceService::class);$mobil=$servis->kendaraan?->penjualan?->mobil;$galleryData=$mobil?app(\App\Services\VehicleGalleryService::class)->for($mobil):[];$selected=collect($galleryData)->first();$vehicleImage=$selected['images'][0]['src']??asset('assets/images/hero/yza-hero-reference-v3.png');return view('operations.servis.wizard',compact('servis','stage','stages','promos','serviceTypes','spareparts','verifiedFitmentCount','vehicleImage','prefix','workSources'));}
 public function fromBooking(StartServisRequest $r,BookingServis $booking_servis,ServisService $svc){try{$d=$r->validated();$isAdmin=$r->user()->roleName()==='admin';$hasExplicitTechnician=$isAdmin && array_key_exists('id_petugas',$d) && $d['id_petugas']!==null;$assigned=(int)($hasExplicitTechnician?$d['id_petugas']:$r->user()->petugas?->id_petugas);$advisor=$isAdmin&&isset($d['id_service_advisor'])?(int)$d['id_service_advisor']:null;$servis=$svc->createFromBooking($booking_servis->id_booking_servis,$assigned,$d['kilometer']??null,$d['catatan']??null,$advisor,$hasExplicitTechnician);$route=$isAdmin?'admin.servis.show':'petugas.servis.show';return redirect()->route($route,$servis)->with('success','Work Order siap diproses.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function inspection(UpdateInspectionRequest $r,Servis $servis,ServisService $svc){try{$d=$r->validated();$svc->updateInspection($servis->id_servis,$d['hasil_pemeriksaan'],$d['catatan']??null);$prefix=$r->is('admin/*')?'admin':'petugas';return redirect()->route($prefix.'.servis.persetujuan',$servis)->with('success','Hasil pemeriksaan tersimpan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function detail(StoreDetailServisRequest $r,Servis $servis,ServisService $svc){try{$d=$r->validated();$svc->addDetail($servis->id_servis,$d['id_jenis_servis']??null,$d['deskripsi_pekerjaan'],$d['biaya'],$d['catatan']??null);return back()->with('success','Jasa berhasil ditambahkan.')->with('execution_form','jasa');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function removeDetail(Servis $servis,DetailServis $detail_servis,ServisService $svc){try{$svc->removeDetail($servis->id_servis,$detail_servis->id_detail_servis);return back()->with('success','Detail pekerjaan dihapus.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function sparepart(StoreServisSparepartRequest $r,Servis $servis,ServisSparepartService $svc){try{$d=$r->validated();$svc->add($servis->id_servis,(int)$d['id_sparepart'],(int)$d['jumlah']);return back()->with('success','Suku cadang berhasil dicatat.')->with('execution_form','sparepart');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function removeSparepart(Servis $servis,ServisSparepart $servis_sparepart,ServisSparepartService $svc){try{$svc->remove($servis->id_servis,$servis_sparepart->id_servis_sparepart);return back()->with('success','Draft spare part dihapus.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
 public function finalize(FinalizeServisRequest $r,Servis $servis,ServisService $svc){try{$actor=$r->user()->petugas?->id_petugas ?? app(\App\Services\InventoryService::class)->actorPetugasId($r->user()->id_user);$tx=$svc->finalizeWithPromo($servis->id_servis,(int)$actor,$r->integer('promo_id')?:null);return back()->with('success','Servis difinalisasi dan transaksi '.$tx->kode_transaksi.' dibuat dengan status belum_bayar.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
