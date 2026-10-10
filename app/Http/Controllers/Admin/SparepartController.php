<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSparepartRequest;
use App\Http\Requests\UpdateSparepartRequest;
use App\Models\KategoriSparepart;
use App\Models\Sparepart;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SparepartController extends Controller
{
    public function index(): View { return view('admin.sparepart.index', ['items' => Sparepart::with('kategori')->when(request('q'), fn($q, $v) => $q->where(fn($x) => $x->where('kode_sparepart','like','%'.$v.'%')->orWhere('nama_sparepart','like','%'.$v.'%')->orWhere('merk','like','%'.$v.'%')))->when(request('kategori'), fn($q, $v) => $q->where('id_kategori',$v))->when(request('status'), fn($q, $v) => $q->where('status',$v))->when(request('stok') === 'aman', fn($q) => $q->whereColumn('stok','>','stok_minimum'))->when(request('stok') === 'menipis', fn($q) => $q->where('stok','>',0)->whereColumn('stok','<=','stok_minimum'))->when(request('stok') === 'habis', fn($q) => $q->where('stok',0))->orderByDesc('created_at')->paginate(10)->withQueryString(), 'categories' => KategoriSparepart::where('status','aktif')->orderBy('nama_kategori')->get()]); }
    public function create(): View { return view('admin.sparepart.create', ['item' => new Sparepart(['harga_beli' => 0, 'harga_jual' => 0, 'stok_minimum' => 0, 'satuan' => 'pcs']), 'categories' => KategoriSparepart::where('status','aktif')->orderBy('nama_kategori')->get()]); }
    public function store(StoreSparepartRequest $request, MasterAuditService $audit): RedirectResponse { $data = $request->validated(); unset($data['foto']); $data['status'] = 'aktif'; $item = Sparepart::create($data); $this->storePhoto($item, $request->file('foto')); $audit->record('create', 'sparepart', $item->id_sparepart, 'Menambahkan spare part'); return redirect()->route('admin.sparepart.show', $item)->with('success', 'Spare part berhasil ditambahkan.'); }
    public function show(Sparepart $sparepart): View { $sparepart->load('kategori'); return view('admin.sparepart.show', ['item' => $sparepart]); }
    public function edit(Sparepart $sparepart): View { return view('admin.sparepart.edit', ['item' => $sparepart, 'categories' => KategoriSparepart::where('status','aktif')->orWhere('id_kategori',$sparepart->id_kategori)->orderBy('nama_kategori')->get()]); }
    public function update(UpdateSparepartRequest $request, Sparepart $sparepart, MasterAuditService $audit): RedirectResponse { $data = $request->validated(); unset($data['foto']); $sparepart->update($data); $this->storePhoto($sparepart, $request->file('foto')); $audit->record('update', 'sparepart', $sparepart->id_sparepart, 'Mengubah master tanpa mengubah stok'); return redirect()->route('admin.sparepart.show', $sparepart)->with('success', 'Spare part berhasil diperbarui.'); }
    public function toggle(Sparepart $sparepart, MasterAuditService $audit): RedirectResponse { $target = $sparepart->status === 'aktif' ? 'nonaktif' : 'aktif'; $sparepart->update(['status' => $target]); $audit->record('status', 'sparepart', $sparepart->id_sparepart, 'Status spare part menjadi '.$target); return back()->with('success', 'Status spare part diperbarui.'); }
    private function storePhoto(Sparepart $item, ?UploadedFile $file): void { if (!$file) return; $name = bin2hex(random_bytes(16)).'.'.strtolower($file->extension()); $file->storeAs('uploads/sparepart', $name, 'public'); $item->update(['foto' => $name]); }
}
