<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePetugasRequest;
use App\Http\Requests\UpdatePetugasRequest;
use App\Models\Petugas;
use App\Models\Role;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
class PetugasController extends Controller
{
 public function index(Request $request): View
 {
  $q = trim((string) $request->query('q'));
  $jabatan = trim((string) $request->query('jabatan'));
  $status = trim((string) $request->query('status'));
  $perPage = (int) $request->query('per_page', 5);
  $perPage = in_array($perPage, [5, 10, 20], true) ? $perPage : 5;
  $query = Petugas::with('user')
   ->withMax('confirmedBookings as latest_booking_at', 'created_at')
   ->withMax('testDrives as latest_test_drive_at', 'created_at')
   ->withMax('bookingCuci as latest_booking_cuci_at', 'created_at')
   ->withMax('servis as latest_servis_at', 'created_at')
   ->withMax('transaksi as latest_transaksi_at', 'created_at')
   ->withMax('penjualanMobil as latest_sale_at', 'created_at')
   ->withMax('inspeksiMobil as latest_inspection_at', 'created_at')
   ->when($q !== '', function ($builder) use ($q): void {
    $builder->where(function ($search) use ($q): void {
     $value = '%'.$q.'%';
     $search->where('kode_petugas', 'like', $value)
      ->orWhere('nama_lengkap', 'like', $value)
      ->orWhere('jabatan', 'like', $value);
    });
   })
   ->when($jabatan !== '', fn ($builder) => $builder->where('jabatan', $jabatan))
   ->when($status !== '', fn ($builder) => $builder->where('status', $status));

  $today = now()->toDateString();
  $activityToday = Petugas::query()->whereDate('created_at', $today)->count()
   + \App\Models\BookingMobil::whereDate('created_at', $today)->whereNotNull('id_petugas_konfirmasi')->count()
   + \App\Models\TestDrive::whereDate('created_at', $today)->whereNotNull('id_petugas')->count()
   + \App\Models\BookingCuci::whereDate('created_at', $today)->whereNotNull('id_petugas')->count()
   + \App\Models\Servis::whereDate('created_at', $today)->whereNotNull('id_petugas')->count()
   + \App\Models\Transaksi::whereDate('created_at', $today)->whereNotNull('id_petugas')->count()
   + \App\Models\InspeksiMobil::whereDate('created_at', $today)->whereNotNull('id_petugas')->count();

  $total = Petugas::count();
  $metrics = [
   'total' => $total,
   'active' => Petugas::where('status', 'aktif')->count(),
   'roles' => Petugas::whereNotNull('jabatan')->where('jabatan', '<>', '')->distinct('jabatan')->count('jabatan'),
   'readyToday' => null,
   'review' => null,
   'activityToday' => $activityToday,
   'onDuty' => null,
   'newToday' => Petugas::whereDate('created_at', $today)->count(),
   'hrFollowUp' => null,
  ];
  $distribution = Petugas::query()->select('jabatan')->selectRaw('COUNT(*) as total')->whereNotNull('jabatan')->where('jabatan', '<>', '')->groupBy('jabatan')->orderByDesc('total')->get()->map(fn ($item) => [
   'name' => $item->jabatan,
   'total' => (int) $item->total,
   'percentage' => $total > 0 ? round(((int) $item->total / $total) * 100) : 0,
  ]);

  return view('admin.petugas.index', [
   'items' => $query->latest('created_at')->paginate($perPage)->withQueryString(),
   'q' => $q,
   'jabatan' => $jabatan,
   'status' => $status,
   'perPage' => $perPage,
   'jabatanOptions' => Petugas::whereNotNull('jabatan')->where('jabatan', '<>', '')->distinct()->orderBy('jabatan')->pluck('jabatan'),
   'metrics' => $metrics,
   'distribution' => $distribution,
  ]);
 }
 public function create(): View { return view('admin.petugas.create',['item'=>new Petugas]); }
 public function store(StorePetugasRequest $request, MasterAuditService $audit): RedirectResponse { $data=$request->validated(); $p=DB::transaction(function()use($data,$audit){$rid=Role::where('nama_role','petugas')->value('id_role');abort_unless($rid,422);$uid=DB::table('users')->insertGetId(['id_role'=>$rid,'username'=>$data['username'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]);$pid=DB::table('petugas')->insertGetId(['id_user'=>$uid,'kode_petugas'=>'PTG-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3))),'nama_lengkap'=>$data['nama_lengkap'],'no_hp'=>$data['no_hp'],'alamat'=>$data['alamat']??null,'jabatan'=>$data['jabatan'],'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]);$audit->record('create','petugas',$pid,'Admin membuat petugas');return $pid;}); return redirect()->route('admin.petugas.show',$p)->with('success','Petugas berhasil ditambahkan.'); }
 public function show(Petugas $petugas): View { return view('admin.petugas.show',['item'=>$petugas->load('user')]); }
 public function edit(Petugas $petugas): View { return view('admin.petugas.edit',['item'=>$petugas->load('user')]); }
 public function update(UpdatePetugasRequest $request, Petugas $petugas, MasterAuditService $audit): RedirectResponse { $data=$request->validated();DB::transaction(function()use($data,$petugas,$audit){$petugas->user->update(['username'=>$data['username'],'email'=>$data['email']]);$petugas->update(['nama_lengkap'=>$data['nama_lengkap'],'no_hp'=>$data['no_hp']??null,'alamat'=>$data['alamat']??null,'jabatan'=>$data['jabatan']]);$audit->record('update','petugas',$petugas->id_petugas,'Admin mengubah petugas');});return redirect()->route('admin.petugas.show',$petugas)->with('success','Petugas berhasil diperbarui.'); }
 public function toggle(Petugas $petugas, MasterAuditService $audit): RedirectResponse { DB::transaction(function()use($petugas,$audit){$target=$petugas->status==='aktif'?'nonaktif':'aktif';$petugas->update(['status'=>$target]);$petugas->user?->update(['status'=>$target]);$audit->record('status','petugas',$petugas->id_petugas,'Status petugas menjadi '.$target);});return back()->with('success','Status petugas diperbarui.'); }
}
