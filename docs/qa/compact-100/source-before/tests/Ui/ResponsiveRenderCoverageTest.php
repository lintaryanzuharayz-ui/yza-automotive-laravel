<?php

namespace Tests\Ui;

use App\Models\{Servis, ServiceWorkOrderAssignment, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Artisan, DB, File, Route};
use Tests\TestCase;

class ResponsiveRenderCoverageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_export_authorized_get_views_for_isolated_visual_review(): void
    {
        $this->assertSame('testing', app()->environment());
        $this->assertSame('yza_automotive_laravel_test', DB::connection()->getDatabaseName());
        Artisan::call('yza:seed-local-staff');
        $directory = base_path('docs/qa/responsive-100/site');
        File::ensureDirectoryExists($directory);
        $inventory = []; $pages = [];
        $actors = ['public' => null];
        foreach (User::with('role','petugas')->get()->unique(fn ($u) => $u->roleName()) as $user) {
            if ($user->status === 'aktif') $actors[$user->roleName()] = $user;
        }
        $map = ['mobil' => ['mobil','id_mobil'], 'servis' => ['servis','id_servis'], 'pelanggan' => ['pelanggan','id_pelanggan'],
            'petugas' => ['petugas','id_petugas'], 'sparepart' => ['sparepart','id_sparepart'], 'supplier' => ['supplier','id_supplier'],
            'kategori_sparepart' => ['kategori_sparepart','id_kategori'], 'booking_servis' => ['booking_servis','id_booking_servis'],
            'booking_cuci' => ['booking_cuci','id_booking_cuci'], 'booking_mobil' => ['booking_mobil','id_booking'],
            'test_drive' => ['test_drive','id_test_drive'], 'pengajuan_mobil' => ['pengajuan_mobil','id_pengajuan'],
            'trade_in' => ['trade_in','id_trade_in'], 'penjualan_mobil' => ['penjualan_mobil','id_penjualan'],
            'penjualan_sparepart' => ['penjualan_sparepart','id_penjualan_sparepart'], 'stok_masuk' => ['stok_masuk','id_stok_masuk'],
            'kendaraan' => ['kendaraan_pelanggan','id_kendaraan_pelanggan'], 'pesanan_mobil' => ['pesanan_mobil_member','id_pesanan'],
            'promo' => ['promo_member','id_promo'], 'pembayaran' => ['pembayaran','id_pembayaran']];
        foreach (Route::getRoutes() as $route) {
            if (!in_array('GET', $route->methods(), true)) continue;
            $inventory[] = ['name' => $route->getName(), 'uri' => $route->uri(), 'action' => $route->getActionName(), 'middleware' => $route->gatherMiddleware()];
        }
        foreach ($actors as $role => $user) {
            if ($user) $this->actingAs($user); else auth()->forgetUser();
            if ($role === 'mechanic' && $user?->petugas) {
                $s = Servis::first();
                if ($s) ServiceWorkOrderAssignment::firstOrCreate(['id_servis' => $s->getKey(), 'id_petugas' => $user->petugas->getKey(), 'assignment_role' => 'assistant'], ['assigned_at' => now(), 'assigned_by' => $user->petugas->getKey()]);
            }
            foreach ($inventory as $entry) {
                $uri = $entry['uri']; $name = $entry['name'] ?? '';
                if (preg_match('~(?:export|download|sync|regions|/open|finish|preview|search|receipt|\.pdf|storage|sanctum|up$)~', $uri)) continue;
                if ($role === 'public' && preg_match('~^(admin|owner|sales|staff|petugas|pelanggan|akun|notifikasi|promo-preview)~', $uri)) continue;
                if ($role !== 'public' && !preg_match('~^(admin|owner|sales|staff|petugas|pelanggan|akun|notifikasi)~', $uri)) continue;
                $possible = true;
                $url = preg_replace_callback('/\{([^}?]+)(\?)?\}/', function ($m) use ($map, &$possible, $user) {
                    if ($m[1] === 'transaksi') return DB::table('transaksi')->where('jenis_transaksi', 'servis')->value('id_transaksi');
                    if (!isset($map[$m[1]])) { $possible = false; return ''; }
                    [$table,$key] = $map[$m[1]];
                    $query = DB::table($table);
                    if ($user?->pelanggan && in_array($table, ['kendaraan_pelanggan','servis','pesanan_mobil_member','booking_servis','booking_cuci','trade_in','pengajuan_mobil'], true)) $query->where('id_pelanggan', $user->pelanggan->getKey());
                    $value = $query->value($key);
                    if (!$value) $possible = false;
                    return $value ?? '';
                }, $uri);
                if (!$possible) { $pages[] = ['role' => $role, 'route' => $name, 'uri' => $uri, 'status' => 'fixture-unavailable']; continue; }
                try {
                    $bufferLevel = ob_get_level();
                    $response = $this->get('/'.ltrim($url, '/'));
                    $status = $response->getStatusCode();
                    $row = ['role' => $role, 'route' => $name, 'uri' => $url, 'status' => $status];
                    if ($status === 200 && str_contains($response->getContent(), '<html')) {
                        $file = $role.'--'.preg_replace('/[^a-zA-Z0-9-]/', '-', $name ?: $url ?: 'home').'.html';
                        $html = str_replace(['http://localhost/', 'http://127.0.0.1:8000/'], 'http://127.0.0.1:8011/', $response->getContent());
                        File::put($directory.'/'.$file, $html); $row['file'] = $file;
                    }
                    $pages[] = $row;
                } catch (\Throwable $e) { $pages[] = ['role' => $role, 'route' => $name, 'uri' => $url, 'status' => 'exception', 'error' => get_class($e)]; }
                finally { while (ob_get_level() > $bufferLevel) ob_end_clean(); }
            }
        }
        File::put(dirname($directory).'/render-inventory.json', json_encode(['generated_at' => now()->toIso8601String(), 'database' => DB::connection()->getDatabaseName(), 'routes' => $inventory, 'pages' => $pages,
            'views' => collect(File::allFiles(resource_path('views')))->map(fn ($f) => str_replace('\\','/', $f->getRelativePathname()))->all()], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        $this->assertGreaterThan(30, count(array_filter($pages, fn ($p) => isset($p['file']))));
        $serverErrors = array_values(array_filter($pages, fn ($p) => $p['status'] === 'exception' || (is_int($p['status']) && $p['status'] >= 500)));
        $this->assertSame([], $serverErrors, 'Authorized GET rendering must not produce server errors.');
    }
}
