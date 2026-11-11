<?php

namespace Tests\Feature;

use App\Models\{Mobil, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Visual-only local fixture; the real checkout still uses the signed-in account. */
class RenderCheckoutReferencePreview extends TestCase
{
    use DatabaseTransactions;

    public function test_render_checkout_reference(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        $user = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))
            ->whereHas('pelanggan.membership', fn ($q) => $q->where('status', 'active'))->firstOrFail();
        $user->forceFill(['email' => 'member.preview@example.test'])->save();
        $user->pelanggan->forceFill(['nama_lengkap' => 'Member Preview', 'no_hp' => '081234567890', 'alamat' => 'Alamat contoh member untuk pratinjau checkout.'])->save();
        $car = Mobil::where('model', 'LFA')->where('status', 'tersedia')->firstOrFail();
        $response = $this->actingAs($user)->get(route('pelanggan.pesanan-mobil.create', ['mobil' => $car, 'warna' => 'red']));
        $response->assertOk()->assertSee('Member Preview')->assertSee('member.preview@example.test')
            ->assertSee('name="warna_pilihan"', false)->assertSee('form="checkout-form"', false)
            ->assertSee('name="syarat_disetujui"', false)->assertSee('value="red"', false);
        $directory = __DIR__.'/preview';
        if (!is_dir($directory)) mkdir($directory, 0777, true);
        $html = preg_replace('/<meta name="csrf-token"[^>]*>/', '', $response->getContent());
        $html = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $html);
        file_put_contents($directory.'/index.html', $html);
    }
}
