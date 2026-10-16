<?php

namespace App\Http\Controllers;

use App\Models\{ServiceApprovalRequest, Servis};
use App\Services\{InventoryService, ServiceExecutionService};
use Illuminate\Http\Request;
use Throwable;

class ServiceExecutionController extends Controller
{
    public function storeFinding(Request $request, Servis $servis, ServiceExecutionService $execution, InventoryService $inventory)
    {
        try { $data = $request->validate(['title' => 'required|string|max:160', 'description' => 'required|string', 'recommendation' => 'nullable|string', 'severity' => 'nullable|in:info,warning,critical']); $execution->createFinding($servis->id_servis, $data, (int) $inventory->actorPetugasId($request->user()->id_user)); return back()->with('success', 'Temuan tambahan tersimpan.'); }
        catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); }
    }

    public function storeApproval(Request $request, Servis $servis, ServiceExecutionService $execution, InventoryService $inventory)
    {
        try {
            $data = $request->validate(['id_service_finding' => 'nullable|integer', 'clarification_of_approval_id' => 'nullable|integer', 'title' => 'required|string|max:160', 'reason' => 'required|string', 'items' => 'required|array|min:1', 'items.*.item_type' => 'required|in:labor,sparepart', 'items.*.quantity' => 'required|integer|min:1', 'items.*.id_jenis_servis' => 'nullable|integer', 'items.*.id_sparepart' => 'nullable|integer']);
            $execution->createApprovalRequest($servis->id_servis, $data, (int) $inventory->actorPetugasId($request->user()->id_user));
            return back()->with('success', 'Permintaan persetujuan customer berhasil dikirim.');
        } catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); }
    }

    public function requestApprovalClarification(Request $request, Servis $servis, ServiceApprovalRequest $approval, ServiceExecutionService $execution, InventoryService $inventory)
    {
        abort_unless((int) $approval->id_servis === (int) $servis->id_servis, 404);
        try {
            $data = $request->validate(['reason' => 'required|string|max:1000']);
            $execution->requestApprovalClarification($servis->id_servis, $approval->id_service_approval_request, (int) $inventory->actorPetugasId($request->user()->id_user), $data['reason']);
            return back()->with('success', 'Klarifikasi approval tercatat. Buat persetujuan customer baru dengan item yang sebenarnya.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function startQc(Request $request, Servis $servis, ServiceExecutionService $execution, InventoryService $inventory)
    {
        try { $execution->assertReadyForQualityControl($servis->id_servis); $execution->startQualityControl($servis->id_servis, (int) $inventory->actorPetugasId($request->user()->id_user)); return back()->with('success', 'Quality Control dimulai.'); }
        catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function updateQc(Request $request, Servis $servis, ServiceExecutionService $execution, InventoryService $inventory)
    {
        try { $data = $request->validate(['items' => 'required|array', 'items.*.result' => 'required|in:PASS,FAIL,N/A', 'items.*.note' => 'nullable|string', 'notes' => 'nullable|string']); $execution->updateQualityControl($servis->id_servis, $data['items'], $data['notes'] ?? null, (int) $inventory->actorPetugasId($request->user()->id_user)); return back()->with('success', 'Checklist Quality Control diperbarui.'); }
        catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); }
    }

    public function tracking(Request $request, Servis $servis, ServiceExecutionService $execution)
    {
        abort_unless((int) $servis->id_pelanggan === (int) $request->user()->pelanggan?->id_pelanggan, 403);
        return response()->json($execution->tracking($servis));
    }

    public function approvals(Request $request, Servis $servis)
    {
        abort_unless((int) $servis->id_pelanggan === (int) $request->user()->pelanggan?->id_pelanggan, 403);
        $servis->load(['approvalRequests.items']);
        return response()->json(['service_id' => $servis->id_servis, 'approvals' => $servis->approvalRequests->map(fn ($approval) => ['id' => $approval->id_service_approval_request, 'title' => $approval->title, 'reason' => $approval->reason, 'status' => $approval->status, 'requested_at' => $approval->requested_at?->toISOString(), 'responded_at' => $approval->responded_at?->toISOString(), 'estimated_additional_total' => (float) $approval->items->sum('total_snapshot'), 'items' => $approval->items->map(fn ($item) => ['id' => $item->id_service_approval_request_item, 'type' => $item->item_type, 'description' => $item->description, 'quantity' => (int) $item->quantity, 'unit_price' => (float) $item->unit_price_snapshot, 'total' => (float) $item->total_snapshot])->values()])->values()]);
    }

    public function decide(Request $request, Servis $servis, ServiceApprovalRequest $approval, ServiceExecutionService $execution)
    {
        $data = $request->validate(['decision' => 'required|in:approve,reject', 'note' => 'nullable|string|max:1000']);
        abort_unless((int) $approval->id_servis === (int) $servis->id_servis, 404);
        try { $execution->decide($servis->id_servis, $approval->id_service_approval_request, $data['decision'], $data['note'] ?? null, (int) $request->user()->pelanggan?->id_pelanggan); return response()->json(['message' => 'Keputusan approval tersimpan.', 'status' => $data['decision'] === 'approve' ? 'approved' : 'rejected']); }
        catch (Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }
}
