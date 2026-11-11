<?php

namespace App\Services;

use App\Models\{Petugas, Servis, ServiceExecutionEvent, ServiceQualityControl};
use Illuminate\Auth\Access\AuthorizationException;
use RuntimeException;

class InspectionIntegrityService
{
    public function inspector(int $actorId, ?Servis $service = null, string $ability = 'qc', bool $checkCaller = true): Petugas
    {
        $staff = Petugas::with('user.role')->find($actorId);
        if (!$staff || $staff->status !== 'aktif' || $staff->user?->status !== 'aktif') throw new AuthorizationException();
        if ($checkCaller && auth()->check() && (int) auth()->user()->petugas?->id_petugas !== $actorId) throw new AuthorizationException();
        if ($ability === 'qc') app(WorkOrderAuthorizationService::class)->authorize($staff->user, $service, 'qc');
        else {
            $user = $staff->user;
            $allowed = $user->roleKey() === 'super_admin' || $user->roleName() === 'petugas'
                || ($user->roleKey() !== 'owner' && $user->hasPermission($ability === 'pdi' ? 'qc.manage' : 'sales.manage'));
            if (!$allowed) throw new AuthorizationException();
        }
        return $staff;
    }

    public function workSnapshot(Servis $service): string
    {
        $s = $service->fresh(['kendaraan']);
        $vehicle = \App\Models\KendaraanPelanggan::whereKey($s->id_kendaraan_pelanggan)->lockForUpdate()->first();
        $rows = [
            $s->only(['id_servis', 'id_kendaraan_pelanggan', 'id_pelanggan', 'kilometer', 'hasil_pemeriksaan']),
            $vehicle?->only(['nomor_rangka', 'nomor_mesin', 'merk', 'model', 'tahun']),
            $s->details()->orderBy('id_detail_servis')->get()->map->only(['id_detail_servis','id_jenis_servis','deskripsi_pekerjaan','biaya','catatan'])->all(),
            $s->sparepartItems()->orderBy('id_servis_sparepart')->get()->map->only(['id_servis_sparepart','id_sparepart','jumlah','harga_satuan','subtotal'])->all(),
            $s->findings()->orderBy('id_service_finding')->get()->map->only(['id_service_finding','title','description','severity','recommendation'])->all(),
            $s->approvalRequests()->with('items')->orderBy('id_service_approval_request')->get()->map(fn ($a) => [
                $a->only(['id_service_approval_request','status','responded_at']),
                $a->items->sortBy('id_service_approval_request_item')->values()->map->only(['id_service_approval_request_item','item_type','id_sparepart','id_jenis_servis','quantity','unit_price_snapshot','total_snapshot','applied_detail_id','applied_sparepart_id'])->all(),
            ])->all(),
        ];
        return hash('sha256', json_encode($rows));
    }

    public function metadata(Servis $s, ServiceQualityControl $qc): array
    {
        return ['qc_id' => $qc->getKey(), 'checked_by' => $qc->checked_by,
            'checked_at' => $qc->checked_at?->toISOString(), 'work_snapshot' => $this->workSnapshot($s),
            'status' => $qc->status, 'notes' => $qc->notes,
            'items' => $qc->items()->orderBy('item_key')->get()->map->only(['item_key','label_snapshot','is_mandatory','result','note'])->all()];
    }

    public function assertValidQc(Servis $service): void
    {
        $qc = ServiceQualityControl::with('items')->where('id_servis', $service->id_servis)->lockForUpdate()->first();
        if (!$qc || $qc->status !== 'passed' || !$qc->checked_by || !$qc->checked_at) throw new RuntimeException('Finalisasi ditahan sampai Quality Control lengkap dan lulus.');
        $this->inspector((int) $qc->checked_by, $service, 'qc', false);
        foreach (ServiceExecutionService::qcDefinition() as $definition) {
            $item = $qc->items->firstWhere('item_key', $definition['key']);
            if (!$item || !$item->is_mandatory || !in_array($item->result, ['PASS','N/A'], true)) throw new RuntimeException('Checklist Quality Control belum lengkap atau tidak lulus.');
        }
        $event = ServiceExecutionEvent::where('id_servis', $service->id_servis)->whereIn('event_key', ['qc_passed', 'qc_failed', 'qc_in_progress', 'qc_started', 'qc_invalidated'])->latest('id_service_execution_event')->first();
        $proof = $event?->metadata;
        $now = $this->metadata($service, $qc);
        if ($event?->event_key !== 'qc_passed' || !$proof || $proof !== $now) throw new RuntimeException('Quality Control tidak lagi valid. Periksa ulang setelah perubahan pekerjaan.');
        if (trim((string) $service->hasil_pemeriksaan) === '') throw new RuntimeException('Diagnosis wajib selesai sebelum finalisasi.');
    }

    public function invalidate(Servis $service, string $reason): void
    {
        $qc = ServiceQualityControl::where('id_servis', $service->id_servis)->lockForUpdate()->first();
        if (!$qc) return;
        app(ServiceExecutionService::class)->recordEvent($service->id_servis, 'qc_invalidated', 'QC perlu diperiksa ulang: '.$reason,
            'petugas', auth()->user()?->petugas?->id_petugas, $this->metadata($service, $qc));
        $qc->items()->update(['result' => null, 'note' => null]);
        $qc->forceFill(['status' => 'in_progress', 'checked_by' => null, 'checked_at' => null])->save();
    }
}
