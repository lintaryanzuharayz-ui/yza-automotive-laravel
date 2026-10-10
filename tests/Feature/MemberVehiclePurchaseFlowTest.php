<?php

namespace Tests\Feature;

use App\Models\{Mobil, User};
use App\Services\MemberVehiclePurchaseService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

class MemberVehiclePurchaseFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User { return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail(); }
    private function actor(): int { return (int) $this->user('petugas')->petugas->id_petugas; }

    public function test_member_checkout_snapshots_server_price_and_reserves_unit(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('status', 'tersedia')->firstOrFail();
        $order = app(MemberVehiclePurchaseService::class)->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'email_pembeli' => $customer->user?->email, 'telepon_pembeli' => $customer->no_hp ?: '08123456789', 'alamat_pembeli' => $customer->alamat ?: 'Alamat QA']);
        $this->assertSame((string) $car->harga_jual, (string) $order->snapshot_harga);
        $this->assertSame('menunggu_verifikasi', $order->status);
        $this->assertSame('dibooking', $car->fresh()->status);
    }

    public function test_admin_approval_reuses_existing_transaction_and_sale_tables(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('status', 'tersedia')->firstOrFail();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']);
        $approved = $service->approve($order, $this->actor(), 'QA approval');
        $this->assertNotNull($approved->id_transaksi);
        $this->assertNotNull($approved->id_penjualan);
        $this->assertSame('penjualan_mobil', $approved->transaksi->jenis_transaksi);
        $this->assertSame('belum_bayar', $approved->transaksi->status);
    }

    public function test_pdi_cannot_be_completed_before_full_payment(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('status', 'tersedia')->firstOrFail();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $this->expectException(RuntimeException::class);
        $service->savePdi($order, $this->actor(), ['mesin' => true]);
    }
}
