<?php

namespace Tests\Feature;

use App\Models\{KendaraanPelanggan, Mobil, User};
use App\Services\{PaymentService, PenjualanMobilService, VerifiedCarOwnershipService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VerifiedCarOwnershipTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    public function test_manual_vehicle_does_not_unlock_exclusive_services(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $vehicle = $customer->kendaraan()->create([
            'merk' => 'Manual', 'model' => 'Input', 'nomor_polisi' => 'B 1234 QA',
            'kilometer' => 1, 'status' => 'aktif',
        ]);

        $this->assertFalse(app(VerifiedCarOwnershipService::class)->vehicleBelongsToVerifiedPurchase($customer->id_pelanggan, $vehicle->id_kendaraan_pelanggan));
    }

    public function test_completed_sale_creates_verified_customer_vehicle_and_unlocks_services(): void
    {
        $customerUser = $this->user('pelanggan');
        $customer = $customerUser->pelanggan;
        $car = Mobil::where('status', 'tersedia')->firstOrFail();
        $sale = app(PenjualanMobilService::class)->create([
            'id_pelanggan' => $customer->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'diskon' => 0,
            'dp' => 0,
            'metode_pembelian' => 'tunai',
        ], $this->user('petugas')->petugas->id_petugas);

        app(PaymentService::class)->create(
            $sale->id_transaksi,
            $this->user('petugas')->petugas->id_petugas,
            (string) $sale->total_akhir,
            'tunai',
            null,
            'ownership test'
        );

        $vehicle = KendaraanPelanggan::where('id_pelanggan', $customer->id_pelanggan)
            ->where('nomor_rangka', $car->nomor_rangka)->first();
        $this->assertNotNull($vehicle);
        $this->assertTrue(app(VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase($customer->id_pelanggan));
        $this->assertTrue(app(VerifiedCarOwnershipService::class)->vehicleBelongsToVerifiedPurchase($customer->id_pelanggan, $vehicle->id_kendaraan_pelanggan));
        $this->actingAs($customerUser)->get('/pelanggan/booking-servis/create')->assertOk();
    }
}
