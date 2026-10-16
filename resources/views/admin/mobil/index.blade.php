@extends('layouts.app')

@section('title', 'Inventori & Stok Mobil - Admin YZA Automotive')
@section('admin-page-title', 'Inventori & Stok Mobil')

@section('content')
@php
    function resolveCarThumbnail($car) {
        if ($car->foto && $car->foto->isNotEmpty()) {
            $first = $car->foto->first();
            if ($first->existsOnDisk()) {
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
    }
@endphp

<div class="admin-inventory-page">
    {{-- Header Inventori & Stok Mobil --}}
    <header class="inventory-header">
        <div class="inventory-header-left">
            <nav class="inventory-breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-back-arrow" aria-hidden="true">&lsaquo;</span>
                <span class="breadcrumb-item">Penjualan Mobil</span>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current">Inventori & Stok Mobil</span>
            </nav>

            <div class="inventory-title-group">
                <div class="inventory-title-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="inventory-heading">Inventori & Stok Mobil</h1>
                    <p class="inventory-subheading">Kelola data inventori kendaraan, pantau stok, dan atur ketersediaan unit mobil di showroom.</p>
                </div>
            </div>
        </div>

        <div class="inventory-header-right">
            <div class="inventory-banner">
                <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="YZA Automotive Premium Sports Car" class="inventory-banner-img" loading="eager">
                <div class="inventory-banner-overlay"></div>
                <div class="inventory-banner-content">
                    <span class="banner-tagline-top">INVENTORI TERKELOLA</span>
                    <span class="banner-tagline-bottom">PENJUALAN LEBIH MAKSIMAL</span>
                    <div class="banner-accent-line"></div>
                </div>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="admin-alert is-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Enam Kartu Statistik Inventori Premium --}}
    <section class="inventory-kpi-grid" aria-label="Statistik Inventori Mobil">
        {{-- Card 1: Total Unit (Merah Pastel) --}}
        <a class="inventory-kpi-card is-red" href="{{ route('admin.mobil.index') }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"></path>
                    <circle cx="7" cy="17" r="2"></circle>
                    <path d="M9 17h6"></path>
                    <circle cx="17" cy="17" r="2"></circle>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Unit</span>
                <strong class="kpi-value">{{ number_format($stats['total_unit']) }}</strong>
                <div class="kpi-subtext">
                    <span>Seluruh unit kendaraan</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Mobil Tersedia (Hijau Pastel) --}}
        <a class="inventory-kpi-card is-green" href="{{ route('admin.mobil.index', ['status' => 'tersedia']) }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Mobil Tersedia</span>
                <strong class="kpi-value">{{ number_format($stats['tersedia']) }}</strong>
                <div class="kpi-subtext is-up">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Siap dijual di showroom</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Mobil Dipesan (Kuning Pastel) --}}
        <a class="inventory-kpi-card is-amber" href="{{ route('admin.pesanan-mobil.index') }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Mobil Dipesan</span>
                <strong class="kpi-value">{{ number_format($stats['dipesan']) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit dalam pesanan</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Mobil Terjual (Biru Pastel) --}}
        <a class="inventory-kpi-card is-blue" href="{{ route('admin.mobil.index', ['status' => 'terjual']) }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Mobil Terjual</span>
                <strong class="kpi-value">{{ number_format($stats['terjual']) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit sudah terjual</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Stok Menipis (Oranye Pastel) --}}
        <div class="inventory-kpi-card is-amber">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Stok Menipis</span>
                <strong class="kpi-value">{{ number_format($stats['stok_menipis']) }}</strong>
                <div class="kpi-subtext" style="color: #D97706;">
                    <span>Unit perlu restock</span>
                </div>
            </div>
        </div>

        {{-- Card 6: Unit Perlu Perhatian (Ungu Pastel) --}}
        <a class="inventory-kpi-card is-purple" href="{{ route('admin.mobil.index', ['status' => 'nonaktif']) }}">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Unit Perlu Perhatian</span>
                <strong class="kpi-value">{{ number_format($stats['perlu_perhatian']) }}</strong>
                <div class="kpi-subtext">
                    <span>Perlu pengecekan kondisi</span>
                </div>
            </div>
        </a>
    </section>

    {{-- Container Utama Daftar Inventori Mobil --}}
    <main class="inventory-main-card">
        {{-- Card Header --}}
        <div class="inventory-card-header">
            <div class="card-header-left">
                <div class="card-header-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="card-header-title">Daftar Inventori Mobil</h2>
                    <p class="card-header-subtitle">Semua data unit kendaraan yang tersedia di sistem.</p>
                </div>
            </div>

            <div class="card-header-right">
                <a class="btn-add-car" href="{{ route('admin.mobil.create') }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Tambah Mobil</span>
                </a>
            </div>
        </div>

        {{-- Toolbar Filter Sejajar Horizontal Satu Baris --}}
        <form class="inventory-filter-toolbar" method="get" action="{{ route('admin.mobil.index') }}">
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            {{-- 1. Input Pencarian --}}
            <div class="filter-wrap search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ $q }}" class="filter-input" placeholder="Cari kode, merek, model, atau tahun...">
            </div>

            {{-- 2. Dropdown Merek --}}
            <div class="filter-wrap select-box select-brand">
                <select name="merk" class="filter-select">
                    <option value="">Semua Merek</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}" @selected($merk === $brand)>{{ $brand }}</option>
                    @endforeach
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            {{-- 3. Dropdown Status --}}
            <div class="filter-wrap select-box select-status">
                <select name="status" class="filter-select">
                    <option value="">Semua Status</option>
                    <option value="tersedia" @selected($status === 'tersedia')>Tersedia</option>
                    <option value="dibooking" @selected($status === 'dibooking')>Dipesan</option>
                    <option value="terjual" @selected($status === 'terjual')>Terjual</option>
                    <option value="nonaktif" @selected($status === 'nonaktif')>Nonaktif</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            {{-- 4. Tahun Min --}}
            <div class="filter-wrap number-box">
                <input type="number" name="tahun_min" value="{{ $tahunMin }}" placeholder="Tahun Min" class="filter-input" min="1990" max="2035">
            </div>

            {{-- 5. Tahun Maks --}}
            <div class="filter-wrap number-box">
                <input type="number" name="tahun_maks" value="{{ $tahunMaks }}" placeholder="Tahun Maks" class="filter-input" min="1990" max="2035">
            </div>

            {{-- 6. Harga Min --}}
            <div class="filter-wrap number-box price-box">
                <input type="number" name="harga_min" value="{{ $hargaMin }}" placeholder="Harga Min" class="filter-input" step="10000000">
            </div>

            {{-- 7. Harga Maks --}}
            <div class="filter-wrap number-box price-box">
                <input type="number" name="harga_maks" value="{{ $hargaMaks }}" placeholder="Harga Maks" class="filter-input" step="10000000">
            </div>

            {{-- Tombol Aksi --}}
            <div class="filter-actions-group">
                <button type="submit" class="btn-filter-apply">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.mobil.index') }}" class="btn-filter-reset">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        {{-- Tabel Daftar Inventori Mobil --}}
        <div class="inventory-table-responsive">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th class="th-check">
                            <input type="checkbox" id="checkAllInventory" class="custom-checkbox" title="Pilih Semua">
                        </th>
                        <th class="th-code">
                            <span class="th-sortable">
                                <span>KODE</span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </th>
                        <th class="th-car">MOBIL</th>
                        <th class="th-showroom">SHOWROOM</th>
                        <th class="th-price">
                            <span class="th-sortable">
                                <span>HARGA JUAL</span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </th>
                        <th class="th-stock" style="text-align: center;">TOTAL STOK</th>
                        <th class="th-orderable" style="text-align: center;">
                            <span class="th-sortable" style="justify-content: center;">
                                <span>STOK DAPAT DIBELI</span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </th>
                        <th class="th-reserved" style="text-align: center;">RESERVASI AKTIF</th>
                        <th class="th-sold" style="text-align: center;">TERJUAL</th>
                        <th class="th-status" style="text-align: center;">
                            <span class="th-sortable" style="justify-content: center;">
                                <span>STATUS</span>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                            </span>
                        </th>
                        <th class="th-action" style="text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cars as $mobil)
                        @php
                            $availability = $mobil->getAttribute('availability') ?? [];
                            $stockTotal = $availability['stock_total'] ?? (($availability['stock'] ?? null) !== null ? $availability['stock'] : 0);
                            $stockOrderable = $availability['stock'] ?? 0;
                            $reservedUnits = $availability['reserved_units'] ?? ($availability['active_reservation_count'] ?? 0);
                            $soldUnits = $availability['sold_units'] ?? 0;
                            $fotoUrl = resolveCarThumbnail($mobil);
                            $cleanStatus = strtolower((string) $mobil->status);
                        @endphp
                        <tr>
                            {{-- Checkbox --}}
                            <td class="td-check">
                                <input type="checkbox" class="inventory-row-check custom-checkbox" value="{{ $mobil->id_mobil }}">
                            </td>

                            {{-- Kode Mobil --}}
                            <td class="td-code">
                                <a href="{{ route('admin.mobil.show', $mobil) }}" class="car-code-link">
                                    {{ $mobil->kode_mobil }}
                                </a>
                            </td>

                            {{-- Mobil (Thumb + Nama + Spek) --}}
                            <td class="td-car">
                                <div class="car-cell">
                                    <div class="car-thumb-box">
                                        <img src="{{ $fotoUrl }}" alt="{{ $mobil->merk }} {{ $mobil->model }}" class="car-thumb-img" loading="lazy">
                                    </div>
                                    <div class="car-meta">
                                        <a href="{{ route('admin.mobil.show', $mobil) }}" class="car-title-link">
                                            <strong class="car-title">{{ $mobil->merk }} {{ $mobil->model }}</strong>
                                        </a>
                                        <span class="car-subtext">{{ $mobil->tipe }} &bull; {{ $mobil->tahun }} &bull; {{ $mobil->warna }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Showroom --}}
                            <td class="td-showroom">
                                {{ $mobil->showcase_slug ?: '—' }}
                            </td>

                            {{-- Harga Jual --}}
                            <td class="td-price">
                                Rp {{ number_format((float) $mobil->harga_jual, 0, ',', '.') }}
                            </td>

                            {{-- Total Stok --}}
                            <td class="td-stock">
                                {{ $stockTotal }}
                            </td>

                            {{-- Stok Dapat Dibeli --}}
                            <td class="td-orderable">
                                <span class="orderable-unit-badge {{ $stockOrderable > 0 ? 'is-available' : 'is-empty' }}">
                                    {{ $stockOrderable }} unit
                                </span>
                            </td>

                            {{-- Reservasi Aktif --}}
                            <td class="td-reserved">
                                {{ $reservedUnits }}
                            </td>

                            {{-- Terjual --}}
                            <td class="td-sold">
                                {{ $soldUnits }}
                            </td>

                            {{-- Status Badge --}}
                            <td class="td-status">
                                @if($cleanStatus === 'tersedia')
                                    <span class="badge-status is-available">
                                        Tersedia
                                    </span>
                                @elseif($cleanStatus === 'dibooking')
                                    <span class="badge-status is-booked">
                                        Dipesan
                                    </span>
                                @elseif($cleanStatus === 'terjual')
                                    <span class="badge-status is-sold">
                                        Terjual
                                    </span>
                                @else
                                    <span class="badge-status is-inactive">
                                        {{ ucfirst($cleanStatus) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="td-action">
                                <div class="action-buttons-group">
                                    {{-- Tombol Detail --}}
                                    <a href="{{ route('admin.mobil.show', $mobil) }}" class="btn-row-action is-detail" title="Lihat detail mobil">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <span>Detail</span>
                                    </a>

                                    {{-- Tombol Stok --}}
                                    <a href="{{ route('admin.mobil.stock', $mobil) }}" class="btn-row-action is-stock" title="Kelola stok mobil">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                                        <span>Stok</span>
                                    </a>

                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('admin.mobil.edit', $mobil) }}" class="btn-row-action is-edit" title="Edit spesifikasi mobil">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                        <span>Edit</span>
                                    </a>

                                    {{-- Menu Tambahan (⋯) --}}
                                    <div class="action-dropdown-wrap" data-dropdown-wrap>
                                        <button type="button" class="btn-more-options" data-dropdown-toggle aria-label="Opsi lainnya" aria-expanded="false">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                        </button>
                                        <div class="action-dropdown-menu" data-dropdown-menu hidden>
                                            <a href="{{ route('admin.mobil.show', $mobil) }}" class="dropdown-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                <span>Lihat Spesifikasi</span>
                                            </a>
                                            <a href="{{ route('admin.mobil.stock', $mobil) }}" class="dropdown-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path></svg>
                                                <span>Riwayat Stok</span>
                                            </a>
                                            @if($mobil->status !== 'terjual')
                                                <form action="{{ route('admin.mobil.deactivate', $mobil) }}" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan unit mobil ini?')">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item is-reject">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                                        <span>Nonaktifkan Mobil</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="table-empty-cell">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                    </div>
                                    <h3>Tidak Ada Mobil Ditemukan</h3>
                                    <p>Belum ada data mobil yang sesuai dengan parameter filter atau pencarian Anda.</p>
                                    <a href="{{ route('admin.mobil.index') }}" class="btn-empty-reset">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination & Per-Page Controls --}}
        <div class="inventory-table-footer">
            <div class="footer-info">
                Menampilkan <strong>{{ $cars->firstItem() ?: 0 }}</strong> – <strong>{{ $cars->lastItem() ?: 0 }}</strong> dari <strong>{{ $cars->total() }}</strong> unit mobil
            </div>

            <div class="footer-controls">
                <div class="pagination-links">
                    {{ $cars->links('pagination::bootstrap-4') }}
                </div>

                {{-- Dropdown Per-Halaman --}}
                <form method="get" action="{{ route('admin.mobil.index') }}" class="per-page-form">
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
    </main>
