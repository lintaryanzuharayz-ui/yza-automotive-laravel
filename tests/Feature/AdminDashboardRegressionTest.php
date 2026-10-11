<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDashboardRegressionTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    public function test_admin_dashboard_loads_all_live_widgets(): void
    {
        $response = $this->actingAs($this->user('admin'))->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Dashboard Utama')
            ->assertSee('Pesanan Mobil')
            ->assertSee('Transaksi Aktif')
            ->assertSee('Booking Hari Ini')
            ->assertSee('Mobil Aktif')
            ->assertSee('Pesanan terbaru')
            ->assertSee('admin-sidebar', false);
    }

    public function test_non_admin_cannot_open_admin_dashboard(): void
    {
        $this->actingAs($this->user('pelanggan'))->get(route('admin.dashboard'))->assertForbidden();
    }
}
