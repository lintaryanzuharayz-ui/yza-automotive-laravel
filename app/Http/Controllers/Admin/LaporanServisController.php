<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{JenisServis, Servis};
use Illuminate\Http\Request;

class LaporanServisController extends Controller
{
    public function index(Request $request)
    {
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

        $all = (clone $query)->get();
        $rows = $query->paginate(20)->withQueryString();
        $summary = [
            'total' => $all->count(),
            'selesai' => $all->where('status', 'selesai')->count(),
            'pending' => $all->whereIn('status', ['pemeriksaan', 'dikerjakan', 'menunggu_bayar', 'belum_bayar', 'dp'])->count(),
            'revenue' => $all->sum(fn ($service) => (float) ($service->transaksi?->total ?? $service->total_biaya ?? 0)),
            'average' => $all->count() ? $all->avg(fn ($service) => (float) ($service->transaksi?->total ?? $service->total_biaya ?? 0)) : 0,
        ];

        return view('admin.reports.servis', [
            'rows' => $rows,
            'summary' => $summary,
            'types' => JenisServis::orderBy('nama_servis')->get(),
        ]);
    }
}
