<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{ServiceSpecialization,ServiceStaffProfile,TechnicianTimeOff,TechnicianWorkingHour};
use Illuminate\Http\{RedirectResponse,Request}; use Illuminate\View\View;
class ServicePersonnelController extends Controller {
 public function index(Request $request):View
 {
  $q=trim((string)$request->input('q','')); $status=(string)$request->input('status',''); $bookable=(string)$request->input('bookable','');
  $perPage=in_array((int)$request->input('perPage',8),[8,12,24],true)?(int)$request->input('perPage',8):8;
  $base=ServiceStaffProfile::query();
  $profiles=(clone $base)->with(['petugas','specializations','workingHours'])
   ->when($q!=='' ,fn($query)=>$query->where(fn($inner)=>$inner->whereHas('petugas',fn($petugas)=>$petugas->where('nama_lengkap','like',"%{$q}%")->orWhere('jabatan','like',"%{$q}%"))->orWhereHas('specializations',fn($specialization)=>$specialization->where('name','like',"%{$q}%"))))
   ->when($status==='active',fn($query)=>$query->whereHas('petugas',fn($petugas)=>$petugas->where('status','aktif')))
   ->when($status==='inactive',fn($query)=>$query->whereHas('petugas',fn($petugas)=>$petugas->where('status','!=','aktif')))
   ->when($bookable==='1',fn($query)=>$query->where('is_bookable',true))
   ->when($bookable==='0',fn($query)=>$query->where('is_bookable',false))
   ->orderBy('id_service_staff_profile')->paginate($perPage)->withQueryString();
  $selectedId=(int)$request->input('selected',0);
  $selectedProfile=$selectedId?ServiceStaffProfile::with(['petugas','specializations','workingHours'])->find($selectedId):$profiles->first();
  $activePetugas=fn($query)=>$query->where('status','aktif');
  $stats=[
   'total'=>(clone $base)->count(),
   'technicians'=>(clone $base)->where('can_be_technician',true)->whereHas('petugas',$activePetugas)->count(),
   'advisors'=>(clone $base)->where('can_be_service_advisor',true)->whereHas('petugas',$activePetugas)->count(),
   'bookable'=>(clone $base)->where('is_bookable',true)->whereHas('petugas',$activePetugas)->count(),
   'time_off'=>TechnicianTimeOff::whereDate('date',now()->toDateString())->distinct('id_petugas')->count('id_petugas'),
  ];
  return view('admin.servis.personel',[
   'profiles'=>$profiles,'selectedProfile'=>$selectedProfile,'specializations'=>ServiceSpecialization::where('is_active',true)->orderBy('name')->get(),
   'timeOffs'=>TechnicianTimeOff::with('petugas')->whereDate('date','>=',now()->toDateString())->orderBy('date')->limit(50)->get(),
   'stats'=>$stats,'q'=>$q,'status'=>$status,'bookable'=>$bookable,'perPage'=>$perPage,
  ]);
 }
 public function updateProfile(Request $r,ServiceStaffProfile $profile):RedirectResponse{$d=$r->validate(['can_be_technician'=>'nullable|boolean','can_be_service_advisor'=>'nullable|boolean','experience_years'=>'required|integer|min:0|max:60','is_bookable'=>'nullable|boolean','specializations'=>'array','specializations.*'=>'integer|exists:service_specializations,id_service_specialization']);$profile->update(['can_be_technician'=>(bool)($d['can_be_technician']??false),'can_be_service_advisor'=>(bool)($d['can_be_service_advisor']??false),'experience_years'=>$d['experience_years'],'is_bookable'=>(bool)($d['is_bookable']??false)]);$profile->specializations()->sync($d['specializations']??[]);return back()->with('success','Profil personnel servis diperbarui.');}
 public function updateHours(Request $r,ServiceStaffProfile $profile):RedirectResponse{$d=$r->validate(['hours'=>'required|array','hours.*.is_working'=>'nullable|boolean','hours.*.open_time'=>'nullable|date_format:H:i','hours.*.close_time'=>'nullable|date_format:H:i']);foreach($d['hours'] as $id=>$row)TechnicianWorkingHour::where('id_petugas',$profile->id_petugas)->whereKey($id)->update(array_merge($row,['is_working'=>(bool)($row['is_working']??false)]));return back()->with('success','Jam kerja teknisi diperbarui.');}
 public function storeTimeOff(Request $r):RedirectResponse{$d=$r->validate(['id_petugas'=>'required|exists:petugas,id_petugas','date'=>'required|date','reason'=>'required|string|max:255']);TechnicianTimeOff::updateOrCreate(['id_petugas'=>$d['id_petugas'],'date'=>$d['date']],['reason'=>$d['reason']]);return back()->with('success','Time off ditambahkan.');}
 public function destroyTimeOff(TechnicianTimeOff $timeOff):RedirectResponse{$timeOff->delete();return back()->with('success','Time off dihapus.');}
}
