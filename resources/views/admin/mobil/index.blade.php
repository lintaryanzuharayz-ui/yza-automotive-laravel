@extends('layouts.app')

@section('title', 'Manajemen Mobil & Showroom - Admin YZA Automotive')
@section('admin-page-title', 'Manajemen Mobil & Showroom')

@section('content')
@php
    $resolveCarThumbnail = static function ($car) {
        if ($car->foto && $car->foto->isNotEmpty()) {
            $first = $car->foto->first();
            if ($first && $first->existsOnDisk()) {
                return $first->url();
            }
        }
        $slug = \Illuminate\Support\Str::slug($car->merk . ' ' . $car->model);
        $premiumMap = [
            'lexus-lfa' => 'lexus-lfa/pearl-white/front.png',
            'nissan-gt-r-nismo' => 'nissan-gt-r-nismo/nismo-stealth-gray/front.png',
            'dodge-challenger-srt-hellcat-redeye' => 'dodge-challenger-srt-hellcat-redeye/torred/front.png',
            'ford-mustang-shelby-gt500' => 'ford-mustang-shelby-gt500/grabber-blue/front.png',
            'chevrolet-corvette-z06' => 'chevrolet-corvette-z06/torch-red/front.png',
            'aston-martin-dbs-770-ultimate' => 'aston-martin-dbs-770-ultimate/minotaur-green/front.png',
            'mercedes-amg-gt-black-series' => 'mercedes-amg-gt-black-series/magno-grey/front.png',
            'porsche-911-gt3-rs' => 'porsche-911-gt3-rs/guards-red/front.png',
            'ferrari-sf90-stradale' => 'ferrari-sf90-stradale/rosso-corsa/front.png',
            'lamborghini-revuelto' => 'lamborghini-revuelto/rosso-mars/front.png',
        ];
        if (isset($premiumMap[$slug])) {
            $path = 'assets/images/vehicles/premium/' . $premiumMap[$slug];
            if (file_exists(public_path($path))) {
                return asset($path);
            }
        }
        $modelSlug = \Illuminate\Support\Str::slug($car->model);
        $stdFolders = ['avanza', 'brio', 'civic', 'fortuner', 'hrv', 'innova-zenix', 'mobilio', 'raize', 'toyota-gr-supra', 'toyota-supra-mk4', 'xenia', 'xl7', 'xpander', 'audi-r8', 'bmw-m4', 'bugatti-chiron', 'mclaren-720s'];
        foreach ($stdFolders as $folder) {
            if ($modelSlug === $folder || str_contains($modelSlug, $folder) || str_contains($slug, $folder)) {
                $glob = glob(public_path('assets/images/vehicles/' . $folder . '/*/front.png'));
                if (!empty($glob)) {
                    $rel = str_replace(public_path() . DIRECTORY_SEPARATOR, '', $glob[0]);
                    $rel = str_replace('\\', '/', $rel);
                    return asset($rel);
                }
            }
        }
        return asset('assets/images/hero/yza-hero-black-v2.png');
    };

    $activeTab = $activeTab ?? request('tab', 'inventori');
    if (!in_array($activeTab, ['inventori', 'katalog', 'foto'], true)) {
        $activeTab = 'inventori';
    }

    $isOwner = auth()->user()?->roleKey() === 'owner';
@endphp