</div>

@push('styles')
<style>
/* ========================================================
   YZA AUTOMOTIVE — PREMIUM ADMIN INVENTORY MANAGEMENT STYLES
   ======================================================== */
.admin-inventory-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
    width: 100%;
}

/* Header & Banner */
.inventory-header {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}
.inventory-header-left {
    flex: 1 1 380px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.inventory-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748B;
    margin-bottom: 12px;
}
.breadcrumb-back-arrow {
    font-size: 16px;
    font-weight: 700;
    color: #94A3B8;
}
.breadcrumb-item {
    color: #64748B;
    font-weight: 500;
}
.breadcrumb-separator {
    color: #CBD5E1;
    font-size: 11px;
}
.breadcrumb-current {
    color: #0F172A;
    font-weight: 700;
}
.inventory-title-group {
    display: flex;
    align-items: center;
    gap: 16px;
}
.inventory-title-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #ED172B;
    color: #FFFFFF;
    display: grid;
    place-items: center;
    box-shadow: 0 6px 16px rgba(237, 23, 43, 0.28);
    flex-shrink: 0;
}
.inventory-heading {
    margin: 0;
    font-size: 30px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.inventory-subheading {
    margin: 6px 0 0;
    font-size: 13.5px;
    color: #64748B;
    line-height: 1.5;
    max-width: 620px;
}

/* Header Banner */
.inventory-header-right {
    flex: 0 0 380px;
    min-width: 320px;
}
@media (max-width: 900px) {
    .inventory-header-right {
        flex: 1 1 100%;
    }
}
.inventory-banner {
    position: relative;
    height: 108px;
    border-radius: 16px;
    overflow: hidden;
    background: #0B1018;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
}
.inventory-banner-img {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 65%;
    object-fit: cover;
    object-position: right center;
    opacity: 0.88;
}
.inventory-banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #0B1018 35%, rgba(11, 16, 24, 0.7) 65%, transparent 100%);
}
.inventory-banner-content {
    position: relative;
    z-index: 2;
    padding-left: 22px;
    display: flex;
    flex-direction: column;
}
.banner-tagline-top {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.12em;
    color: #FFFFFF;
}
.banner-tagline-bottom {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #CBD5E1;
    margin-top: 2px;
}
.banner-accent-line {
    width: 36px;
    height: 3px;
    background: #ED172B;
    border-radius: 2px;
    margin-top: 6px;
}

