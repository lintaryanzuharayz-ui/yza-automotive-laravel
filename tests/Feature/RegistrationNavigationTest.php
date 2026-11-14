<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RegistrationNavigationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_membership_origin_uses_membership_back_link(): void
    {
        $this->get('/register?from=membership')
            ->assertOk()
            ->assertSee('KEMBALI KE MEMBERSHIP')
            ->assertSee('href="'.url('/#janji-temu').'"', false)
            ->assertSee('action="'.route('register.store', ['from' => 'membership']).'"', false);
    }

    public function test_login_origin_uses_login_back_link(): void
    {
        $this->get('/register?from=login')
            ->assertOk()
            ->assertSee('KEMBALI KE LOGIN')
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('action="'.route('register.store', ['from' => 'login']).'"', false)
            ->assertSee('data-prev-step="2"', false)
            ->assertSee('data-prev-step="3"', false)
            ->assertSee('LANJUT KE PEMBAYARAN');
    }

    public function test_direct_or_untrusted_origin_falls_back_to_login(): void
    {
        foreach (['/register', '/register?from=https%3A%2F%2Fevil.test', '/register?from=unexpected'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('KEMBALI KE LOGIN')
                ->assertSee('href="'.route('login').'"', false)
                ->assertDontSee('KEMBALI KE MEMBERSHIP');
        }
    }

    public function test_validation_redirect_keeps_membership_origin(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/register?from=membership', [])
            ->assertRedirect(route('register', ['from' => 'membership']))
            ->assertSessionHasErrors();
    }
}
