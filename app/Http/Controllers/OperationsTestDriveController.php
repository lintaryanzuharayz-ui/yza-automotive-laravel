<?php
namespace App\Http\Controllers;
use App\Models\TestDrive;use App\Services\TestDriveService;use Illuminate\Http\Request;use Throwable;
class OperationsTestDriveController extends Controller {
 public function index(Request $r){$q=TestDrive::with(['pelanggan','mobil','petugas'])->latest();if($r->filled('status'))$q->where('status',$r->string('status'));return view('operations.test-drive.index',['rows'=>$q->paginate(20)->withQueryString(),'role'=>$r->user()->roleKey()]);}
 public function show(Request $r,TestDrive $test_drive){$test_drive->load(['pelanggan','mobil','petugas']);return view('operations.test-drive.show',['test_drive'=>$test_drive,'role'=>$r->user()->roleKey()]);}
 public function action(Request $r,TestDrive $test_drive,string $action,TestDriveService $svc){try{$role=$r->user()->roleKey();$petugas=$svc->actorPetugasId($r->user()->id_user);$canOperate=in_array($role,['admin','petugas','operational_staff'],true);if($action==='approve'&&$canOperate)$svc->approve($test_drive->id_test_drive,(int)$petugas);elseif($action==='reject'&&$canOperate)$svc->reject($test_drive->id_test_drive,(int)$petugas);elseif($action==='complete'&&$canOperate)$svc->complete($test_drive->id_test_drive,(int)$petugas);elseif($action==='cancel'&&in_array($role,['petugas','operational_staff'],true))$svc->cancel($test_drive->id_test_drive,null,(int)$petugas);else abort(403);return back()->with('success','Status test drive diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
