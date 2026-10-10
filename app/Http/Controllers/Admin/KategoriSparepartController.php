<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKategoriSparepartRequest;
use App\Http\Requests\UpdateKategoriSparepartRequest;
use App\Models\KategoriSparepart;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KategoriSparepartController extends Controller
{
    public function index(): View { return view('admin.kategori-sparepart.index', ['items' => KategoriSparepart::withCount('sparepart')->when(request('q'), fn($q, $v) => $q->where('nama_kategori','like','%'.$v.'%')->orWhere('deskripsi','like','%'.$v.'%'))->orderBy('nama_kategori')->paginate(10)->withQueryString()]); }
    public function create(): View { return view('admin.kategori-sparepart.create', ['item' => new KategoriSparepart]); }
    public function store(StoreKategoriSparepartRequest $request, MasterAuditService $audit): RedirectResponse { $item = KategoriSparepart::create($request->validated() + ['status' => 'aktif']); $audit->record('create', 'kategori_sparepart', $item->id_kategori, 'Menambahkan kategori'); return redirect()->route('admin.kategori-sparepart.index')->with('success', 'Kategori berhasil ditambahkan.'); }
    public function edit(KategoriSparepart $kategoriSparepart): View { return view('admin.kategori-sparepart.edit', ['item' => $kategoriSparepart]); }
    public function update(UpdateKategoriSparepartRequest $request, KategoriSparepart $kategoriSparepart, MasterAuditService $audit): RedirectResponse { $kategoriSparepart->update($request->validated()); $audit->record('update', 'kategori_sparepart', $kategoriSparepart->id_kategori, 'Mengubah kategori'); return redirect()->route('admin.kategori-sparepart.index')->with('success', 'Kategori berhasil diperbarui.'); }
    public function toggle(KategoriSparepart $kategoriSparepart, MasterAuditService $audit): RedirectResponse { $target = $kategoriSparepart->status === 'aktif' ? 'nonaktif' : 'aktif'; $kategoriSparepart->update(['status' => $target]); $audit->record('status', 'kategori_sparepart', $kategoriSparepart->id_kategori, 'Status kategori menjadi '.$target); return back()->with('success', 'Status kategori diperbarui.'); }
}
