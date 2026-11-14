<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Local visual render using the isolated test database, never a public route. */
class RenderMemberDetailPreview extends TestCase
{
    use DatabaseTransactions;

    public function test_render_detail_preview(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        $user = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))
            ->whereHas('pelanggan.membership', fn ($q) => $q->where('status', 'active'))
            ->firstOrFail();
        $this->actingAs($user);
        $directory = __DIR__.'/preview';
        if (!is_dir($directory)) mkdir($directory, 0777, true);
        foreach (['bugatti-chiron' => 'index', 'mercedes-amg-gt-black-series' => 'long-brand'] as $slug => $filename) {
            $response = $this->get('/pelanggan/katalog-mobil/koleksi/'.$slug.'?source=member-home&return_to=%2Fpelanggan%2Fmember');
            $response->assertOk()->assertSee('member-vehicle-detail.css', false);
            $html = preg_replace('/<meta name="csrf-token"[^>]*>/', '', $response->getContent());
            $html = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $html);
            file_put_contents($directory.'/'.$filename.'.html', $html);
        }
    }
}
