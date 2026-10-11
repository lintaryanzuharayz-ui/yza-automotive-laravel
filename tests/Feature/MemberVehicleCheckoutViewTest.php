<?php

namespace Tests\Feature;

use App\Models\{Mobil, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberVehicleCheckoutViewTest extends TestCase
{
    use DatabaseTransactions;

    public function test_member_checkout_renders_premium_vehicle_summary_and_form(): void
    {
        $user = User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))->firstOrFail();
        $car = Mobil::where('model', 'GR Supra')->where('status', 'tersedia')->firstOrFail();

        $response = $this->actingAs($user)->get(route('pelanggan.pesanan-mobil.create', ['mobil' => $car]));

        $response->assertOk()
            ->assertSee('Checkout Kendaraan')
            ->assertSee('Data Pembeli')
            ->assertSee('Ringkasan Pesanan')
            ->assertSee('Kirim Pesanan')
            ->assertSee('Toyota GR Supra')
            ->assertSee('Rp 2.350.000.000')
            ->assertSee('syarat_disetujui', false)
            ->assertSee(route('pelanggan.pesanan-mobil.store', $car), false);
    }
}
