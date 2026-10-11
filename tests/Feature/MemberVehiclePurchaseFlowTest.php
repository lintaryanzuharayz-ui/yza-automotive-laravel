<?php

namespace Tests\Feature;

use App\Models\{KendaraanPelanggan, Mobil, User};
use App\Services\{MemberVehiclePurchaseService, PaymentService, VerifiedCarOwnershipService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

class MemberVehiclePurchaseFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User { return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail(); }
    private function car(): Mobil { return Mobil::where('status', 'tersedia')->whereNotIn('model', ['GR Supra', 'Supra MK4', 'Revuelto', 'SF90 Stradale', '911 GT3 RS', 'GT Black Series', 'DBS 770 Ultimate', 'Corvette Z06', 'Mustang Shelby GT500', 'Challenger SRT Hellcat Redeye', 'GT-R Nismo', 'LFA'])->firstOrFail(); }
    private function actor(): int { return (int) $this->user('petugas')->petugas->id_petugas; }

    public function test_member_checkout_snapshots_server_price_and_reserves_unit(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $order = app(MemberVehiclePurchaseService::class)->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'email_pembeli' => $customer->user?->email, 'telepon_pembeli' => $customer->no_hp ?: '08123456789', 'alamat_pembeli' => $customer->alamat ?: 'Alamat QA']);
        $this->assertSame((string) $car->harga_jual, (string) $order->snapshot_harga);
        $this->assertSame('menunggu_verifikasi', $order->status);
        $this->assertSame('dibooking', $car->fresh()->status);
    }

    public function test_member_checkout_snapshots_validated_color_without_changing_price_or_inventory(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('model', 'GR Supra')->where('status', 'tersedia')->firstOrFail();
        $price = (string) $car->harga_jual;
        $order = app(MemberVehiclePurchaseService::class)->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => 'white']);

        $this->assertSame('Pearl White', $order->snapshot_warna);
        $this->assertSame($price, (string) $order->snapshot_harga);
        $this->assertSame((string) $car->status, (string) $car->fresh()->status);
    }

    public function test_member_order_detail_renders_premium_dashboard_for_owned_order(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('model', 'GR Supra')->where('status', 'tersedia')->firstOrFail();
        $order = app(MemberVehiclePurchaseService::class)->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => 'white']);

        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Informasi Pembeli')
            ->assertSee('Ringkasan Pembayaran')
            ->assertSee('Langkah Selanjutnya')
            ->assertSee('Pearl White')
            ->assertSee('order-vehicle-thumbs', false)
            ->assertSee('Rear 3/4')
            ->assertSee('Interior / cockpit')
            ->assertSee('data-invoice-url', false)
            ->assertSee('member-order-detail.css', false);
    }

    public function test_member_order_index_renders_tracking_dashboard_and_filters_owned_orders(): void
    {
        $member = $this->user('pelanggan');
        $customer = $member->pelanggan;
        $car = $this->car();
        app(MemberVehiclePurchaseService::class)->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']);
        $response = $this->actingAs($member)->get(route('pelanggan.pesanan-mobil.index', ['q' => 'ORD-', 'sort' => 'action']));

        $response->assertOk()
            ->assertSee('Pesanan Mobil Saya')
            ->assertSee('Total Pesanan')
            ->assertSee('Butuh Tindakan')
            ->assertSee('member-order-card', false)
            ->assertSee('member-order-timeline', false)
            ->assertSee('Lihat Detail');
    }

    public function test_admin_approval_reuses_existing_transaction_and_sale_tables(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']);
        $approved = $service->approve($order, $this->actor(), 'QA approval');
        $this->assertNotNull($approved->id_transaksi);
        $this->assertNotNull($approved->id_penjualan);
        $this->assertSame('penjualan_mobil', $approved->transaksi->jenis_transaksi);
        $this->assertSame('belum_bayar', $approved->transaksi->status);
    }
    public function test_member_car_invoice_modal_returns_owned_invoice_fragment(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $this->actingAs($customer->user)->get(route('pelanggan.invoice.car-sale', [$order->transaksi, 'modal' => 1]))
            ->assertOk()
            ->assertSee('Invoice Pembayaran')
            ->assertSee('Kendaraan Pesanan')
            ->assertDontSee('<!doctype html>', false);
    }

    public function test_pdi_cannot_be_completed_before_full_payment(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $this->expectException(RuntimeException::class);
        $service->savePdi($order, $this->actor(), ['mesin' => true]);
    }

    public function test_second_checkout_cannot_double_sell_reserved_unit_and_rejection_releases_it(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']);
        $this->expectException(RuntimeException::class);
        try {
            $service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']);
        } finally {
            $service->reject($order, $this->actor(), 'Unit tidak tersedia setelah verifikasi.');
            $this->assertSame('tersedia', $car->fresh()->status);
        }
    }

    public function test_pending_payment_does_not_complete_sale_but_verified_payment_marks_order_lunas(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $payment = app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'transfer', null, 'Stage 2 pending');
        $this->assertSame('menunggu', $payment->status);
        $this->assertSame('belum_bayar', $order->transaksi->fresh()->status);
        app(PaymentService::class)->verify($payment->id_pembayaran, $this->actor());
        $this->assertSame('lunas', $order->transaksi->fresh()->status);
        $this->assertSame('lunas', $order->fresh()->status);
        $this->assertSame('proses', $order->penjualan->fresh()->status);
        $this->assertSame('dibooking', $car->fresh()->status);
    }

    public function test_failed_pdi_cannot_be_handed_over(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'tunai', null, 'Stage 2 full payment');
        $service->savePdi($order, $this->actor(), ['mesin' => true, 'final_quality' => false]);
        $this->assertSame('pdi_gagal', $order->fresh()->status);
        $this->expectException(RuntimeException::class);
        $service->handover($order, $this->actor(), ['nama_penerima' => 'Stage 2 Receiver']);
    }

    public function test_only_completed_handover_creates_verified_my_garage_vehicle_and_is_not_repeatable(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'tunai', null, 'Stage 2 full payment');
        $items = array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], true);
        $service->savePdi($order, $this->actor(), $items);
        $this->assertSame(0, KendaraanPelanggan::where('id_pelanggan', $customer->id_pelanggan)->count());
        $service->handover($order, $this->actor(), ['nama_penerima' => 'Stage 2 Receiver', 'nomor_identitas' => 'SYNTHETIC-ID']);
        $this->assertSame('diserahkan', $order->fresh()->status);
        $this->assertSame('terjual', $car->fresh()->status);
        $this->assertTrue(app(VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase($customer->id_pelanggan));
        $this->assertSame(1, KendaraanPelanggan::where('id_pelanggan', $customer->id_pelanggan)->count());
        $this->expectException(RuntimeException::class);
        $service->handover($order, $this->actor(), ['nama_penerima' => 'Stage 2 Receiver']);
    }
}
