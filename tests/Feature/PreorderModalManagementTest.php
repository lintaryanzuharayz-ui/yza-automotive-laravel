<?php

namespace Tests\Feature;

use App\Models\{MemberVehiclePreorder, Mobil, Notifikasi, User};
use App\Services\{PreorderManagementService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PreorderModalManagementTest extends TestCase
{
    use DatabaseTransactions;

    private function actor(string $role = 'admin'): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    private function car(array $changes = []): Mobil
    {
        $car = Mobil::firstOrFail()->replicate();
        $unique = bin2hex(random_bytes(5));
        $car->forceFill(array_replace(['kode_mobil' => 'POM-'.$unique, 'nomor_rangka' => 'VIN-'.$unique,
            'nomor_mesin' => 'ENGINE-'.$unique, 'merk' => 'QA', 'model' => 'Exact Car', 'tipe' => 'Exact Trim',
            'tahun' => 2026, 'warna' => 'QA Cyan', 'transmisi' => 'automatic', 'bahan_bakar' => 'bensin',
            'kondisi' => 'baru', 'is_limited_edition' => false, 'showcase_slug' => null, 'status' => 'tersedia'], $changes))->save();
        return $car;
    }

    private function preorder(Mobil $car, array $changes = []): MemberVehiclePreorder
    {
        return MemberVehiclePreorder::create(array_replace(['id_pelanggan' => $this->actor('pelanggan')->pelanggan->id_pelanggan,
            'id_mobil' => $car->id_mobil, 'product_key' => 'mobil:'.$car->id_mobil,
            'nama_mobil' => trim($car->merk.' '.$car->model), 'varian' => $car->tipe, 'tahun' => $car->tahun,
            'warna' => $car->warna, 'harga_referensi' => $car->harga_jual, 'status' => 'menunggu_stok'], $changes));
    }

    private function endpoint(MemberVehiclePreorder $preorder, string $action): string
    {
        return route('admin.preorder-mobil.'.$action, $preorder);
    }

    private function body(MemberVehiclePreorder $preorder, string $action): array
    {
        return ['action' => $action, 'expected_status' => $preorder->status,
            'confirmation' => 'PRE-'.str_pad((string) $preorder->getKey(), 5, '0', STR_PAD_LEFT)];
    }

    public function test_detail_is_read_only_authoritative_and_has_no_fabricated_history(): void
    {
        $car = $this->car(); $preorder = $this->preorder($car);
        $before = $preorder->fresh()->getRawOriginal();
        $this->actingAs($this->actor())->getJson($this->endpoint($preorder, 'detail'))->assertOk()
            ->assertJsonPath('vehicle', 'QA Exact Car')->assertJsonPath('color', 'QA Cyan')
            ->assertJsonPath('history_supported', true)->assertJsonCount(1, 'history')->assertJsonPath('history.0.kind', 'created')->assertJsonPath('customer_note', null);
        $this->assertSame($before, $preorder->fresh()->getRawOriginal());
    }

    public function test_stock_requires_exact_variant_year_color_and_other_known_attributes_without_writes(): void
    {
        $exact = $this->car(); $preorder = $this->preorder($exact);
        foreach ([['warna' => 'Other Color'], ['tipe' => 'Other Trim'], ['tahun' => 2025], ['transmisi' => 'manual'], ['status' => 'dibooking']] as $changes) $this->car($changes);
        $before = array_map(fn ($table) => DB::table($table)->count(), ['pesanan_mobil_member', 'penjualan_mobil', 'transaksi', 'kendaraan_pelanggan', 'vehicle_inventory_histories']);
        $this->actingAs($this->actor())->getJson($this->endpoint($preorder, 'stock'))->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('units.0.id', $exact->id_mobil)->assertJsonPath('units.0.kind', 'Unit fisik');
        $after = array_map(fn ($table) => DB::table($table)->count(), ['pesanan_mobil_member', 'penjualan_mobil', 'transaksi', 'kendaraan_pelanggan', 'vehicle_inventory_histories']);
        $this->assertSame($before, $after);
        $this->assertSame('menunggu_stok', $preorder->fresh()->status);
        $this->assertSame('tersedia', $exact->fresh()->status);
    }

    public function test_aggregate_quantity_does_not_multiply_one_physical_vin(): void
    {
        $car = $this->car(); $preorder = $this->preorder($car);
        app(VehicleInventoryService::class)->ensure($car, 3);
        $this->actingAs($this->actor())->getJson($this->endpoint($preorder, 'stock'))->assertOk()
            ->assertJsonPath('total', 1)->assertJsonPath('units.0.kind', 'Unit fisik')->assertJsonPath('units.0.vin', $car->nomor_rangka);
    }

    public function test_notify_is_transactional_idempotent_and_never_reserves_or_creates_checkout(): void
    {
        $car = $this->car(); $preorder = $this->preorder($car);
        $before = array_map(fn ($table) => DB::table($table)->count(), ['pesanan_mobil_member', 'penjualan_mobil', 'transaksi', 'kendaraan_pelanggan']);
        $this->actingAs($this->actor())->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'notify'))->assertOk()
            ->assertJsonPath('preorder.status', 'notifikasi_terkirim');
        $this->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'notify'))->assertOk();
        $notices = Notifikasi::where('referensi_tipe', 'pre_order_mobil')->where('referensi_id', $preorder->getKey())->where('tipe', 'stok')->get();
        $this->assertCount(1, $notices);
        $this->assertSame($preorder->pelanggan->id_user, $notices[0]->id_user);
        $this->assertStringContainsString('QA Cyan', $notices[0]->pesan);
        $this->assertSame('tersedia', $car->fresh()->status);
        $this->assertSame($before, array_map(fn ($table) => DB::table($table)->count(), ['pesanan_mobil_member', 'penjualan_mobil', 'transaksi', 'kendaraan_pelanggan']));
        $this->assertNull($preorder->fresh()->id_pesanan);
    }

    public function test_no_stock_blocks_notification_without_status_change(): void
    {
        $car = $this->car(['status' => 'dibooking']); $preorder = $this->preorder($car);
        $this->actingAs($this->actor())->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'notify'))
            ->assertUnprocessable()->assertJsonValidationErrors('action');
        $this->assertSame('menunggu_stok', $preorder->fresh()->status);
        $this->assertFalse(Notifikasi::where('referensi_tipe', 'pre_order_mobil')->where('referensi_id', $preorder->getKey())->where('tipe', 'stok')->exists());
    }

    public function test_missing_requested_color_does_not_claim_inventory_is_eligible(): void
    {
        $preorder = $this->preorder($this->car(), ['warna' => null]);
        $this->actingAs($this->actor())->getJson($this->endpoint($preorder, 'stock'))->assertOk()->assertJsonPath('available', false)->assertJsonPath('total', 0);
        $this->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'notify'))->assertUnprocessable();
        $this->assertSame('menunggu_stok', $preorder->fresh()->status);
    }

    public function test_cancellation_requires_confirmation_and_blocks_checkout_or_stale_status(): void
    {
        $preorder = $this->preorder($this->car());
        $this->actingAs($this->actor())->postJson($this->endpoint($preorder, 'action'), ['action' => 'cancel'])->assertUnprocessable();
        $this->postJson($this->endpoint($preorder, 'action'), array_replace($this->body($preorder, 'cancel'), ['expected_status' => 'stok_tersedia']))->assertUnprocessable();
        $this->postJson($this->endpoint($preorder, 'action'), array_replace($this->body($preorder, 'cancel'), ['action' => 'lunas']))->assertUnprocessable();
        $this->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'cancel'))->assertOk()->assertJsonPath('preorder.status', 'dibatalkan');
        $this->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'cancel'))->assertOk();
        $this->assertSame(1, Notifikasi::where('referensi_tipe', 'pre_order_mobil')->where('referensi_id', $preorder->getKey())->where('tipe', 'info')->count());
        $converted = $this->preorder($this->car(), ['status' => 'dilanjutkan_ke_checkout', 'id_pesanan' => 1]);
        $this->postJson($this->endpoint($converted, 'action'), $this->body($converted, 'cancel'))->assertUnprocessable();
    }

    public function test_member_finance_and_owner_cannot_write_and_service_guard_cannot_be_bypassed(): void
    {
        $preorder = $this->preorder($this->car());
        foreach (['pelanggan', 'finance', 'owner'] as $role) {
            $this->actingAs($this->actor($role))->postJson($this->endpoint($preorder, 'action'), $this->body($preorder, 'cancel'))->assertForbidden();
        }
        $this->actingAs($this->actor('pelanggan'))->getJson($this->endpoint($preorder, 'detail'))->assertForbidden();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(PreorderManagementService::class)->act($preorder, $this->actor('owner'), 'cancel', $preorder->status);
    }

    public function test_workspace_has_one_review_button_and_all_four_tabs_inside_reusable_modal(): void
    {
        $preorder = $this->preorder($this->car());
        $response = $this->actingAs($this->actor())->get(route('admin.preorder-mobil.index', ['q' => $preorder->product_key]))->assertOk();
        $response->assertSee('>Tinjau</button>', false);
        $this->assertSame(1, substr_count($response->getContent(), 'data-preorder-open='));
        $response->assertSee('data-preorder-open="detail"', false);
        foreach (['detail', 'stock', 'followup', 'history'] as $tab) $response->assertSee('data-pom-tab="'.$tab.'"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'data-preorder-modal hidden'));
    }
}
