<?php
namespace Tests\Feature;

use App\Models\{PromoMember,PromoSimulation,User};
use App\Services\{PromoEngineService,PromoSimulationService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Stage14FinalQaTest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role', fn($q)=>$q->where('nama_role',$role))->firstOrFail(); }
    private function promo(array $extra=[]): PromoMember { $p=new PromoMember();$p->forceFill(array_merge(['kode_promo'=>'S14-'.strtoupper(bin2hex(random_bytes(3))),'nama'=>'Stage 14 Promo','jenis_benefit'=>'persentase','nilai_benefit'=>17,'minimum_transaksi'=>0,'maksimum_benefit'=>null,'berlaku_untuk'=>'servis','mulai_at'=>now()->subMinute(),'selesai_at'=>now()->addDay(),'status'=>'aktif'],$extra))->save();return $p; }
    public function test_integer_promo_rounding_and_negative_total_are_safe(): void { $p=$this->promo(['jenis_benefit'=>'nominal','nilai_benefit'=>250000]);$customer=$this->user('pelanggan')->pelanggan;$r=app(PromoEngineService::class)->calculate($p,100000,'servis',$customer);$this->assertSame(100000,$r['discount']);$this->assertSame(0,$r['total_final']);$p=$this->promo(['nilai_benefit'=>17]);$r=app(PromoEngineService::class)->calculate($p,999999,'servis',$customer);$this->assertSame(169999,$r['discount']);$this->assertSame(830000,$r['total_final']); }
    public function test_spk_simulation_is_saved_and_can_seed_nonactive_promo_draft(): void { $admin=$this->user('admin');$response=$this->actingAs($admin)->post('/admin/promo-simulations',['scope'=>'servis','discount_type'=>'percentage','discount_value'=>10,'max_discount'=>100000,'min_transaction'=>0,'average_price'=>1000000,'estimated_cost'=>300000,'expected_transactions'=>10,'expected_sales_uplift'=>10]);$response->assertRedirect();$simulation=PromoSimulation::latest('id_promo_simulation')->firstOrFail();$this->assertSame('AMAN / LAYAK',$simulation->status);$this->actingAs($admin)->get('/admin/promo-member/from-simulation/'.$simulation->id_promo_simulation.'/create')->assertOk()->assertSee($simulation->simulation_id);$this->assertDatabaseHas('promo_simulations',['simulation_id'=>$simulation->simulation_id]); }
    public function test_spk_routes_are_admin_only_and_preview_is_authenticated(): void { $this->get('/promo-preview/calculate?promo_id=1&scope=servis&subtotal=100000')->assertRedirect('/login');$this->actingAs($this->user('petugas'))->get('/admin/promo-simulations')->assertForbidden();$this->actingAs($this->user('pelanggan'))->get('/admin/promo-simulations')->assertForbidden(); }
    public function test_promo_preview_does_not_mutate_redemptions(): void { $promo=$this->promo();$before=\App\Models\PromoRedemption::count();$this->actingAs($this->user('pelanggan'))->get('/promo-preview/calculate?promo_id='.$promo->id_promo.'&scope=servis&subtotal=999999')->assertOk()->assertJsonPath('discount',169999);$this->assertSame($before,\App\Models\PromoRedemption::count()); }
    public function test_simulation_service_remains_deterministic(): void { $input=['average_price'=>1000000,'estimated_cost'=>300000,'expected_transactions'=>10,'expected_sales_uplift'=>10,'discount_type'=>'percentage','discount_value'=>10,'max_discount'=>100000];$this->assertSame(app(PromoSimulationService::class)->simulate($input),app(PromoSimulationService::class)->simulate($input)); }
}
