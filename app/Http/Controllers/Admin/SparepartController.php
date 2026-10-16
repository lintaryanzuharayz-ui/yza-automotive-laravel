<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSparepartRequest;
use App\Http\Requests\StoreSparepartFitmentRequest;
use App\Http\Requests\UpdateSparepartRequest;
use App\Models\KategoriSparepart;
use App\Models\Sparepart;
use App\Models\SparepartFitment;
use App\Models\Supplier;
use App\Models\KendaraanPelanggan;
use App\Models\Servis;
use App\Services\InventoryService;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SparepartController extends Controller
{
    public function index(): View {
        $stats = [
            'total_jenis' => Sparepart::count(),
            'total_stok' => (int) Sparepart::sum('stok'),
            'stok_menipis' => Sparepart::where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum')->count(),
            'stok_habis' => Sparepart::where('stok', 0)->count(),
            'barang_masuk' => (int) \App\Models\RiwayatStok::where('jenis_transaksi', 'stok_masuk')->sum('jumlah_perubahan'),
            'barang_servis' => (int) abs(\App\Models\RiwayatStok::where('jenis_transaksi', 'servis')->sum('jumlah_perubahan')),
            'fitment_review' => Sparepart::whereDoesntHave('fitments', fn ($q) => $q->where('status_verifikasi', 'verified_fit'))->count(),
        ];

        return view('admin.sparepart.index', [
            'items' => Sparepart::with(['kategori', 'supplier', 'fitments'])
                ->when(request('q'), fn($q, $v) => $q->where(fn($x) => $x->where('kode_sparepart','like','%'.$v.'%')->orWhere('nama_sparepart','like','%'.$v.'%')->orWhere('merk','like','%'.$v.'%')))
                ->when(request('kategori'), fn($q, $v) => $q->where('id_kategori', $v))
                ->when(request('status'), fn($q, $v) => $q->where('status', $v))
                ->when(request('stok') === 'aman', fn($q) => $q->whereColumn('stok', '>', 'stok_minimum'))
                ->when(request('stok') === 'menipis', fn($q) => $q->where('stok', '>', 0)->whereColumn('stok', '<=', 'stok_minimum'))
                ->when(request('stok') === 'habis', fn($q) => $q->where('stok', 0))
                ->when(request('compatibility') === 'with_fitment', fn($q) => $q->whereHas('fitments'))
                ->when(request('compatibility') === 'without_fitment', fn($q) => $q->whereDoesntHave('fitments'))
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString(),
            'categories' => KategoriSparepart::where('status', 'aktif')->orderBy('nama_kategori')->get(),
            'suppliers' => \App\Models\Supplier::where('status', 'aktif')->orderBy('nama_supplier')->get(),
            'stats' => $stats,
        ]);
    }
    public function create(): View { return view('admin.sparepart.create', ['item' => new Sparepart(['harga_beli' => 0, 'harga_jual' => 0, 'stok_minimum' => 0, 'satuan' => 'pcs']), 'categories' => KategoriSparepart::where('status','aktif')->orderBy('nama_kategori')->get(), 'suppliers' => Supplier::where('status','aktif')->orderBy('nama_supplier')->get()]); }
    public function store(StoreSparepartRequest $request, MasterAuditService $audit): RedirectResponse { $data = $request->validated(); unset($data['foto']); $data['status'] = 'aktif'; $data['harga_diperbarui_at'] = now(); $item = Sparepart::create($data); $this->storePhoto($item, $request->file('foto')); $audit->record('create', 'sparepart', $item->id_sparepart, 'Menambahkan spare part'); return redirect()->route('admin.sparepart.show', $item)->with('success', 'Spare part berhasil ditambahkan.'); }
    public function show(Sparepart $sparepart, Request $request): View {
        $sparepart->load(['kategori', 'supplier', 'fitments.verifier']);
        $stockHistory = $sparepart->stockHistory()->with('petugas')->latest('tanggal')->paginate(20);
        $workOrder = null;
        $vehicleContext = null;
        if ($request->filled('work_order')) {
            $workOrder = Servis::with(['kendaraan', 'pelanggan'])->findOrFail($request->integer('work_order'));
            abort_unless($workOrder->kendaraan, 422, 'Work Order tidak memiliki kendaraan yang valid.');
            $vehicleContext = $workOrder->kendaraan;
        }
        $vehicleCatalog = KendaraanPelanggan::query()
            ->where('status', 'aktif')
            ->select(['id_kendaraan_pelanggan', 'merk', 'model', 'tipe', 'tahun', 'nomor_polisi', 'nomor_mesin'])
            ->orderBy('merk')->orderBy('model')->orderByDesc('tahun')->get();
        return view('admin.sparepart.show', compact('sparepart', 'stockHistory', 'workOrder', 'vehicleContext', 'vehicleCatalog') + ['item' => $sparepart]);
    }
    public function edit(Sparepart $sparepart): View { return view('admin.sparepart.edit', ['item' => $sparepart, 'categories' => KategoriSparepart::where('status','aktif')->orWhere('id_kategori',$sparepart->id_kategori)->orderBy('nama_kategori')->get(), 'suppliers' => Supplier::where('status','aktif')->orWhere('id_supplier',$sparepart->id_supplier)->orderBy('nama_supplier')->get()]); }
    public function update(UpdateSparepartRequest $request, Sparepart $sparepart, MasterAuditService $audit): RedirectResponse { $data = $request->validated(); unset($data['foto']); $data['harga_diperbarui_at'] = now(); $sparepart->update($data); $this->storePhoto($sparepart, $request->file('foto')); $audit->record('update', 'sparepart', $sparepart->id_sparepart, 'Mengubah master tanpa mengubah stok'); return redirect()->route('admin.sparepart.show', $sparepart)->with('success', 'Spare part berhasil diperbarui.'); }
    public function storeFitment(StoreSparepartFitmentRequest $request, Sparepart $sparepart, MasterAuditService $audit, InventoryService $inventory): RedirectResponse {
        $data = $request->validated();
        $vehicle = null;
        if ($request->filled('work_order')) {
            $workOrder = Servis::with('kendaraan')->findOrFail($request->integer('work_order'));
            $vehicle = $workOrder->kendaraan;
            abort_unless($vehicle, 422, 'Work Order tidak memiliki kendaraan yang valid.');
        } elseif (!empty($data['id_kendaraan_pelanggan'])) {
            $vehicle = KendaraanPelanggan::whereKey($data['id_kendaraan_pelanggan'])->where('status', 'aktif')->firstOrFail();
        }
        if ($vehicle) {
            $data['merk_kendaraan'] = $vehicle->merk;
            $data['model_kendaraan'] = $vehicle->model;
            $data['tahun_mulai'] = $vehicle->tahun;
            $data['tahun_akhir'] = $vehicle->tahun;
            $data['varian'] = $vehicle->tipe;
            unset($data['id_kendaraan_pelanggan']);
        }
        $duplicate = $sparepart->fitments()->where('merk_kendaraan',$data['merk_kendaraan'] ?? null)->where('model_kendaraan',$data['model_kendaraan'] ?? null)->where('tahun_mulai',$data['tahun_mulai'] ?? null)->where('tahun_akhir',$data['tahun_akhir'] ?? null)->where('varian',$data['varian'] ?? null)->where('kode_mesin',$data['kode_mesin'] ?? null)->where('status_verifikasi',$data['status_verifikasi'])->exists();
        if ($duplicate) return back()->withInput()->withErrors(['fitment' => 'Fitment yang sama sudah terdaftar untuk sparepart ini.']);
        $status = $data['status_verifikasi'];
        unset($data['work_order'], $data['id_kendaraan_pelanggan']);
        $fitment = $sparepart->fitments()->create($data + ['verified_by' => $status === 'unverified' ? null : $inventory->actorPetugasId($request->user()?->id_user), 'verified_at' => $status === 'unverified' ? null : now()]);
        $audit->record('create', 'sparepart_fitment', $fitment->id_sparepart_fitment, 'Menambahkan verifikasi kompatibilitas sparepart');
        return back()->with('success', 'Data kompatibilitas sparepart berhasil disimpan.');
    }
    public function search(Request $request, \App\Models\Servis $servis): JsonResponse { $term = trim((string) $request->query('q', '')); if (mb_strlen($term) < 2) return response()->json(['vehicle' => $servis->kendaraan?->only(['merk','model','tahun']), 'results' => []]); $parts = Sparepart::with('fitments')->where('status','aktif')->where(function($query) use ($term) { $query->where('nama_sparepart','like','%'.$term.'%')->orWhere('kode_sparepart','like','%'.$term.'%')->orWhere('merk','like','%'.$term.'%'); })->limit(30)->get(); $fitment = app(\App\Services\SparepartFitmentService::class); $results = $parts->map(function($part) use ($fitment, $servis) { $state = $fitment->statusFor($part, $servis->kendaraan); return ['id' => $part->id_sparepart, 'name' => $part->nama_sparepart, 'sku' => $part->kode_sparepart, 'stock' => (int) $part->stok, 'price' => (float) $part->harga_jual, 'status' => $state['status'], 'label' => $state['status'] === 'verified_fit' ? 'KOMPATIBEL TERVERIFIKASI' : ($state['status'] === 'verified_not_fit' ? 'TIDAK KOMPATIBEL' : 'KOMPATIBILITAS BELUM TERVERIFIKASI'), 'can_select' => $state['status'] === 'verified_fit' && (int) $part->stok > 0, 'manage_url' => request()->is('admin/*') ? route('admin.sparepart.show', $part).'?work_order='.$servis->id_servis : null]; })->sortBy(fn($part) => match($part['status']) { 'verified_fit' => 0, 'unverified' => 1, default => 2 })->values(); return response()->json(['vehicle' => $servis->kendaraan?->only(['merk','model','tahun']), 'results' => $results]); }
    public function destroyFitment(Sparepart $sparepart, SparepartFitment $fitment, MasterAuditService $audit): RedirectResponse { abort_unless((int) $fitment->id_sparepart === (int) $sparepart->id_sparepart, 404); $fitment->delete(); $audit->record('delete', 'sparepart_fitment', $fitment->id_sparepart_fitment, 'Menghapus verifikasi kompatibilitas sparepart'); return back()->with('success', 'Data kompatibilitas dihapus.'); }
    public function toggle(Sparepart $sparepart, MasterAuditService $audit): RedirectResponse { $target = $sparepart->status === 'aktif' ? 'nonaktif' : 'aktif'; $sparepart->update(['status' => $target]); $audit->record('status', 'sparepart', $sparepart->id_sparepart, 'Status spare part menjadi '.$target); return back()->with('success', 'Status spare part diperbarui.'); }
    private function storePhoto(Sparepart $item, ?UploadedFile $file): void { if (!$file) return; $name = bin2hex(random_bytes(16)).'.'.strtolower($file->extension()); $file->storeAs('uploads/sparepart', $name, 'public'); $item->update(['foto' => $name]); }
}
