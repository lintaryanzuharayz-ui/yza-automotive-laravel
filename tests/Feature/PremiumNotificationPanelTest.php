<?php

namespace Tests\Feature;

use App\Models\{Notifikasi, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PremiumNotificationPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function actor(string $role): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    public function test_panel_uses_dark_only_for_members_and_white_for_every_existing_staff_role(): void
    {
        $users = User::with('role')->where('status', 'aktif')->get()->unique(fn ($u) => $u->roleName());
        $this->assertGreaterThanOrEqual(10, $users->count());
        foreach ($users as $user) {
            $this->actingAs($user);
            $html = view('components.notification-panel')->render();
            $theme = $user->roleKey() === 'pelanggan' ? 'member' : 'backoffice';
            $this->assertStringContainsString('yza-notification-panel--'.$theme, $html, $user->roleName());
            $this->assertStringContainsString('data-panel-filter="unread"', $html);
            $this->assertStringNotContainsString('data-panel-pending', $html);
            $this->assertStringNotContainsString('Perlu tindakan saat ini', $html);
        }
    }

    public function test_panel_filters_latest_four_without_leaking_or_marking_records_read(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        foreach (['pelanggan' => 'notifications.sync', 'admin' => 'admin.notifikasi.sync', 'owner' => 'notifications.sync'] as $role => $route) {
            $user = $this->actor($role);
            $ids = [];
            for ($index = 0; $index < 6; $index++) {
                $item = new Notifikasi();
                $item->forceFill(['id_user' => $user->id_user, 'tipe' => 'info', 'judul' => 'Panel QA '.$index,
                    'pesan' => 'Pesan milik akun ini', 'is_read' => $index % 2 === 0, 'created_at' => now()])->save();
                $ids[] = $item->getKey();
            }
            $this->actingAs($user)->getJson(route($route, ['panel' => 1, 'after_id' => PHP_INT_MAX]))
                ->assertOk()->assertJsonCount(4, 'notifications')
                ->assertJsonPath('notifications.0.id', $ids[5])->assertJsonPath('notifications.3.id', $ids[2]);
            $response = $this->getJson(route($route, ['panel' => 1, 'filter' => 'unread']));
            $response->assertOk();
            foreach ($response->json('notifications') as $item) {
                $this->assertFalse($item['is_read']);
                $this->assertSame($user->id_user, Notifikasi::findOrFail($item['id'])->id_user);
            }
            $this->assertFalse(Notifikasi::findOrFail($ids[5])->is_read);
            $this->assertTrue(Notifikasi::findOrFail($ids[4])->is_read);
        }
    }

    public function test_owner_notification_center_keeps_owner_shell_and_shared_bell(): void
    {
        $this->actingAs($this->actor('owner'))->get(route('notifications.index'))->assertOk()
            ->assertSee('data-owner-shell', false)->assertSee('data-yza-notification-panel', false)
            ->assertSee('yza-notification-panel--backoffice', false);
    }
}
