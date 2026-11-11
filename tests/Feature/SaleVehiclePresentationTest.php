<?php
namespace Tests\Feature;

use App\Models\{Mobil, PenjualanMobil, User};
use App\Services\{MemberVehiclePurchaseService, SaleVehiclePresentationService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SaleVehiclePresentationTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;
    private function user(string $role='admin'): User { return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail(); }
    private function sale(string $model='GR Supra',string $color='black'): PenjualanMobil
    {
        $member=$this->user('pelanggan')->pelanggan;
        $order=app(MemberVehiclePurchaseService::class)->create($member,$this->colorStockUnit(Mobil::where('model',$model)->firstOrFail(),$color),[
            'nama_pembeli'=>$member->nama_lengkap,'telepon_pembeli'=>'08123456789','alamat_pembeli'=>'QA','warna_pilihan'=>$color]);
        $order=app(MemberVehiclePurchaseService::class)->approve($order,$this->user('petugas')->petugas->getKey());
        return PenjualanMobil::findOrFail($order->id_penjualan);
    }
    public function test_list_and_detail_use_selected_order_color_for_each_actual_sale(): void
    {
        foreach ([['GR Supra','black','toyota-gr-supra/black/front.png'],['Challenger SRT Hellcat Redeye','Destroyer Grey','premium/dodge-challenger-srt-hellcat-redeye/destroyer-grey/front.png']] as [$model,$color,$path]) {
            $sale=$this->sale($model,$color);$before=$sale->fresh()->getRawOriginal();
            $data=app(SaleVehiclePresentationService::class)->for($sale);
            $this->assertTrue($data['snapshot']);$this->assertTrue($data['has_photo']);$this->assertStringEndsWith($path,$data['image']);
            $response=$this->actingAs($this->user())->get(route('admin.penjualan-mobil.index',['q'=>$sale->kode_penjualan]))->assertOk();
            $response->assertSee($data['image'])->assertSee($data['color'])->assertSee($sale->pelanggan->kode_pelanggan);
            $this->get(route('admin.penjualan-mobil.show',$sale))->assertOk()->assertSee($data['image'])->assertSee($data['color']);
            $this->assertSame($before,$sale->fresh()->getRawOriginal());
        }
    }
    public function test_changed_catalog_identity_does_not_rewrite_snapshot_or_show_replacement_vehicle_photo(): void
    {
        $sale=$this->sale();$sale->mobil->update(['model'=>'Model Pengganti QA','warna'=>'Other Color']);
        $data=app(SaleVehiclePresentationService::class)->for($sale->fresh());
        $this->assertSame('Toyota GR Supra',$data['name']);$this->assertSame('Obsidian Black',$data['color']);
        $this->assertFalse($data['has_photo']);$this->assertStringEndsWith('vehicle-photo-unavailable.svg',$data['image']);
        $this->actingAs($this->user())->get(route('admin.penjualan-mobil.index',['q'=>$sale->kode_penjualan]))->assertOk()->assertSee('Toyota GR Supra')->assertDontSee('Model Pengganti QA');
    }
    public function test_unknown_color_and_missing_image_never_fall_back_to_default_color_or_hero(): void
    {
        $sale=$this->sale();$sale->memberOrder->update(['snapshot_warna'=>'Arsip tanpa galeri']);
        $data=app(SaleVehiclePresentationService::class)->for($sale->fresh());
        $this->assertFalse($data['has_photo']);$this->assertStringEndsWith('vehicle-photo-unavailable.svg',$data['image']);
        $this->assertSame('Arsip tanpa galeri',$data['color']);
    }
    public function test_legacy_unit_without_photos_is_explicitly_unknown_and_remains_read_only(): void
    {
        $sale=$this->sale();$order=$sale->memberOrder;$order->update(['id_penjualan'=>null]);
        $car=$sale->mobil;$car->update(['merk'=>'QA','model'=>'Legacy No Image','warna'=>'QA White']);$car->foto()->delete();$car->warnaPilihan()->delete();
        $before=$sale->fresh()->getRawOriginal();$data=app(SaleVehiclePresentationService::class)->for($sale->fresh());
        $this->assertFalse($data['snapshot']);$this->assertFalse($data['has_photo']);$this->assertStringContainsString('snapshot',$data['source']);
        $this->assertSame($before,$sale->fresh()->getRawOriginal());
    }
    public function test_inconsistent_order_relation_is_flagged_and_never_uses_foreign_order_photo(): void
    {
        $sale=$this->sale();$order=$sale->memberOrder;
        $order->update(['id_pelanggan'=>\App\Models\Pelanggan::whereKeyNot($sale->id_pelanggan)->firstOrFail()->getKey()]);
        $data=app(SaleVehiclePresentationService::class)->for($sale->fresh());
        $this->assertFalse($data['snapshot']);$this->assertNotNull($data['warning']);$this->assertFalse($data['has_photo']);
    }
    public function test_duplicate_order_bindings_are_not_resolved_by_arbitrarily_taking_first_record(): void
    {
        $sale=$this->sale();$copy=$sale->memberOrder->replicate();
        // The database already forbids duplicate sale bindings. Also fail closed for ambiguous imported/in-memory data.
        $sale->setRelation('memberOrders',collect([$sale->memberOrder,$copy]));
        $data=app(SaleVehiclePresentationService::class)->for($sale);
        $this->assertFalse($data['snapshot']);$this->assertFalse($data['has_photo']);$this->assertNotNull($data['warning']);
    }
    public function test_cancelled_order_does_not_silently_change_historical_sale_status_or_total(): void
    {
        $sale=$this->sale();$sale->memberOrder->update(['status'=>'dibatalkan']);$before=$sale->fresh()->getRawOriginal();
        $data=app(SaleVehiclePresentationService::class)->for($sale->fresh());
        $this->assertStringContainsString('dibatalkan',$data['warning']);$this->assertSame($before,$sale->fresh()->getRawOriginal());
    }
    public function test_missing_exact_color_asset_is_not_replaced_by_another_color(): void
    {
        $sale=$this->sale('LFA','black');$color=$sale->memberOrder->snapshot_warna;
        $variant=$sale->mobil->warnaPilihan()->where('nama',$color)->firstOrFail();
        $variant->galeri()->where('sudut','front')->update(['path'=>'assets/images/qa-missing-photo.png']);
        $data=app(SaleVehiclePresentationService::class)->for($sale->fresh());
        $this->assertFalse($data['has_photo']);$this->assertStringEndsWith('vehicle-photo-unavailable.svg',$data['image']);
    }
}
