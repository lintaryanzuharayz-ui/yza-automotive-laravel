<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LandingExperienceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_blank_company_phone_keeps_public_contact_and_appointment_destination_usable(): void
    {
        DB::table('pengaturan')->updateOrInsert(['kunci_pengaturan' => 'no_hp_perusahaan'], ['nilai' => '']);
        $this->assertSame('https://wa.me/6287784304417', yza_whatsapp_url());
        $this->assertSame('https://wa.me/6287784304417?text=Halo%20YZA', yza_whatsapp_url('Halo YZA'));
        $this->get('/')->assertOk()->assertSee('href="https://wa.me/6287784304417"', false)->assertDontSee('href="https://wa.me/"', false);
    }

    public function test_public_navigation_matches_content_and_visit_form_keeps_its_fields(): void
    {
        $response = $this->get('/')->assertOk();
        $html = $response->getContent();
        $last = -1;
        foreach (['mobil','layanan','jual-mobil','test-drive','yza-member','testimoni','kontak','janji-temu'] as $id) {
            $position = strpos($html, 'id="'.$id.'"');
            $this->assertNotFalse($position, $id.' must remain reachable');
            $this->assertGreaterThan($last, $position, 'The content should follow the discovery-to-visit flow');
            $last = $position;
        }
        $response->assertSee('href="#yza-member"', false)->assertSee('href="#test-drive"', false)->assertDontSee('href="#"', false);
        foreach (['nama','no_hp','layanan','tanggal','jam','catatan'] as $field) $response->assertSee('name="'.$field.'"', false);
        $response->assertSee('data-intro-replay', false)->assertSee('hidden aria-hidden="true" aria-modal="true"', false);
    }

    public function test_configured_contact_is_escaped_and_used_for_location_instead_of_generic_map_claims(): void
    {
        $address = 'Jl. QA <Showroom> No. 10';
        DB::table('pengaturan')->updateOrInsert(['kunci_pengaturan' => 'alamat_perusahaan'], ['nilai' => $address]);
        DB::table('pengaturan')->updateOrInsert(['kunci_pengaturan' => 'no_hp_perusahaan'], ['nilai' => '+62 812 3456 7890']);
        $this->assertSame('https://wa.me/6281234567890', yza_whatsapp_url());
        $this->get('/')->assertOk()->assertSee(e($address), false)->assertSee('query='.rawurlencode($address), false)->assertDontSee($address, false);
    }
}
