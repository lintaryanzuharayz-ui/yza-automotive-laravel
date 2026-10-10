<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Services\VehicleGalleryService;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobilController extends Controller
{
    public function index(Request $request, VehicleAvailabilityService $availabilityService)
    {
        $galleryService = app(VehicleGalleryService::class);
        $allCars = Mobil::query()->where('status', '!=', 'nonaktif')->with('foto')->get()->map(function (Mobil $car) use ($galleryService): Mobil {
            $identity = strtolower(trim($car->merk.' '.$car->model.' '.$car->tipe));
            if (str_contains($identity, 'toyota gr supra')) {
                $car->setAttribute('showcase_slug', 'toyota-gr-supra');
            } elseif (str_contains($identity, 'toyota supra mk4') || str_contains($identity, 'toyota supra 3.0l a/t')) {
                $car->setAttribute('showcase_slug', 'toyota-supra-mk4');
            } elseif (str_starts_with((string) $car->kode_mobil, 'PREM-')) {
                if ($galleryService->isReady($car)) {
                    $car->setAttribute('showcase_slug', Str::slug($car->merk.' '.$car->model));
                    $car->setAttribute('image', $galleryService->defaultImage($car));
                } else {
                    $car->setAttribute('gallery_ready', false);
                }
            }
            return $car;
        })->filter(fn (Mobil $car): bool => !str_starts_with((string) $car->kode_mobil, 'PREM-') || $car->getAttribute('gallery_ready') !== false);
        $realIds = $allCars->pluck('id_mobil')->filter()->map(fn ($id): int => (int) $id)->all();
        $showcaseCars = $this->catalogShowcases()->filter(fn (Mobil $car): bool => !$car->getKey() || !in_array((int) $car->getKey(), $realIds, true));
        $allCars = $allCars->concat($showcaseCars);
        $allCars = $availabilityService->decorate($allCars);
        $cars = $allCars;
        $search = trim((string) $request->input('q', ''));
        $condition = (string) $request->input('kondisi', '');
        $brand = (string) $request->input('merk', '');
        $sportOnly = $request->boolean('sport');

        $cars = $cars->filter(function (Mobil $car) use ($search, $condition, $brand, $sportOnly): bool {
            $haystack = strtolower($car->merk.' '.$car->model.' '.$car->tipe);
            return ($search === '' || str_contains($haystack, strtolower($search)))
                && ($condition === '' || $car->kondisi === $condition)
                && ($brand === '' || $car->merk === $brand)
                && (!$sportOnly || (bool) $car->showcase_slug);
        });

        $sort = (string) $request->input('sort', 'terbaru');
        $hasExplicitCatalogFilter = $search !== '' || $condition !== '' || $brand !== '' || $sportOnly;

        match ($sort) {
            'harga_termurah' => $cars = $cars->sortBy(fn (Mobil $car) => (float) $car->harga_jual),
            'harga_tertinggi' => $cars = $cars->sortByDesc(fn (Mobil $car) => (float) $car->harga_jual),
            'tahun_terbaru' => $cars = $cars->sortByDesc(fn (Mobil $car) => (int) $car->tahun),
            default => $cars = $hasExplicitCatalogFilter
                ? $cars->sortByDesc(fn (Mobil $car) => (int) ($car->id_mobil ?: 0))
                : $cars->sortByDesc(fn (Mobil $car) => [(bool) $car->showcase_slug, (int) ($car->id_mobil ?: 0)]),
        };

        $cars = $cars->values();
        $filteredTotal = $cars->count();
        $perPage = 12;
        $currentPage = max(1, (int) $request->input('page', 1));
        $cars = $cars->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $lastPage = max(1, (int) ceil($filteredTotal / $perPage));
        $view = $request->routeIs('pelanggan.katalog-mobil.index') ? 'pelanggan.katalog-mobil.index' : 'mobil.index';
        return view($view, [
            'cars' => $cars,
            'totalCars' => $filteredTotal,
            'visibleCars' => $cars->count(),
            'allTotalCars' => $allCars->count(),
            'newCars' => $allCars->where('kondisi', 'baru')->count(),
            'usedCars' => $allCars->where('kondisi', 'bekas')->count(),
            'sportCars' => $allCars->filter(fn (Mobil $car): bool => (bool) $car->showcase_slug)->count(),
            'brands' => $allCars->pluck('merk')->filter()->unique()->sort()->values(),
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
        ]);
    }

    public function memberShow(Request $request, Mobil $mobil, VehicleGalleryService $galleryService, VehicleAvailabilityService $availabilityService)
    {
        abort_unless(strtolower((string) $mobil->status) !== 'nonaktif', 404);
        abort_if(str_starts_with((string) $mobil->kode_mobil, 'PREM-') && !$galleryService->isReady($mobil), 404);
        $mobil->load('foto');
        $mobil->setAttribute('availability', $availabilityService->for($mobil));
        return view('pelanggan.katalog-mobil.member-show', [
            'mobil' => $mobil,
            'galleryData' => $galleryService->for($mobil),
            'defaultColorKey' => $galleryService->defaultColor($mobil),
        ]);
    }

    public function memberShowcase(string $slug, VehicleGalleryService $galleryService)
    {
        $premium = Mobil::query()->where('kode_mobil', 'like', 'PREM-%')->where('status', 'tersedia')->get()->first(fn (Mobil $car): bool => Str::slug($car->merk.' '.$car->model) === $slug);
        if ($premium && $galleryService->isReady($premium)) {
            return $this->memberShowcaseView($premium, $galleryService);
        }

        $showcases = [
            'audi-r8' => ['merk' => 'Audi', 'model' => 'R8', 'tipe' => 'Performance', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 1850000000, 'kondisi' => 'baru', 'warna' => 'Ibis White', 'kode_mobil' => 'SHOWCASE-AUDI-R8', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/audi-r8.png')],
            'bmw-m4' => ['merk' => 'BMW', 'model' => 'M4', 'tipe' => 'Competition', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 2100000000, 'kondisi' => 'baru', 'warna' => 'Toronto Red', 'kode_mobil' => 'SHOWCASE-BMW-M4', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/bmw-m4.png')],
            'bugatti-chiron' => ['merk' => 'Bugatti', 'model' => 'Chiron', 'tipe' => 'Super Sport', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 68000000000, 'kondisi' => 'baru', 'warna' => 'Atlantic Blue', 'kode_mobil' => 'SHOWCASE-BUGATTI-CHIRON', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/bugatti-chiron.png')],
            'mclaren-720s' => ['merk' => 'McLaren', 'model' => '720S', 'tipe' => 'Performance', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 7900000000, 'kondisi' => 'baru', 'warna' => 'Papaya Spark', 'kode_mobil' => 'SHOWCASE-MCLAREN-720S', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/mclaren-720s.png')],
        ];
        abort_unless(isset($showcases[$slug]), 404);
        $inventory = $this->showcaseInventory($slug);
        if ($inventory) {
            $inventory->setAttribute('showcase_slug', $slug);
            $inventory->setAttribute('image', $showcases[$slug]['image']);
            return $this->memberShowcaseView($inventory, $galleryService, $showcases[$slug]['image']);
        }
        $data = $showcases[$slug];
        $image = $data['image']; unset($data['image']);
        $mobil = new Mobil($data); $mobil->setAttribute('status', 'tersedia'); $mobil->setAttribute('image', $image);
        return $this->memberShowcaseView($mobil, $galleryService, $image);
    }

    private function memberShowcaseView(Mobil $mobil, VehicleGalleryService $galleryService, ?string $fallback = null)
    {
        return view('pelanggan.katalog-mobil.member-show', ['mobil' => $mobil, 'galleryData' => $galleryService->for($mobil, $fallback), 'defaultColorKey' => $galleryService->defaultColor($mobil)]);
    }

    private function catalogShowcases()
    {
        $showcases = [
            'audi-r8' => ['merk' => 'Audi', 'model' => 'R8', 'tipe' => 'Performance', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 1850000000, 'kondisi' => 'baru', 'warna' => 'Ibis White', 'kode_mobil' => 'SHOWCASE-AUDI-R8', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/audi-r8.png')],
            'bmw-m4' => ['merk' => 'BMW', 'model' => 'M4', 'tipe' => 'Competition', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 2100000000, 'kondisi' => 'baru', 'warna' => 'Toronto Red', 'kode_mobil' => 'SHOWCASE-BMW-M4', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/bmw-m4.png')],
            'bugatti-chiron' => ['merk' => 'Bugatti', 'model' => 'Chiron', 'tipe' => 'Super Sport', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 68000000000, 'kondisi' => 'baru', 'warna' => 'Atlantic Blue', 'kode_mobil' => 'SHOWCASE-BUGATTI-CHIRON', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/bugatti-chiron.png')],
            'mclaren-720s' => ['merk' => 'McLaren', 'model' => '720S', 'tipe' => 'Performance', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 7900000000, 'kondisi' => 'baru', 'warna' => 'Papaya Spark', 'kode_mobil' => 'SHOWCASE-MCLAREN-720S', 'bahan_bakar' => 'bensin', 'image' => asset('assets/images/catalog/sport/mclaren-720s.png')],
        ];

        return collect($showcases)->map(function (array $data, string $slug) {
            $image = $data['image'];
            unset($data['image']);
            $car = $this->showcaseInventory($slug) ?: new Mobil($data);
            if (!$car->exists) {
                $car->setAttribute('status', 'tersedia');
            }
            $car->setAttribute('image', $image);
            $car->setAttribute('showcase_slug', $slug);
            return $car;
        });
    }

    private function showcaseInventory(string $slug): ?Mobil
    {
        return Mobil::query()
            ->where('showcase_slug', $slug)
            ->where('status', '!=', 'nonaktif')
            ->first();
    }

    public function sportShowcases()
    {
        $galleryService = app(VehicleGalleryService::class);
        $premium = Mobil::query()
            ->where('status', 'tersedia')
            ->where('kode_mobil', 'like', 'PREM-%')
            ->orderBy('id_mobil')
            ->get()
            ->filter(fn (Mobil $car): bool => $galleryService->isReady($car))
            ->take(8)
            ->map(function (Mobil $car) use ($galleryService): Mobil {
                $car->setAttribute('showcase_slug', Str::slug($car->merk.' '.$car->model));
                $car->setAttribute('image', $galleryService->defaultImage($car));
                return $car;
            });

        return $this->catalogShowcases()->concat($premium)->values();
    }

    public function show(Request $request, Mobil $mobil, VehicleGalleryService $galleryService, VehicleAvailabilityService $availabilityService)
    {
        abort_unless($mobil->status !== 'nonaktif', 404);
        abort_if(str_starts_with((string) $mobil->kode_mobil, 'PREM-') && !$galleryService->isReady($mobil), 404);
        $mobil->load('foto');
        $mobil->setAttribute('availability', $availabilityService->for($mobil));
        return view('mobil.show', [
            'mobil' => $mobil,
            'galleryData' => $galleryService->for($mobil),
            'defaultColorKey' => $galleryService->defaultColor($mobil),
            ...$this->detailNavigation($request),
        ]);
    }

    public function tradeInEntry(Request $request)
    {
        $query = $this->tradeInQuery($request);
        $entryUrl = route('trade-in.entry', $query);

        if (!auth()->check()) {
            $this->rememberTradeInIntent($request, $query);
            return redirect()->route('register');
        }

        abort_unless($request->user()->roleName() === 'pelanggan', 403);
        $customer = $request->user()->pelanggan;
        if (!$customer?->membership?->isActive()) {
            $this->rememberTradeInIntent($request, $query);
            return redirect()->route('pelanggan.membership.activation')
                ->with('status', 'Aktifkan YZA Member untuk melanjutkan Trade-In.');
        }

        if ($request->filled('mobil')) {
            $car = Mobil::query()->whereKey($request->integer('mobil'))->where('status', 'tersedia')->first();
            abort_unless($car, 404);
        }

        $request->session()->forget('trade_in.intent');
        return redirect()->route('pelanggan.pengajuan-mobil.create', $query);
    }

    public function membershipEntry(Request $request)
    {
        $query = $this->tradeInQuery($request);
        $this->rememberTradeInIntent($request, $query);

        if (!auth()->check()) {
            return redirect()->route('register');
        }

        abort_unless($request->user()->roleName() === 'pelanggan', 403);
        if ($request->user()->pelanggan?->membership?->isActive()) {
            return redirect()->route('trade-in.entry', $query);
        }

        return redirect()->route('pelanggan.membership.activation')
            ->with('status', 'Aktifkan YZA Member untuk melanjutkan Trade-In.');
    }

    private function tradeInQuery(Request $request): array
    {
        return array_filter([
            'mobil' => $request->integer('mobil') ?: null,
            'slug' => $request->filled('slug') ? (string) $request->string('slug') : null,
        ], fn ($value) => filled($value));
    }

    private function rememberTradeInIntent(Request $request, array $query): void
    {
        $request->session()->put('trade_in.intent', $query);
    }

    public function showcase(Request $request, string $slug)
    {
        $premium = Mobil::query()->where('kode_mobil', 'like', 'PREM-%')->where('status', 'tersedia')->get()->first(fn (Mobil $car): bool => Str::slug($car->merk.' '.$car->model) === $slug);
        if ($premium && app(VehicleGalleryService::class)->isReady($premium)) {
            return view('mobil.show', ['mobil' => $premium, 'galleryData' => app(VehicleGalleryService::class)->for($premium), 'defaultColorKey' => app(VehicleGalleryService::class)->defaultColor($premium), ...$this->detailNavigation($request)]);
        }
        if ($slug === 'toyota-gr-supra') {
            $mobil = Mobil::query()->where('kode_mobil', 'YZA-TOYOTA-GR-SUPRA-2024')->where('status', 'tersedia')->firstOrFail();
            $showcaseImage = asset('assets/images/catalog/toyota-gr-supra/front.png');
            return view('mobil.show', ['mobil' => $mobil, 'showcaseImage' => $showcaseImage, 'galleryData' => app(VehicleGalleryService::class)->for($mobil, $showcaseImage), 'defaultColorKey' => app(VehicleGalleryService::class)->defaultColor($mobil), ...$this->detailNavigation($request)]);
        }

        if ($slug === 'toyota-supra') {
            return redirect()->route('mobil.showcase', array_merge(['slug' => 'toyota-supra-mk4'], $request->query()), 301);
        }

        if ($slug === 'toyota-supra-mk4') {
            $mobil = Mobil::query()->whereIn('kode_mobil', ['YZA-TOYOTA-SUPRA-MK4-2025', 'YZA-TOYOTA-SUPRA-3-0-2025'])->where('status', 'tersedia')->firstOrFail();
            $showcaseImage = asset('assets/images/catalog/toyota-supra-mk4/front.png');
            return view('mobil.show', ['mobil' => $mobil, 'showcaseImage' => $showcaseImage, 'galleryData' => app(VehicleGalleryService::class)->for($mobil, $showcaseImage), 'defaultColorKey' => app(VehicleGalleryService::class)->defaultColor($mobil), ...$this->detailNavigation($request)]);
        }

        $showcases = [
            'audi-r8' => ['merk' => 'Audi', 'model' => 'R8', 'tipe' => 'Performance', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 1850000000, 'kondisi' => 'baru', 'warna' => 'Ibis White', 'bahan_bakar' => 'bensin', 'kode_mobil' => 'SHOWCASE-AUDI-R8', 'deskripsi' => 'Audi R8 Performance dengan karakter sporty, kabin premium, dan performa yang dirancang untuk pengalaman berkendara berkelas.', 'image' => asset('assets/images/catalog/sport/audi-r8.png')],
            'bmw-m4' => ['merk' => 'BMW', 'model' => 'M4', 'tipe' => 'Competition', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 2100000000, 'kondisi' => 'baru', 'warna' => 'Toronto Red', 'bahan_bakar' => 'bensin', 'kode_mobil' => 'SHOWCASE-BMW-M4', 'deskripsi' => 'BMW M4 Competition yang agresif, presisi, dan tetap nyaman untuk penggunaan harian dengan sentuhan performa M.', 'image' => asset('assets/images/catalog/sport/bmw-m4.png')],
            'bugatti-chiron' => ['merk' => 'Bugatti', 'model' => 'Chiron', 'tipe' => 'Super Sport', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 68000000000, 'kondisi' => 'baru', 'warna' => 'Atlantic Blue', 'bahan_bakar' => 'bensin', 'kode_mobil' => 'SHOWCASE-BUGATTI-CHIRON', 'deskripsi' => 'Bugatti Chiron Super Sport untuk Anda yang menginginkan kemewahan ekstrem, detail ikonik, dan performa tanpa kompromi.', 'image' => asset('assets/images/catalog/sport/bugatti-chiron.png')],
            'mclaren-720s' => ['merk' => 'McLaren', 'model' => '720S', 'tipe' => 'Performance', 'tahun' => 2024, 'transmisi' => 'automatic', 'harga_jual' => 7900000000, 'kondisi' => 'baru', 'warna' => 'Papaya Spark', 'bahan_bakar' => 'bensin', 'kode_mobil' => 'SHOWCASE-MCLAREN-720S', 'deskripsi' => 'McLaren 720S Performance dengan siluet eksotis, respons tajam, dan pengalaman berkendara yang sangat dinamis.', 'image' => asset('assets/images/catalog/sport/mclaren-720s.png')],
        ];
        abort_unless(isset($showcases[$slug]), 404);
        $inventory = $this->showcaseInventory($slug);
        if ($inventory) {
            $showcaseImage = $showcases[$slug]['image'];
            $inventory->setAttribute('showcase_slug', $slug);
            $inventory->setAttribute('image', $showcaseImage);
            $galleryService = app(VehicleGalleryService::class);
            return view('mobil.show', ['mobil' => $inventory, 'showcaseImage' => $showcaseImage, 'galleryData' => $galleryService->for($inventory, $showcaseImage), 'defaultColorKey' => $galleryService->defaultColor($inventory), ...$this->detailNavigation($request)]);
        }
        $data = $showcases[$slug];
        $showcaseImage = $data['image'];
        unset($data['image']);
        $mobil = new Mobil($data);
        $mobil->setAttribute('status', 'tersedia');
        return view('mobil.show', ['mobil' => $mobil, 'showcaseImage' => $showcaseImage, 'galleryData' => app(VehicleGalleryService::class)->for($mobil, $showcaseImage), 'defaultColorKey' => app(VehicleGalleryService::class)->defaultColor($mobil), ...$this->detailNavigation($request)]);
    }

    private function detailNavigation(Request $request): array
    {
        if ($request->query('source') === 'landing') {
            return ['backUrl' => url('/#mobil'), 'backLabel' => 'Kembali ke Showroom'];
        }

        $returnTo = (string) $request->query('return_to', '');
        if ($request->query('source') === 'catalog' && $this->isSafeCatalogReturn($returnTo)) {
            return ['backUrl' => $returnTo, 'backLabel' => 'Kembali ke Semua Mobil'];
        }

        return ['backUrl' => route('mobil.index'), 'backLabel' => 'Kembali ke Semua Mobil'];
    }

    private function isSafeCatalogReturn(string $returnTo): bool
    {
        if ($returnTo === '' || str_starts_with($returnTo, '//') || str_contains($returnTo, '\\')) {
            return false;
        }

        $parts = parse_url($returnTo);
        $path = $parts['path'] ?? '';

        return ($parts['scheme'] ?? null) === null
            && ($parts['host'] ?? null) === null
            && ($parts['user'] ?? null) === null
            && ($parts['pass'] ?? null) === null
            && ($parts['port'] ?? null) === null
            && ($path === '/mobil' || str_starts_with($path, '/mobil/'));
    }
}
