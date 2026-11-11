<?php

namespace Tests\Feature;

use App\Models\{Mobil, PesananMobilMember, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Isolated sample order rows for visual inspection; never a production route. */
class RenderMemberOrdersPreview extends TestCase
{
    use DatabaseTransactions;

    public function test_render_compact_orders(): void
    {
        $this->assertSame('yza_automotive_laravel_test', \DB::connection()->getDatabaseName());
        $user = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))
            ->whereHas('pelanggan.membership', fn ($q) => $q->where('status', 'active'))->firstOrFail();
        $directory = __DIR__.'/preview';
        if (!is_dir($directory)) mkdir($directory, 0777, true);
        foreach ([['Challenger SRT Hellcat Redeye', 'dibatalkan'], ['GR Supra', 'diserahkan'], ['LFA', 'menunggu_pembayaran']] as $i => [$model, $status]) {
            $car = Mobil::where('model', $model)->firstOrFail();
            $order = new PesananMobilMember();
            $order->forceFill([
                'nomor_pesanan' => 'QA-VISUAL-'.($i+1), 'id_pelanggan' => $user->pelanggan->id_pelanggan,
                'id_mobil' => $car->id_mobil, 'nama_pembeli' => 'Member Preview',
                'snapshot_merk' => $car->merk, 'snapshot_model' => $car->model, 'snapshot_tipe' => $car->tipe,
                'snapshot_tahun' => $car->tahun, 'snapshot_warna' => $car->warna,
                'snapshot_harga' => $car->harga_jual, 'total_tagihan' => $car->harga_jual,
                'syarat_disetujui' => true, 'status' => $status,
                'created_at' => now()->subDays($i+1), 'diputuskan_at' => now()->subDays($i+1)->addHour(),
                'diserahkan_at' => $status === 'diserahkan' ? now()->subDay() : null,
            ])->save();
        }
        $this->actingAs($user);
        foreach (['index' => '', 'filtered' => '?q=QA-VISUAL&status=dibatalkan', 'empty' => '?q=NOT-AN-ORDER-987654'] as $file => $query) {
            $response = $this->get('/pelanggan/pesanan-mobil'.$query);
            $response->assertOk()->assertSee('member-orders.css', false);
            if ($file === 'index') $response->assertSee('QA-VISUAL-1')->assertSee('QA-VISUAL-2')->assertSee('QA-VISUAL-3');
            if ($file === 'filtered') $response->assertSee('QA-VISUAL-1')->assertDontSee('QA-VISUAL-2');
            if ($file === 'empty') $response->assertSee('Pesanan tidak ditemukan')->assertSee('Tampilkan Semua Pesanan');
            $html = preg_replace('/<meta name="csrf-token"[^>]*>/', '', $response->getContent());
            $html = preg_replace('/<input[^>]*name="_token"[^>]*>/', '', $html);
            file_put_contents($directory.'/'.$file.'.html', $html);
        }
    }
}
