<?php
namespace App\Http\Controllers;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Services\{IndonesiaRegionService, MasterAuditService, MemberIdentityService, PengaturanService};
use App\Services\YzaPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
class RegistrationController extends Controller
{
 public function create(): View { return view('auth.register'); }
 public function regions(string $level, ?string $parent = null): \Illuminate\Http\JsonResponse { abort_unless(in_array($level, ['provinces','cities','districts','villages'], true), 404); return response()->json(['data' => app(IndonesiaRegionService::class)->children($level, $parent)]); }
 public function store(RegisterRequest $request, MasterAuditService $audit, YzaPointService $points, PengaturanService $settings, MemberIdentityService $identity, IndonesiaRegionService $regions): RedirectResponse { $data=$request->validated(); $hierarchy=$regions->resolveHierarchy($data); abort_unless($hierarchy, 422, 'Kombinasi wilayah tidak valid.'); $uid=DB::transaction(function() use($data,$hierarchy,$audit,$points,$settings,$identity){$role=Role::where('nama_role','pelanggan')->firstOrFail(); $uid=DB::table('users')->insertGetId(['id_role'=>$role->id_role,'username'=>$data['username'],'email'=>$data['email'],'password'=>Hash::make($data['password']),'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]); $address=collect([$data['address_detail']??null,$hierarchy['village']['name'],$hierarchy['district']['name'],$hierarchy['city']['name'],$hierarchy['province']['name']])->filter()->implode(', '); $pid=DB::table('pelanggan')->insertGetId(['id_user'=>$uid,'kode_pelanggan'=>'PLG-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3))),'nama_lengkap'=>$data['nama_lengkap'],'jenis_kelamin'=>$data['jenis_kelamin'],'no_hp'=>$data['no_hp'],'alamat'=>$address,'province_code'=>$hierarchy['province']['code'],'province_name'=>$hierarchy['province']['name'],'city_code'=>$hierarchy['city']['code'],'city_name'=>$hierarchy['city']['name'],'district_code'=>$hierarchy['district']['code'],'district_name'=>$hierarchy['district']['name'],'village_code'=>$hierarchy['village']['code'],'village_name'=>$hierarchy['village']['name'],'address_detail'=>$data['address_detail']??null,'tanggal_lahir'=>$data['tanggal_lahir'],'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]); $customer=\App\Models\Pelanggan::findOrFail($pid); $points->ensureAccount($customer); DB::table('memberships')->insert(['id_pelanggan'=>$pid,'member_id'=>$identity->memberCode($customer),'status'=>'pending_payment','activation_fee'=>$settings->membershipFee(),'registered_at'=>now(),'created_at'=>now(),'updated_at'=>now()]); $audit->record('create','pelanggan',$pid,'Registrasi YZA Member pending payment'); return $uid; }); Auth::loginUsingId($uid); $request->session()->regenerate(); return redirect()->route('pelanggan.membership.activation')->with('registered','Data diri dan alamat tersimpan. Lanjutkan pembayaran untuk mengaktifkan YZA Member.'); }
}
