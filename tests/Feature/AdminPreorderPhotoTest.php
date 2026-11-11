<?php

namespace Tests\Feature;

use App\Models\{MemberVehiclePreorder, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPreorderPhotoTest extends TestCase
{
    use DatabaseTransactions;

    private function preorder(array $changes = []): MemberVehiclePreorder
    {
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        return MemberVehiclePreorder::create(array_replace([
            'id_pelanggan' => $member->pelanggan->id_pelanggan, 'id_mobil' => null,
            'product_key' => 'showcase:bugatti-chiron-qa', 'showcase_slug' => 'bugatti-chiron',
            'nama_mobil' => 'Bugatti Chiron', 'varian' => 'Super Sport', 'tahun' => 2024,
            'warna' => 'Atlantic Blue', 'harga_referensi' => 68000000000, 'status' => 'menunggu_stok',
        ], $changes));
    }

    private function response(MemberVehiclePreorder $preorder)
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        return $this->actingAs($admin)->get(route('admin.preorder-mobil.index', ['q' => $preorder->product_key]))->assertOk();
    }

    public function test_showcase_preorder_without_inventory_uses_the_exact_model_and_color_photo(): void
    {
        $preorder = $this->preorder();
        $response = $this->response($preorder);
        $image = asset('assets/images/vehicles/bugatti-chiron/atlantic-blue/front.png');
        $this->assertSame($image, $response->viewData('preorders')->first()->presentation_image);
        $this->assertFileExists(public_path('assets/images/vehicles/bugatti-chiron/atlantic-blue/front.png'));
        $response->assertSee($image, false)->assertSee('Atlantic Blue')->assertSee('Super Sport')->assertSee('2024');
        $this->assertNull($preorder->fresh()->id_mobil);
        $this->assertSame('menunggu_stok', $preorder->fresh()->status);
        $this->assertSame('Atlantic Blue', $preorder->fresh()->warna);
    }

    public function test_preorder_photo_tracks_its_saved_color_instead_of_the_catalog_default(): void
    {
        $preorder = $this->preorder(['warna' => 'Racing Red']);
        $this->assertSame(asset('assets/images/vehicles/bugatti-chiron/racing-red/front.png'),
            $this->response($preorder)->viewData('preorders')->first()->presentation_image);
    }

    public function test_unknown_color_or_mismatched_identity_does_not_use_another_vehicle_photo(): void
    {
        $preorder = $this->preorder(['warna' => 'Warna Arsip Tanpa Foto']);
        $this->assertSame(asset('assets/images/vehicle-photo-unavailable.svg'),
            $this->response($preorder)->viewData('preorders')->first()->presentation_image);
        $preorder->update(['warna' => 'Atlantic Blue', 'nama_mobil' => 'Model Arsip Lain']);
        $this->assertSame(asset('assets/images/vehicle-photo-unavailable.svg'),
            $this->response($preorder)->viewData('preorders')->first()->presentation_image);
    }
}