<div class="admin-vehicle-management-page">
    {{-- Header Terpadu Manajemen Mobil & Showroom --}}
    <header class="vehicle-header">
        <div class="vehicle-header-left">
            <nav class="vehicle-breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-back-arrow" aria-hidden="true">&lsaquo;</span>
                <span class="breadcrumb-item">Penjualan Mobil</span>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current">Manajemen Mobil &amp; Showroom</span>
            </nav>

            <div class="vehicle-title-group">
                <div class="vehicle-title-icon" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m4 10 2-6h12l2 6"></path>
                        <path d="M3 11h18v9h-3v-3H6v3H3v-9Z"></path>
                        <path d="M9 14h6"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="vehicle-heading">Manajemen Mobil &amp; Showroom</h1>
                    <p class="vehicle-subheading">Kelola kendaraan, stok, warna, galeri, dan tampilan showroom YZA Automotive dalam satu halaman.</p>
                </div>
            </div>
        </div>

        <div class="vehicle-header-right">
            @if(!$isOwner)
                <button type="button" class="btn-primary-action" onclick="openVehicleActionModal('new', 'add_unit')" title="Daftarkan Unit Mobil Baru">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Tambah Mobil</span>
                </button>
            @else
                <span class="owner-readonly-badge" title="Mode Baca Saja (Owner)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Owner (Read-Only)</span>
                </span>
            @endif
        </div>
    </header>

    @if(session('success'))
        <div class="admin-alert is-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Enam Kartu Statistik Inventori & Showroom Kompak --}}
    <section class="vehicle-kpi-grid" aria-label="Statistik Kendaraan & Showroom">
        {{-- Card 1: Total Model Katalog --}}
        <a class="vehicle-kpi-card is-red" href="{{ route('admin.mobil.index', ['tab' => $activeTab]) }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m4 10 2-6h12l2 6"></path>
                    <path d="M3 11h18v9h-3v-3H6v3H3v-9Z"></path>
                    <path d="M9 14h6"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Model Katalog</span>
                <strong class="kpi-value" id="kpiTotalUnit">{{ number_format($stats['total_model_katalog'] ?? $stats['total_unit']) }}</strong>
                <div class="kpi-subtext">
                    <span>{{ number_format($stats['model_berinventori'] ?? 26) }} Total Kendaraan berinventori</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Unit Fisik Tercatat --}}
        <div class="vehicle-kpi-card is-blue">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                    <path d="M3 9h18M3 15h18M9 3v18M15 3v18"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Unit Fisik Tercatat</span>
                <strong class="kpi-value" id="kpiTotalFisik">{{ number_format($stats['unit_fisik_tercatat'] ?? $stats['total_unit_fisik']) }}</strong>
                <div class="kpi-subtext">
                    <span>{{ number_format($stats['stok_menipis']) }} model Stok Menipis (&le; 3 unit)</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Unit Ready (Tersedia) --}}
        <a class="vehicle-kpi-card is-green" href="{{ route('admin.mobil.index', ['tab' => $activeTab, 'status' => 'ready']) }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Unit Ready</span>
                <strong class="kpi-value" id="kpiTersedia">{{ number_format($stats['unit_ready'] ?? $stats['tersedia']) }}</strong>
                <div class="kpi-subtext is-up">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Kendaraan Tersedia siap dibeli</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Reservasi Aktif --}}
        <a class="vehicle-kpi-card is-amber" href="{{ route('admin.pesanan-mobil.index') }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Reservasi Aktif</span>
                <strong class="kpi-value" id="kpiDipesan">{{ number_format($stats['reservasi_aktif'] ?? $stats['dipesan']) }}</strong>
                <div class="kpi-subtext">
                    <span>Kendaraan Dipesan &amp; booking member</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Pre-Order Aktif --}}
        <a class="vehicle-kpi-card is-cyan" href="{{ route('admin.preorder-mobil.index') }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="9"></circle>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Pre-Order Aktif</span>
                <strong class="kpi-value">{{ number_format($stats['preorder']) }}</strong>
                <div class="kpi-subtext">
                    <span>Permintaan pre-order aktif</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Kendaraan Terjual --}}
        <a class="vehicle-kpi-card is-orange" href="{{ route('admin.mobil.index', ['tab' => $activeTab, 'status' => 'terjual']) }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Kendaraan Terjual</span>
                <strong class="kpi-value" id="kpiTerjual">{{ number_format($stats['terjual']) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit berhasil diserahterimakan</span>
                </div>
            </div>
        </a>
    </section>

    {{-- Tabs Navigation Bar --}}
    <nav class="vehicle-tabs-nav" aria-label="Tab Manajemen Mobil & Showroom">
        <button type="button" class="vehicle-tab-btn {{ $activeTab === 'inventori' ? 'is-active' : '' }}" data-tab-target="tab-inventori" aria-selected="{{ $activeTab === 'inventori' ? 'true' : 'false' }}" role="tab">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                <path d="M3 9h18M3 15h18M9 3v18M15 3v18"></path>
            </svg>
            <span>Inventori &amp; Stok</span>
            <span class="tab-badge">{{ $cars->total() }}</span>
        </button>

        <button type="button" class="vehicle-tab-btn {{ $activeTab === 'katalog' ? 'is-active' : '' }}" data-tab-target="tab-katalog" aria-selected="{{ $activeTab === 'katalog' ? 'true' : 'false' }}" role="tab">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="7" x="3" y="3" rx="1"></rect>
                <rect width="7" height="7" x="14" y="3" rx="1"></rect>
                <rect width="7" height="7" x="14" y="14" rx="1"></rect>
                <rect width="7" height="7" x="3" y="14" rx="1"></rect>
            </svg>
            <span>Katalog &amp; Showroom</span>
            <span class="tab-badge">{{ $catalogCars->count() }}</span>
        </button>

        <button type="button" class="vehicle-tab-btn {{ $activeTab === 'foto' ? 'is-active' : '' }}" data-tab-target="tab-foto" aria-selected="{{ $activeTab === 'foto' ? 'true' : 'false' }}" role="tab">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                <path d="M2 12h20"></path>
            </svg>
            <span>Foto &amp; Warna</span>
            <span class="tab-badge">Galeri &amp; Aset</span>
        </button>
    </nav>

    {{-- Main Container Card --}}
    <main class="vehicle-main-card">
        {{-- ========================================================== --}}
        {{-- ========================================================== --}}
        {{-- TAB 1: INVENTORI & STOK (TABEL UTAMA TERPADU)              --}}
        {{-- ========================================================== --}}
        <div id="tab-inventori" class="vehicle-tab-pane {{ $activeTab === 'inventori' ? 'is-active' : '' }}" role="tabpanel">

            {{-- Konteks Pre-Order jika Navigasi dari Modul Pre-Order --}}
            @if(request()->integer('preorder'))
                <div class="preorder-context-banner" role="status">
                    <div class="context-banner-text">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
                        <span>Menampilkan konteks permintaan Pre-Order <strong>#{{ request()->integer('preorder') }}</strong>. Tambahkan unit ke inventori untuk memenuhi permintaan ini.</span>
                    </div>
                    <a href="{{ route('admin.preorder-mobil.index') }}" class="btn-context-back">&larr; Kembali ke Daftar Pre-Order</a>
                </div>
            @endif

            {{-- Konteks Filter Model Katalog jika Aktif --}}
            @if($catalogKey && $catalogModel)
                <div class="catalog-filter-context-banner" role="status">
                    <div class="context-banner-text">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        <span>Memfilter spesifik model katalog: <strong>{{ $catalogModel->merk }} {{ $catalogModel->model }}</strong> ({{ $catalogKey }}).</span>
                    </div>
                    <a href="{{ route('admin.mobil.index', ['tab' => 'inventori']) }}" class="btn-context-back">Tampilkan Semua Mobil</a>
                </div>
            @endif

            {{-- Toolbar Filter Sejajar Horizontal --}}
            <form class="vehicle-filter-toolbar" method="get" action="{{ route('admin.mobil.index') }}">
                <input type="hidden" name="tab" value="inventori">
                @if($catalogKey)<input type="hidden" name="catalog" value="{{ $catalogKey }}">@endif
                @if(request()->integer('preorder'))<input type="hidden" name="preorder" value="{{ request()->integer('preorder') }}">@endif
                @if(request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif

                {{-- Input Pencarian --}}
                <div class="filter-wrap search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" name="q" value="{{ $q }}" class="filter-input" placeholder="Cari merek, model, varian, kode mobil, VIN...">
                </div>

                {{-- Dropdown Merek --}}
                <div class="filter-wrap select-box select-brand">
                    <select name="merk" class="filter-select">
                        <option value="">Semua Merek</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand }}" @selected($merk === $brand)>{{ $brand }}</option>
                        @endforeach
                    </select>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>

                {{-- Dropdown Status & Ketersediaan --}}
                <div class="filter-wrap select-box select-status">
                    <select name="status" class="filter-select">
                        <option value="">Semua Mobil</option>
                        <option value="ready" @selected($status === 'ready' || $status === 'tersedia')>Ready (Stok Tersedia)</option>
                        <option value="habis_atau_belum_ada" @selected($status === 'habis_atau_belum_ada')>Stok Habis / Belum Ada Unit</option>
                        <option value="ada_preorder" @selected($status === 'ada_preorder')>Ada Pre-Order Aktif</option>
                        <option value="tidak_tayang" @selected($status === 'tidak_tayang' || $status === 'nonaktif')>Tidak Tayang</option>
                        <option value="dibooking" @selected($status === 'dibooking')>Dipesan (Booking)</option>
                        <option value="terjual" @selected($status === 'terjual')>Terjual</option>
                    </select>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>

                {{-- Kondisi --}}
                <div class="filter-wrap select-box select-kondisi">
                    <select name="kondisi" class="filter-select">
                        <option value="">Semua Kondisi</option>
                        <option value="baru" @selected($kondisi === 'baru')>Baru</option>
                        <option value="bekas" @selected($kondisi === 'bekas')>Bekas</option>
                    </select>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>

                {{-- Tombol Aksi --}}
                <div class="filter-actions-group">
                    <button type="submit" class="btn-filter-apply">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <span>Terapkan</span>
                    </button>
                    <a href="{{ route('admin.mobil.index', ['tab' => 'inventori']) }}" class="btn-filter-reset">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                        <span>Reset</span>
                    </a>
                </div>
            </form>

            {{-- Tabel Utama Terpadu Manajemen Mobil & Showroom --}}
            <div class="vehicle-table-responsive" id="vehicleTableScrollContainer">
                <table class="vehicle-table">
                    <thead>
                        <tr>
                            <th class="th-check" style="width: 38px; text-align: center;">
                                <input type="checkbox" id="checkAllInventory" class="custom-checkbox" title="Pilih Semua">
                            </th>
                            <th class="th-car" style="min-width: 250px;">KENDARAAN &amp; KODE</th>
                            <th class="th-price" style="min-width: 120px;">HARGA</th>
                            <th class="th-catalog-status" style="min-width: 110px; text-align: center;">STATUS KATALOG</th>
                            <th class="th-stock-physical" style="min-width: 90px; text-align: center;">STOK FISIK</th>
                            <th class="th-stock-ready" style="min-width: 95px; text-align: center;">STOK READY</th>
                            <th class="th-reserved" style="min-width: 90px; text-align: center;">RESERVASI AKTIF</th>
                            <th class="th-preorder" style="min-width: 105px; text-align: center;">PRE-ORDER AKTIF</th>
                            <th class="th-availability" style="min-width: 125px; text-align: center;">KETERSEDIAAN</th>
                            <th class="th-action sticky-col" style="width: 72px; min-width: 72px; text-align: center;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $renderedAny = false;
                            $identity = app(\App\Services\CatalogVehicleIdentityService::class);
                        @endphp

                        {{-- 1. DAFTAR MODEL DENGAN UNIT FISIK (DATABASE INVENTORI) --}}
                        @foreach($cars as $mobil)
                            @php
                                $renderedAny = true;
                                $availability = $mobil->getAttribute('availability') ?? [];
                                $stockTotal = $availability['stock_total'] ?? (($availability['stock'] ?? null) !== null ? $availability['stock'] : 0);
                                $stockOrderable = $availability['stock'] ?? 0;
                                $reservedUnits = $availability['reserved_units'] ?? ($availability['active_reservation_count'] ?? 0);
                                $soldUnits = $availability['sold_units'] ?? 0;
                                $fotoUrl = $resolveCarThumbnail($mobil);
                                $cleanStatus = strtolower((string) $mobil->status);
                                $invHistory = isset($inventories[$mobil->id_mobil]) ? $inventories[$mobil->id_mobil]->histories : collect();
                                $activeOrdersList = $ordersByCar[$mobil->id_mobil] ?? collect();
                                $activeBookingsList = $bookingsByCar[$mobil->id_mobil] ?? collect();
                                $publicPreviewUrl = $mobil->showcase_slug
                                    ? route('mobil.showcase', ['slug' => $mobil->showcase_slug, 'source' => 'showroom'])
                                    : route('mobil.show', $mobil);

                                $angles = [
                                    'front' => ['label' => 'Depan (Front 3/4)', 'path' => null],
                                    'side' => ['label' => 'Samping (Side Profile)', 'path' => null],
                                    'rear' => ['label' => 'Belakang (Rear 3/4)', 'path' => null],
                                    'interior' => ['label' => 'Kabin / Interior', 'path' => null],
                                ];
                                $defaultColor = $mobil->warnaPilihan->firstWhere('is_default', true) ?: $mobil->warnaPilihan->first();
                                if ($defaultColor && $defaultColor->relationLoaded('galeri')) {
                                    foreach ($defaultColor->galeri as $galeriItem) {
                                        if (isset($angles[$galeriItem->sudut]) && is_file(public_path($galeriItem->path))) {
                                            $angles[$galeriItem->sudut]['path'] = asset($galeriItem->path);
                                        }
                                    }
                                }
                                $slug = \Illuminate\Support\Str::slug($mobil->merk . ' ' . $mobil->model);
                                foreach (['front', 'side', 'rear', 'interior'] as $angleKey) {
                                    if (!$angles[$angleKey]['path']) {
                                        $candidate = "assets/images/vehicles/{$slug}/{$angleKey}.png";
                                        if (file_exists(public_path($candidate))) {
                                            $angles[$angleKey]['path'] = asset($candidate);
                                        }
                                    }
                                }
                                if (!$angles['front']['path'] && $mobil->foto->isNotEmpty()) {
                                    $angles['front']['path'] = $mobil->foto->first()->url();
                                }

                                $carDataPayload = [
                                    'id' => $mobil->id_mobil,
                                    'kode' => $mobil->kode_mobil,
                                    'merk' => $mobil->merk,
                                    'model' => $mobil->model,
                                    'tipe' => $mobil->tipe ?: '',
                                    'tahun' => (int) $mobil->tahun,
                                    'warna' => $mobil->warna ?: '',
                                    'transmisi' => $mobil->transmisi ?: '',
                                    'bahan_bakar' => $mobil->bahan_bakar ?: '',
                                    'nomor_rangka' => $mobil->nomor_rangka ?: '',
                                    'nomor_mesin' => $mobil->nomor_mesin ?: '',
                                    'kilometer' => (int) $mobil->kilometer,
                                    'harga_beli' => (float) $mobil->harga_beli,
                                    'harga_jual' => (float) $mobil->harga_jual,
                                    'harga_jual_formatted' => 'Rp ' . number_format((float) $mobil->harga_jual, 0, ',', '.'),
                                    'kondisi' => $mobil->kondisi,
                                    'status' => $mobil->status,
                                    'is_limited_edition' => (bool) $mobil->is_limited_edition,
                                    'showcase_slug' => $mobil->showcase_slug ?: '',
                                    'deskripsi' => $mobil->deskripsi ?: '',
                                    'tanggal_masuk' => optional($mobil->tanggal_masuk)->format('Y-m-d') ?: '',
                                    'thumbnail' => $fotoUrl,
                                    'color_stocks' => array_values(app(\App\Services\VehicleColorStockService::class)->colors($mobil)),
                                    'stock_total' => $stockTotal,
                                    'stock_orderable' => $stockOrderable,
                                    'reserved_units' => $reservedUnits,
                                    'sold_units' => $soldUnits,
                                    'is_sold' => $mobil->status === 'terjual',
                                    'photos' => $mobil->foto->map(fn($f) => [
                                        'id' => $f->id_foto,
                                        'url' => $f->url(),
                                        'is_primary' => (bool) $f->foto_utama,
                                        'primary_url' => route('admin.mobil.foto.primary', [$mobil, $f]),
                                        'destroy_url' => route('admin.mobil.foto.destroy', [$mobil, $f]),
                                    ])->values(),
                                    'colors' => $mobil->warnaPilihan->map(fn($w) => [
                                        'id' => $w->id_warna,
                                        'nama' => $w->nama,
                                        'hex' => $w->hex,
                                        'slug' => $w->slug,
                                        'is_default' => (bool) $w->is_default,
                                    ])->values(),
                                    'angles' => $angles,
                                    'histories' => $invHistory->map(fn($h) => [
                                        'tanggal' => optional($h->created_at)->format('d M Y, H:i') ?: '—',
                                        'jenis' => ucwords(str_replace('_', ' ', $h->jenis)),
                                        'jumlah' => $h->jumlah_perubahan > 0 ? ('+' . $h->jumlah_perubahan) : $h->jumlah_perubahan,
                                        'before_after' => $h->stock_before . ' → ' . $h->stock_after,
                                        'catatan' => $h->catatan ?: 'Pergerakan sistem',
                                    ])->values(),
                                    'active_orders' => $activeOrdersList->map(fn($o) => [
                                        'nomor' => $o->nomor_pesanan,
                                        'nama' => $o->nama_pembeli,
                                        'status' => ucwords(str_replace('_', ' ', $o->status)),
                                    ])->values(),
                                    'active_bookings' => $activeBookingsList->map(fn($b) => [
                                        'kode' => $b->kode_booking,
                                        'status' => ucwords(str_replace('_', ' ', $b->status)),
                                    ])->values(),
                                    'urls' => [
                                        'show' => route('admin.mobil.show', $mobil),
                                        'stock_adjust' => route('admin.mobil.stock.adjust', $mobil),
                                        'update' => route('admin.mobil.update', $mobil),
                                        'deactivate' => route('admin.mobil.deactivate', $mobil),
                                        'photo_store' => route('admin.mobil.foto.store', $mobil),
                                        'public_preview' => $publicPreviewUrl,
                                    ],
                                ];
                                $identity = app(\App\Services\CatalogVehicleIdentityService::class);
                                $matchingPreorderModels = $identity->activePreordersList($mobil, $allActivePreorders ?? null);
                                $activePoCount = $matchingPreorderModels->count();
                                $matchingPreorders = $matchingPreorderModels->map(function ($po) {
                                    return [
                                        'id_preorder' => $po->getKey(),
                                        'kode_preorder' => 'PRE-' . str_pad((string) $po->getKey(), 5, '0', STR_PAD_LEFT),
                                        'customer_name' => $po->pelanggan?->nama_lengkap ?: 'Member YZA',
                                        'customer_code' => $po->pelanggan?->kode_pelanggan ?: 'MEM',
                                        'customer_phone' => $po->pelanggan?->nomor_telepon ?: ($po->pelanggan?->no_hp ?: '—'),
                                        'customer_email' => $po->pelanggan?->email ?: ($po->pelanggan?->user?->email ?: '—'),
                                        'vehicle' => $po->nama_mobil,
                                        'variant' => $po->varian ?: 'Standar',
                                        'year' => (int) ($po->tahun ?: date('Y')),
                                        'color' => $po->warna ?: 'Standar',
                                        'status' => $po->status,
                                        'status_label' => ucfirst(str_replace('_', ' ', $po->status)),
                                        'created_at' => optional($po->created_at)->format('d M Y, H:i') ?: '',
                                        'detail_url' => route('admin.preorder-mobil.detail', $po),
                                    ];
                                })->values()->all();
                                $carDataPayload['preorders'] = $matchingPreorders;
                            @endphp
                            <tr id="row-car-{{ $mobil->id_mobil }}" data-car-id="{{ $mobil->id_mobil }}">
                                <script type="application/json" id="car-data-{{ $mobil->id_mobil }}">@json($carDataPayload)</script>

                                {{-- Checkbox --}}
                                <td class="td-check" style="text-align: center;">
                                    <input type="checkbox" class="inventory-row-check custom-checkbox" value="{{ $mobil->id_mobil }}">
                                </td>

                                {{-- Kendaraan & Kode (Termasuk VIN & Mesin dalam Subline) --}}
                                <td class="td-car">
                                    <div class="car-cell">
                                        <div class="car-thumb-box">
                                            <img src="{{ $fotoUrl }}" alt="{{ $mobil->merk }} {{ $mobil->model }}" class="car-thumb-img" loading="lazy">
                                        </div>
                                        <div class="car-meta">
                                            <div class="car-title-wrapper">
                                                <button type="button" class="car-title-btn" onclick="openVehicleActionModal({{ $mobil->id_mobil }}, 'detail')" title="Lihat detail mobil">
                                                    <strong class="car-title">{{ $mobil->merk }} {{ $mobil->model }}</strong>
                                                </button>
                                                <span class="car-code-pill">{{ $mobil->kode_mobil }}</span>
                                            </div>
                                            <span class="car-subtext">{{ $mobil->tipe ?: 'Varian Standar' }} &bull; {{ $mobil->tahun }} &bull; {{ $mobil->warna }}</span>
                                            <div class="car-vin-subline">
                                                <span class="vin-badge" title="Nomor Rangka (VIN)"><strong>VIN:</strong> {{ $mobil->nomor_rangka ?: '—' }}</span>
                                                <span class="vin-separator">&bull;</span>
                                                <span class="engine-badge" title="Nomor Mesin"><small>Mesin:</small> {{ $mobil->nomor_mesin ?: '—' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Harga Jual --}}
                                <td class="td-price">
                                    <strong>Rp {{ number_format((float) $mobil->harga_jual, 0, ',', '.') }}</strong>
                                </td>

                                {{-- Status Katalog (Pengganti Kolom Showroom) --}}
                                <td class="td-catalog-status" style="text-align: center;">
                                    @if($cleanStatus === 'nonaktif')
                                        <span class="badge-catalog-status is-unpublished" title="Kendaraan tidak ditayangkan pada katalog website">Tidak Tayang</span>
                                    @else
                                        <span class="badge-catalog-status is-published" title="Kendaraan aktif ditayangkan pada katalog website">Tayang</span>
                                        @if($mobil->showcase_slug)
                                            <span class="catalog-slug-pill" title="Showcase: {{ $mobil->showcase_slug }}">{{ $mobil->showcase_slug }}</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Stok Fisik --}}
                                <td class="td-stock-physical" style="text-align: center;">
                                    <strong>{{ $stockTotal }}</strong> unit
                                </td>

                                {{-- Stok Ready (Dapat Dipesan) --}}
                                <td class="td-stock-ready" style="text-align: center;">
                                    @if($stockOrderable > 0)
                                        <span class="stock-ready-badge is-ready">{{ $stockOrderable }} unit</span>
                                    @else
                                        <span class="stock-ready-badge is-zero">0 unit</span>
                                    @endif
                                </td>

                                {{-- Reservasi Aktif --}}
                                <td class="td-reserved" style="text-align: center;">
                                    @if($reservedUnits > 0)
                                        <span class="reserved-count-badge">{{ $reservedUnits }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>

                                {{-- Pre-Order Aktif --}}
                                <td class="td-preorder" style="text-align: center;">
                                    <button type="button" class="preorder-count-badge {{ $activePoCount > 0 ? 'is-active' : 'is-zero' }}" data-action="preorder" onclick="openVehicleActionModal({{ $mobil->id_mobil }}, 'preorder')" title="Buka Permintaan Pre-Order">
                                        {{ $activePoCount }} permintaan
                                    </button>
                                </td>

                                {{-- Ketersediaan --}}
                                <td class="td-availability" style="text-align: center;">
                                    @if($cleanStatus === 'tersedia' && $stockOrderable > 0 && ($availability['can_purchase'] ?? false))
                                        <span class="badge-availability is-ready">Ready</span>
                                    @elseif($cleanStatus === 'tersedia')
                                        <span class="badge-availability is-exhausted">Stok Habis</span>
                                    @elseif($cleanStatus === 'dibooking')
                                        <span class="badge-availability is-booked">Dipesan</span>
                                    @elseif($cleanStatus === 'terjual')
                                        <span class="badge-availability is-sold">Terjual</span>
                                    @elseif($cleanStatus === 'nonaktif')
                                        <span class="badge-availability is-inactive">Nonaktif</span>
                                    @else
                                        <span class="badge-availability is-other">{{ ucfirst($cleanStatus) }}</span>
                                    @endif
                                </td>

                                {{-- Aksi (Sticky Column — Single Three-Dot Menu) --}}
                                <td class="td-action sticky-col" style="text-align: center;">
                                    <div class="action-single-wrap">
                                        <button type="button"
                                                class="btn-more-options"
                                                onclick="toggleDropdownFixed(this, {{ $mobil->id_mobil }})"
                                                aria-label="Menu Opsi Kendaraan {{ $mobil->merk }} {{ $mobil->model }}"
                                                aria-haspopup="true"
                                                aria-expanded="false"
                                                title="Menu Opsi Kendaraan">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1.5"></circle><circle cx="19" cy="12" r="1.5"></circle><circle cx="5" cy="12" r="1.5"></circle></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- 2. DAFTAR MODEL KATALOG TANPA UNIT FISIK (TETAP TAMPIL DI TABEL UTAMA) --}}
                        @if(($virtualCatalogCars ?? collect())->isNotEmpty() && ($cars->currentPage() === 1 || $cars->isEmpty()))
                            @foreach($virtualCatalogCars as $model)
                                @php
                                    $renderedAny = true;
                                    $identity = app(\App\Services\CatalogVehicleIdentityService::class);
                                    $vKey = $identity->key($model);
                                    $vMatchingPreorderModels = $identity->activePreordersList($model, $allActivePreorders ?? null);
                                    $vActivePo = $vMatchingPreorderModels->count();
                                    $vPhotoUrl = $model->image ?: $resolveCarThumbnail($model);

                                    $vMatchingPreorders = $vMatchingPreorderModels->map(function ($po) {
                                        return [
                                            'id_preorder' => $po->getKey(),
                                            'kode_preorder' => 'PRE-' . str_pad((string) $po->getKey(), 5, '0', STR_PAD_LEFT),
                                            'customer_name' => $po->pelanggan?->nama_lengkap ?: 'Member YZA',
                                            'customer_code' => $po->pelanggan?->kode_pelanggan ?: 'MEM',
                                            'customer_phone' => $po->pelanggan?->nomor_telepon ?: ($po->pelanggan?->no_hp ?: '—'),
                                            'customer_email' => $po->pelanggan?->email ?: ($po->pelanggan?->user?->email ?: '—'),
                                            'vehicle' => $po->nama_mobil,
                                            'variant' => $po->varian ?: 'Standar',
                                            'year' => (int) ($po->tahun ?: date('Y')),
                                            'color' => $po->warna ?: 'Standar',
                                            'status' => $po->status,
                                            'status_label' => ucfirst(str_replace('_', ' ', $po->status)),
                                            'created_at' => optional($po->created_at)->format('d M Y, H:i') ?: '',
                                            'detail_url' => route('admin.preorder-mobil.detail', $po),
                                        ];
                                    })->values()->all();

                                    $vCarDataPayload = [
                                        'id' => $vKey,
                                        'kode' => $vKey,
                                        'catalog_key' => $vKey,
                                        'merk' => $model->merk,
                                        'model' => $model->model,
                                        'tipe' => $model->tipe ?: '',
                                        'tahun' => (int) $model->tahun,
                                        'warna' => $model->warna ?: '',
                                        'transmisi' => $model->transmisi ?: 'automatic',
                                        'bahan_bakar' => $model->bahan_bakar ?: 'bensin',
                                        'nomor_rangka' => '',
                                        'nomor_mesin' => '',
                                        'kilometer' => 0,
                                        'harga_beli' => 0,
                                        'harga_jual' => (float) $model->harga_jual,
                                        'harga_jual_formatted' => 'Rp ' . number_format((float) $model->harga_jual, 0, ',', '.'),
                                        'kondisi' => $model->kondisi ?: 'baru',
                                        'status' => 'belum_ada_unit',
                                        'status_label' => 'Belum Ada Unit Fisik',
                                        'status_katalog' => 'Tayang',
                                        'is_virtual' => true,
                                        'is_limited_edition' => (bool) ($model->is_limited_edition ?? false),
                                        'showcase_slug' => $model->showcase_slug ?: '',
                                        'deskripsi' => $model->deskripsi ?: 'Model resmi katalog showroom YZA Automotive. Belum memiliki alokasi unit fisik di dealer.',
                                        'tanggal_masuk' => '',
                                        'thumbnail' => $vPhotoUrl,
                                        'color_stocks' => array_values(app(\App\Services\VehicleColorStockService::class)->colors($model)),
                                        'stock_total' => 0,
                                        'stock_orderable' => 0,
                                        'reserved_units' => 0,
                                        'sold_units' => 0,
                                        'is_sold' => false,
                                        'photos' => [],
                                        'colors' => [],
                                        'angles' => [
                                            'front' => ['label' => 'Depan (Front 3/4)', 'path' => $vPhotoUrl],
                                            'side' => ['label' => 'Samping (Side Profile)', 'path' => null],
                                            'rear' => ['label' => 'Belakang (Rear 3/4)', 'path' => null],
                                            'interior' => ['label' => 'Kabin / Interior', 'path' => null],
                                        ],
                                        'histories' => [],
                                        'active_orders' => [],
                                        'active_bookings' => [],
                                        'preorders' => $vMatchingPreorders,
                                        'urls' => [
                                            'store' => route('admin.mobil.store'),
                                        ],
                                    ];
                                @endphp
                                <tr id="row-virtual-{{ $model->showcase_slug }}" class="is-virtual-model-row" data-catalog-key="{{ $vKey }}">
                                    <script type="application/json" id="car-data-{{ $vKey }}">@json($vCarDataPayload)</script>

                                    {{-- Checkbox --}}
                                    <td class="td-check" style="text-align: center;">
                                        <input type="checkbox" class="inventory-row-check custom-checkbox" value="{{ $vKey }}">
                                    </td>

                                    {{-- Kendaraan & Kode --}}
                                    <td class="td-car">
                                        <div class="car-cell">
                                            <div class="car-thumb-box is-virtual-thumb">
                                                <img src="{{ $vPhotoUrl }}" alt="{{ $model->merk }} {{ $model->model }}" class="car-thumb-img" loading="lazy">
                                            </div>
                                            <div class="car-meta">
                                                <div class="car-title-wrapper">
                                                    <button type="button" class="car-title-btn" onclick="openVehicleActionModal('{{ $vKey }}', 'detail')" title="Lihat detail model katalog">
                                                        <strong class="car-title">{{ $model->merk }} {{ $model->model }}</strong>
                                                    </button>
                                                    <span class="car-code-pill">{{ $vKey }}</span>
                                                </div>
                                                <span class="car-subtext">{{ $model->tipe ?: 'Showcase APM' }} &bull; {{ $model->tahun }} &bull; Warna katalog: {{ $model->warna }}</span>
                                                <div class="car-virtual-hint">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                    <span>Model Katalog &bull; Belum Ada Unit Fisik</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Harga Jual --}}
                                    <td class="td-price">
                                        <strong>Rp {{ number_format((float) $model->harga_jual, 0, ',', '.') }}</strong>
                                    </td>

                                    {{-- Status Katalog --}}
                                    <td class="td-catalog-status" style="text-align: center;">
                                        <span class="badge-catalog-status is-published" title="Model tayang di katalog website">Tayang</span>
                                        @if($model->showcase_slug)
                                            <span class="catalog-slug-pill" title="Showcase: {{ $model->showcase_slug }}">{{ $model->showcase_slug }}</span>
                                        @endif
                                    </td>

                                    {{-- Stok Fisik --}}
                                    <td class="td-stock-physical" style="text-align: center;">
                                        <span class="text-muted">0 unit</span>
                                    </td>

                                    {{-- Stok Ready --}}
                                    <td class="td-stock-ready" style="text-align: center;">
                                        <span class="stock-ready-badge is-zero">0 unit</span>
                                    </td>

                                    {{-- Reservasi Aktif --}}
                                    <td class="td-reserved" style="text-align: center;">
                                        <span class="text-muted">0</span>
                                    </td>

                                    {{-- Pre-Order Aktif --}}
                                    <td class="td-preorder" style="text-align: center;">
                                        <button type="button" class="preorder-count-badge {{ $vActivePo > 0 ? 'is-active' : 'is-zero' }}" data-action="preorder" onclick="openVehicleActionModal('{{ $vKey }}', 'preorder')" title="Buka Permintaan Pre-Order">
                                            {{ $vActivePo }} permintaan
                                        </button>
                                    </td>

                                    {{-- Ketersediaan --}}
                                    <td class="td-availability" style="text-align: center;">
                                        <span class="badge-availability is-virtual" title="Model katalog resmi YZA, belum memiliki unit fisik tercatat">Belum Ada Unit Fisik</span>
                                    </td>

                                    {{-- Aksi (Sticky Column — Single Three-Dot Menu) --}}
                                    <td class="td-action sticky-col" style="text-align: center;">
                                        <div class="action-single-wrap">
                                            <button type="button"
                                                    class="btn-more-options"
                                                    onclick="toggleDropdownFixed(this, '{{ $vKey }}')"
                                                    aria-label="Menu Opsi Model Katalog {{ $model->merk }} {{ $model->model }}"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                    title="Menu Opsi Model">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1.5"></circle><circle cx="19" cy="12" r="1.5"></circle><circle cx="5" cy="12" r="1.5"></circle></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif

                        {{-- 3. EMPTY STATE BILA TIDAK ADA KENDARAAN SAMA SEKALI --}}
                        @if(!$renderedAny)
                            <tr>
                                <td colspan="10" class="table-empty-cell">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                        </div>
                                        <h3>Tidak Ada Mobil Ditemukan</h3>
                                        <p>Belum ada data mobil yang sesuai dengan parameter filter atau pencarian Anda.</p>
                                        <a href="{{ route('admin.mobil.index', ['tab' => 'inventori']) }}" class="btn-empty-reset">Reset Filter</a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination & Per-Page Controls --}}
            <div class="vehicle-table-footer">
                <div class="footer-info">
                    Menampilkan <strong>{{ $cars->firstItem() ?: 0 }}</strong> – <strong>{{ $cars->lastItem() ?: 0 }}</strong> dari <strong>{{ $cars->total() }}</strong> unit mobil
                </div>

                <div class="footer-controls">
                    <div class="pagination-links">
                        {{ $cars->links('pagination::bootstrap-4') }}
                    </div>

                    {{-- Dropdown Per-Halaman --}}
                    <form method="get" action="{{ route('admin.mobil.index') }}" class="per-page-form">
                        <input type="hidden" name="tab" value="inventori">
                        <input type="hidden" name="q" value="{{ $q }}">
                        <input type="hidden" name="merk" value="{{ $merk }}">
                        <input type="hidden" name="status" value="{{ $status }}">
                        <input type="hidden" name="kondisi" value="{{ $kondisi }}">
                        <input type="hidden" name="tahun_min" value="{{ $tahunMin }}">
                        <input type="hidden" name="tahun_maks" value="{{ $tahunMaks }}">
                        <input type="hidden" name="harga_min" value="{{ $hargaMin }}">
                        <input type="hidden" name="harga_maks" value="{{ $hargaMaks }}">
                        <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                            <option value="10" @selected($perPage == 10)>10 per halaman</option>
                            <option value="25" @selected($perPage == 25)>25 per halaman</option>
                            <option value="50" @selected($perPage == 50)>50 per halaman</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- TAB 2: KATALOG & SHOWROOM                                  --}}
        {{-- ========================================================== --}}
        <div id="tab-katalog" class="vehicle-tab-pane {{ $activeTab === 'katalog' ? 'is-active' : '' }}" role="tabpanel">
            <div class="tab-section-intro">
                <div class="intro-text">
                    <h2 class="intro-title">Katalog &amp; Showroom</h2>
                    <p class="intro-desc">Model katalog dan kendaraan inventori dalam satu daftar. Model tanpa unit fisik tetap ditampilkan dengan stok 0.</p>
                </div>
                <div class="intro-badge">
                    <span>Sumber: <strong>Master Katalog &amp; Inventori Terhubung</strong></span>
                </div>
            </div>

            {{-- Grid 3-Kolom Kartu Showroom --}}
            <div class="showroom-card-grid">
                @forelse($catalogCars as $car)
                    @php
                        $availability = $car->getAttribute('availability') ?? [];
                        $stockOrderable = $availability['stock'] ?? 0;
                        $fotoUrl = $resolveCarThumbnail($car);
                        $cleanStatus = strtolower((string) $car->status);
                        $publicPreviewUrl = $car->showcase_slug
                            ? route('mobil.showcase', ['slug' => $car->showcase_slug, 'source' => 'showroom'])
                            : route('mobil.show', $car);
                    @endphp
                    <article class="showroom-car-card">
                        <div class="showroom-media">
                            <img src="{{ $fotoUrl }}" alt="{{ $car->merk }} {{ $car->model }}" loading="lazy">
                            <div class="showroom-media-badges">
                                @if($car->status === 'tersedia' && $stockOrderable > 0 && ($availability['can_purchase'] ?? false))
                                    <span class="showroom-badge is-available">Tersedia</span>
                                @elseif($cleanStatus === 'dibooking')
                                    <span class="showroom-badge is-booked">Dipesan</span>
                                @elseif($cleanStatus === 'terjual')
                                    <span class="showroom-badge is-sold">Terjual</span>
                                @elseif(!$car->exists || $stockOrderable < 1)
                                    <span class="showroom-badge is-inactive">{{ $car->exists ? 'Stok Habis' : 'Belum Ada Unit Fisik' }}</span>
                                @else
                                    <span class="showroom-badge is-inactive">{{ ucfirst($cleanStatus) }}</span>
                                @endif

                                @if($car->is_limited_edition)
                                    <span class="showroom-badge is-highlight">Limited Edition</span>
                                @endif
                                <span class="showroom-badge is-condition">{{ ucfirst($car->kondisi) }}</span>
                            </div>
                        </div>

                        <div class="showroom-body">
                            <div class="showroom-top">
                                <div>
                                    <h3 class="showroom-title">{{ $car->merk }} {{ $car->model }}</h3>
                                    <p class="showroom-sub">{{ $car->kode_mobil }} &bull; {{ $car->tipe ?: 'Koleksi YZA' }}</p>
                                </div>
                                @if($car->showcase_slug)
                                    <span class="showroom-slug-pill" title="Showcase: {{ $car->showcase_slug }}">{{ $car->showcase_slug }}</span>
                                @endif
                            </div>

                            <div class="showroom-price-row">
                                <div class="showroom-price">Rp {{ number_format((float) $car->harga_jual, 0, ',', '.') }}</div>
                                <span class="showroom-stock-pill {{ $stockOrderable > 0 ? 'is-stock' : 'is-nostock' }}">
                                    {{ $stockOrderable > 0 ? $stockOrderable.' unit tersedia' : 'Stok Kosong' }}
                                </span>
                            </div>

                            <div class="showroom-meta-chips">
                                <span>{{ $car->tahun ?: 'Tahun —' }}</span>
                                <span>{{ $car->warna ?: 'Warna —' }}</span>
                                <span>{{ ucfirst($car->transmisi ?: 'Otomatis') }}</span>
                                <span>{{ ucfirst($car->bahan_bakar ?: 'Bensin') }}</span>
                            </div>

                            @if($car->deskripsi)
                                <p class="showroom-description">{{ \Illuminate\Support\Str::limit($car->deskripsi, 85) }}</p>
                            @endif

                            <div class="showroom-actions-row">
                                @if(!$car->exists)
                                <button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('{{ app(\App\Services\CatalogVehicleIdentityService::class)->key($car) }}', 'add_unit')">Kelola Model &amp; Stok</button>
                                @else
                                <button type="button" class="btn-showroom-detail" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'detail')">Lihat Detail</button>
                                <button type="button" class="btn-showroom-detail" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'stock')">Kelola Stok</button>
                                @if(!$isOwner)<button type="button" class="btn-showroom-edit" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'edit')">Edit</button>@endif
                                @endif
                                <a class="btn-showroom-preview" href="{{ $publicPreviewUrl }}" target="_blank" rel="noopener" title="Lihat preview halaman publik website">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                    <span>Preview</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="catalog-empty-full">
                        <p>Tidak ada kendaraan yang sesuai dengan kriteria filter saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================================== --}}
        {{-- TAB 3: FOTO & WARNA                                        --}}
        {{-- ========================================================== --}}
        <div id="tab-foto" class="vehicle-tab-pane {{ $activeTab === 'foto' ? 'is-active' : '' }}" role="tabpanel">
            <div class="tab-section-intro">
                <div class="intro-text">
                    <h2 class="intro-title">Foto &amp; Master Warna Kendaraan</h2>
                    <p class="intro-desc">Kelola galeri visual multi-sudut (Depan, Samping, Belakang, Interior) dan daftar warna kendaraan. Membedakan warna katalog showroom dengan unit fisik yang tersedia di inventori.</p>
                </div>
            </div>

            <div class="gallery-management-container">
                @forelse($cars as $car)
                    @php
                        $warnaPilihan = $car->warnaPilihan ?? collect();
                        $uploadedPhotos = $car->foto ?? collect();
                        $slug = \Illuminate\Support\Str::slug($car->merk . ' ' . $car->model);

                        $angles = [
                            'front' => ['label' => 'Depan (Front 3/4)', 'path' => null],
                            'side' => ['label' => 'Samping (Side Profile)', 'path' => null],
                            'rear' => ['label' => 'Belakang (Rear 3/4)', 'path' => null],
                            'interior' => ['label' => 'Kabin / Interior', 'path' => null],
                        ];
                        $defaultColor = $warnaPilihan->firstWhere('is_default', true) ?: $warnaPilihan->first();
                        if ($defaultColor && $defaultColor->relationLoaded('galeri')) {
                            foreach ($defaultColor->galeri as $galeriItem) {
                                if (isset($angles[$galeriItem->sudut]) && is_file(public_path($galeriItem->path))) {
                                    $angles[$galeriItem->sudut]['path'] = asset($galeriItem->path);
                                }
                            }
                        }
                        foreach (['front', 'side', 'rear', 'interior'] as $angleKey) {
                            if (!$angles[$angleKey]['path']) {
                                $candidate = "assets/images/vehicles/{$slug}/{$angleKey}.png";
                                if (file_exists(public_path($candidate))) {
                                    $angles[$angleKey]['path'] = asset($candidate);
                                }
                            }
                        }
                        if (!$angles['front']['path'] && $uploadedPhotos->isNotEmpty()) {
                            $angles['front']['path'] = $uploadedPhotos->first()->url();
                        }
                    @endphp

                    <div class="car-gallery-card" id="car-gallery-{{ $car->id_mobil }}">
                        <div class="car-gallery-header">
                            <div class="gallery-car-meta">
                                <span class="gallery-car-code">{{ $car->kode_mobil }}</span>
                                <h3 class="gallery-car-name">{{ $car->merk }} {{ $car->model }} <small>({{ $car->tahun }})</small></h3>
                                <div class="gallery-color-distinction">
                                    <span class="badge-physical-color" title="Warna unit fisik di inventori">
                                        <strong>Warna Fisik Unit:</strong> {{ $car->warna ?: 'Tidak ditentukan' }}
                                    </span>
                                    <span class="badge-showroom-status">
                                        Showroom: {{ $car->showcase_slug ?: 'Standar' }}
                                    </span>
                                </div>
                            </div>
                            <div class="gallery-car-actions">
                                <button type="button" class="btn-sm-ghost" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'detail')">Buka Detail</button>
                                <button type="button" class="btn-sm-ghost" onclick="openVehicleActionModal({{ $car->id_mobil }}, 'gallery')">Kelola Galeri Modal</button>
                            </div>
                        </div>

                        {{-- Multi-Sudut Visual Grid --}}
                        <div class="visual-angles-grid">
                            @foreach($angles as $angleKey => $angleData)
                                <div class="angle-card">
                                    <div class="angle-view-box">
                                        @if($angleData['path'])
                                            <img src="{{ $angleData['path'] }}" alt="{{ $angleData['label'] }} - {{ $car->model }}" loading="lazy">
                                        @else
                                            <div class="angle-placeholder">
                                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                <span>{{ $angleData['label'] }} belum diunggah</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="angle-caption">
                                        <span>{{ $angleData['label'] }}</span>
                                        @if($angleData['path'])
                                            <span class="badge-dot is-active" title="Aset tersedia"></span>
                                        @else
                                            <span class="badge-dot is-inactive" title="Aset kosong"></span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Master Warna Pilihan Showroom vs Stok Fisik --}}
                        <div class="car-colors-section">
                            <h4 class="section-micro-title">Pilihan Warna Katalog Showroom &amp; Master Konfigurasi</h4>
                            <div class="color-swatches-list">
                                @forelse($warnaPilihan as $warnaItem)
                                    <div class="color-chip {{ $warnaItem->is_default ? 'is-default' : '' }}">
                                        <span class="color-circle" style="background-color: {{ $warnaItem->hex ?: '#1e293b' }};"></span>
                                        <div class="color-chip-info">
                                            <strong>{{ $warnaItem->nama }}</strong>
                                            <small>{{ $warnaItem->hex ?: 'Default Hex' }}</small>
                                        </div>
                                        @if($warnaItem->is_default)
                                            <span class="chip-tag-default">Default</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="color-empty-notice">
                                        <span>Menggunakan konfigurasi warna fisik tunggal: <strong>{{ $car->warna ?: 'Koleksi YZA' }}</strong>. Warna katalog tambahan dapat ditambahkan melalui form edit mobil.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Foto Uploaded & Pengelolaan Aset --}}
                        <div class="car-uploaded-photos-section">
                            <div class="uploaded-photos-header">
                                <h4 class="section-micro-title">Foto Terunggah ({{ $uploadedPhotos->count() }})</h4>
                                @if(!$isOwner)
                                    <form action="{{ route('admin.mobil.foto.store', $car) }}" method="post" enctype="multipart/form-data" class="quick-upload-form">
                                        @csrf
                                        <label class="btn-upload-file">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                            <span>Pilih Foto</span>
                                            <input type="file" name="foto" accept="image/*" required onchange="this.form.submit()" hidden>
                                        </label>
                                    </form>
                                @endif
                            </div>

                            <div class="uploaded-thumbnails-grid">
                                @forelse($uploadedPhotos as $foto)
                                    <div class="uploaded-photo-card {{ $foto->foto_utama ? 'is-primary' : '' }}">
                                        <img src="{{ $foto->url() }}" alt="Foto mobil {{ $car->model }}" loading="lazy">
                                        <div class="uploaded-photo-overlay">
                                            @if($foto->foto_utama)
                                                <span class="badge-primary-photo">Utama</span>
                                            @elseif(!$isOwner)
                                                <form action="{{ route('admin.mobil.foto.primary', [$car, $foto]) }}" method="post">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn-action-photo set-primary" title="Jadikan Foto Utama">Utama</button>
                                                </form>
                                            @endif
                                            @if(!$isOwner)
                                                <form action="{{ route('admin.mobil.foto.destroy', [$car, $foto]) }}" method="post" onsubmit="return confirm('Hapus foto ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action-photo delete" title="Hapus Foto">
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="no-uploaded-photos">
                                        <span>Belum ada foto yang diunggah untuk unit ini. Gunakan tombol "Pilih Foto" untuk menambahkan.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="catalog-empty-full">
                        <p>Belum ada data mobil untuk dikelola fotonya.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</div>

