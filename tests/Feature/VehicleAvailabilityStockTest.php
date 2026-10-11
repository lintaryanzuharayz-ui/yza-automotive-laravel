<?php

namespace Tests\Feature;

use App\Models\Mobil;
use App\Services\VehicleAvailabilityService;
use Database\Seeders\TestingVehicleStockFixtureSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionMethod;
use Tests\TestCase;

class VehicleAvailabilityStockTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestingVehicleStockFixtureSeeder::class);
    }

    public function test_testing_fixture_exposes_exact_5_8_and_3_units(): void
    {
        $service = app(VehicleAvailabilityService::class);

        $cases = [
            ['Toyota', 'GR Supra', 5, 'TERSEDIA · 5 UNIT'],
            ['Lamborghini', 'Revuelto', 8, 'TERSEDIA · 8 UNIT'],
            ['Toyota', 'Supra MK4', 3, 'STOK MENIPIS · SISA 3 UNIT'],
        ];

        foreach ($cases as [$brand, $model, $stock, $label]) {
            $car = Mobil::where('merk', $brand)->where('model', $model)->firstOrFail();
            $availability = $service->for($car);
            $this->assertSame($stock, $availability['stock']);
            $this->assertSame($label, $availability['label']);
        }
    }

    public function test_status_labels_cover_zero_through_four_plus_without_runtime_randomness(): void
    {
        $method = new ReflectionMethod(VehicleAvailabilityService::class, 'statusForStock');
        $method->setAccessible(true);
        $service = app(VehicleAvailabilityService::class);

        $this->assertSame('STOK HABIS', $method->invoke($service, 0)['label']);
        $this->assertSame('SISA 1 UNIT', $method->invoke($service, 1)['label']);
        $this->assertSame('STOK MENIPIS · SISA 2 UNIT', $method->invoke($service, 2)['label']);
        $this->assertSame('STOK MENIPIS · SISA 3 UNIT', $method->invoke($service, 3)['label']);
        $this->assertSame('TERSEDIA · 4 UNIT', $method->invoke($service, 4)['label']);
    }

    public function test_catalog_and_detail_render_the_same_live_stock_label(): void
    {
        foreach ([['GR Supra', 'TERSEDIA · 5 UNIT'], ['Revuelto', 'TERSEDIA · 8 UNIT'], ['Supra MK4', 'STOK MENIPIS · SISA 3 UNIT']] as [$model, $label]) {
            $this->get('/mobil?q='.rawurlencode($model))->assertOk()->assertSee($model)->assertSee($label);
        }

        foreach ([['GR Supra', 'TERSEDIA · 5 UNIT'], ['Revuelto', 'TERSEDIA · 8 UNIT'], ['Supra MK4', 'STOK MENIPIS · SISA 3 UNIT']] as [$model, $label]) {
            $car = Mobil::where('model', $model)->firstOrFail();
            $this->get('/mobil/'.$car->id_mobil)->assertOk()->assertSee($label);
        }
    }
}
