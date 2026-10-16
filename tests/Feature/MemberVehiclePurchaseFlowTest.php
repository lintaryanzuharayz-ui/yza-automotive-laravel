<?php

namespace Tests\Feature;

use App\Models\{KendaraanPelanggan, Mobil, Pembayaran, PembayaranGateway, SerahTerimaPesananMobil, User};
use App\Services\{MemberVehiclePurchaseService, PaymentService, VehicleGalleryService, VerifiedCarOwnershipService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
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

    public function test_unpaid_invoice_distinguishes_bill_from_verified_payment(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());

        $this->actingAs($customer->user)->get(route('pelanggan.invoice.car-sale', [$order->transaksi, 'modal' => 1]))
            ->assertOk()
            ->assertSee('Total bayar')
            ->assertSee('Rp '.number_format((float) $order->transaksi->total, 0, ',', '.'))
            ->assertSee('Pembayaran belum diterima')
            ->assertDontSee('Total tagihan')
            ->assertDontSee('Sisa tagihan')
            ->assertDontSee('Pembayaran terverifikasi');
    }

    public function test_paid_invoice_shows_verified_amount_and_zero_balance(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $amount = (string) $order->total_tagihan;
        $payment = app(PaymentService::class)->create($order->id_transaksi, $this->actor(), $amount, 'transfer', null, 'Invoice paid QA');
        app(PaymentService::class)->verify($payment->id_pembayaran, $this->actor());

        $this->actingAs($customer->user)->get(route('pelanggan.invoice.car-sale', [$order->transaksi, 'modal' => 1]))
            ->assertOk()
            ->assertSee('LUNAS')
            ->assertSee('Pembayaran terverifikasi')
            ->assertSee('Total bayar')
            ->assertSee('Rp '.number_format((float) $order->transaksi->total, 0, ',', '.'))
            ->assertDontSee('Sisa tagihan');
    }

    public function test_pending_or_rejected_payment_never_counts_as_paid_in_invoice(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $payment = app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'transfer', null, 'Invoice pending QA');

        $this->actingAs($customer->user)->get(route('pelanggan.invoice.car-sale', [$order->transaksi, 'modal' => 1]))
            ->assertOk()
            ->assertSee('Total bayar')
            ->assertSee('Pembayaran belum diterima')
            ->assertSee('Rp '.number_format((float) $order->transaksi->total, 0, ',', '.'));

        app(PaymentService::class)->reject($payment->id_pembayaran, $this->actor());
        $this->assertSame('ditolak', $payment->fresh()->status);
        $this->assertSame('dibatalkan', $order->transaksi->fresh()->status);
        $this->assertSame('dibatalkan', $order->fresh()->status);
    }

    public function test_active_midtrans_attempt_still_allows_selecting_offline_payment(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $gateway = new PembayaranGateway();
        $gateway->forceFill(['id_transaksi' => $order->id_transaksi, 'provider' => 'midtrans', 'order_id' => 'YZA-ACTIVE-OFFLINE-QA', 'gross_amount' => $order->total_tagihan, 'status_provider' => 'pending', 'initiated_by' => $customer->user->id_user])->save();

        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Lanjutkan Pembayaran Midtrans')
            ->assertSee('Pilih Pembayaran Offline');
    }

    public function test_selecting_offline_supersedes_active_gateway_without_creating_duplicate_payment(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $gateway = new PembayaranGateway();
        $gateway->forceFill(['id_transaksi' => $order->id_transaksi, 'provider' => 'midtrans', 'order_id' => 'YZA-MANUAL-SELECT-QA', 'gross_amount' => $order->total_tagihan, 'status_provider' => 'pending', 'initiated_by' => $customer->user->id_user])->save();

        $this->actingAs($customer->user)->post(route('pelanggan.pesanan-mobil.payment.offline', $order), ['metode' => 'tunai'])
            ->assertRedirect()
            ->assertSessionHas('offline_payment_submitted', true)
            ->assertSessionHas('offline_payment_method', 'tunai');

        $this->assertSame('manual_selected', $gateway->fresh()->status_provider);
        $this->assertSame(1, Pembayaran::where('id_transaksi', $order->id_transaksi)->where('status', 'menunggu')->count());
    }

    public function test_member_order_progress_uses_admin_verification_state_after_offline_submission(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());

        $this->actingAs($customer->user)->post(route('pelanggan.pesanan-mobil.payment.offline', $order), ['metode' => 'tunai'])->assertRedirect();
        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi Admin')
            ->assertSee('Pengajuan pembayaran sudah diterima. Menunggu verifikasi admin.')
            ->assertDontSee('Silakan lanjutkan pembayaran.');
    }

    public function test_rejected_offline_payment_returns_member_to_payment_method_selection(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $payment = app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'transfer', null, 'Reject flow QA');
        app(PaymentService::class)->reject($payment->id_pembayaran, $this->actor());

        $this->assertSame('ditolak', $payment->fresh()->status);
        $this->assertSame('dibatalkan', $order->fresh()->status);
        $this->assertSame('dibatalkan', $order->transaksi->fresh()->status);
        $this->assertSame('tersedia', $car->fresh()->status);
        $this->assertDatabaseHas('notifikasi', [
            'id_user' => $customer->id_user,
            'judul' => 'Pembayaran ditolak',
            'referensi_tipe' => 'pesanan_mobil_member',
            'referensi_id' => $order->id_pesanan,
            'is_read' => 0,
        ]);
        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Pembayaran sebelumnya ditolak.')
            ->assertSee('Pilih Metode Pembayaran Lagi')
            ->assertDontSee('Pilih cara pembayaran');

        $this->actingAs($customer->user)->post(route('pelanggan.pesanan-mobil.payment.reopen', $order))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pesanan dibuka kembali. Silakan pilih pembayaran online atau offline.');

        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Pilih cara pembayaran')
            ->assertSee('Bayar Online melalui Midtrans')
            ->assertSee('Pembayaran Offline');
    }

    public function test_legacy_cancelled_payment_rejection_can_be_reopened_once(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $order->transaksi->forceFill(['status' => 'dibatalkan'])->save();
        $order->penjualan->forceFill(['status' => 'proses'])->save();
        $order->forceFill(['status' => 'dibatalkan', 'catatan_admin' => 'Pembayaran ditolak; alokasi stok dilepas.'])->save();
        $car->forceFill(['status' => 'tersedia'])->save();

        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Pilih Metode Pembayaran Lagi');

        $this->actingAs($customer->user)->post(route('pelanggan.pesanan-mobil.payment.reopen', $order))
            ->assertRedirect()
            ->assertSessionHas('status', 'Pesanan dibuka kembali. Silakan pilih pembayaran online atau offline.');

        $this->assertSame('menunggu_pembayaran', $order->fresh()->status);
        $this->assertSame('belum_bayar', $order->transaksi->fresh()->status);
        $this->assertSame('dibooking', $car->fresh()->status);
        $this->actingAs($customer->user)->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()
            ->assertSee('Bayar Online melalui Midtrans')
            ->assertSee('Pembayaran Offline');
    }

    public function test_invoice_keeps_checkout_snapshot_when_catalog_price_changes(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $historicalTotal = (float) $order->transaksi->total;
        $car->forceFill(['harga_jual' => $historicalTotal + 999999])->save();

        $this->actingAs($customer->user)->get(route('pelanggan.invoice.car-sale', [$order->transaksi, 'modal' => 1]))
            ->assertOk()
            ->assertSee(number_format($historicalTotal, 0, ',', '.'))
            ->assertDontSee(number_format($historicalTotal + 999999, 0, ',', '.'));
    }

    public function test_invoice_is_owner_scoped_and_admin_sees_same_payment_source(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        $other = User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))
            ->where('id_user', '<>', $customer->id_user)->first();
        if ($other) $this->actingAs($other)->get(route('pelanggan.invoice.car-sale', [$order->transaksi, 'modal' => 1]))->assertForbidden();

        $this->actingAs($this->user('admin'))->get(route('admin.invoice.car-sale', $order->transaksi))
            ->assertOk()
            ->assertSee('Status Pembayaran')
            ->assertSee(number_format((float) $order->transaksi->total, 0, ',', '.'));
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

    public function test_handover_cannot_happen_before_scheduled_pickup_time(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'tunai', null, 'Early handover QA');
        $items = array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], true);
        $service->savePdi($order, $this->actor(), $items);
        $service->schedule($order, now()->addHour()->toDateTimeString(), 'YZA Automotive', 'Early handover QA');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('sebelum jadwal pengambilan tiba');
        $service->handover($order, $this->actor(), ['nama_penerima' => 'Early Handover QA']);
    }

    public function test_only_completed_handover_creates_verified_my_garage_vehicle_and_is_not_repeatable(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $passwordHashBeforeHandover = $customer->user->getRawOriginal('password');
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->approve($service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'tunai', null, 'Stage 2 full payment');
        $items = array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], true);
        $service->savePdi($order, $this->actor(), $items);
        $service->schedule($order, now()->addHour()->toDateTimeString(), 'YZA Automotive', 'Stage 2 pickup');
        $this->assertSame(0, KendaraanPelanggan::where('id_pelanggan', $customer->id_pelanggan)->count());
        Carbon::setTestNow(now()->addHours(2));
        try {
            $service->handover($order, $this->actor(), ['nama_penerima' => 'Stage 2 Receiver', 'nomor_identitas' => 'SYNTHETIC-ID']);
        } finally {
            Carbon::setTestNow();
        }
        $this->assertSame('diserahkan', $order->fresh()->status);
        $this->assertSame('terjual', $car->fresh()->status);
        $this->assertTrue(app(VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase($customer->id_pelanggan));
        $this->assertSame(1, KendaraanPelanggan::where('id_pelanggan', $customer->id_pelanggan)->count());
        $this->assertSame($passwordHashBeforeHandover, $customer->user->fresh()->getRawOriginal('password'));
        $repeat = $service->handover($order, $this->actor(), ['nama_penerima' => 'Stage 2 Receiver']);
        $this->assertSame(1, SerahTerimaPesananMobil::where('id_pesanan', $order->id_pesanan)->count());
        $this->assertSame($repeat->id_serah_terima, SerahTerimaPesananMobil::where('id_pesanan', $order->id_pesanan)->value('id_serah_terima'));
    }

    public function test_handover_ownership_uses_purchased_color_and_matching_gallery_not_master_default(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('model', 'GR Supra')->where('status', 'tersedia')->firstOrFail();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($customer, $car, ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => 'black']);

        $this->assertSame('Obsidian Black', $order->snapshot_warna);
        $this->assertSame('Supra Red', $car->warna);

        $order = $service->approve($order, $this->actor());
        app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'tunai', null, 'Color source QA');
        $items = array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], true);
        $service->savePdi($order, $this->actor(), $items);
        $service->schedule($order, now()->addHour()->toDateTimeString(), 'YZA Automotive', 'Color source QA');
        Carbon::setTestNow(now()->addHours(2));
        try {
            $service->handover($order, $this->actor(), ['nama_penerima' => 'Color Source QA', 'nomor_identitas' => 'COLOR-QA']);
        } finally {
            Carbon::setTestNow();
        }

        $vehicle = KendaraanPelanggan::where('id_pesanan', $order->id_pesanan)->firstOrFail();
        $this->assertSame('Obsidian Black', $vehicle->warna);
        $this->assertSame('Obsidian Black', $vehicle->pesanan->snapshot_warna);
        $this->assertSame('Obsidian Black', app(VehicleGalleryService::class)->forColor($car->fresh(), $vehicle->pesanan->snapshot_warna)['label']);
        $this->assertTrue(app(VerifiedCarOwnershipService::class)->vehicleBelongsToVerifiedPurchase($customer->id_pelanggan, $vehicle->id_kendaraan_pelanggan));
    }
}
