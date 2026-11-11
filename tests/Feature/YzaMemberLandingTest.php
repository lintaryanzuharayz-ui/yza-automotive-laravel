<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class YzaMemberLandingTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    public function test_guest_sees_member_value_and_register_cta(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('YZA EXCLUSIVE MEMBERSHIP')
            ->assertSee('BERGABUNG JADI MEMBER')
            ->assertSee('href="'.route('register', ['from' => 'membership']).'"', false)
            ->assertSee('Rp '.number_format((float) app(\App\Services\PengaturanService::class)->membershipFee(), 0, ',', '.'), false);
    }

    public function test_pending_customer_sees_activation_cta(): void
    {
        $customer = $this->user('pelanggan');
        $customer->pelanggan->membership()->update(['status' => 'pending_payment']);

        $response = $this->actingAs($customer)->get('/');

        $response->assertOk()
            ->assertSee('AKTIFKAN YZA MEMBER')
            ->assertSee('Membership Anda belum aktif.')
            ->assertSee('href="'.route('pelanggan.membership.activation').'"', false)
            ->assertDontSee('BUKA MEMBER CENTER');
    }

    public function test_active_customer_sees_member_center_cta(): void
    {
        $customer = $this->user('pelanggan');
        $customer->pelanggan->membership()->update(['status' => 'active']);

        $response = $this->actingAs($customer)->get('/');

        $response->assertOk()
            ->assertSee('BUKA MEMBER CENTER')
            ->assertSee('✓ YZA Member Active')
            ->assertSee('href="'.route('pelanggan.member').'"', false)
            ->assertDontSee('BERGABUNG JADI MEMBER');
    }

    public function test_cancelled_customer_uses_existing_membership_status_flow(): void
    {
        $customer = $this->user('pelanggan');
        $customer->pelanggan->membership()->update(['status' => 'cancelled']);

        $response = $this->actingAs($customer)->get('/');

        $response->assertOk()
            ->assertSee('LIHAT STATUS MEMBERSHIP')
            ->assertSee('href="'.route('pelanggan.membership.activation').'"', false)
            ->assertDontSee('BUKA MEMBER CENTER');
    }

    public function test_admin_and_petugas_are_not_presented_as_active_customers(): void
    {
        foreach (['admin', 'petugas'] as $role) {
            $response = $this->actingAs($this->user($role))->get('/');

            $response->assertOk()
                ->assertSee('YZA MEMBER')
                ->assertDontSee('BUKA MEMBER CENTER')
                ->assertDontSee('AKTIFKAN YZA MEMBER')
                ->assertDontSee('✓ YZA Member Active');
        }
    }
}
