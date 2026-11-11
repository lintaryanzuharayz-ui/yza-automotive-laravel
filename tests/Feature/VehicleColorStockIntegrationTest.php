<?php

namespace Tests\Feature;

use App\Models\{Mobil, MobilWarna, MemberVehiclePreorder, Notifikasi, User};
use App\Services\{ActiveMemberCustomerService, MemberVehiclePreorderService, MemberVehiclePurchaseService, PreorderInventoryNotificationService, VehicleAvailabilityService, VehicleColorStockService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleColorStockIntegrationTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\PromoIntegrityFixtures;

    private function model(): Mobil
    {
        $car = $this->stage4Car();
        $car->update(['warna' => 'Pearl White', 'showcase_slug' => null, 'catalog_identity' => null]);
        foreach (['Pearl White', 'Red', 'Silver Metallic', 'Matte Black'] as $index => $name) {
            MobilWarna::create(['id_mobil' => $car->getKey(), 'nama' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'hex' => '#aabbcc', 'is_default' => $index === 0, 'urutan' => $index]);
        }
        return $car;
    }

    private function customer() { return app(ActiveMemberCustomerService::class)->query()->firstOrFail(); }
    private function colors(Mobil $car) { return collect(app(VehicleColorStockService::class)->colors($car)); }
    private function unit(Mobil $car, string $color, string $status = 'tersedia'): Mobil
    {
        $copy = $car->replicate(); $key = bin2hex(random_bytes(6));
        $copy->forceFill(['kode_mobil' => 'CS-'.$key, 'nomor_rangka' => 'CS-VIN-'.$key, 'nomor_mesin' => 'CS-ENG-'.$key,
            'warna' => $color, 'catalog_identity' => 'mobil:'.$car->getKey(), 'showcase_slug' => null, 'status' => $status])->save();
        return $copy;
    }

    public function test_catalog_colors_are_not_physical_stock_and_legacy_quantity_does_not_create_vins(): void
    {
        $car = $this->model(); app(VehicleInventoryService::class)->ensure($car, 6);
        $colors = $this->colors($car);
        $this->assertCount(4, $colors);
        $this->assertSame(1, $colors['pearl-white']['stock']);
        $this->assertSame(5, $colors['pearl-white']['unverified_units']);
        $this->assertSame(0, $colors['red']['stock']);
        $this->assertSame([], $colors['red']['images']);
    }

    public function test_multiple_physical_colors_share_one_source_for_admin_member_and_spk(): void
    {
        $car = $this->model(); $red = $this->unit($car, 'Red'); $this->unit($car, 'Pearl White');
        $colors = $this->colors($car);
        $this->assertSame(2, $colors['pearl-white']['stock']);
        $this->assertSame(1, $colors['red']['stock']);
        $this->assertSame([$red->getKey()], $colors['red']['orderable_ids']);
        $this->assertSame(1, app(VehicleAvailabilityService::class)->for($red)['stock']);
        $this->assertCount(4, $this->colors($red));
        $this->assertSame(1, app(\App\Services\SmartSpkOrderService::class)->vehicle($red)['stock']);
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        $this->actingAs($admin)->getJson(route('admin.mobil.stock', $car))->assertOk()->assertJsonPath('color_stocks.0.stock', 2);
    }

    public function test_empty_color_preorder_is_allowed_when_another_color_is_ready_and_creates_no_transaction(): void
    {
        $car = $this->model(); $before = array_map(fn ($table) => DB::table($table)->count(), ['mobil', 'pesanan_mobil_member', 'transaksi', 'penjualan_mobil']);
        $service = app(MemberVehiclePreorderService::class);
        $red = $service->create($this->customer(), $car, 'red');
        $black = $service->create($this->customer(), $car, 'matte-black');
        $this->assertSame('Red', $red->warna); $this->assertSame('Matte Black', $black->warna);
        $this->assertSame($car->tipe, $red->varian); $this->assertEquals($car->tahun, $red->tahun);
        $this->assertNull($red->id_pesanan);
        $this->assertSame($before, array_map(fn ($table) => DB::table($table)->count(), ['mobil', 'pesanan_mobil_member', 'transaksi', 'penjualan_mobil']));
    }

    public function test_duplicate_preorder_for_the_same_member_model_color_is_rejected(): void
    {
        $car = $this->model(); $service = app(MemberVehiclePreorderService::class);
        $service->create($this->customer(), $car, 'red');
        $this->expectException(\RuntimeException::class); $service->create($this->customer(), $car, 'Red');
    }

    public function test_reservation_and_rejection_affect_only_the_selected_physical_color(): void
    {
        $car = $this->model(); $red = $this->unit($car, 'Red');
        $service = app(MemberVehiclePurchaseService::class);
        $order = $service->create($this->customer(), $red, ['nama_pembeli' => 'Color QA', 'warna_pilihan' => 'red']);
        $this->assertSame('Red', $order->snapshot_warna);
        $this->assertSame(0, $this->colors($car)['red']['stock']);
        $this->assertSame(1, $this->colors($car)['red']['reserved_units']);
        $this->assertSame(1, $this->colors($car)['pearl-white']['stock']);
        $service->reject($order, $this->stage4Actor(), 'QA cancellation');
        $this->assertSame(1, $this->colors($car)['red']['stock']);
    }

    public function test_checkout_cannot_assign_a_gallery_color_to_another_physical_unit(): void
    {
        $car = $this->model(); $this->expectException(\RuntimeException::class);
        app(MemberVehiclePurchaseService::class)->create($this->customer(), $car, ['nama_pembeli' => 'QA', 'warna_pilihan' => 'red']);
    }

    public function test_reserved_unit_cannot_be_sold_twice_even_when_legacy_quantity_is_six(): void
    {
        $car = $this->model(); app(VehicleInventoryService::class)->ensure($car, 6);
        $service = app(MemberVehiclePurchaseService::class); $input = ['nama_pembeli' => 'QA'];
        $service->create($this->customer(), $car, $input);
        $this->expectException(\RuntimeException::class); $service->create($this->customer(), $car, $input);
    }

    public function test_stock_cannot_be_inflated_without_physical_identities(): void
    {
        $car = $this->model(); app(VehicleInventoryService::class)->ensure($car, 1);
        $this->expectException(\RuntimeException::class); app(VehicleInventoryService::class)->adjust($car, 8, 'QA restock');
    }

    public function test_reserved_unit_identity_and_status_are_immutable(): void
    {
        $car = $this->model(); app(MemberVehiclePurchaseService::class)->create($this->customer(), $car, ['nama_pembeli' => 'QA']);
        $this->expectException(\RuntimeException::class); app(VehicleInventoryService::class)->assertEditable($car->fresh(), ['warna' => 'Red', 'status' => 'nonaktif']);
    }

    public function test_legacy_booking_and_spk_on_one_unit_are_counted_as_one_bound_physical_reservation(): void
    {
        $car = $this->model();
        app(MemberVehiclePurchaseService::class)->create($this->customer(), $car, ['nama_pembeli' => 'QA']);
        \App\Models\BookingMobil::forceCreate(['kode_booking' => 'CS-LEGACY-'.bin2hex(random_bytes(5)), 'id_pelanggan' => $this->customer()->getKey(),
            'id_mobil' => $car->getKey(), 'tanggal_booking' => now(), 'status' => 'menunggu']);
        $state = app(VehicleAvailabilityService::class)->for($car->fresh());
        $this->assertSame(1, $state['stock_total']); $this->assertSame(1, $state['reserved_units']); $this->assertSame(0, $state['stock']);
        $this->assertSame(1, app(VehicleInventoryService::class)->reserveCount($car));
    }

    public function test_delivered_identity_cannot_be_sold_again_even_if_legacy_status_is_available(): void
    {
        $car = $this->model();
        $order = app(MemberVehiclePurchaseService::class)->create($this->customer(), $car, ['nama_pembeli' => 'QA']);
        // Simulate an inconsistent historical flag; the delivered relation remains authoritative.
        $order->update(['status' => 'diserahkan']); $car->refresh()->update(['status' => 'tersedia']);
        $this->assertSame(0, app(VehicleAvailabilityService::class)->for($car->fresh())['stock']);
        $this->expectException(\RuntimeException::class);
        app(MemberVehiclePurchaseService::class)->create($this->customer(), $car, ['nama_pembeli' => 'QA']);
    }

    public function test_database_rejects_duplicate_vin(): void
    {
        $car = $this->model(); $copy = $car->replicate(); $copy->kode_mobil = 'DUP-'.bin2hex(random_bytes(5)); $copy->nomor_mesin .= '-OTHER';
        $this->expectException(\Illuminate\Database\QueryException::class); $copy->save();
    }

    public function test_verified_matching_color_notifies_once_without_allocating_or_creating_an_invoice(): void
    {
        $car = $this->model(); $item = app(MemberVehiclePreorderService::class)->create($this->customer(), $car, 'red');
        $notifier = app(PreorderInventoryNotificationService::class);
        $white = $notifier->queue($car, 'White arrival'); $notifier->process($white);
        $this->assertSame(0, Notifikasi::where('tipe', 'stok')->where('referensi_tipe', 'pre_order_mobil')->where('referensi_id', $item->getKey())->count());
        $red = $this->unit($car, 'Red', 'dalam_pemeriksaan');
        $event = $notifier->queue($red, 'Pending verification'); $notifier->process($event);
        $this->assertSame('menunggu_stok', $item->fresh()->status);
        $red->update(['status' => 'tersedia']);
        $before = DB::table('transaksi')->count();
        $event = $notifier->queue($red, 'Verified Red'); $notifier->process($event); $notifier->process($event);
        $notifier->process($notifier->queue($red, 'Replay'));
        $this->assertSame(1, Notifikasi::where('tipe', 'stok')->where('referensi_tipe', 'pre_order_mobil')->where('referensi_id', $item->getKey())->count());
        $this->assertSame('notifikasi_terkirim', $item->fresh()->status); $this->assertNull($item->fresh()->id_pesanan);
        $this->assertSame($before, DB::table('transaksi')->count()); $this->assertSame(1, $this->colors($car)['red']['stock']);
    }

    public function test_member_detail_exposes_empty_colors_and_both_dynamic_actions(): void
    {
        $car = $this->model();
        $response = $this->actingAs($this->customer()->user)->get(route('pelanggan.katalog-mobil.show', $car));
        $response
            ->assertOk()->assertSee('Stok Habis')->assertSee('Lanjutkan Pembelian')->assertSee('Ajukan Pre-Order')->assertSee('data-color-stock-status', false)->assertSee('data-preorder-form', false);
        if (getenv('YZA_COLOR_UI_EXPORT') === '1') {
            file_put_contents(base_path('.color-stock-member.html'), $response->getContent());
            $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
            $adminPage = $this->actingAs($admin)->get(route('admin.mobil.index'))->assertOk();
            file_put_contents(base_path('.color-stock-admin.html'), $adminPage->getContent());
            file_put_contents(base_path('.color-stock-admin.json'), json_encode(['id' => $car->getKey(), 'data' => $this->getJson(route('admin.mobil.show', $car))->assertOk()->json()]));
        }
    }

    public function test_owner_cannot_adjust_stock_and_admin_requires_evidence(): void
    {
        $car = $this->model(); app(VehicleInventoryService::class)->ensure($car, 1);
        $owner = User::whereHas('role', fn ($q) => $q->where('nama_role', 'owner'))->firstOrFail();
        $this->actingAs($owner)->postJson(route('admin.mobil.stock.adjust', $car), ['stok_baru' => 1, 'alasan' => 'QA opname', 'bukti' => 'BAST-QA-1'])->assertForbidden();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        $this->actingAs($admin)->postJson(route('admin.mobil.stock.adjust', $car), ['stok_baru' => 1, 'alasan' => 'QA opname'])->assertUnprocessable();
        $this->postJson(route('admin.mobil.stock.adjust', $car), ['stok_baru' => 1, 'alasan' => 'QA opname', 'bukti' => 'BAST-QA-1'])->assertOk();
        $this->assertDatabaseHas('vehicle_inventory_histories', ['jenis' => 'penyesuaian', 'id_user' => $admin->getKey()]);
    }
}