/* Alert notifications */
.admin-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 600;
}
.admin-alert.is-success {
    background: #ECFDF5;
    border: 1px solid #A7F3D0;
    color: #065F46;
}

/* ========================================================
   ENAM STATISTIK INVENTORI (6 KPI CARDS)
   ======================================================== */
.inventory-kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
}
@media (max-width: 1400px) {
    .inventory-kpi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 768px) {
    .inventory-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 480px) {
    .inventory-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.inventory-kpi-card {
    border-radius: 16px;
    padding: 16px 16px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid transparent;
    text-decoration: none;
    min-height: 122px;
    box-sizing: border-box;
}
.inventory-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Themes */
.inventory-kpi-card.is-red {
    background: #FFF1F2;
    border-color: #FFE4E6;
}
.inventory-kpi-card.is-red .kpi-icon-box {
    background: #FEE2E2;
    color: #E11D48;
}
.inventory-kpi-card.is-green {
    background: #ECFDF5;
    border-color: #D1FAE5;
}
.inventory-kpi-card.is-green .kpi-icon-box {
    background: #D1FAE5;
    color: #059669;
}
.inventory-kpi-card.is-amber {
    background: #FFFBEB;
    border-color: #FEF3C7;
}
.inventory-kpi-card.is-amber .kpi-icon-box {
    background: #FEF3C7;
    color: #D97706;
}
.inventory-kpi-card.is-blue {
    background: #EFF6FF;
    border-color: #DBEAFE;
}
.inventory-kpi-card.is-blue .kpi-icon-box {
    background: #DBEAFE;
    color: #2563EB;
}
.inventory-kpi-card.is-purple {
    background: #FAF5FF;
    border-color: #F3E8FF;
}
.inventory-kpi-card.is-purple .kpi-icon-box {
    background: #F3E8FF;
    color: #9333EA;
}

.kpi-icon-box {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    margin-bottom: 12px;
}
.kpi-content {
    display: flex;
    flex-direction: column;
}
.kpi-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #64748B;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kpi-value {
    font-size: 24px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.kpi-subtext {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11.5px;
    font-weight: 600;
    color: #64748B;
    margin-top: 6px;
}
.kpi-subtext.is-up {
    color: #059669;
}

/* ========================================================
   CONTAINER UTAMA DAFTAR INVENTORI
   ======================================================== */
.inventory-main-card {
    background: #FFFFFF;
    border-radius: 20px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Card Header */
.inventory-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding-bottom: 4px;
}
.card-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.card-header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #ED172B;
    color: #FFFFFF;
    display: grid;
    place-items: center;
    box-shadow: 0 4px 12px rgba(237, 23, 43, 0.25);
    flex-shrink: 0;
}
.card-header-title {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.01em;
}
.card-header-subtitle {
    margin: 3px 0 0;
    font-size: 13.5px;
    color: #64748B;
}
.btn-add-car {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 40px;
    padding: 0 18px;
    border-radius: 10px;
    background: #ED172B;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(237, 23, 43, 0.25);
    transition: background 0.15s ease, transform 0.15s ease;
}
.btn-add-car:hover {
    background: #D91325;
    transform: translateY(-1px);
    color: #FFFFFF;
}

