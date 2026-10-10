<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use Illuminate\View\View;

class MobilController extends Controller
{
    public function index(): View { return view('petugas.mobil.index', ['cars' => Mobil::query()->when(request('q'), fn($q, $v) => $q->where(fn($x) => $x->where('kode_mobil','like','%'.$v.'%')->orWhere('merk','like','%'.$v.'%')->orWhere('model','like','%'.$v.'%')))->when(request('status'), fn($q, $v) => $q->where('status',$v))->when(request('kondisi'), fn($q, $v) => $q->where('kondisi',$v))->orderByDesc('id_mobil')->paginate(10)->withQueryString()]); }
    public function show(Mobil $mobil): View { $mobil->load('foto'); return view('petugas.mobil.show', compact('mobil')); }
}
