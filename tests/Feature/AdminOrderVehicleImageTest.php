<?php

namespace Tests\Feature;

use App\Models\{Mobil, User};
use App\Services\{MemberVehiclePurchaseService, VehicleGalleryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminOrderVehicleImageTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesColorStockUnit;

    public function test_admin_list_and_detail_use_the_ordered_model_and_selected_color_gallery(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        foreach ([['GR Supra', 'white'], ['GR Supra', 'black'], ['LFA', 'black'], ['Challenger SRT Hellcat Redeye', 'blue']] as [$model, $color]) {
            $car = Mobil::where('model', $model)->firstOrFail();
            $car = $this->colorStockUnit($car, $color);
            $order = app(MemberVehiclePurchaseService::class)->create($member->pelanggan, $car, [
                'nama_pembeli' => $member->pelanggan->nama_lengkap,
                'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => $color,
            ]);
            $gallery = app(VehicleGalleryService::class)->forColor($car, $order->snapshot_warna);
            $this->assertNotEmpty($gallery['images']);
            $image = $gallery['images'][0]['src'];
            $response = $this->actingAs($admin)->get(route('admin.pesanan-mobil.index', ['q' => $order->nomor_pesanan]));
            $response->assertOk();
            $alt = e($order->snapshot_merk.' '.$order->snapshot_model.' warna '.$order->snapshot_warna);
            $response->assertSee('<img src="'.e($image).'" alt="'.$alt.'" class="car-thumb"', false);
            $response->assertSee('<img src="'.e($image).'" alt="'.$alt.'" class="vehicle-stage-img"', false);
            $response->assertDontSee('assets/images/hero/yza-hero-black-v2.png', false);
        }
    }

    public function test_unavailable_order_color_never_displays_a_different_car_or_color(): void
    {
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        $car = Mobil::where('model', 'GR Supra')->firstOrFail();
        $car = $this->colorStockUnit($car, 'white');
        $order = app(MemberVehiclePurchaseService::class)->create($member->pelanggan, $car, [
            'nama_pembeli' => $member->pelanggan->nama_lengkap, 'telepon_pembeli' => '08123456789',
            'alamat_pembeli' => 'Alamat QA', 'warna_pilihan' => 'white',
        ]);
        $order->update(['snapshot_warna' => 'Warna arsip tanpa foto']);
        $response = $this->actingAs($admin)->get(route('admin.pesanan-mobil.index', ['q' => $order->nomor_pesanan]));
        $response->assertOk()->assertSee('assets/images/vehicle-photo-unavailable.svg', false)
            ->assertDontSee('assets/images/vehicles/toyota-gr-supra/black/front.png', false);
    }
}
