<?php

namespace Tests\Feature;

use App\Models\{Mobil,MobilWarna,MobilGaleri,PesananMobilMember,PdiPesananMobil,User};
use App\Services\{SmartSpkOrderService,VehicleInventoryService,MemberVehiclePurchaseService,VehiclePdiIntegrityService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleColorInventorySpkTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\PromoIntegrityFixtures;
    private function color(Mobil $car,string $name,string $slug): void
    {
        $color=MobilWarna::create(['id_mobil'=>$car->id_mobil,'nama'=>$name,'slug'=>$slug,'hex'=>'#ddbb00','is_default'=>false,'urutan'=>1]);
        MobilGaleri::create(['id_warna'=>$color->id_warna,'sudut'=>'front','path'=>'assets/images/vehicle-photo-unavailable.svg','urutan'=>0]);
    }
    private function colors(Mobil $car): array
    {
        $service=app(SmartSpkOrderService::class);$group=$service->vehicle($car)['group'];
        $catalog=$service->catalog($service->vehicles());
        return collect($catalog)->firstWhere('group',$group)['colors'];
    }
    private function duplicateCar(Mobil $car,array $changes=[]): Mobil
    {
        $copy=$car->replicate();$suffix=bin2hex(random_bytes(5));
        $copy->forceFill(array_replace(['kode_mobil'=>'COLOR-'.$suffix,'nomor_rangka'=>'VIN-'.$suffix,'nomor_mesin'=>'ENG-'.$suffix],$changes))->save();return$copy;
    }
    public function test_catalog_colors_without_stock_remain_visible_but_have_no_unit_ids(): void
    {
        $car=$this->stage4Car();$car->update(['warna'=>'QA Red']);$this->color($car,'QA Red','qa-red');$this->color($car,'QA Yellow','qa-yellow');
        $colors=collect($this->colors($car));$this->assertCount(2,$colors);
        $red=$colors->firstWhere('name','QA Red');$yellow=$colors->firstWhere('name','QA Yellow');
        $this->assertSame(1,$red['stock']);$this->assertTrue($red['available']);
        $this->assertFalse($yellow['available']);$this->assertSame(0,$yellow['stock']);$this->assertSame([],$yellow['unit_ids']);
        $this->assertFalse($yellow['can_preorder']);$this->assertSame('qa-yellow',$yellow['code']);
        $this->assertStringEndsWith('vehicle-photo-unavailable.svg',$yellow['image']);
    }
    public function test_same_color_individual_units_are_counted_separately_and_keep_unique_vins(): void
    {
        $car=$this->stage4Car();$second=$this->duplicateCar($car);$color=$this->colors($car)[0];
        $this->assertSame(2,$color['stock']);$this->assertSame(2,$color['identified_units']);$this->assertSame(0,$color['aggregate_stock']);
        $this->assertEqualsCanonicalizing([$car->id_mobil,$second->id_mobil],$color['unit_ids']);
        $spk=app(SmartSpkOrderService::class);$this->assertNotSame($spk->vehicle($car)['vin'],$spk->vehicle($second)['vin']);
    }
    public function test_different_inventory_colors_and_variants_are_not_mixed(): void
    {
        $car=$this->stage4Car();$car->update(['warna'=>'QA White']);$red=$this->duplicateCar($car,['warna'=>'QA Red']);
        $other=$this->duplicateCar($car,['warna'=>'QA Blue','tipe'=>'Another variant']);
        $colors=collect($this->colors($car));$this->assertCount(2,$colors);
        $this->assertSame([$red->id_mobil],$colors->firstWhere('name','QA Red')['unit_ids']);
        $this->assertFalse($colors->contains('name','QA Blue'));
        $service=app(SmartSpkOrderService::class);$this->assertSame($service->vehicle($car)['model_key'],$service->vehicle($other)['model_key']);
        $this->assertNotSame($service->vehicle($car)['group'],$service->vehicle($other)['group']);
    }
    public function test_legacy_aggregate_does_not_replace_two_real_physical_identities(): void
    {
        $car=$this->stage4Car();app(VehicleInventoryService::class)->ensure($car,2);$this->duplicateCar($car);
        $color=$this->colors($car)[0];$this->assertSame(2,$color['stock']);$this->assertSame(0,$color['aggregate_stock']);
        $this->assertSame(2,$color['identified_units']);$this->assertSame(2,$color['physical_units']);
        $this->assertSame($car->nomor_rangka,app(SmartSpkOrderService::class)->vehicle($car)['vin']);
    }
    public function test_reserved_sold_and_exhausted_units_are_not_eligible_but_catalog_survives(): void
    {
        $car=$this->stage4Car();$this->color($car,$car->warna,'qa-stock');
        $customer=User::whereHas('role',fn($q)=>$q->where('nama_role','pelanggan'))->firstOrFail()->pelanggan;
        app(MemberVehiclePurchaseService::class)->create($customer,$car,['nama_pembeli'=>'QA','telepon_pembeli'=>'08123','alamat_pembeli'=>'QA']);
        $color=$this->colors($car)[0];$this->assertFalse($color['available']);$this->assertSame([],$color['unit_ids']);
        $car->update(['status'=>'terjual']);$this->assertFalse($this->colors($car)[0]['available']);
        $another=$this->stage4Car();app(VehicleInventoryService::class)->ensure($another,0);
        $this->assertSame(1,$this->colors($another)[0]['stock']);
    }
    public function test_quote_rejects_catalog_only_color_and_does_not_create_preorder(): void
    {
        $car=$this->stage4Car();$this->color($car,'No stock yellow','yellow');$count=\App\Models\MemberVehiclePreorder::count();
        $admin=User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail();
        $this->actingAs($admin)->getJson(route('admin.pesanan-mobil.quote',['id_mobil'=>$car->id_mobil,'warna_pilihan'=>'No stock yellow','manual_discount_type'=>'none','manual_discount_value'=>'0']))->assertUnprocessable();
        $this->assertSame($count,\App\Models\MemberVehiclePreorder::count());
    }
    private function order(Mobil $car): PesananMobilMember
    {
        $customer=User::whereHas('role',fn($q)=>$q->where('nama_role','pelanggan'))->firstOrFail()->pelanggan;
        return app(MemberVehiclePurchaseService::class)->create($customer,$car,['nama_pembeli'=>'QA','telepon_pembeli'=>'08123','alamat_pembeli'=>'QA']);
    }
    public function test_missing_physical_identity_blocks_final_allocation(): void
    {
        $car=$this->stage4Car();$order=$this->order($car);$car->update(['nomor_mesin'=>'']);
        $this->expectException(\RuntimeException::class);app(VehiclePdiIntegrityService::class)->assertPhysicalIdentity($order);
    }
    public function test_two_aggregate_orders_cannot_use_one_passed_pdi_vin(): void
    {
        $car=$this->stage4Car();app(VehicleInventoryService::class)->ensure($car,2);$first=$this->order($car);
        // A corrupt legacy duplicate is manufactured only in testing to exercise the PDI guard.
        $second=$first->replicate();$second->nomor_pesanan='QA-LEGACY-'.bin2hex(random_bytes(5));$second->save();
        PdiPesananMobil::forceCreate(['id_pesanan'=>$first->id_pesanan,'id_petugas'=>$this->stage4Actor(),'status'=>'lulus',
            'nomor_rangka_snapshot'=>$car->nomor_rangka,'nomor_mesin_snapshot'=>$car->nomor_mesin]);
        app(VehiclePdiIntegrityService::class)->assertPhysicalIdentity($first);
        $this->expectException(\RuntimeException::class);app(VehiclePdiIntegrityService::class)->assertPhysicalIdentity($second);
    }

    public function test_complete_pdi_cannot_pass_missing_identity_or_create_ownership(): void
    {
        $car=$this->stage4Car();$service=app(MemberVehiclePurchaseService::class);$order=$this->order($car);
        $order=$service->approve($order,$this->stage4Actor());
        app(\App\Services\PaymentService::class)->create($order->id_transaksi,$this->stage4Actor(),$order->total_tagihan,'tunai',null,'QA');
        $car->update(['nomor_mesin'=>'']);$owned=\App\Models\KendaraanPelanggan::count();
        try {$service->completePdi($order->fresh(),$this->stage4Actor(),$this->pdiInput());$this->fail('Incomplete physical identity accepted');}
        catch(\RuntimeException $e){$this->assertStringContainsString('belum lengkap',$e->getMessage());}
        $this->assertSame('lunas',$order->fresh()->status);$this->assertSame(0,PdiPesananMobil::where('id_pesanan',$order->id_pesanan)->count());
        $this->assertSame($owned,\App\Models\KendaraanPelanggan::count());
    }
}
