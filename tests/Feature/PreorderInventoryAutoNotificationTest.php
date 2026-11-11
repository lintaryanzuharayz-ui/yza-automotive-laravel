<?php

namespace Tests\Feature;

use App\Models\{MemberVehiclePreorder, Mobil, Notifikasi, PreorderInventoryEvent, User};
use App\Services\{ActiveMemberCustomerService, MemberVehiclePreorderService, NotificationDestinationService, PreorderInventoryNotificationService, PreorderManagementService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PreorderInventoryAutoNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private function actor(string $role = 'admin'): User { return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail(); }
    private function car(array $attributes = []): Mobil
    {
        $car = Mobil::firstOrFail()->replicate(); $id = bin2hex(random_bytes(6));
        $car->forceFill(array_replace(['kode_mobil'=>'PA-'.$id,'nomor_rangka'=>'PA-VIN-'.$id,'nomor_mesin'=>'PA-ENGINE-'.$id,
            'merk'=>'QA-'.$id,'model'=>'Model','tipe'=>'Trim','tahun'=>2026,'warna'=>'Cyan','transmisi'=>'automatic',
            'bahan_bakar'=>'bensin','kondisi'=>'baru','is_limited_edition'=>false,'showcase_slug'=>null,'catalog_identity'=>null,'status'=>'dalam_pemeriksaan','harga_jual'=>100000000], $attributes))->save();
        app(VehicleInventoryService::class)->ensure($car, 0);
        return $car;
    }
    // Restock is a verified change of the real unit's status, not an aggregate number.
    private function stock(Mobil $car, int $quantity, string $reason): void
    {
        $car->refresh()->update(['status' => $quantity > 0 ? 'tersedia' : 'dalam_pemeriksaan']);
        app(PreorderInventoryNotificationService::class)->queue($car, $reason);
    }
    private function requestFor(Mobil $car, array $attributes = []): MemberVehiclePreorder
    {
        $customer = app(ActiveMemberCustomerService::class)->query()->firstOrFail();
        return MemberVehiclePreorder::create(array_replace(['id_pelanggan'=>$customer->getKey(),'id_mobil'=>$car->getKey(),
            'product_key'=>app(MemberVehiclePreorderService::class)->productKey($car),'nama_mobil'=>$car->merk.' '.$car->model,
            'varian'=>$car->tipe,'tahun'=>$car->tahun,'warna'=>$car->warna,'status'=>'menunggu_stok','harga_referensi'=>$car->harga_jual], $attributes));
    }
    private function notices(MemberVehiclePreorder $item) { return Notifikasi::where('referensi_tipe','pre_order_mobil')->where('referensi_id',$item->getKey())->where('tipe','stok'); }
    private function reconcile(Mobil $car): int
    {
        $service = app(PreorderInventoryNotificationService::class);
        $id = $service->queue($car, 'QA restock'); $service->process($id); return $id;
    }
    public function test_empty_then_matching_stock_delivers_once_with_correct_destination_and_no_transaction(): void
    {
        $car = $this->car(); $item = $this->requestFor($car);
        $before = array_map(fn ($table) => DB::table($table)->count(), ['pesanan_mobil_member','transaksi','penjualan_mobil','kendaraan_pelanggan']);
        $this->reconcile($car); $this->assertSame(0,$this->notices($item)->count());
        $this->stock($car,1,'QA restock');
        $event = $this->reconcile($car);
        $notice = $this->notices($item)->sole();
        $this->assertSame($item->pelanggan->id_user,$notice->id_user);
        foreach ([$item->nama_mobil,'Trim','2026','Cyan','PRE-','tidak mencadangkan'] as $text) $this->assertStringContainsString($text,$notice->pesan);
        $this->assertSame(route('pelanggan.katalog-mobil.show',$car),app(NotificationDestinationService::class)->for($item->pelanggan->user,$notice));
        $this->assertSame('notifikasi_terkirim',$item->fresh()->status);
        $this->assertSame(1,app(\App\Services\VehicleAvailabilityService::class)->for($car)['stock']);
        $this->assertSame($before,array_map(fn ($table) => DB::table($table)->count(), ['pesanan_mobil_member','transaksi','penjualan_mobil','kendaraan_pelanggan']));
        $notice->update(['is_read'=>true]);
        app(PreorderInventoryNotificationService::class)->process($event); $this->reconcile($car);
        $this->assertSame(1,$this->notices($item)->count()); $this->assertTrue($notice->fresh()->is_read);
        $history = app(PreorderManagementService::class)->detail($item->fresh(),$this->actor())['history'];
        $this->assertContains('notification_sent',array_column($history,'kind'));
        $this->assertStringContainsString('Dibaca',collect($history)->firstWhere('kind','notification_sent')['notification_status']);
    }
    public function test_wrong_color_variant_year_model_and_unbound_legacy_never_notify(): void
    {
        foreach (['warna'=>'Other','varian'=>'Other','tahun'=>2025,'nama_mobil'=>'Other Model'] as $field=>$value) {
            $car=$this->car(); $item=$this->requestFor($car,[$field=>$value]);
            $this->stock($car,1,'QA'); $this->reconcile($car);
            $this->assertSame(0,$this->notices($item)->count()); $this->assertFalse(app(PreorderManagementService::class)->matchingStock($item)['available']);
        }
        $car=$this->car();$item=$this->requestFor($car,['id_mobil'=>null,'product_key'=>'legacy:unbound']);
        $this->stock($car,1,'QA'); $this->reconcile($car);
        $this->assertFalse(app(PreorderManagementService::class)->matchingStock($item)['available']);
    }
    public function test_catalog_color_or_incomplete_physical_identity_cannot_prove_stock(): void
    {
        $car=$this->car(['nomor_mesin'=>'']);$item=$this->requestFor($car); $this->stock($car,1,'QA');
        $this->reconcile($car);
        $this->assertSame(0,$this->notices($item)->count());
        $car->update(['nomor_mesin'=>'VALID-QA-ENGINE','status'=>'dalam_pemeriksaan']);$this->reconcile($car);
        $this->assertSame(0,$this->notices($item)->count());
    }
    public function test_limited_stock_notifies_multiple_interested_members_without_allocating_stock(): void
    {
        $car=$this->car();$first=$this->requestFor($car);
        $template=$first->pelanggan; $user=$template->user->replicate();$key=bin2hex(random_bytes(5));
        $user->forceFill(['username'=>'qa-'.$key,'email'=>'qa-'.$key.'@example.test'])->save();
        $customer=$template->replicate();$customer->forceFill(['id_user'=>$user->getKey(),'kode_pelanggan'=>'QA-'.$key])->save();
        $membership=$template->membership->replicate();$membership->forceFill(['id_pelanggan'=>$customer->getKey(),'member_id'=>'YZA-QA-'.$key,'gateway_payment_id'=>null,'registered_at'=>'2026-10-01','status'=>'active','activated_at'=>now(),'cancelled_at'=>null])->save();
        $second=$this->requestFor($car,['id_pelanggan'=>$customer->getKey()]);
        $this->stock($car,1,'QA');$this->reconcile($car);
        $this->assertSame(1,$this->notices($first)->count());$this->assertSame(1,$this->notices($second)->count());
        $this->assertSame(0,app(VehicleInventoryService::class)->reserveCount($car));
        $this->assertNull($first->fresh()->id_pesanan);$this->assertNull($second->fresh()->id_pesanan);
        $foreign=$this->actor('pelanggan');
        if ($foreign->getKey() !== $template->id_user) $this->assertSame(route('notifications.index'),app(NotificationDestinationService::class)->for($foreign,$this->notices($first)->sole()));
    }
    public function test_failed_delivery_outbox_can_retry_without_duplicates(): void
    {
        $car=$this->car();$item=$this->requestFor($car);
        $real=app(PreorderManagementService::class);
        $failing=\Mockery::mock(PreorderManagementService::class);$failing->shouldReceive('matchingStock')->andThrow(new \RuntimeException('Temporary QA failure'));
        app()->instance(PreorderManagementService::class,$failing);
        $this->stock($car,1,'QA');
        $service=app(PreorderInventoryNotificationService::class);$id=$service->queue($car,'Retry QA');
        try {$service->process($id);} catch (\RuntimeException $failure) {$this->assertSame('Temporary QA failure',$failure->getMessage());}
        $this->assertSame('failed',PreorderInventoryEvent::findOrFail($id)->status);$this->assertSame(0,$this->notices($item)->count());
        app()->instance(PreorderManagementService::class,$real);$service->process($id);$service->process($id);
        $this->assertSame(1,$this->notices($item)->count());$this->assertSame('processed',PreorderInventoryEvent::findOrFail($id)->status);
    }
    public function test_inactive_membership_and_terminal_preorders_are_never_notified(): void
    {
        $car=$this->car();$item=$this->requestFor($car);$item->pelanggan->membership->update(['status'=>'cancelled']);
        $this->stock($car,1,'QA');$this->reconcile($car);$this->assertSame(0,$this->notices($item)->count());
        $item->update(['status'=>'dibatalkan']);$this->reconcile($car);$this->assertSame(0,$this->notices($item)->count());
    }
    public function test_new_availability_cycle_can_notify_again_but_same_cycle_cannot(): void
    {
        $car=$this->car();$item=$this->requestFor($car);
        $this->stock($car,1,'QA arrival');$this->reconcile($car);
        $this->stock($car,0,'QA empty');$this->reconcile($car);
        $this->assertSame('menunggu_stok',$item->fresh()->status);
        $this->stock($car,1,'QA new arrival');$this->reconcile($car);$this->reconcile($car);
        $this->assertSame(2,$this->notices($item)->count());$this->assertSame(2,$item->fresh()->stock_generation);
    }
    public function test_notification_read_endpoint_and_badge_are_scoped_to_recipient(): void
    {
        $car=$this->car();$item=$this->requestFor($car);$recipient=$item->pelanggan->user;
        $state=app(\App\Services\NotificationStateService::class);
        $before=$state->query($recipient)->where('is_read',false)->count();
        $this->stock($car,1,'QA arrival');$this->reconcile($car);
        $notice=$this->notices($item)->sole();$this->assertSame($before+1,$state->query($recipient)->where('is_read',false)->count());
        $read=route('notifications.read',$notice);
        $this->actingAs($this->actor('finance'))->patchJson($read)->assertForbidden();
        $this->actingAs($recipient)->patchJson($read)->assertOk();
        $this->assertTrue($notice->fresh()->is_read);$this->assertSame($before,$state->query($recipient)->where('is_read',false)->count());
        $this->reconcile($car);$this->assertSame(1,$this->notices($item)->count());
    }
    public function test_resend_requires_reason_and_available_stock_and_is_retry_safe(): void
    {
        $car=$this->car();$item=$this->requestFor($car);$this->stock($car,1,'QA');$this->reconcile($car);
        $body=['action'=>'resend','expected_status'=>'notifikasi_terkirim','confirmation'=>'PRE-'.str_pad($item->getKey(),5,'0',STR_PAD_LEFT)];
        $url=route('admin.preorder-mobil.action',$item);
        $this->actingAs($this->actor())->postJson($url,$body)->assertUnprocessable();
        $body['reason']='Member meminta pemberitahuan ulang.';
        $this->postJson($url,$body)->assertOk();$this->postJson($url,$body)->assertOk();$this->assertSame(2,$this->notices($item)->count());
        $this->stock($car,0,'QA sold out');
        $this->postJson($url,$body)->assertUnprocessable();
    }
    public function test_stock_deep_link_keeps_context_and_permission_and_check_is_audited(): void
    {
        $car=$this->car();$item=$this->requestFor($car);
        $detail=$this->actingAs($this->actor())->getJson(route('admin.preorder-mobil.detail',$item))->assertOk()->json();
        $this->assertStringContainsString('stock_mobil='.$car->getKey(),$detail['stock_management_url']);
        $this->assertStringContainsString('preorder='.$item->getKey(),$detail['stock_management_url']);
        $this->get($detail['stock_management_url'])->assertOk()->assertSee('Kembali ke Pre-Order');
        $this->postJson(route('admin.preorder-mobil.stock.check',$item))->assertOk();
        $this->assertTrue(PreorderInventoryEvent::where('preorder_id',$item->getKey())->where('kind','stock_checked')->where('actor_id',$this->actor()->getKey())->exists());
        $this->actingAs($this->actor('owner'))->postJson(route('admin.preorder-mobil.stock.check',$item))->assertForbidden();
        $this->actingAs($this->actor('finance'))->postJson(route('admin.mobil.stock.adjust',$car),['stok_baru'=>1,'alasan'=>'QA'])->assertForbidden();
    }

    public function test_rollback_discards_inventory_outbox_and_never_delivers_before_commit(): void
    {
        $car=$this->car();$item=$this->requestFor($car);
        $before=PreorderInventoryEvent::count();
        DB::beginTransaction();
        $this->stock($car,1,'QA rollback');
        $this->assertSame(0,$this->notices($item)->count());
        $this->assertTrue(PreorderInventoryEvent::where('kind','inventory_changed')->where('status','pending')->where('mobil_id',$car->getKey())->exists());
        DB::rollBack();
        $this->assertSame($before,PreorderInventoryEvent::count());
        $this->assertSame(0,$this->notices($item)->count());
        $this->assertSame(0,app(\App\Services\VehicleAvailabilityService::class)->for($car)['stock']);
    }

    public function test_reserved_stock_is_not_eligible_and_checkout_cannot_oversell(): void
    {
        $car=$this->car();$this->stock($car,1,'QA');
        $customer=app(ActiveMemberCustomerService::class)->query()->firstOrFail();
        $input=['nama_pembeli'=>$customer->nama_lengkap,'telepon_pembeli'=>'08123456789','alamat_pembeli'=>'QA'];
        app(\App\Services\MemberVehiclePurchaseService::class)->create($customer,$car,$input);
        $item=$this->requestFor($car);$this->reconcile($car);
        $this->assertSame(0,$this->notices($item)->count());
        $this->assertFalse(app(PreorderManagementService::class)->matchingStock($item)['available']);
        $this->assertSame(1,app(VehicleInventoryService::class)->reserveCount($car));
        $this->expectException(\RuntimeException::class);
        app(\App\Services\MemberVehiclePurchaseService::class)->create($customer,$car,$input);
    }

    public function test_legacy_history_is_not_invented_and_foreign_recipient_cannot_follow_link(): void
    {
        $car=$this->car();$item=$this->requestFor($car);
        PreorderInventoryEvent::where('preorder_id',$item->getKey())->delete(); // Simulate an older record with no event history.
        $detail=app(PreorderManagementService::class)->detail($item,$this->actor());
        $this->assertSame([],$detail['history']);
        $this->stock($car,1,'QA');$this->reconcile($car);
        $other=$this->actor('finance');
        $this->assertSame(route('admin.notifikasi.index'),app(NotificationDestinationService::class)->for($other,$this->notices($item)->sole()));
    }
}
