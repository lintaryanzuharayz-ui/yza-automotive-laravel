<?php

namespace Tests\Feature;

use App\Models\{Mobil, PesananMobilMember, User};
use App\Services\{MemberVehiclePurchaseService, VehicleGalleryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMemberOrderDecisionTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    private function order(string $color = 'black'): PesananMobilMember
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $car = Mobil::where('model', 'GR Supra')->where('status', 'tersedia')->firstOrFail();
        $car = $this->colorStockUnit($car, $color);
        return app(MemberVehiclePurchaseService::class)->create($customer, $car, [
            'nama_pembeli' => 'Pembeli Snapshot', 'telepon_pembeli' => '08123456789',
            'alamat_pembeli' => 'Alamat pengujian', 'warna_pilihan' => $color,
        ]);
    }

    private function confirmation(PesananMobilMember $order, string $decision): array
    {
        return ['confirmation_reviewed' => '1', 'confirmation_order' => $order->nomor_pesanan,
            'confirmation_decision' => $decision, 'catatan_admin' => 'Verifikasi pengujian'];
    }

    public function test_admin_displays_the_ordered_color_and_snapshot_in_each_order_modal(): void
    {
        $black = $this->order('black');
        $white = $this->order('white');
        Mobil::where('model', 'GR Supra')->orderBy('id_mobil')->firstOrFail()->forceFill(['nomor_rangka' => 'VIN-KATALOG-BUKAN-UNIT', 'nomor_mesin' => 'MESIN-KATALOG-BUKAN-UNIT'])->save();
        $html = $this->actingAs($this->user('admin'))->get(route('admin.pesanan-mobil.index'))->assertOk()->getContent();
        foreach ([$black, $white] as $order) {
            $part = explode('id="order-detail-modal-'.$order->id_pesanan.'"', $html, 2)[1];
            $part = explode('class="admin-order-detail-modal"', $part, 2)[0];
            $gallery = app(VehicleGalleryService::class)->forColor($order->mobil, $order->snapshot_warna);
            $this->assertStringContainsString($order->snapshot_warna, $part);
            $this->assertStringContainsString($gallery['images'][0]['src'], $part);
            $this->assertStringContainsString($gallery['swatch'], $part);
            $this->assertStringContainsString('Menunggu Keputusan Admin', $part);
            $this->assertStringContainsString($order->mobil->nomor_rangka, $part);
            $this->assertStringNotContainsString('VIN-KATALOG-BUKAN-UNIT', $part);
            $this->assertStringNotContainsString('MESIN-KATALOG-BUKAN-UNIT', $part);
        }
    }

    public function test_catalog_changes_cannot_replace_the_ordered_vehicle_in_admin_detail(): void
    {
        $order = $this->order();
        $originalPrice = $order->total_tagihan;
        $order->mobil->forceFill(['merk' => 'Merek Berubah', 'model' => 'Model Berubah',
            'tipe' => 'Tipe Berubah', 'tahun' => 2030, 'warna' => 'Warna Berubah', 'harga_jual' => 12345])->save();
        $html = $this->actingAs($this->user('admin'))->get(route('admin.pesanan-mobil.index'))->assertOk()->getContent();
        $part = explode('id="order-detail-modal-'.$order->id_pesanan.'"', $html, 2)[1];
        $this->assertStringContainsString($order->snapshot_merk.' '.$order->snapshot_model, $part);
        $this->assertStringContainsString($order->snapshot_warna, $part);
        $this->assertStringContainsString(number_format((float) $originalPrice, 0, ',', '.'), $part);
        $this->assertStringContainsString('vehicle-photo-unavailable.svg', $part);
        $this->assertStringNotContainsString('Model Berubah', $part);
        $this->actingAs($this->user('pelanggan'))->get(route('pelanggan.pesanan-mobil.show', $order))
            ->assertOk()->assertSee($order->snapshot_warna)
            ->assertSee('vehicle-photo-unavailable.svg', false)->assertDontSee('Model Berubah')
            ->assertDontSee('3.0L Turbo 6-Cylinder');
    }

    public function test_approval_requires_review_correct_order_number_and_matching_action(): void
    {
        $order = $this->order();
        $this->actingAs($this->user('admin'));
        $url = route('admin.pesanan-mobil.approve', $order);
        $this->post($url)->assertSessionHasErrors(['confirmation_reviewed', 'confirmation_order', 'confirmation_decision']);
        $this->post($url, array_replace($this->confirmation($order, 'approve'), ['confirmation_order' => 'ORD-WRONG']))->assertSessionHasErrors('confirmation_order');
        $this->post($url, $this->confirmation($order, 'reject'))->assertSessionHasErrors('confirmation_decision');
        $this->post($url, array_replace($this->confirmation($order, 'approve'), ['confirmation_reviewed' => '0']))->assertSessionHasErrors('confirmation_reviewed');
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);
        $this->assertNull($order->fresh()->id_transaksi);
        $this->post($url, $this->confirmation($order, 'approve'))->assertSessionHasNoErrors();
        $approved = $order->fresh();
        $this->assertSame('menunggu_pembayaran', $approved->status);
        $this->assertNotNull($approved->id_transaksi);
        $transaction = $approved->id_transaksi;
        $this->post($url, $this->confirmation($order, 'approve'))->assertSessionHasErrors();
        $this->assertSame($transaction, $order->fresh()->id_transaksi);
    }

    public function test_rejection_requires_the_same_confirmation_and_does_not_create_a_transaction(): void
    {
        $order = $this->order();
        $this->actingAs($this->user('admin'));
        $url = route('admin.pesanan-mobil.reject', $order);
        $this->post($url)->assertSessionHasErrors('confirmation_order');
        $this->post($url, $this->confirmation($order, 'approve'))->assertSessionHasErrors('confirmation_decision');
        $this->assertSame('menunggu_verifikasi', $order->fresh()->status);
        $this->post($url, $this->confirmation($order, 'reject'))->assertSessionHasNoErrors();
        $this->assertSame('ditolak', $order->fresh()->status);
        $this->assertNull($order->fresh()->id_transaksi);
    }

    public function test_an_unavailable_color_is_not_silently_replaced_with_the_default(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Warna pilihan tidak tersedia');
        $this->order('warna-tidak-tersedia');
    }

    public function test_legacy_basic_color_uses_the_matching_variant_instead_of_the_catalog_default(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        foreach ([['LFA', 'black', 'Matte Black'], ['Challenger SRT Hellcat Redeye', 'blue', 'Frostbite Blue']] as [$model, $input, $expected]) {
            $car = Mobil::where('model', $model)->firstOrFail();
            $car = $this->colorStockUnit($car, $input);
            $order = app(MemberVehiclePurchaseService::class)->create($customer, $car, [
                'nama_pembeli' => 'Pembeli Uji', 'telepon_pembeli' => '08123456789',
                'alamat_pembeli' => 'Alamat pengujian', 'warna_pilihan' => $input,
            ]);
            $this->assertSame($expected, $order->snapshot_warna);
            $this->assertSame($expected, app(VehicleGalleryService::class)->forColor($car, $order->snapshot_warna)['label']);
        }
    }
}
