<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMobilRequest;
use App\Http\Requests\UpdateMobilRequest;
use App\Models\Mobil;
use App\Services\MobilAuditService;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MobilController extends Controller
{
    public function index(VehicleAvailabilityService $availability): View { $cars = Mobil::query()->when(request('q'), fn($q, $v) => $q->where(fn($x) => $x->where('kode_mobil','like','%'.$v.'%')->orWhere('merk','like','%'.$v.'%')->orWhere('model','like','%'.$v.'%')->orWhere('showcase_slug','like','%'.$v.'%')))->when(request('status'), fn($q, $v) => $q->where('status',$v))->when(request('kondisi'), fn($q, $v) => $q->where('kondisi',$v))->orderByDesc('id_mobil')->paginate(10)->withQueryString(); $cars->setCollection($availability->decorate($cars->getCollection())); return view('admin.mobil.index', compact('cars')); }
    public function create(): View { return view('admin.mobil.create', ['mobil' => new Mobil(['status' => 'tersedia', 'kondisi' => 'baru', 'kilometer' => 0, 'harga_beli' => 0, 'harga_jual' => 0])]); }
    public function store(StoreMobilRequest $request, MobilAuditService $audit): RedirectResponse { $mobil = DB::transaction(function () use ($request, $audit) { $data = $request->validated(); unset($data['foto']); $data['sumber_mobil'] = 'dealer'; $mobil = Mobil::create($data); $this->storePhotos($mobil, $request->file('foto', [])); $audit->record('create', $mobil->id_mobil, 'Mobil dibuat'); return $mobil; }); return redirect()->route('admin.mobil.show', $mobil)->with('success', 'Mobil berhasil dibuat.'); }
    public function show(Mobil $mobil): View { $mobil->load('foto'); return view('admin.mobil.show', compact('mobil')); }
    public function edit(Mobil $mobil): View { return view('admin.mobil.edit', compact('mobil')); }
    public function update(UpdateMobilRequest $request, Mobil $mobil, MobilAuditService $audit): RedirectResponse { DB::transaction(function () use ($request, $mobil, $audit) { $data = $request->validated(); unset($data['foto'], $data['sumber_mobil']); $mobil->update($data); $this->storePhotos($mobil, $request->file('foto', [])); $audit->record('update', $mobil->id_mobil, 'Mobil diperbarui'); }); return redirect()->route('admin.mobil.show', $mobil)->with('success', 'Mobil berhasil diperbarui.'); }
    public function deactivate(Mobil $mobil, MobilAuditService $audit): RedirectResponse { abort_if($mobil->status === 'terjual', 422, 'Mobil terjual tidak dapat dinonaktifkan.'); $mobil->update(['status' => 'nonaktif']); $audit->record('deactivate', $mobil->id_mobil, 'Mobil dinonaktifkan'); return back()->with('success', 'Mobil dinonaktifkan.'); }

    private function storePhotos(Mobil $mobil, array $files): void
    {
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            $name = 'mobil_'.$mobil->id_mobil.'_'.bin2hex(random_bytes(8)).'.'.strtolower($file->extension());
            $path = $file->storeAs('uploads/mobil', $name, 'public');
            $hasPrimary = $mobil->foto()->exists();
            $mobil->foto()->create(['nama_file' => basename($path), 'foto_utama' => !$hasPrimary, 'urutan' => (int) $mobil->foto()->max('urutan') + 1]);
        }
    }
}
