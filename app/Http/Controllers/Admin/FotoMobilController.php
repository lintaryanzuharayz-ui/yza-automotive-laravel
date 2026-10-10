<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFotoMobilRequest;
use App\Models\FotoMobil;
use App\Models\Mobil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FotoMobilController extends Controller
{
    private function owned(Mobil $mobil, FotoMobil $foto): FotoMobil { abort_unless((int) $foto->id_mobil === (int) $mobil->id_mobil, 404); return $foto; }
    public function store(StoreFotoMobilRequest $request, Mobil $mobil): RedirectResponse { $name = 'mobil_'.$mobil->id_mobil.'_'.bin2hex(random_bytes(8)).'.'.strtolower($request->file('foto')->extension()); $path = $request->file('foto')->storeAs('uploads/mobil', $name, 'public'); $mobil->foto()->create(['nama_file' => $name, 'foto_utama' => !$mobil->foto()->exists(), 'urutan' => (int) $mobil->foto()->max('urutan') + 1]); return back()->with('success', 'Foto berhasil diunggah.'); }
    public function primary(Mobil $mobil, FotoMobil $foto): RedirectResponse { $this->owned($mobil, $foto); DB::transaction(function () use ($mobil, $foto) { $mobil->foto()->update(['foto_utama' => false]); $foto->update(['foto_utama' => true]); }); return back()->with('success', 'Foto utama diperbarui.'); }
    public function destroy(Mobil $mobil, FotoMobil $foto): RedirectResponse { $this->owned($mobil, $foto); $name = basename($foto->nama_file); DB::transaction(function () use ($mobil, $foto) { $wasPrimary = $foto->foto_utama; $foto->delete(); if ($wasPrimary && ($next = $mobil->foto()->first())) $next->update(['foto_utama' => true]); }); Storage::disk('public')->delete('uploads/mobil/'.$name); return back()->with('success', 'Foto dihapus.'); }
}