/* ========================================================
   TOOLBAR FILTER SEJAJAR HORIZONTAL (SESUAI GAMBAR 1)
   ======================================================== */
.inventory-filter-toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
    width: 100%;
}
@media (max-width: 1400px) {
    .inventory-filter-toolbar {
        flex-wrap: wrap;
    }
}
.filter-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.filter-wrap.search-box {
    flex: 2.2 1 200px;
    min-width: 180px;
}
.filter-wrap.search-box svg {
    position: absolute;
    left: 12px;
    color: #94A3B8;
    pointer-events: none;
}
.filter-wrap.search-box .filter-input {
    padding-left: 36px;
}
.filter-wrap.select-brand {
    flex: 1.1 1 120px;
    min-width: 110px;
}
.filter-wrap.select-status {
    flex: 1.1 1 120px;
    min-width: 110px;
}
.filter-wrap.number-box {
    flex: 0.8 1 85px;
    min-width: 80px;
}
.filter-wrap.price-box {
    flex: 0.9 1 95px;
    min-width: 90px;
}
.select-chevron {
    position: absolute;
    right: 10px;
    color: #94A3B8;
    pointer-events: none;
}
.filter-input, .filter-select {
    width: 100%;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #E2E8F0;
    border-radius: 9px;
    background: #FFFFFF;
    color: #0F172A;
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.filter-select {
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    padding-right: 26px;
}
.filter-input:focus, .filter-select:focus {
    outline: none;
    border-color: #ED172B;
    box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.1);
}
.filter-actions-group {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.btn-filter-apply {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 40px;
    padding: 0 16px;
    border-radius: 9px;
    background: #ED172B;
    color: #FFFFFF;
    border: 0;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(237, 23, 43, 0.25);
    transition: background 0.15s ease;
    white-space: nowrap;
}
.btn-filter-apply:hover {
    background: #D91325;
}
.btn-filter-reset {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 40px;
    padding: 0 14px;
    border-radius: 9px;
    background: #FFFFFF;
    color: #475569;
    border: 1px solid #E2E8F0;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.btn-filter-reset:hover {
    background: #F8FAFC;
    color: #0F172A;
    border-color: #CBD5E1;
}

/* ========================================================
   TABEL INVENTORI (PREMIUM WHITE EDITION)
   ======================================================== */
.inventory-table-responsive {
    overflow-x: auto;
    border-radius: 14px;
    border: 1px solid #F1F5F9;
}
.inventory-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: separate;
    border-spacing: 0;
}
.inventory-table thead tr th {
    background: #F8FAFC;
    color: #475569;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 14px 14px;
    border-bottom: 1px solid #E2E8F0;
    text-align: left;
    white-space: nowrap;
}
.th-check {
    width: 40px;
    text-align: center !important;
}
.custom-checkbox {
    width: 16px;
    height: 16px;
    accent-color: #ED172B;
    cursor: pointer;
    vertical-align: middle;
}
.th-sortable {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}
.inventory-table tbody tr {
    transition: background 0.15s ease;
}
.inventory-table tbody tr:hover {
    background: #F8FAFC;
}
.inventory-table tbody tr td {
    padding: 12px 14px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
    font-size: 13px;
    color: #334155;
    background: #FFFFFF;
}
.inventory-table tbody tr:hover td {
    background: #F8FAFC;
}

