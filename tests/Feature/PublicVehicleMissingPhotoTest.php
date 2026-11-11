<?php

namespace Tests\Feature;

use App\Services\VehicleColorStockService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PublicVehicleMissingPhotoTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\PromoIntegrityFixtures;

    public function test_public_vehicle_without_verified_photos_renders_the_unavailable_placeholder(): void
    {
        $car = $this->stage4Car();
        $car->forceFill(['catalog_identity' => null, 'showcase_slug' => null])->save();
        $colors = app(VehicleColorStockService::class)->colors($car);
        $this->assertNotEmpty($colors);
        $this->assertSame([], reset($colors)['images']);

        $this->get(route('mobil.show', $car))->assertOk()
            ->assertSee('Foto kendaraan belum tersedia')
            ->assertSee('assets/images/vehicle-photo-unavailable.svg', false);
    }
}
