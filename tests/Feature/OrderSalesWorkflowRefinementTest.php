<?php

namespace Tests\Feature;

use App\Models\{Mobil, PesananMobilMember, User};
use App\Services\{MemberVehiclePurchaseService, PenjualanMobilService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Artisan, DB};
use Tests\Support\PromoIntegrityFixtures;
use Tests\TestCase;

class OrderSalesWorkflowRefinementTest extends TestCase
{
    use DatabaseTransactions, PromoIntegrityFixtures, \Tests\Support\CreatesPaidMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activatePaidMember($this->user('pelanggan')->pelanggan);
    }

    private function user(string $role = 'admin'): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    private function input(?Mobil $car = null): array
    {
        return ['id_pelanggan' => $this->user('pelanggan')->pelanggan->id_pelanggan,
            'id_mobil' => ($car ?? $this->stage4Car())->id_mobil, 'nama_pembeli' => 'Pembeli QA',
            'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat pengujian', 'syarat_disetujui' => '1'];
    }

    public function test_add_order_opens_native_dialog_and_sales_remains_full_form(): void
    {
        $this->actingAs($this->user())->get(route('admin.pesanan-mobil.index'))->assertOk()
            ->assertSee('data-order-create-open', false)->assertSee('<dialog id="order-create-dialog"', false)
            ->assertSee(route('admin.pesanan-mobil.store'), false);
        $this->get(route('admin.penjualan-mobil.create'))->assertOk()->assertDontSee('id="sale-create-form"', false)
            ->assertSee('Lanjutkan penjualan dari pesanan');
        $this->get(route('admin.penjualan-mobil.index'))->assertOk()
            ->assertDontSee('admin-selling-benefits', false)->assertDontSee('Buat Penjualan');
    }

    public function test_order_creation_reserves_without_invoice_payment_ownership_or_handover(): void
    {
        $input = $this->input();
        $counts = $this->counts();
        $this->actingAs($this->user())->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasNoErrors()->assertRedirect();
        $order = PesananMobilMember::where('id_mobil', $input['id_mobil'])->firstOrFail();
        $this->assertSame('menunggu_verifikasi', $order->status);
        $this->assertNull($order->id_penjualan); $this->assertNull($order->id_transaksi);
        $this->assertSame($counts, $this->counts());
        $this->assertSame('dibooking', $order->mobil->status);
        $this->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasErrors('order', null, 'createOrder');
        $this->assertSame(1, PesananMobilMember::where('id_mobil', $input['id_mobil'])->count());
    }

    private function counts(): array
    {
        return collect(['transaksi', 'penjualan_mobil', 'pembayaran', 'kendaraan_pelanggan', 'serah_terima_pesanan_mobil'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()])->all();
    }

    public function test_invalid_inactive_and_unavailable_orders_are_rejected(): void
    {
        $this->actingAs($this->user())->post(route('admin.pesanan-mobil.store'), [])
            ->assertSessionHasErrors(['id_pelanggan', 'id_mobil', 'nama_pembeli', 'telepon_pembeli', 'alamat_pembeli', 'syarat_disetujui'], null, 'createOrder');
        $input = $this->input();
        $this->user('pelanggan')->pelanggan->update(['status' => 'nonaktif']);
        $this->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasErrors('order', null, 'createOrder');
        $this->user('pelanggan')->pelanggan->update(['status' => 'aktif']);
        Mobil::findOrFail($input['id_mobil'])->update(['status' => 'terjual']);
        $this->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasErrors('order', null, 'createOrder');
        $this->assertDatabaseMissing('pesanan_mobil_member', ['id_mobil' => $input['id_mobil']]);
    }

    public function test_member_and_owner_cannot_mutate_and_sales_uses_existing_permission(): void
    {
        Artisan::call('yza:seed-local-staff');
        $input = $this->input();
        foreach (['pelanggan', 'owner', 'finance', 'mechanic'] as $role) {
            $this->actingAs($this->user($role))->post(route('admin.pesanan-mobil.store'), $input)->assertForbidden();
            $this->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput(Mobil::findOrFail($input['id_mobil'])))->assertForbidden();
        }
        $this->actingAs($this->user('sales'))->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasNoErrors();
    }

    public function test_existing_approved_order_reuses_sale_invoice_and_reservation(): void
    {
        $car = $this->stage4Car();
        $inventory = app(VehicleInventoryService::class); $inventory->ensure($car, 5);
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($this->user('pelanggan')->pelanggan, $car, $this->input($car));
        $order = $service->approve($order, $this->stage4Actor());
        $counts = $this->counts(); $reserved = $inventory->reserveCount($car);
        $this->actingAs($this->user());
        for ($i = 0; $i < 2; $i++) {
            $this->get(route('admin.penjualan-mobil.create', ['id_pesanan' => $order->id_pesanan]))
                ->assertRedirect(route('admin.penjualan-mobil.show', $order->id_penjualan));
        }
        $this->assertSame($counts, $this->counts()); $this->assertSame($reserved, $inventory->reserveCount($car));
        $this->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car) + ['id_pesanan' => $order->id_pesanan])
            ->assertRedirect(route('admin.penjualan-mobil.show', $order->id_penjualan));
        $this->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car))->assertSessionHasErrors('id_pesanan');
        $this->assertSame($counts, $this->counts());
    }

    public function test_pending_or_tampered_order_cannot_be_continued_as_sale(): void
    {
        $car = $this->stage4Car(); $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($this->user('pelanggan')->pelanggan, $car, $this->input($car));
        $this->actingAs($this->user())->get(route('admin.penjualan-mobil.create', ['id_pesanan' => $order->id_pesanan]))->assertUnprocessable();
        $order = $service->approve($order, $this->stage4Actor());
        $order->penjualan->update(['id_mobil' => $this->stage4Car()->id_mobil]);
        $this->get(route('admin.penjualan-mobil.create', ['id_pesanan' => $order->id_pesanan]))->assertUnprocessable();
    }

    public function test_direct_sale_is_unpaid_and_duplicate_submission_is_rejected_for_aggregate_stock(): void
    {
        $car = $this->stage4Car(); app(VehicleInventoryService::class)->ensure($car, 5);
        $input = $this->stage4SaleInput($car);
        $before = DB::table('pembayaran')->count();
        $this->actingAs($this->user())->post(route('admin.penjualan-mobil.store'), $input)
            ->assertSessionHasErrors('id_pesanan');
        $this->assertSame($before, DB::table('pembayaran')->count());
        $this->assertSame(0, \App\Models\PenjualanMobil::where('id_mobil', $car->id_mobil)->count());
    }

    public function test_promo_preview_uses_selected_customer_and_catalog_price_without_writes(): void
    {
        $car = $this->stage4Car(); $promo = $this->stage4Promo(['berlaku_untuk' => 'penjualan_mobil']);
        $input = $this->stage4SaleInput($car) + ['promo_id' => $promo->id_promo, 'subtotal' => 1];
        $counts = $this->counts();
        $this->actingAs($this->user())->getJson(route('admin.penjualan-mobil.promo-preview', $input))
            ->assertOk()->assertJsonPath('subtotal', 100000000)->assertJsonPath('discount', 10000000)->assertJsonPath('eligible', true);
        $this->assertSame($counts, $this->counts());
        $promo->update(['member_only' => true]);
        $this->user('pelanggan')->pelanggan->membership?->update(['status' => 'cancelled']);
        $this->getJson(route('admin.penjualan-mobil.promo-preview', $input))->assertUnprocessable();
        $this->actingAs($this->user('pelanggan'))->getJson(route('admin.penjualan-mobil.promo-preview', $input))->assertForbidden();
    }

    public function test_promo_failure_rolls_back_direct_sale_and_stock(): void
    {
        $car = $this->stage4Car(); $promo = $this->stage4Promo(['member_only' => true]);
        $this->user('pelanggan')->pelanggan->membership?->update(['status' => 'cancelled']);
        $counts = $this->counts();
        $page = $this->actingAs($this->user())->get(route('admin.penjualan-mobil.create'))->assertOk();
        $this->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car) + ['promo_id' => $promo->id_promo, 'submission_key' => $page->viewData('submissionKey')])->assertSessionHasErrors();
        $this->assertSame($counts, $this->counts()); $this->assertSame('tersedia', $car->fresh()->status);
    }

    public function test_missing_or_forged_submission_key_cannot_create_sale(): void
    {
        $car = $this->stage4Car(); $counts = $this->counts();
        $this->actingAs($this->user())->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car))
            ->assertSessionHasErrors('id_pesanan');
        $this->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car) + ['submission_key' => (string) \Illuminate\Support\Str::uuid()])
            ->assertSessionHasErrors('id_pesanan');
        $this->assertSame($counts, $this->counts());
    }

    public function test_aggregate_order_duplicate_and_exhausted_stock_are_rejected(): void
    {
        $car = $this->stage4Car(); $inventory = app(VehicleInventoryService::class)->ensure($car, 2);
        $input = $this->input($car);
        $this->actingAs($this->user())->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasNoErrors();
        $this->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasErrors('order', null, 'createOrder');
        $this->assertSame(1, PesananMobilMember::where('id_mobil', $car->id_mobil)->count());
        $inventory->update(['stock_total' => 1]);
        $other = $this->newPaidMember('Pembeli stok QA');
        $this->post(route('admin.pesanan-mobil.store'), array_replace($input, ['id_pelanggan' => $other->id_pelanggan]))
            ->assertSessionHasErrors('order', null, 'createOrder');
        $this->assertDatabaseMissing('pesanan_mobil_member', ['id_mobil' => $car->id_mobil, 'id_pelanggan' => $other->id_pelanggan]);
    }

    public function test_sales_permission_can_create_direct_sale_and_no_revenue_fallback_to_contract(): void
    {
        Artisan::call('yza:seed-local-staff');
        $car = $this->stage4Car();
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($this->user('pelanggan')->pelanggan, $car, $this->input($car));
        $order = $service->approve($order, $this->stage4Actor());
        $this->actingAs($this->user('sales'))->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car))
            ->assertSessionHasErrors('id_pesanan');
        $this->post(route('admin.penjualan-mobil.store'), $this->stage4SaleInput($car) + ['id_pesanan' => $order->id_pesanan])
            ->assertRedirect(route('admin.penjualan-mobil.show', $order->id_penjualan));
        DB::table('pembayaran')->whereIn('id_transaksi', DB::table('penjualan_mobil')->select('id_transaksi'))->update(['status' => 'menunggu']);
        $this->get(route('admin.penjualan-mobil.index'))->assertOk()->assertViewHas('stats', fn ($stats) => $stats['pendapatan_terverifikasi'] === 0.0);
    }

    public function test_dp_exceeding_post_promo_total_rolls_back_sale_and_redemption(): void
    {
        $car = $this->stage4Car(); $promo = $this->stage4Promo(); $counts = $this->counts();
        $redemptions = DB::table('promo_redemptions')->count();
        try {
            app(PenjualanMobilService::class)->createWithPromo(array_replace($this->stage4SaleInput($car), ['dp' => '95000000']), $this->stage4Actor(), $promo->id_promo);
            $this->fail('DP above final invoice total must be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertSame('DP melebihi total setelah promo.', $e->getMessage());
        }
        $this->assertSame($counts, $this->counts());
        $this->assertSame($redemptions, DB::table('promo_redemptions')->count());
        $this->assertSame('tersedia', $car->fresh()->status);
    }
}
