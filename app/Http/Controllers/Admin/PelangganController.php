<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePelangganRequest;
use App\Models\Pelanggan;
use App\Models\Membership;
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
 public function index(Request $request): View
 {
  $query = Pelanggan::query()
   ->with(['user', 'membership'])
   ->withMax('bookings as latest_bookings_created_at', 'created_at')
   ->withMax('testDrives as latest_test_drives_created_at', 'created_at')
   ->withMax('bookingServis as latest_booking_servis_created_at', 'created_at')
   ->withMax('bookingCuci as latest_booking_cuci_created_at', 'created_at')
   ->withMax('servis as latest_servis_created_at', 'created_at')
   ->withMax('transaksi as latest_transaksi_created_at', 'created_at')
   ->withMax('penjualanMobil as latest_penjualan_mobil_created_at', 'created_at')
   ->withMax('pengajuanMobil as latest_pengajuan_mobil_created_at', 'created_at');

  $search = trim((string) $request->string('q'));
  $query->when($search !== '', function ($builder) use ($search): void {
   $builder->where(function ($nested) use ($search): void {
    $value = '%'.$search.'%';
    $nested->where('nama_lengkap', 'like', $value)
     ->orWhere('kode_pelanggan', 'like', $value)
     ->orWhere('no_hp', 'like', $value)
     ->orWhereHas('user', fn ($user) => $user->where('email', 'like', $value));
   });
  });
  $query->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
   ->when($request->filled('from'), fn ($builder) => $builder->whereDate('created_at', '>=', $request->date('from')))
   ->when($request->filled('to'), fn ($builder) => $builder->whereDate('created_at', '<=', $request->date('to')));

  $completeProfiles = Pelanggan::query()
   ->whereNotNull('no_hp')->where('no_hp', '<>', '')
   ->where(function ($builder): void {
    $builder->where(function ($address): void {
     $address->whereNotNull('alamat')->where('alamat', '<>', '');
    })->orWhere(function ($address): void {
     $address->whereNotNull('address_detail')->where('address_detail', '<>', '');
    });
   })
   ->whereHas('user', fn ($user) => $user->whereNotNull('email')->where('email', '<>', ''))
   ->count();

  $today = now()->toDateString();
  $metrics = [
   'total' => Pelanggan::count(),
   'active' => Pelanggan::where('status', 'aktif')->count(),
   'members' => Membership::where('status', 'active')->count(),
   'complete' => $completeProfiles,
   'followUpAvailable' => false,
   'newToday' => Pelanggan::whereDate('created_at', $today)->count(),
   'newMembersToday' => Membership::where('status', 'active')->whereDate('registered_at', $today)->count(),
  ];

  return view('admin.pelanggan.index', [
   'items' => $query->latest('created_at')->paginate(10)->withQueryString(),
   'metrics' => $metrics,
  ]);
 }
 public function show(Pelanggan $pelanggan): View { return view('admin.pelanggan.show',['item'=>$pelanggan->load(['user','kendaraan'])]); }
 public function edit(Pelanggan $pelanggan): View { return view('admin.pelanggan.edit',['item'=>$pelanggan->load('user')]); }
 public function update(Request $request, Pelanggan $pelanggan, MasterAuditService $audit): RedirectResponse { $data=$request->validate(['nama_lengkap'=>['required','string','max:120'],'no_hp'=>['required','string','max:25'],'jenis_kelamin'=>['nullable','in:L,P'],'tanggal_lahir'=>['nullable','date'],'alamat'=>['nullable','string'],'email'=>['nullable','email','max:120']]); DB::transaction(function()use($data,$pelanggan,$audit){$email=$data['email']??null;unset($data['email']);$pelanggan->update($data);if($email!==null)$pelanggan->user?->update(['email'=>$email]);$audit->record('update','pelanggan',$pelanggan->id_pelanggan,'Admin mengubah pelanggan');}); return redirect()->route('admin.pelanggan.show',$pelanggan)->with('success','Pelanggan berhasil diperbarui.'); }
 public function toggle(Pelanggan $pelanggan, MasterAuditService $audit): RedirectResponse { DB::transaction(function()use($pelanggan,$audit){$target=$pelanggan->status==='aktif'?'nonaktif':'aktif';$pelanggan->update(['status'=>$target]);if($pelanggan->user)$pelanggan->user->update(['status'=>$target]);$audit->record('status','pelanggan',$pelanggan->id_pelanggan,'Status pelanggan menjadi '.$target);}); return back()->with('success','Status pelanggan diperbarui.'); }
}
