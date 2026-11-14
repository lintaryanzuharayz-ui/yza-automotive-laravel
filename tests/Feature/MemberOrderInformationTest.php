<?php

namespace Tests\Feature;

use App\Models\{Mobil, Pelanggan, PesananMobilMember, User};
use App\Services\{MemberVehiclePurchaseService, VehicleGalleryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberOrderInformationTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;

    private function member(): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))->firstOrFail();
    }

    private function order(string $color): PesananMobilMember
    {
        return app(MemberVehiclePurchaseService::class)->create(
            $this->member()->pelanggan, $this->colorStockUnit(Mobil::where('model', 'LFA')->firstOrFail(), $color), [
                'nama_pembeli' => 'Pembeli Uji', 'telepon_pembeli' => '08123456789',
                'alamat_pembeli' => 'Alamat pengujian', 'warna_pilihan' => $color,
            ]
        );
    }

    public function test_each_card_uses_its_own_order_color_photo_and_status(): void
    {
        $red = $this->order('red');
        $black = $this->order('black');
        $black->update(['status' => 'dibatalkan']);
        // A catalog color change must not repaint either historical order.
        $red->mobil->forceFill(['warna' => 'Warna Katalog Berubah'])->save();
        $response = $this->actingAs($this->member())->get(route('pelanggan.pesanan-mobil.index'))->assertOk();
        $orders = $response->viewData('orders')->getCollection()->keyBy('id_pesanan');
        foreach ([$red, $black] as $order) {
            $variant = app(VehicleGalleryService::class)->forColor($order->mobil, $order->snapshot_warna);
            $card = $orders[$order->id_pesanan];
            $this->assertSame($order->snapshot_warna, $card->presentation_color);
            $this->assertSame($variant['swatch'], $card->presentation_swatch);
            $this->assertSame($variant['images'][0]['src'], $card->presentation_image);
            $response->assertSee('--swatch: '.$variant['swatch'], false);
        }
        $this->assertSame('Menunggu Verifikasi Admin', $orders[$red->id_pesanan]->presentation_status['label']);
        $this->assertSame('Pesanan Dibatalkan', $orders[$black->id_pesanan]->presentation_status['label']);
        $response->assertDontSee('Warna Katalog Berubah');
    }

    public function test_unknown_order_color_does_not_use_the_first_catalog_color_or_fake_photo_count(): void
    {
        $order = $this->order('red');
        $order->update(['snapshot_warna' => 'Warna Arsip Tanpa Galeri']);
        $response = $this->actingAs($this->member())->get(route('pelanggan.pesanan-mobil.index', ['q' => $order->nomor_pesanan]))->assertOk();
        $card = $response->viewData('orders')->first();
        $this->assertSame('Warna Arsip Tanpa Galeri', $card->presentation_color);
        $this->assertNull($card->presentation_swatch);
        $this->assertSame([], $card->presentation_gallery);
        $this->assertSame(0, $card->presentation_photo_count);
        $this->assertSame(asset('assets/images/vehicle-photo-unavailable.svg'), $card->presentation_image);
        $response->assertSee('Foto belum tersedia')->assertDontSee('class="color-swatch"', false);
    }

    public function test_catalog_identity_changes_do_not_replace_the_ordered_car_or_specs(): void
    {
        $order = $this->order('red');
        $order->mobil->forceFill(['model' => 'Model Pengganti', 'transmisi' => 'manual'])->save();
        $response = $this->actingAs($this->member())->get(route('pelanggan.pesanan-mobil.index', ['q' => $order->nomor_pesanan]))->assertOk();
        $card = $response->viewData('orders')->first();
        $this->assertSame(asset('assets/images/vehicle-photo-unavailable.svg'), $card->presentation_image);
        $this->assertSame('Belum tercatat', $card->presentation_transmission);
        $response->assertSee('Lexus LFA')->assertDontSee('Model Pengganti');
    }

    public function test_member_cannot_see_another_customers_order_information(): void
    {
        $order = $this->order('red');
        $other = Pelanggan::where('id_pelanggan', '!=', $order->id_pelanggan)->firstOrFail();
        $order->update(['id_pelanggan' => $other->id_pelanggan]);
        $response = $this->actingAs($this->member())->get(route('pelanggan.pesanan-mobil.index', ['q' => $order->nomor_pesanan]))->assertOk();
        $this->assertCount(0, $response->viewData('orders'));
        $response->assertDontSee('data-copy-order="'.$order->nomor_pesanan.'"', false);
    }
}
