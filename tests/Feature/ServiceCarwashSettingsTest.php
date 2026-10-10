<?php
namespace Tests\Feature;
use App\Models\JenisServis;
use App\Models\PaketCuci;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
class ServiceCarwashSettingsTest extends TestCase
{
 use DatabaseTransactions;
 private function userWithRole(string $role):User{return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail();}
 public function test_admin_can_open_service_and_package_masters():void{$this->actingAs($this->userWithRole('admin'));$this->get('/admin/jenis-servis')->assertOk();$this->get('/admin/jenis-servis/1')->assertOk();$this->get('/admin/paket-cuci')->assertOk();$this->get('/admin/paket-cuci/1')->assertOk();}
 public function test_service_crud_validation_and_toggle():void{$this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($this->userWithRole('admin'));$this->post('/admin/jenis-servis',['nama_servis'=>'QA Service','estimasi_harga'=>100,'status'=>'aktif'])->assertRedirect();$this->post('/admin/jenis-servis',['nama_servis'=>'Servis Berkala','estimasi_harga'=>-1,'status'=>'bad'])->assertSessionHasErrors(['nama_servis','estimasi_harga','status']);$item=JenisServis::firstOrFail();$this->put('/admin/jenis-servis/'.$item->id_jenis_servis,['nama_servis'=>$item->nama_servis,'estimasi_harga'=>200,'status'=>'nonaktif'])->assertRedirect();$this->patch('/admin/jenis-servis/'.$item->id_jenis_servis.'/toggle')->assertRedirect();}
 public function test_package_crud_validation_and_toggle():void{$this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($this->userWithRole('admin'));$this->post('/admin/paket-cuci',['nama_paket'=>'QA Wash','harga'=>100,'estimasi_menit'=>30,'status'=>'aktif'])->assertRedirect();$this->post('/admin/paket-cuci',['nama_paket'=>'Basic','harga'=>-1,'estimasi_menit'=>-2,'status'=>'invalid'])->assertSessionHasErrors(['nama_paket','harga','estimasi_menit','status']);$item=PaketCuci::firstOrFail();$this->put('/admin/paket-cuci/'.$item->id_paket,['nama_paket'=>$item->nama_paket,'harga'=>100,'estimasi_menit'=>40,'status'=>'nonaktif'])->assertRedirect();$this->patch('/admin/paket-cuci/'.$item->id_paket.'/toggle')->assertRedirect();}
 public function test_settings_are_whitelisted_and_validated():void{$this->withoutMiddleware(VerifyCsrfToken::class)->actingAs($this->userWithRole('admin'));$this->get('/admin/pengaturan')->assertOk();$this->put('/admin/pengaturan/nama_singkat',['nilai'=>'YZA QA'])->assertRedirect();$this->assertSame('YZA QA',Pengaturan::where('kunci_pengaturan','nama_singkat')->value('nilai'));$this->put('/admin/pengaturan/timezone',['nilai'=>'Mars/Unknown'])->assertSessionHasErrors('nilai');$this->put('/admin/pengaturan/arbitrary_key',['nilai'=>'x'])->assertNotFound();}
 public function test_non_admin_cannot_manage_masters_or_settings():void{$this->actingAs($this->userWithRole('petugas'))->get('/admin/jenis-servis')->assertForbidden();$this->actingAs($this->userWithRole('pelanggan'))->get('/admin/pengaturan')->assertForbidden();\Illuminate\Support\Facades\Auth::logout();$this->get('/admin/paket-cuci')->assertRedirect('/login');}
 public function test_previous_identity_mobil_and_sparepart_regression():void{$this->get('/login')->assertOk();$this->get('/')->assertOk();$this->get('/mobil')->assertOk();$this->actingAs($this->userWithRole('petugas'))->get('/petugas/sparepart')->assertOk();$this->actingAs($this->userWithRole('pelanggan'))->get('/pelanggan/kendaraan')->assertOk();}
}
