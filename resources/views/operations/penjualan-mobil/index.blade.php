@extends('layouts.app')
@section('title', 'Penjualan Mobil - YZA Automotive Admin')
@section('admin-page-title', 'Penjualan Mobil')

@section('content')
<div class="admin-sales-page">
    {{-- Header Penjualan Mobil --}}
    <header class="admin-sales-header">
        <div class="admin-sales-header-left">
            <nav class="admin-sales-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item" aria-label="Dashboard">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Penjualan Mobil</span>
            </nav>

            <div class="admin-sales-title-group">
                <div class="admin-sales-icon-box" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <path d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                </div>
                <div>
                    <h1 class="admin-sales-heading">Penjualan Mobil</h1>
                    <p class="admin-sales-subheading">Kelola data penjualan mobil, pantau progres transaksi, dan pastikan setiap penjualan berjalan dengan lancar.</p>
                </div>
            </div>
        </div>

        <div class="admin-sales-header-right">
            <div class="admin-sales-banner">
                <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="YZA Automotive Premium Sports Car" class="admin-banner-img" loading="eager">
                <div class="admin-banner-overlay"></div>
                <div class="admin-banner-content">
                    <span class="admin-banner-brand">YZA AUTOMOTIVE</span>
                    <span class="admin-banner-tagline">DRIVE A BETTER TOMORROW</span>
                </div>
            </div>
        </div>
    </header>

    {{-- 4 Kartu Statistik Penjualan --}}
    <section class="admin-sales-stats-grid" aria-label="Statistik Penjualan">
        {{-- Card 1: Total Penjualan --}}
        <article class="admin-stat-card is-sales">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-red" aria-hidden="true">
                    <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Total Penjualan</span>
                    <strong class="admin-stat-value">{{ number_format($stats['total']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-trend">
                    <span class="trend-indicator is-positive">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        +100%
                    </span>
                    <span class="trend-text">Dibanding bulan lalu</span>
                </div>
                <svg class="admin-stat-sparkline" viewBox="0 0 70 24" fill="none" stroke="#ED172B" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M2 18 C 18 18, 25 12, 38 14 C 50 16, 56 4, 68 6" />
                </svg>
            </div>
        </article>

        {{-- Card 2: Unit Diproses --}}
        <article class="admin-stat-card is-process">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-blue" aria-hidden="true">
                    <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <path d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Unit Diproses</span>
                    <strong class="admin-stat-value">{{ number_format($stats['proses']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-blue-text">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Dalam proses transaksi</span>
                </div>
            </div>
        </article>

        {{-- Card 3: Penjualan Selesai --}}
        <article class="admin-stat-card is-done">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-green" aria-hidden="true">
                    <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Penjualan Selesai</span>
                    <strong class="admin-stat-value">{{ number_format($stats['selesai']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-green-text">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>Transaksi berhasil</span>
                </div>
            </div>
        </article>

        {{-- Card 4: Total Pendapatan --}}
        <article class="admin-stat-card is-revenue">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-yellow" aria-hidden="true">
                    <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                        <line x1="2" x2="22" y1="10" y2="10"></line>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Total Pendapatan</span>
                    <strong class="admin-stat-value is-currency">
                        Rp {{ number_format($stats['pendapatan_terverifikasi'] > 0 ? $stats['pendapatan_terverifikasi'] : $stats['total_nilai_kontrak'], 0, ',', '.') }}
                    </strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-yellow-text" title="Pendapatan terverifikasi: Rp {{ number_format($stats['pendapatan_terverifikasi'], 0, ',', '.') }} | Total Kontrak: Rp {{ number_format($stats['total_nilai_kontrak'], 0, ',', '.') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"></line><line x1="18" y1="20" x2="18" y2="4"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                    <span>Dari {{ $stats['total'] }} transaksi (Terverifikasi: Rp {{ number_format($stats['pendapatan_terverifikasi'], 0, ',', '.') }})</span>
                </div>
            </div>
        </article>
    </section>

    {{-- Container Data Penjualan --}}
    <section class="admin-sales-card">
        {{-- Card Header --}}
        <div class="admin-sales-card-header">
            <div class="card-header-left">
                <div class="card-header-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"></path>
                        <circle cx="7" cy="17" r="2"></circle>
                        <path d="M9 17h6"></path>
                        <circle cx="17" cy="17" r="2"></circle>
                    </svg>
                </div>
                <div>
                    <h2 class="card-header-title">Daftar Penjualan Mobil</h2>
                    <p class="card-header-subtitle">Semua data transaksi penjualan mobil di YZA Automotive</p>
                </div>
            </div>

            <div class="card-header-right">
                <a class="btn-create-sale" href="{{ route(request()->user()->roleName().'.penjualan-mobil.create') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Buat Penjualan</span>
                </a>
            </div>
        </div>

        {{-- Search dan Filter Bar --}}
        <form class="admin-sales-filters" method="get" action="{{ url()->current() }}">
            <div class="filter-search-wrap">
                <svg class="filter-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ $keyword }}" class="filter-search-input" placeholder="Cari kode, nama pelanggan, atau unit mobil..." aria-label="Cari transaksi">
            </div>

            <div class="filter-select-wrap">
                <select name="status" class="filter-select" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="proses" @selected($status === 'proses')>Proses</option>
                    <option value="selesai" @selected($status === 'selesai')>Selesai</option>
                    <option value="batal" @selected($status === 'batal')>Dibatalkan</option>
                </select>
                <svg class="select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-date-wrap">
                <svg class="filter-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <input type="date" name="from" value="{{ $from }}" class="filter-date-input" title="Dari tanggal" aria-label="Dari tanggal">
                <span class="date-sep">–</span>
                <input type="date" name="to" value="{{ $to }}" class="filter-date-input" title="Sampai tanggal" aria-label="Sampai tanggal">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter-apply" title="Terapkan filter">
                    <span>Terapkan</span>
                </button>
                <a href="{{ url()->current() }}" class="btn-filter-reset" title="Reset filter">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        {{-- Tabel Daftar Penjualan --}}
        <div class="admin-sales-table-wrapper">
            <table class="admin-sales-table">
                <thead>
                    <tr>
                        <th class="th-no">No</th>
                        <th class="th-code">Kode</th>
                        <th class="th-customer">Pelanggan</th>
                        <th class="th-car">Unit</th>
                        <th class="th-total">Total</th>
                        <th class="th-status">Status</th>
                        <th class="th-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php
                            $car = $row->mobil;
                            $customer = $row->pelanggan;
                            $fotoMobil = $car?->foto?->first()?->url();
                            if (!$fotoMobil) {
                                if (str_contains(strtolower($car?->model ?? ''), 'supra')) {
                                    $fotoMobil = asset('assets/images/vehicles/toyota-gr-supra/black/front.png');
                                } else {
                                    $fotoMobil = asset('assets/images/hero/yza-hero-black-v2.png');
                                }
                            }
                            $customerIdentifier = $car?->kode_mobil ?? ($customer?->nomor_telepon ?: 'ID: PEL-'.str_pad($row->id_pelanggan, 4, '0', STR_PAD_LEFT));
                            $statusClean = strtolower((string) $row->status);
                        @endphp
                        <tr>
                            <td class="td-no">{{ $rows->firstItem() + $loop->index }}</td>
                            <td class="td-code">
                                <span class="sale-code">{{ $row->kode_penjualan }}</span>
                                <time class="sale-date">{{ $row->tanggal_penjualan ? $row->tanggal_penjualan->translatedFormat('d M Y, H:i') : ($row->created_at ? $row->created_at->translatedFormat('d M Y, H:i') : '-') }}</time>
                            </td>
                            <td class="td-customer">
                                <div class="customer-cell">
                                    <div class="customer-avatar" aria-hidden="true">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="customer-meta">
                                        <strong class="customer-name">{{ $customer?->nama_lengkap ?? 'Pelanggan Umum' }}</strong>
                                        <span class="customer-subtext">{{ $customerIdentifier }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="td-car">
                                <div class="car-cell">
                                    <img src="{{ $fotoMobil }}" alt="{{ $car?->merk }} {{ $car?->model }}" class="car-thumb" loading="lazy">
                                    <div class="car-meta">
                                        <strong class="car-title">{{ $car?->merk }} {{ $car?->model }} {{ $car?->tahun ? $car->tahun : '' }}</strong>
                                        <span class="car-brand">{{ $car?->merk ?? 'Toyota' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="td-total">
                                <strong class="sale-price">Rp {{ number_format((float) $row->total_akhir, 0, ',', '.') }}</strong>
                            </td>
                            <td class="td-status">
                                @if($statusClean === 'selesai')
                                    <span class="badge-status is-success">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span>Selesai Detail</span>
                                    </span>
                                @elseif($statusClean === 'proses')
                                    <span class="badge-status is-process">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span>Proses Detail</span>
                                    </span>
                                @elseif($statusClean === 'batal' || $statusClean === 'dibatalkan')
                                    <span class="badge-status is-cancelled">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        <span>Dibatalkan</span>
                                    </span>
                                @else
                                    <span class="badge-status is-neutral">
                                        <span>{{ ucfirst($row->status) }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="td-action">
                                <div class="action-cell">
                                    <a href="{{ route(request()->user()->roleName().'.penjualan-mobil.show', $row) }}" class="btn-detail-view" title="Lihat detail penjualan">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="3" r="3"></circle></svg>
                                        <span>Lihat Detail</span>
                                    </a>

                                    <div class="action-dropdown-wrap" data-dropdown-wrap>
                                        <button type="button" class="btn-more-options" data-dropdown-toggle aria-label="Opsi lainnya" aria-expanded="false">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                        </button>
                                        <div class="action-dropdown-menu" data-dropdown-menu hidden>
                                            <a href="{{ route(request()->user()->roleName().'.penjualan-mobil.show', $row) }}" class="dropdown-item">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                <span>Detail Transaksi</span>
                                            </a>
                                            @if($row->id_transaksi)
                                                <a href="{{ Route::has(request()->user()->roleName().'.invoice.car-sale') ? route(request()->user()->roleName().'.invoice.car-sale', $row->id_transaksi) : '#' }}" class="dropdown-item" target="_blank">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                    <span>Invoice / Kwitansi</span>
                                                </a>
                                                @if($statusClean === 'proses')
                                                    <a href="{{ Route::has(request()->user()->roleName().'.car-sale.payment.create') ? route(request()->user()->roleName().'.car-sale.payment.create', $row->id_transaksi) : '#' }}" class="dropdown-item">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                                        <span>Catat Pembayaran</span>
                                                    </a>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty-cell">
                                <div class="empty-state">
                                    <div class="empty-state-icon" aria-hidden="true">
                                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    </div>
                                    <h3>Tidak Ada Data Penjualan</h3>
                                    <p>Tidak ada transaksi penjualan mobil yang sesuai dengan filter yang dipilih.</p>
                                    <a href="{{ url()->current() }}" class="btn-empty-reset">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <footer class="admin-sales-pagination">
            <div class="pagination-info">
                Menampilkan <strong>{{ $rows->firstItem() ?: 0 }}</strong> – <strong>{{ $rows->lastItem() ?: 0 }}</strong> dari <strong>{{ $rows->total() }}</strong> data
            </div>
            <div class="pagination-links">
                {{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </footer>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    // Dropdown toggle for row actions
    const dropdownWraps = document.querySelectorAll('[data-dropdown-wrap]');
    dropdownWraps.forEach(wrap => {
        const toggle = wrap.querySelector('[data-dropdown-toggle]');
        const menu = wrap.querySelector('[data-dropdown-menu]');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !menu.hidden;
            // Close others
            dropdownWraps.forEach(w => {
                const m = w.querySelector('[data-dropdown-menu]');
                const t = w.querySelector('[data-dropdown-toggle]');
                if (m) m.hidden = true;
                if (t) t.setAttribute('aria-expanded', 'false');
            });
            menu.hidden = isOpen;
            toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
        });
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-dropdown-wrap]')) {
            dropdownWraps.forEach(w => {
                const m = w.querySelector('[data-dropdown-menu]');
                const t = w.querySelector('[data-dropdown-toggle]');
                if (m) m.hidden = true;
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdownWraps.forEach(w => {
                const m = w.querySelector('[data-dropdown-menu]');
                if (m) m.hidden = true;
            });
        }
    });
})();
</script>
@endpush
