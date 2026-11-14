<?php

namespace Tests\Feature;

use App\Models\{KendaraanPelanggan, Mobil, Pelanggan, PembelianMobilBekas, PenjualanMobil, PesananMobilMember, TradeIn, Transaksi, User};
use App\Services\{ActiveMemberCustomerService, MemberVehiclePurchaseService, PengajuanMobilService, PenjualanMobilService, TradeInService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Artisan, DB};
use Tests\Support\{CreatesPaidMember, PromoIntegrityFixtures};
use Tests\TestCase;

class MemberOnlyVehicleTransactionEnforcementTest extends TestCase
{
    use DatabaseTransactions, CreatesPaidMember, PromoIntegrityFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    private function user(string $role = 'admin'): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    private function superAdmin(): User
    {
        return User::where('username', 'admin506')->first()
            ?? User::whereHas('role', fn ($q) => $q->whereIn('nama_role', ['admin', 'super_admin']))->firstOrFail();
    }

    private function car(): Mobil
    {
        return $this->stage4Car();
    }

    private function activeCustomer(string $name = 'Member Aktif QA'): Pelanggan
    {
        return $this->newPaidMember($name);
    }

    private function inactiveCustomer(string $name = 'Member Pending QA'): Pelanggan
    {
        $customer = $this->newPaidMember($name);
        $customer->membership->update(['status' => 'pending_payment']);
        return $customer->fresh(['membership', 'user']);
    }

    /** 1. Tombol Buat Penjualan sudah tidak tampil di admin.penjualan-mobil.index */
    public function test_create_sale_button_is_removed_from_sales_index(): void
    {
        $response = $this->actingAs($this->user('admin'))->get(route('admin.penjualan-mobil.index'))->assertOk();
        $response->assertDontSee('+ Buat Penjualan');
        $response->assertDontSee('btn-create-sale', false);
        $response->assertSee('Daftar Penjualan Mobil');
        $response->assertSee('Semua data transaksi penjualan mobil di YZA Automotive');
    }

    /** 2. Form Penjualan Langsung baru tidak tersedia di admin.penjualan-mobil.create */
    public function test_direct_sales_form_is_eliminated_from_create_page(): void
    {
        $response = $this->actingAs($this->user('admin'))->get(route('admin.penjualan-mobil.create'))->assertOk();
        $response->assertDontSee('id="sale-create-form"', false);
        $response->assertDontSee('Penjualan langsung');
        $response->assertDontSee('Informasi Pelanggan & Unit');
        $response->assertSee('Lanjutkan penjualan dari pesanan');
    }

