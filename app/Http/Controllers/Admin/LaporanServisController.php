<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{JenisServis, Servis};
use App\Services\ServiceStatusPresenter;
use Illuminate\Http\Request;

class LaporanServisController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'string', 'max:30'],
            'service_type' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $query = Servis::with(['pelanggan', 'kendaraan', 'bookingServis.jenisServis', 'transaksi'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('tanggal_masuk', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('tanggal_masuk', '<=', $request->date('to')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('service_type'), fn ($q) => $q->whereHas('bookingServis', fn ($b) => $b->where('id_jenis_servis', $request->integer('service_type'))))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($nested) use ($term): void {
                    $nested->where('kode_servis', 'like', $term)
                        ->orWhereHas('pelanggan', fn ($p) => $p->where('nama_lengkap', 'like', $term))
                        ->orWhereHas('kendaraan', fn ($v) => $v->where('nomor_polisi', 'like', $term)->orWhere('model', 'like', $term));
                });
            })
            ->latest('tanggal_masuk');

        $all = (clone $query)->with('transaksi.pembayarans')->get();
        $rows = $query->paginate(20)->withQueryString();
        $transactionRows = $all->filter(fn ($service) => $service->transaksi !== null);
        $verifiedRevenue = $all->sum(fn ($service) => $service->transaksi?->pembayarans
            ?->where('arah', 'masuk')->where('status', 'terverifikasi')->sum('jumlah') ?? 0);
        $distribution = $all->groupBy(fn ($service) => $service->bookingServis?->jenisServis?->nama_servis ?: 'Lainnya')
            ->map(fn ($items, $label) => ['label' => $label, 'units' => $items->count()])
            ->sortByDesc('units')->values();
        $durationRows = $all->filter(fn ($service) => $service->tanggal_masuk && $service->tanggal_selesai);
        $repeatCustomers = $all->filter(fn ($service) => $service->id_pelanggan !== null)->groupBy('id_pelanggan')->filter(fn ($items) => $items->count() > 1)->count();
        $today = now('Asia/Jakarta')->toDateString();
        $summary = [
            'total' => $all->count(),
            'selesai' => $all->where('status', 'selesai')->count(),
            'in_process' => $all->whereIn('status', ['pemeriksaan', 'dikerjakan'])->count(),
            'revenue' => (float) $verifiedRevenue,
            'average' => $transactionRows->count() ? (float) $transactionRows->avg(fn ($service) => (float) $service->transaksi->total) : null,
        ];

        return view('admin.reports.servis', [
            'rows' => $rows,
            'summary' => $summary,
            'types' => JenisServis::orderBy('nama_servis')->get(),
            'distribution' => $distribution,
            'operational' => [
                'today' => $all->where('tanggal_masuk', '>=', now('Asia/Jakarta')->startOfDay())->where('tanggal_masuk', '<=', now('Asia/Jakarta')->endOfDay())->count(),
                'completed_today' => $all->where('status', 'selesai')->filter(fn ($service) => $service->tanggal_selesai?->toDateString() === $today)->count(),
                'average_duration' => $durationRows->count() ? $durationRows->avg(fn ($service) => $service->tanggal_masuk->diffInMinutes($service->tanggal_selesai) / 60) : null,
                'repeat_customers' => $repeatCustomers,
            ],
            'statusLabels' => collect(['menunggu', 'diterima', 'diproses', 'pemeriksaan', 'dikerjakan', 'menunggu_bayar', 'belum_bayar', 'dp', 'selesai', 'ditolak', 'dibatalkan'])->mapWithKeys(fn ($status) => [$status => ServiceStatusPresenter::label($status)]),
        ]);
    }
}
