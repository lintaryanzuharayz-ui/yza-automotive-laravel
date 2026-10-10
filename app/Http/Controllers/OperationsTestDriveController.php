<?php
namespace App\Http\Controllers;
use App\Models\TestDrive;use App\Services\TestDriveService;use Illuminate\Http\Request;use Throwable;
class OperationsTestDriveController extends Controller {
 public function index(Request $r){$q=TestDrive::with(['pelanggan','mobil','petugas'])->latest();if($r->filled('status'))$q->where('status',$r->string('status'));return view('operations.test-drive.index',['rows'=>$q->paginate(20)->withQueryString(),'role'=>$r->user()->roleName()]);}
 public function show(Request $r,TestDrive $test_drive){$test_drive->load(['pelanggan','mobil','petugas']);return view('operations.test-drive.show',['test_drive'=>$test_drive,'role'=>$r->user()->roleName()]);}
 public function action(Request $r,TestDrive $test_drive,string $action,TestDriveService $svc){try{$role=$r->user()->roleName();$petugas=$svc->actorPetugasId($r->user()->id_user);if($action==='approve'&&in_array($role,['admin','petugas'],true))$svc->approve($test_drive->id_test_drive,(int)$petugas);elseif($action==='reject'&&in_array($role,['admin','petugas'],true))$svc->reject($test_drive->id_test_drive,(int)$petugas);elseif($action==='complete'&&in_array($role,['admin','petugas'],true))$svc->complete($test_drive->id_test_drive,(int)$petugas);elseif($action==='cancel'&&$role==='petugas')$svc->cancel($test_drive->id_test_drive,null,(int)$petugas);else abort(403);return back()->with('success','Status test drive diperbarui.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
