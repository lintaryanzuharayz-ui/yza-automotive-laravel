<?php
namespace App\Http\Controllers;
use App\Models\TestDrive;use App\Services\TestDriveService;use Illuminate\Http\Request;use Throwable;
class OperationsTestDriveController extends Controller {
 public function index(Request $r){
  $search=trim((string)$r->query('q'));$status=trim((string)$r->query('status'));$date=trim((string)$r->query('date'));$perPage=(int)$r->query('perPage',10);if(!in_array($perPage,[10,20,50],true))$perPage=10;
  $q=TestDrive::with(['pelanggan','mobil.foto','petugas'])->latest('tanggal')->latest('jam');
  if($search!=='')$q->where(function($query)use($search){$query->where('kode_test_drive','like','%'.$search.'%')->orWhereHas('pelanggan',fn($customer)=>$customer->where('nama_lengkap','like','%'.$search.'%'))->orWhereHas('mobil',fn($car)=>$car->where('kode_mobil','like','%'.$search.'%')->orWhere('merk','like','%'.$search.'%')->orWhere('model','like','%'.$search.'%'));});
  if($status!=='')$q->where('status',$status);if($date!=='')$q->whereDate('tanggal',$date);
  $stats=['total'=>TestDrive::count(),'waiting'=>TestDrive::where('status','menunggu')->count(),'scheduled'=>TestDrive::where('status','disetujui')->count(),'completed'=>TestDrive::where('status','selesai')->count(),'cancelled'=>TestDrive::whereIn('status',['ditolak','dibatalkan'])->count()];
  return view('operations.test-drive.index',['rows'=>$q->paginate($perPage)->withQueryString(),'role'=>$r->user()->roleKey(),'search'=>$search,'status'=>$status,'date'=>$date,'perPage'=>$perPage,'testDriveStats'=>$stats,'statuses'=>['menunggu','disetujui','selesai','ditolak','dibatalkan']]);
 }
 public function show(Request $r,TestDrive $test_drive){$test_drive->load(['pelanggan','mobil','petugas']);return view('operations.test-drive.show',['test_drive'=>$test_drive,'role'=>$r->user()->roleKey()]);}
 public function action(Request $r,TestDrive $test_drive,string $action,TestDriveService $svc){try{$role=$r->user()->roleKey();$petugas=$svc->actorPetugasId($r->user()->id_user);$canOperate=in_array($role,['admin','petugas','operational_staff'],true);if($action==='approve'&&$canOperate)$svc->approve($test_drive->id_test_drive,(int)$petugas);elseif($action==='reject'&&$canOperate)$svc->reject($test_drive->id_test_drive,(int)$petugas);elseif($action==='complete'&&$canOperate)$svc->complete($test_drive->id_test_drive,(int)$petugas);elseif($action==='cancel'&&in_array($role,['petugas','operational_staff'],true))$svc->cancel($test_drive->id_test_drive,null,(int)$petugas);else abort(403);return back()->with('success','Status test drive diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
