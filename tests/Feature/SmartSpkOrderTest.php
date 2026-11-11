<?php

namespace Tests\Feature;

use App\Models\{Mobil, PesananMobilMember, User};
use App\Services\{SmartSpkOrderService, MemberVehiclePurchaseService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SmartSpkOrderTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesPaidMember, \Tests\Support\PromoIntegrityFixtures;
    private function admin(): User { return User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail(); }
    private function input(Mobil $car, string $type = 'none', string $value = '0'): array
    {
        $member = $this->newPaidMember();
        return ['id_pelanggan'=>$member->id_pelanggan,'id_mobil'=>$car->id_mobil,'nama_pembeli'=>$member->nama_lengkap,
            'telepon_pembeli'=>'08123456789','alamat_pembeli'=>'Alamat QA','syarat_disetujui'=>'1','warna_pilihan'=>$car->warna,
            'manual_discount_type'=>$type,'manual_discount_value'=>$value,'manual_discount_reason'=>'Penawaran pengujian'];
    }
    private function counts(): array
    {
        return collect(['transaksi','penjualan_mobil','pembayaran','promo_redemptions','kendaraan_pelanggan','serah_terima_pesanan_mobil'])
            ->mapWithKeys(fn($t)=>[$t=>DB::table($t)->count()])->all();
    }
    public function test_percentage_nominal_zero_and_rounding_use_exact_integer_money(): void
    {
        $spk=app(SmartSpkOrderService::class);
        foreach (['5'=>'45000000.00','10'=>'90000000.00','30'=>'270000000.00'] as $percent=>$amount) {
            $quote=$spk->calculate('900000000.00','percentage',(string)$percent);
            $this->assertSame($amount,$quote['discount_amount']);$this->assertTrue($quote['approval_required']);
        }
        $this->assertSame('630000000.00',$spk->calculate('900000000.00','percentage','30')['estimated_total']);
        $this->assertSame('0.00',$spk->calculate('900000000.00')['discount_amount']);
        $this->assertSame('890000000.00',$spk->calculate('900000000.00','nominal','10000000')['estimated_total']);
        $this->assertSame('0.01',$spk->calculate('0.05','percentage','10')['discount_amount']);
        $this->assertSame('9999999999999.99',$spk->calculate('9999999999999.99','percentage','100')['discount_amount']);
    }
    public function test_negative_over_limit_and_invalid_values_are_rejected(): void
    {
        foreach ([['percentage','-1'],['percentage','101'],['nominal','1000.01'],['none','30'],['percentage','1e2'],['percentage','5.001']] as [$type,$value]) {
            try { app(SmartSpkOrderService::class)->calculate('1000.00',$type,$value); $this->fail('Invalid discount accepted'); }
            catch(\RuntimeException $e){$this->assertNotEmpty($e->getMessage());}
        }
    }
    public function test_vehicle_color_and_vin_come_from_physical_stock_despite_legacy_aggregate(): void
    {
        $car=$this->stage4Car();$car->update(['warna'=>'QA White']);
        $other=$car->replicate();$other->forceFill(['kode_mobil'=>'SPK-'.bin2hex(random_bytes(4)),'nomor_rangka'=>'VIN-'.bin2hex(random_bytes(4)),'nomor_mesin'=>'ENG-'.bin2hex(random_bytes(4)),'warna'=>'QA Red'])->save();
        $spk=app(SmartSpkOrderService::class);$a=$spk->vehicle($car);$b=$spk->vehicle($other);
        $this->assertSame($a['group'],$b['group']);$this->assertNotSame($a['id'],$b['id']);
        $this->assertSame($car->nomor_rangka,$a['vin']);
        app(VehicleInventoryService::class)->ensure($car,3);
        $this->assertSame($car->nomor_rangka,$spk->vehicle($car)['vin']);$this->assertSame(1,$spk->vehicle($car)['stock']);
    }
    public function test_quote_ignores_browser_price_and_reads_changed_server_price(): void
    {
        $car=$this->stage4Car(); $input=['id_mobil'=>$car->id_mobil,'warna_pilihan'=>$car->warna,'manual_discount_type'=>'percentage','manual_discount_value'=>'30','base_price'=>1,'estimated_total'=>1];
        $this->actingAs($this->admin())->getJson(route('admin.pesanan-mobil.quote',$input))->assertOk()->assertJsonPath('estimated_total','70000000.00');
        $car->update(['harga_jual'=>'900000000']);
        $this->getJson(route('admin.pesanan-mobil.quote',$input))->assertOk()->assertJsonPath('estimated_total','630000000.00');
        $input['warna_pilihan']='Tidak tersedia';$this->getJson(route('admin.pesanan-mobil.quote',$input))->assertUnprocessable();
    }
    public function test_discount_draft_keeps_base_price_and_no_invoice_payment_or_ownership(): void
    {
        $car=$this->stage4Car();$input=$this->input($car,'percentage','30');$before=$this->counts();
        $this->actingAs($this->admin())->post(route('admin.pesanan-mobil.store'),$input)->assertSessionHasNoErrors();
        $order=PesananMobilMember::where('id_mobil',$car->id_mobil)->firstOrFail();
        $this->assertSame('pending',$order->manual_discount_status);$this->assertSame('30000000.00',$order->manual_discount_amount);
        $this->assertSame('100000000.00',$order->total_tagihan);$this->assertNull($order->id_transaksi);$this->assertNull($order->id_penjualan);
        $this->assertSame('menunggu_verifikasi',$order->status);$this->assertSame($before,$this->counts());
        $this->assertSame($this->admin()->id_user,(int)$order->manual_discount_requested_by);$this->assertCount(1,$order->manual_discount_history);
        $this->assertSame('dibooking',$car->fresh()->status);
        $this->get(route('admin.pesanan-mobil.index'))->assertOk()->assertSee('Pengajuan diskon SPK');
        $this->post(route('admin.pesanan-mobil.approve',$order),['confirmation_reviewed'=>1,'confirmation_order'=>$order->nomor_pesanan,'confirmation_decision'=>'approve'])->assertSessionHasErrors();
        $this->assertSame($before,$this->counts());
    }
    public function test_missing_reason_color_tampering_promo_and_stale_quote_roll_back(): void
    {
        $this->actingAs($this->admin());
        foreach (['reason','color','promo','stale','status','number'] as $case) {
            $car=$this->stage4Car();$input=$this->input($car,'percentage','5');$before=$this->counts();
            if($case==='reason')$input['manual_discount_reason']='';
            if($case==='color')$input['warna_pilihan']='Invented Red';
            if($case==='promo')$input['promo_id']=1;
            if($case==='stale')$input['expected_base_price']='1';
            if($case==='status')$input['manual_discount_status']='approved';
            if($case==='number')$input['nomor_pesanan']='SPK-CLIENT';
            $this->post(route('admin.pesanan-mobil.store'),$input)->assertSessionHasErrors(null,null,'createOrder');
            $this->assertSame($before,$this->counts());$this->assertSame('tersedia',$car->fresh()->status);
            $this->assertDatabaseMissing('pesanan_mobil_member',['id_mobil'=>$car->id_mobil]);
        }
    }
    public function test_independent_superadmin_still_cannot_approve_without_margin_policy_and_can_reject(): void
    {
        $car=$this->stage4Car();$input=$this->input($car,'nominal','1000');
        $this->actingAs($this->admin())->post(route('admin.pesanan-mobil.store'),$input)->assertSessionHasNoErrors();
        $order=PesananMobilMember::where('id_mobil',$car->id_mobil)->firstOrFail();$before=$this->counts();
        $this->post(route('admin.pesanan-mobil.discount',$order),['decision'=>'approve','manual_discount_reason'=>'Self'])->assertSessionHasErrors();
        $reviewer=$this->admin()->replicate();$suffix=bin2hex(random_bytes(5));$reviewer->forceFill(['username'=>'review-'.$suffix,'email'=>$suffix.'@example.test'])->save();
        $this->actingAs($reviewer)->post(route('admin.pesanan-mobil.discount',$order),['decision'=>'approve','manual_discount_reason'=>'Review'])->assertSessionHasErrors();
        $this->assertSame('pending',$order->fresh()->manual_discount_status);
        $this->post(route('admin.pesanan-mobil.discount',$order),['decision'=>'reject','manual_discount_reason'=>'Margin belum terverifikasi'])->assertSessionHasNoErrors();
        $this->assertSame('rejected',$order->fresh()->manual_discount_status);$this->assertCount(2,$order->fresh()->manual_discount_history);
        $this->assertSame($before,$this->counts());
    }
    public function test_proposer_can_revise_and_withdraw_without_modifying_snapshot_or_invoice(): void
    {
        $car=$this->stage4Car();$input=$this->input($car,'percentage','5');
        $this->actingAs($this->admin())->post(route('admin.pesanan-mobil.store'),$input)->assertSessionHasNoErrors();
        $order=PesananMobilMember::where('id_mobil',$car->id_mobil)->firstOrFail();
        $this->post(route('admin.pesanan-mobil.discount',$order),['decision'=>'revise','manual_discount_reason'=>'Tarik pengajuan','manual_discount_type'=>'none','manual_discount_value'=>'0'])->assertSessionHasNoErrors();
        $this->assertSame('none',$order->fresh()->manual_discount_status);$this->assertSame('100000000.00',$order->fresh()->snapshot_harga);
        $this->post(route('admin.pesanan-mobil.approve',$order),['confirmation_reviewed'=>1,'confirmation_order'=>$order->nomor_pesanan,'confirmation_decision'=>'approve'])->assertSessionHasNoErrors();
        $this->assertSame('belum_bayar',$order->fresh()->transaksi->status);
    }
    public function test_owner_and_member_cannot_create_or_review_discount(): void
    {
        \Illuminate\Support\Facades\Artisan::call('yza:seed-local-staff');
        $input=$this->input($this->stage4Car(),'percentage','30');
        foreach(['owner','pelanggan']as$role){$user=User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail();
            $this->actingAs($user)->post(route('admin.pesanan-mobil.store'),$input)->assertForbidden();}
    }
    public function test_modal_renders_three_steps_without_payment_or_promo_fields(): void
    {
        $html=$this->actingAs($this->admin())->get(route('admin.pesanan-mobil.index'))->assertOk()->getContent();
        $part=explode('</dialog>',explode('id="order-create-dialog"',$html,2)[1],2)[0];
        $this->assertStringContainsString('Tambah Pesanan Mobil — SPK',$part);
        foreach([0,1,2]as$step)$this->assertStringContainsString('data-spk-step="'.$step.'"',$part);
        $this->assertStringNotContainsString('name="dp"',$part);$this->assertStringNotContainsString('name="promo_id"',$part);
        $this->assertStringContainsString('data-spk-swatches',$part);
    }
}
