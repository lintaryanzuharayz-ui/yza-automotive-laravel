<?php

namespace Tests\Feature;

use App\Models\{User, PesananMobilMember, PdiPesananMobil, Pembayaran};
use App\Support\AdminNavigation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SuperAdminMenuAuditTest extends TestCase
{
    use DatabaseTransactions;

    private function actor(string $role = 'admin'): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    private function navLinks(string $html, string $xpath = '//aside[@data-admin-sidebar]//nav//a'): array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        return iterator_to_array((new \DOMXPath($dom))->query($xpath));
    }

    public function test_all_super_admin_menu_routes_render_and_have_accessible_semantic_icons(): void
    {
        $page = $this->actingAs($this->actor())->get(route('admin.dashboard'))->assertOk()->assertSee('Super Admin');
        $links = $this->navLinks($page->getContent());
        $this->assertCount(35, $links);
        $evidence = [];
        foreach ($links as $link) {
            $url = $link->getAttribute('href');
            $this->assertNotEmpty($link->getAttribute('title'));
            $this->assertNotEmpty($link->getAttribute('aria-label'));
            $this->assertSame(1, $link->getElementsByTagName('svg')->length);
            $this->assertNotEmpty($link->getElementsByTagName('path')->item(0)?->getAttribute('d'));
            $response = $this->get($url);
            $this->assertContains($response->status(), [200, 302], $url);
            if (str_contains($url, '/owner/')) {
                $this->assertSame(200, $response->status(), $url.' redirect: '.$response->headers->get('Location'));
                $response->assertSee('Super Admin');
                if (str_contains($url, '/pendapatan-pembayaran')) $response->assertSee('Terverifikasi bruto')->assertSee('Export Excel');
            }
            $evidence[] = ['url' => parse_url($url, PHP_URL_PATH), 'status' => $response->status()];
        }
        file_put_contents(storage_path('logs/super-admin-menu-route-results.json'), json_encode($evidence, JSON_PRETTY_PRINT));
    }

    public function test_active_parent_routes_are_unambiguous_and_icons_do_not_fall_back(): void
    {
        foreach (['admin.mobil.edit' => 'admin.mobil.index', 'admin.pesanan-mobil.show' => 'admin.pesanan-mobil.index',
            'admin.servis.pengerjaan' => 'admin.servis.index', 'admin.preorder-mobil.detail' => 'admin.preorder-mobil.index',
            'admin.payments.show' => 'admin.payments.index', 'admin.servis.schedule' => 'admin.servis.schedule',
            'admin.servis.personnel' => 'admin.servis.personnel'] as $current => $parent) {
            $this->assertTrue(AdminNavigation::active($parent, $current));
            if (str_starts_with($current, 'admin.servis.') && $parent !== 'admin.servis.index') $this->assertFalse(AdminNavigation::active('admin.servis.index', $current));
        }
        foreach (AdminNavigation::ICONS as $route => $name) {
            $this->assertNotSame('info', $name, $route);
            $html = view('components.order-icon', ['name' => $name])->render();
            $this->assertStringContainsString('stroke-width="1.8"', $html);
            $this->assertStringNotContainsString('M12 11v6m0-10h.01', $html, $route); // fallback info path
        }
    }

    public function test_every_role_visible_navigation_has_an_authorized_destination_and_correct_role_label(): void
    {
        foreach (['sales', 'service_advisor', 'mechanic', 'warehouse_staff', 'finance', 'operational_staff', 'quality_control'] as $role) {
            $this->actingAs($this->actor($role));
            $page = $this->get(route('staff.dashboard'))->assertOk()->assertSee(AdminNavigation::roleLabel($role));
            foreach ($this->navLinks($page->getContent()) as $link) {
                $this->assertSame(1, $link->getElementsByTagName('svg')->length);
                $response = $this->get($link->getAttribute('href'));
                $this->assertContains($response->status(), [200, 302], $role.' '.$link->getAttribute('href'));
            }
            $this->assertStringNotContainsString('>Pengaturan Sistem<', $page->getContent());
            $this->get(route('admin.roles.index'))->assertForbidden();
            $this->get(route('admin.audit-log.index'))->assertForbidden();
        }
        $this->actingAs($this->actor('owner'));
        $page = $this->get(route('owner.dashboard'))->assertOk();
        foreach ($this->navLinks($page->getContent(), '//nav[contains(@class,"oe-nav")]//a') as $link) {
            $this->assertContains($this->get($link->getAttribute('href'))->status(), [200, 302]);
        }
        $this->put(route('admin.pengaturan.update', \App\Services\PengaturanService::KEYS[0]), [])->assertForbidden();
        $this->actingAs($this->actor('pelanggan'))->get(route('admin.aktivitas.index'))->assertForbidden();
    }

    public function test_direct_url_permission_matrix_for_all_nine_roles(): void
    {
        $matrix = [
            'admin.roles.index' => ['admin'],
            'admin.payments.index' => ['admin', 'owner', 'finance'],
            'admin.mobil.index' => ['admin', 'owner', 'mechanic', 'warehouse_staff'],
            'admin.servis.index' => ['admin', 'owner', 'service_advisor', 'mechanic', 'operational_staff', 'quality_control'],
        ];
        foreach (['admin', 'owner', 'sales', 'service_advisor', 'mechanic', 'warehouse_staff', 'finance', 'operational_staff', 'quality_control'] as $role) {
            $this->actingAs($this->actor($role));
            foreach ($matrix as $route => $allowed) {
                $response = $this->get(route($route));
                $this->assertSame(in_array($role, $allowed, true) ? 200 : 403, $response->status(), $role.' '.$route);
            }
        }
    }

    private function order(string $status): PesananMobilMember
    {
        $car = \App\Models\Mobil::where('status', 'tersedia')->firstOrFail();
        $order = new PesananMobilMember();
        $order->forceFill(['nomor_pesanan' => 'NAV-QA-'.bin2hex(random_bytes(5)), 'status' => $status,
            'id_pelanggan' => $this->actor('pelanggan')->pelanggan->getKey(), 'id_mobil' => $car->getKey(),
            'snapshot_merk' => $car->merk, 'snapshot_model' => $car->model, 'snapshot_tipe' => $car->tipe,
            'snapshot_tahun' => $car->tahun, 'snapshot_warna' => $car->warna, 'snapshot_harga' => $car->harga_jual,
            'total_tagihan' => $car->harga_jual, 'nama_pembeli' => 'QA Navigation', 'email_pembeli' => 'navigation@example.test',
            'telepon_pembeli' => '080000000', 'alamat_pembeli' => 'QA only', 'syarat_disetujui' => true,
            'created_at' => '2036-01-01 09:00:00', 'updated_at' => '2036-01-01 09:00:00'])->save();
        return $order;
    }

    public function test_activity_kpis_classify_each_record_once_and_links_keep_resource_context(): void
    {
        $waiting = $this->order('menunggu_verifikasi');
        $readyPdi = $this->order('lunas');
        $inspecting = $this->order('lunas');
        $pdi = new PdiPesananMobil();
        $pdi->forceFill(['id_pesanan' => $inspecting->getKey(), 'status' => 'sedang_diperiksa'])->save();
        $done = $this->order('diserahkan');
        $awaitingPickup = $this->order('pdi_lulus');
        $payment = Pembayaran::firstOrFail()->replicate();
        $payment->forceFill(['kode_pembayaran' => 'NAV-PAY-'.bin2hex(random_bytes(5)), 'arah' => 'masuk', 'status' => 'menunggu', 'tanggal' => '2036-01-01 09:00:00'])->save();
        $this->actingAs($this->actor())->get(route('admin.aktivitas.index', ['from' => '2036-01-01', 'to' => '2036-01-01']))
            ->assertOk()->assertViewHas('stats', ['menunggu' => 2, 'diproses' => 1, 'tindakan' => 2, 'selesai' => 1])
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 6 && $tasks->where('resource_type', 'pembayaran')->first()['url'] === route('admin.payments.show', $payment))
            ->assertSee('Jadwal pengambilan perlu disiapkan')
            ->assertSee('task-marker is-menunggu', false)->assertSee('task-marker is-diproses', false)->assertSee('task-marker is-selesai', false);
        $this->actingAs($this->actor('finance'))->get(route('admin.aktivitas.index', ['from' => '2036-01-01', 'to' => '2036-01-01']))
            ->assertOk()->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 1 && $tasks->first()['resource_id'] === $payment->getKey());
    }
}
