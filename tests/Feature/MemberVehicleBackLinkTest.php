<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberVehicleBackLinkTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))
            ->whereHas('pelanggan.membership', fn ($q) => $q->where('status', 'active'))->firstOrFail());
    }

    public function test_home_origin_returns_to_home_collection_for_showcase_and_stock_details(): void
    {
        $urls = ['/pelanggan/katalog-mobil/koleksi/audi-r8'];
        $car = \App\Models\Mobil::where('status', '!=', 'nonaktif')->firstOrFail();
        $urls[] = '/pelanggan/katalog-mobil/'.$car->id_mobil;
        foreach ($urls as $url) {
            $this->get($url.'?'.http_build_query(['source' => 'member-home', 'return_to' => '/pelanggan/member']))
                ->assertOk()->assertSee('Kembali ke Beranda')
                ->assertSee('href="/pelanggan/member#yza-home-garage-title"', false);
        }
    }

    public function test_catalog_return_preserves_filters_and_page(): void
    {
        $returnTo = '/pelanggan/katalog-mobil?merk=BMW&kondisi=baru&page=2';
        $this->get('/pelanggan/katalog-mobil/koleksi/audi-r8?'.http_build_query([
            'source' => 'member-catalog', 'return_to' => $returnTo,
        ]))->assertOk()->assertSee('Kembali ke Katalog Member')
            ->assertSee('href="'.e($returnTo).'"', false);
    }

    public function test_unsafe_or_unrelated_return_locations_use_the_catalog_fallback(): void
    {
        foreach (['https://evil.example', '//evil.example', '/\\evil.example', '/pelanggan/katalog-mobil/../profil', '/admin/dashboard', ['/pelanggan/member']] as $returnTo) {
            $this->get('/pelanggan/katalog-mobil/koleksi/audi-r8?'.http_build_query(['return_to' => $returnTo]))
                ->assertOk()->assertSee('href="'.route('pelanggan.katalog-mobil.index').'"', false);
        }
    }

    public function test_home_origin_without_a_valid_return_location_still_returns_home(): void
    {
        $this->get('/pelanggan/katalog-mobil/koleksi/audi-r8?'.http_build_query([
            'source' => 'member-home', 'return_to' => 'https://evil.example',
        ]))->assertOk()->assertSee('href="'.route('pelanggan.member').'#yza-home-garage-title"', false);
    }
}
