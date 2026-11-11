<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\{AdjustVehicleInventoryRequest, StoreMobilRequest};
use App\Http\Requests\UpdateMobilRequest;
use App\Models\Mobil;
use App\Services\MobilAuditService;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\JsonResponse;
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
        $identity = app(\App\Services\CatalogVehicleIdentityService::class);
        $catalogKey = (string) request('catalog', '');
        $catalogModel = $catalogKey !== '' ? $identity->resolve($catalogKey) : null;
        abort_if($catalogKey !== '' && !$catalogModel, 404, 'Identitas katalog tidak ditemukan.');

        $activeTab = (string) request('tab', 'inventori');
        if (!in_array($activeTab, ['inventori', 'katalog', 'foto'], true)) {
            $activeTab = 'inventori';
        }

        $carsQuery = Mobil::query()
            ->with(['foto', 'warnaPilihan.galeri'])
            ->when($q, function ($query, $v) {
                $query->where(function ($sub) use ($v) {
                    $sub->where('kode_mobil', 'like', "%{$v}%")
                        ->orWhereRaw("CONCAT_WS(' ', merk, model, tipe) LIKE ?", ["%{$v}%"])
                        ->orWhere('merk', 'like', "%{$v}%")
                        ->orWhere('model', 'like', "%{$v}%")
                        ->orWhere('tipe', 'like', "%{$v}%")
                        ->orWhere('tahun', 'like', "%{$v}%")
                        ->orWhere('warna', 'like', "%{$v}%")
                        ->orWhere('nomor_rangka', 'like', "%{$v}%")
                        ->orWhere('nomor_mesin', 'like', "%{$v}%")
                        ->orWhere('showcase_slug', 'like', "%{$v}%");
                });
            })
            ->when($merk, fn($query, $v) => $query->where('merk', $v))
            ->when($status, function ($query, $v) {
                if ($v === 'ready') {
                    $query->where('status', 'tersedia');
                } elseif ($v === 'habis_atau_belum_ada') {
                    $zeroStockCarIds = \Illuminate\Support\Facades\Schema::hasTable('vehicle_inventories')
                        ? \App\Models\VehicleInventory::whereRaw('(stock_total - sold_units) <= 0')->pluck('id_mobil')->all()
                        : [];
                    $query->where(function ($sub) use ($zeroStockCarIds) {
                        $sub->whereIn('status', ['terjual', 'nonaktif', 'dibooking']);
                        if (!empty($zeroStockCarIds)) {
                            $sub->orWhereIn('id_mobil', $zeroStockCarIds);
                        }
                    });
                } elseif ($v === 'ada_preorder') {
                    $activePreordersColl = \Illuminate\Support\Facades\Schema::hasTable('pre_order_mobil_member')
                        ? \App\Models\MemberVehiclePreorder::active()->get()
                        : collect();
                    $activeIds = $activePreordersColl->pluck('id_mobil')->filter()->all();
                    $activeSlugs = $activePreordersColl->pluck('showcase_slug')->filter()->all();
                    $query->where(function ($sub) use ($activeIds, $activeSlugs) {
                        $sub->whereIn('id_mobil', $activeIds);
                        if (!empty($activeSlugs)) {
                            $sub->orWhereIn('showcase_slug', $activeSlugs);
                        }
                    });
                } elseif ($v === 'tidak_tayang') {
                    $query->where('status', 'nonaktif');
                } else {
                    $query->where('status', $v);
                }
            })
            ->when($kondisi, fn($query, $v) => $query->where('kondisi', $v))
            ->when($tahunMin, fn($query, $v) => $query->where('tahun', '>=', (int) $v))
            ->when($tahunMaks, fn($query, $v) => $query->where('tahun', '<=', (int) $v))
            ->when($hargaMin, fn($query, $v) => $query->where('harga_jual', '>=', (float) $v))
            ->when($hargaMaks, fn($query, $v) => $query->where('harga_jual', '<=', (float) $v))
            ->orderByDesc('id_mobil');

        if ($catalogModel) $carsQuery->whereIn('id_mobil', $identity->physical($catalogModel)->pluck('id_mobil'));
        if (request()->integer('stock_mobil') > 0) $carsQuery->reorder()->orderByRaw('id_mobil = ? DESC',[request()->integer('stock_mobil')])->orderByDesc('id_mobil');
        $allCatalogModels = $identity->models();
        $catalogCars = $allCatalogModels->filter(function ($car) use ($q,$merk,$kondisi,$status,$catalogKey,$identity,$tahunMin,$tahunMaks,$hargaMin,$hargaMaks) {
            $text = mb_strtolower($car->merk.' '.$car->model.' '.$car->tipe.' '.$car->kode_mobil.' '.$car->showcase_slug.' '.($car->nomor_rangka ?? ''));
            $activePreorders = $identity->activePreorders($car);
            $stockOrderable = (int) ($car->availability['stock'] ?? 0);
            $isTayang = $car->status !== 'nonaktif';

            $statusMatch = true;
            if ($status) {
                if ($status === 'ready') {
                    $statusMatch = $car->exists && $car->status === 'tersedia' && $stockOrderable > 0;
                } elseif ($status === 'habis_atau_belum_ada') {
                    $statusMatch = !$car->exists || $stockOrderable === 0 || in_array($car->status, ['terjual', 'nonaktif', 'dibooking'], true);
                } elseif ($status === 'ada_preorder') {
                    $statusMatch = $activePreorders > 0;
                } elseif ($status === 'tidak_tayang') {
                    $statusMatch = !$isTayang;
                } else {
                    $statusMatch = $car->status === $status;
                }
            }

            return (!$q || str_contains($text,mb_strtolower($q))) && (!$merk || $car->merk === $merk)
                && (!$kondisi || $car->kondisi === $kondisi) && $statusMatch
                && (!$catalogKey || $identity->key($car) === $catalogKey)
                && (!$tahunMin || $car->tahun >= $tahunMin) && (!$tahunMaks || $car->tahun <= $tahunMaks)
                && (!$hargaMin || $car->harga_jual >= $hargaMin) && (!$hargaMaks || $car->harga_jual <= $hargaMaks);
        })->unique(fn ($car) => $identity->key($car))->values();
        $virtualCatalogCars = $catalogCars->filter(fn ($car) => !$car->exists);

        $cars = $carsQuery->paginate($perPage)->withQueryString();
        $cars->setCollection($availability->decorate($cars->getCollection()));

        // Daftar Merek Unik untuk Filter Dropdown
        $brands = $allCatalogModels->pluck('merk')->filter()->unique()->sort()->values();

        // Kalkulasi Statistik Nyata
        $allCars = Mobil::all();
        $totalModelKatalog = $allCatalogModels->count();
        $modelBerinventori = $allCars->count();
        $totalUnitFisik = $allCars->filter(fn ($car) => !in_array($car->status, ['nonaktif', 'terjual'], true) && app(\App\Services\VehicleColorStockService::class)->identified($car))->count();
        $mobilTersedia = $allCars->filter(fn ($car) => $car->status === 'tersedia' && ($availability->for($car)['can_purchase'] ?? false))->count();
        $mobilTerjual = $allCars->where('status', 'terjual')->count();

        $unitReady = $mobilTersedia;

        // Reservasi aktif: Pesanan member aktif + Booking aktif
        $activeOrders = \Illuminate\Support\Facades\Schema::hasTable('pesanan_mobil_member')
            ? DB::table('pesanan_mobil_member')->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])->count()
            : 0;
        $activeBookings = \Illuminate\Support\Facades\Schema::hasTable('booking_mobil')
            ? DB::table('booking_mobil')->whereIn('status', ['menunggu', 'dikonfirmasi'])->count()
            : 0;
        $mobilDipesan = app(\App\Services\VehicleColorStockService::class)->reservedIds($allCars->whereNotIn('status', ['nonaktif', 'terjual'])->pluck('id_mobil'))->count();

        // Preorder aktif
        $preorderCount = \App\Models\MemberVehiclePreorder::active()->count();

        // Stok Menipis: unit dengan stok tersedia <= 3 dan > 0
        $stokMenipis = $allCars->unique(fn ($car) => app(\App\Services\VehicleInventoryService::class)->key($car))
            ->filter(fn ($car) => ($stock = $availability->for($car)['stock']) > 0 && $stock <= 3)->count();

        // Unit Perlu Perhatian: unit nonaktif atau dalam pemeriksaan
        $unitPerhatian = $allCars->whereIn('status', ['nonaktif', 'dalam_pemeriksaan'])->count();
        if ($unitPerhatian === 0) {
            $unitPerhatian = $allCars->where('status', 'nonaktif')->count();
        }

        $stats = [
            'total_unit' => $totalModelKatalog,
            'total_model_katalog' => $totalModelKatalog,
            'model_berinventori' => $modelBerinventori,
            'total_unit_fisik' => $totalUnitFisik,
            'unit_fisik_tercatat' => $totalUnitFisik,
            'tersedia' => $unitReady,
            'unit_ready' => $unitReady,
            'dipesan' => $mobilDipesan,
            'reservasi_aktif' => $mobilDipesan,
            'terjual' => $mobilTerjual,
            'preorder' => $preorderCount,
            'stok_menipis' => $stokMenipis,
            'perlu_perhatian' => $unitPerhatian,
        ];

        $carIds = $cars->pluck('id_mobil');
        $inventories = \Illuminate\Support\Facades\Schema::hasTable('vehicle_inventories')
            ? \App\Models\VehicleInventory::with(['histories' => fn ($q) => $q->latest('id_vehicle_inventory_history')->limit(20)])
                ->whereIn('id_mobil', $carIds)->get()->keyBy('id_mobil')
            : collect();
        $ordersByCar = \Illuminate\Support\Facades\Schema::hasTable('pesanan_mobil_member')
            ? DB::table('pesanan_mobil_member')
                ->whereIn('id_mobil', $carIds)
                ->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])
                ->get(['id_pesanan', 'id_mobil', 'nomor_pesanan', 'nama_pembeli', 'status', 'created_at'])
                ->groupBy('id_mobil')
            : collect();
        $bookingsByCar = \Illuminate\Support\Facades\Schema::hasTable('booking_mobil')
            ? DB::table('booking_mobil')
                ->whereIn('id_mobil', $carIds)
                ->whereIn('status', ['menunggu', 'dikonfirmasi'])
                ->get(['id_booking', 'id_mobil', 'kode_booking', 'status', 'created_at'])
                ->groupBy('id_mobil')
            : collect();

        $allActivePreorders = \Illuminate\Support\Facades\Schema::hasTable('pre_order_mobil_member')
            ? \App\Models\MemberVehiclePreorder::active()
                ->with(['pelanggan.user', 'mobil'])
                ->latest('created_at')
                ->get()
            : collect();

        return view('admin.mobil.index', compact('cars', 'stats', 'brands', 'q', 'merk', 'status', 'kondisi', 'tahunMin', 'tahunMaks', 'hargaMin', 'hargaMaks', 'perPage', 'activeTab', 'inventories', 'ordersByCar', 'bookingsByCar', 'catalogCars', 'virtualCatalogCars', 'catalogModel', 'catalogKey', 'allActivePreorders'));
    }
    public function create(): View
    {
        $key=(string) request('catalog','');
        $model=$key ? app(\App\Services\CatalogVehicleIdentityService::class)->resolve($key) : null;
        abort_if($key && !$model,404);
        $defaults=['status'=>'tersedia','kondisi'=>'baru','kilometer'=>0,'harga_beli'=>0,'harga_jual'=>0];
        if ($model) $defaults=array_replace($defaults,\Illuminate\Support\Arr::only($model->getAttributes(),['merk','model','tipe','tahun','warna','transmisi','bahan_bakar','kondisi','harga_jual']),['status'=>'dalam_pemeriksaan']);
        return view('admin.mobil.create',['mobil'=>new Mobil($defaults),'catalogModel'=>$model,'catalogKey'=>$key]);
    }
    public function store(StoreMobilRequest $request, MobilAuditService $audit): RedirectResponse|JsonResponse
    {
        $proof = $request->validate(['alasan_stok' => ['required', 'string', 'min:5', 'max:160'], 'bukti_stok' => ['required', 'string', 'min:5', 'max:500'], 'status' => ['required', 'in:tersedia,dalam_pemeriksaan,nonaktif']]);
        $mobil = DB::transaction(function () use ($request, $audit, $proof) {
            $data = app(\App\Services\CatalogVehicleIdentityService::class)->bindNewUnit($request->validated());
            unset($data['foto']);
            $data['sumber_mobil'] = 'dealer';
            $mobil = Mobil::create($data);
            if ($mobil->status !== 'nonaktif') {
                app(\App\Services\VehicleInventoryService::class)->registerPhysicalUnit($mobil);
            }
            $this->storePhotos($mobil, $request->file('foto', []));
            app(\App\Services\VehicleInventoryService::class)->recordUnitChange($mobil, $proof['alasan_stok'], $proof['bukti_stok']);
            $audit->record('create', $mobil->id_mobil, 'Mobil dibuat');
            return $mobil;
        });

        app(\App\Services\MemberVehiclePreorderService::class)->notifyForRestock($mobil->fresh());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Unit kendaraan {$mobil->merk} {$mobil->model} (VIN: {$mobil->nomor_rangka}) berhasil didaftarkan ke inventori.",
                'mobil' => $mobil,
            ]);
        }

        return redirect()->route('admin.mobil.show', $mobil)->with('success', 'Mobil berhasil dibuat.');
    }
    public function show(Mobil $mobil, VehicleAvailabilityService $availability): View|JsonResponse
    {
        $mobil->load(['foto', 'warnaPilihan.galeri']);
        $avail = $availability->for($mobil);
        $mobil->setAttribute('availability', $avail);

        if (request()->wantsJson() || request()->ajax()) {
            $inv = app(\App\Services\VehicleInventoryService::class)->find($mobil);
            $histories = app(\App\Services\VehicleInventoryService::class)->histories($mobil);
            $activeOrders = \Illuminate\Support\Facades\Schema::hasTable('pesanan_mobil_member')
                ? DB::table('pesanan_mobil_member')
                    ->where('id_mobil', $mobil->id_mobil)
                    ->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])
                    ->get(['id_pesanan', 'nomor_pesanan', 'nama_pembeli', 'status', 'created_at'])
                : collect();
            $activeBookings = \Illuminate\Support\Facades\Schema::hasTable('booking_mobil')
                ? DB::table('booking_mobil')
                    ->where('id_mobil', $mobil->id_mobil)
                    ->whereIn('status', ['menunggu', 'dikonfirmasi'])
                    ->get(['id_booking', 'kode_booking', 'status', 'created_at'])
                : collect();

            return response()->json([
                'success' => true,
                'mobil' => $mobil,
                'availability' => $avail,
                'color_stocks' => array_values(app(\App\Services\VehicleColorStockService::class)->colors($mobil)),
                'histories' => $histories,
                'active_orders' => $activeOrders,
                'active_bookings' => $activeBookings,
                'is_deactivatable' => $mobil->status !== 'terjual',
            ]);
        }

        return view('admin.mobil.show', compact('mobil'));
    }
    public function stock(Mobil $mobil, VehicleAvailabilityService $availability): View|JsonResponse
    {
        $avail = $availability->for($mobil);
        $mobil->setAttribute('availability', $avail);
        if (request()->wantsJson() || request()->ajax()) {
            $inv = app(\App\Services\VehicleInventoryService::class)->find($mobil);
            $histories = app(\App\Services\VehicleInventoryService::class)->histories($mobil);
            return response()->json([
                'success' => true,
                'mobil' => $mobil,
                'availability' => $avail,
                'color_stocks' => array_values(app(\App\Services\VehicleColorStockService::class)->colors($mobil)),
                'histories' => $histories,
            ]);
        }
        return view('admin.mobil.stock', compact('mobil'));
    }
    public function adjustStock(AdjustVehicleInventoryRequest $request, Mobil $mobil, \App\Services\VehicleInventoryService $inventory): RedirectResponse|JsonResponse
    {
        try {
            $before = app(VehicleAvailabilityService::class)->for($mobil);
            $data = $request->validated();
            $inventory->adjust($mobil, (int) $data['stok_baru'], $data['alasan'], trim(($data['catatan'] ?? '').' Bukti: '.$data['bukti']));
            $mobil->refresh();
            $after = app(VehicleAvailabilityService::class)->for($mobil);
            if (($before['stock'] ?? 0) < 1 && ($after['stock'] ?? 0) > 0) {
                app(\App\Services\MemberVehiclePreorderService::class)->notifyForRestock($mobil);
            }
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Stok kendaraan berhasil disesuaikan dan dicatat.',
                    'availability' => $after,
                    'mobil' => $mobil,
                ]);
            }
            return back()->with('success', 'Stok kendaraan berhasil disesuaikan dan dicatat.');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            return back()->withInput()->withErrors($e->getMessage());
        }
    }
    public function edit(Mobil $mobil): View|JsonResponse
    {
        $mobil->load(['foto', 'warnaPilihan.galeri']);
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'mobil' => $mobil,
            ]);
        }
        return view('admin.mobil.edit', compact('mobil'));
    }
    public function update(UpdateMobilRequest $request, Mobil $mobil, MobilAuditService $audit): RedirectResponse|JsonResponse
    {
        $proof = $request->validate(['alasan_stok' => ['required', 'string', 'min:5', 'max:160'], 'bukti_stok' => ['required', 'string', 'min:5', 'max:500']]);
        DB::transaction(function () use ($request, $mobil, $audit, $proof) {
            $mobil = Mobil::lockForUpdate()->findOrFail($mobil->getKey());
            $before = $mobil->only(['warna', 'nomor_rangka', 'nomor_mesin', 'status']);
            $data = app(\App\Services\CatalogVehicleIdentityService::class)->validateExisting($mobil, $request->validated());
            unset($data['foto'], $data['sumber_mobil']);
            try { app(\App\Services\VehicleInventoryService::class)->assertEditable($mobil, $data); }
            catch (\RuntimeException $e) { throw \Illuminate\Validation\ValidationException::withMessages(['status' => $e->getMessage()]); }
            $mobil->update($data);
            app(\App\Services\VehicleInventoryService::class)->recordUnitChange($mobil, $proof['alasan_stok'], $proof['bukti_stok'], $before);
            $this->storePhotos($mobil, $request->file('foto', []));
            $audit->record('update', $mobil->id_mobil, 'Mobil diperbarui');
        });
        app(\App\Services\MemberVehiclePreorderService::class)->notifyForRestock($mobil->fresh());

        if ($request->wantsJson() || $request->ajax()) {
            $mobilFresh = $mobil->fresh(['foto', 'warnaPilihan.galeri']);
            $avail = app(VehicleAvailabilityService::class)->for($mobilFresh);
            return response()->json([
                'success' => true,
                'message' => 'Data kendaraan berhasil diperbarui.',
                'mobil' => $mobilFresh,
                'availability' => $avail,
            ]);
        }

        return redirect()->route('admin.mobil.show', $mobil)->with('success', 'Mobil berhasil diperbarui.');
    }
    public function deactivate(Mobil $mobil, MobilAuditService $audit): RedirectResponse|JsonResponse
    {
        abort_if($mobil->status === 'terjual', 422, 'Mobil terjual tidak dapat dinonaktifkan.');
        $proof = request()->validate(['alasan_stok' => ['required', 'string', 'min:5', 'max:160'], 'bukti_stok' => ['required', 'string', 'min:5', 'max:500']]);
        DB::transaction(function () use ($mobil, $proof) {
            $unit = Mobil::lockForUpdate()->findOrFail($mobil->getKey());
            try { app(\App\Services\VehicleInventoryService::class)->assertEditable($unit, ['status' => 'nonaktif']); }
            catch (\RuntimeException $e) { throw \Illuminate\Validation\ValidationException::withMessages(['status' => $e->getMessage()]); }
            $before = $unit->only(['warna', 'nomor_rangka', 'nomor_mesin', 'status']);
            $unit->update(['status' => 'nonaktif']);
            app(\App\Services\VehicleInventoryService::class)->recordUnitChange($unit, $proof['alasan_stok'], $proof['bukti_stok'], $before);
        });
        $audit->record('deactivate', $mobil->id_mobil, 'Mobil dinonaktifkan');
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Mobil berhasil dinonaktifkan.',
                'status' => 'nonaktif',
                'id_mobil' => $mobil->id_mobil,
            ]);
        }
        return back()->with('success', 'Mobil dinonaktifkan.');
    }

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
