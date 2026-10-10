<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePelangganRequest;
use App\Models\Pelanggan;
use App\Services\MasterAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class PelangganController extends Controller
{
 public function create(): View { return view('admin.pelanggan.create',['item'=>new Pelanggan]); }
 public function store(StorePelangganRequest $request, MasterAuditService $audit): RedirectResponse { $data=$request->validated();$item=DB::transaction(function()use($data,$audit){$uid=null;if(!empty($data['username'])||!empty($data['email'])||!empty($data['password'])){abort_unless(!empty($data['username'])&&!empty($data['email'])&&!empty($data['password']),422);$rid=DB::table('roles')->where('nama_role','pelanggan')->value('id_role');$uid=DB::table('users')->insertGetId(['id_role'=>$rid,'username'=>$data['username'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]);}$pid=DB::table('pelanggan')->insertGetId(['id_user'=>$uid,'kode_pelanggan'=>'PLG-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3))),'nama_lengkap'=>$data['nama_lengkap'],'jenis_kelamin'=>$data['jenis_kelamin']??null,'no_hp'=>$data['no_hp'],'alamat'=>$data['alamat']??null,'tanggal_lahir'=>$data['tanggal_lahir']??null,'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]);$audit->record('create','pelanggan',$pid,'Admin membuat pelanggan');return $pid;});return redirect()->route('admin.pelanggan.show',$item)->with('success','Pelanggan berhasil ditambahkan.'); }
 public function index(): View { return view('admin.pelanggan.index',['items'=>Pelanggan::with('user')->when(request('q'),fn($q,$v)=>$q->where('nama_lengkap','like','%'.$v.'%')->orWhere('kode_pelanggan','like','%'.$v.'%'))->when(request('status'),fn($q,$v)=>$q->where('status',$v))->latest()->paginate(10)->withQueryString()]); }
 public function show(Pelanggan $pelanggan): View { return view('admin.pelanggan.show',['item'=>$pelanggan->load(['user','kendaraan'])]); }
 public function edit(Pelanggan $pelanggan): View { return view('admin.pelanggan.edit',['item'=>$pelanggan->load('user')]); }
 public function update(Request $request, Pelanggan $pelanggan, MasterAuditService $audit): RedirectResponse { $data=$request->validate(['nama_lengkap'=>['required','string','max:120'],'no_hp'=>['required','string','max:25'],'jenis_kelamin'=>['nullable','in:L,P'],'tanggal_lahir'=>['nullable','date'],'alamat'=>['nullable','string'],'email'=>['nullable','email','max:120']]); DB::transaction(function()use($data,$pelanggan,$audit){$email=$data['email']??null;unset($data['email']);$pelanggan->update($data);if($email!==null)$pelanggan->user?->update(['email'=>$email]);$audit->record('update','pelanggan',$pelanggan->id_pelanggan,'Admin mengubah pelanggan');}); return redirect()->route('admin.pelanggan.show',$pelanggan)->with('success','Pelanggan berhasil diperbarui.'); }
 public function toggle(Pelanggan $pelanggan, MasterAuditService $audit): RedirectResponse { DB::transaction(function()use($pelanggan,$audit){$target=$pelanggan->status==='aktif'?'nonaktif':'aktif';$pelanggan->update(['status'=>$target]);if($pelanggan->user)$pelanggan->user->update(['status'=>$target]);$audit->record('status','pelanggan',$pelanggan->id_pelanggan,'Status pelanggan menjadi '.$target);}); return back()->with('success','Status pelanggan diperbarui.'); }
}
