<?php

namespace Tests\Feature;

use App\Models\{Mobil, Notifikasi, User};
use App\Services\{MemberVehiclePurchaseService, NotificationStateService, StaffNotificationService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PendingNotificationReconciliationTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;

    public function test_missing_order_notification_is_delivered_once_with_real_order_information(): void
    {
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        $order = app(MemberVehiclePurchaseService::class)->create($member->pelanggan,
            $this->colorStockUnit(Mobil::where('model', 'GR Supra')->firstOrFail(), 'black'), ['nama_pembeli' => 'Pembeli Uji',
                'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => 'black']);
        $service = app(StaffNotificationService::class);
        $service->reconcilePendingOrders();
        $service->reconcilePendingOrders();
        $query = Notifikasi::where('id_user', $admin->id_user)->where('referensi_tipe', 'pesanan_mobil_member')->where('referensi_id', $order->getKey());
        $this->assertSame(1, $query->count());
        $notification = $query->firstOrFail();
        $this->assertStringContainsString($order->nomor_pesanan, $notification->pesan);
        $this->assertStringContainsString($order->snapshot_warna, $notification->pesan);
        $this->assertStringContainsString($order->snapshot_model, $notification->pesan);
        $notification->update(['is_read' => true]);
        $service->reconcilePendingOrders();
        $this->assertTrue($notification->fresh()->is_read);
        $this->assertSame(1, $query->count());
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);
        $this->assertFalse(Notifikasi::where('id_user', $member->id_user)->where('judul', 'Pesanan Mobil Baru')->where('referensi_id', $order->getKey())->exists());
        $response = $this->actingAs($admin)->getJson(route('admin.notifikasi.sync', ['panel' => 1]))->assertOk();
        $response->assertJsonPath('unread_count', app(NotificationStateService::class)->query($admin)->where('is_read', false)->count());
        foreach ($response->json('pending_actions') as $action) {
            $this->assertSame($response->json('pending_counts.'.$action['key']), $action['count']);
        }
    }
}
