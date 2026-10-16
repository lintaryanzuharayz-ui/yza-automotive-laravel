@extends('layouts.app')
@section('title', 'Pesanan Mobil - Admin YZA Automotive')
@section('admin-page-title', 'Pesanan Mobil')

@section('content')
<div class="admin-sales-page">
    {{-- Header Pesanan Mobil Member --}}
    <header class="admin-sales-header">
        <div class="admin-sales-header-left">
            <nav class="admin-sales-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item" aria-label="Dashboard">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('admin.penjualan-mobil.index') }}">Penjualan Mobil</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">Pesanan Mobil</span>
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
                    <h1 class="admin-sales-heading">Pesanan Mobil Member</h1>
                    <p class="admin-sales-subheading">Kelola dan pantau pesanan mobil dari member, verifikasi pembayaran, dan proses serah-terima.</p>
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

    {{-- 6 Kartu Statistik Pesanan Member --}}
    <section class="admin-orders-stats-grid" aria-label="Statistik Pesanan Member">
        {{-- Card 1: Total Pesanan --}}
        <a class="admin-stat-card is-red" href="{{ route('admin.pesanan-mobil.index') }}">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-red" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Total Pesanan</span>
                    <strong class="admin-stat-value">{{ number_format($stats['total']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-red-text">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="4"></line><line x1="12" y1="20" x2="12" y2="10"></line><line x1="6" y1="20" x2="6" y2="16"></line></svg>
                    <span>Total pesanan dari member</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Semua Pesanan --}}
        <a class="admin-stat-card is-blue" href="{{ route('admin.pesanan-mobil.index') }}">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-blue" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Semua Pesanan</span>
                    <strong class="admin-stat-value">{{ number_format($stats['semua']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-blue-text">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    <span>Seluruh pesanan member</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Menunggu Verifikasi --}}
        <a class="admin-stat-card is-yellow" href="{{ route('admin.pesanan-mobil.index', ['status' => 'menunggu_verifikasi']) }}">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-yellow" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Menunggu Verifikasi</span>
                    <strong class="admin-stat-value">{{ number_format($stats['menunggu_verifikasi']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-yellow-text">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Perlu diverifikasi</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Dikonfirmasi --}}
        <a class="admin-stat-card is-green" href="{{ route('admin.pesanan-mobil.index', ['status' => 'dikonfirmasi']) }}">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-green" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Dikonfirmasi</span>
                    <strong class="admin-stat-value">{{ number_format($stats['dikonfirmasi']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-green-text">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Siap diproses</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Dalam Proses --}}
        <a class="admin-stat-card is-purple" href="{{ route('admin.pesanan-mobil.index', ['status' => 'proses']) }}">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-purple" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Dalam Proses</span>
                    <strong class="admin-stat-value">{{ number_format($stats['proses']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-purple-text">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Sedang dipersiapkan</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Selesai --}}
        <a class="admin-stat-card is-slate" href="{{ route('admin.pesanan-mobil.index', ['status' => 'diserahkan']) }}">
            <div class="admin-stat-top">
                <div class="admin-stat-icon is-slate" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
                        <line x1="4" y1="22" x2="4" y2="15"></line>
                    </svg>
                </div>
                <div class="admin-stat-info">
                    <span class="admin-stat-label">Selesai</span>
                    <strong class="admin-stat-value">{{ number_format($stats['selesai']) }}</strong>
                </div>
            </div>
            <div class="admin-stat-bottom">
                <div class="admin-stat-status is-slate-text">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>Pesanan selesai</span>
                </div>
            </div>
        </a>
    </section>

    {{-- Container Daftar Pesanan Mobil --}}
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
                    <h2 class="card-header-title">Daftar Pesanan Mobil</h2>
                    <p class="card-header-subtitle">Semua data pesanan mobil dari member.</p>
                </div>
            </div>

            <div class="card-header-right">
                <a class="btn-create-sale" href="{{ route('admin.penjualan-mobil.create') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Tambah Pesanan</span>
                </a>
            </div>
        </div>

        {{-- Search dan Filter Bar --}}
        <form class="admin-sales-filters" method="get" action="{{ route('admin.pesanan-mobil.index') }}">
            <div class="filter-search-wrap">
                <svg class="filter-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ $keyword }}" class="filter-search-input" placeholder="Cari berdasarkan Order ID, nama pelanggan, atau mobil..." aria-label="Cari pesanan">
            </div>

            <div class="filter-select-wrap">
                <select name="status" class="filter-select" aria-label="Filter status">
                    <option value="">Semua Status</option>
                    <option value="menunggu_verifikasi" @selected($status === 'menunggu_verifikasi')>Menunggu Verifikasi</option>
                    <option value="dikonfirmasi" @selected($status === 'dikonfirmasi' || $status === 'menunggu_pembayaran')>Dikonfirmasi</option>
                    <option value="proses" @selected($status === 'proses')>Dalam Proses</option>
                    <option value="selesai" @selected($status === 'selesai' || $status === 'diserahkan')>Selesai</option>
                    <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
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
                <button type="submit" class="btn-filter-apply is-red" title="Terapkan filter">
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.pesanan-mobil.index') }}" class="btn-filter-reset" title="Reset filter">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        {{-- Tabel Daftar Pesanan Mobil --}}
        <div class="admin-sales-table-wrapper">
            <table class="admin-sales-table admin-orders-table">
                <thead>
                    <tr>
                        <th class="th-no">#</th>
                        <th class="th-code">Order ID</th>
                        <th class="th-customer">Customer</th>
                        <th class="th-car">Mobil</th>
                        <th class="th-total">Tanggal Pesan</th>
                        <th class="th-status" style="width: 13%;">Pembayaran</th>
                        <th class="th-status" style="width: 11%;">Status</th>
                        <th class="th-action">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $car = $order->mobil;
                            $customer = $order->pelanggan;
                            $fotoMobil = $car?->foto?->first()?->url();
                            if (!$fotoMobil) {
                                if (str_contains(strtolower($order->snapshot_model ?? ''), 'supra')) {
                                    $fotoMobil = asset('assets/images/vehicles/toyota-gr-supra/black/front.png');
                                } else {
                                    $fotoMobil = asset('assets/images/hero/yza-hero-black-v2.png');
                                }
                            }
                            $customerIdentifier = $car?->kode_mobil ?? ($customer?->kode_pelanggan ?: ($order->telepon_pembeli ?: 'ID: PEL-'.str_pad($order->id_pelanggan, 4, '0', STR_PAD_LEFT)));
                            $orderIdDisplay = $order->penjualan?->kode_penjualan ?: $order->nomor_pesanan;
                            $cleanStatus = strtolower((string) $order->status);

                            // Status Pembayaran yang akurat
                            $transaksi = $order->transaksi;
                            $isPaid = ($transaksi?->status === 'lunas') || in_array($cleanStatus, ['lunas', 'pdi_lulus', 'siap_diambil', 'diserahkan']);
                            $isWaitingPayment = ($cleanStatus === 'menunggu_pembayaran') || ($transaksi?->status === 'belum_bayar' && !$isPaid);
                            $isPendingVerify = $transaksi?->pembayarans?->contains(fn($p) => $p->status === 'menunggu_verifikasi');
                        @endphp
                        <tr>
                            <td class="td-no">{{ $orders->firstItem() + $loop->index }}</td>
                            <td class="td-code">
                                <button type="button" class="sale-code-btn" data-open-detail-modal="{{ $order->id_pesanan }}" title="Lihat detail pesanan">
                                    <span class="sale-code">{{ $orderIdDisplay }}</span>
                                </button>
                                <time class="sale-date">{{ $order->created_at?->translatedFormat('d M Y, H:i') }}</time>
                            </td>
                            <td class="td-customer">
                                <div class="customer-cell">
                                    <div class="customer-avatar" aria-hidden="true">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    </div>
                                    <div class="customer-meta">
                                        <strong class="customer-name">{{ $customer?->nama_lengkap ?: $order->nama_pembeli }}</strong>
                                        <span class="customer-subtext">{{ $customerIdentifier }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="td-car">
                                <div class="car-cell">
                                    <img src="{{ $fotoMobil }}" alt="{{ $order->snapshot_merk }} {{ $order->snapshot_model }}" class="car-thumb" loading="lazy">
                                    <div class="car-meta">
                                        <strong class="car-title">{{ $order->snapshot_merk }} {{ $order->snapshot_model }} {{ $order->snapshot_tahun ?: '' }}</strong>
                                        <span class="car-brand">{{ $order->snapshot_merk }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="td-total">
                                <div>{{ $order->created_at?->translatedFormat('d M Y') }}</div>
                                <small class="sale-date" style="margin-top: 2px;">{{ $order->created_at?->format('H:i') }}</small>
                            </td>
                            <td class="td-status" style="text-align: center;">
                                @if($isPaid)
                                    <span class="badge-status is-paid">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span>Pembayaran Lunas</span>
                                    </span>
                                @elseif($isPendingVerify)
                                    <span class="badge-status is-verify">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span>Menunggu Verifikasi</span>
                                    </span>
                                @elseif($cleanStatus === 'ditolak' || $cleanStatus === 'dibatalkan')
                                    <span class="badge-status is-cancelled">
                                        <span>Dibatalkan</span>
                                    </span>
                                @else
                                    <span class="badge-status is-waiting">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <span>Menunggu Pembayaran</span>
                                    </span>
                                @endif
                            </td>
                            <td class="td-status" style="text-align: center;">
                                @if($cleanStatus === 'menunggu_pembayaran')
                                    <span class="badge-status is-confirmed">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                        <span>Dikonfirmasi</span>
                                    </span>
                                @elseif($cleanStatus === 'menunggu_verifikasi')
                                    <span class="badge-status is-waiting">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span>Menunggu Verifikasi</span>
                                    </span>
                                @elseif(in_array($cleanStatus, ['lunas', 'pdi_lulus', 'siap_diambil']))
                                    <span class="badge-status is-processing">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                        <span>Dalam Proses</span>
                                    </span>
                                @elseif($cleanStatus === 'diserahkan')
                                    <span class="badge-status is-success">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span>Selesai</span>
                                    </span>
                                @elseif($cleanStatus === 'ditolak')
                                    <span class="badge-status is-cancelled">
                                        <span>Ditolak</span>
                                    </span>
                                @else
                                    <span class="badge-status is-neutral">
                                        <span>{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="td-action">
                                <div class="action-cell">
                                    <button type="button" class="btn-detail-view" data-open-detail-modal="{{ $order->id_pesanan }}" title="Lihat detail pesanan (Pop-up)">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <span>Detail</span>
                                    </button>

                                    <div class="action-dropdown-wrap" data-dropdown-wrap>
                                        <button type="button" class="btn-more-options" data-dropdown-toggle aria-label="Opsi lainnya" aria-expanded="false">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                        </button>
                                        <div class="action-dropdown-menu" data-dropdown-menu hidden>
                                            <button type="button" class="dropdown-item" data-open-detail-modal="{{ $order->id_pesanan }}" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                <span>Detail Pesanan</span>
                                            </button>
                                            <button type="button" class="dropdown-item" data-order-detail
                                                data-order-id="{{ $orderIdDisplay }}"
                                                data-customer="{{ $customer?->nama_lengkap ?: $order->nama_pembeli }}"
                                                data-email="{{ $order->email_pembeli }}"
                                                data-car="{{ $order->snapshot_merk }} {{ $order->snapshot_model }}"
                                                data-type="{{ $order->snapshot_tipe ?: 'New Car' }}"
                                                data-year="{{ $order->snapshot_tahun ?: '-' }}"
                                                data-color="{{ $order->snapshot_warna ?: '-' }}"
                                                data-price="Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}"
                                                data-date="{{ $order->created_at?->format('d M Y H:i') }}"
                                                data-payment="{{ $isPaid ? 'Pembayaran Lunas' : ($isPendingVerify ? 'Menunggu Verifikasi' : 'Menunggu Pembayaran') }}"
                                                data-status="{{ ucwords(str_replace('_', ' ', $order->status)) }}"
                                                style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                                <span>Lihat Ringkasan</span>
                                            </button>

                                            @if($cleanStatus === 'menunggu_verifikasi')
                                                <button type="button" class="dropdown-item is-approve" data-decision="approve" data-form="approve-order-{{ $order->id_pesanan }}" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left; color: #16A34A;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <span>Setujui Pesanan</span>
                                                </button>
                                                <button type="button" class="dropdown-item is-reject" data-decision="reject" data-form="reject-order-{{ $order->id_pesanan }}" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left; color: #DC2626;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                                    <span>Tolak Pesanan</span>
                                                </button>
                                            @endif

                                            @if($order->transaksi && Route::has('admin.invoice.car-sale'))
                                                <a href="{{ route('admin.invoice.car-sale', $order->transaksi) }}" class="dropdown-item">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                                    <span>Lihat Invoice</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    @if($cleanStatus === 'menunggu_verifikasi')
                                        <form id="approve-order-{{ $order->id_pesanan }}" method="post" action="{{ route('admin.pesanan-mobil.approve', $order) }}" style="display: none;">
                                            @csrf
                                            <input type="hidden" name="catatan_admin" value="Pesanan disetujui oleh admin.">
                                        </form>
                                        <form id="reject-order-{{ $order->id_pesanan }}" method="post" action="{{ route('admin.pesanan-mobil.reject', $order) }}" style="display: none;">
                                            @csrf
                                            <input type="hidden" name="catatan_admin" value="Pesanan ditolak oleh admin.">
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-empty-cell">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                    </div>
                                    <h3>Tidak Ada Pesanan Mobil</h3>
                                    <p>Belum ada data pesanan mobil yang sesuai dengan filter pencarian Anda.</p>
                                    <a href="{{ route('admin.pesanan-mobil.index') }}" class="btn-empty-reset">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="admin-sales-pagination">
            <div class="pagination-info">
                Menampilkan <strong>{{ $orders->firstItem() ?: 0 }}</strong> – <strong>{{ $orders->lastItem() ?: 0 }}</strong> dari <strong>{{ $orders->total() }}</strong> data
            </div>
            <div class="pagination-links">
                {{ $orders->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </section>

    {{-- Modal Konfirmasi Keputusan (Approve / Reject) --}}
    <div class="admin-confirm-modal" data-decision-modal hidden>
        <div class="admin-confirm-backdrop" data-decision-cancel></div>
        <div class="admin-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="decision-title">
            <span class="admin-confirm-icon">!</span>
            <h2 id="decision-title" data-decision-title>Konfirmasi Keputusan Pesanan</h2>
            <p data-decision-message>Pastikan keputusan pesanan sudah benar.</p>
            <label class="admin-reject-note" data-reject-note hidden>
                Alasan penolakan (opsional)
                <textarea data-reject-input rows="3" placeholder="Tuliskan alasan penolakan pesanan..."></textarea>
            </label>
            <div class="admin-confirm-actions">
                <button type="button" class="admin-modal-button is-cancel" data-decision-cancel>Batal</button>
                <button type="button" class="admin-modal-button is-confirm" data-decision-submit>Konfirmasi</button>
            </div>
        </div>
    </div>

    {{-- Pop-up Modal Detail Pesanan Mobil (Luxury YZA Executive Modal) --}}
    @foreach($orders as $order)
        @php
            $car = $order->mobil;
            $customer = $order->pelanggan;
            $fotoMobil = $car?->foto?->first()?->url();
            if (!$fotoMobil) {
                if (str_contains(strtolower($order->snapshot_model ?? ''), 'supra')) {
                    $fotoMobil = asset('assets/images/vehicles/toyota-gr-supra/black/front.png');
                } else {
                    $fotoMobil = asset('assets/images/hero/yza-hero-black-v2.png');
                }
            }
            $customerIdentifier = $car?->kode_mobil ?? ($customer?->kode_pelanggan ?: ($order->telepon_pembeli ?: 'ID: PEL-'.str_pad($order->id_pelanggan, 4, '0', STR_PAD_LEFT)));
            $orderIdDisplay = $order->penjualan?->kode_penjualan ?: $order->nomor_pesanan;
            $cleanStatus = strtolower((string) $order->status);
            $transaksi = $order->transaksi;
            $isPaid = ($transaksi?->status === 'lunas') || in_array($cleanStatus, ['lunas', 'pdi_lulus', 'siap_diambil', 'diserahkan']);
            $isWaitingPayment = ($cleanStatus === 'menunggu_pembayaran') || ($transaksi?->status === 'belum_bayar' && !$isPaid);
            $isPendingVerify = $transaksi?->pembayarans?->contains(fn($p) => $p->status === 'menunggu_verifikasi');
        @endphp

        <div class="admin-order-detail-modal" id="order-detail-modal-{{ $order->id_pesanan }}" data-order-detail-modal hidden>
            <div class="admin-order-detail-backdrop" data-close-order-modal></div>
            <div class="admin-order-detail-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-title-{{ $order->id_pesanan }}">
                <div class="modal-top-accent-bar"></div>

                {{-- Header Modal dengan tombol silang (X) --}}
                <div class="admin-order-detail-header">
                    <div class="modal-header-meta">
                        <div class="modal-badge-group">
                            <span class="modal-brand-badge">YZA AUTOMOTIVE</span>
                            <span class="modal-order-number">#{{ $orderIdDisplay }}</span>
                        </div>
                        <h2 id="modal-title-{{ $order->id_pesanan }}" class="modal-main-title">Detail Pesanan Member</h2>
                        <div class="modal-timestamp">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Dipesan pada {{ $order->created_at?->translatedFormat('d F Y, H:i') }} WIB</span>
                        </div>
                    </div>
                    <button type="button" class="admin-detail-close-btn" data-close-order-modal aria-label="Tutup detail pesanan" title="Tutup (Esc)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                {{-- Hero Status & Price Ribbon --}}
                <div class="modal-summary-ribbon">
                    <div class="ribbon-statuses">
                        <div class="ribbon-status-item">
                            <span class="ribbon-label">Status Pesanan:</span>
                            @if($cleanStatus === 'menunggu_pembayaran')
                                <span class="badge-status is-confirmed">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                    <span>Dikonfirmasi</span>
                                </span>
                            @elseif($cleanStatus === 'menunggu_verifikasi')
                                <span class="badge-status is-waiting">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>Menunggu Verifikasi</span>
                                </span>
                            @elseif(in_array($cleanStatus, ['lunas', 'pdi_lulus', 'siap_diambil']))
                                <span class="badge-status is-processing">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                                    <span>Dalam Proses</span>
                                </span>
                            @elseif($cleanStatus === 'diserahkan')
                                <span class="badge-status is-success">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Selesai (Diserahkan)</span>
                                </span>
                            @elseif($cleanStatus === 'ditolak')
                                <span class="badge-status is-cancelled">
                                    <span>Ditolak</span>
                                </span>
                            @else
                                <span class="badge-status is-neutral">
                                    <span>{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
                                </span>
                            @endif
                        </div>

                        <div class="ribbon-status-item">
                            <span class="ribbon-label">Status Pembayaran:</span>
                            @if($isPaid)
                                <span class="badge-status is-paid">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Pembayaran Lunas</span>
                                </span>
                            @elseif($isPendingVerify)
                                <span class="badge-status is-verify">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <span>Menunggu Verifikasi</span>
                                </span>
                            @elseif($cleanStatus === 'ditolak' || $cleanStatus === 'dibatalkan')
                                <span class="badge-status is-cancelled">
                                    <span>Dibatalkan</span>
                                </span>
                            @else
                                <span class="badge-status is-waiting">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                    <span>Menunggu Pembayaran</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="ribbon-price-card">
                        <span class="price-card-label">TOTAL TAGIHAN</span>
                        <span class="price-card-value">Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Body Modal --}}
                <div class="modal-scrollable-body">
                    <div class="modal-detail-columns">
                        {{-- Kolom Kiri: Detail Mobil --}}
                        <div class="modal-column-card">
                            <div class="card-section-head">
                                <div class="section-head-icon is-red">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 11 2 11.4V16c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle></svg>
                                </div>
                                <div>
                                    <h3 class="section-head-title">Unit Mobil Dipesan</h3>
                                    <p class="section-head-sub">Detail spesifikasi kendaraan pilihan pembeli</p>
                                </div>
                            </div>

                            <div class="modal-vehicle-showcase">
                                <div class="vehicle-image-stage">
                                    <img src="{{ $fotoMobil }}" alt="{{ $order->snapshot_merk }} {{ $order->snapshot_model }}" class="vehicle-stage-img">
                                    <span class="vehicle-badge-pill">{{ $order->snapshot_tipe ?: 'Official Unit' }}</span>
                                </div>
                                <div class="vehicle-header-info">
                                    <h4 class="vehicle-name">{{ $order->snapshot_merk }} {{ $order->snapshot_model }} {{ $order->snapshot_tahun ?: '' }}</h4>
                                    <span class="vehicle-brand">{{ $order->snapshot_merk }} &bull; Sport / Luxury Collection</span>
                                </div>
                            </div>

                            <div class="modal-spec-grid">
                                <div class="spec-tile">
                                    <span class="spec-label">Merk</span>
                                    <strong class="spec-value">{{ $order->snapshot_merk }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Model</span>
                                    <strong class="spec-value">{{ $order->snapshot_model }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Tahun Pembuatan</span>
                                    <strong class="spec-value">{{ $order->snapshot_tahun ?: '-' }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Warna Pilihan</span>
                                    <strong class="spec-value">
                                        <span class="color-dot" style="background-color: {{ str_contains(strtolower($order->snapshot_warna ?? ''), 'white') ? '#FFFFFF; border: 1px solid #CBD5E1' : '#0F172A' }};"></span>
                                        {{ $order->snapshot_warna ?: 'Sesuai Standar' }}
                                    </strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Tipe Kendaraan</span>
                                    <strong class="spec-value">{{ $order->snapshot_tipe ?: 'New Car' }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Kode Mobil Unit</span>
                                    <strong class="spec-value">{{ $car?->kode_mobil ?: $customerIdentifier }}</strong>
                                </div>
                                @if($car?->nomor_rangka)
                                <div class="spec-tile is-wide">
                                    <span class="spec-label">Nomor Rangka (VIN)</span>
                                    <strong class="spec-value font-mono">{{ $car->nomor_rangka }}</strong>
                                </div>
                                @endif
                                @if($car?->nomor_mesin)
                                <div class="spec-tile is-wide">
                                    <span class="spec-label">Nomor Mesin</span>
                                    <strong class="spec-value font-mono">{{ $car->nomor_mesin }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Kolom Kanan: Pelanggan & Pembayaran --}}
                        <div class="modal-column-card">
                            <div class="card-section-head">
                                <div class="section-head-icon is-blue">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                                <div>
                                    <h3 class="section-head-title">Informasi Pembeli</h3>
                                    <p class="section-head-sub">Data member pemesan unit kendaraan</p>
                                </div>
                            </div>

                            <div class="customer-profile-card">
                                <div class="customer-avatar-large">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                                <div class="customer-info-meta">
                                    <h4 class="customer-full-name">{{ $customer?->nama_lengkap ?: $order->nama_pembeli }}</h4>
                                    <span class="customer-id-tag">{{ $customer?->kode_pelanggan ?: ('ID: PEL-'.str_pad($order->id_pelanggan, 4, '0', STR_PAD_LEFT)) }}</span>
                                </div>
                            </div>

                            <div class="customer-details-list">
                                <div class="detail-row">
                                    <span class="detail-row-icon">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    </span>
                                    <div class="detail-row-content">
                                        <span class="row-label">No. Telepon / WhatsApp</span>
                                        <strong class="row-value">{{ $order->telepon_pembeli ?: ($customer?->no_hp ?: '-') }}</strong>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-row-icon">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                    </span>
                                    <div class="detail-row-content">
                                        <span class="row-label">Email Pembeli</span>
                                        <strong class="row-value">{{ $order->email_pembeli ?: ($customer?->user?->email ?: '-') }}</strong>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-row-icon">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    </span>
                                    <div class="detail-row-content">
                                        <span class="row-label">Alamat Lengkap</span>
                                        <strong class="row-value">{{ $order->alamat_pembeli ?: ($customer?->alamat ?: 'Tidak ada alamat dicantumkan') }}</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Transaksi & Dokumen --}}
                            <div class="card-section-head" style="margin-top: 14px;">
                                <div class="section-head-icon is-slate">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                                </div>
                                <div>
                                    <h3 class="section-head-title">Transaksi & Operasional</h3>
                                    <p class="section-head-sub">Status transaksi keuangan dan persiapan unit</p>
                                </div>
                            </div>

                            <div class="modal-spec-grid">
                                <div class="spec-tile">
                                    <span class="spec-label">Kode Transaksi</span>
                                    <strong class="spec-value font-mono">{{ $transaksi?->kode_transaksi ?: ($order->penjualan?->kode_penjualan ?: '-') }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Metode Pembayaran</span>
                                    <strong class="spec-value">{{ $transaksi?->metode_pembayaran ? ucwords(str_replace('_', ' ', $transaksi->metode_pembayaran)) : 'Transfer Bank / Online' }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Status PDI (Inspeksi)</span>
                                    <strong class="spec-value">{{ $order->pdi ? ($order->pdi->final_quality ? 'PDI Lulus' : 'Dalam PDI') : 'Menunggu PDI' }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Jadwal Pengambilan</span>
                                    <strong class="spec-value">{{ $order->jadwal_pengambilan ? $order->jadwal_pengambilan->translatedFormat('d M Y, H:i') : 'Belum Ditentukan' }}</strong>
                                </div>
                            </div>

                            @php
                                $pdi = $order->pdi;
                                $pdiStatus = $pdi?->status;
                                $isPaidForWorkflow = $transaksi?->status === 'lunas';
                            @endphp
                            <div class="admin-purchase-workflow" aria-label="Workflow PDI dan serah terima">
                                <div class="card-section-head" style="margin-top: 18px;">
                                    <div class="section-head-icon is-red">✓</div>
                                    <div>
                                        <h3 class="section-head-title">PDI &amp; Serah Terima</h3>
                                        <p class="section-head-sub">Tahap berikutnya tersedia setelah pembayaran terverifikasi.</p>
                                    </div>
                                </div>

                                @if(!$isPaidForWorkflow)
                                    <p class="workflow-locked-note">Workflow terkunci sampai pembayaran berstatus Lunas.</p>
                                @elseif(!$pdi)
                                    <form method="post" action="{{ route('admin.pesanan-mobil.pdi.start', $order) }}" class="workflow-form">
                                        @csrf
                                        <button type="submit" class="btn-modal-approve">Mulai PDI</button>
                                    </form>
                                @elseif($pdiStatus !== 'lulus')
                                    <form method="post" action="{{ route('admin.pesanan-mobil.pdi', $order) }}" class="workflow-form">
                                        @csrf
                                        <div class="workflow-checklist-grid">
                                            @foreach($checklistDefinition as $section => $items)
                                                <fieldset class="workflow-checklist-section">
                                                    <legend>{{ ucwords(str_replace('_', ' ', $section)) }}</legend>
                                                    @foreach($items as $item)
                                                        @php $existing = $pdi->checklist[$section][$item] ?? []; @endphp
                                                        <div class="workflow-check-row">
                                                            <label>{{ ucwords(str_replace('_', ' ', $item)) }}
                                                                <select name="checklist[{{ $section }}][{{ $item }}][result]" required>
                                                                    <option value="pass" @selected(($existing['result'] ?? '') === 'pass')>PASS</option>
                                                                    <option value="na" @selected(($existing['result'] ?? '') === 'na')>N/A</option>
                                                                    <option value="issue" @selected(($existing['result'] ?? '') === 'issue')>ISSUE</option>
                                                                </select>
                                                            </label>
                                                            <input type="text" name="checklist[{{ $section }}][{{ $item }}][notes]" value="{{ $existing['notes'] ?? '' }}" placeholder="Catatan bila diperlukan">
                                                        </div>
                                                    @endforeach
                                                </fieldset>
                                            @endforeach
                                        </div>
                                        <label class="workflow-wide-field">Catatan PDI<textarea name="catatan" rows="2" placeholder="Catatan pemeriksaan sintetis UAT"></textarea></label>
                                        <button type="submit" class="btn-modal-approve">Simpan &amp; Selesaikan PDI</button>
                                    </form>
                                @elseif(!$order->jadwal_pengambilan)
                                    <div class="workflow-success-note">PDI LULUS. Jadwal pengambilan dapat dibuat.</div>
                                    <form method="post" action="{{ route('admin.pesanan-mobil.schedule', $order) }}" class="workflow-form workflow-inline-form">
                                        @csrf
                                        <label>Jadwal Pengambilan<input type="datetime-local" name="jadwal_pengambilan" required></label>
                                        <label>Lokasi<input type="text" name="lokasi_pengambilan" value="YZA Automotive Showroom" required></label>
                                        <label>Catatan<input type="text" name="catatan_pengambilan" placeholder="Catatan pickup UAT"></label>
                                        <button type="submit" class="btn-modal-approve">Simpan Jadwal &amp; Siap Diambil</button>
                                    </form>
                                @elseif(!$order->serahTerima)
                                    <div class="workflow-success-note">Pickup terjadwal: {{ $order->jadwal_pengambilan->format('d M Y, H:i') }} · {{ $order->lokasi_pengambilan }}</div>
                                    <form method="post" action="{{ route('admin.pesanan-mobil.handover', $order) }}" class="workflow-form workflow-inline-form">
                                        @csrf
                                        <label>Nama Penerima<input type="text" name="nama_penerima" value="{{ $customer?->nama_lengkap ?: $order->nama_pembeli }}" required></label>
                                        <label>Nomor Identitas<input type="text" name="nomor_identitas" value="UAT-SYNTHETIC-{{ $order->id_pesanan }}"></label>
                                        <label>Catatan<input type="text" name="catatan" value="Serah terima sintetis UAT"></label>
                                        <button type="submit" class="btn-modal-approve">Selesaikan Serah Terima</button>
                                    </form>
                                @else
                                    <div class="workflow-success-note">Serah terima selesai pada {{ $order->diserahkan_at?->format('d M Y, H:i') }}. Kendaraan telah diproses ke My Garage.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Action Bar --}}
                <div class="admin-order-detail-footer">
                    <div class="footer-left-actions">
                        @if($order->transaksi && Route::has('admin.invoice.car-sale'))
                            <a href="{{ route('admin.invoice.car-sale', $order->transaksi) }}" target="_blank" class="btn-modal-invoice">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                <span>Buka Dokumen Invoice</span>
                            </a>
                        @endif
                    </div>

                    <div class="footer-right-actions">
                        @if($cleanStatus === 'menunggu_verifikasi')
                            <button type="button" class="btn-modal-reject" data-decision="reject" data-form="reject-order-{{ $order->id_pesanan }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                <span>Tolak Pesanan</span>
                            </button>
                            <button type="button" class="btn-modal-approve" data-decision="approve" data-form="approve-order-{{ $order->id_pesanan }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Setujui Pesanan</span>
                            </button>
                        @endif

                        <button type="button" class="btn-modal-close" data-close-order-modal>
                            <span>Tutup</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('styles')
<style>
.admin-purchase-workflow{margin-top:18px;padding:18px;border:1px solid #e8ebef;border-radius:14px;background:#fbfcfd}.workflow-locked-note,.workflow-success-note{padding:12px 14px;border-radius:10px;background:#f4f6f8;color:#647486;font-size:12px}.workflow-success-note{background:#edf9f1;color:#187344}.workflow-form{display:grid;gap:12px;margin-top:12px}.workflow-checklist-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.workflow-checklist-section{min-width:0;padding:12px;border:1px solid #e4e8ec;border-radius:10px;background:#fff}.workflow-checklist-section legend{padding:0 6px;color:#263442;font-size:11px;font-weight:800;text-transform:uppercase}.workflow-check-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(110px,1fr);gap:8px;margin-top:8px}.workflow-check-row label,.workflow-inline-form label,.workflow-wide-field{display:grid;gap:5px;color:#647486;font-size:11px;font-weight:700}.workflow-check-row select,.workflow-check-row input,.workflow-inline-form input,.workflow-wide-field textarea{width:100%;box-sizing:border-box;padding:8px;border:1px solid #dce3e9;border-radius:7px;background:#fff;color:#263442;font:inherit;font-size:11px}.workflow-inline-form{grid-template-columns:repeat(3,minmax(0,1fr));align-items:end}.workflow-inline-form button{grid-column:1/-1;justify-self:start}@media(max-width:760px){.workflow-checklist-grid,.workflow-inline-form{grid-template-columns:1fr}.workflow-check-row{grid-template-columns:1fr}}
/* Tombol Order ID di Tabel */
.sale-code-btn {
    background: transparent;
    border: 0;
    padding: 0;
    font: inherit;
    color: inherit;
    cursor: pointer;
    text-align: left;
    display: inline-block;
}
.sale-code-btn:hover .sale-code {
    color: var(--yza-red, #ED172B);
    text-decoration: underline;
}

/* Modal Confirmation and Detail Styling */
.admin-confirm-modal, .admin-order-detail-modal {
    position: fixed;
    inset: 0;
    z-index: 120;
    display: grid;
    place-items: center;
    padding: 20px;
    box-sizing: border-box;
}
.admin-confirm-modal {
    z-index: 200;
}
.admin-confirm-modal[hidden], .admin-order-detail-modal[hidden] {
    display: none !important;
}
.admin-confirm-backdrop, .admin-order-detail-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(11, 16, 24, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.admin-confirm-dialog {
    position: relative;
    width: min(520px, calc(100% - 32px));
    padding: 36px 36px 32px;
    border-radius: 20px;
    background: #FFFFFF;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    text-align: center;
}
.admin-confirm-icon {
    display: grid;
    place-items: center;
    width: 62px;
    height: 62px;
    margin: 0 auto 18px;
    border-radius: 50%;
    background: #FFFBEB;
    color: #D97706;
    font-size: 30px;
    font-weight: 900;
    box-shadow: 0 6px 16px rgba(217, 138, 0, 0.15);
}
.admin-confirm-dialog h2 {
    margin: 0 0 10px;
    color: var(--yza-heading, #0F172A);
    font-size: 24px;
    font-weight: 800;
}
.admin-confirm-dialog p {
    margin: 0 auto;
    color: var(--yza-secondary, #64748B);
    font-size: 14px;
    line-height: 1.5;
}
.admin-reject-note {
    display: block;
    margin-top: 18px;
    text-align: left;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}
.admin-reject-note textarea {
    display: block;
    width: 100%;
    margin-top: 8px;
    padding: 12px;
    border: 1px solid var(--yza-border, #E2E8F0);
    border-radius: 10px;
    resize: vertical;
    font: inherit;
    font-size: 13px;
    box-sizing: border-box;
}
.admin-confirm-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 26px;
}
.admin-modal-button {
    min-width: 120px;
    border: 0;
    border-radius: 10px;
    padding: 12px 20px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.admin-modal-button.is-cancel {
    background: #F1F5F9;
    color: #475569;
}
.admin-modal-button.is-confirm {
    background: var(--yza-red, #ED172B);
    color: #FFFFFF;
    box-shadow: 0 6px 16px rgba(237, 23, 43, 0.25);
}
.admin-modal-button.is-confirm.is-approve {
    background: #16A34A;
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.25);
}

/* ========================================================
   POP-UP MODAL DETAIL PESANAN MOBIL (PREMIUM YZA STYLING)
   ======================================================== */
.admin-order-detail-dialog {
    position: relative;
    width: min(920px, calc(100vw - 32px));
    max-height: calc(100vh - 40px);
    background: #FFFFFF;
    border-radius: 22px;
    box-shadow: 0 25px 70px -10px rgba(15, 23, 42, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalPopIn 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes modalPopIn {
    from { opacity: 0; transform: scale(0.96) translateY(8px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-top-accent-bar {
    height: 4px;
    background: linear-gradient(90deg, #ED172B 0%, #FF3850 50%, #991B1B 100%);
    width: 100%;
    flex-shrink: 0;
}
.admin-order-detail-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 28px 16px;
    background: #FFFFFF;
    flex-shrink: 0;
}
.modal-badge-group {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 6px;
}
.modal-brand-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 6px;
    background: #FEE2E2;
    color: #ED172B;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
}
.modal-order-number {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    background: #F1F5F9;
    padding: 3px 9px;
    border-radius: 6px;
}
.modal-main-title {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.01em;
}
.modal-timestamp {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 5px;
    color: #64748B;
    font-size: 12.5px;
}
.admin-detail-close-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    border: 1px solid #E2E8F0;
    background: #F8FAFC;
    color: #64748B;
    display: grid;
    place-items: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.admin-detail-close-btn:hover {
    background: #FEE2E2;
    color: #DC2626;
    border-color: #FECACA;
    transform: rotate(90deg) scale(1.06);
}
.modal-summary-ribbon {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    padding: 14px 28px;
    background: #F8FAFC;
    border-top: 1px solid #F1F5F9;
    border-bottom: 1px solid #E2E8F0;
    flex-shrink: 0;
}
.ribbon-statuses {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.ribbon-status-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ribbon-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
}
.ribbon-price-card {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}
.price-card-label {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    color: #94A3B8;
}
.price-card-value {
    font-size: 23px;
    font-weight: 900;
    color: #ED172B;
    letter-spacing: -0.02em;
}
.modal-scrollable-body {
    padding: 24px 28px;
    overflow-y: auto;
    max-height: calc(85vh - 210px);
}
.modal-detail-columns {
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    gap: 24px;
}
@media (max-width: 820px) {
    .modal-detail-columns {
        grid-template-columns: 1fr;
    }
    .ribbon-price-card {
        align-items: flex-start;
        width: 100%;
        padding-top: 8px;
        border-top: 1px dashed #E2E8F0;
    }
}
.modal-column-card {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.card-section-head {
    display: flex;
    align-items: center;
    gap: 12px;
}
.section-head-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.section-head-icon.is-red {
    background: #FEE2E2;
    color: #DC2626;
}
.section-head-icon.is-blue {
    background: #EFF6FF;
    color: #2563EB;
}
.section-head-icon.is-slate {
    background: #F1F5F9;
    color: #475569;
}
.section-head-title {
    margin: 0;
    font-size: 14.5px;
    font-weight: 800;
    color: #0F172A;
}
.section-head-sub {
    margin: 2px 0 0;
    font-size: 12px;
    color: #64748B;
}
.modal-vehicle-showcase {
    border-radius: 14px;
    border: 1px solid #E2E8F0;
    overflow: hidden;
    background: #FFFFFF;
}
.vehicle-image-stage {
    position: relative;
    height: 185px;
    background: radial-gradient(circle at center, #1E293B 0%, #0F172A 100%);
    display: grid;
    place-items: center;
    overflow: hidden;
}
.vehicle-stage-img {
    max-width: 85%;
    max-height: 80%;
    object-fit: contain;
    filter: drop-shadow(0 12px 18px rgba(0, 0, 0, 0.45));
    transition: transform 0.3s ease;
}
.vehicle-stage-img:hover {
    transform: scale(1.05);
}
.vehicle-badge-pill {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    color: #F8FAFC;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.15);
}
.vehicle-header-info {
    padding: 12px 14px;
    background: #FFFFFF;
    border-top: 1px solid #F1F5F9;
}
.vehicle-name {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0F172A;
}
.vehicle-brand {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    color: #64748B;
}
.modal-spec-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
}
.spec-tile {
    padding: 10px 12px;
    border-radius: 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}
.spec-tile.is-wide {
    grid-column: 1 / -1;
}
.spec-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #64748B;
}
.spec-value {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 3px;
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
}
.color-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}
.font-mono {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    letter-spacing: 0.04em;
}
.customer-profile-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
}
.customer-avatar-large {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #EFF6FF;
    color: #2563EB;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    border: 1px solid #DBEAFE;
}
.customer-full-name {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #0F172A;
}
.customer-id-tag {
    display: inline-block;
    margin-top: 2px;
    font-size: 11.5px;
    color: #64748B;
    font-family: 'JetBrains Mono', monospace;
}
.customer-details-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.detail-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}
.detail-row-icon {
    color: #64748B;
    margin-top: 2px;
    flex-shrink: 0;
}
.detail-row-content {
    flex: 1;
}
.row-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #64748B;
}
.row-value {
    display: block;
    margin-top: 2px;
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
    word-break: break-word;
}
.admin-order-detail-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 28px;
    background: #F8FAFC;
    border-top: 1px solid #E2E8F0;
    flex-shrink: 0;
}
.footer-left-actions, .footer-right-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.btn-modal-invoice {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 16px;
    border-radius: 9px;
    background: #FFFFFF;
    color: #0F172A;
    border: 1px solid #CBD5E1;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}
.btn-modal-invoice:hover {
    background: #F1F5F9;
    border-color: #94A3B8;
}
.btn-modal-close {
    padding: 9px 20px;
    border-radius: 9px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-modal-close:hover {
    background: #F1F5F9;
    color: #0F172A;
}
.btn-modal-approve {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 9px;
    border: 0;
    background: #16A34A;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
    transition: all 0.15s ease;
}
.btn-modal-approve:hover {
    background: #15803D;
    box-shadow: 0 6px 16px rgba(22, 163, 74, 0.35);
}
.btn-modal-reject {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 9px;
    border: 1px solid #FECACA;
    background: #FEE2E2;
    color: #DC2626;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-modal-reject:hover {
    background: #FCA5A5;
    color: #991B1B;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dropdown more options handling
    document.querySelectorAll('[data-dropdown-wrap]').forEach(function (wrap) {
        var toggle = wrap.querySelector('[data-dropdown-toggle]');
        var menu = wrap.querySelector('[data-dropdown-menu]');
        if (!toggle || !menu) return;

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            var isHidden = menu.hasAttribute('hidden');
            // Close other dropdowns
            document.querySelectorAll('[data-dropdown-menu]').forEach(function (m) {
                m.setAttribute('hidden', '');
            });
            document.querySelectorAll('[data-dropdown-toggle]').forEach(function (b) {
                b.setAttribute('aria-expanded', 'false');
            });
            if (isHidden) {
                menu.removeAttribute('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('[data-dropdown-menu]').forEach(function (m) {
            m.setAttribute('hidden', '');
        });
        document.querySelectorAll('[data-dropdown-toggle]').forEach(function (b) {
            b.setAttribute('aria-expanded', 'false');
        });
    });

    // Pop-up Detail Modal Handling (Open on current page)
    var closeAllDetailModals = function () {
        document.querySelectorAll('.admin-order-detail-modal').forEach(function (modal) {
            modal.setAttribute('hidden', '');
        });
        document.body.style.overflow = '';
        if (window.location.search.includes('detail=')) {
            var newSearch = window.location.search.replace(/([?&])detail=[^&]*(&|$)/, function (match, p1, p2) {
                return p2 === '&' ? p1 : '';
            }).replace(/\?$/, '');
            var newUrl = window.location.pathname + newSearch + window.location.hash;
            window.history.replaceState(null, '', newUrl);
        }
    };

    // Auto-open modal if URL query param has ?detail=...
    var urlParams = new URLSearchParams(window.location.search);
    var detailId = urlParams.get('detail');
    if (detailId) {
        var autoModal = document.getElementById('order-detail-modal-' + detailId);
        if (autoModal) {
            closeAllDetailModals();
            autoModal.removeAttribute('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    document.querySelectorAll('[data-open-detail-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Close any open dropdowns
            document.querySelectorAll('[data-dropdown-menu]').forEach(function (m) {
                m.setAttribute('hidden', '');
            });

            var orderId = this.getAttribute('data-open-detail-modal');
            var targetModal = document.getElementById('order-detail-modal-' + orderId);
            if (targetModal) {
                closeAllDetailModals();
                targetModal.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close Modal via [data-close-order-modal] ('X' button and 'Tutup' button)
    document.querySelectorAll('[data-close-order-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            closeAllDetailModals();
        });
    });

    // Close when clicking backdrop
    document.querySelectorAll('.admin-order-detail-modal').forEach(function (modal) {
        var backdrop = modal.querySelector('.admin-order-detail-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                e.preventDefault();
                closeAllDetailModals();
            });
        }
    });

    // Confirmation Decision Modal Handling
    var decisionModal = document.querySelector('[data-decision-modal]');
    if (decisionModal) {
        var targetForm = null;
        var titleNode = decisionModal.querySelector('[data-decision-title]');
        var messageNode = decisionModal.querySelector('[data-decision-message]');
        var noteNode = decisionModal.querySelector('[data-reject-note]');
        var inputNode = decisionModal.querySelector('[data-reject-input]');
        var submitNode = decisionModal.querySelector('[data-decision-submit]');

        var closeDecision = function () {
            decisionModal.hidden = true;
            targetForm = null;
            if (noteNode) noteNode.hidden = true;
            if (inputNode) inputNode.value = '';
            // Only restore scroll if detail modal is also closed
            var hasOpenDetail = document.querySelector('.admin-order-detail-modal:not([hidden])');
            if (!hasOpenDetail) {
                document.body.style.overflow = '';
            }
        };

        document.querySelectorAll('[data-decision]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                targetForm = document.getElementById(btn.dataset.form);
                var isReject = btn.dataset.decision === 'reject';
                if (titleNode) titleNode.textContent = isReject ? 'Konfirmasi Penolakan Pesanan' : 'Konfirmasi Persetujuan Pesanan';
                if (messageNode) messageNode.textContent = isReject ? 'Apakah Anda yakin ingin menolak pesanan ini? Unit mobil akan dilepas kembali ke stok.' : 'Apakah Anda yakin ingin menyetujui pesanan ini? Transaksi pembayaran akan dibuat untuk pelanggan.';
                if (noteNode) noteNode.hidden = !isReject;
                if (submitNode) {
                    submitNode.textContent = isReject ? 'Ya, Tolak Pesanan' : 'Ya, Setujui Pesanan';
                    submitNode.classList.toggle('is-approve', !isReject);
                }
                decisionModal.hidden = false;
                document.body.style.overflow = 'hidden';
            });
        });

        decisionModal.querySelectorAll('[data-decision-cancel]').forEach(function (btn) {
            btn.addEventListener('click', closeDecision);
        });

        if (submitNode) {
            submitNode.addEventListener('click', function () {
                if (!targetForm) return;
                if (noteNode && !noteNode.hidden && inputNode) {
                    var noteField = targetForm.querySelector('[name="catatan_admin"]');
                    if (noteField) noteField.value = inputNode.value.trim() || 'Pesanan ditolak oleh admin.';
                }
                targetForm.submit();
            });
        }
    }

    // Escape Key to close any active modal
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (decisionModal && !decisionModal.hidden) {
                decisionModal.hidden = true;
            } else {
                closeAllDetailModals();
            }
        }
    });
});
</script>
@endpush
