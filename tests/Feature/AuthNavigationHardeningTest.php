<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthNavigationHardeningTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_member_navigation_goes_to_login(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('href="'.route('login').'"', false);
    }

    public function test_guest_can_still_open_login_page(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk', false);
    }

    public function test_authenticated_active_member_navigation_goes_to_member_center(): void
    {
        $member = User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))
            ->where('status', 'aktif')
            ->whereHas('pelanggan.membership', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();

        $response = $this->actingAs($member)->get('/');

        $response->assertOk();
        $response->assertSee('href="'.route('pelanggan.member').'"', false);
        $response->assertSee('MEMBER CENTER', false);
        $response->assertDontSee('MASUK MEMBER', false);
        $response->assertDontSee('BUAT MEMBER', false);
    }

    public function test_authenticated_member_login_redirects_to_member_center(): void
    {
        $member = User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))
            ->where('status', 'aktif')
            ->whereHas('pelanggan.membership', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();

        $this->actingAs($member)->get('/login')->assertRedirect(route('pelanggan.member'));
    }

    public function test_authenticated_staff_login_redirects_to_role_dashboard(): void
    {
        $admin = User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))
            ->where('status', 'aktif')->firstOrFail();
        $petugas = User::whereHas('role', fn ($query) => $query->where('nama_role', 'petugas'))
            ->where('status', 'aktif')->firstOrFail();

        $this->actingAs($admin)->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($petugas)->get('/login')->assertRedirect(route('petugas.dashboard'));
    }

    public function test_yza_session_cookie_is_project_specific(): void
    {
        $this->assertSame('yza_automotive_laravel_session', config('session.cookie'));
        $this->assertNotSame('laravel_session', config('session.cookie'));
        $this->assertSame('http://127.0.0.1:8000', (string) config('app.url'));
    }
}
