<?php

namespace Tests\Feature;

use App\Models\Servis;
use App\Models\User;
use App\Services\WorkOrderStageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WorkOrderWizardPhaseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_work_order_root_redirects_to_authoritative_current_stage(): void
    {
        $service = Servis::firstOrFail();
        $admin = User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))->firstOrFail();
        $stage = app(WorkOrderStageService::class)->currentStage($service);

        $this->actingAs($admin)
            ->get(route('admin.servis.show', $service))
            ->assertRedirect(route('admin.servis.'.$stage, $service));
    }

    public function test_initial_inspection_work_order_blocks_future_finalization_url(): void
    {
        $service = Servis::where('status', 'pemeriksaan')->first();
        if (!$service) {
            $source = Servis::firstOrFail();
            $service = $source->replicate();
            $service->kode_servis = 'SRV-TEST-INSPECTION';
            $service->id_transaksi = null;
            $service->id_booking_servis = null;
            $service->status = 'pemeriksaan';
            $service->tanggal_selesai = null;
            $service->save();
        }
        $admin = User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.servis.finalisasi', $service))
            ->assertRedirect(route('admin.servis.pemeriksaan', $service));
    }

    public function test_all_wizard_named_routes_are_registered_for_admin_and_petugas(): void
    {
        foreach (['admin', 'petugas'] as $prefix) {
            foreach (WorkOrderStageService::STAGES as $stage) {
                $this->assertTrue(route($prefix.'.servis.'.$stage, ['servis' => 1]) !== '');
            }
        }
    }

    public function test_pengerjaan_renders_qc_action_and_estimate_instead_of_old_pengerjaan_action(): void
    {
        $service = Servis::where('status', 'dikerjakan')
            ->whereDoesntHave('qualityControl', fn ($query) => $query->where('status', 'passed'))
            ->first();
        if (!$service) {
            $this->markTestSkipped('Tidak ada Work Order pengerjaan pada fixture saat ini.');
        }

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.servis.pengerjaan', $service));

        $response->assertOk()
            ->assertSee('Tindakan Berikutnya')
            ->assertSee('Pastikan seluruh pekerjaan, jasa, dan sparepart sudah tercatat sebelum melanjutkan ke pemeriksaan mutu.')
            ->assertSee('Lanjut ke Pemeriksaan Mutu')
            ->assertSee('Estimasi Jasa')
            ->assertSee('Estimasi Suku Cadang')
            ->assertSee('Estimasi Total')
            ->assertDontSee('Lanjut ke Pengerjaan →');
    }

    private function adminUser(): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))->firstOrFail();
    }
}
