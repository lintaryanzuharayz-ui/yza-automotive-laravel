<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PesananMobilMember;
use App\Services\{InventoryService, MemberVehiclePurchaseService};
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MemberVehiclePurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $query = PesananMobilMember::with(['pelanggan', 'mobil.foto', 'pdi', 'transaksi.pembayarans', 'penjualan'])->latest('created_at');
        $keyword = trim((string) $request->input('q'));
        $status = (string) $request->input('status');
        $from = (string) $request->input('from');
        $to = (string) $request->input('to');

        $query->when($keyword !== '', function ($builder) use ($keyword): void {
            $builder->where(function ($search) use ($keyword): void {
                $search->where('nomor_pesanan', 'like', "%{$keyword}%")
                    ->orWhere('nama_pembeli', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_merk', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_model', 'like', "%{$keyword}%")
                    ->orWhereHas('penjualan', function ($q) use ($keyword) {
                        $q->where('kode_penjualan', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('pelanggan', function ($q) use ($keyword) {
                        $q->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('kode_pelanggan', 'like', "%{$keyword}%")
                            ->orWhere('no_hp', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('mobil', function ($q) use ($keyword) {
                        $q->where('kode_mobil', 'like', "%{$keyword}%");
                    });
            });
        });

        $query->when($status !== '', function ($builder) use ($status) {
            if ($status === 'proses') {
                $builder->whereIn('status', ['lunas', 'pdi_lulus', 'siap_diambil']);
            } elseif ($status === 'dikonfirmasi') {
                $builder->where('status', 'menunggu_pembayaran');
            } else {
                $builder->where('status', $status);
            }
        });

        $query->when($from !== '', fn ($builder) => $builder->whereDate('created_at', '>=', $from));
        $query->when($to !== '', fn ($builder) => $builder->whereDate('created_at', '<=', $to));

        $orders = $query->paginate(10)->withQueryString();
        $stats = [
            'total' => PesananMobilMember::count(),
            'semua' => PesananMobilMember::count(),
            'menunggu_verifikasi' => PesananMobilMember::where('status', 'menunggu_verifikasi')->count(),
            'dikonfirmasi' => PesananMobilMember::where('status', 'menunggu_pembayaran')->count(),
            'proses' => PesananMobilMember::whereIn('status', ['lunas', 'pdi_lulus', 'siap_diambil'])->count(),
            'selesai' => PesananMobilMember::where('status', 'diserahkan')->count(),
        ];

        $detailOrder = null;
        if ($request->filled('detail')) {
            $detailOrder = PesananMobilMember::with(['pelanggan', 'mobil.foto', 'transaksi.pembayarans', 'penjualan', 'pdi', 'serahTerima'])->find($request->input('detail'));
        }

        $checklistDefinition = app(MemberVehiclePurchaseService::class)->checklistDefinition();

        return view('admin.pesanan-mobil.index', compact('orders', 'stats', 'keyword', 'status', 'from', 'to', 'detailOrder', 'checklistDefinition'));
    }
    public function show(PesananMobilMember $pesanan_mobil) {
        return redirect()->route('admin.pesanan-mobil.index', ['detail' => $pesanan_mobil->id_pesanan]);
    }
    public function approve(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->approve($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->input('catatan_admin')); return back()->with('success', 'Pesanan disetujui dan transaksi pembayaran dibuat.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function reject(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->reject($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), (string) $request->input('catatan_admin')); return back()->with('success', 'Pesanan ditolak dan unit dilepas.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function schedule(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service) { $data = $request->validate(['jadwal_pengambilan' => ['required', 'date'], 'lokasi_pengambilan' => ['required', 'string', 'max:180'], 'catatan_pengambilan' => ['nullable', 'string', 'max:1000']]); try { $service->schedule($pesanan_mobil, $data['jadwal_pengambilan'], $data['lokasi_pengambilan'], $data['catatan_pengambilan'] ?? null); return back()->with('success', 'Jadwal pengambilan disimpan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function startPdi(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->startPdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user)); return back()->with('success', 'PDI dimulai.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function pdi(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { if ($request->has('checklist')) $service->completePdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); else $service->savePdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); return back()->with('success', 'Hasil PDI disimpan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function handover(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { $request->validate(['nama_penerima' => ['required', 'string', 'max:120'], 'nomor_identitas' => ['nullable', 'string', 'max:80']]); try { $service->handover($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); return back()->with('success', 'Serah-terima selesai; kendaraan masuk My Garage pelanggan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
}
