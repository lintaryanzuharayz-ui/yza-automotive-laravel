<?php

namespace Tests\Feature;

use App\Models\{BookingServis, JenisServis, KendaraanPelanggan, Notifikasi, User};
use App\Services\BookingServisService;
use App\Services\ServiceNotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminRealtimeNotificationsTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    private function booking(): BookingServis
    {
        $member = $this->user('pelanggan');
        $vehicle = $member->pelanggan->kendaraan()->create([
            'merk' => 'QA',
            'model' => 'Realtime Vehicle',
            'nomor_polisi' => 'RT-' . random_int(1000, 9999),
            'status' => 'aktif',
        ]);
        $service = JenisServis::where('status', 'aktif')->firstOrFail();

        return app(BookingServisService::class)->create(
            $member->pelanggan->id_pelanggan,
            $vehicle->id_kendaraan_pelanggan,
            $service->id_jenis_servis,
            now()->addDay()->toDateString(),
            '10:00',
            'Realtime test',
            null,
        );
    }

    public function test_booking_notification_is_deduplicated_per_admin(): void
    {
        $booking = $this->booking();
        $service = app(ServiceNotificationService::class);
        $service->forAdminBooking($booking);
        $service->forAdminBooking($booking);

        $admins = User::where('status', 'aktif')
            ->whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))
            ->pluck('id_user');

        $this->assertGreaterThan(0, $admins->count());
        $this->assertSame(
            $admins->count(),
            Notifikasi::whereIn('id_user', $admins)
                ->where('tipe', 'booking')
                ->where('referensi_tipe', 'booking_servis')
                ->where('referensi_id', $booking->id_booking_servis)
                ->count(),
        );
    }

    public function test_admin_sync_returns_booking_deep_link_and_member_page_is_denied(): void
    {
        $booking = $this->booking();
        $admin = $this->user('admin');

        $response = $this->actingAs($admin)->getJson(route('admin.notifikasi.sync', [
            'after_id' => 0,
        ]));

        $response->assertOk();
        $notification = collect($response->json('notifications'))
            ->first(fn (array $item) => $item['id'] === Notifikasi::where('referensi_id', $booking->id_booking_servis)->latest('id_notifikasi')->value('id_notifikasi'));

        $this->assertNotNull($notification);
        $this->assertSame(route('admin.booking-servis.show', $booking->id_booking_servis), $notification['url']);

        $this->actingAs($this->user('pelanggan'))
            ->getJson(route('admin.notifikasi.sync'))
            ->assertForbidden();
    }

    public function test_mark_read_changes_authoritative_notification_state(): void
    {
        $booking = $this->booking();
        $admin = $this->user('admin');
        $notification = Notifikasi::where('id_user', $admin->id_user)
            ->where('referensi_id', $booking->id_booking_servis)
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.notifikasi.read', $notification->id_notifikasi))
            ->assertRedirect();

        $this->assertDatabaseHas('notifikasi', [
            'id_notifikasi' => $notification->id_notifikasi,
            'id_user' => $admin->id_user,
            'is_read' => 1,
        ]);
    }

    public function test_admin_notification_page_uses_member_neutral_branding(): void
    {
        $this->actingAs($this->user('admin'))
            ->get(route('admin.notifikasi.index'))
            ->assertOk()
            ->assertSee('Pusat Notifikasi')
            ->assertDontSee('Notifikasi Member')
            ->assertSee('data-admin-notification-list', false)
            ->assertSee('data-admin-live-toast-stack', false);
    }

    public function test_member_sync_returns_new_notifications_without_relogin(): void
    {
        $member = $this->user('pelanggan');
        $notification = new Notifikasi();
        $notification->forceFill([
            'id_user' => $member->id_user,
            'tipe' => 'info',
            'judul' => 'Status pesanan diperbarui',
            'pesan' => 'Pesanan Anda sedang menunggu verifikasi admin.',
            'is_read' => false,
            'created_at' => now(),
        ])->save();

        $this->actingAs($member)
            ->getJson(route('notifications.sync', ['after_id' => $notification->id_notifikasi - 1]))
            ->assertOk()
            ->assertJsonPath('unread_count', Notifikasi::where('id_user', $member->id_user)->where('is_read', false)->count())
            ->assertJsonPath('notifications.0.id', $notification->id_notifikasi)
            ->assertJsonPath('notifications.0.title', 'Status pesanan diperbarui');
    }
}