/* Cells */
.td-check {
    text-align: center;
}
.car-code-link {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
    text-decoration: none;
    transition: color 0.15s ease;
}
.car-code-link:hover {
    color: #ED172B;
    text-decoration: underline;
}
.car-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.car-thumb-box {
    width: 70px;
    height: 46px;
    border-radius: 8px;
    overflow: hidden;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.car-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.car-meta {
    display: flex;
    flex-direction: column;
}
.car-title-link {
    text-decoration: none;
    color: inherit;
}
.car-title {
    color: #0F172A;
    font-size: 13px;
    font-weight: 700;
    transition: color 0.15s ease;
}
.car-title:hover {
    color: #ED172B;
}
.car-subtext {
    font-size: 11px;
    color: #64748B;
    margin-top: 2px;
}
.td-showroom {
    color: #64748B;
    font-size: 13px;
}
.td-price {
    font-size: 13.5px;
    font-weight: 700;
    color: #0F172A;
    white-space: nowrap;
}
.td-stock {
    text-align: center;
    font-weight: 600;
    color: #1E293B;
}
.td-orderable {
    text-align: center;
}
.orderable-unit-badge {
    font-weight: 700;
    font-size: 13px;
}
.orderable-unit-badge.is-available {
    color: #16A34A;
}
.orderable-unit-badge.is-empty {
    color: #DC2626;
}
.td-reserved, .td-sold {
    text-align: center;
    font-weight: 600;
    color: #475569;
}

/* Badges Status */
.td-status {
    text-align: center;
}
.badge-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
    white-space: nowrap;
}
.badge-status.is-available {
    background: #DCFCE7;
    color: #15803D;
    border: 1px solid #BBF7D0;
}
.badge-status.is-booked {
    background: #FEF3C7;
    color: #D97706;
    border: 1px solid #FDE68A;
}
.badge-status.is-sold {
    background: #EFF6FF;
    color: #2563EB;
    border: 1px solid #DBEAFE;
}
.badge-status.is-inactive {
    background: #F1F5F9;
    color: #64748B;
    border: 1px solid #E2E8F0;
}