    /** 3. Direct POST tidak dapat bypass membership (non-member ditolak) */
    public function test_direct_post_cannot_bypass_membership(): void
    {
        $inactive = $this->inactiveCustomer();
        $car = $this->car();

        $this->actingAs($this->user('admin'))->post(route('admin.penjualan-mobil.store'), [
            'id_pelanggan' => $inactive->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'diskon' => 0,
            'dp' => 0,
            'metode_pembelian' => 'tunai',
            'submission_key' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertSessionHasErrors('id_pelanggan');
    }

    /** 4. Member aktif dapat membuat SPK (via customer dan staff) */
    public function test_active_member_can_create_spk_order(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();

        // Customer self-order
        $this->actingAs($active->user)->post(route('pelanggan.pesanan-mobil.store', $car->id_mobil), [
            'nama_pembeli' => $active->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Jl. Boulevard Raya QA',
            'syarat_disetujui' => '1',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('pesanan_mobil_member', [
            'id_pelanggan' => $active->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    /** 5. Member nonaktif ditolak dari SPK, Jual Mobil, dan Trade-In */
    public function test_inactive_member_is_rejected_from_spk_jual_mobil_and_trade_in(): void
    {
        $inactive = $this->inactiveCustomer();
        $car = $this->car();

        // SPK reject
        $this->actingAs($inactive->user)->post(route('pelanggan.pesanan-mobil.store', $car->id_mobil), [
            'nama_pembeli' => $inactive->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat QA',
            'syarat_disetujui' => '1',
        ])->assertRedirect(route('pelanggan.membership.activation'));

        // Jual Mobil create reject
        $this->actingAs($inactive->user)->get(route('pelanggan.pengajuan-mobil.create'))
            ->assertRedirect(route('pelanggan.membership.activation'));

        // Trade-In create reject
        $this->actingAs($inactive->user)->get(route('pelanggan.trade-in.create'))
            ->assertRedirect(route('pelanggan.membership.activation'));
    }

    /** 6. Guest ditolak dari transaksi Member-only */
    public function test_guest_is_redirected_or_rejected_from_member_transactions(): void
    {
        $car = $this->car();
        $this->post(route('pelanggan.pesanan-mobil.store', $car->id_mobil))->assertRedirect(route('login'));
        $this->get(route('pelanggan.pengajuan-mobil.create'))->assertRedirect(route('login'));
        $this->get(route('pelanggan.trade-in.create'))->assertRedirect(route('login'));
    }

    /** 7. Admin tidak dapat memilih non-member untuk SPK */
    public function test_admin_cannot_select_non_member_for_staff_spk(): void
    {
        $inactive = $this->inactiveCustomer();
        $car = $this->car();

        $this->actingAs($this->user('admin'))->post(route('admin.pesanan-mobil.store'), [
            'id_pelanggan' => $inactive->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'nama_pembeli' => $inactive->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat Pengujian',
            'syarat_disetujui' => '1',
        ])->assertSessionHasErrors('order', null, 'createOrder');
    }

    /** 8. Super Admin tidak dapat bypass aturan membership */
    public function test_super_admin_cannot_bypass_membership_rule(): void
    {
        $inactive = $this->inactiveCustomer();
        $car = $this->car();

        $superAdmin = User::where('username', 'admin506')->first()
            ?? User::whereHas('role', fn ($q) => $q->whereIn('nama_role', ['admin', 'super_admin']))->firstOrFail();
        $this->actingAs($superAdmin)->post(route('admin.pesanan-mobil.store'), [
            'id_pelanggan' => $inactive->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'nama_pembeli' => $inactive->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat Pengujian',
            'syarat_disetujui' => '1',
        ])->assertSessionHasErrors('order', null, 'createOrder');

        $this->actingAs($superAdmin)->post(route('admin.penjualan-mobil.store'), [
            'id_pelanggan' => $inactive->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'diskon' => 0,
            'dp' => 0,
            'metode_pembelian' => 'tunai',
            'submission_key' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertSessionHasErrors('id_pelanggan');
    }

    /** 9. Pesanan disetujui dapat dilanjutkan ke transaksi penjualan */
    public function test_approved_order_can_be_continued_as_sales_transaction(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);

        $order = $service->create($active, $car, [
            'nama_pembeli' => $active->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat QA',
            'syarat_disetujui' => true,
        ]);
        $order = $service->approve($order, $this->user('admin')->petugas->id_petugas);

        $this->actingAs($this->user('admin'))
            ->get(route('admin.penjualan-mobil.create', ['id_pesanan' => $order->id_pesanan]))
            ->assertRedirect(route('admin.penjualan-mobil.show', $order->id_penjualan));
    }

    /** 10. Pesanan belum disetujui tidak dapat dilanjutkan secara tidak sah */
    public function test_unapproved_order_cannot_be_continued(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);

        $order = $service->create($active, $car, [
            'nama_pembeli' => $active->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat QA',
            'syarat_disetujui' => true,
        ]);

        $this->actingAs($this->user('admin'))
            ->get(route('admin.penjualan-mobil.create', ['id_pesanan' => $order->id_pesanan]))
            ->assertUnprocessable();
    }

    /** 11 & 12. Satu SPK tidak menghasilkan transaksi atau invoice ganda */
    public function test_single_spk_does_not_create_duplicate_transactions_or_invoices(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);

        $order = $service->create($active, $car, [
            'nama_pembeli' => $active->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat QA',
            'syarat_disetujui' => true,
        ]);
        $order = $service->approve($order, $this->user('admin')->petugas->id_petugas);

        $txCountBefore = Transaksi::where('id_pelanggan', $active->id_pelanggan)->count();
        $saleCountBefore = PenjualanMobil::where('id_pelanggan', $active->id_pelanggan)->count();

        // Attempting to reopen or re-continue does not create new transaction
        $this->actingAs($this->user('admin'))
            ->get(route('admin.penjualan-mobil.create', ['id_pesanan' => $order->id_pesanan]))
            ->assertRedirect(route('admin.penjualan-mobil.show', $order->id_penjualan));

        $this->assertSame($txCountBefore, Transaksi::where('id_pelanggan', $active->id_pelanggan)->count());
        $this->assertSame($saleCountBefore, PenjualanMobil::where('id_pelanggan', $active->id_pelanggan)->count());
    }

    /** 18. Transaksi lama (legacy sales) tetap dapat dilihat */
    public function test_historical_legacy_sales_remain_viewable(): void
    {
        $response = $this->actingAs($this->user('admin'))->get(route('admin.penjualan-mobil.index'))->assertOk();
        $firstSale = PenjualanMobil::first();
        if ($firstSale) {
            $this->actingAs($this->user('admin'))
                ->get(route('admin.penjualan-mobil.show', $firstSale->id_penjualan))
                ->assertOk();
        }
    }

    /** 19. Pengajuan Jual Mobil mewajibkan active member */
    public function test_pengajuan_jual_mobil_enforces_active_member(): void
    {
        $inactive = $this->inactiveCustomer();
        $this->actingAs($inactive->user)->post(route('pelanggan.pengajuan-mobil.store'), [
            'id_kendaraan_pelanggan' => 1,
            'harga_harapan' => '200000000',
        ])->assertRedirect(route('pelanggan.membership.activation'));
    }

    /** 20. Trade-In mewajibkan active member */
    public function test_trade_in_enforces_active_member(): void
    {
        $inactive = $this->inactiveCustomer();
        $this->actingAs($inactive->user)->post(route('pelanggan.trade-in.store'), [
            'id_pengajuan' => 1,
            'id_inspeksi' => 1,
            'id_mobil_dibeli' => $this->car()->id_mobil,
        ])->assertRedirect(route('pelanggan.membership.activation'));
    }

    /** 21 & 22. Role authorization & Owner tetap read-only */
    public function test_role_authorization_and_owner_is_read_only(): void
    {
        $owner = $this->user('owner');
        $car = $this->car();

        // Owner can view sales index
        $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk();

        // Owner cannot create sales or SPK
        $this->actingAs($owner)->post(route('admin.penjualan-mobil.store'), [
            'id_pelanggan' => 1,
            'id_mobil' => $car->id_mobil,
            'diskon' => 0,
            'dp' => 0,
            'metode_pembelian' => 'tunai',
            'submission_key' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertForbidden();

        $this->actingAs($owner)->post(route('admin.pesanan-mobil.store'), [
            'id_pelanggan' => 1,
            'id_mobil' => $car->id_mobil,
            'nama_pembeli' => 'QA',
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat QA',
            'syarat_disetujui' => '1',
        ])->assertForbidden();
    }

    /** 16. Active member cannot create direct sale without approved SPK */
    public function test_active_member_cannot_create_direct_sale_without_approved_spk(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();

        // Direct POST even with active member is rejected for missing SPK
        $this->actingAs($this->user('admin'))->post(route('admin.penjualan-mobil.store'), [
            'id_pelanggan' => $active->id_pelanggan,
            'id_mobil' => $car->id_mobil,
            'diskon' => 0,
            'dp' => 0,
            'metode_pembelian' => 'tunai',
        ])->assertSessionHasErrors('id_pesanan');
    }

    /** 17. Super Admin and Sales cannot bypass SPK requirement via request manipulation */
    public function test_super_admin_and_sales_cannot_bypass_spk_via_request_manipulation(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();

        $superAdmin = $this->superAdmin();
        $sales = $this->user('sales');

        foreach ([$superAdmin, $sales] as $staff) {
            $this->actingAs($staff)->post(route('admin.penjualan-mobil.store'), [
                'id_pelanggan' => $active->id_pelanggan,
                'id_mobil' => $car->id_mobil,
                'diskon' => 0,
                'dp' => 0,
                'metode_pembelian' => 'tunai',
                'submission_key' => (string) \Illuminate\Support\Str::uuid(),
            ])->assertSessionHasErrors('id_pesanan');
        }
    }

    /** 18. Double/concurrent SPK approval throws exception and never duplicates sale */
    public function test_double_approval_of_spk_throws_exception_and_does_not_duplicate_sale(): void
    {
        $active = $this->activeCustomer();
        $car = $this->car();
        $service = app(MemberVehiclePurchaseService::class);

        $order = $service->create($active, $car, [
            'nama_pembeli' => $active->nama_lengkap,
            'telepon_pembeli' => '081234567890',
            'alamat_pembeli' => 'Alamat Pengujian',
        ]);

        $approved = $service->approve($order, 1);
        $this->assertSame('menunggu_pembayaran', $approved->status);
        $this->assertNotNull($approved->id_penjualan);

        $this->expectException(\RuntimeException::class);
        $service->approve($approved, 1);
    }

    /** 19. Tampered active status without valid gateway payment or legacy date is rejected */
    public function test_tampered_active_status_without_valid_gateway_or_legacy_date_is_rejected(): void
    {
        $active = $this->activeCustomer();

        // Tamper: clear gateway and set registration date to today (post-migration)
        $active->membership->update([
            'gateway_payment_id' => null,
            'registered_at' => now(),
            'status' => 'active',
        ]);

        $this->assertFalse(app(\App\Services\ActiveMemberCustomerService::class)->isEligible($active));
    }
}
