<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\{AdjustVehicleInventoryRequest, StoreMobilRequest};
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
    public function index(VehicleAvailabilityService $availability): View
    {
        $q = request('q');
        $merk = request('merk');
        $status = request('status');
        $kondisi = request('kondisi');
        $tahunMin = request('tahun_min');
        $tahunMaks = request('tahun_maks');
        $hargaMin = request('harga_min');
        $hargaMaks = request('harga_maks');
        $perPage = (int) (request('per_page', 10) ?: 10);

        $carsQuery = Mobil::query()
            ->with(['foto'])
            ->when($q, function ($query, $v) {
                $query->where(function ($sub) use ($v) {
                    $sub->where('kode_mobil', 'like', "%{$v}%")
                        ->orWhere('merk', 'like', "%{$v}%")
                        ->orWhere('model', 'like', "%{$v}%")
                        ->orWhere('tipe', 'like', "%{$v}%")
                        ->orWhere('tahun', 'like', "%{$v}%")
                        ->orWhere('warna', 'like', "%{$v}%")
                        ->orWhere('showcase_slug', 'like', "%{$v}%");
                });
            })
            ->when($merk, fn($query, $v) => $query->where('merk', $v))
            ->when($status, fn($query, $v) => $query->where('status', $v))
            ->when($kondisi, fn($query, $v) => $query->where('kondisi', $v))
            ->when($tahunMin, fn($query, $v) => $query->where('tahun', '>=', (int) $v))
            ->when($tahunMaks, fn($query, $v) => $query->where('tahun', '<=', (int) $v))
            ->when($hargaMin, fn($query, $v) => $query->where('harga_jual', '>=', (float) $v))
            ->when($hargaMaks, fn($query, $v) => $query->where('harga_jual', '<=', (float) $v))
            ->orderByDesc('id_mobil');

        $cars = $carsQuery->paginate($perPage)->withQueryString();
        $cars->setCollection($availability->decorate($cars->getCollection()));

        // Daftar Merek Unik untuk Filter Dropdown
        $brands = Mobil::distinct()->whereNotNull('merk')->where('merk', '!=', '')->orderBy('merk')->pluck('merk');

        // Kalkulasi 6 Kartu Statistik Inventori Nyata
        $allCars = Mobil::all();
        $totalUnit = $allCars->count();
        $mobilTersedia = $allCars->where('status', 'tersedia')->count();
        $mobilTerjual = $allCars->where('status', 'terjual')->count();

        // Reservasi aktif: Pesanan member aktif + Booking aktif
        $activeOrders = \Illuminate\Support\Facades\Schema::hasTable('pesanan_mobil_member')
            ? DB::table('pesanan_mobil_member')->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])->count()
            : 0;
        $activeBookings = \Illuminate\Support\Facades\Schema::hasTable('booking_mobil')
            ? DB::table('booking_mobil')->whereIn('status', ['menunggu', 'dikonfirmasi'])->count()
            : 0;
        $mobilDipesan = $activeOrders + $activeBookings;

        // Stok Menipis: unit dengan stok tersedia <= 3
        $stokMenipis = 0;
        foreach (\App\Models\VehicleInventory::all() as $inv) {
            $reserveCount = $inv->mobil ? app(\App\Services\VehicleInventoryService::class)->reserveCount($inv->mobil) : 0;
            $avail = max(0, (int) $inv->stock_total - (int) $inv->sold_units - $reserveCount);
            if ($avail <= 3 && $avail > 0) {
                $stokMenipis++;
            }
        }

        // Unit Perlu Perhatian: unit nonaktif atau dalam pemeriksaan
        $unitPerhatian = $allCars->whereIn('status', ['nonaktif', 'dalam_pemeriksaan'])->count();
        if ($unitPerhatian === 0) {
            $unitPerhatian = $allCars->where('status', 'nonaktif')->count();
        }

        $stats = [
            'total_unit' => $totalUnit,
            'tersedia' => $mobilTersedia,
            'dipesan' => $mobilDipesan,
            'terjual' => $mobilTerjual,
            'stok_menipis' => $stokMenipis,
            'perlu_perhatian' => $unitPerhatian,
        ];

        return view('admin.mobil.index', compact('cars', 'stats', 'brands', 'q', 'merk', 'status', 'kondisi', 'tahunMin', 'tahunMaks', 'hargaMin', 'hargaMaks', 'perPage'));
    }
    public function create(): View { return view('admin.mobil.create', ['mobil' => new Mobil(['status' => 'tersedia', 'kondisi' => 'baru', 'kilometer' => 0, 'harga_beli' => 0, 'harga_jual' => 0])]); }
    public function store(StoreMobilRequest $request, MobilAuditService $audit): RedirectResponse { $mobil = DB::transaction(function () use ($request, $audit) { $data = $request->validated(); unset($data['foto']); $data['sumber_mobil'] = 'dealer'; $mobil = Mobil::create($data); if ($mobil->status !== 'nonaktif') app(\App\Services\VehicleInventoryService::class)->registerPhysicalUnit($mobil); $this->storePhotos($mobil, $request->file('foto', [])); $audit->record('create', $mobil->id_mobil, 'Mobil dibuat'); return $mobil; }); app(\App\Services\MemberVehiclePreorderService::class)->notifyForRestock($mobil->fresh()); return redirect()->route('admin.mobil.show', $mobil)->with('success', 'Mobil berhasil dibuat.'); }
    public function show(Mobil $mobil): View { $mobil->load('foto'); return view('admin.mobil.show', compact('mobil')); }
    public function stock(Mobil $mobil, VehicleAvailabilityService $availability): View { $mobil->setAttribute('availability', $availability->for($mobil)); return view('admin.mobil.stock', compact('mobil')); }
    public function adjustStock(AdjustVehicleInventoryRequest $request, Mobil $mobil, \App\Services\VehicleInventoryService $inventory): RedirectResponse { try { $before = app(VehicleAvailabilityService::class)->for($mobil); $data = $request->validated(); $inventory->adjust($mobil, (int) $data['stok_baru'], $data['alasan'], $data['catatan'] ?? null); $mobil->refresh(); $after = app(VehicleAvailabilityService::class)->for($mobil); if (($before['stock'] ?? 0) < 1 && ($after['stock'] ?? 0) > 0) app(\App\Services\MemberVehiclePreorderService::class)->notifyForRestock($mobil); return back()->with('success', 'Stok kendaraan berhasil disesuaikan dan dicatat.'); } catch (\Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); } }
    public function edit(Mobil $mobil): View { return view('admin.mobil.edit', compact('mobil')); }
    public function update(UpdateMobilRequest $request, Mobil $mobil, MobilAuditService $audit): RedirectResponse { DB::transaction(function () use ($request, $mobil, $audit) { $data = $request->validated(); unset($data['foto'], $data['sumber_mobil']); $mobil->update($data); $this->storePhotos($mobil, $request->file('foto', [])); $audit->record('update', $mobil->id_mobil, 'Mobil diperbarui'); }); app(\App\Services\MemberVehiclePreorderService::class)->notifyForRestock($mobil->fresh()); return redirect()->route('admin.mobil.show', $mobil)->with('success', 'Mobil berhasil diperbarui.'); }
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
