<?php

namespace App\Services;

use App\Models\{DetailServis, JenisServis, Servis, ServisSparepart, ServiceApprovalRequest, ServiceApprovalRequestItem, ServiceExecutionEvent, ServiceFinding, ServiceQualityControl, ServiceQualityControlItem, Sparepart};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServiceExecutionService
{
    private const QC_ITEMS = [
        ['key' => 'work_order', 'label' => 'Pekerjaan sesuai Work Order'],
        ['key' => 'warning_indicator', 'label' => 'Tidak ada warning indicator abnormal'],
        ['key' => 'fluid_area', 'label' => 'Fluid dan area relevan diperiksa'],
        ['key' => 'safety_check', 'label' => 'Pekerjaan rem atau safety sudah diperiksa bila relevan'],
        ['key' => 'clean_vehicle', 'label' => 'Area kerja kendaraan bersih'],
        ['key' => 'functional_check', 'label' => 'Final functional check atau road-test selesai bila relevan'],
    ];

    public function recordEvent(int $serviceId, string $key, string $label, ?string $actorType = null, ?int $actorId = null, array $metadata = []): ServiceExecutionEvent
    {
        return ServiceExecutionEvent::create([
            'id_servis' => $serviceId, 'event_key' => $key, 'label_snapshot' => $label,
            'actor_type' => $actorType, 'actor_id' => $actorId, 'metadata' => $metadata ?: null,
            'occurred_at' => now(),
        ]);
    }

    public function createFinding(int $serviceId, array $data, int $actorId): ServiceFinding
    {
        $service = Servis::findOrFail($serviceId);
        if (!in_array($service->status, ['pemeriksaan', 'dikerjakan'], true) || $service->id_transaksi) {
            throw new RuntimeException('Temuan hanya dapat dibuat saat Work Order masih berjalan.');
        }
        $finding = ServiceFinding::create([
            'id_servis' => $serviceId, 'title' => trim($data['title']), 'description' => trim($data['description']),
            'recommendation' => $data['recommendation'] ?? null, 'severity' => $data['severity'] ?? 'info',
            'created_by' => $actorId,
        ]);
        $this->recordEvent($serviceId, 'finding_created', 'Temuan tambahan dibuat', 'petugas', $actorId, ['finding_id' => $finding->id_service_finding]);
        return $finding;
    }

    public function createApprovalRequest(int $serviceId, array $data, int $actorId): ServiceApprovalRequest
    {
        return DB::transaction(function () use ($serviceId, $data, $actorId): ServiceApprovalRequest {
            $service = Servis::with('kendaraan')->lockForUpdate()->findOrFail($serviceId);
            if (!in_array($service->status, ['pemeriksaan', 'dikerjakan'], true) || $service->id_transaksi) {
                throw new RuntimeException('Approval hanya dapat dibuat saat Work Order masih berjalan.');
            }
            if (ServiceApprovalRequest::where('id_servis', $serviceId)->where('status', 'pending')->exists()) {
                throw new RuntimeException('Masih ada approval customer yang menunggu respons.');
            }
            if (!empty($data['id_service_finding']) && !ServiceFinding::where('id_servis', $serviceId)->whereKey($data['id_service_finding'])->exists()) {
                throw new RuntimeException('Temuan tidak terkait dengan Work Order ini.');
            }
            $clarificationOfApprovalId = isset($data['clarification_of_approval_id']) ? (int) $data['clarification_of_approval_id'] : null;
            if (!$clarificationOfApprovalId) {
                $openClarifications = $this->unresolvedApprovalClarificationIds($serviceId);
                if ($openClarifications->count() === 1) $clarificationOfApprovalId = (int) $openClarifications->first();
            }
            if ($clarificationOfApprovalId) {
                $oldApproval = ServiceApprovalRequest::where('id_servis', $serviceId)->whereKey($clarificationOfApprovalId)->where('status', 'approved')->first();
                if (!$oldApproval || !ServiceExecutionEvent::where('id_servis', $serviceId)->where('event_key', 'approval_clarification_requested')->whereJsonContains('metadata->approval_id', $clarificationOfApprovalId)->exists()) {
                    throw new RuntimeException('Approval revisi harus terhubung ke klarifikasi approval lama yang sah.');
                }
            }
            $request = ServiceApprovalRequest::create([
                'id_servis' => $serviceId, 'id_service_finding' => $data['id_service_finding'] ?? null,
                'title' => trim($data['title']), 'reason' => trim($data['reason']), 'requested_by' => $actorId,
                'requested_at' => now(), 'status' => 'pending',
            ]);
            foreach ($data['items'] as $item) {
                $type = strtolower($item['item_type']);
                $quantity = max(1, (int) $item['quantity']);
                if ($type === 'labor') {
                    $master = JenisServis::where('status', 'aktif')->findOrFail((int) $item['id_jenis_servis']);
                    $description = $master->nama_servis;
                    $unit = (string) $master->estimasi_harga;
                    $request->items()->create(['item_type' => 'labor', 'description' => $description, 'quantity' => $quantity, 'unit_price_snapshot' => $unit, 'total_snapshot' => (float) $unit * $quantity, 'id_jenis_servis' => $master->id_jenis_servis]);
                } elseif ($type === 'sparepart') {
                    $master = Sparepart::where('status', 'aktif')->findOrFail((int) $item['id_sparepart']);
                    app(SparepartFitmentService::class)->assertUsableForVehicle($master, $service->kendaraan);
                    if ((int) $master->stok < $quantity) throw new RuntimeException('Stok spare part tidak cukup untuk approval ini.');
                    $description = $master->nama_sparepart;
                    $unit = (string) $master->harga_jual;
                    $request->items()->create(['item_type' => 'sparepart', 'description' => $description, 'quantity' => $quantity, 'unit_price_snapshot' => $unit, 'total_snapshot' => (float) $unit * $quantity, 'id_sparepart' => $master->id_sparepart]);
                } else {
                    throw new RuntimeException('Jenis item approval tidak didukung.');
                }
            }
            $metadata = ['approval_id' => $request->id_service_approval_request];
            if ($clarificationOfApprovalId) {
                $metadata['clarification_of_approval_id'] = $clarificationOfApprovalId;
                $this->recordEvent($serviceId, 'approval_revision_requested', 'Persetujuan revisi customer diminta', 'petugas', $actorId, [
                    'old_approval_id' => $clarificationOfApprovalId,
                    'new_approval_id' => $request->id_service_approval_request,
                ]);
            }
            $this->recordEvent($serviceId, 'approval_requested', 'Persetujuan customer diminta', 'petugas', $actorId, $metadata);
            app(ServiceNotificationService::class)->forService($service->fresh('pelanggan'), 'Persetujuan servis diperlukan', 'Ada pekerjaan tambahan yang menunggu persetujuan Anda.');
            return $request->fresh('items');
        });
    }

    public function requestApprovalClarification(int $serviceId, int $approvalId, int $actorId, string $reason): ServiceExecutionEvent
    {
        return DB::transaction(function () use ($serviceId, $approvalId, $actorId, $reason): ServiceExecutionEvent {
            $service = Servis::lockForUpdate()->findOrFail($serviceId);
            if ($service->id_transaksi || !in_array($service->status, ['pemeriksaan', 'dikerjakan'], true)) {
                throw new RuntimeException('Klarifikasi hanya dapat diajukan saat Work Order masih berjalan.');
            }
            $approval = ServiceApprovalRequest::with('items')->where('id_servis', $serviceId)->lockForUpdate()->findOrFail($approvalId);
            if ($approval->status !== 'approved') {
                throw new RuntimeException('Klarifikasi item hanya dapat diajukan untuk approval yang sudah diputuskan.');
            }
            $alreadyRequested = ServiceExecutionEvent::where('id_servis', $serviceId)
                ->where('event_key', 'approval_clarification_requested')
                ->whereJsonContains('metadata->approval_id', $approvalId)
                ->exists();
            if ($alreadyRequested) {
                throw new RuntimeException('Klarifikasi untuk approval ini sudah tercatat.');
            }

            return $this->recordEvent($serviceId, 'approval_clarification_requested', 'Klarifikasi keterkaitan item approval diajukan', 'petugas', $actorId, [
                'approval_id' => $approval->id_service_approval_request,
                'approval_item_ids' => $approval->items->pluck('id_service_approval_request_item')->values()->all(),
                'reason' => trim($reason),
                'next_action' => 'Buat persetujuan customer baru dengan pekerjaan dan item sebenarnya.',
            ]);
        });
    }

    public function decide(int $serviceId, int $approvalId, string $decision, ?string $note, int $customerId): ServiceApprovalRequest
    {
        return DB::transaction(function () use ($serviceId, $approvalId, $decision, $note, $customerId): ServiceApprovalRequest {
            $request = ServiceApprovalRequest::with('items')->lockForUpdate()->where('id_servis', $serviceId)->findOrFail($approvalId);
            $service = Servis::lockForUpdate()->findOrFail($serviceId);
            if ((int) $service->id_pelanggan !== $customerId) abort(403);
            if (!in_array($decision, ['approve', 'reject'], true)) throw new RuntimeException('Keputusan approval tidak valid.');
            $target = $decision === 'approve' ? 'approved' : 'rejected';
            if ($request->status === $target) return $request;
            if ($request->status !== 'pending') throw new RuntimeException('Approval sudah diputuskan dan tidak dapat diubah.');
            if ($target === 'approved') $this->applyApprovedItems($service, $request);
            $request->forceFill(['status' => $target, 'responded_at' => now(), 'customer_note' => $note])->save();
            $this->recordEvent($serviceId, 'approval_'.$target, $target === 'approved' ? 'Approval customer disetujui' : 'Approval customer ditolak', 'pelanggan', $customerId, ['approval_id' => $request->id_service_approval_request]);
            if ($target === 'approved') {
                $revision = ServiceExecutionEvent::where('id_servis', $serviceId)->where('event_key', 'approval_revision_requested')->get()->first(fn ($event) => (int) data_get($event->metadata, 'new_approval_id') === (int) $request->id_service_approval_request);
                if ($revision) {
                    $this->recordEvent($serviceId, 'approval_clarification_resolved', 'Klarifikasi approval diselesaikan melalui approval revisi', 'pelanggan', $customerId, [
                        'old_approval_id' => (int) data_get($revision->metadata, 'old_approval_id'),
                        'new_approval_id' => $request->id_service_approval_request,
                        'decision' => $target,
                    ]);
                }
            }
            app(ServiceNotificationService::class)->forService($service->fresh('pelanggan'), $target === 'approved' ? 'Persetujuan servis diterima' : 'Persetujuan servis ditolak', $target === 'approved' ? 'Pekerjaan tambahan disetujui customer.' : 'Pekerjaan tambahan ditolak customer.');
            return $request->fresh('items');
        });
    }

    private function applyApprovedItems(Servis $service, ServiceApprovalRequest $request): void
    {
        if ($service->status !== 'dikerjakan') throw new RuntimeException('Approval dapat diproses setelah pemeriksaan selesai.');
        foreach ($request->items as $item) {
            if ($item->item_type === 'labor' && !$item->applied_detail_id) {
                $detail = new DetailServis();
                $detail->forceFill(['id_servis' => $service->id_servis, 'id_jenis_servis' => $item->id_jenis_servis, 'deskripsi_pekerjaan' => $item->description, 'biaya' => $item->total_snapshot, 'catatan' => 'Disetujui customer pada approval #'.$request->id_service_approval_request])->save();
                $item->forceFill(['applied_detail_id' => $detail->id_detail_servis])->save();
            }
            if ($item->item_type === 'sparepart' && !$item->applied_sparepart_id) {
                $part = Sparepart::where('status', 'aktif')->lockForUpdate()->findOrFail($item->id_sparepart);
                app(SparepartFitmentService::class)->assertUsableForVehicle($part, $service->kendaraan);
                if ((int) $part->stok < (int) $item->quantity) throw new RuntimeException('Stok spare part tidak cukup untuk approval ini.');
                $line = ServisSparepart::where('id_servis', $service->id_servis)
                    ->where('id_sparepart', $part->id_sparepart)
                    ->where('jumlah', $item->quantity)
                    ->lockForUpdate()
                    ->first();
                if (!$line) {
                    $line = new ServisSparepart();
                    $line->forceFill(['id_servis' => $service->id_servis, 'id_sparepart' => $part->id_sparepart, 'jumlah' => $item->quantity, 'harga_satuan' => $item->unit_price_snapshot, 'subtotal' => $item->total_snapshot])->save();
                }
                $item->forceFill(['applied_sparepart_id' => $line->id_servis_sparepart])->save();
            }
        }
    }

    public function assertCanFinalize(int $serviceId): void
    {
        if (ServiceApprovalRequest::where('id_servis', $serviceId)->where('status', 'pending')->exists()) throw new RuntimeException('Finalisasi ditahan karena masih ada approval customer yang menunggu.');
        if ($this->hasUnresolvedApprovalClarification($serviceId)) throw new RuntimeException('Finalisasi ditahan karena klarifikasi approval belum diselesaikan melalui approval revisi yang sah.');
        $qc = ServiceQualityControl::where('id_servis', $serviceId)->first();
        if ($qc && $qc->status !== 'passed') throw new RuntimeException('Finalisasi ditahan sampai Quality Control berstatus lulus.');
    }

    public function startQualityControl(int $serviceId, int $actorId): ServiceQualityControl
    {
        return DB::transaction(function () use ($serviceId, $actorId): ServiceQualityControl {
            $service = Servis::lockForUpdate()->findOrFail($serviceId);
            if ($service->status !== 'dikerjakan' || $service->id_transaksi) throw new RuntimeException('QC hanya dapat dimulai saat pengerjaan aktif.');
            $qc = ServiceQualityControl::firstOrCreate(['id_servis' => $serviceId], ['status' => 'not_started']);
            if ($qc->status === 'passed') return $qc->load('items');
            $qc->forceFill(['status' => 'in_progress'])->save();
            foreach (self::QC_ITEMS as $item) $qc->items()->firstOrCreate(['item_key' => $item['key']], ['label_snapshot' => $item['label'], 'is_mandatory' => true]);
            $this->recordEvent($serviceId, 'qc_started', 'Quality Control dimulai', 'petugas', $actorId);
            return $qc->fresh('items');
        });
    }

    public function updateQualityControl(int $serviceId, array $items, ?string $notes, int $actorId): ServiceQualityControl
    {
        return DB::transaction(function () use ($serviceId, $items, $notes, $actorId): ServiceQualityControl {
            $qc = ServiceQualityControl::with('items')->where('id_servis', $serviceId)->lockForUpdate()->firstOrFail();
            if ($qc->status === 'passed') throw new RuntimeException('QC yang sudah lulus tidak dapat diubah.');
            foreach ($items as $itemId => $data) {
                $item = $qc->items->firstWhere('id_service_quality_control_item', (int) $itemId);
                if (!$item) continue;
                $result = strtoupper((string) ($data['result'] ?? ''));
                if (!in_array($result, ['PASS', 'FAIL', 'N/A'], true)) throw new RuntimeException('Hasil checklist QC tidak valid.');
                if ($result === 'FAIL' && trim((string) ($data['note'] ?? '')) === '') throw new RuntimeException('Checklist QC yang FAIL wajib memiliki catatan.');
                $item->forceFill(['result' => $result, 'note' => $data['note'] ?? null])->save();
            }
            $hasFail = $qc->items()->where('is_mandatory', true)->where('result', 'FAIL')->exists();
            $missing = $qc->items()->where('is_mandatory', true)->whereNull('result')->exists();
            $status = $hasFail ? 'failed' : ($missing ? 'in_progress' : 'passed');
            $qc->forceFill(['status' => $status, 'checked_by' => $status === 'passed' ? $actorId : null, 'checked_at' => $status === 'passed' ? now() : null, 'notes' => $notes])->save();
            $this->recordEvent($serviceId, 'qc_'.$status, $status === 'passed' ? 'Quality Control lulus' : ($status === 'failed' ? 'Quality Control perlu perbaikan ulang' : 'Quality Control diperbarui'), 'petugas', $actorId);
            if ($status === 'passed') app(ServiceNotificationService::class)->forService($qc->servis()->with('pelanggan')->first(), 'Quality Control selesai', 'Quality Control servis telah lulus.');
            return $qc->fresh('items');
        });
    }

    public function assertReadyForQualityControl(int $serviceId): void
    {
        $service = Servis::with(['bookingServis', 'approvalRequests.items.approval', 'sparepartItems'])->findOrFail($serviceId);
        if ($service->status !== 'dikerjakan' || $service->id_transaksi) {
            throw new RuntimeException('Pemeriksaan mutu hanya dapat dimulai saat pengerjaan aktif.');
        }
        if ($service->approvalRequests->where('status', 'pending')->isNotEmpty()) {
            throw new RuntimeException('Pemeriksaan mutu ditahan karena masih ada persetujuan pelanggan yang menunggu.');
        }
        if ($this->hasUnresolvedApprovalClarification($serviceId)) {
            throw new RuntimeException('Pemeriksaan mutu ditahan karena klarifikasi approval belum diselesaikan melalui approval revisi yang sah.');
        }

        $approvalItems = $service->approvalRequests->flatMap(fn ($request) => $request->items);
        if ($approvalItems->isNotEmpty()) {
            $approvedItems = $approvalItems->filter(fn ($item) => $item->approval?->status === 'approved');
            foreach ($service->details as $detail) {
                $isCustomerRequest = $service->bookingServis?->id_jenis_servis
                    && (int) $service->bookingServis->id_jenis_servis === (int) $detail->id_jenis_servis;
                $isApprovedRecommendation = $approvedItems->contains(fn ($item) => (int) $item->applied_detail_id === (int) $detail->id_detail_servis);
                if (!$isCustomerRequest && !$isApprovedRecommendation) {
                    throw new RuntimeException('Pekerjaan tambahan belum memiliki persetujuan yang sesuai.');
                }
            }
            foreach ($service->sparepartItems as $item) {
                $isApprovedRecommendation = $approvedItems->contains(fn ($approvalItem) => (int) $approvalItem->applied_sparepart_id === (int) $item->id_servis_sparepart);
                if (!$isApprovedRecommendation) {
                    throw new RuntimeException('Item suku cadang belum memiliki persetujuan yang sesuai.');
                }
            }
        }

        $quantities = $service->sparepartItems->groupBy('id_sparepart')->map(fn ($items) => (int) $items->sum('jumlah'));
        foreach ($quantities as $sparepartId => $quantity) {
            $stock = (int) Sparepart::whereKey($sparepartId)->value('stok');
            if ($quantity <= 0 || $quantity > $stock) {
                throw new RuntimeException('Jumlah suku cadang melebihi stok tersedia. Periksa kembali draft suku cadang.');
            }
        }
    }

    private function hasUnresolvedApprovalClarification(int $serviceId): bool
    {
        return $this->unresolvedApprovalClarificationIds($serviceId)->isNotEmpty();
    }

    private function unresolvedApprovalClarificationIds(int $serviceId)
    {
        $requested = ServiceExecutionEvent::where('id_servis', $serviceId)->where('event_key', 'approval_clarification_requested')->get();
        $resolved = ServiceExecutionEvent::where('id_servis', $serviceId)->where('event_key', 'approval_clarification_resolved')->get();
        return $requested->map(fn ($event) => (int) data_get($event->metadata, 'approval_id'))->filter()->unique()->reject(fn ($approvalId) => $resolved->contains(fn ($item) => (int) data_get($item->metadata, 'old_approval_id') === $approvalId))->values();
    }

    public function tracking(Servis $service): array
    {
        $service->loadMissing(['bookingServis', 'transaksi', 'executionEvents', 'qualityControl', 'approvalRequests']);
        $pending = $service->approvalRequests->where('status', 'pending')->count();
        $bookingDone = $service->bookingServis && !in_array($service->bookingServis->status, ['menunggu', 'ditolak', 'dibatalkan'], true);
        $inspectionDone = filled($service->hasil_pemeriksaan) || in_array($service->status, ['dikerjakan', 'selesai'], true);
        $qc = $service->qualityControl;
        $paid = $service->transaksi?->status === 'lunas';
        $ready = $paid && $service->status === 'selesai';
        $stages = [
            $this->stage('booking_confirmed', 'Booking Dikonfirmasi', $bookingDone, false, $bookingDone ? ($service->bookingServis?->updated_at ?: $service->created_at) : null),
            $this->stage('check_in', 'Check-in Kendaraan', true, false, $service->created_at),
            $this->stage('inspection', 'Pemeriksaan', $inspectionDone, false, $inspectionDone ? ($service->updated_at ?: $service->created_at) : null),
            $this->stage('customer_approval', 'Persetujuan Customer', $service->approvalRequests->isEmpty() ? false : $pending === 0, $pending > 0, $pending > 0 ? null : $service->approvalRequests->max('responded_at')),
            $this->stage('in_progress', 'Pengerjaan', $service->status === 'selesai', $pending === 0 && $service->status === 'dikerjakan', $service->status === 'dikerjakan' ? null : $service->updated_at),
            $this->stage('quality_control', 'Quality Control', $qc?->status === 'passed', $qc && in_array($qc->status, ['in_progress', 'failed'], true), $qc?->checked_at),
            $this->stage('payment', 'Pembayaran', $paid, $service->transaksi && !$paid, $service->transaksi?->updated_at),
            $this->stage('ready_for_pickup', 'Siap Diambil', $ready, false, $ready ? $service->updated_at : null),
            $this->stage('completed', 'Selesai', false, false, null),
        ];
        $current = collect($stages)->firstWhere('status', 'current')['key'] ?? ($ready ? 'ready_for_pickup' : 'booking_confirmed');
        return ['service_id' => $service->id_servis, 'service_code' => $service->kode_servis, 'current_stage' => $current, 'stages' => $stages, 'pending_approval_count' => $pending, 'qc_status' => $qc?->status ?: 'not_started', 'payment_status' => $service->transaksi?->status ?: 'belum_bayar', 'ready_for_pickup' => $ready];
    }

    private function stage(string $key, string $label, bool $completed, bool $current, $occurredAt): array
    {
        return ['key' => $key, 'label' => $label, 'status' => $current ? 'current' : ($completed ? 'completed' : 'upcoming'), 'occurred_at' => $occurredAt ? Carbon::parse($occurredAt)->toISOString() : null];
    }
}
