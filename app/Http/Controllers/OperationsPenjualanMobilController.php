<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePenjualanMobilRequest;
use App\Models\{BookingMobil, Mobil, Pelanggan, PenjualanMobil};
use App\Services\{InventoryService, PenjualanMobilService};
use Illuminate\Http\Request;
use Throwable;

class OperationsPenjualanMobilController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->input('q'));
        $status = $request->input('status');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = PenjualanMobil::with([
            'pelanggan',
            'mobil.foto',
            'transaksi.pembayarans',
            'petugas',
            'booking',
        ]);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword): void {
                $q->where('kode_penjualan', 'like', "%{$keyword}%")
                    ->orWhereHas('pelanggan', function ($pq) use ($keyword): void {
                        $pq->where('nama_lengkap', 'like', "%{$keyword}%")
                            ->orWhere('kode_pelanggan', 'like', "%{$keyword}%")
                            ->orWhere('no_hp', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('mobil', function ($mq) use ($keyword): void {
                        $mq->where('merk', 'like', "%{$keyword}%")
                            ->orWhere('model', 'like', "%{$keyword}%")
                            ->orWhere('kode_mobil', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($status && in_array($status, ['proses', 'selesai', 'batal', 'dibatalkan'], true)) {
            $query->where('status', $status);
        }

        if ($from) {
            $query->whereDate('tanggal_penjualan', '>=', $from);
        }

        if ($to) {
            $query->whereDate('tanggal_penjualan', '<=', $to);
        }

        $rows = $query->latest('tanggal_penjualan')->latest('id_penjualan')->paginate(10)->withQueryString();

        $totalPenjualan = PenjualanMobil::count();
        $unitDiproses = PenjualanMobil::where('status', 'proses')->count();
        $penjualanSelesai = PenjualanMobil::where('status', 'selesai')->count();
        $totalPendapatan = (float) \App\Models\Pembayaran::whereHas('transaksi.penjualanMobil')
            ->where('arah', 'masuk')
            ->where('status', 'terverifikasi')
            ->sum('jumlah');
        $totalNilaiKontrak = (float) PenjualanMobil::sum('total_akhir');
        $verifiedTxCount = \App\Models\Transaksi::whereHas('penjualanMobil')
            ->whereHas('pembayarans', fn($p) => $p->where('arah', 'masuk')->where('status', 'terverifikasi'))
            ->count();

        $currentMonthCount = PenjualanMobil::whereYear('tanggal_penjualan', now()->year)
            ->whereMonth('tanggal_penjualan', now()->month)
            ->count();
        $prevMonthCount = PenjualanMobil::whereYear('tanggal_penjualan', now()->subMonth()->year)
            ->whereMonth('tanggal_penjualan', now()->subMonth()->month)
            ->count();

        $stats = [
            'total' => $totalPenjualan,
            'proses' => $unitDiproses,
            'selesai' => $penjualanSelesai,
            'pendapatan_terverifikasi' => $totalPendapatan,
            'total_nilai_kontrak' => $totalNilaiKontrak,
            'verified_tx_count' => $verifiedTxCount,
            'current_month' => $currentMonthCount,
            'prev_month' => $prevMonthCount,
        ];

        return view('operations.penjualan-mobil.index', [
            'rows' => $rows,
            'stats' => $stats,
            'keyword' => $keyword,
            'status' => $status,
            'from' => $from,
            'to' => $to,
        ]);
    }
    public function create() { return view('operations.penjualan-mobil.create', ['customers'=>Pelanggan::where('status','aktif')->orderBy('nama_lengkap')->get(), 'cars'=>Mobil::whereIn('status',['tersedia','dibooking'])->orderBy('merk')->get(), 'bookings'=>BookingMobil::with(['pelanggan','mobil'])->where('status','dikonfirmasi')->latest()->get(), 'promos'=>\App\Models\PromoMember::visible()->whereIn('berlaku_untuk',['semua','penjualan_mobil'])->get()]); }
    public function store(StorePenjualanMobilRequest $request, PenjualanMobilService $service) { try { $data=$request->validated(); $id=$service->createWithPromo($data, (int)($request->user()->petugas?->id_petugas ?: app(InventoryService::class)->actorPetugasId($request->user()->id_user)), $request->integer('promo_id')?:null)->id_penjualan; return redirect()->route($request->user()->roleName().'.penjualan-mobil.show',$id)->with('success','Penjualan mobil dibuat dan unit dicadangkan.'); } catch(Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); } }
    public function show(PenjualanMobil $penjualan_mobil) { $penjualan_mobil->load(['pelanggan','mobil','booking','petugas','transaksi']); return view('operations.penjualan-mobil.show',['sale'=>$penjualan_mobil]); }
}
