<?php

namespace App\Http\Controllers;

use App\Models\{FinancialAdjustment, Pembayaran};
use App\Services\FinancialAdjustmentService;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Throwable;

class FinancialAdjustmentController extends Controller
{
    public function index(Request $request): View
    {
        $items = FinancialAdjustment::with(['payment.transaksi', 'requester', 'approver', 'confirmer'])->latest()->paginate(25)->withQueryString();
        $eligiblePayments = Pembayaran::with(['transaksi.pelanggan'])
            ->where('arah', 'masuk')
            ->where('status', 'terverifikasi')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('pembayaran_gateway as gateway')
                    ->whereColumn('gateway.id_pembayaran', 'pembayaran.id_pembayaran');
            })
            ->latest('id_pembayaran')
            ->limit(50)
            ->get();

        return view('admin.financial-adjustments.index', compact('items', 'eligiblePayments'));
    }

    public function store(Request $request, FinancialAdjustmentService $service): RedirectResponse
    {
        abort_unless(in_array($request->user()->roleKey(), ['finance', 'super_admin'], true), 403);
        $data = $request->validate(['id_pembayaran' => ['required', 'exists:pembayaran,id_pembayaran'], 'amount' => ['required', 'numeric', 'min:0.01'], 'adjustment_type' => ['required', 'in:refund,reversal'], 'reason' => ['required', 'string', 'max:500'], 'evidence_reference' => ['nullable', 'string', 'max:255']]);
        try { $service->request((int) $data['id_pembayaran'], (float) $data['amount'], $data['reason'], (int) $request->user()->petugas?->id_petugas, $data['evidence_reference'] ?? null, $data['adjustment_type']); return back()->with('status', 'Permohonan refund/reversal tercatat dan menunggu review.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function transition(Request $request, FinancialAdjustment $adjustment, FinancialAdjustmentService $service): RedirectResponse
    {
        abort_unless(in_array($request->user()->roleKey(), ['finance', 'super_admin'], true), 403);
        $data = $request->validate(['action' => ['required', 'in:under_review,approved,processing,confirmed,rejected,failed'], 'reason' => ['nullable', 'string', 'max:500'], 'evidence_reference' => ['nullable', 'string', 'max:255']]);
        try { $service->transition($adjustment, $data['action'], (int) $request->user()->petugas?->id_petugas, $data['reason'] ?? null, $data['evidence_reference'] ?? null); return back()->with('status', 'Status financial adjustment diperbarui dan diaudit.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
}
