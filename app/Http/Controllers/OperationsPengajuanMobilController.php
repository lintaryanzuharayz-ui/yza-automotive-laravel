<?php

namespace App\Http\Controllers;

use App\Http\Requests\{SaveInspeksiMobilRequest, StoreOfferMobilRequest};
use App\Models\{PengajuanMobil, TradeIn};
use App\Services\{BookingMobilService, PengajuanMobilService};
use Illuminate\Http\Request;
use Throwable;

class OperationsPengajuanMobilController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ['diajukan', 'menunggu_inspeksi', 'dalam_inspeksi', 'penawaran_diberikan', 'disetujui', 'ditolak', 'selesai'];
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $brand = trim((string) $request->input('brand', ''));
        $date = (string) $request->input('date', '');
        $period = $request->input('period', '30') === 'all' ? 'all' : '30';
        $perPage = in_array((int) $request->input('perPage', 10), [10, 20, 50], true) ? (int) $request->input('perPage', 10) : 10;

        $query = PengajuanMobil::with(['pelanggan', 'inspeksi'])->latest('created_at');
        if ($status !== '' && in_array($status, $statuses, true)) {
            $query->where('status', $status);
        }
        if ($brand !== '') {
            $query->whereRaw('LOWER(TRIM(merk)) = ?', [strtolower($brand)]);
        }
        if ($date !== '') {
            $query->whereDate('created_at', $date);
        }
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('kode_pengajuan', 'like', "%{$search}%")
                    ->orWhere('merk', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('nomor_polisi', 'like', "%{$search}%")
                    ->orWhereHas('pelanggan', fn ($customer) => $customer->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $rows = $query->paginate($perPage)->withQueryString();
        $count = fn ($statuses) => PengajuanMobil::whereIn('status', $statuses)->count();
        $stats = [
            'total' => PengajuanMobil::count(),
            'waiting' => $count(['diajukan', 'menunggu_inspeksi']),
            'inspection' => $count(['menunggu_inspeksi', 'dalam_inspeksi']),
            'offers' => PengajuanMobil::where('status', 'penawaran_diberikan')->count(),
            'completed' => PengajuanMobil::where('status', 'selesai')->count(),
        ];

        $brandQuery = PengajuanMobil::query()->whereNotNull('merk')->where('merk', '<>', '');
        if ($period === '30') {
            $brandQuery->where('created_at', '>=', now()->subDays(30));
        }
        $brands = $brandQuery->selectRaw('LOWER(TRIM(merk)) as brand, COUNT(*) as total')
            ->groupByRaw('LOWER(TRIM(merk))')->orderByDesc('total')->get();
        $brandTotal = max(1, (int) $brands->sum('total'));
        $tradeIns = TradeIn::count();
        $operational = [
            'today' => PengajuanMobil::whereDate('created_at', today())->count(),
            'reviewToday' => PengajuanMobil::whereDate('created_at', today())->whereIn('status', ['diajukan', 'menunggu_inspeksi'])->count(),
            'averageEstimate' => PengajuanMobil::whereNotNull('harga_harapan')->avg('harga_harapan'),
            'conversion' => $tradeIns > 0 ? (int) round(TradeIn::where('status', 'selesai')->count() / $tradeIns * 100) : null,
        ];

        return view('operations.pengajuan-mobil.index', compact('rows', 'search', 'status', 'brand', 'date', 'period', 'perPage', 'statuses', 'stats', 'brands', 'brandTotal', 'operational'));
    }

    public function show(PengajuanMobil $pengajuan_mobil){$pengajuan_mobil->load(['pelanggan','kendaraan','inspeksi.petugas']);return view('operations.pengajuan-mobil.show',['submission'=>$pengajuan_mobil]);}
    public function process(PengajuanMobil $p,PengajuanMobilService $svc){try{$svc->process($p->id_pengajuan);return back()->with('success','Pengajuan masuk antrean inspeksi.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
    public function start(Request $r,PengajuanMobil $p,PengajuanMobilService $svc){try{$svc->startInspection($p->id_pengajuan,(int)(app(BookingMobilService::class)->actorPetugasId($r->user()->id_user)),(int)$r->validate(['kilometer'=>'required|integer|min:0'])['kilometer']);return back()->with('success','Inspeksi dimulai.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
    public function inspection(SaveInspeksiMobilRequest $r,PengajuanMobil $p,PengajuanMobilService $svc){try{$svc->saveInspection($p->id_pengajuan,$r->validated(),(int)app(BookingMobilService::class)->actorPetugasId($r->user()->id_user));return back()->with('success','Hasil inspeksi disimpan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
    public function offer(StoreOfferMobilRequest $r,PengajuanMobil $p,PengajuanMobilService $svc){try{$svc->offer($p->id_pengajuan,(string)$r->validated()['harga_penawaran']);return back()->with('success','Penawaran tersimpan.');}catch(Throwable $e){return back()->withErrors($e->getMessage());}}
}
