<?php

namespace Tests\Feature;

use App\Models\KendaraanPelanggan;
use App\Models\Petugas;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminWorkspaceNavigationTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    public function test_admin_sidebar_destinations_render_real_workspace_pages(): void
    {
        $admin = $this->actingAs($this->user('admin'));

        $admin->get(route('admin.aktivitas.index'))
            ->assertOk()
            ->assertSee('Aktivitas &amp; Tugas Admin', false)
            ->assertSee('Daftar tugas operasional', false)
            ->assertSee('Riwayat aktivitas', false);

        $admin->get(route('admin.katalog-showroom.index'))
            ->assertRedirect(route('admin.mobil.index', ['tab' => 'katalog']));

        $admin->get(route('admin.mobil.index', ['tab' => 'katalog']))
            ->assertOk()
            ->assertSee('Katalog &amp; Showroom', false)
            ->assertSee('Total Kendaraan', false)
            ->assertSee('Lihat Detail', false);

        $admin->get(route('admin.mobil.index'))
            ->assertOk()
            ->assertSee('Manajemen Mobil &amp; Showroom', false)
            ->assertSee('Inventori &amp; Stok', false)
            ->assertSee('Katalog &amp; Showroom', false)
            ->assertSee('Foto &amp; Warna', false);
    }

    public function test_member_cannot_open_admin_workspace_pages(): void
    {
        $member = $this->actingAs($this->user('pelanggan'));

        $member->get(route('admin.aktivitas.index'))->assertForbidden();
        $member->get(route('admin.katalog-showroom.index'))->assertForbidden();
    }

    public function test_sidebar_auxiliary_destinations_render_real_data_pages(): void
    {
        $admin = $this->actingAs($this->user('admin'));

        $pages = [
            ['admin.pdi.index', 'Daftar Pesanan Mobil'],
            ['admin.pengambilan.index', 'Daftar Pesanan Mobil'],
            ['admin.serah-terima.index', 'Daftar Pesanan Mobil'],
            ['admin.transaksi.index', 'Semua Transaksi'],
            ['admin.invoices.index', 'Invoice &amp; Kwitansi'],
            ['admin.garage.index', 'My Garage'],
            ['admin.roles.index', 'Role &amp; Permission'],
            ['admin.audit-log.index', 'Audit Log'],
        ];

        foreach ($pages as [$routeName, $heading]) {
            $admin->get(route($routeName))->assertOk()->assertSee($heading, false);
        }
    }

    public function test_my_garage_excludes_vehicle_without_verified_handover_source(): void
    {
        $customer = User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))->firstOrFail()->pelanggan;
        $manual = new KendaraanPelanggan;
        $manual->forceFill([
            'id_pelanggan' => $customer->id_pelanggan,
            'merk' => 'QA Manual',
            'model' => 'Tidak Terverifikasi',
            'tipe' => 'Test Fixture',
            'tahun' => 2026,
            'status' => 'aktif',
        ])->save();

        $this->actingAs($this->user('admin'))
            ->get(route('admin.garage.index'))
            ->assertOk()
            ->assertDontSee('QA Manual Tidak Terverifikasi', false)
            ->assertSee('Siap Servis', false)
            ->assertSee('—', false);
    }

    public function test_petugas_dashboard_search_and_status_filters_use_real_query_parameters(): void
    {
        $staff = Petugas::firstOrFail();
        $admin = $this->actingAs($this->user('admin'));

        $admin->get(route('admin.petugas.index', ['q' => $staff->kode_petugas]))
            ->assertOk()
            ->assertSee($staff->kode_petugas, false)
            ->assertSee($staff->nama_lengkap, false);

        $admin->get(route('admin.petugas.index', ['status' => $staff->status]))
            ->assertOk()
            ->assertSee('Semua Jabatan', false)
            ->assertSee(ucfirst($staff->status), false);
    }
}
