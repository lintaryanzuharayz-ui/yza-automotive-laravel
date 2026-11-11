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
            'mobil.warnaPilihan.galeri',
            'memberOrders',
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
        $presentation = app(\App\Services\SaleVehiclePresentationService::class);
        $rows->getCollection()->each(fn ($sale) => $sale->setAttribute('vehicle_presentation', $presentation->for($sale)));

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
    public function create(Request $request)
    {
        $orders = \App\Models\PesananMobilMember::with(['penjualan', 'pelanggan', 'transaksi', 'mobil'])
            ->whereIn('status', ['menunggu_pembayaran', 'lunas', 'pdi_lulus', 'siap_diambil'])
            ->whereHas('penjualan', fn ($q) => $q->where('status', 'proses'))
            ->whereHas('transaksi', fn ($q) => $q->where('status', '<>', 'dibatalkan'))
            ->latest()->get();
        if ($request->filled('id_pesanan')) {
            $request->validate(['id_pesanan' => ['required', 'integer']]);
            $order = $orders->firstWhere('id_pesanan', $request->integer('id_pesanan'));
            abort_unless($order && $order->matchesCatalogVehicle() && $order->penjualan && $order->id_transaksi
                && $order->transaksi->jenis_transaksi === 'penjualan_mobil'
                && (int) $order->transaksi->id_pelanggan === (int) $order->id_pelanggan
                && (int) $order->id_transaksi === (int) $order->penjualan->id_transaksi
                && (int) $order->id_mobil === (int) $order->penjualan->id_mobil
                && (int) $order->id_pelanggan === (int) $order->penjualan->id_pelanggan, 422, 'Pesanan belum memenuhi syarat atau relasi transaksi tidak sesuai.');
            $prefix = $request->routeIs('admin.*') ? 'admin' : 'petugas';
            return redirect()->route($prefix.'.penjualan-mobil.show', $order->id_penjualan);
        }
        $submissionKey = (string) \Illuminate\Support\Str::uuid();
        $submissions = $request->session()->get('car_sale_submissions', []);
        $submissions[$submissionKey] = ['user' => $request->user()->id_user, 'sale' => null,
            'code' => app(PenjualanMobilService::class)->newSaleCode()];
        $request->session()->put('car_sale_submissions', array_slice($submissions, -20, null, true));
        return view('operations.penjualan-mobil.create', [
            'submissionKey' => $submissionKey,
            'sourceOrders' => $orders,
            'customers' => Pelanggan::where('status', 'aktif')->orderBy('nama_lengkap')->get(),
            'cars' => Mobil::with('foto')->whereIn('status', ['tersedia', 'dibooking'])->orderBy('merk')->get(),
            'bookings' => BookingMobil::with(['pelanggan', 'mobil'])->where('status', 'dikonfirmasi')->latest()->get(),
            'promos' => \App\Models\PromoMember::visible()->whereIn('berlaku_untuk', ['semua', 'penjualan_mobil'])->get(),
        ]);
    }

    public function promoPreview(Request $request, \App\Services\PromoEngineService $engine)
    {
        $data = $request->validate([
            'id_pelanggan' => ['required', 'integer', 'exists:pelanggan,id_pelanggan'],
            'id_mobil' => ['required', 'integer', 'exists:mobil,id_mobil'],
            'promo_id' => ['required', 'integer', 'exists:promo_member,id_promo'],
        ]);
        try {
            $customer = Pelanggan::where('status', 'aktif')->findOrFail($data['id_pelanggan']);
            app(\App\Services\ActiveMemberCustomerService::class)->assertEligibleLocked($customer);
            $car = Mobil::findOrFail($data['id_mobil']);
            return response()->json($engine->calculate(\App\Models\PromoMember::findOrFail($data['promo_id']),
                (string) $car->harga_jual, 'penjualan_mobil', $customer))->header('Cache-Control', 'private, no-store');
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function store(StorePenjualanMobilRequest $request, PenjualanMobilService $service)
    {
        $data = $request->validated();
        $prefix = $request->routeIs('admin.*') ? 'admin' : 'petugas';
        $order = \App\Models\PesananMobilMember::with(['penjualan', 'pelanggan', 'mobil'])->findOrFail($data['id_pesanan']);

        return redirect()->route($prefix.'.penjualan-mobil.show', $order->id_penjualan)
            ->with('success', 'Transaksi penjualan dari pesanan SPK telah dibuka.');
    }
    public function show(PenjualanMobil $penjualan_mobil) { $penjualan_mobil->load(['pelanggan','mobil.foto','mobil.warnaPilihan.galeri','memberOrders','booking','petugas','transaksi']); return view('operations.penjualan-mobil.show',['sale'=>$penjualan_mobil,'vehicle'=>app(\App\Services\SaleVehiclePresentationService::class)->for($penjualan_mobil)]); }
}
