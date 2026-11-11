<?php

namespace App\Services;

use App\Models\{PenjualanMobil, Petugas, PesananMobilMember, SalesAssignmentAudit, SalesLead, SalesLeadActivity, SalesTarget, TestDrive};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesManagementService
{
    public const LEAD_STATUSES = ['baru', 'dihubungi', 'tertarik', 'test_drive', 'negosiasi', 'pemesanan', 'berhasil', 'tidak_lanjut'];

    public function salesPeople(): Builder
    {
        return Petugas::query()->where('status', 'aktif')->whereHas('user.role', fn ($q) => $q->where('nama_role', 'sales'));
    }

    public function scopeLeads(Builder $query, ?int $salesId, bool $supervises = false): Builder
    {
        return $supervises ? $query : $query->where('id_sales', $salesId);
    }

    public function dashboard(?int $salesId = null): array
    {
        $leadQuery = SalesLead::query();
        if ($salesId) $leadQuery->where('id_sales', $salesId);
        $orderQuery = PesananMobilMember::query();
        if ($salesId) $orderQuery->where('id_sales', $salesId);
        $driveQuery = TestDrive::query();
        if ($salesId) $driveQuery->where('id_sales', $salesId);
        $salesQuery = PenjualanMobil::query();
        if ($salesId) $salesQuery->where('id_sales', $salesId);
        $month = now();
        $successfulOrders = (clone $orderQuery)->where('status', 'diserahkan');
        $target = $salesId ? SalesTarget::where('id_sales', $salesId)->where('status', 'aktif')->whereDate('periode_mulai', '<=', $month)->whereDate('periode_selesai', '>=', $month)->first() : null;
        $realizedUnits = (clone $successfulOrders)->count();
        $realizedAmount = (float) (clone $successfulOrders)->sum('total_tagihan');

        return [
            'active_leads' => (clone $leadQuery)->whereNotIn('status', ['berhasil', 'tidak_lanjut'])->count(),
            'new_leads_month' => (clone $leadQuery)->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->count(),
            'test_drives_scheduled' => (clone $driveQuery)->whereIn('status', ['menunggu', 'disetujui'])->count(),
            'test_drives_completed' => (clone $driveQuery)->where('status', 'selesai')->count(),
            'orders_handled' => (clone $orderQuery)->count(),
            'successful_sales' => $realizedUnits,
            'units_handed_over' => $realizedUnits,
            'target' => $target,
            'realized_units' => $realizedUnits,
            'realized_amount' => $realizedAmount,
            'target_percentage' => $target && (int) $target->target_unit > 0 ? round($realizedUnits / $target->target_unit * 100, 1) : 0,
            'recent_leads' => (clone $leadQuery)->with(['sales', 'mobil'])->latest()->limit(8)->get(),
            'recent_orders' => (clone $orderQuery)->with(['mobil', 'sales', 'pelanggan'])->latest('created_at')->limit(8)->get(),
        ];
    }

    public function assign(string $entityType, int $entityId, ?int $newSalesId, ?int $actorPetugasId, ?string $reason = null): void
    {
        if ($newSalesId && !$this->salesPeople()->whereKey($newSalesId)->exists()) throw new RuntimeException('Petugas yang dipilih bukan sales aktif.');
        DB::transaction(function () use ($entityType, $entityId, $newSalesId, $actorPetugasId, $reason): void {
            $model = match ($entityType) {
                'lead' => SalesLead::lockForUpdate()->findOrFail($entityId),
                'order' => PesananMobilMember::lockForUpdate()->findOrFail($entityId),
                'sale' => PenjualanMobil::lockForUpdate()->findOrFail($entityId),
                'test_drive' => TestDrive::lockForUpdate()->findOrFail($entityId),
                default => throw new RuntimeException('Jenis assignment tidak valid.'),
            };
            $column = $entityType === 'lead' ? 'id_sales' : 'id_sales';
            $previous = $model->{$column};
            if ((int) $previous === (int) $newSalesId) return;
            $model->forceFill([$column => $newSalesId])->save();
            SalesAssignmentAudit::create(['entity_type' => $entityType, 'entity_id' => $entityId, 'previous_sales_id' => $previous, 'new_sales_id' => $newSalesId, 'actor_petugas_id' => $actorPetugasId, 'reason' => $reason]);
        });
    }

    public function addLeadActivity(SalesLead $lead, int $actorPetugasId, array $data): SalesLeadActivity
    {
        return DB::transaction(function () use ($lead, $actorPetugasId, $data): SalesLeadActivity {
            if ((int) $lead->id_sales !== $actorPetugasId && !auth()->user()?->hasPermission('sales.manage')) throw new RuntimeException('Prospek bukan tanggung jawab sales ini.');
            $lead->update(array_filter(['status' => $data['status'] ?? null, 'follow_up_at' => $data['follow_up_at'] ?? null, 'catatan' => $data['catatan'] ?? null], fn ($v) => $v !== null));
            return SalesLeadActivity::create(['id_sales_lead' => $lead->id_sales_lead, 'id_petugas' => $actorPetugasId, 'jenis' => $data['jenis'] ?? 'catatan', 'catatan' => $data['activity_note'], 'follow_up_at' => $data['follow_up_at'] ?? null]);
        });
    }
}
