<?php

namespace Tests\Feature;

use App\Models\{Mobil, Notifikasi, PesananMobilMember, Servis, ServiceWorkOrderAssignment, User};
use App\Services\{MemberVehiclePurchaseService, NotificationStateService, StaffNotificationService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GlobalNotificationStateTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;

    private function user(string $role): User { return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail(); }
    private function state(User $user): array { return app(NotificationStateService::class)->summary($user->fresh()); }
    private function message(User $user, ?string $reference = null, string $title = 'Kabar QA'): Notifikasi
    {
        return Notifikasi::create(['id_user' => $user->id_user, 'referensi_tipe' => $reference,
            'judul' => $title, 'pesan' => 'Informasi uji terisolasi.', 'tipe' => 'info', 'is_read' => false, 'created_at' => now()]);
    }
    private function order(): PesananMobilMember
    {
        $member = $this->user('pelanggan');
        return app(MemberVehiclePurchaseService::class)->create($member->pelanggan, $this->colorStockUnit(Mobil::where('model', 'GR Supra')->firstOrFail(), 'white'), [
            'nama_pembeli' => $member->pelanggan->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => 'white',
        ]);
    }

    public function test_unique_unread_records_map_to_one_primary_member_menu(): void
    {
        $member = $this->user('pelanggan'); $before = $this->state($member);
        foreach (['pesanan_mobil_member' => 'orders', 'pre_order_mobil' => 'buy', 'kendaraan_pelanggan' => 'garage', 'booking_servis' => 'services', 'booking_mobil' => 'activity', 'membership' => 'benefits'] as $reference => $category) {
            $this->message($member, $reference);
        }
        $state = $this->state($member);
        $this->assertSame($before['unread_count'] + 6, $state['unread_count']);
        foreach (['buy', 'orders', 'garage', 'services', 'activity', 'benefits'] as $category) $this->assertSame($before['category_unread'][$category] + 1, $state['category_unread'][$category]);
        $this->assertSame($state['unread_count'], array_sum($state['category_unread']));
        $this->actingAs($member)->get(route('pelanggan.pesanan-mobil.index'))->assertOk()->assertSee('data-notification-category="orders"', false);
    }

    public function test_read_one_of_two_messages_keeps_dot_and_pending_tasks_unchanged(): void
    {
        $member = $this->user('pelanggan'); $before = $this->state($member);
        $first = $this->message($member, 'pesanan_mobil_member'); $second = $this->message($member, 'pesanan_mobil_member');
        $this->actingAs($member)->patchJson(route('notifications.read', $first))->assertOk()
            ->assertJsonPath('category_unread.orders', $before['category_unread']['orders'] + 1);
        $this->assertFalse($second->fresh()->is_read);
        $admin = $this->user('admin'); $order = $this->order(); $pending = $this->state($admin)['pending_counts']['orders'];
        $notice = Notifikasi::where('id_user', $admin->id_user)->where('referensi_tipe', 'pesanan_mobil_member')->where('referensi_id', $order->getKey())->firstOrFail();
        $this->actingAs($admin)->patchJson(route('admin.notifikasi.read', $notice))->assertOk()->assertJsonPath('pending_counts.orders', $pending);
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);
    }

    public function test_mark_all_and_other_account_isolation_are_authoritative(): void
    {
        $member = $this->user('pelanggan'); $other = $this->user('owner');
        $this->message($member, 'pesanan_mobil_member'); $foreign = $this->message($other, 'membership');
        $this->actingAs($member)->patchJson(route('notifications.read', $foreign))->assertForbidden();
        $this->get(route('notifications.open', $foreign))->assertForbidden();
        $this->patchJson(route('notifications.read-all'))->assertOk()->assertJsonPath('unread_count', 0)->assertJsonPath('category_unread.orders', 0);
        $this->assertFalse($foreign->fresh()->is_read);
    }

    public function test_every_role_gets_only_its_own_unread_and_no_foreign_finance_counts(): void
    {
        $users = User::with('role')->where('status', 'aktif')->get()->unique(fn ($user) => $user->roleName());
        $this->assertGreaterThanOrEqual(10, $users->count());
        foreach ($users as $user) {
            $before = $this->state($user); $own = $this->message($user);
            $route = 'notifications.sync';
            $response = $this->actingAs($user)->getJson(route($route, ['after_id' => $own->id_notifikasi - 1]));
            $response->assertOk()->assertJsonPath('account_id', $user->id_user)->assertJsonPath('unread_count', $before['unread_count'] + 1);
            foreach ($response->json('notifications') as $item) $this->assertSame($user->id_user, Notifikasi::findOrFail($item['id'])->id_user);
            $this->get(route('notifications.index'))->assertOk()->assertSee('data-notification-state', false)->assertSee('data-yza-notification-panel', false);
            if (!$user->hasPermission('finance.view') || in_array($user->roleKey(), ['pelanggan', 'owner'], true)) $this->assertArrayNotHasKey('payments', $response->json('pending_counts'));
        }
    }

    public function test_pagination_and_bursts_never_skip_events_or_change_global_count(): void
    {
        $member = $this->user('pelanggan'); $before = $this->state($member); $ids = [];
        for ($i = 0; $i < 45; $i++) $ids[] = $this->message($member, 'pesanan_mobil_member')->id_notifikasi;
        $cursor = $ids[0] - 1; $seen = [];
        do {
            $response = $this->actingAs($member)->getJson(route('notifications.sync', ['after_id' => $cursor, 'incremental' => 1]));
            $response->assertOk()->assertJsonPath('unread_count', $before['unread_count'] + 45);
            $seen = array_merge($seen, array_column($response->json('notifications'), 'id'));
            $cursor = $response->json('cursor');
        } while ($cursor < max($ids));
        $this->assertSame($ids, $seen); $this->assertSame($seen, array_values(array_unique($seen)));
        $this->get(route('notifications.index', ['page' => 2]))->assertOk();
        $this->assertSame($before['unread_count'] + 45, $this->state($member)['unread_count']);
    }

    public function test_external_read_and_new_message_are_returned_together(): void
    {
        $member = $this->user('pelanggan'); $first = $this->message($member, 'pesanan_mobil_member');
        $before = $this->state($member); $first->update(['is_read' => true]); $new = $this->message($member, 'booking_servis');
        $this->actingAs($member)->getJson(route('notifications.sync', ['after_id' => $first->id_notifikasi, 'visible_ids' => [$first->id_notifikasi]]))
            ->assertOk()->assertJsonPath('unread_count', $before['unread_count'])->assertJsonPath('visible_read_state.'.$first->id_notifikasi, true)
            ->assertJsonPath('notifications.0.id', $new->id_notifikasi)->assertJsonPath('recent_notifications.1.is_read', true);
    }

    public function test_order_event_is_committed_deduplicated_and_routes_to_the_actual_order(): void
    {
        $admin = $this->user('admin'); $order = $this->order();
        app(StaffNotificationService::class)->emit(PesananMobilMember::class, $order->getKey());
        app(StaffNotificationService::class)->emit(PesananMobilMember::class, $order->getKey());
        $notices = Notifikasi::where('id_user', $admin->id_user)->where('referensi_tipe', 'pesanan_mobil_member')->where('referensi_id', $order->getKey())->get();
        $this->assertCount(1, $notices);
        $this->actingAs($admin)->get(route('admin.notifikasi.open', $notices->first()))->assertRedirect(route('admin.pesanan-mobil.index', ['detail' => $order->getKey()]));
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);
    }

    public function test_rolled_back_order_never_generates_staff_success_notification(): void
    {
        $before = Notifikasi::count(); $orders = PesananMobilMember::count();
        try { DB::transaction(function () { $this->order(); throw new \RuntimeException('rollback QA'); }); }
        catch (\RuntimeException $expected) { $this->assertSame('rollback QA', $expected->getMessage()); }
        $this->assertSame($before, Notifikasi::count()); $this->assertSame($orders, PesananMobilMember::count());
    }

    public function test_mechanic_feed_hides_missing_future_and_revoked_assignments(): void
    {
        $mechanic = $this->user('mechanic');
        $service = Servis::firstOrFail()->replicate();
        $service->forceFill(['kode_servis' => 'NOTIFY-'.bin2hex(random_bytes(6)), 'id_booking_servis' => null,
            'id_transaksi' => null, 'id_petugas' => $mechanic->petugas->id_petugas, 'status' => 'dikerjakan'])->save();
        $assignment = ServiceWorkOrderAssignment::where('id_servis', $service->getKey())->firstOrFail();
        $notice = Notifikasi::where('id_user', $mechanic->id_user)->where('referensi_tipe', 'service_assignment')->where('referensi_id', $assignment->getKey())->firstOrFail();
        $this->actingAs($mechanic)->getJson(route('admin.notifikasi.sync', ['after_id' => $notice->getKey() - 1]))->assertOk()->assertJsonPath('notifications.0.id', $notice->getKey());
        foreach (['future', 'revoked'] as $case) {
            $assignment->update(['assigned_at' => $case === 'future' ? now()->addDay() : now()->subDay(), 'unassigned_at' => $case === 'revoked' ? now() : null]);
            $response = $this->getJson(route('admin.notifikasi.sync', ['after_id' => $notice->getKey() - 1]));
            $response->assertOk()->assertJsonCount(0, 'notifications');
            $this->patchJson(route('admin.notifikasi.read', $notice))->assertForbidden();
            $this->get(route('admin.notifikasi.open', $notice))->assertForbidden();
        }
    }

    public function test_assigned_sales_and_finance_receive_only_actual_order_and_pending_payment_events(): void
    {
        $order = $this->order(); $sales = $this->user('sales'); $finance = $this->user('finance'); $owner = $this->user('owner');
        $order->forceFill(['id_sales' => $sales->petugas->id_petugas])->save();
        $this->assertDatabaseHas('notifikasi', ['id_user' => $sales->id_user, 'referensi_tipe' => 'pesanan_mobil_member', 'referensi_id' => $order->getKey()]);
        $this->assertDatabaseMissing('notifikasi', ['id_user' => $owner->id_user, 'referensi_tipe' => 'pesanan_mobil_member', 'referensi_id' => $order->getKey()]);
        $approved = app(MemberVehiclePurchaseService::class)->approve($order, $this->user('admin')->petugas->id_petugas);
        $payment = new \App\Models\Pembayaran();
        $payment->forceFill(['kode_pembayaran' => 'NSTATE-'.bin2hex(random_bytes(5)), 'id_transaksi' => $approved->id_transaksi,
            'tanggal' => now(), 'metode' => 'transfer', 'arah' => 'masuk',
            'jumlah' => 1000, 'status' => 'menunggu', 'created_at' => now()])->save();
        $this->assertDatabaseHas('notifikasi', ['id_user' => $finance->id_user, 'referensi_tipe' => 'pembayaran', 'referensi_id' => $payment->getKey(), 'judul' => 'Pembayaran menunggu verifikasi']);
        $this->assertSame('menunggu', $payment->fresh()->status);
        $this->assertDatabaseMissing('notifikasi', ['id_user' => $this->user('mechanic')->id_user, 'referensi_tipe' => 'pembayaran', 'referensi_id' => $payment->getKey()]);
    }

    public function test_warehouse_alert_is_not_inventory_quantity_or_global_unread_and_remains_pending_after_read(): void
    {
        $warehouse = $this->user('warehouse_staff');
        $part = \App\Models\Sparepart::where('status', 'aktif')->firstOrFail()->replicate();
        $part->forceFill(['kode_sparepart' => 'NSTATE-'.bin2hex(random_bytes(4)), 'stok' => 5, 'stok_minimum' => 2])->save();
        $before = $this->state($warehouse);
        app(\App\Services\InventoryService::class)->adjust($part->getKey(), 0, 'QA', 'Uji transaksi terisolasi', $this->user('admin')->petugas->id_petugas);
        $notice = Notifikasi::where('id_user', $warehouse->id_user)->where('referensi_tipe', 'sparepart')->where('referensi_id', $part->getKey())->firstOrFail();
        $state = $this->state($warehouse);
        $this->assertSame($before['unread_count'] + 1, $state['unread_count']);
        $this->assertSame($before['pending_counts']['stock'] + 1, $state['pending_counts']['stock']);
        $this->actingAs($warehouse)->patchJson(route('notifications.read', $notice))->assertOk()->assertJsonPath('pending_counts.stock', $state['pending_counts']['stock']);
    }

    public function test_operational_booking_advisor_and_qc_events_obey_their_existing_roles(): void
    {
        $customer = $this->user('pelanggan')->pelanggan; $car = Mobil::firstOrFail();
        $booking = new \App\Models\BookingMobil();
        $booking->forceFill(['kode_booking' => 'NSTATE-'.bin2hex(random_bytes(4)), 'id_pelanggan' => $customer->getKey(), 'id_mobil' => $car->getKey(), 'status' => 'menunggu', 'tanggal_booking' => now()])->save();
        $this->assertDatabaseHas('notifikasi', ['id_user' => $this->user('operational_staff')->id_user, 'referensi_tipe' => 'booking_mobil', 'referensi_id' => $booking->getKey()]);
        $serviceBooking = \App\Models\BookingServis::firstOrFail()->replicate();
        $serviceBooking->forceFill(['kode_booking' => 'NSTATE-'.bin2hex(random_bytes(4))])->save();
        $this->assertDatabaseHas('notifikasi', ['id_user' => $this->user('service_advisor')->id_user, 'referensi_tipe' => 'booking_servis', 'referensi_id' => $serviceBooking->getKey()]);
        $service = Servis::firstOrFail()->replicate();
        $service->forceFill(['kode_servis' => 'NSTATE-'.bin2hex(random_bytes(4)), 'id_booking_servis' => null, 'id_transaksi' => null, 'status' => 'dikerjakan'])->save();
        \App\Models\ServiceQualityControl::create(['id_servis' => $service->getKey(), 'status' => 'in_progress']);
        $this->assertDatabaseHas('notifikasi', ['id_user' => $this->user('quality_control')->id_user, 'referensi_tipe' => 'servis', 'referensi_id' => $service->getKey(), 'judul' => 'Quality Control dimulai']);
    }
}
