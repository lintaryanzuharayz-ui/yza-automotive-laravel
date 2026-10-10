<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\MasterAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View { return view('admin.supplier.index', ['items' => Supplier::query()->when(request('q'), fn($q, $v) => $q->where(fn($x) => $x->where('kode_supplier','like','%'.$v.'%')->orWhere('nama_supplier','like','%'.$v.'%')->orWhere('no_hp','like','%'.$v.'%')->orWhere('email','like','%'.$v.'%')->orWhere('kontak_person','like','%'.$v.'%')))->when(request('status'), fn($q, $v) => $q->where('status',$v))->orderByDesc('created_at')->paginate(10)->withQueryString()]); }
    public function create(): View { return view('admin.supplier.create', ['item' => new Supplier]); }
    public function store(StoreSupplierRequest $request, MasterAuditService $audit): RedirectResponse { $data = $request->validated(); $data['kode_supplier'] = $data['kode_supplier'] ?: 'SUP-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3))); $item = Supplier::create($data + ['status' => 'aktif']); $audit->record('create', 'supplier', $item->id_supplier, 'Menambahkan supplier'); return redirect()->route('admin.supplier.show', $item)->with('success', 'Supplier berhasil ditambahkan.'); }
    public function show(Supplier $supplier): View { return view('admin.supplier.show', ['item' => $supplier]); }
    public function edit(Supplier $supplier): View { return view('admin.supplier.edit', ['item' => $supplier]); }
    public function update(UpdateSupplierRequest $request, Supplier $supplier, MasterAuditService $audit): RedirectResponse { $supplier->update($request->validated()); $audit->record('update', 'supplier', $supplier->id_supplier, 'Mengubah supplier'); return redirect()->route('admin.supplier.show', $supplier)->with('success', 'Supplier berhasil diperbarui.'); }
    public function toggle(Supplier $supplier, MasterAuditService $audit): RedirectResponse { $target = $supplier->status === 'aktif' ? 'nonaktif' : 'aktif'; $supplier->update(['status' => $target]); $audit->record('status', 'supplier', $supplier->id_supplier, 'Status supplier menjadi '.$target); return back()->with('success', 'Status supplier diperbarui.'); }
}
