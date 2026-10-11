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
        $query = PesananMobilMember::with(['pelanggan', 'mobil.foto', 'pdi', 'transaksi.pembayarans'])->latest('created_at');
        $keyword = trim((string) $request->input('q'));
        $status = (string) $request->input('status');

        $query->when($keyword !== '', function ($builder) use ($keyword): void {
            $builder->where(function ($search) use ($keyword): void {
                $search->where('nomor_pesanan', 'like', "%{$keyword}%")
                    ->orWhere('nama_pembeli', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_merk', 'like', "%{$keyword}%")
                    ->orWhere('snapshot_model', 'like', "%{$keyword}%");
            });
        });
        $query->when($status !== '', fn ($builder) => $builder->where('status', $status));
        $query->when($request->filled('from'), fn ($builder) => $builder->whereDate('created_at', '>=', $request->date('from')));
        $query->when($request->filled('to'), fn ($builder) => $builder->whereDate('created_at', '<=', $request->date('to')));

        $orders = $query->paginate(10)->withQueryString();
        $stats = [
            'total' => PesananMobilMember::count(),
            'menunggu_verifikasi' => PesananMobilMember::where('status', 'menunggu_verifikasi')->count(),
            'dikonfirmasi' => PesananMobilMember::where('status', 'menunggu_pembayaran')->count(),
            'proses' => PesananMobilMember::whereIn('status', ['lunas', 'pdi_lulus', 'siap_diambil'])->count(),
            'selesai' => PesananMobilMember::where('status', 'diserahkan')->count(),
        ];
        return view('admin.pesanan-mobil.index', compact('orders', 'stats', 'keyword', 'status'));
    }
    public function show(PesananMobilMember $pesanan_mobil): View { $pesanan_mobil->load(['pelanggan', 'mobil', 'transaksi.pembayarans', 'penjualan', 'pdi', 'serahTerima']); return view('admin.pesanan-mobil.show', ['order' => $pesanan_mobil]); }
    public function approve(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->approve($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->input('catatan_admin')); return back()->with('success', 'Pesanan disetujui dan transaksi pembayaran dibuat.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function reject(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->reject($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), (string) $request->input('catatan_admin')); return back()->with('success', 'Pesanan ditolak dan unit dilepas.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function schedule(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service) { $request->validate(['jadwal_pengambilan' => ['required', 'date']]); try { $service->schedule($pesanan_mobil, $request->string('jadwal_pengambilan')->toString()); return back()->with('success', 'Jadwal pengambilan disimpan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function pdi(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { try { $service->savePdi($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); return back()->with('success', 'Hasil PDI disimpan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
    public function handover(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, InventoryService $inventory) { $request->validate(['nama_penerima' => ['required', 'string', 'max:120'], 'nomor_identitas' => ['nullable', 'string', 'max:80']]); try { $service->handover($pesanan_mobil, (int) $inventory->actorPetugasId($request->user()->id_user), $request->all()); return back()->with('success', 'Serah-terima selesai; kendaraan masuk My Garage pelanggan.'); } catch (Throwable $e) { return back()->withErrors($e->getMessage()); } }
}
