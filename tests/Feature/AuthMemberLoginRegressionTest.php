<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthMemberLoginRegressionTest extends TestCase
{
    use DatabaseTransactions;

    private function activeMember(): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))
            ->where('status', 'aktif')
            ->whereHas('pelanggan.membership', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();
    }

    public function test_active_member_login_redirects_to_member_center_and_persists_session(): void
    {
        $user = $this->activeMember();
        $password = Str::random(32);
        $user->forceFill(['password' => Hash::make($password)])->save();

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->post('/login', ['identity' => $user->username, 'password' => $password])
            ->assertRedirect(route('pelanggan.member'));

        $this->get('/pelanggan/member')->assertOk();
        $this->get('/pelanggan/servis')->assertOk();
        $this->post('/logout')->assertRedirect(route('login'));
        $this->get('/pelanggan/member')->assertRedirect(route('login'));
    }

    public function test_active_member_can_login_by_email_and_open_my_garage_without_changing_credential(): void
    {
        $user = $this->activeMember();
        $password = Str::random(32);
        $user->forceFill(['password' => Hash::make($password)])->save();
        $hashBeforeGarage = $user->fresh()->getRawOriginal('password');

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->post('/login', ['identity' => $user->email, 'password' => $password])
            ->assertRedirect(route('pelanggan.member'));

        $this->get(route('pelanggan.kendaraan.index'))->assertOk();
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame($hashBeforeGarage, $user->fresh()->getRawOriginal('password'));
    }

    public function test_invalid_password_and_inactive_member_are_rejected(): void
    {
        $user = $this->activeMember();
        $password = Str::random(32);
        $user->forceFill(['password' => Hash::make($password)])->save();

        $this->withoutMiddleware(VerifyCsrfToken::class)
            ->post('/login', ['identity' => $user->username, 'password' => Str::random(32)])
            ->assertSessionHasErrors('identity');

        $user->forceFill(['status' => 'nonaktif'])->save();

        $this->post('/login', ['identity' => $user->username, 'password' => $password])
            ->assertSessionHasErrors('identity');
    }
}