/* Action Buttons */
.action-buttons-group {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-row-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 32px;
    padding: 0 10px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    white-space: nowrap;
    box-sizing: border-box;
}
.btn-row-action.is-detail,
.btn-row-action.is-stock {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: #334155;
}
.btn-row-action.is-detail:hover,
.btn-row-action.is-stock:hover {
    background: #F8FAFC;
    border-color: #CBD5E1;
    color: #0F172A;
}
.btn-row-action.is-edit {
    background: #ED172B;
    border: 1px solid #ED172B;
    color: #FFFFFF;
    box-shadow: 0 2px 6px rgba(237, 23, 43, 0.25);
}
.btn-row-action.is-edit:hover {
    background: #D91325;
    border-color: #D91325;
    color: #FFFFFF;
}

.action-dropdown-wrap {
    position: relative;
}
.btn-more-options {
    width: 32px;
    height: 32px;
    border-radius: 7px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-more-options:hover {
    background: #F1F5F9;
    color: #0F172A;
}
.action-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 4px);
    width: 170px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    padding: 6px;
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.1);
    z-index: 50;
    text-align: left;
}
.action-dropdown-menu[hidden] {
    display: none;
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    font-size: 12px;
    color: #334155;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.15s ease;
}
.dropdown-item:hover {
    background: #F1F5F9;
    color: #0F172A;
}
.dropdown-item.is-reject {
    color: #DC2626;
}

