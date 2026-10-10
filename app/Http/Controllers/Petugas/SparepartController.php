<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use Illuminate\View\View;

class SparepartController extends Controller
{
    public function index(): View { return view('petugas.sparepart.index', ['items' => Sparepart::with('kategori')->when(request('q'), fn($q, $v) => $q->where(fn($x) => $x->where('kode_sparepart','like','%'.$v.'%')->orWhere('nama_sparepart','like','%'.$v.'%')->orWhere('merk','like','%'.$v.'%')))->orderBy('nama_sparepart')->paginate(10)->withQueryString()]); }
    public function show(Sparepart $sparepart): View { $sparepart->load('kategori'); return view('petugas.sparepart.show', ['item' => $sparepart]); }
}
