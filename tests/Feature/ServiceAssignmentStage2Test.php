<?php

namespace Tests\Feature;

use App\Models\{ServiceStaffProfile, Servis};
use App\Services\ServiceAssignmentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ServiceAssignmentStage2Test extends TestCase
{
    use DatabaseTransactions;

    public function test_primary_and_assistant_assignment_keeps_history_and_reason(): void
    {
        $service = Servis::firstOrFail();
        $profiles = ServiceStaffProfile::where('id_petugas', '!=', $service->id_petugas)->where('can_be_technician', false)->limit(2)->get();
        if ($profiles->count() < 2) $profiles = ServiceStaffProfile::limit(2)->get();
        $this->assertCount(2, $profiles);
        $profiles->each(fn ($profile) => $profile->update(['can_be_technician' => true, 'is_bookable' => true]));

        app(ServiceAssignmentService::class)->assignPrimary($service, $profiles[0]->id_petugas, null, 'Ganti teknisi karena kompetensi');
        app(ServiceAssignmentService::class)->setAssistants($service, [$profiles[1]->id_petugas], null, 'Bantuan pekerjaan khusus');

        $this->assertSame($profiles[0]->id_petugas, $service->fresh()->id_petugas);
        $this->assertDatabaseHas('service_work_order_assignments', ['id_servis' => $service->id_servis, 'id_petugas' => $profiles[0]->id_petugas, 'assignment_role' => 'primary', 'reason' => 'Ganti teknisi karena kompetensi']);
        $this->assertDatabaseHas('service_work_order_assignments', ['id_servis' => $service->id_servis, 'id_petugas' => $profiles[1]->id_petugas, 'assignment_role' => 'assistant', 'reason' => 'Bantuan pekerjaan khusus']);
        $this->assertDatabaseHas('service_execution_events', ['id_servis' => $service->id_servis, 'event_key' => 'assignment_changed']);

        $member = $service->fresh('pelanggan')->pelanggan->user;
        $this->actingAs($member)->get(route('pelanggan.servis.show', $service))
            ->assertOk()->assertSee('Mekanik Utama')->assertSee('Mekanik Pendamping')->assertSee($profiles[0]->petugas->nama_lengkap)->assertSee($profiles[1]->petugas->nama_lengkap);
    }
}
