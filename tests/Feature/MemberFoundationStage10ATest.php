<?php
namespace Tests\Feature;
use App\Models\{Pelanggan,User};
use App\Services\MemberDashboardService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
class MemberFoundationStage10ATest extends TestCase
{
 use DatabaseTransactions;
 private function user(string $role):User{return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail();}
 public function test_customer_can_open_member_home_with_shared_navbar():void{$u=$this->user('pelanggan');$r=$this->actingAs($u)->get('/pelanggan/member');$r->assertOk()->assertSee('YZA AUTOMOTIVE')->assertSee('Premium Member Portal')->assertSee('Koleksi Mobil')->assertSee('Kendaraan Saya')->assertSee('Selamat Datang Kembali')->assertSee('Layanan Eksklusif YZA')->assertSee('MY YZA GARAGE');}
 public function test_dashboard_alias_remains_available_with_member_home_content():void{$u=$this->user('pelanggan');$r=$this->actingAs($u)->get('/pelanggan/dashboard');$r->assertOk()->assertSee('YZA AUTOMOTIVE')->assertSee('Selamat Datang Kembali')->assertSeeText('Aktivitas & Jadwal Anda');$data=app(MemberDashboardService::class)->for($u->pelanggan,$u);$this->assertEquals($u->pelanggan->created_at,$data['memberSince']);}
 public function test_guest_admin_and_petugas_are_blocked():void{$this->get('/pelanggan/member')->assertRedirect('/login');$this->actingAs($this->user('admin'))->get('/pelanggan/member')->assertForbidden();$this->actingAs($this->user('petugas'))->get('/pelanggan/member')->assertForbidden();}
 public function test_member_home_renders_data_driven_sections():void{$u=$this->user('pelanggan');$r=$this->actingAs($u)->get('/pelanggan/member');$r->assertOk()->assertSeeText('KENDARAAN YZA')->assertSeeText('BOOKING AKTIF')->assertSeeText('YZA POINTS')->assertSeeText('PROMO MEMBER')->assertSeeText('Layanan Eksklusif YZA')->assertSeeText('MY YZA GARAGE')->assertSeeText('Keuntungan Eksklusif Membership');}
 public function test_zero_activity_member_renders_premium_empty_states():void{$u=User::whereHas('role',fn($q)=>$q->where('nama_role','pelanggan'))->whereDoesntHave('pelanggan.transaksi')->first();if(!$u){$this->markTestSkipped('No zero-activity customer fixture available.');}else{$this->actingAs($u)->get('/pelanggan/member')->assertOk()->assertSee('Selamat Datang Kembali')->assertSee('Belum ada aktivitas mendatang.');}}
 public function test_member_center_does_not_create_schema_or_expose_member_role():void{$this->assertFalse(Schema::hasTable('members'));$this->assertFalse(Schema::hasTable('points'));$this->assertFalse(Schema::hasTable('promo'));$this->assertFalse(Schema::hasTable('midtrans_orders'));$this->assertNull(\App\Models\Role::where('nama_role','member')->first());}
 public function test_profile_and_existing_customer_routes_remain_available():void{$u=$this->user('pelanggan');$this->actingAs($u)->get('/pelanggan/profil')->assertOk();$this->actingAs($u)->get('/pelanggan/kendaraan')->assertOk();$this->actingAs($u)->get('/pelanggan/trade-in')->assertOk();}
}
