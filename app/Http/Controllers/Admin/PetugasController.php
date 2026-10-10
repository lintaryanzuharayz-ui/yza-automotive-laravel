<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePetugasRequest;
use App\Http\Requests\UpdatePetugasRequest;
use App\Models\Petugas;
use App\Models\Role;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class PetugasController extends Controller
{
 public function index(): View { return view('admin.petugas.index',['items'=>Petugas::with('user')->when(request('q'),fn($q,$v)=>$q->where('nama_lengkap','like','%'.$v.'%')->orWhere('kode_petugas','like','%'.$v.'%'))->latest()->paginate(10)->withQueryString()]); }
 public function create(): View { return view('admin.petugas.create',['item'=>new Petugas]); }
 public function store(StorePetugasRequest $request, MasterAuditService $audit): RedirectResponse { $data=$request->validated(); $p=DB::transaction(function()use($data,$audit){$rid=Role::where('nama_role','petugas')->value('id_role');abort_unless($rid,422);$uid=DB::table('users')->insertGetId(['id_role'=>$rid,'username'=>$data['username'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]);$pid=DB::table('petugas')->insertGetId(['id_user'=>$uid,'kode_petugas'=>'PTG-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3))),'nama_lengkap'=>$data['nama_lengkap'],'no_hp'=>$data['no_hp'],'alamat'=>$data['alamat']??null,'jabatan'=>$data['jabatan'],'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]);$audit->record('create','petugas',$pid,'Admin membuat petugas');return $pid;}); return redirect()->route('admin.petugas.show',$p)->with('success','Petugas berhasil ditambahkan.'); }
 public function show(Petugas $petugas): View { return view('admin.petugas.show',['item'=>$petugas->load('user')]); }
 public function edit(Petugas $petugas): View { return view('admin.petugas.edit',['item'=>$petugas->load('user')]); }
 public function update(UpdatePetugasRequest $request, Petugas $petugas, MasterAuditService $audit): RedirectResponse { $data=$request->validated();DB::transaction(function()use($data,$petugas,$audit){$petugas->user->update(['username'=>$data['username'],'email'=>$data['email']]);$petugas->update(['nama_lengkap'=>$data['nama_lengkap'],'no_hp'=>$data['no_hp']??null,'alamat'=>$data['alamat']??null,'jabatan'=>$data['jabatan']]);$audit->record('update','petugas',$petugas->id_petugas,'Admin mengubah petugas');});return redirect()->route('admin.petugas.show',$petugas)->with('success','Petugas berhasil diperbarui.'); }
 public function toggle(Petugas $petugas, MasterAuditService $audit): RedirectResponse { DB::transaction(function()use($petugas,$audit){$target=$petugas->status==='aktif'?'nonaktif':'aktif';$petugas->update(['status'=>$target]);$petugas->user?->update(['status'=>$target]);$audit->record('status','petugas',$petugas->id_petugas,'Status petugas menjadi '.$target);});return back()->with('success','Status petugas diperbarui.'); }
}
