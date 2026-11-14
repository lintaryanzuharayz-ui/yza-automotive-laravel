<?php

namespace App\Http\Controllers;

use App\Models\{Mobil, PenjualanMobil, Petugas, PesananMobilMember, SalesLead, SalesTarget, TestDrive};
use App\Services\{SalesAwardsService, SalesManagementService};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Throwable;

class SalesManagementController extends Controller
{
    public function dashboard(Request $request, SalesManagementService $service, SalesAwardsService $awards): View
    {
        $salesId = $request->user()->roleKey() === 'sales' ? $request->user()->petugas?->id_petugas : null;
        $year = (int) now('Asia/Jakarta')->year;
        $ranking = $salesId ? collect($awards->leaderboard($year))->firstWhere('id', $salesId) : null;
        return view('sales.dashboard', ['stats' => $service->dashboard($salesId), 'salesId' => $salesId, 'awardYear' => $year, 'awardRanking' => $ranking]);
    }

    public function awards(Request $request, SalesAwardsService $awards): View
    {
        $year = $awards->year((int) $request->query('year', now('Asia/Jakarta')->year));
        $rows = $awards->leaderboard($year);
        return view('sales.awards', ['year' => $year, 'rows' => $rows, 'me' => collect($rows)->firstWhere('id', $request->user()->petugas?->id_petugas)]);
    }

