<?php

namespace App\Services;

use App\Models\{DetailServis, Servis, ServisSparepart, ServiceApprovalRequest};

/**
 * Resolves the origin of existing execution rows without rewriting historical data.
 * Rows created before source metadata existed remain explicitly reviewable as LEGACY.
 */
class WorkOrderSourceService
{
    public const CUSTOMER_REQUEST = 'CUSTOMER_REQUEST';
    public const TECHNICIAN_RECOMMENDATION = 'TECHNICIAN_RECOMMENDATION';
    public const LEGACY = 'LEGACY';

    public function detail(Servis $service, DetailServis $detail): array
    {
        $bookingType = $service->bookingServis?->id_jenis_servis;
        if ($bookingType && (int) $bookingType === (int) $detail->id_jenis_servis) {
            return ['key' => self::CUSTOMER_REQUEST, 'label' => 'Permintaan Pelanggan', 'review' => false];
        }

        $approval = $service->approvalRequests
            ->flatMap(fn ($request) => $request->items)
            ->first(fn ($item) => (int) $item->applied_detail_id === (int) $detail->id_detail_servis);
        if ($approval) {
            return ['key' => self::TECHNICIAN_RECOMMENDATION, 'label' => 'Rekomendasi Disetujui', 'review' => false, 'approval_item_id' => $approval->id_service_approval_request_item];
        }

        return ['key' => self::LEGACY, 'label' => 'Legacy — Perlu Klarifikasi', 'review' => true];
    }

    public function sparepart(Servis $service, ServisSparepart $item): array
    {
        $approval = $service->approvalRequests
            ->flatMap(fn ($request) => $request->items)
            ->first(fn ($approvalItem) => (int) $approvalItem->applied_sparepart_id === (int) $item->id_servis_sparepart);
        if ($approval) {
            return [
                'key' => $approval->approval?->status === 'approved' ? self::TECHNICIAN_RECOMMENDATION : self::LEGACY,
                'label' => $approval->approval?->status === 'approved' ? 'Rekomendasi Disetujui' : 'Legacy — Perlu Klarifikasi',
                'review' => $approval->approval?->status !== 'approved',
                'approval_item_id' => $approval->id_service_approval_request_item,
            ];
        }

        return ['key' => self::LEGACY, 'label' => 'Legacy — Perlu Klarifikasi', 'review' => true];
    }

    public function approvalItems(Servis $service)
    {
        return $service->approvalRequests->flatMap(fn ($request) => $request->items->map(function ($item) use ($request) {
            $item->setRelation('approval', $request);
            return $item;
        }));
    }

    /**
     * Flags a decided approval whose linked finding does not describe the
     * approved snapshot item. This is a review signal only; it never creates
     * or rewrites a relationship based on a name match.
     */
    public function approvalNeedsClarification(ServiceApprovalRequest $approval): bool
    {
        if ($approval->status !== 'approved' || !$approval->relationLoaded('finding') || !$approval->finding) {
            return false;
        }

        $scopeText = strtolower(trim(implode(' ', array_filter([
            $approval->finding->title,
            $approval->finding->recommendation,
        ]))));
        if ($scopeText === '') return false;

        foreach ($approval->items as $item) {
            if ($item->item_type !== 'sparepart') continue;
            $description = strtolower((string) $item->description);
            $tokens = collect(preg_split('/[^a-z0-9]+/i', $description) ?: [])
                ->filter(fn ($token) => strlen($token) >= 4)
                ->values();
            $meaningful = $tokens->take(2);
            if ($meaningful->isNotEmpty() && $meaningful->contains(fn ($token) => !str_contains($scopeText, $token))) {
                return true;
            }
        }

        return false;
    }
}