/* Table Footer */
.inventory-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 18px;
    border-top: 1px solid #F1F5F9;
    flex-wrap: wrap;
    gap: 14px;
}
.footer-info {
    font-size: 13px;
    color: #64748B;
}
.footer-controls {
    display: flex;
    align-items: center;
    gap: 16px;
}
.pagination-links .pagination {
    margin: 0;
    gap: 4px;
}
.pagination-links .page-link {
    border-radius: 8px !important;
    border: 1px solid #E2E8F0;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 12px;
}
.pagination-links .page-item.active .page-link {
    background: #ED172B !important;
    border-color: #ED172B !important;
    color: #FFFFFF !important;
}
.per-page-form {
    display: inline-flex;
    align-items: center;
    margin: 0;
}
.per-page-select {
    height: 36px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    padding: 0 10px;
    font-size: 12.5px;
    color: #334155;
    background: #FFFFFF;
    cursor: pointer;
}

/* Empty State */
.table-empty-cell {
    padding: 48px 16px !important;
    text-align: center;
}
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    max-width: 400px;
    margin: 0 auto;
}
.empty-state-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #F1F5F9;
    color: #94A3B8;
    display: grid;
    place-items: center;
    margin-bottom: 16px;
}
.empty-state h3 {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: #0F172A;
}
.empty-state p {
    margin: 0 0 18px;
    font-size: 13.5px;
    color: #64748B;
}
.btn-empty-reset {
    display: inline-flex;
    align-items: center;
    padding: 8px 18px;
    border-radius: 8px;
    background: #ED172B;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dropdown toggle handler
    const dropdownWraps = document.querySelectorAll('[data-dropdown-wrap]');
    dropdownWraps.forEach(wrap => {
        const toggle = wrap.querySelector('[data-dropdown-toggle]');
        const menu = wrap.querySelector('[data-dropdown-menu]');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = !menu.hasAttribute('hidden');
            dropdownWraps.forEach(w => {
                const m = w.querySelector('[data-dropdown-menu]');
                if (m) m.setAttribute('hidden', '');
            });
            if (!isOpen) {
                menu.removeAttribute('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            } else {
                menu.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    });

    document.addEventListener('click', function () {
        dropdownWraps.forEach(w => {
            const m = w.querySelector('[data-dropdown-menu]');
            if (m) m.setAttribute('hidden', '');
            const t = w.querySelector('[data-dropdown-toggle]');
            if (t) t.setAttribute('aria-expanded', 'false');
        });
    });

    // Check all checkbox
    const checkAll = document.getElementById('checkAllInventory');
    const rowChecks = document.querySelectorAll('.inventory-row-check');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowChecks.forEach(cb => cb.checked = checkAll.checked);
        });
    }
});
</script>
@endpush
@endsection