{{-- ========================================================================= --}}
{{-- FLOATING / FIXED ACTION DROPDOWN PORTAL (PREVENTS TABLE CLIPPING)         --}}
{{-- ========================================================================= --}}
<div id="floatingActionDropdown" class="floating-action-dropdown-menu" hidden role="menu" tabindex="-1" aria-orientation="vertical">
    {{-- Initial template items for accessibility & test fixtures; overwritten dynamically per vehicle on click --}}
    <button type="button" class="dropdown-item" data-action="detail" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        <span>Detail Kendaraan</span>
    </button>
    <button type="button" class="dropdown-item" data-action="stock" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
        <span>Kelola Stok</span>
    </button>
    <button type="button" class="dropdown-item" data-action="edit" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
        <span>Edit Kendaraan</span>
    </button>
    <button type="button" class="dropdown-item" data-action="add_unit" role="menuitem" title="Tambahkan Unit ke Inventori">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        <span>Tambah Unit Fisik</span>
    </button>
    <button type="button" class="dropdown-item has-badge" data-action="preorder" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
        <span>Permintaan Pre-Order</span>
    </button>
    <button type="button" class="dropdown-item" data-action="gallery" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path></svg>
        <span>Galeri &amp; Warna</span>
    </button>
    <div class="dropdown-divider"></div>
    <button type="button" class="dropdown-item is-danger" data-action="deactivate" role="menuitem">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        <span>Nonaktifkan Kendaraan</span>
    </button>
</div>

{{-- ========================================================================= --}}
{{-- REUSABLE VEHICLE MANAGEMENT ACTION MODALS                                 --}}
{{-- ========================================================================= --}}
<div id="vehicleModalBackdrop" class="vehicle-modal-backdrop" hidden>
    <div class="vehicle-modal-dialog" id="vehicleModalDialog" role="dialog" aria-modal="true" aria-labelledby="vehicleModalTitle" tabindex="-1">
        {{-- Modal Header --}}
        <header class="vehicle-modal-header">
            <div class="modal-header-titles">
                <span class="modal-kicker" id="vehicleModalKicker">YZA AUTOMOTIVE SHOWROOM</span>
                <h2 class="modal-title" id="vehicleModalTitle">Detail Kendaraan</h2>
                <div class="modal-header-meta" id="vehicleModalMeta"></div>
            </div>
            <button type="button" class="vehicle-modal-close" onclick="closeVehicleModal()" aria-label="Tutup popup" title="Tutup (Esc)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </header>

        {{-- Modal Internal Tabs Bar (Optional) --}}
        <nav class="modal-nav-tabs" id="vehicleModalNavTabs" hidden></nav>

        {{-- Modal Body Content --}}
        <div class="vehicle-modal-body" id="vehicleModalBody">
            <div class="modal-loading-state" id="modalLoadingState">
                <div class="modal-spinner"></div>
                <span>Memuat data kendaraan...</span>
            </div>
        </div>

        {{-- Modal Footer --}}
        <footer class="vehicle-modal-footer" id="vehicleModalFooter">
            <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
        </footer>
    </div>
</div>

@push('styles')
<style>
/* ========================================================
   YZA AUTOMOTIVE — MANAJEMEN MOBIL & SHOWROOM STYLES
   Clean White Background, Navy Accents, YZA Red Highlights
   ======================================================== */
.admin-vehicle-management-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;
}

/* Header */
.vehicle-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    padding: 10px 0;
}
.vehicle-header-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.vehicle-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748b;
}
.vehicle-breadcrumb .breadcrumb-separator {
    color: #cbd5e1;
    font-size: 11px;
}
.vehicle-breadcrumb .breadcrumb-current {
    color: #0f172a;
    font-weight: 600;
}
.vehicle-title-group {
    display: flex;
    align-items: center;
    gap: 14px;
}
.vehicle-title-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #0f172a;
    color: #ffffff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.12);
}
.vehicle-heading {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}
.vehicle-subheading {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.btn-primary-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 44px;
    padding: 0 20px;
    border-radius: 10px;
    background: linear-gradient(135deg, #ef233c 0%, #d90429 100%);
    color: #ffffff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(239, 35, 60, 0.25);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.btn-primary-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 18px rgba(239, 35, 60, 0.35);
    color: #ffffff;
}
.owner-readonly-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

/* Alert */
.admin-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
}
.admin-alert.is-success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}

