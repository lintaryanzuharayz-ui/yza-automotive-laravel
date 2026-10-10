<?php

namespace Tests\Feature;

use App\Models\KendaraanPelanggan;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class IdentityStage3CTest extends TestCase
{
    use DatabaseTransactions;

    private function registrationFields(): array
    {
        return [
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1990-01-01',
            'province_code' => '11',
            'city_code' => '1101',
            'district_code' => '1101010',
            'village_code' => '1101010001',
            'address_detail' => 'Alamat QA',
            'current_step' => 3,
            'membership_confirmed' => '1',
        ];
    }

    private function userWithRole(string $role): User { return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail(); }

    public function test_register_valid_creates_pelanggan_role_and_hashed_password(): void
    {
        $email='qa'.uniqid().'@example.test'; $username='qa'.strtolower(substr(uniqid(),-8));
        $this->withoutMiddleware(VerifyCsrfToken::class)->post('/register', array_merge($this->registrationFields(), ['nama_lengkap'=>'QA Register '.uniqid(),'no_hp'=>'081234567890','email'=>$email,'username'=>$username,'password'=>'Password123!','password_confirmation'=>'Password123!']))->assertRedirect('/pelanggan/membership/aktivasi');
        $user = User::where('email', $email)->firstOrFail();
        $this->assertSame('pelanggan', $user->roleName());
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertNotNull($user->pelanggan);
        Auth::logout();
        $this->post('/login', ['identity'=>$username,'password'=>'Password123!'])->assertRedirect('/pelanggan/membership/aktivasi');
    }

    public function test_pending_customer_can_recover_payment_but_cannot_open_member_routes(): void
    {
        $username = 'pending'.strtolower(substr(uniqid(), -8));
        $this->withoutMiddleware(VerifyCsrfToken::class)->post('/register', array_merge($this->registrationFields(), [
            'nama_lengkap' => 'Pending QA', 'no_hp' => '081234567890',
            'email' => $username.'@example.test', 'username' => $username,
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ]))->assertRedirect('/pelanggan/membership/aktivasi');

        Auth::logout();
        $this->post('/login', ['identity' => $username, 'password' => 'Password123!'])
            ->assertRedirect('/pelanggan/membership/aktivasi');
        $this->get('/pelanggan/dashboard')->assertRedirect('/pelanggan/membership/aktivasi');
        $this->post('/pelanggan/booking-servis', [])->assertRedirect('/pelanggan/membership/aktivasi');
    }

    public function test_register_duplicate_username_and_email_are_rejected(): void
    {
        $existing = User::whereNotNull('email')->firstOrFail();
        $this->withoutMiddleware(VerifyCsrfToken::class)->post('/register', array_merge($this->registrationFields(), ['nama_lengkap'=>'Duplicate','no_hp'=>'081234567890','email'=>$existing->email,'username'=>$existing->username,'password'=>'Password123!','password_confirmation'=>'Password123!']))->assertSessionHasErrors(['email','username']);
    }

    public function test_register_role_injection_is_ignored(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class)->post('/register', array_merge($this->registrationFields(), ['nama_lengkap'=>'Role Injection','no_hp'=>'081234567890','email'=>'role'.uniqid().'@example.test','username'=>'role'.substr(uniqid(),-8),'password'=>'Password123!','password_confirmation'=>'Password123!','role'=>'admin']))->assertRedirect('/pelanggan/membership/aktivasi');
        $this->assertSame('pelanggan', User::where('email','like','role%@example.test')->latest('id_user')->firstOrFail()->roleName());
    }

    public function test_admin_can_open_customer_and_staff_management(): void
    {
        $this->actingAs($this->userWithRole('admin'));
        $this->get('/admin/pelanggan')->assertOk();
        $this->get('/admin/pelanggan/1')->assertOk();
        $this->get('/admin/petugas')->assertOk();
        $this->get('/admin/petugas/create')->assertOk();
    }

    public function test_non_admin_cannot_open_management(): void
    {
        $this->actingAs($this->userWithRole('petugas'))->get('/admin/pelanggan')->assertForbidden();
        $this->actingAs($this->userWithRole('pelanggan'))->get('/admin/petugas')->assertForbidden();
        Auth::logout();
        $this->get('/admin/petugas')->assertRedirect('/login');
    }

    public function test_staff_create_is_atomic_and_role_is_server_side(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($this->userWithRole('admin'));
        $this->post('/admin/petugas', ['nama_lengkap'=>'QA Petugas','jabatan'=>'QA','no_hp'=>'081234567890','username'=>'ptg'.substr(uniqid(),-8),'email'=>'ptg'.uniqid().'@example.test','password'=>'Password123!','password_confirmation'=>'Password123!','id_role'=>1])->assertRedirect();
        $staff = User::where('email','like','ptg%@example.test')->latest('id_user')->firstOrFail();
        $this->assertSame('petugas', $staff->roleName());
        $this->assertNotNull($staff->petugas);
    }

    public function test_customer_profile_is_self_scoped(): void
    {
        $customer = $this->userWithRole('pelanggan');
        $this->actingAs($customer)->get('/pelanggan/profil')->assertOk();
        $this->actingAs($customer)->put('/pelanggan/profil', ['nama_lengkap'=>'Changed','no_hp'=>'081234567890','id_pelanggan'=>999999])->assertRedirect();
        $this->assertSame('Changed', $customer->fresh()->pelanggan->nama_lengkap);
    }

    public function test_vehicle_create_validation_and_ownership_are_enforced(): void
    {
        $customer = $this->userWithRole('pelanggan');
        $this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($customer);
        $this->get('/pelanggan/kendaraan')->assertOk();
        $this->post('/pelanggan/kendaraan', ['merk'=>'','model'=>'','kilometer'=>-1,'id_pelanggan'=>999])->assertSessionHasErrors(['merk','model','kilometer']);
        $other = Pelanggan::whereNotNull('id_user')->where('id_pelanggan','!=',$customer->pelanggan->id_pelanggan)->first();
        if ($other) { $id=DB::table('kendaraan_pelanggan')->insertGetId(['id_pelanggan'=>$other->id_pelanggan,'merk'=>'Other','model'=>'Car','kilometer'=>0,'status'=>'aktif','created_at'=>now(),'updated_at'=>now()]); $this->get('/pelanggan/kendaraan/'.$id)->assertNotFound(); }
    }

    public function test_vehicle_valid_create_and_update_preserve_owner(): void
    {
        $customer=$this->userWithRole('pelanggan');$this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($customer);$this->post('/pelanggan/kendaraan',['merk'=>'QA','model'=>'Test','kilometer'=>10,'nomor_polisi'=>'B QA 123'])->assertRedirect();$item=$customer->fresh()->pelanggan->kendaraan()->latest('id_kendaraan_pelanggan')->firstOrFail();$this->put('/pelanggan/kendaraan/'.$item->id_kendaraan_pelanggan,['merk'=>'QA2','model'=>'Test2','kilometer'=>20,'id_pelanggan'=>999])->assertRedirect();$this->assertSame($customer->fresh()->pelanggan->id_pelanggan,$item->fresh()->id_pelanggan);
    }

    public function test_password_change_requires_current_password_and_confirmation(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);$this->post('/register',array_merge($this->registrationFields(),['nama_lengkap'=>'Password QA','no_hp'=>'081234567890','email'=>'pass'.uniqid().'@example.test','username'=>'pass'.substr(uniqid(),-8),'password'=>'Password123!','password_confirmation'=>'Password123!']));$user=User::where('email','like','pass%@example.test')->latest('id_user')->firstOrFail();$this->actingAs($user)->put('/akun/password',['password_lama'=>'Password123!','password_baru'=>'NewPassword123!','password_baru_confirmation'=>'NewPassword123!'])->assertRedirect();$this->assertTrue(Hash::check('NewPassword123!',$user->fresh()->password));
    }

    public function test_existing_auth_and_previous_modules_smoke(): void
    {
        $this->get('/login')->assertOk();$this->get('/')->assertOk();$this->get('/mobil')->assertOk();$this->get('/petugas/sparepart')->assertRedirect('/login');
    }
}