    public function leads(Request $request, SalesManagementService $service): View
    {
        $supervises = $request->user()->roleKey() === 'super_admin';
        $query = $service->scopeLeads(SalesLead::query()->with(['sales', 'mobil', 'pelanggan']), $request->user()->petugas?->id_petugas, $supervises);
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('q')) $query->where('nama_prospek', 'like', '%'.$request->string('q').'%');
        return view('sales.leads.index', ['leads' => $query->latest()->paginate(15)->withQueryString(), 'statuses' => SalesManagementService::LEAD_STATUSES, 'supervises' => $supervises]);
    }

    public function createLead(Request $request, SalesManagementService $service): View
    {
        return view('sales.leads.create', ['cars' => Mobil::whereIn('status', ['tersedia', 'dibooking'])->orderBy('merk')->get(), 'salesPeople' => $request->user()->roleKey() === 'super_admin' ? $service->salesPeople()->get() : collect()]);
    }

    public function storeLead(Request $request, SalesManagementService $service): RedirectResponse
    {
        $data = $request->validate(['nama_prospek' => ['required', 'string', 'max:120'], 'kontak' => ['nullable', 'string', 'max:120'], 'id_mobil' => ['nullable', 'exists:mobil,id_mobil'], 'id_sales' => ['nullable', 'exists:petugas,id_petugas'], 'sumber' => ['nullable', 'string', 'max:60'], 'status' => ['required', 'in:'.implode(',', SalesManagementService::LEAD_STATUSES)], 'follow_up_at' => ['nullable', 'date'], 'catatan' => ['nullable', 'string', 'max:5000']]);
        $actor = $request->user()->petugas?->id_petugas;
        $salesId = $request->user()->roleKey() === 'sales' ? $actor : ($data['id_sales'] ?? null);
        if (!$salesId) return back()->withInput()->withErrors(['id_sales' => 'Prospek harus ditugaskan kepada sales.']);
        if (!$service->salesPeople()->whereKey($salesId)->exists()) return back()->withInput()->withErrors(['id_sales' => 'Sales tidak aktif atau tidak valid.']);
        $lead = SalesLead::create($data + ['id_sales' => $salesId]);
        return redirect()->route('sales.leads.show', $lead)->with('status', 'Prospek berhasil dibuat.');
    }

    public function showLead(SalesLead $lead): View
    {
        $this->authorizeLead($lead);
        return view('sales.leads.show', ['lead' => $lead->load(['sales', 'mobil', 'pelanggan', 'activities.petugas'])]);
    }

    public function activity(Request $request, SalesLead $lead, SalesManagementService $service): RedirectResponse
    {
        $this->authorizeLead($lead);
        $data = $request->validate(['activity_note' => ['required', 'string', 'max:5000'], 'jenis' => ['required', 'string', 'max:40'], 'status' => ['required', 'in:'.implode(',', SalesManagementService::LEAD_STATUSES)], 'follow_up_at' => ['nullable', 'date']]);
        try { $service->addLeadActivity($lead, (int) $request->user()->petugas?->id_petugas, $data); return back()->with('status', 'Aktivitas prospek tersimpan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function assignLead(Request $request, SalesLead $lead, SalesManagementService $service): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin', 403);
        $data = $request->validate(['id_sales' => ['nullable', 'exists:petugas,id_petugas'], 'reason' => ['nullable', 'string', 'max:500']]);
        try { $service->assign('lead', $lead->id_sales_lead, $data['id_sales'] ?? null, $request->user()->petugas?->id_petugas, $data['reason'] ?? null); return back()->with('status', 'Assignment prospek tercatat.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function orders(Request $request): View
    {
        $query = PesananMobilMember::with(['pelanggan', 'mobil', 'sales', 'penjualan', 'pdi', 'serahTerima']);
        if ($request->user()->roleKey() === 'sales') $query->where('id_sales', $request->user()->petugas?->id_petugas);
        return view('sales.orders', ['orders' => $query->latest('created_at')->paginate(15)->withQueryString()]);
    }

    public function sales(Request $request): View
    {
        $query = PenjualanMobil::with(['pelanggan', 'mobil', 'sales', 'transaksi.pembayarans', 'memberOrder.serahTerima']);
        if ($request->user()->roleKey() === 'sales') $query->where('id_sales', $request->user()->petugas?->id_petugas);
        return view('sales.sales', ['sales' => $query->latest('tanggal_penjualan')->paginate(15)->withQueryString(), 'salesPeople' => $request->user()->roleKey() === 'super_admin' ? app(SalesManagementService::class)->salesPeople()->get() : collect()]);
    }

    public function assignOrder(Request $request, PesananMobilMember $order, SalesManagementService $service): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin', 403);
        $data = $request->validate(['id_sales' => ['nullable', 'exists:petugas,id_petugas'], 'reason' => ['nullable', 'string', 'max:500']]);
        try { $service->assign('order', $order->id_pesanan, $data['id_sales'] ?? null, $request->user()->petugas?->id_petugas, $data['reason'] ?? null); return back()->with('status', 'Sales pesanan diperbarui dan diaudit.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function assignSale(Request $request, PenjualanMobil $sale, SalesManagementService $service): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin', 403);
        $data = $request->validate(['id_sales' => ['nullable', 'exists:petugas,id_petugas'], 'reason' => ['nullable', 'string', 'max:500']]);
        try { $service->assign('sale', $sale->id_penjualan, $data['id_sales'] ?? null, $request->user()->petugas?->id_petugas, $data['reason'] ?? null); return back()->with('status', 'Sales penanggung jawab diperbarui dan diaudit.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function testDrives(Request $request): View
    {
        $query = TestDrive::with(['pelanggan', 'mobil', 'sales', 'petugas']);
        if ($request->user()->roleKey() === 'sales') $query->where('id_sales', $request->user()->petugas?->id_petugas);
        return view('sales.test-drives', ['testDrives' => $query->latest('tanggal')->latest('jam')->paginate(15)->withQueryString()]);
    }

    public function updateTestDrive(Request $request, TestDrive $testDrive): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin' || (int) $testDrive->id_sales === (int) $request->user()->petugas?->id_petugas, 403);
        $data = $request->validate(['hasil' => ['nullable', 'string', 'max:60'], 'catatan_sales' => ['nullable', 'string', 'max:5000'], 'follow_up_at' => ['nullable', 'date']]);
        $testDrive->update($data);
        return back()->with('status', 'Hasil test drive dan follow-up tersimpan.');
    }

    public function assignTestDrive(Request $request, TestDrive $testDrive, SalesManagementService $service): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin', 403);
        $data = $request->validate(['id_sales' => ['nullable', 'exists:petugas,id_petugas'], 'reason' => ['nullable', 'string', 'max:500']]);
        try { $service->assign('test_drive', $testDrive->id_test_drive, $data['id_sales'] ?? null, $request->user()->petugas?->id_petugas, $data['reason'] ?? null); return back()->with('status', 'Sales pendamping test drive diperbarui dan diaudit.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }

    public function targets(Request $request): View
    {
        $query = SalesTarget::with('sales');
        if ($request->user()->roleKey() === 'sales') $query->where('id_sales', $request->user()->petugas?->id_petugas);
        return view('sales.targets', ['targets' => $query->latest('periode_mulai')->paginate(15), 'salesPeople' => $request->user()->roleKey() === 'super_admin' ? app(SalesManagementService::class)->salesPeople()->get() : collect()]);
    }

    public function storeTarget(Request $request): RedirectResponse
    {
        abort_unless($request->user()->roleKey() === 'super_admin', 403);
        $data = $request->validate(['id_sales' => ['required', 'exists:petugas,id_petugas'], 'periode_mulai' => ['required', 'date'], 'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'], 'target_unit' => ['required', 'integer', 'min:0'], 'target_nominal' => ['nullable', 'numeric', 'min:0']]);
        SalesTarget::updateOrCreate(['id_sales' => $data['id_sales'], 'periode_mulai' => $data['periode_mulai'], 'periode_selesai' => $data['periode_selesai']], $data + ['status' => 'aktif']);
        return back()->with('status', 'Target sales tersimpan.');
    }

    private function authorizeLead(SalesLead $lead): void
    {
        if (auth()->user()->roleKey() !== 'super_admin') abort_unless((int) $lead->id_sales === (int) auth()->user()->petugas?->id_petugas, 403);
    }
}