/* 6 KPI Cards */
.vehicle-kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 14px;
}
.vehicle-kpi-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}
.vehicle-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.kpi-icon-box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    flex-shrink: 0;
}
.vehicle-kpi-card.is-red .kpi-icon-box { background: #ffe4e6; color: #e11d48; }
.vehicle-kpi-card.is-green .kpi-icon-box { background: #dcfce7; color: #16a34a; }
.vehicle-kpi-card.is-amber .kpi-icon-box { background: #fef3c7; color: #d97706; }
.vehicle-kpi-card.is-blue .kpi-icon-box { background: #e0e7ff; color: #4338ca; }
.vehicle-kpi-card.is-cyan .kpi-icon-box { background: #cffafe; color: #0891b2; }
.vehicle-kpi-card.is-orange .kpi-icon-box { background: #ffedd5; color: #ea580c; }

.kpi-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.kpi-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.03em;
}
.kpi-value {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.kpi-subtext {
    font-size: 10px;
    color: #94a3b8;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kpi-subtext.is-up {
    color: #16a34a;
    display: flex;
    align-items: center;
    gap: 3px;
}

/* Tabs Navigation Bar */
.vehicle-tabs-nav {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0;
    margin-top: 4px;
}
.vehicle-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border: 0;
    border-bottom: 3px solid transparent;
    background: transparent;
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    margin-bottom: -2px;
    transition: color 0.15s ease, border-color 0.15s ease;
}
.vehicle-tab-btn:hover {
    color: #0f172a;
}
.vehicle-tab-btn.is-active {
    color: #ef233c;
    border-bottom-color: #ef233c;
}
.tab-badge {
    padding: 2px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
}
.vehicle-tab-btn.is-active .tab-badge {
    background: #ffe4e6;
    color: #e11d48;
}

/* Main Card Container */
.vehicle-main-card {
    border-radius: 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.vehicle-tab-pane {
    display: none;
}
.vehicle-tab-pane.is-active {
    display: block;
}

/* Filter Toolbar */
.vehicle-filter-toolbar {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid #edf2f7;
    background: #fafbfc;
    align-items: center;
}
.filter-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.filter-input, .filter-select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.filter-input:focus, .filter-select:focus {
    border-color: #ef233c;
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.1);
}
.search-box svg {
    position: absolute;
    left: 12px;
    color: #94a3b8;
    pointer-events: none;
}
.search-box .filter-input {
    padding-left: 36px;
}
.select-chevron {
    position: absolute;
    right: 12px;
    color: #94a3b8;
    pointer-events: none;
}
.filter-actions-group {
    display: flex;
    gap: 8px;
}
.btn-filter-apply, .btn-filter-reset {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 40px;
    padding: 0 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 0;
}
.btn-filter-apply {
    background: #ef233c;
    color: #ffffff;
}
.btn-filter-reset {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

/* ========================================================
   TABLE LAYOUT & STICKY ACTION COLUMN
   ======================================================== */
.vehicle-table-responsive {
    overflow-x: auto;
    width: 100%;
    position: relative;
    scroll-behavior: smooth;
}
.vehicle-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12px;
}
.vehicle-table th {
    padding: 12px 14px;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap;
}
.vehicle-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
    background: #ffffff;
}
.vehicle-table tr:hover td {
    background: #fafbfc;
}

/* Sticky Action Column on the Right */
.vehicle-table th.sticky-col {
    position: sticky;
    right: 0;
    z-index: 12;
    background: #f8fafc;
    box-shadow: -4px 0 8px rgba(15, 23, 42, 0.05);
}
.vehicle-table td.sticky-col {
    position: sticky;
    right: 0;
    z-index: 10;
    background: #ffffff;
    box-shadow: -4px 0 8px rgba(15, 23, 42, 0.05);
}
.vehicle-table tr:hover td.sticky-col {
    background: #fafbfc;
}

/* Vehicle cell */
.car-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.car-thumb-box {
    width: 60px;
    height: 42px;
    border-radius: 6px;
    background: #0f172a;
    overflow: hidden;
    flex-shrink: 0;
}
.car-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.car-meta {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.car-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.car-title-btn {
    background: transparent;
    border: 0;
    padding: 0;
    color: #0f172a;
    cursor: pointer;
    text-align: left;
    font-size: 13px;
    font-weight: 700;
    transition: color 0.15s ease;
}
.car-title-btn:hover { color: #ef233c; }
.car-code-pill {
    padding: 2px 6px;
    border-radius: 4px;
    background: #f1f5f9;
    color: #475569;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 10px;
    font-weight: 700;
}
.car-subtext {
    font-size: 11px;
    color: #64748b;
}

.vin-cell {
    display: flex;
    flex-direction: column;
    gap: 3px;
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11px;
}
.vin-badge { color: #0f172a; }
.engine-badge { color: #64748b; }

.showroom-slug-badge {
    padding: 3px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
}

.orderable-unit-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11px;
}
.orderable-unit-badge.is-available {
    background: #dcfce7;
    color: #15803d;
}
.orderable-unit-badge.is-empty {
    background: #fee2e2;
    color: #b91c1c;
}

.badge-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.badge-status.is-available { background: #ecfdf5; color: #047857; }
.badge-status.is-booked { background: #fffbeb; color: #b45309; }
.badge-status.is-sold { background: #ffe4e6; color: #be123c; }
.badge-status.is-inactive { background: #f1f5f9; color: #64748b; }

/* Status Katalog Badge */
.badge-catalog-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.badge-catalog-status.is-published {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.badge-catalog-status.is-unpublished {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
}
.catalog-slug-pill {
    display: block;
    padding: 2px 6px;
    font-size: 9px;
    font-family: ui-monospace, SFMono-Regular, monospace;
    background: #f8fafc;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    margin-top: 3px;
    max-width: 110px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Stock Ready & Reserved & Pre-Order Badges */
.stock-ready-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11px;
}
.stock-ready-badge.is-ready {
    background: #dcfce7;
    color: #15803d;
}
.stock-ready-badge.is-zero {
    background: #fee2e2;
    color: #b91c1c;
}
.reserved-count-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 11px;
    background: #fef3c7;
    color: #b45309;
}
.preorder-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
}
.preorder-count-badge.is-active {
    padding: 3px 9px;
    border-radius: 999px;
    font-weight: 700;
    background: #cffafe;
    color: #0891b2;
    border: 1px solid #a5f3fc;
    text-decoration: none;
    transition: transform 0.12s ease, background 0.12s ease;
}
.preorder-count-badge.is-active:hover {
    transform: translateY(-1px);
    background: #a5f3fc;
}
.preorder-count-badge.is-zero {
    color: #94a3b8;
}

/* Ketersediaan Badge */
.badge-availability {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 999px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}
.badge-availability.is-ready {
    background: #ecfdf5;
    color: #047857;
}
.badge-availability.is-exhausted {
    background: #fef3c7;
    color: #b45309;
}
.badge-availability.is-virtual {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
}
.badge-availability.is-booked {
    background: #fffbeb;
    color: #b45309;
}
.badge-availability.is-sold {
    background: #ffe4e6;
    color: #be123c;
}
.badge-availability.is-inactive {
    background: #f1f5f9;
    color: #64748b;
}
.badge-availability.is-other {
    background: #f1f5f9;
    color: #64748b;
}

/* Context Banners */
.preorder-context-banner, .catalog-filter-context-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 16px;
    border-radius: 10px;
    margin-bottom: 14px;
    font-size: 12px;
    font-weight: 600;
}
.preorder-context-banner {
    background: #ecfeff;
    border: 1px solid #a5f3fc;
    color: #0e7490;
}
.catalog-filter-context-banner {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}
.context-banner-text {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-context-back {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 6px;
    background: #ffffff;
    color: #0e7490;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid #a5f3fc;
    transition: all 0.12s ease;
}
.btn-context-back:hover {
    background: #0891b2;
    color: #ffffff;
}

/* Sublines & Hints */
.car-vin-subline {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 10px;
    color: #64748b;
    font-family: ui-monospace, SFMono-Regular, monospace;
    margin-top: 2px;
}
.car-vin-subline .vin-separator {
    color: #cbd5e1;
}
.car-virtual-hint {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    color: #2563eb;
    margin-top: 2px;
}
.is-virtual-model-row td {
    background: #fafbfc;
}
.is-virtual-model-row:hover td {
    background: #f1f5f9;
}
.btn-row-action.is-add-unit {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    text-decoration: none;
}
.btn-row-action.is-add-unit:hover {
    background: #dbeafe;
    color: #1e40af;
}
.btn-row-action.is-preorder {
    background: #cffafe;
    color: #0891b2;
    border: 1px solid #a5f3fc;
    text-decoration: none;
}
.btn-row-action.is-preorder:hover {
    background: #a5f3fc;
    color: #0e7490;
}

/* Single Three-Dot Action Button & Wrapper */
.action-single-wrap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-more-options {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
    cursor: pointer;
    color: #475569;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.btn-more-options:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
    box-shadow: 0 2px 4px rgba(15, 23, 42, 0.08);
    transform: translateY(-1px);
}
.btn-more-options[aria-expanded="true"] {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

/* Floating Fixed-Positioning Action Dropdown */
.floating-action-dropdown-menu {
    position: fixed;
    z-index: 9999;
    width: 230px;
    padding: 6px;
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
    display: flex;
    flex-direction: column;
    gap: 3px;
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
    transition: opacity 150ms cubic-bezier(0.16, 1, 0.3, 1), transform 150ms cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
}
.floating-action-dropdown-menu.is-open {
    opacity: 1;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}
.floating-action-dropdown-menu.is-flipped {
    transform: translateY(4px) scale(0.98);
}
.floating-action-dropdown-menu.is-flipped.is-open {
    transform: translateY(0) scale(1);
}
.floating-action-dropdown-menu[hidden] {
    display: none;
}
.floating-action-dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: 0;
    background: transparent;
    color: #1e293b;
    font-size: 13px;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.floating-action-dropdown-menu .dropdown-item svg {
    flex-shrink: 0;
    color: #64748b;
    transition: color 0.12s ease;
}
.floating-action-dropdown-menu .dropdown-item:hover,
.floating-action-dropdown-menu .dropdown-item:focus {
    background: #f8fafc;
    color: #0f172a;
    outline: none;
}
.floating-action-dropdown-menu .dropdown-item:hover svg,
.floating-action-dropdown-menu .dropdown-item:focus svg {
    color: #0f172a;
}
.floating-action-dropdown-menu .dropdown-item.has-badge {
    justify-content: flex-start;
}
.floating-action-dropdown-menu .dropdown-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 7px;
    border-radius: 999px;
    background: #e0f2fe;
    color: #0284c7;
    font-size: 11px;
    font-weight: 700;
}
.floating-action-dropdown-menu .dropdown-divider {
    height: 1px;
    background: #f1f5f9;
    margin: 4px 6px;
}
.floating-action-dropdown-menu .dropdown-item.is-danger {
    color: #dc2626;
}
.floating-action-dropdown-menu .dropdown-item.is-danger svg {
    color: #ef4444;
}
.floating-action-dropdown-menu .dropdown-item.is-danger:hover,
.floating-action-dropdown-menu .dropdown-item.is-danger:focus {
    background: #fef2f2;
    color: #b91c1c;
}
.floating-action-dropdown-menu .dropdown-item.is-danger:hover svg,
.floating-action-dropdown-menu .dropdown-item.is-danger:focus svg {
    color: #dc2626;
}

/* Table Footer */
.vehicle-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #fafbfc;
    border-top: 1px solid #e2e8f0;
    font-size: 12px;
}
.footer-controls {
    display: flex;
    align-items: center;
    gap: 16px;
}
.per-page-select {
    height: 34px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    font-size: 12px;
}

/* TAB 2: KATALOG & SHOWROOM */
.tab-section-intro {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px 24px;
    border-bottom: 1px solid #edf2f7;
    background: #fafbfc;
}
.intro-title {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}
.intro-desc {
    margin: 4px 0 0;
    font-size: 13px;
    color: #64748b;
}
.intro-badge {
    font-size: 11px;
    color: #475569;
    background: #f1f5f9;
    padding: 6px 12px;
    border-radius: 8px;
    white-space: nowrap;
}

.showroom-card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    padding: 24px;
}
.showroom-car-card {
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.showroom-car-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.09);
}
.showroom-media {
    position: relative;
    height: 190px;
    background: #0d1420;
    overflow: hidden;
}
.showroom-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.25s ease;
}
.showroom-car-card:hover .showroom-media img {
    transform: scale(1.04);
}
.showroom-media-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    z-index: 2;
}
.showroom-badge {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}
.showroom-badge.is-available { background: #10b981; color: #ffffff; }
.showroom-badge.is-booked { background: #f59e0b; color: #ffffff; }
.showroom-badge.is-sold { background: #ef233c; color: #ffffff; }
.showroom-badge.is-inactive { background: #64748b; color: #ffffff; }
.showroom-badge.is-highlight { background: #8b5cf6; color: #ffffff; }
.showroom-badge.is-condition { background: rgba(15, 23, 42, 0.85); color: #ffffff; }

.showroom-body {
    padding: 18px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.showroom-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 8px;
}
.showroom-title {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0f172a;
}
.showroom-sub {
    margin: 3px 0 0;
    font-size: 11px;
    color: #64748b;
}
.showroom-slug-pill {
    padding: 3px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}
.showroom-price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 8px 0 12px;
}
.showroom-price {
    font-size: 18px;
    font-weight: 800;
    color: #ef233c;
}
.showroom-stock-pill {
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
}
.showroom-stock-pill.is-stock { background: #ecfdf5; color: #047857; }
.showroom-stock-pill.is-nostock { background: #fee2e2; color: #b91c1c; }

.showroom-meta-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
}
.showroom-meta-chips span {
    padding: 4px 8px;
    border-radius: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    font-size: 11px;
    color: #475569;
}
.showroom-description {
    font-size: 12px;
    color: #64748b;
    line-height: 1.45;
    margin: 0 0 16px;
}
.showroom-actions-row {
    display: flex;
    gap: 8px;
    margin-top: auto;
}
.btn-showroom-detail, .btn-showroom-edit, .btn-showroom-preview {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    border: 0;
}
.btn-showroom-detail {
    flex: 2;
    background: #ef233c;
    color: #ffffff;
    box-shadow: 0 3px 10px rgba(239, 35, 60, 0.2);
}
.btn-showroom-detail:hover { color: #ffffff; background: #d90429; }
.btn-showroom-edit {
    flex: 1;
    border: 1px solid #cbd5e1;
    color: #334155;
    background: #ffffff;
}
.btn-showroom-edit:hover { background: #f8fafc; color: #0f172a; }
.btn-showroom-preview {
    border: 1px solid #e2e8f0;
    color: #64748b;
    background: #f8fafc;
}

/* TAB 3: FOTO & WARNA */
.gallery-management-container {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 28px;
}
.car-gallery-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.car-gallery-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.gallery-car-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.gallery-car-code {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11px;
    font-weight: 700;
    color: #ef233c;
}
.gallery-car-name {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
}
.gallery-car-name small {
    color: #64748b;
    font-weight: 600;
}
.gallery-color-distinction {
    display: flex;
    gap: 8px;
    margin-top: 4px;
    flex-wrap: wrap;
}
.badge-physical-color {
    padding: 4px 8px;
    border-radius: 6px;
    background: #e0f2fe;
    color: #0369a1;
    font-size: 11px;
}
.badge-showroom-status {
    padding: 4px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
}
.gallery-car-actions {
    display: flex;
    gap: 8px;
}
.btn-sm-ghost {
    padding: 6px 12px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    background: #ffffff;
    cursor: pointer;
}
.btn-sm-ghost:hover { background: #f8fafc; color: #0f172a; }

/* 4 Sudut Visual Grid */
.visual-angles-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.angle-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: #fafbfc;
}
.angle-view-box {
    height: 120px;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.angle-view-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.angle-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px;
    text-align: center;
    color: #94a3b8;
    font-size: 10px;
}
.angle-caption {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
    background: #ffffff;
}
.badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.badge-dot.is-active { background: #10b981; }
.badge-dot.is-inactive { background: #cbd5e1; }

/* Colors Section */
.car-colors-section {
    padding: 14px 16px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
}
.section-micro-title {
    margin: 0 0 10px;
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.color-swatches-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.color-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
}
.color-chip.is-default {
    border-color: #ef233c;
    background: #fff5f5;
}
.color-circle {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, 0.15);
}
.color-chip-info {
    display: flex;
    flex-direction: column;
    font-size: 11px;
}
.color-chip-info strong { color: #0f172a; }
.color-chip-info small { color: #64748b; font-family: monospace; font-size: 9px; }
.chip-tag-default {
    font-size: 9px;
    font-weight: 800;
    color: #ef233c;
    text-transform: uppercase;
}
.color-empty-notice {
    font-size: 12px;
    color: #64748b;
}

/* Uploaded Photos Grid */
.car-uploaded-photos-section {
    padding: 14px 16px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}
.uploaded-photos-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.btn-upload-file {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #0f172a;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}
.uploaded-thumbnails-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
}
.uploaded-photo-card {
    position: relative;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    background: #0f172a;
    border: 2px solid transparent;
}
.uploaded-photo-card.is-primary {
    border-color: #ef233c;
}
.uploaded-photo-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.uploaded-photo-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    opacity: 0;
    transition: opacity 0.15s ease;
}
.uploaded-photo-card:hover .uploaded-photo-overlay {
    opacity: 1;
}
.badge-primary-photo {
    padding: 3px 6px;
    border-radius: 4px;
    background: #ef233c;
    color: #ffffff;
    font-size: 10px;
    font-weight: 800;
}
.btn-action-photo {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
}
.btn-action-photo.set-primary { background: #ffffff; color: #0f172a; }
.btn-action-photo.delete { background: #fee2e2; color: #dc2626; }
.no-uploaded-photos {
    grid-column: 1 / -1;
    font-size: 12px;
    color: #94a3b8;
    padding: 10px 0;
}

.catalog-empty-full {
    padding: 48px;
    text-align: center;
    color: #64748b;
    font-size: 13px;
}

/* ========================================================
   REUSABLE PREMIUM ACTION MODAL STYLES
   ======================================================== */
.vehicle-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 1;
    transition: opacity 200ms ease-out;
}
.vehicle-modal-backdrop[hidden] {
    display: none;
    opacity: 0;
}
.vehicle-modal-dialog {
    position: relative;
    width: 100%;
    max-width: 900px;
    max-height: 88dvh;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: translateY(0);
    transition: transform 200ms ease-out;
}
.vehicle-modal-dialog.is-compact {
    max-width: 520px;
}
.vehicle-modal-dialog.is-medium {
    max-width: 680px;
}

@media (prefers-reduced-motion: no-preference) {
    .vehicle-modal-backdrop:not([hidden]) .vehicle-modal-dialog {
        animation: modalSlideUp 200ms ease-out;
    }
}
@keyframes modalSlideUp {
    from { transform: translateY(8px); opacity: 0.85; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes modalSpin {
    to { transform: rotate(360deg); }
}

.vehicle-modal-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #edf2f7;
    background: #ffffff;
}
.modal-header-titles {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.modal-kicker {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    color: #ef233c;
    letter-spacing: 0.08em;
}
.modal-title {
    margin: 0;
    font-size: 19px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.01em;
}
.modal-header-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
    font-size: 12px;
    color: #64748b;
}
.vehicle-modal-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}
.vehicle-modal-close:hover {
    background: #f1f5f9;
    color: #0f172a;
}

/* Modal Nav Tabs */
.modal-nav-tabs {
    display: flex;
    padding: 0 24px;
    gap: 6px;
    border-bottom: 1px solid #e2e8f0;
    background: #fafbfc;
}
.modal-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    border: 0;
    border-bottom: 2px solid transparent;
    background: transparent;
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    margin-bottom: -1px;
    transition: color 0.12s ease, border-color 0.12s ease;
}
.modal-tab-btn:hover { color: #0f172a; }
.modal-tab-btn.is-active {
    color: #ef233c;
    border-bottom-color: #ef233c;
}

/* Modal Body */
.vehicle-modal-body {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
    font-size: 13px;
    color: #334155;
}

/* Modal Footer */
.vehicle-modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 24px;
    border-top: 1px solid #edf2f7;
    background: #fafbfc;
}
.btn-modal-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 18px;
    border-radius: 8px;
    background: #ef233c;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(239, 35, 60, 0.2);
}
.btn-modal-primary:hover { background: #d90429; color: #ffffff; }
.btn-modal-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 18px;
    border-radius: 8px;
    background: #dc2626;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    border: 0;
    cursor: pointer;
}
.btn-modal-danger:hover { background: #b91c1c; }
.btn-modal-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 38px;
    padding: 0 16px;
    border-radius: 8px;
    background: #ffffff;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    cursor: pointer;
}
.btn-modal-secondary:hover { background: #f8fafc; color: #0f172a; }

/* Modal Panes Visibility & Content Segregation */
.modal-pane {
    display: none;
    animation: fadeInModalPane 150ms cubic-bezier(0.16, 1, 0.3, 1);
}
.modal-pane.is-active,
.modal-pane:not(.hidden) {
    display: block;
}
.modal-pane.hidden {
    display: none !important;
}
@keyframes fadeInModalPane {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-strip-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    color: #334155;
    margin-bottom: 14px;
}
.modal-strip-summary strong {
    color: #0f172a;
}
.color-chip.is-interactive {
    cursor: pointer;
    transition: transform 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
}
.color-chip.is-interactive:hover {
    transform: translateY(-1px);
    border-color: #94a3b8;
}
.color-chip.is-interactive.is-selected {
    border-color: #ef233c;
    background: #fff5f5;
    box-shadow: 0 0 0 2px rgba(239, 35, 60, 0.2);
}
.gallery-active-preview {
    width: 100%;
    height: 220px;
    border-radius: 12px;
    overflow: hidden;
    background: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
}
.gallery-active-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Modal Content Layouts */
.modal-grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.modal-detail-hero {
    display: flex;
    gap: 18px;
    align-items: center;
    padding: 14px 18px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 18px;
}
.modal-detail-thumb {
    width: 140px;
    height: 90px;
    border-radius: 8px;
    overflow: hidden;
    background: #0f172a;
    flex-shrink: 0;
}
.modal-detail-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.modal-hero-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.modal-hero-price {
    font-size: 20px;
    font-weight: 800;
    color: #ef233c;
}
.modal-spec-dl {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 16px;
    margin: 0;
}
.modal-spec-row {
    display: flex;
    flex-direction: column;
    padding: 8px 12px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #edf2f7;
}
.modal-spec-row.is-full {
    grid-column: 1 / -1;
}
.modal-spec-row dt {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
}
.modal-spec-row dd {
    margin: 3px 0 0;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

/* Modal Form Controls */
.modal-form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 14px;
}
.modal-form-label {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}
.modal-form-label .required-mark { color: #ef233c; }
.modal-form-input, .modal-form-select, .modal-form-textarea {
    width: 100%;
    height: 38px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    font-size: 13px;
    color: #1e293b;
    background: #ffffff;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.modal-form-textarea {
    height: 70px;
    padding: 8px 12px;
    resize: vertical;
}
.modal-form-input:focus, .modal-form-select:focus, .modal-form-textarea:focus {
    border-color: #ef233c;
    box-shadow: 0 0 0 3px rgba(239, 35, 60, 0.1);
}
.modal-field-error {
    font-size: 11px;
    color: #dc2626;
    margin-top: 3px;
}

/* Modal Info Callouts */
.modal-info-callout {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    font-size: 12px;
    margin-bottom: 16px;
    align-items: center;
}
.modal-warning-callout {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 10px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    font-size: 12px;
    margin-bottom: 16px;
    align-items: center;
}

/* Timeline / History Table */
.modal-history-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.modal-history-table th {
    padding: 10px 12px;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
.modal-history-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}

/* Pre-Order & Add Unit Modal Components */
.vehicle-modal-dialog.is-wide {
    max-width: 860px;
}
body.modal-open {
    overflow: hidden !important;
}
.preorder-modal-summary-bar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 16px;
}
.summary-stat-box {
    padding: 12px 14px;
    border-radius: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.stat-label {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    color: #64748b;
    letter-spacing: 0.04em;
}
.stat-number {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
}
.stat-badge {
    font-size: 12px;
    font-weight: 700;
    color: #0284c7;
}
.stat-note {
    font-size: 13px;
    font-weight: 800;
    color: #ef233c;
}
.preorder-empty-box {
    padding: 36px 20px;
    text-align: center;
    color: #64748b;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.preorder-empty-box h4 {
    margin: 8px 0 0;
    color: #0f172a;
    font-size: 15px;
    font-weight: 700;
}
.preorder-empty-box p {
    margin: 0;
    font-size: 12px;
    max-width: 440px;
}
.po-code-tag {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 11px;
    color: #0284c7;
    background: #e0f2fe;
    padding: 2px 6px;
    border-radius: 4px;
}
.po-member-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.po-member-code {
    font-family: ui-monospace, SFMono-Regular, monospace;
    font-size: 10px;
    color: #64748b;
}
.po-color-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #f1f5f9;
    font-size: 11px;
    font-weight: 600;
    color: #334155;
}
.btn-po-review {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    border-radius: 6px;
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s ease;
}
.btn-po-review:hover {
    background: #dbeafe;
    color: #1e40af;
    transform: translateY(-1px);
}
.btn-preorder-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #cbd5e1;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.12s ease;
}
.btn-preorder-back:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.preorder-review-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.preorder-card-box {
    padding: 16px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
}
.card-box-title {
    margin: 0 0 12px;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 6px;
}
.preorder-stock-status-box {
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
}
.preorder-stock-status-box.is-available {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #065f46;
}
.preorder-stock-status-box.is-waiting {
    background: #fffbeb;
    border-color: #fde68a;
    color: #92400e;
}
.status-box-header {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
}
.btn-action-inline {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    background: #059669;
    color: #ffffff;
    border: 0;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.12s ease;
}
.btn-action-inline:hover {
    background: #047857;
}
.modal-form-input.has-error,
.modal-form-select.has-error,
.modal-form-textarea.has-error {
    border-color: #ef233c !important;
    background: #fff5f5 !important;
}
.form-field-error {
    color: #dc2626;
    font-size: 11px;
    font-weight: 600;
    margin-top: 3px;
    display: block;
}

@media (prefers-reduced-motion: reduce) {
    .vehicle-modal-backdrop,
    .vehicle-modal-dialog,
    .btn-row-action,
    .btn-primary-action,
    .modal-pane {
        animation: none !important;
        transition: none !important;
    }
}

/* Responsive */
@media (max-width: 1280px) {
    .vehicle-kpi-grid { grid-template-columns: repeat(3, 1fr); }
    .showroom-card-grid { grid-template-columns: repeat(2, 1fr); }
    .visual-angles-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .vehicle-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .vehicle-filter-toolbar { grid-template-columns: 1fr; }
    .showroom-card-grid { grid-template-columns: 1fr; }
    .visual-angles-grid { grid-template-columns: 1fr; }
    .uploaded-thumbnails-grid { grid-template-columns: repeat(3, 1fr); }
    .modal-grid-2col { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .vehicle-header { flex-direction: column; align-items: flex-start; }
    .vehicle-kpi-grid { grid-template-columns: 1fr; }
    .vehicle-tabs-nav { flex-wrap: wrap; }
    .vehicle-modal-dialog { max-height: 94dvh; border-radius: 12px; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // TAB SWITCHING (INVENTORI / KATALOG / FOTO)
    // -------------------------------------------------------------
    const tabButtons = document.querySelectorAll('.vehicle-tab-btn');
    const tabPanes = document.querySelectorAll('.vehicle-tab-pane');

    function activateTab(tabId, updateUrl = true) {
        tabButtons.forEach(btn => {
            const isActive = btn.getAttribute('data-tab-target') === tabId;
            btn.classList.toggle('is-active', isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        tabPanes.forEach(pane => {
            const isActive = pane.id === tabId;
            pane.classList.toggle('is-active', isActive);
        });

        if (updateUrl) {
            const shortName = tabId.replace('tab-', '');
            const url = new URL(window.location.href);
            url.searchParams.set('tab', shortName);
            window.history.replaceState({}, '', url.toString());
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-tab-target');
            activateTab(targetId, true);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const preorderContext = Number(urlParams.get('preorder'));
    if (Number.isInteger(preorderContext) && preorderContext > 0) {
        const back = document.createElement('a');
        back.className = 'btn-modal-secondary'; back.textContent = '← Kembali ke Pre-Order';
        back.href = '{{ route('admin.preorder-mobil.index') }}?review=' + preorderContext;
        document.querySelector('.admin-content')?.prepend(back);
    }
    const stockTarget = Number(urlParams.get('stock_mobil'));
    const requestedAction = urlParams.get('vehicle_action');
    if (Number.isInteger(stockTarget) && stockTarget > 0 && getCarPayload(stockTarget)) openVehicleActionModal(stockTarget, ['detail','stock','edit'].includes(requestedAction) ? requestedAction : 'stock');
    const queryTab = urlParams.get('tab');
    if (queryTab && ['inventori', 'katalog', 'foto'].includes(queryTab)) {
        activateTab('tab-' + queryTab, false);
    }

    // -------------------------------------------------------------
    // FLOATING 3-DOTS ACTION DROPDOWN (SINGLE THREE-DOT ACTION MENU)
    // -------------------------------------------------------------
    const floatingMenu = document.getElementById('floatingActionDropdown');
    let currentDropdownCarId = null;
    let currentDropdownTriggerBtn = null;

    function renderDropdownItems(car) {
        if (!floatingMenu) return;
        const isVirtual = Boolean(car.is_virtual);
        const poCount = Array.isArray(car.preorders) ? car.preorders.length : 0;
        const canManage = !isUserOwner;

        let html = '';

        if (!isVirtual) {
            // A. MOBIL READY / MEMILIKI UNIT FISIK
            // 1. Detail Kendaraan
            html += `
                <button type="button" class="dropdown-item" data-action="detail" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <span>Detail Kendaraan</span>
                </button>
            `;

            // 2. Kelola Stok (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="stock" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                        <span>Kelola Stok</span>
                    </button>
                `;
            }

            // 3. Edit Kendaraan (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="edit" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        <span>Edit Kendaraan</span>
                    </button>
                `;
            }

            // 4. Galeri & Warna
            html += `
                <button type="button" class="dropdown-item" data-action="gallery" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path></svg>
                    <span>Galeri &amp; Warna</span>
                </button>
            `;

            // 5. Permintaan Pre-Order (Hanya bila jumlah aktif > 0)
            if (poCount > 0) {
                html += `
                    <button type="button" class="dropdown-item has-badge" data-action="preorder" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
                        <span>Permintaan Pre-Order</span>
                        <span class="dropdown-badge">${poCount}</span>
                    </button>
                `;
            }

            // 6. Nonaktifkan Mobil (Hanya jika diizinkan dan mobil belum terjual)
            if (canManage && car.status !== 'terjual') {
                html += `
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item is-danger" data-action="deactivate" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <span>Nonaktifkan Mobil</span>
                    </button>
                `;
            }
        } else {
            // B. MOBIL TANPA UNIT FISIK (KATALOG SHOWROOM)
            // 1. Detail Katalog
            html += `
                <button type="button" class="dropdown-item" data-action="detail" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    <span>Detail Katalog</span>
                </button>
            `;

            // 2. Tambah Unit Fisik (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="add_unit" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Unit Fisik</span>
                    </button>
                `;
            }

            // 3. Permintaan Pre-Order (N) jika terdapat permintaan aktif
            if (poCount > 0) {
                html += `
                    <button type="button" class="dropdown-item has-badge" data-action="preorder" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="9"></circle></svg>
                        <span>Permintaan Pre-Order (${poCount})</span>
                        <span class="dropdown-badge">${poCount}</span>
                    </button>
                `;
            }

            // 4. Edit Katalog (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <button type="button" class="dropdown-item" data-action="edit" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        <span>Edit Katalog</span>
                    </button>
                `;
            }

            // 5. Galeri & Warna
            html += `
                <button type="button" class="dropdown-item" data-action="gallery" role="menuitem">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path></svg>
                    <span>Galeri &amp; Warna</span>
                </button>
            `;

            // 6. Nonaktifkan Model (Hanya Staf Berwenang)
            if (canManage) {
                html += `
                    <div class="dropdown-divider"></div>
                    <button type="button" class="dropdown-item is-danger" data-action="deactivate" role="menuitem">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                        <span>Nonaktifkan Model</span>
                    </button>
                `;
            }
        }

        floatingMenu.innerHTML = html;

        // Pasang event listener untuk membuka modal
        floatingMenu.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                const action = this.getAttribute('data-action');
                const targetCarId = currentDropdownCarId;
                hideFloatingDropdown();
                if (targetCarId && action) {
                    openVehicleActionModal(targetCarId, action);
                }
            });
        });
    }

    window.toggleDropdownFixed = function (button, carId) {
        if (!floatingMenu) return;

        if (!floatingMenu.hasAttribute('hidden') && currentDropdownCarId === carId) {
            hideFloatingDropdown();
            return;
        }

        const car = getCarPayload(carId);
        if (!car) {
            console.warn('Vehicle payload not found for ID:', carId);
            return;
        }

        currentDropdownCarId = carId;
        currentDropdownTriggerBtn = button;

        renderDropdownItems(car);

        floatingMenu.removeAttribute('hidden');
        button.setAttribute('aria-expanded', 'true');

        const rect = button.getBoundingClientRect();
        const menuWidth = floatingMenu.offsetWidth || 230;
        const menuHeight = floatingMenu.offsetHeight || 220;

        let left = rect.right - menuWidth;
        if (left < 10) left = 10;
        if (left + menuWidth > window.innerWidth - 10) {
            left = window.innerWidth - menuWidth - 10;
        }

        let top = rect.bottom + 6;
        let isFlipped = false;
        if (top + menuHeight > window.innerHeight - 10) {
            top = rect.top - menuHeight - 6;
            isFlipped = true;
            if (top < 10) top = 10;
        }

        floatingMenu.style.top = top + 'px';
        floatingMenu.style.left = left + 'px';
        floatingMenu.classList.toggle('is-flipped', isFlipped);

        requestAnimationFrame(() => {
            floatingMenu.classList.add('is-open');
        });
    };

    function hideFloatingDropdown() {
        if (!floatingMenu) return;
        floatingMenu.classList.remove('is-open');
        floatingMenu.setAttribute('hidden', '');
        if (currentDropdownTriggerBtn) {
            currentDropdownTriggerBtn.setAttribute('aria-expanded', 'false');
            currentDropdownTriggerBtn = null;
        }
        currentDropdownCarId = null;
    }

    document.addEventListener('click', function (e) {
        if (floatingMenu && !floatingMenu.hasAttribute('hidden')) {
            if (!floatingMenu.contains(e.target) && !e.target.closest('.btn-more-options')) {
                hideFloatingDropdown();
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (!floatingMenu || floatingMenu.hasAttribute('hidden')) return;

        if (e.key === 'Escape') {
            const btn = currentDropdownTriggerBtn;
            hideFloatingDropdown();
            if (btn) btn.focus();
            return;
        }

        const items = Array.from(floatingMenu.querySelectorAll('.dropdown-item'));
        if (!items.length) return;

        const currentIndex = items.indexOf(document.activeElement);

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = (currentIndex + 1) % items.length;
            items[nextIndex].focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = (currentIndex - 1 + items.length) % items.length;
            items[prevIndex].focus();
        }
    });

    window.addEventListener('resize', hideFloatingDropdown);
    window.addEventListener('scroll', hideFloatingDropdown, true);

    // Check All Checkbox
    const checkAll = document.getElementById('checkAllInventory');
    const rowChecks = document.querySelectorAll('.inventory-row-check');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks.forEach(cb => cb.checked = checkAll.checked);
        });
    }
});

// -------------------------------------------------------------
// REUSABLE VEHICLE MANAGEMENT ACTION MODALS ENGINE
// -------------------------------------------------------------
let activeModalTriggerElement = null;
const isUserOwner = @json($isOwner);
let isModalFormDirty = false;

function resetModalFormDirty() {
    isModalFormDirty = false;
}

function markModalFormDirty() {
    isModalFormDirty = true;
}

function setupFormDirtyTracking(form) {
    if (!form) return;
    form.addEventListener('input', () => { isModalFormDirty = true; });
    form.addEventListener('change', () => { isModalFormDirty = true; });
}

function getCarPayload(carId) {
    const scriptEl = document.getElementById('car-data-' + carId);
    if (scriptEl) {
        try {
            return JSON.parse(scriptEl.textContent);
        } catch (e) {
            console.error('Failed to parse car payload', e);
        }
    }
    return null;
}

function updateLocalCarPayload(carId, updatedData) {
    const scriptEl = document.getElementById('car-data-' + carId);
    if (scriptEl) {
        const current = getCarPayload(carId) || {};
        const merged = Object.assign({}, current, updatedData);
        scriptEl.textContent = JSON.stringify(merged);
    }
}

async function openVehicleActionModal(carId, action) {
    activeModalTriggerElement = document.activeElement;
    let car = null;
    if (carId === 'new') {
        car = {
            id: 'new',
            kode: 'YZA-UNIT-' + Math.floor(1000 + Math.random() * 9000),
            catalog_key: '',
            merk: '',
            model: '',
            tipe: '',
            tahun: new Date().getFullYear(),
            warna: '',
            transmisi: 'automatic',
            bahan_bakar: 'bensin',
            nomor_rangka: '',
            nomor_mesin: '',
            kilometer: 0,
            harga_beli: 0,
            harga_jual: 0,
            harga_jual_formatted: 'Rp 0',
            kondisi: 'baru',
            status: 'tersedia',
            is_virtual: false,
            preorders: [],
            urls: {
                store: @json(route('admin.mobil.store'))
            }
        };
    } else {
        car = getCarPayload(carId);
    }
    if (!car) {
        alert('Data kendaraan tidak ditemukan.');
        return;
    }

    if (car.urls?.show && ['stock', 'gallery', 'history', 'colors'].includes(action)) {
        try {
            const response = await fetch(car.urls.show, {headers:{Accept:'application/json'}, cache:'no-store', credentials:'same-origin'});
            if (!response.ok) throw new Error('Stok terbaru belum dapat dimuat.');
            const data = await response.json(), state = data.availability || {};
            Object.assign(car, {color_stocks:data.color_stocks || [], stock_total:state.stock_total || 0, stock_orderable:state.stock || 0, reserved_units:state.reserved_units || 0, sold_units:state.sold_units || 0});
            car.histories = (data.histories || []).map(h => ({tanggal:h.created_at,jenis:h.jenis,jumlah:h.jumlah_perubahan,before_after:h.stock_before+' → '+h.stock_after,catatan:h.catatan}));
        } catch (error) { showNotificationToast(error.message, 'error'); return; }
    }

    const backdrop = document.getElementById('vehicleModalBackdrop');
    const dialog = document.getElementById('vehicleModalDialog');
    const kicker = document.getElementById('vehicleModalKicker');
    const title = document.getElementById('vehicleModalTitle');
    const meta = document.getElementById('vehicleModalMeta');
    const navTabs = document.getElementById('vehicleModalNavTabs');
    const body = document.getElementById('vehicleModalBody');
    const footer = document.getElementById('vehicleModalFooter');

    // Reset Dialog Classes
    dialog.classList.remove('is-compact', 'is-medium', 'is-wide');
    navTabs.setAttribute('hidden', '');
    navTabs.innerHTML = '';
    document.body.classList.add('modal-open');
    resetModalFormDirty();

    kicker.textContent = 'YZA AUTOMOTIVE · SHOWROOM MANAGEMENT';
    const statusText = car.status_label || (car.status ? car.status.toUpperCase() : 'AKTIF');
    meta.innerHTML = `<span><strong>${car.kode || 'UNIT BARU'}</strong></span> &bull; <span>${car.merk || 'Mobil'} ${car.model || ''} (${car.tahun || ''})</span> &bull; <span class="badge-status is-${car.status || 'available'}">${statusText}</span>`;

    switch (action) {
        case 'detail':
        case 'specs':
            renderDetailAndSpecsModal(car, action, { title, kicker, meta, navTabs, body, footer, dialog });
            break;
        case 'stock':
            renderStockManagementModal(car, { title, kicker, meta, navTabs, body, footer, dialog });
            break;
        case 'edit':
            renderEditVehicleModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        case 'add_unit':
            renderAddUnitModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        case 'preorder':
            renderPreorderModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        case 'history':
            renderStockHistoryModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        case 'gallery':
            renderGalleryAndColorsModal(car, { title, kicker, meta, navTabs, body, footer, dialog });
            break;
        case 'deactivate':
            renderDeactivateConfirmationModal(car, { title, kicker, meta, body, footer, dialog });
            break;
        default:
            console.warn('Unknown action:', action);
            return;
    }

    backdrop.removeAttribute('hidden');
    dialog.focus();
}

function closeVehicleModal(force = false) {
    if (!force && isModalFormDirty) {
        if (!confirm('Terdapat perubahan formulir yang belum disimpan. Yakin ingin menutup popup?')) {
            return false;
        }
    }
    resetModalFormDirty();
    const backdrop = document.getElementById('vehicleModalBackdrop');
    if (backdrop) {
        backdrop.setAttribute('hidden', '');
    }
    document.body.classList.remove('modal-open');
    if (activeModalTriggerElement) {
        activeModalTriggerElement.focus();
        activeModalTriggerElement = null;
    }
    return true;
}

// Global Escape Key Listener for Modals
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const backdrop = document.getElementById('vehicleModalBackdrop');
        if (backdrop && !backdrop.hasAttribute('hidden')) {
            closeVehicleModal();
        }
    }
});

// Close when clicking directly on backdrop
document.getElementById('vehicleModalBackdrop')?.addEventListener('click', function (e) {
    if (e.target === this) {
        closeVehicleModal();
    }
});

// -------------------------------------------------------------
// 1 & 4. DETAIL & SPESIFIKASI MODAL (PHASE 3 & 6)
// -------------------------------------------------------------
function renderDetailAndSpecsModal(car, initialTab, dom) {
    dom.title.textContent = 'Detail & Spesifikasi Kendaraan';
    dom.navTabs.removeAttribute('hidden');
    dom.navTabs.innerHTML = `
        <button type="button" class="modal-tab-btn ${initialTab === 'detail' ? 'is-active' : ''}" data-target="pane-detail-overview" aria-selected="${initialTab === 'detail' ? 'true' : 'false'}">Ringkasan</button>
        <button type="button" class="modal-tab-btn ${initialTab === 'specs' ? 'is-active' : ''}" data-target="pane-detail-specs" aria-selected="${initialTab === 'specs' ? 'true' : 'false'}">Spesifikasi Lengkap</button>
        <button type="button" class="modal-tab-btn" data-target="pane-detail-stock" aria-selected="false">Ketersediaan Stok</button>
    `;

    const vinDisplay = car.is_virtual
        ? '<span class="text-muted">Belum ada unit fisik terdaftar (Model Showcase Katalog)</span>'
        : (car.nomor_rangka || '<span class="text-muted">Identitas unit fisik belum terverifikasi</span>');
    const engineDisplay = car.is_virtual
        ? '<span class="text-muted">Belum ada unit fisik terdaftar</span>'
        : (car.nomor_mesin || '<span class="text-muted">Data nomor mesin belum dilengkapi</span>');

    dom.body.innerHTML = `
        {{-- Pane 1: Ringkasan --}}
        <div id="pane-detail-overview" class="modal-pane ${initialTab === 'detail' ? 'is-active' : 'hidden'}" style="${initialTab === 'detail' ? 'display:block;' : 'display:none;'}">
            <div class="modal-grid-2col" style="margin-bottom:16px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:12px;">
                    <div style="width:100%; height:180px; border-radius:10px; overflow:hidden; background:#0f172a;">
                        <img src="${car.thumbnail}" alt="${car.merk} ${car.model}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                        <div class="modal-hero-price" style="font-size:18px;">${car.harga_jual_formatted}</div>
                        <div style="display:flex; gap:6px;">
                            <span class="showroom-badge is-${car.status}">${car.status_label ? car.status_label.toUpperCase() : car.status.toUpperCase()}</span>
                            <span class="showroom-badge is-condition">${car.kondisi.toUpperCase()}</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div class="modal-section-box" style="margin-bottom:0;">
                        <h4 style="margin:0 0 8px; font-size:16px; font-weight:800; color:#0f172a;">${car.merk} ${car.model}</h4>
                        <div style="color:#64748b; font-size:12px; margin-bottom:10px;">
                            Varian: <strong>${car.tipe || 'Varian Standar'}</strong> &bull; Tahun: <strong>${car.tahun}</strong> &bull; Warna: <strong>${car.warna}</strong>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            <span class="car-code-pill" style="font-size:11px;">KODE: ${car.kode}</span>
                            ${car.showcase_slug ? `<span class="showroom-slug-badge" style="font-size:11px;">${car.showcase_slug}</span>` : ''}
                            ${car.is_limited_edition ? '<span class="showroom-badge is-highlight">LIMITED EDITION</span>' : ''}
                        </div>
                    </div>

                    <div class="modal-section-box" style="margin-bottom:0; flex:1;">
                        <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase; margin-bottom:6px;">Catatan Ringkas Kendaraan</div>
                        <p style="margin:0; font-size:12px; color:#334155; line-height:1.6;">
                            ${car.deskripsi || '<em class="text-muted">Tidak ada catatan deskripsi tambahan untuk unit kendaraan ini.</em>'}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pane 2: Spesifikasi Lengkap --}}
        <div id="pane-detail-specs" class="modal-pane ${initialTab === 'specs' ? 'is-active' : 'hidden'}" style="${initialTab === 'specs' ? 'display:block;' : 'display:none;'}">
            <h4 class="section-micro-title" style="margin-top:0;">Parameter Teknis Kendaraan</h4>
            <dl class="modal-spec-dl" style="margin-bottom:18px;">
                <div class="modal-spec-row">
                    <dt>Merek &amp; Model</dt>
                    <dd>${car.merk} ${car.model}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tipe / Varian</dt>
                    <dd>${car.tipe || 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tahun Pembuatan</dt>
                    <dd>${car.tahun || 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Mesin / Konfigurasi</dt>
                    <dd>${car.tipe ? 'Spesifikasi Standar ' + car.tipe : 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Kapasitas Mesin</dt>
                    <dd>Belum dilengkapi</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Transmisi</dt>
                    <dd>${car.transmisi ? car.transmisi.toUpperCase() : 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tenaga / Daya Kuda</dt>
                    <dd>Belum dilengkapi</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tipe Bahan Bakar</dt>
                    <dd>${car.bahan_bakar ? car.bahan_bakar.toUpperCase() : 'Belum dilengkapi'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Jarak Tempuh (Kilometer)</dt>
                    <dd>${Number(car.kilometer).toLocaleString('id-ID')} km</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Warna Fisik Unit</dt>
                    <dd>${car.warna || 'Belum dilengkapi'}</dd>
                </div>
            </dl>

            <h4 class="section-micro-title">Identitas Registrasi Unit (Dealer Tracking)</h4>
            <dl class="modal-spec-dl">
                <div class="modal-spec-row">
                    <dt>Nomor Rangka (VIN)</dt>
                    <dd style="font-family:monospace;">${vinDisplay}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Nomor Mesin</dt>
                    <dd style="font-family:monospace;">${engineDisplay}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Tanggal Masuk Dealer</dt>
                    <dd>${car.tanggal_masuk || '—'}</dd>
                </div>
                <div class="modal-spec-row">
                    <dt>Sumber Kendaraan</dt>
                    <dd>Dealer YZA Automotive</dd>
                </div>
            </dl>
        </div>

        {{-- Pane 3: Ketersediaan Stok --}}
        <div id="pane-detail-stock" class="modal-pane hidden" style="display:none;">
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
                <div style="padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#64748b; font-weight:700;">TOTAL STOK</div>
                    <div style="font-size:22px; font-weight:800; color:#0f172a;">${car.stock_total}</div>
                </div>
                <div style="padding:14px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#047857; font-weight:700;">DAPAT DIBELI</div>
                    <div style="font-size:22px; font-weight:800; color:#047857;">${car.stock_orderable}</div>
                </div>
                <div style="padding:14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#b45309; font-weight:700;">RESERVASI</div>
                    <div style="font-size:22px; font-weight:800; color:#b45309;">${car.reserved_units}</div>
                </div>
                <div style="padding:14px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px; text-align:center;">
                    <div style="font-size:11px; color:#475569; font-weight:700;">TERJUAL</div>
                    <div style="font-size:22px; font-weight:800; color:#0f172a;">${car.sold_units}</div>
                </div>
            </div>

            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>${car.is_virtual ? 'Model ini merupakan showcase resmi katalog APM digital dan saat ini belum memiliki unit fisik tercatat di showroom. Anda dapat mendaftarkan unit fisik baru melalui tombol Tambah Unit.' : `Unit fisik yang tersedia di inventori dealer terdaftar dengan warna resmi: <strong>${car.warna}</strong>.`}</span>
            </div>

            <h4 class="section-micro-title">Pesanan Member &amp; Reservasi Aktif</h4>
            ${car.active_orders && car.active_orders.length > 0
                ? `<div style="overflow-x:auto;"><table class="modal-history-table"><thead><tr><th>Nomor Pesanan</th><th>Nama Pembeli</th><th>Status Pesanan</th></tr></thead><tbody>${car.active_orders.map(o => `<tr><td><strong>${o.nomor}</strong></td><td>${o.nama}</td><td><span class="badge-status is-active">${o.status}</span></td></tr>`).join('')}</tbody></table></div>`
                : '<div style="padding:18px; background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; text-align:center; color:#64748b;">Tidak ada pesanan member aktif yang mengikat unit ini saat ini.</div>'}
        </div>
    `;

    function updateDetailFooter(activePaneId) {
        if (activePaneId === 'pane-detail-overview') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                ${!isUserOwner ? (car.is_virtual
                    ? `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit')" style="background:#059669;">+ Tambah Unit ke Inventori</button>`
                    : `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'edit')">Edit Data Kendaraan</button>`
                ) : ''}
            `;
        } else if (activePaneId === 'pane-detail-specs') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                <button type="button" class="btn-modal-secondary" onclick="switchDetailTab('pane-detail-overview')">Lihat Ringkasan</button>
            `;
        } else if (activePaneId === 'pane-detail-stock') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                ${!isUserOwner ? (car.is_virtual
                    ? `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit')" style="background:#059669;">+ Tambah Unit ke Inventori</button>`
                    : `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'stock')">Kelola Stok Kendaraan</button>`
                ) : ''}
            `;
        }
    }

    window.switchDetailTab = function(targetId) {
        const btn = dom.navTabs.querySelector(`.modal-tab-btn[data-target="${targetId}"]`);
        if (btn) btn.click();
    };

    setupModalPaneTabs(dom.navTabs, dom.body, updateDetailFooter);
    updateDetailFooter(initialTab === 'specs' ? 'pane-detail-specs' : 'pane-detail-overview');
}

// -------------------------------------------------------------
// 2. MODAL MANAJEMEN STOK KENDARAAN (PHASE 4)
// -------------------------------------------------------------
function colorStockEscape(value) { const el = document.createElement('span'); el.textContent = String(value ?? ''); return el.innerHTML; }
function renderColorStockCards(car) {
    return '<section class="color-stock-summary"><h4>Stok per warna fisik</h4><div class="color-stock-grid">' + (car.color_stocks || []).map(c => '<article class="color-stock-card"><strong><i style="background:' + (/^#[0-9a-f]{6}$/i.test(c.swatch) ? c.swatch : '#cbd5e1') + '"></i>' + colorStockEscape(c.label) + '</strong><span class="color-stock-status">' + (c.stock > 0 ? 'Tersedia' : '× Stok Habis') + '</span><dl><div><dt>Fisik</dt><dd>' + Number(c.stock_total) + '</dd></div><div><dt>Reservasi</dt><dd>' + Number(c.reserved_units) + '</dd></div><div><dt>Ready</dt><dd>' + Number(c.stock) + '</dd></div></dl>' + (c.unverified_units > 0 ? '<small>' + Number(c.unverified_units) + ' stok agregat lama belum terverifikasi</small>' : '') + '</article>').join('') + '</div></section>';
}
function renderStockManagementModal(car, dom) {
    dom.title.textContent = 'Manajemen Stok Kendaraan';
    dom.dialog.classList.add('is-medium');
    dom.navTabs.removeAttribute('hidden');
    dom.navTabs.innerHTML = `
        <button type="button" class="modal-tab-btn is-active" data-target="pane-stock-overview" aria-selected="true">Ikhtisar Stok</button>
        <button type="button" class="modal-tab-btn" data-target="pane-stock-adjust" aria-selected="false">Penyesuaian Stok</button>
    `;

    const recentHistories = (car.histories || []).slice(0, 4);

    dom.body.innerHTML = `
        {{-- Pane 1: Ikhtisar Stok (Monitoring / Read-only) --}}
        <div id="pane-stock-overview" class="modal-pane is-active" style="display:block;">
            <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:16px;">
                <div style="padding:14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                    <div style="font-size:11px; color:#64748b; font-weight:700;">TOTAL STOK FISIK</div>
                    <div style="font-size:24px; font-weight:800; color:#0f172a;">${car.stock_total} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
                <div style="padding:14px; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px;">
                    <div style="font-size:11px; color:#047857; font-weight:700;">STOK DAPAT DIBELI</div>
                    <div style="font-size:24px; font-weight:800; color:#047857;">${car.stock_orderable} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
                <div style="padding:14px; background:#fef3c7; border:1px solid #fde68a; border-radius:10px;">
                    <div style="font-size:11px; color:#b45309; font-weight:700;">RESERVASI AKTIF (SPK/BOOKING)</div>
                    <div style="font-size:24px; font-weight:800; color:#b45309;">${car.reserved_units} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
                <div style="padding:14px; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:10px;">
                    <div style="font-size:11px; color:#475569; font-weight:700;">UNIT TELAH TERJUAL</div>
                    <div style="font-size:24px; font-weight:800; color:#0f172a;">${car.sold_units} <small style="font-size:12px; font-weight:500;">unit</small></div>
                </div>
            </div>

            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Warna fisik unit terdaftar di inventori: <strong>${car.warna || 'Standar'}</strong>. Pengurangan stok tidak diizinkan melebihi ${car.reserved_units} unit yang sedang direservasi.</span>
            </div>

            <h4 class="section-micro-title">Riwayat Pergerakan Stok Terbaru</h4>
            ${recentHistories.length > 0 ? `
                <div style="overflow-x:auto;">
                    <table class="modal-history-table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Jenis</th>
                                <th style="text-align:center;">Mutasi</th>
                                <th>Sebelum &rarr; Sesudah</th>
                                <th>Alasan / Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${recentHistories.map(h => `
                                <tr>
                                    <td style="font-size:11px; color:#64748b; white-space:nowrap;">${h.tanggal}</td>
                                    <td><strong>${h.jenis}</strong></td>
                                    <td style="text-align:center;"><span style="font-weight:700; color:${h.jumlah.toString().startsWith('-') ? '#dc2626' : '#16a34a'};">${h.jumlah} unit</span></td>
                                    <td style="font-family:monospace; font-size:11px;">${h.before_after}</td>
                                    <td style="color:#64748b;">${colorStockEscape(h.catatan)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : `
                <div style="padding:18px; background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; text-align:center; color:#64748b;">
                    Belum ada riwayat pergerakan stok tambahan selain inisialisasi awal.
                </div>
            `}
        </div>

        {{-- Pane 2: Form Penyesuaian Stok --}}
        <div id="pane-stock-adjust" class="modal-pane hidden" style="display:none;">
            ${isUserOwner ? `
                <div class="modal-warning-callout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span>Role Owner hanya memiliki hak akses baca (read-only). Penyesuaian stok dinonaktifkan.</span>
                </div>
            ` : `
                <div class="modal-strip-summary">
                    <span>Stok Fisik Saat Ini: <strong>${car.stock_total} unit</strong></span>
                    <span>Reservasi Terikat: <strong>${car.reserved_units} unit</strong></span>
                    <span>Batas Minimum Stok Baru: <strong>${car.reserved_units} unit</strong></span>
                </div>

                <form id="stockAdjustForm" onsubmit="submitStockAdjust(event, ${car.id})">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="stok_baru">Total Stok Baru <span class="required-mark">*</span></label>
                        <input type="number" id="stok_baru" name="stok_baru" class="modal-form-input" min="${car.reserved_units}" value="${Number(car.stock_total) + Number(car.sold_units)}" readonly required>
                        <small style="color:#64748b; font-size:11px;">Rekonsiliasi mengikuti unit beridentitas nyata. Tambah stok melalui Tambah Unit Fisik; koreksi warna/status melalui Edit Unit. Unit terikat transaksi dilindungi.</small>
                    </div>

                    <div id="stockDeltaBox" style="padding:10px 14px; border-radius:8px; background:#f8fafc; border:1px solid #e2e8f0; margin-bottom:14px; font-size:12px; display:flex; justify-content:space-between; align-items:center;">
                        <span>Dampak Kuantitas: <strong id="stockDeltaValue" style="color:#64748b;">0 unit (Tidak ada perubahan)</strong></span>
                        <span>Estimasi Siap Jual: <strong id="stockImpactPreview" style="color:#047857;">${car.stock_orderable} unit</strong></span>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="alasan_preset">Pilihan Alasan Penyesuaian</label>
                        <select id="alasan_preset" class="modal-form-select" onchange="onStockReasonPresetChange(this)">
                            <option value="">-- Pilih Alasan Penyesuaian Standar --</option>
                            <option value="Restock unit baru dari dealer">Restock unit baru dari dealer</option>
                            <option value="Rekonsiliasi hasil stock opname fisik gudang">Rekonsiliasi hasil stock opname fisik gudang</option>
                            <option value="Pengurangan unit rusak / cacat pengiriman">Pengurangan unit rusak / cacat pengiriman</option>
                            <option value="Retur unit ke distributor / leasing">Retur unit ke distributor / leasing</option>
                            <option value="custom">Alasan khusus lainnya...</option>
                        </select>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="alasan">Keterangan Alasan Resmi <span class="required-mark">*</span></label>
                        <input type="text" id="alasan" name="alasan" class="modal-form-input" placeholder="Wajib diisi: Penjelasan penyesuaian inventori" required>
                    </div>

                    <div class="modal-form-group">
                        <label class="modal-form-label" for="catatan">Catatan Audit / Referensi Dokumen</label>
                        <textarea id="catatan" name="catatan" class="modal-form-textarea" placeholder="Opsional: Nomor Surat Jalan, Berita Acara, No PO, dll..."></textarea>
                    </div>

                    <div class="modal-form-group"><label class="modal-form-label">Bukti / nomor dokumen *</label><input name="bukti" class="modal-form-input" minlength="5" maxlength="500" required></div><div id="stockAdjustError" class="modal-field-error" style="display:none; margin-bottom:12px;"></div>

                    <div style="display:none;">
                        <button type="submit" id="btnSubmitStockAdjust">Submit</button>
                    </div>
                </form>
            `}
        </div>
    `;

    // Setup live calculation on stock input
    const stockInput = dom.body.querySelector('#stok_baru');
    const deltaDisplay = dom.body.querySelector('#stockDeltaValue');
    const impactPreview = dom.body.querySelector('#stockImpactPreview');
    if (stockInput && deltaDisplay) {
        const updateDelta = () => {
            const val = parseInt(stockInput.value, 10);
            if (isNaN(val)) return;
            const delta = val - car.stock_total;
            const newOrderable = Math.max(0, val - car.reserved_units);
            if (delta > 0) {
                deltaDisplay.innerHTML = `<span style="color:#16a34a; font-weight:700;">+${delta} unit (Penambahan Stok)</span>`;
            } else if (delta < 0) {
                deltaDisplay.innerHTML = `<span style="color:#dc2626; font-weight:700;">${delta} unit (Pengurangan Stok)</span>`;
            } else {
                deltaDisplay.innerHTML = `<span style="color:#64748b; font-weight:600;">0 unit (Tidak ada perubahan)</span>`;
            }
            if (impactPreview) {
                impactPreview.textContent = `${newOrderable} unit`;
            }
        };
        stockInput.addEventListener('input', updateDelta);
    }

    dom.body.insertAdjacentHTML('afterbegin', renderColorStockCards(car));
    function updateStockFooter(activePaneId) {
        if (activePaneId === 'pane-stock-overview') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                <button type="button" class="btn-modal-secondary" onclick="openVehicleActionModal(${car.id}, 'history')">Lihat Riwayat</button>
                ${!isUserOwner ? `<button type="button" class="btn-modal-primary" onclick="switchStockTab('pane-stock-adjust')">Sesuaikan Stok</button>` : ''}
            `;
        } else {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="switchStockTab('pane-stock-overview')">Kembali ke Ikhtisar</button>
                ${!isUserOwner ? `<button type="submit" form="stockAdjustForm" class="btn-modal-primary" id="btnFooterStockAdjust">Simpan Penyesuaian</button>` : ''}
            `;
        }
    }

    window.switchStockTab = function(targetId) {
        const btn = dom.navTabs.querySelector(`.modal-tab-btn[data-target="${targetId}"]`);
        if (btn) btn.click();
    };

    window.onStockReasonPresetChange = function(selectEl) {
        const input = document.getElementById('alasan');
        if (!input) return;
        if (selectEl.value && selectEl.value !== 'custom') {
            input.value = selectEl.value;
        } else if (selectEl.value === 'custom') {
            input.value = '';
            input.focus();
        }
    };

    setupModalPaneTabs(dom.navTabs, dom.body, updateStockFooter);
    updateStockFooter('pane-stock-overview');
}

// Handler Submit Penyesuaian Stok
async function submitStockAdjust(e, carId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSubmitStockAdjust');
    const errBox = document.getElementById('stockAdjustError');
    errBox.style.display = 'none';

    const car = getCarPayload(carId);
    if (!car) return;

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const formData = new FormData(form);

    try {
        const response = await fetch(car.urls.stock_adjust, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok && data.success) {
            // Update local payload
            const newAvailability = data.availability || {};
            updateLocalCarPayload(carId, {
                stock_total: newAvailability.stock_total ?? car.stock_total,
                stock_orderable: newAvailability.stock ?? car.stock_orderable,
            });

            // Update row in table
            const row = document.getElementById('row-car-' + carId);
            if (row) {
                const stockCell = row.querySelector('.td-stock-physical strong') || row.querySelector('.td-stock strong');
                if (stockCell) stockCell.textContent = (newAvailability.stock_total ?? car.stock_total);
                const orderableCell = row.querySelector('.td-stock-ready .stock-ready-badge') || row.querySelector('.td-orderable .orderable-unit-badge');
                if (orderableCell) {
                    const count = Number(newAvailability.stock || 0);
                    orderableCell.textContent = count + ' unit';
                    orderableCell.classList.toggle('is-ready', count > 0);
                    orderableCell.classList.toggle('is-available', count > 0);
                    orderableCell.classList.toggle('is-zero', count <= 0);
                    orderableCell.classList.toggle('is-empty', count <= 0);
                }
                const availBadge = row.querySelector('.td-availability .badge-availability');
                if (availBadge) {
                    const count = Number(newAvailability.stock || 0);
                    if (count > 0) {
                        availBadge.className = 'badge-availability is-ready';
                        availBadge.textContent = 'Ready';
                    } else {
                        availBadge.className = 'badge-availability is-exhausted';
                        availBadge.textContent = 'Stok Habis';
                    }
                }
            }

            closeVehicleModal();
            showNotificationToast('Sukses: ' + data.message, 'success');
        } else {
            errBox.textContent = data.message || 'Terjadi kesalahan saat menyimpan stok.';
            errBox.style.display = 'block';
        }
    } catch (err) {
        errBox.textContent = 'Gagal menghubungi server: ' + err.message;
        errBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Penyesuaian Stok';
    }
}

// -------------------------------------------------------------
// 3. MODAL EDIT DATA KENDARAAN (PHASE 5)
// -------------------------------------------------------------
function renderEditVehicleModal(car, dom) {
    dom.title.textContent = 'Edit Data Kendaraan';

    if (isUserOwner) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Role Owner hanya memiliki hak akses baca (read-only). Edit data kendaraan dinonaktifkan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    dom.body.innerHTML = `
        <form id="editVehicleForm" onsubmit="submitEditVehicle(event, ${car.id})">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
            <input type="hidden" name="_method" value="PUT">

            <div class="modal-grid-2col">
                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_merk">Merek <span class="required-mark">*</span></label>
                    <input type="text" id="edit_merk" name="merk" class="modal-form-input" value="${car.merk}" required>
                    <div class="modal-field-error" data-error-field="merk"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_model">Model <span class="required-mark">*</span></label>
                    <input type="text" id="edit_model" name="model" class="modal-form-input" value="${car.model}" required>
                    <div class="modal-field-error" data-error-field="model"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_tipe">Tipe / Varian</label>
                    <input type="text" id="edit_tipe" name="tipe" class="modal-form-input" value="${car.tipe || ''}">
                    <div class="modal-field-error" data-error-field="tipe"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_tahun">Tahun Pembuatan <span class="required-mark">*</span></label>
                    <input type="number" id="edit_tahun" name="tahun" class="modal-form-input" value="${car.tahun}" min="1990" max="2035" required>
                    <div class="modal-field-error" data-error-field="tahun"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_warna">Warna Fisik Unit <span class="required-mark">*</span></label>
                    <input type="text" id="edit_warna" name="warna" class="modal-form-input" value="${car.warna}" required>
                    <div class="modal-field-error" data-error-field="warna"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_kondisi">Kondisi Kendaraan <span class="required-mark">*</span></label>
                    <select id="edit_kondisi" name="kondisi" class="modal-form-select" required>
                        <option value="baru" ${car.kondisi === 'baru' ? 'selected' : ''}>Baru</option>
                        <option value="bekas" ${car.kondisi === 'bekas' ? 'selected' : ''}>Bekas</option>
                    </select>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_harga_jual">Harga Jual (Rp) <span class="required-mark">*</span></label>
                    <input type="number" step="0.01" id="edit_harga_jual" name="harga_jual" class="modal-form-input" value="${car.harga_jual}" required>
                    <div class="modal-field-error" data-error-field="harga_jual"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_showcase_slug">Showroom Slug (Koleksi)</label>
                    <input type="text" id="edit_showcase_slug" name="showcase_slug" class="modal-form-input" value="${car.showcase_slug || ''}" placeholder="contoh: lexus-lfa">
                    <div class="modal-field-error" data-error-field="showcase_slug"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="edit_status">Status Ketersediaan <span class="required-mark">*</span></label>
                    <select id="edit_status" name="status" class="modal-form-select" ${car.is_sold ? 'disabled' : ''} required>
                        <option value="tersedia" ${car.status === 'tersedia' ? 'selected' : ''}>Tersedia</option>
                        <option value="dibooking" ${car.status === 'dibooking' ? 'selected' : ''}>Dipesan</option>
                        <option value="dalam_pemeriksaan" ${car.status === 'dalam_pemeriksaan' ? 'selected' : ''}>Dalam Pemeriksaan</option>
                        <option value="terjual" ${car.status === 'terjual' ? 'selected' : ''}>Terjual</option>
                        <option value="nonaktif" ${car.status === 'nonaktif' ? 'selected' : ''}>Nonaktif</option>
                    </select>
                    ${car.is_sold ? '<small style="color:#64748b; font-size:10px;">Unit terjual statusnya terkunci.</small>' : ''}
                </div>

                <div class="modal-form-group" style="justify-content:center;">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:700; margin-top:16px;">
                        <input type="hidden" name="is_limited_edition" value="0">
                        <input type="checkbox" name="is_limited_edition" value="1" ${car.is_limited_edition ? 'checked' : ''} class="custom-checkbox">
                        <span>Tandai Sebagai Limited Edition</span>
                    </label>
                </div>

                <div class="modal-form-group" style="grid-column: 1 / -1;">
                    <label class="modal-form-label" for="edit_deskripsi">Deskripsi Kendaraan</label>
                    <textarea id="edit_deskripsi" name="deskripsi" class="modal-form-textarea" placeholder="Spesifikasi tambahan, catatan unit, riwayat perawatan...">${car.deskripsi || ''}</textarea>
                </div>
            </div>

            <div id="editVehicleGlobalError" class="modal-field-error" style="display:none; margin-bottom:12px;"></div>
        <div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Alasan perubahan stok *</label><input name="alasan_stok" class="modal-form-input" minlength="5" maxlength="160" required></div><div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Bukti / nomor dokumen verifikasi *</label><input name="bukti_stok" class="modal-form-input" minlength="5" maxlength="500" placeholder="Nomor surat jalan / berita acara pemeriksaan" required></div></form>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
        <button type="submit" form="editVehicleForm" class="btn-modal-primary" id="btnSubmitEditVehicle">Simpan Perubahan</button>
    `;
}

// Handler Submit Edit Kendaraan
async function submitEditVehicle(e, carId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSubmitEditVehicle');
    const globalErr = document.getElementById('editVehicleGlobalError');
    globalErr.style.display = 'none';

    // Clear individual field errors
    form.querySelectorAll('[data-error-field]').forEach(el => el.textContent = '');

    const car = getCarPayload(carId);
    if (!car) return;

    btn.disabled = true;
    btn.textContent = 'Menyimpan...';

    const formData = new FormData(form);

    try {
        const response = await fetch(car.urls.update, {
            method: 'POST', // with _method=PUT
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok && data.success) {
            const updated = data.mobil || {};
            updateLocalCarPayload(carId, {
                merk: updated.merk || car.merk,
                model: updated.model || car.model,
                tipe: updated.tipe || car.tipe,
                tahun: updated.tahun || car.tahun,
                warna: updated.warna || car.warna,
                kondisi: updated.kondisi || car.kondisi,
                status: updated.status || car.status,
                harga_jual: updated.harga_jual || car.harga_jual,
                harga_jual_formatted: 'Rp ' + Number(updated.harga_jual).toLocaleString('id-ID'),
                showcase_slug: updated.showcase_slug || car.showcase_slug,
                deskripsi: updated.deskripsi || car.deskripsi,
            });

            // Update row in table
            const row = document.getElementById('row-car-' + carId);
            if (row) {
                const titleEl = row.querySelector('.car-title');
                if (titleEl) titleEl.textContent = (updated.merk || car.merk) + ' ' + (updated.model || car.model);
                const subtextEl = row.querySelector('.car-subtext');
                if (subtextEl) subtextEl.innerHTML = `${updated.tipe || 'Varian Standar'} &bull; ${updated.tahun || car.tahun} &bull; ${updated.warna || car.warna}`;
                const priceEl = row.querySelector('.td-price strong');
                if (priceEl) priceEl.textContent = 'Rp ' + Number(updated.harga_jual).toLocaleString('id-ID');
                const statusBadge = row.querySelector('.td-status .badge-status');
                if (statusBadge) {
                    const st = (updated.status || car.status).toLowerCase();
                    statusBadge.className = 'badge-status is-' + st;
                    statusBadge.textContent = st.charAt(0).toUpperCase() + st.slice(1);
                }
            }

            closeVehicleModal();
            showNotificationToast('Sukses: ' + data.message, 'success');
        } else if (response.status === 422 && data.errors) {
            // Validation errors
            for (const [field, messages] of Object.entries(data.errors)) {
                const errEl = form.querySelector(`[data-error-field="${field}"]`);
                if (errEl) {
                    errEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            }
            globalErr.textContent = 'Periksa kembali isian formulir yang ditandai merah.';
            globalErr.style.display = 'block';
        } else {
            globalErr.textContent = data.message || 'Gagal menyimpan perubahan kendaraan.';
            globalErr.style.display = 'block';
        }
    } catch (err) {
        globalErr.textContent = 'Gagal menghubungi server: ' + err.message;
        globalErr.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Simpan Perubahan';
    }
}

// -------------------------------------------------------------
// 3B. MODAL TAMBAH UNIT FISIK KE INVENTORI (CODEX REFINEMENT)
// -------------------------------------------------------------
function renderAddUnitModal(car, dom) {
    const isNew = car.id === 'new';
    dom.title.textContent = isNew ? 'Daftarkan Unit Mobil Baru ke Inventori' : `Tambah Unit Fisik — ${car.merk} ${car.model}`;
    dom.kicker.textContent = 'INVENTORI FISIK DEALER · REGISTRASI UNIT';
    dom.dialog.classList.add('is-wide');

    if (isUserOwner) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Role Owner hanya memiliki hak akses baca (read-only). Pendaftaran unit baru dinonaktifkan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    const defaultCode = (isNew || !car.kode || car.is_virtual)
        ? ('YZA-' + Math.floor(1000 + Math.random() * 9000))
        : (car.kode + '-U' + Math.floor(10 + Math.random() * 90));

    dom.body.innerHTML = `
        <form id="addUnitVehicleForm" onsubmit="submitAddUnit(event, '${car.id}')" autocomplete="off">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
            ${car.showcase_slug ? `<input type="hidden" name="showcase_slug" value="${car.showcase_slug}">` : ''}
            <input type="hidden" name="catalog_identity" value="${colorStockEscape(car.catalog_key || (car.id !== 'new' ? 'mobil:' + car.id : ''))}">

            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Formulir ini mendaftarkan unit fisik kendaraan ke inventori dealer. Seluruh nomor identitas (VIN & Nomor Mesin) wajib diisi valid dan akan diverifikasi keasliannya di sistem.</span>
            </div>

            <div class="modal-form-grid">
                {{-- Merek & Model --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_merk">Merek Kendaraan <span class="required-mark">*</span></label>
                    <input type="text" id="add_unit_merk" name="merk" class="modal-form-input" value="${car.merk || ''}" ${(!isNew && car.merk) ? 'readonly style="background:#f8fafc;"' : 'required placeholder="contoh: Toyota, Lexus, Porsche"'}>
                    <div class="modal-field-error" data-error-field="merk" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_model">Model Kendaraan <span class="required-mark">*</span></label>
                    <input type="text" id="add_unit_model" name="model" class="modal-form-input" value="${car.model || ''}" ${(!isNew && car.model) ? 'readonly style="background:#f8fafc;"' : 'required placeholder="contoh: GR Supra, 911 GT3"'}>
                    <div class="modal-field-error" data-error-field="model" style="display:none;"></div>
                </div>

                {{-- Varian / Tipe & Tahun --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_tipe">Tipe / Varian</label>
                    <input type="text" id="add_unit_tipe" name="tipe" class="modal-form-input" value="${car.tipe || ''}" placeholder="contoh: 3.0L Pro, Performance Edition">
                    <div class="modal-field-error" data-error-field="tipe" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_tahun">Tahun Pembuatan <span class="required-mark">*</span></label>
                    <input type="number" id="add_unit_tahun" name="tahun" class="modal-form-input" value="${car.tahun || new Date().getFullYear()}" min="1950" max="${new Date().getFullYear() + 1}" required>
                    <div class="modal-field-error" data-error-field="tahun" style="display:none;"></div>
                </div>

                {{-- Warna Fisik Unit & Kode Mobil --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_warna">Warna Fisik Unit <span class="required-mark">*</span></label>
                    <input type="text" id="add_unit_warna" name="warna" class="modal-form-input" value="${car.warna || ''}" placeholder="contoh: Pearl White, Nardo Gray, Midnight Black" required>
                    <div class="modal-field-error" data-error-field="warna" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_kode">Kode Kendaraan (Internal Dealer) <span class="required-mark">*</span></label>
                    <input type="text" id="add_unit_kode" name="kode_mobil" class="modal-form-input" value="${defaultCode}" required>
                    <div class="modal-field-error" data-error-field="kode_mobil" style="display:none;"></div>
                </div>

                {{-- Nomor Rangka (VIN) & Nomor Mesin --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_vin">Nomor Rangka (VIN Fisik) <span class="required-mark">*</span></label>
                    <input type="text" id="add_unit_vin" name="nomor_rangka" class="modal-form-input" style="font-family:monospace; text-transform:uppercase;" placeholder="Contoh: MH3ABC123DK004921" required>
                    <div class="modal-field-error" data-error-field="nomor_rangka" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_mesin">Nomor Mesin Fisik <span class="required-mark">*</span></label>
                    <input type="text" id="add_unit_mesin" name="nomor_mesin" class="modal-form-input" style="font-family:monospace; text-transform:uppercase;" placeholder="Contoh: B58B30-9823140" required>
                    <div class="modal-field-error" data-error-field="nomor_mesin" style="display:none;"></div>
                </div>

                {{-- Kondisi & Kilometer --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_kondisi">Kondisi Fisik <span class="required-mark">*</span></label>
                    <select id="add_unit_kondisi" name="kondisi" class="modal-form-select" required>
                        <option value="baru" ${car.kondisi === 'baru' ? 'selected' : ''}>Baru (Brand New / Showroom)</option>
                        <option value="bekas" ${car.kondisi === 'bekas' ? 'selected' : ''}>Bekas (Pre-Owned Bersertifikat)</option>
                    </select>
                    <div class="modal-field-error" data-error-field="kondisi" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_kilometer">Kilometer (Odometer) <span class="required-mark">*</span></label>
                    <input type="number" id="add_unit_kilometer" name="kilometer" class="modal-form-input" value="0" min="0" required>
                    <div class="modal-field-error" data-error-field="kilometer" style="display:none;"></div>
                </div>

                {{-- Harga Beli & Harga Jual --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_harga_beli">Harga Beli / Modal (Rp) <span class="required-mark">*</span></label>
                    <input type="number" id="add_unit_harga_beli" name="harga_beli" class="modal-form-input" value="${car.harga_beli || Math.round((car.harga_jual || 1000000000) * 0.85)}" min="0" required>
                    <div class="modal-field-error" data-error-field="harga_beli" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_harga_jual">Harga Jual Showroom (Rp) <span class="required-mark">*</span></label>
                    <input type="number" id="add_unit_harga_jual" name="harga_jual" class="modal-form-input" value="${car.harga_jual || ''}" min="0" required>
                    <div class="modal-field-error" data-error-field="harga_jual" style="display:none;"></div>
                </div>

                {{-- Transmisi & Bahan Bakar --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_transmisi">Transmisi</label>
                    <select id="add_unit_transmisi" name="transmisi" class="modal-form-select">
                        <option value="automatic" ${car.transmisi === 'automatic' ? 'selected' : ''}>Automatic</option>
                        <option value="manual" ${car.transmisi === 'manual' ? 'selected' : ''}>Manual</option>
                        <option value="cvt" ${car.transmisi === 'cvt' ? 'selected' : ''}>CVT</option>
                        <option value="dct" ${car.transmisi === 'dct' ? 'selected' : ''}>DCT</option>
                        <option value="lainnya" ${car.transmisi === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                    </select>
                    <div class="modal-field-error" data-error-field="transmisi" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_bahan_bakar">Bahan Bakar</label>
                    <select id="add_unit_bahan_bakar" name="bahan_bakar" class="modal-form-select">
                        <option value="bensin" ${car.bahan_bakar === 'bensin' ? 'selected' : ''}>Bensin</option>
                        <option value="diesel" ${car.bahan_bakar === 'diesel' ? 'selected' : ''}>Diesel</option>
                        <option value="hybrid" ${car.bahan_bakar === 'hybrid' ? 'selected' : ''}>Hybrid</option>
                        <option value="listrik" ${car.bahan_bakar === 'listrik' ? 'selected' : ''}>Listrik (EV)</option>
                        <option value="lainnya" ${car.bahan_bakar === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                    </select>
                    <div class="modal-field-error" data-error-field="bahan_bakar" style="display:none;"></div>
                </div>

                {{-- Status Masuk & Tanggal --}}
                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_status">Status Awal Unit <span class="required-mark">*</span></label>
                    <select id="add_unit_status" name="status" class="modal-form-select" required>
                        <option value="tersedia">Tersedia (Sudah Diverifikasi)</option>
                        <option value="dalam_pemeriksaan" selected>Dalam Pemeriksaan (Menunggu Verifikasi)</option>
                    </select>
                    <div class="modal-field-error" data-error-field="status" style="display:none;"></div>
                </div>

                <div class="modal-form-group">
                    <label class="modal-form-label" for="add_unit_tanggal_masuk">Tanggal Masuk Dealer</label>
                    <input type="date" id="add_unit_tanggal_masuk" name="tanggal_masuk" class="modal-form-input" value="${new Date().toISOString().split('T')[0]}">
                    <div class="modal-field-error" data-error-field="tanggal_masuk" style="display:none;"></div>
                </div>

                {{-- Catatan Dokumen & BASTK --}}
                <div class="modal-form-group" style="grid-column: 1 / -1;">
                    <label class="modal-form-label" for="add_unit_deskripsi">Catatan Dokumen / BASTK / Keterangan Unit</label>
                    <textarea id="add_unit_deskripsi" name="deskripsi" class="modal-form-textarea" placeholder="Kelengkapan faktur, BPKB, STNK, berita acara serah terima kendaraan (BASTK), atau kelengkapan aksesoris...">${car.deskripsi || ''}</textarea>
                    <div class="modal-field-error" data-error-field="deskripsi" style="display:none;"></div>
                </div>
            </div>

            <div id="addUnitGlobalError" class="modal-field-error" style="display:none; margin-top:12px; margin-bottom:8px;"></div>
        <div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Alasan perubahan stok *</label><input name="alasan_stok" class="modal-form-input" minlength="5" maxlength="160" required></div><div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Bukti / nomor dokumen verifikasi *</label><input name="bukti_stok" class="modal-form-input" minlength="5" maxlength="500" placeholder="Nomor surat jalan / berita acara pemeriksaan" required></div></form>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
        <button type="submit" form="addUnitVehicleForm" class="btn-modal-primary" id="btnSubmitAddUnit" style="background:#059669; border-color:#059669;">+ Simpan Unit ke Inventori</button>
    `;

    setupFormDirtyTracking(document.getElementById('addUnitVehicleForm'));
}

async function submitAddUnit(e, carId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('btnSubmitAddUnit');
    const globalErr = document.getElementById('addUnitGlobalError');
    if (globalErr) {
        globalErr.style.display = 'none';
        globalErr.textContent = '';
    }

    // Reset inline field errors
    form.querySelectorAll('[data-error-field]').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });

    btn.disabled = true;
    btn.textContent = 'Menyimpan Unit Fisik...';

    const formData = new FormData(form);

    try {
        const response = await fetch(@json(route('admin.mobil.store')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            const newCar = data.mobil || {};
            resetModalFormDirty();
            closeVehicleModal(true);
            showNotificationToast(data.message || 'Unit kendaraan berhasil didaftarkan ke inventori.', 'success');

            window.location.reload();
        } else if (response.status === 422 && data.errors) {
            for (const [field, messages] of Object.entries(data.errors)) {
                const errEl = form.querySelector(`[data-error-field="${field}"]`);
                if (errEl) {
                    errEl.textContent = Array.isArray(messages) ? messages[0] : messages;
                    errEl.style.display = 'block';
                }
            }
            if (globalErr) {
                globalErr.textContent = 'Terdapat data formulir yang belum lengkap atau sudah terdaftar.';
                globalErr.style.display = 'block';
            }
        } else {
            if (globalErr) {
                globalErr.textContent = data.message || 'Gagal menyimpan unit ke inventori.';
                globalErr.style.display = 'block';
            }
        }
    } catch (err) {
        if (globalErr) {
            globalErr.textContent = 'Gagal menghubungi server: ' + err.message;
            globalErr.style.display = 'block';
        }
    } finally {
        btn.disabled = false;
        btn.textContent = '+ Simpan Unit ke Inventori';
    }
}

// -------------------------------------------------------------
// 3C. MODAL PERMINTAAN PRE-ORDER (CODEX REFINEMENT)
// -------------------------------------------------------------
function renderPreorderModal(car, dom) {
    dom.title.textContent = `Permintaan Pre-Order — ${car.merk} ${car.model}`;
    dom.kicker.textContent = 'PRE-ORDER MEMBER · MANAJEMEN ANTREAN UNIT';
    dom.dialog.classList.add('is-wide');

    const preorders = car.preorders || [];
    const stockOrderable = Number(car.stock_orderable || 0);

    dom.body.innerHTML = `
        <div id="preorderListView">
            <div class="preorder-modal-summary-bar">
                <div class="summary-stat-box">
                    <span class="label">Total Pre-Order Aktif</span>
                    <span class="val highlight">${preorders.length} Permintaan</span>
                </div>
                <div class="summary-stat-box">
                    <span class="label">Stok Fisik Siap Jual</span>
                    <span class="val ${stockOrderable > 0 ? 'text-green' : 'text-amber'}">
                        ${stockOrderable > 0 ? stockOrderable + ' Unit Siap Jual' : '0 Unit (Stok Habis / Belum Ada)'}
                    </span>
                </div>
                <div class="summary-stat-box">
                    <span class="label">Model Kendaraan</span>
                    <span class="val">${car.merk} ${car.model} (${car.tahun || ''})</span>
                </div>
            </div>

            ${preorders.length === 0 ? `
                <div class="preorder-empty-box">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin:0 auto 12px; display:block;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <h4 style="margin:0 0 6px; font-weight:700; color:#0f172a; font-size:15px;">Tidak Ada Permintaan Pre-Order Aktif</h4>
                    <p style="margin:0 0 16px; font-size:12px; color:#64748b; max-width:440px; margin-inline:auto;">
                        Belum ada member yang mengajukan pre-order untuk model <strong>${car.merk} ${car.model}</strong> saat ini.
                    </p>
                    ${!isUserOwner ? `
                        <button type="button" class="btn-action-inline is-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit')">
                            + Daftarkan Unit Baru ke Inventori
                        </button>
                    ` : ''}
                </div>
            ` : `
                <div style="overflow-x:auto;">
                    <table class="modal-history-table">
                        <thead>
                            <tr>
                                <th>Kode &amp; Tanggal</th>
                                <th>Member &amp; Kontak</th>
                                <th>Varian &amp; Warna</th>
                                <th style="text-align:center;">Status Pre-Order</th>
                                <th style="text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${preorders.map(po => `
                                <tr>
                                    <td>
                                        <strong style="color:#0f172a;">${po.kode_preorder}</strong>
                                        <div style="font-size:11px; color:#64748b;">${po.created_at}</div>
                                    </td>
                                    <td>
                                        <strong style="color:#0f172a;">${po.customer_name}</strong>
                                        <div style="font-size:11px; color:#64748b;">${po.customer_code} &bull; ${po.customer_phone}</div>
                                    </td>
                                    <td>
                                        <div style="color:#0f172a; font-weight:600;">${po.variant || 'Standar'} (${po.year})</div>
                                        <div style="font-size:11px; color:#64748b;">Warna: <strong style="color:#334155;">${po.color}</strong></div>
                                    </td>
                                    <td style="text-align:center;">
                                        <span class="badge-status is-active" style="text-transform:capitalize;">${(po.status_label || po.status).replace('_', ' ')}</span>
                                    </td>
                                    <td style="text-align:center;">
                                        <button type="button" class="btn-po-review" onclick="viewPreorderDetail(${po.id_preorder}, '${car.id}')" title="Tinjau Permintaan Pre-Order">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                            <span>Tinjau</span>
                                        </button>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `}
        </div>

        <div id="preorderReviewView" style="display:none;">
            <div style="margin-bottom:14px;">
                <button type="button" class="btn-preorder-back" onclick="switchPreorderView('list')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    <span>&larr; Kembali ke Daftar Pre-Order</span>
                </button>
            </div>
            <div id="preorderReviewContent"></div>
        </div>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
        ${!isUserOwner ? `
            <button type="button" class="btn-modal-primary" onclick="openVehicleActionModal('${car.id}', 'add_unit')" style="background:#059669; border-color:#059669;">
                + Tambah Unit Fisik untuk Model Ini
            </button>
        ` : ''}
    `;
}

function switchPreorderView(view) {
    const listView = document.getElementById('preorderListView');
    const reviewView = document.getElementById('preorderReviewView');
    if (!listView || !reviewView) return;

    if (view === 'review') {
        listView.style.display = 'none';
        reviewView.style.display = 'block';
    } else {
        reviewView.style.display = 'none';
        listView.style.display = 'block';
    }
}

async function viewPreorderDetail(preorderId, carId) {
    switchPreorderView('review');
    const content = document.getElementById('preorderReviewContent');
    if (!content) return;

    content.innerHTML = `
        <div style="padding:40px 20px; text-align:center; color:#64748b;">
            <div style="display:inline-block; width:28px; height:28px; border:3px solid #e2e8f0; border-top-color:#dc2626; border-radius:50%; animation:modalSpin 0.8s linear infinite;"></div>
            <p style="margin:12px 0 0; font-size:13px; font-weight:600; color:#334155;">Memuat rincian permintaan pre-order #${preorderId}...</p>
        </div>
    `;

    const car = getCarPayload(carId) || {};
    const localPo = (car.preorders || []).find(p => p.id_preorder == preorderId) || {};

    try {
        const response = await fetch('/admin/pre-order-mobil/' + preorderId + '/detail', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (response.ok) {
            const data = await response.json();
            renderPreorderDetailContent(data, car, preorderId, carId);
            return;
        }
    } catch (e) {
        console.warn('Could not fetch server detail, using local preorder payload', e);
    }

    // Fallback
    renderPreorderDetailContent({
        preorder: localPo,
        customer: {
            name: localPo.customer_name,
            phone: localPo.customer_phone,
            email: localPo.customer_email,
            code: localPo.customer_code
        }
    }, car, preorderId, carId);
}

function renderPreorderDetailContent(data, car, preorderId, carId) {
    const content = document.getElementById('preorderReviewContent');
    if (!content) return;

    const po = data.preorder || data || {};
    const cust = data.customer || po.customer || {};
    const stockOrderable = Number(car.stock_orderable || 0);
    const code = data.code || po.kode_preorder || ('PRE-' + String(preorderId).padStart(5, '0'));
    const createdAt = data.created_at || po.created_at || '—';
    const variantName = data.variant || po.varian || po.variant || 'Standar';
    const yearVal = data.year || po.tahun || po.year || car.tahun || '—';
    const colorVal = data.color || po.warna || po.color || 'Standar';
    const statusVal = data.status || po.status || 'Aktif';
    const statusLabel = (po.status_label || statusVal).replace('_', ' ');

    content.innerHTML = `
        <div class="preorder-review-container">
            {{-- Review Header Info --}}
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;">
                <div>
                    <span style="font-size:11px; color:#64748b; font-weight:700; text-transform:uppercase;">KODE PRE-ORDER</span>
                    <div style="font-size:16px; font-weight:800; color:#0f172a;">${code}</div>
                </div>
                <div style="text-align:right;">
                    <span style="font-size:11px; color:#64748b;">Tanggal Pengajuan:</span>
                    <div style="font-weight:700; color:#334155; font-size:13px;">${createdAt}</div>
                </div>
            </div>

            {{-- 2 Cards: Profil Member & Spesifikasi Permintaan --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:12px; margin-bottom:14px;">
                <div class="preorder-card-box">
                    <h5 style="margin:0 0 10px; font-size:13px; font-weight:800; color:#0f172a; text-transform:uppercase;">Profil Member Pemesan</h5>
                    <dl class="modal-spec-dl" style="margin:0;">
                        <div class="modal-spec-row"><dt>Nama Member</dt><dd><strong>${cust.name || po.customer_name || 'Member YZA'}</strong></dd></div>
                        <div class="modal-spec-row"><dt>Kode Member</dt><dd>${cust.code || po.customer_code || '—'}</dd></div>
                        <div class="modal-spec-row"><dt>No. Telepon / WA</dt><dd>${cust.phone || po.customer_phone || '—'}</dd></div>
                        <div class="modal-spec-row"><dt>Email</dt><dd>${cust.email || po.customer_email || '—'}</dd></div>
                        <div class="modal-spec-row"><dt>Status Keanggotaan</dt><dd><span class="badge-status is-available">Member Aktif</span></dd></div>
                    </dl>
                </div>

                <div class="preorder-card-box">
                    <h5 style="margin:0 0 10px; font-size:13px; font-weight:800; color:#0f172a; text-transform:uppercase;">Spesifikasi Kendaraan Diminta</h5>
                    <dl class="modal-spec-dl" style="margin:0;">
                        <div class="modal-spec-row"><dt>Model Kendaraan</dt><dd><strong>${car.merk || ''} ${car.model || ''}</strong></dd></div>
                        <div class="modal-spec-row"><dt>Varian / Tipe</dt><dd>${variantName}</dd></div>
                        <div class="modal-spec-row"><dt>Tahun Pembuatan</dt><dd>${yearVal}</dd></div>
                        <div class="modal-spec-row"><dt>Preferensi Warna</dt><dd><strong style="color:#0f172a;">${colorVal}</strong></dd></div>
                        <div class="modal-spec-row"><dt>Status Pre-Order</dt><dd><span class="badge-status is-active" style="text-transform:capitalize;">${statusLabel}</span></dd></div>
                    </dl>
                </div>
            </div>

            {{-- Evaluasi Stok Fisik Dealer --}}
            <div class="preorder-stock-status-box ${stockOrderable > 0 ? 'is-ready' : 'is-empty'}" style="margin-bottom:14px; padding:16px; border-radius:10px; border:1px solid ${stockOrderable > 0 ? '#a7f3d0' : '#fde68a'}; background:${stockOrderable > 0 ? '#ecfdf5' : '#fffbeb'};">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${stockOrderable > 0 ? '#10b981' : '#f59e0b'};"></span>
                            <strong style="font-size:14px; color:${stockOrderable > 0 ? '#065f46' : '#92400e'};">
                                ${stockOrderable > 0 ? `Unit Fisik Tersedia (${stockOrderable} Unit Siap Jual di Showroom)` : 'Unit Fisik Belum Tersedia di Inventori Operasional'}
                            </strong>
                        </div>
                        <p style="margin:0; font-size:12px; color:${stockOrderable > 0 ? '#047857' : '#b45309'}; line-height:1.5;">
                            ${stockOrderable > 0
                                ? 'Stok fisik kendaraan telah tersedia di dealer. Anda dapat melanjutkan proses ini untuk menerbitkan SPK atau mengabari member bahwa unit telah siap.'
                                : 'Saat ini belum ada unit fisik yang siap jual untuk memenuhi pesanan pre-order ini. Daftarkan unit baru ke inventori untuk segera memproses antrean member ini.'}
                        </p>
                    </div>

                    <div style="display:flex; gap:8px; align-items:center;">
                        ${!isUserOwner ? (stockOrderable > 0
                            ? `<button type="button" class="btn-action-inline is-success" onclick="closeVehicleModal(); showNotificationToast('Unit siap dikaitkan. Silakan buat SPK melalui Pesanan Mobil.', 'success');">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Lanjutkan ke SPK</span>
                               </button>`
                            : `<button type="button" class="btn-action-inline is-primary" onclick="openVehicleActionModal('${carId}', 'add_unit')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>+ Tambah Unit Fisik</span>
                               </button>`
                        ) : ''}
                    </div>
                </div>
            </div>

            {{-- Catatan / Log Tambahan --}}
            ${po.catatan ? `
                <div style="padding:12px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; color:#475569;">
                    <strong>Catatan Khusus dari Member:</strong>
                    <p style="margin:4px 0 0; font-style:italic;">"${po.catatan}"</p>
                </div>
            ` : ''}
        </div>
    `;
}

// -------------------------------------------------------------
// 5. MODAL RIWAYAT PERGERAKAN STOK (PHASE 7)
// -------------------------------------------------------------
function renderStockHistoryModal(car, dom) {
    dom.title.textContent = 'Riwayat Pergerakan Stok';
    dom.dialog.classList.add('is-medium');

    const histories = car.histories || [];

    if (histories.length === 0) {
        dom.body.innerHTML = `
            <div style="padding:40px 20px; text-align:center; color:#64748b;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom:10px;"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                <h4 style="margin:0 0 6px; font-weight:700; color:#0f172a;">Belum Ada Riwayat Pergerakan Stok</h4>
                <p style="margin:0; font-size:12px;">Unit kendaraan ini belum memiliki catatan aktivitas inventori tambahan selain inisialisasi awal.</p>
            </div>
        `;
    } else {
        dom.body.innerHTML = `
            <div style="overflow-x:auto;">
                <table class="modal-history-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aktivitas</th>
                            <th style="text-align:center;">Perubahan</th>
                            <th>Stok Sebelum &rarr; Sesudah</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${histories.map(h => `
                            <tr>
                                <td style="font-size:11px; color:#64748b; white-space:nowrap;">${h.tanggal}</td>
                                <td><strong style="color:#0f172a;">${h.jenis}</strong></td>
                                <td style="text-align:center;"><span style="font-weight:700; color:${h.jumlah.toString().startsWith('-') ? '#dc2626' : '#16a34a'};">${h.jumlah} unit</span></td>
                                <td style="font-family:monospace; font-size:11px;">${h.before_after}</td>
                                <td style="color:#64748b;">${colorStockEscape(h.catatan)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
        <button type="button" class="btn-modal-secondary" onclick="openVehicleActionModal(${car.id}, 'stock')">Kelola Stok</button>
    `;
}

// -------------------------------------------------------------
// 6. MODAL GALERI FOTO & WARNA KENDARAAN (PHASE 8)
// -------------------------------------------------------------
function renderGalleryAndColorsModal(car, dom) {
    dom.title.textContent = 'Galeri Foto & Master Warna';
    dom.navTabs.removeAttribute('hidden');
    dom.navTabs.innerHTML = `
        <button type="button" class="modal-tab-btn is-active" data-target="pane-gallery-photos" aria-selected="true">Foto Kendaraan</button>
        <button type="button" class="modal-tab-btn" data-target="pane-gallery-catalog-colors" aria-selected="false">Warna Katalog</button>
        <button type="button" class="modal-tab-btn" data-target="pane-gallery-inventory-color" aria-selected="false">Warna Unit Inventori</button>
    `;

    const angles = car.angles || {};
    const photos = car.photos || [];
    const colors = car.colors || [];
    const uploadedAnglesCount = Object.values(angles).filter(a => a.path).length;

    dom.body.innerHTML = `
        {{-- Pane A: Foto Kendaraan & Multi-Sudut Visual --}}
        <div id="pane-gallery-photos" class="modal-pane is-active" style="display:block;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; padding:10px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; font-size:12px;">
                <span>Kelengkapan Foto Visual 4-Sudut: <strong>${uploadedAnglesCount} dari 4 sudut terunggah</strong></span>
                <span class="badge-status ${uploadedAnglesCount === 4 ? 'is-available' : 'is-warning'}">
                    ${uploadedAnglesCount === 4 ? 'Visual Lengkap' : 'Perlu Dilengkapi'}
                </span>
            </div>

            {{-- Active Image Preview Container --}}
            <div class="gallery-active-preview" id="galleryActivePreview">
                <img src="${car.thumbnail}" alt="Pratinjau Foto Utama ${car.merk} ${car.model}">
            </div>

            <h4 class="section-micro-title">Pratinjau Multi-Sudut Visual (Klik untuk Memperbesar)</h4>
            <div class="visual-angles-grid" style="margin-bottom:20px;">
                ${Object.entries(angles).map(([key, item]) => `
                    <div class="angle-card" style="cursor:pointer;" onclick="setGalleryActivePreview('${item.path || car.thumbnail}')" title="Klik untuk menampilkan pratinjau ${item.label}">
                        <div class="angle-view-box">
                            ${item.path
                                ? `<img src="${item.path}" alt="${item.label}">`
                                : `<div class="angle-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.6"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg><span>${item.label} belum diunggah</span></div>`}
                        </div>
                        <div class="angle-caption">
                            <span>${item.label}</span>
                            <span class="badge-dot ${item.path ? 'is-active' : 'is-inactive'}"></span>
                        </div>
                    </div>
                `).join('')}
            </div>

            <div class="uploaded-photos-header">
                <h4 class="section-micro-title" style="margin:0;">Galeri Foto Tambahan (${photos.length})</h4>
                ${!isUserOwner ? `
                    <form action="${car.urls.photo_store}" method="post" enctype="multipart/form-data" class="quick-upload-form">
                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                        <label class="btn-upload-file">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            <span>Unggah Foto Baru</span>
                            <input type="file" name="foto" accept="image/*" required onchange="this.form.submit()" hidden>
                        </label>
                    </form>
                ` : ''}
            </div>

            <div class="uploaded-thumbnails-grid">
                ${photos.length > 0
                    ? photos.map(f => `
                        <div class="uploaded-photo-card ${f.is_primary ? 'is-primary' : ''}">
                            <img src="${f.url}" alt="Foto Tambahan" style="cursor:pointer;" onclick="setGalleryActivePreview('${f.url}')" title="Klik untuk pratinjau">
                            <div class="uploaded-photo-overlay">
                                ${f.is_primary
                                    ? '<span class="badge-primary-photo">Utama</span>'
                                    : (!isUserOwner ? `
                                        <form action="${f.primary_url}" method="post">
                                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                                            <input type="hidden" name="_method" value="PATCH">
                                            <button type="submit" class="btn-action-photo set-primary" title="Jadikan Foto Utama">Utama</button>
                                        </form>
                                    ` : '')}
                                ${!isUserOwner ? `
                                    <form action="${f.destroy_url}" method="post" onsubmit="return confirm('Hapus foto ini?')">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn-action-photo delete" title="Hapus Foto">Hapus</button>
                                    </form>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')
                    : '<div class="no-uploaded-photos"><span>Belum ada foto tambahan yang diunggah untuk unit ini. Gunakan tombol Unggah Foto Baru di atas.</span></div>'}
            </div>
        </div>

        {{-- Pane B: Warna Katalog Showroom --}}
        <div id="pane-gallery-catalog-colors" class="modal-pane hidden" style="display:none;">
            <div class="modal-info-callout" style="margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Warna katalog merupakan pilihan varian tampilan showroom yang dapat dikonfigurasi member, BUKAN bukti ketersediaan stok fisik di dealer.</span>
            </div>

            <h4 class="section-micro-title" style="margin-top:0;">Daftar Pilihan Warna Showroom (Klik untuk Rincian)</h4>
            <div class="color-swatches-list" style="margin-bottom:16px;">
                ${colors.length > 0
                    ? colors.map((w, idx) => `
                        <div class="color-chip is-interactive ${w.is_default ? 'is-default' : ''}" data-color-idx="${idx}">
                            <span class="color-circle" style="background-color: ${w.hex || '#1e293b'};"></span>
                            <div class="color-chip-info">
                                <strong>${w.nama}</strong>
                                <small>${w.hex || '—'}</small>
                            </div>
                            ${w.is_default ? '<span class="chip-tag-default">Default</span>' : ''}
                        </div>
                    `).join('')
                    : '<div style="padding:16px; background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; color:#64748b; font-size:12px;">Tidak ada master warna katalog showroom terpisah. Model menggunakan warna dasar bawaan.</div>'}
            </div>

            {{-- Detail Panel for Selected Catalog Color --}}
            <div id="catalogColorDetailPanel" style="margin-top:12px;"></div>
        </div>

        {{-- Pane C: Warna Unit Inventori Aktual --}}
        <div id="pane-gallery-inventory-color" class="modal-pane hidden" style="display:none;">
            <div class="modal-info-callout" style="background:#ecfdf5; border-color:#a7f3d0; color:#065f46; margin-bottom:16px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Warna Fisik Unit yang tercatat pada inventori operasional saat ini adalah: <strong style="font-size:14px; text-transform:uppercase;">${car.warna || 'Tidak Ditentukan'}</strong></span>
            </div>

            <div class="modal-section-box">
                <h4 style="margin:0 0 10px; font-size:14px; font-weight:800; color:#0f172a;">Data Alokasi Stok Fisik Berdasarkan Warna</h4>
                <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px;">
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#64748b;">TOTAL FISIK</div>
                        <div style="font-size:18px; font-weight:800; color:#0f172a;">${car.stock_total}</div>
                    </div>
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #a7f3d0; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#047857;">SIAP JUAL</div>
                        <div style="font-size:18px; font-weight:800; color:#047857;">${car.stock_orderable}</div>
                    </div>
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #fde68a; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#b45309;">RESERVASI</div>
                        <div style="font-size:18px; font-weight:800; color:#b45309;">${car.reserved_units}</div>
                    </div>
                    <div style="padding:10px 12px; background:#ffffff; border:1px solid #cbd5e1; border-radius:8px; text-align:center;">
                        <div style="font-size:11px; color:#475569;">TERJUAL</div>
                        <div style="font-size:18px; font-weight:800; color:#0f172a;">${car.sold_units}</div>
                    </div>
                </div>

                <div style="display:flex; flex-direction:column; gap:6px; font-size:12px; color:#475569;">
                    <div>Kode Mobil: <strong>${car.kode}</strong></div>
                    <div>Nomor Rangka (VIN): <strong style="font-family:monospace;">${car.nomor_rangka || 'Tercatat dalam batch inventori dealer'}</strong></div>
                    <div>Tipe Inventori: <strong>${car.reserved_units > 0 ? 'Batch Fisik dengan Reservasi Mengikat' : 'Stok Fisik Tersedia'}</strong></div>
                </div>
            </div>

            <div style="padding:12px 16px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; font-size:12px; color:#64748b; line-height:1.6;">
                <strong>Kebijakan Alokasi Dealer:</strong> Seluruh transaksi pesanan SPK dan reservasi unit secara otomatis mengikat unit fisik dengan warna <strong>${car.warna}</strong>. Permintaan warna showroom lain yang belum memiliki stok fisik dialokasikan melalui proses pemesanan ulang (pre-order).
            </div>
        </div>
    `;

    // Interactive helper: Set gallery preview image
    window.setGalleryActivePreview = function(url) {
        const preview = dom.body.querySelector('#galleryActivePreview img');
        if (preview && url) {
            preview.src = url;
        }
    };

    // Interactive helper: Swatch selection in Tab B
    const swatchChips = dom.body.querySelectorAll('.color-chip.is-interactive');
    const detailPanel = dom.body.querySelector('#catalogColorDetailPanel');
    if (swatchChips.length > 0 && detailPanel) {
        const selectColor = (idx) => {
            swatchChips.forEach((c, i) => c.classList.toggle('is-selected', i === idx));
            const w = colors[idx];
            if (!w) return;
            detailPanel.innerHTML = `
                <div class="modal-section-box" style="margin-top:10px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <div>
                            <strong style="font-size:14px; color:#0f172a;">${w.nama}</strong>
                            <div style="font-family:monospace; font-size:11px; color:#64748b;">HEX: ${w.hex || '—'} &bull; Slug: ${w.slug || '—'}</div>
                        </div>
                        <div>
                            ${w.is_default ? '<span class="badge-primary-photo">Warna Utama Katalog</span>' : '<span style="font-size:11px; color:#64748b; background:#f1f5f9; padding:4px 8px; border-radius:6px;">Pilihan Tambahan</span>'}
                        </div>
                    </div>
                    <div style="font-size:12px; color:#475569; margin-bottom:10px;">
                        Status Showroom: <strong style="color:#16a34a;">Aktif pada Konfigurator Member</strong>
                    </div>
                    <div style="padding:10px 14px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; font-size:11px; color:#64748b;">
                        <em>Informasi: Pilihan warna katalog memungkinkan member memilih tampilan showroom yang diinginkan. Ketersediaan stok fisik sebenarnya ditampilkan pada tab <strong>Warna Unit Inventori</strong>.</em>
                    </div>
                </div>
            `;
        };

        swatchChips.forEach((chip, i) => {
            chip.addEventListener('click', () => selectColor(i));
        });
        selectColor(0);
    }

    dom.body.insertAdjacentHTML('afterbegin', renderColorStockCards(car));
    function updateGalleryFooter(activePaneId) {
        if (activePaneId === 'pane-gallery-photos' || activePaneId === 'pane-gallery-catalog-colors') {
            dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        } else if (activePaneId === 'pane-gallery-inventory-color') {
            dom.footer.innerHTML = `
                <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>
                ${!isUserOwner ? `<button type="button" class="btn-modal-primary" onclick="openVehicleActionModal(${car.id}, 'edit')">Edit Data Kendaraan</button>` : ''}
            `;
        }
    }

    setupModalPaneTabs(dom.navTabs, dom.body, updateGalleryFooter);
    updateGalleryFooter('pane-gallery-photos');
}

// -------------------------------------------------------------
// 7. MODAL KONFIRMASI NONAKTIFKAN KENDARAAN (PHASE 9)
// -------------------------------------------------------------
function renderDeactivateConfirmationModal(car, dom) {
    dom.title.textContent = 'Konfirmasi Nonaktifkan Kendaraan';
    dom.dialog.classList.add('is-compact');

    if (isUserOwner) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Role Owner hanya memiliki hak akses baca (read-only). Nonaktifkan kendaraan dinonaktifkan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    if (car.is_sold) {
        dom.body.innerHTML = `
            <div class="modal-warning-callout">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>Kendaraan dengan status <strong>Terjual</strong> tidak dapat dinonaktifkan karena telah selesai diserahterimakan ke pelanggan.</span>
            </div>
        `;
        dom.footer.innerHTML = `<button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Tutup</button>`;
        return;
    }

    dom.body.innerHTML = `
        <div class="modal-warning-callout">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <div>
                <strong>Perhatian: Tindakan Penonaktifan</strong>
                <p style="margin:4px 0 0; font-size:11px;">Kendaraan <strong>${car.merk} ${car.model} (${car.kode})</strong> akan disembunyikan dari katalog showroom publik.</p>
            </div>
        </div>

        ${car.reserved_units > 0 ? `
            <div style="padding:10px 14px; background:#fffbeb; border:1px solid #fde68a; border-radius:8px; color:#92400e; font-size:12px; margin-bottom:14px;">
                <strong>Peringatan Reservasi:</strong> Terdapat ${car.reserved_units} reservasi/pesanan aktif pada kendaraan ini. Data pesanan member tetap aman dan tidak akan terhapus.
            </div>
        ` : ''}

        <p style="font-size:12px; color:#475569; line-height:1.5;">
            Menonaktifkan kendaraan tidak akan menghapus data historis, riwayat stok, atau transaksi sebelumnya. Status dapat diaktifkan kembali sewaktu-waktu melalui menu Edit.
        </p>

        <form id="deactivateVehicleForm" onsubmit="submitDeactivateVehicle(event, ${car.id})">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content}">
            <input type="hidden" name="_method" value="PATCH">
            <div id="deactivateVehicleError" class="modal-field-error" style="display:none; margin-top:8px;"></div>
        <div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Alasan perubahan stok *</label><input name="alasan_stok" class="modal-form-input" minlength="5" maxlength="160" required></div><div class="modal-form-group" style="grid-column:1/-1"><label class="modal-form-label">Bukti / nomor dokumen verifikasi *</label><input name="bukti_stok" class="modal-form-input" minlength="5" maxlength="500" placeholder="Nomor surat jalan / berita acara pemeriksaan" required></div></form>
    `;

    dom.footer.innerHTML = `
        <button type="button" class="btn-modal-secondary" onclick="closeVehicleModal()">Batal</button>
        <button type="submit" form="deactivateVehicleForm" class="btn-modal-danger" id="btnSubmitDeactivate">Konfirmasi Nonaktifkan</button>
    `;
}

// Handler Submit Nonaktifkan Kendaraan
async function submitDeactivateVehicle(e, carId) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitDeactivate');
    const errBox = document.getElementById('deactivateVehicleError');
    errBox.style.display = 'none';

    const car = getCarPayload(carId);
    if (!car) return;

    btn.disabled = true;
    btn.textContent = 'Memproses...';

    const formData = new FormData(e.target);

    try {
        const response = await fetch(car.urls.deactivate, {
            method: 'POST', // with _method=PATCH
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();
        if (response.ok && data.success) {
            updateLocalCarPayload(carId, { status: 'nonaktif' });

            // Update row in table
            const row = document.getElementById('row-car-' + carId);
            if (row) {
                const statusBadge = row.querySelector('.td-status .badge-status');
                if (statusBadge) {
                    statusBadge.className = 'badge-status is-inactive';
                    statusBadge.textContent = 'Nonaktif';
                }
            }

            closeVehicleModal();
            showNotificationToast('Kendaraan berhasil dinonaktifkan.', 'success');
        } else {
            errBox.textContent = data.message || 'Gagal menonaktifkan kendaraan.';
            errBox.style.display = 'block';
        }
    } catch (err) {
        errBox.textContent = 'Gagal menghubungi server: ' + err.message;
        errBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Konfirmasi Nonaktifkan';
    }
}

// Helper: Setup Tab Switching inside Modal
function setupModalPaneTabs(navTabs, body, onTabChange = null) {
    const tabBtns = navTabs.querySelectorAll('.modal-tab-btn');
    const panes = body.querySelectorAll('.modal-pane');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            tabBtns.forEach(b => {
                b.classList.remove('is-active');
                b.setAttribute('aria-selected', 'false');
            });
            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');

            panes.forEach(p => {
                if (p.id === targetId) {
                    p.classList.remove('hidden');
                    p.classList.add('is-active');
                    p.style.display = 'block';
                } else {
                    p.classList.add('hidden');
                    p.classList.remove('is-active');
                    p.style.display = 'none';
                }
            });

            // Reset scroll position to top on tab switch
            body.scrollTop = 0;

            if (typeof onTabChange === 'function') {
                onTabChange(targetId);
            }
        });
    });
}

// Helper: Simple Notification Toast
function showNotificationToast(msg, type = 'success') {
    let container = document.getElementById('vehicleActionToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'vehicleActionToastContainer';
        container.style.cssText = 'position:fixed; bottom:24px; right:24px; z-index:100000; display:flex; flex-direction:column; gap:8px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.style.cssText = 'padding:12px 18px; border-radius:10px; background:#0f172a; color:#ffffff; font-size:13px; font-weight:600; box-shadow:0 8px 24px rgba(0,0,0,0.25); display:flex; align-items:center; gap:8px; animation:modalSlideUp 180ms ease-out;';
    toast.innerHTML = `<span style="color:#10b981; font-size:16px;">✓</span> <span>${msg}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 200ms ease';
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}
</script>
@endpush
@endsection
