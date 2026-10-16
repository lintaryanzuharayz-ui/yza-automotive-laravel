<?php

namespace App\Services;

use App\Models\{Petugas, Servis, ServiceStaffProfile, ServiceWorkOrderAssignment};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServiceAssignmentService
{
    public function recordInitial(Servis $servis, int $petugasId, ?int $actorPetugasId = null, ?string $reason = 'Penugasan awal Work Order'): void
    {
        ServiceWorkOrderAssignment::create([
            'id_servis' => $servis->id_servis,
            'id_petugas' => $petugasId,
            'assignment_role' => 'primary',
            'assigned_at' => now(),
            'assigned_by' => $actorPetugasId,
            'reason' => $reason,
        ]);
    }

    public function assignPrimary(Servis $servis, int $petugasId, ?int $actorPetugasId = null, ?string $reason = null): Servis
    {
        return DB::transaction(function () use ($servis, $petugasId, $actorPetugasId, $reason): Servis {
            $servis = Servis::lockForUpdate()->findOrFail($servis->id_servis);
            $this->assertTechnician($petugasId);
            $old = (int) $servis->id_petugas;
            if ($old === $petugasId && ServiceWorkOrderAssignment::where('id_servis', $servis->id_servis)->where('assignment_role', 'primary')->whereNull('unassigned_at')->exists()) {
                return $servis->fresh(['petugas']);
            }
            ServiceWorkOrderAssignment::where('id_servis', $servis->id_servis)->where('assignment_role', 'primary')->whereNull('unassigned_at')->update(['unassigned_at' => now(), 'reason' => $reason]);
            ServiceWorkOrderAssignment::create(['id_servis' => $servis->id_servis, 'id_petugas' => $petugasId, 'assignment_role' => 'primary', 'assigned_at' => now(), 'assigned_by' => $actorPetugasId, 'reason' => $reason]);
            $servis->forceFill(['id_petugas' => $petugasId])->save();
            app(ServiceExecutionService::class)->recordEvent($servis->id_servis, 'assignment_changed', 'Teknisi utama Work Order diubah', 'petugas', $actorPetugasId, ['from_petugas_id' => $old ?: null, 'to_petugas_id' => $petugasId, 'reason' => $reason]);
            return $servis->fresh(['petugas']);
        });
    }

    public function setAssistants(Servis $servis, array $petugasIds, ?int $actorPetugasId = null, ?string $reason = null): void
    {
        DB::transaction(function () use ($servis, $petugasIds, $actorPetugasId, $reason): void {
            $servis = Servis::lockForUpdate()->findOrFail($servis->id_servis);
            $ids = collect($petugasIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
            if ($ids->contains((int) $servis->id_petugas)) throw new RuntimeException('Teknisi utama tidak boleh sekaligus menjadi assistant.');
            foreach ($ids as $id) $this->assertTechnician($id);
            ServiceWorkOrderAssignment::where('id_servis', $servis->id_servis)->where('assignment_role', 'assistant')->whereNull('unassigned_at')->update(['unassigned_at' => now(), 'reason' => $reason]);
            foreach ($ids as $id) ServiceWorkOrderAssignment::create(['id_servis' => $servis->id_servis, 'id_petugas' => $id, 'assignment_role' => 'assistant', 'assigned_at' => now(), 'assigned_by' => $actorPetugasId, 'reason' => $reason]);
            app(ServiceExecutionService::class)->recordEvent($servis->id_servis, 'assistants_changed', 'Assistant Work Order diperbarui', 'petugas', $actorPetugasId, ['petugas_ids' => $ids->all(), 'reason' => $reason]);
        });
    }

    private function assertTechnician(int $petugasId): void
    {
        $valid = ServiceStaffProfile::where('id_petugas', $petugasId)->where('can_be_technician', true)->whereHas('petugas', fn ($q) => $q->where('status', 'aktif'))->exists();
        if (!$valid) throw new RuntimeException('Petugas bukan teknisi aktif yang eligible.');
    }
}
