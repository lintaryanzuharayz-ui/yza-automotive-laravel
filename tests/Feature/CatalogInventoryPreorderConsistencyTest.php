<?php
namespace Tests\Feature;

use App\Models\{MemberVehiclePreorder, Mobil, Notifikasi, User};
use App\Services\{ActiveMemberCustomerService, AdminNotificationBadgeService, CatalogVehicleIdentityService, MemberVehiclePreorderService, PreorderManagementService, VehicleAvailabilityService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogInventoryPreorderConsistencyTest extends TestCase
{
    use DatabaseTransactions;
    private function actor(string $role='admin'): User { return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail(); }
    private function registry(): CatalogVehicleIdentityService { return app(CatalogVehicleIdentityService::class); }
    private function interest(string $slug='bugatti-chiron'): MemberVehiclePreorder
    {
        return app(MemberVehiclePreorderService::class)->create(app(ActiveMemberCustomerService::class)->query()->firstOrFail(),$this->registry()->resolve('showcase:'.$slug));
    }
    private function unitData(string $key='showcase:bugatti-chiron',array $changes=[]): array
    {
        $model=$this->registry()->resolve($key);$id=bin2hex(random_bytes(5));
        return array_replace(['alasan_stok'=>'QA verifikasi unit','bukti_stok'=>'BAST-QA-'.$id,'catalog_identity'=>$key,'kode_mobil'=>'CAT-QA-'.$id,'merk'=>$model->merk,'model'=>$model->model,
            'tipe'=>$model->tipe,'tahun'=>$model->tahun,'warna'=>$model->warna,'transmisi'=>$model->transmisi,
            'bahan_bakar'=>$model->bahan_bakar,'kondisi'=>$model->kondisi,'status'=>'dalam_pemeriksaan',
            'nomor_rangka'=>'QA-VIN-'.$id,'nomor_mesin'=>'QA-ENGINE-'.$id,'kilometer'=>0,'harga_beli'=>0,'harga_jual'=>$model->harga_jual],$changes);
    }
    public function test_all_configured_catalog_models_resolve_in_admin_without_creating_stock_or_masters(): void
    {
        $before=array_map(fn($t)=>DB::table($t)->count(),['mobil','vehicle_inventories','vehicle_inventory_histories']);
        foreach (array_keys(config('vehicle_showcases')) as $slug) {
            $key='showcase:'.$slug;$model=$this->registry()->resolve($key);$this->assertNotNull($model);
            $response=$this->actingAs($this->actor())->get(route('admin.mobil.index',['catalog'=>$key]))->assertOk();
            $response->assertSee($model->merk.' '.$model->model)->assertSee('Belum Ada Unit Fisik')->assertSee('Tambahkan Unit ke Inventori');
            $this->assertSame(0,$response->viewData('cars')->total());
            $this->assertSame(1,$response->viewData('virtualCatalogCars')->count());
            $this->assertFalse($model->exists);$this->assertNull($model->id_mobil);$this->assertNull($model->nomor_rangka);
            $this->assertSame(0,(new VehicleAvailabilityService)->for($model)['stock']);
            $this->get(route('admin.mobil.index',['tab'=>'katalog','catalog'=>$key]))->assertOk()->assertSee($model->merk.' '.$model->model);
        }
        $this->assertSame($before,array_map(fn($t)=>DB::table($t)->count(),['mobil','vehicle_inventories','vehicle_inventory_histories']));
    }
    public function test_bugatti_preorder_deep_link_uses_stable_identity_and_preserves_context(): void
    {
        $item=$this->interest()->fresh();$before=$item->getRawOriginal();
        $detail=$this->actingAs($this->actor())->getJson(route('admin.preorder-mobil.detail',$item))->assertOk()->json();
        parse_str(parse_url($detail['stock_management_url'],PHP_URL_QUERY),$query);
        $this->assertSame('showcase:bugatti-chiron',$query['catalog']);$this->assertArrayNotHasKey('q',$query);
        $this->assertSame((string)$item->getKey(),$query['preorder']);
        $this->get($detail['stock_management_url'])->assertOk()->assertSee('Bugatti Chiron')->assertSee('Belum Ada Unit Fisik')->assertDontSee('Tidak Ada Mobil Ditemukan');
        $this->assertSame($before,$item->fresh()->getRawOriginal());$this->assertNull($item->fresh()->id_mobil);
    }
    public function test_no_unit_form_prefills_model_but_never_vin_engine_or_unit_code(): void
    {
        $response=$this->actingAs($this->actor())->get(route('admin.mobil.create',['catalog'=>'showcase:bugatti-chiron']))->assertOk();
        $car=$response->viewData('mobil');$this->assertSame('Bugatti',$car->merk);$this->assertSame('Chiron',$car->model);
        foreach (['kode_mobil','nomor_rangka','nomor_mesin'] as $field) $this->assertNull($car->$field);
        $this->assertSame('dalam_pemeriksaan',$car->status);$this->assertFalse($car->exists);
        $this->post(route('admin.mobil.store'),['catalog_identity'=>'showcase:bugatti-chiron'])->assertSessionHasErrors(['kode_mobil','nomor_rangka','nomor_mesin']);
    }
    public function test_physical_unit_is_explicitly_linked_and_only_verified_eligible_stock_notifies(): void
    {
        $item=$this->interest();$data=$this->unitData();$count=Mobil::count();
        $this->actingAs($this->actor())->post(route('admin.mobil.store'),$data)->assertRedirect();
        $car=Mobil::where('kode_mobil',$data['kode_mobil'])->sole();$this->assertSame($count+1,Mobil::count());
        $this->assertSame('showcase:bugatti-chiron',$car->catalog_identity);
        $this->assertSame('showcase:bugatti-chiron',$this->registry()->key($this->registry()->resolve('showcase:bugatti-chiron')));
        $this->assertFalse(app(PreorderManagementService::class)->matchingStock($item->fresh())['available']);
        $notices=fn()=>Notifikasi::where('referensi_tipe','pre_order_mobil')->where('referensi_id',$item->getKey())->where('tipe','stok')->count();
        $this->assertSame(0,$notices());
        $this->put(route('admin.mobil.update',$car),array_replace($data,['status'=>'tersedia']))->assertRedirect();
        $this->assertTrue(app(PreorderManagementService::class)->matchingStock($item->fresh())['available']);$this->assertSame(1,$notices());
        app(MemberVehiclePreorderService::class)->notifyForRestock($car->fresh());
        $this->assertSame(1,$notices());$this->assertNull($item->fresh()->id_pesanan);
        $this->assertSame($item->harga_referensi,$item->fresh()->harga_referensi);
    }
    public function test_wrong_color_is_not_stock_for_requested_color_and_other_catalog_identity_is_rejected(): void
    {
        $item=$this->interest();$data=$this->unitData('showcase:bugatti-chiron',['warna'=>'Racing Red','status'=>'tersedia']);
        $this->actingAs($this->actor())->post(route('admin.mobil.store'),$data)->assertRedirect();
        $this->assertFalse(app(PreorderManagementService::class)->matchingStock($item->fresh())['available']);
        $before=Mobil::count();
        $this->post(route('admin.mobil.store'),$this->unitData('showcase:bugatti-chiron',['model'=>'Different Model']))->assertSessionHasErrors('model');
        $this->assertSame($before,Mobil::count());
        $blue=$this->unitData('showcase:bugatti-chiron',['warna'=>'Atlantic Blue','status'=>'tersedia']);
        $this->post(route('admin.mobil.store'),$blue)->assertRedirect();
        $stock=app(PreorderManagementService::class)->matchingStock($item->fresh());
        $this->assertTrue($stock['available']);$this->assertSame(1,$stock['total']);$this->assertSame('Atlantic Blue',$stock['units'][0]['color']);
        $this->assertSame(1,Notifikasi::where('referensi_tipe','pre_order_mobil')->where('referensi_id',$item->getKey())->where('tipe','stok')->count());
    }
    public function test_kpis_and_sidebar_share_the_active_preorder_definition(): void
    {
        $before=MemberVehiclePreorder::active()->count();$base=$this->interest();
        foreach (['stok_tersedia','notifikasi_terkirim','dibatalkan','dilanjutkan_ke_checkout'] as $status) {
            $copy=$base->replicate();$copy->forceFill(['product_key'=>'QA-'.$status,'status'=>$status])->save();
        }
        $expected=$before+3;$this->assertSame($expected,MemberVehiclePreorder::active()->count());
        $this->assertSame($expected,app(AdminNotificationBadgeService::class)->for($this->actor()->getKey())['preorders']);
        $this->actingAs($this->actor())->get(route('admin.mobil.index'))->assertOk()->assertViewHas('stats',fn($stats)=>$stats['preorder']===$expected);
        $this->get(route('admin.preorder-mobil.index'))->assertOk()->assertViewHas('stats',fn($stats)=>$stats['active']===$expected);
    }
    public function test_unknown_canonical_identifier_and_unauthorized_unit_creation_are_rejected(): void
    {
        $this->actingAs($this->actor())->get(route('admin.mobil.index',['catalog'=>'showcase:missing-model']))->assertNotFound();
        $this->get(route('admin.mobil.create',['catalog'=>'mobil:999999999']))->assertNotFound();
        foreach (['owner','finance','pelanggan'] as $role) $this->actingAs($this->actor($role))->post(route('admin.mobil.store'),$this->unitData())->assertForbidden();
    }
    public function test_search_supports_full_brand_model_and_does_not_return_an_unrelated_model(): void
    {
        $response=$this->actingAs($this->actor())->get(route('admin.mobil.index',['q'=>'Bugatti Chiron']))->assertOk();
        $this->assertSame(['showcase:bugatti-chiron'],$response->viewData('catalogCars')->map(fn($c)=>$this->registry()->key($c))->all());
        $this->assertSame(0,$response->viewData('cars')->total());
    }
    public function test_binding_validation_rejects_invalid_aliases_and_supports_first_unit_without_backfill(): void
    {
        $data=$this->unitData();$before=Mobil::count();
        \Illuminate\Support\Facades\Schema::shouldReceive('hasColumn')->with('mobil','catalog_identity')->andReturn(false);
        $bound=$this->registry()->bindNewUnit($data);
        $this->assertArrayNotHasKey('catalog_identity',$bound);$this->assertSame('bugatti-chiron',$bound['showcase_slug']);
        $this->assertSame($data['nomor_rangka'],$bound['nomor_rangka']);$this->assertSame($before,Mobil::count());
    }
}
