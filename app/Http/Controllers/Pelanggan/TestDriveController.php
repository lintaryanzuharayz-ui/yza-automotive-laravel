<?php
namespace App\Http\Controllers\Pelanggan;
use App\Http\Controllers\Controller;use App\Http\Requests\StoreTestDriveRequest;use App\Models\{Mobil,TestDrive};use App\Services\TestDriveService;use Illuminate\Http\Request;use Throwable;
class TestDriveController extends Controller {
 private function customer(Request $r):int{return (int)$r->user()->pelanggan()->value('id_pelanggan');}
 public function index(Request $r){$rows=TestDrive::with('mobil')->where('id_pelanggan',$this->customer($r))->latest()->paginate(15);return view('pelanggan.test-drive.index',compact('rows'));}
 public function create(Request $r){$mobil=Mobil::whereIn('status',['tersedia','dibooking'])->findOrFail((int)$r->query('mobil_id'));return view('pelanggan.test-drive.create',compact('mobil'));}
 public function store(StoreTestDriveRequest $req,TestDriveService $svc){try{$d=$req->validated();$svc->create($this->customer($req),(int)$d['id_mobil'],$d['tanggal'],$d['jam'],$d['lokasi']??null,$d['catatan']??null);return redirect()->route('pelanggan.test-drive.index')->with('success','Test drive berhasil diajukan.');}catch(Throwable $e){return back()->withInput()->withErrors($e->getMessage());}}
 public function show(Request $r,TestDrive $test_drive){abort_unless($test_drive->id_pelanggan===$this->customer($r),403);$test_drive->load('mobil');return view('pelanggan.test-drive.show',compact('test_drive'));}
 public function cancel(Request $r,TestDrive $test_drive,TestDriveService $svc){abort_unless($test_drive->id_pelanggan===$this->customer($r),403);try{$svc->cancel($test_drive->id_test_drive,$this->customer($r));return back()->with('success','Test drive dibatalkan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
