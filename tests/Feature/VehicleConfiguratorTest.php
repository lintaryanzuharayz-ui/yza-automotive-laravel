<?php

namespace Tests\Feature;

use App\Models\Mobil;
use App\Services\VehicleGalleryService;
use Tests\TestCase;

class VehicleConfiguratorTest extends TestCase
{
    public function test_public_sport_showcases_use_the_generic_premium_detail(): void
    {
        foreach (['audi-r8', 'bmw-m4', 'bugatti-chiron', 'mclaren-720s'] as $slug) {
            $response = $this->get('/koleksi/'.$slug);

            $response->assertOk()
                ->assertSee('detail-main-image')
                ->assertSee('detail-gallery-counter')
                ->assertSee('detail-swatch')
                ->assertSee('01 / 04');
        }
    }

    public function test_configurator_is_data_driven_and_has_expected_colors_and_four_views_for_sport_cars(): void
    {
        $service = app(VehicleGalleryService::class);

        foreach ([
            ['Audi', 'R8', 4],
            ['BMW', 'M4', 4],
            ['Bugatti', 'Chiron', 4],
            ['McLaren', '720S', 5],
        ] as [$brand, $model, $expectedColorCount]) {
            $mobil = new Mobil(['merk' => $brand, 'model' => $model, 'warna' => 'Preview']);
            $gallery = $service->for($mobil, '/assets/fallback.png');

            $this->assertCount($expectedColorCount, $gallery);
            foreach ($gallery as $color) {
                $this->assertCount(4, $color['images']);
                $this->assertSame(['front', 'side', 'rear', 'interior'], array_column($color['images'], 'type'));
            }
        }
    }

    public function test_unknown_showcase_is_not_exposed(): void
    {
        $this->get('/koleksi/not-a-real-vehicle')->assertNotFound();
    }

    public function test_detail_back_link_is_explicitly_aware_of_landing_or_catalog_origin(): void
    {
        $this->get('/koleksi/audi-r8?source=landing')
            ->assertSee('Kembali ke Showroom')
            ->assertSee('href="'.url('/#mobil').'"', false);

        $catalogState = '/mobil?kondisi=baru&merk=BMW&sort=harga_termurah&page=2';
        $this->get('/koleksi/audi-r8?source=catalog&return_to='.urlencode($catalogState))
            ->assertSee('Kembali ke Semua Mobil')
            ->assertSee('href="'.e($catalogState).'"', false);
    }

    public function test_detail_back_link_falls_back_safely_for_direct_or_external_return_urls(): void
    {
        $this->get('/koleksi/audi-r8')
            ->assertSee('Kembali ke Semua Mobil')
            ->assertSee('href="'.route('mobil.index').'"', false);

        $this->get('/koleksi/audi-r8?source=catalog&return_to='.urlencode('https://evil.example/steal'))
            ->assertSee('Kembali ke Semua Mobil')
            ->assertSee('href="'.route('mobil.index').'"', false)
            ->assertDontSee('evil.example');
    }

    public function test_landing_and_catalog_cards_emit_explicit_navigation_state(): void
    {
        $this->get('/')
            ->assertSee('source=landing', false);

        $this->get('/mobil?kondisi=baru&merk=BMW&sort=harga_termurah&page=1')
            ->assertSee('source=catalog', false)
            ->assertSee('return_to=%2Fmobil%3Fkondisi%3Dbaru%26merk%3DBMW%26sort%3Dharga_termurah%26page%3D1', false);
    }
}
