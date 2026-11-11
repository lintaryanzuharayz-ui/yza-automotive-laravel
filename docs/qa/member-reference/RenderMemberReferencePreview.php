<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Isolated visual fixture, run explicitly; never a public application route. */
class RenderMemberReferencePreview extends TestCase
{
    use DatabaseTransactions;

    public function test_render_member_preview(): void
    {
        $user = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))
            ->whereHas('pelanggan.membership', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();
        $customer = $user->pelanggan;
        $customer->nama_lengkap = 'Lintar Yan Zuhara';
        $customer->save(); // Rolled back with the isolated test transaction.
        $response = $this->actingAs($user)->get('/pelanggan/member');
        $response->assertOk()->assertSee('yza-home-reference', false)
            ->assertSee('Beli Mobil')->assertDontSee('Lihat Benefit Member')
            ->assertSee('member-home-reference.css', false);
        $html = $response->getContent();
        $html = preg_replace('/<meta name="csrf-token"[^>]*>/', '', $html);
        $html = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $html);
        $folder = __DIR__.'/preview';
        if (!is_dir($folder)) mkdir($folder, 0777, true);
        file_put_contents($folder.'/locked.html', $html);
        // Visual-only reference scenario. No demo values enter production code.
        $view = $response->original;
        $view->with([
            'hasVerifiedOwnership' => true,
            'verifiedVehicles' => collect([(object) ['id' => 'visual-fixture']]),
            'activeBookingCount' => 4,
            'activePromoCount' => 5,
            'pointAccount' => (object) ['saldo' => 48],
            'memberId' => 'YZA-MBR-000750',
            'memberSince' => \Carbon\Carbon::parse('2026-10-10'),
        ]);
        $html = preg_replace('/<meta name="csrf-token"[^>]*>/', '', $view->render());
        $html = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $html);
        file_put_contents($folder.'/index.html', $html);
    }
}
