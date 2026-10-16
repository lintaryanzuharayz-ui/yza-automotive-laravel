@extends('layouts.app')

@section('title', 'Manajemen Pembayaran - YZA Automotive')
@section('admin-page-title', 'Manajemen Pembayaran')

@section('content')
@php
    $items = $tab === 'online' ? $online : $offline;
@endphp

<div class="admin-payment-page" data-admin-payment-live-state="{{ sha1(implode('|', [
    $tab,
    $status,
    $q,
    optional($items->first())->id_pembayaran,
    optional($items->first())->status,
    optional($items->first())->tanggal?->toISOString(),
    $stats['waiting_confirmation'],
    $stats['waiting_payment'],
])) }}">
    {{-- Header & Banner --}}
    <header class="admin-payment-header">
        <div class="admin-payment-header-left">
            <nav class="admin-payment-breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-back-arrow" aria-hidden="true">&lsaquo;</span>
                <span class="breadcrumb-item">Keuangan</span>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current">Manajemen Pembayaran</span>
            </nav>

            <div class="admin-payment-title-group">
                <h1 class="admin-payment-heading">Manajemen Pembayaran</h1>
                <p class="admin-payment-subheading">Pantau pembayaran kendaraan secara online dan verifikasi transaksi offline melalui satu sistem yang aman.</p>
            </div>
        </div>

        <div class="admin-payment-header-right">
            <div class="admin-payment-banner">
                <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="YZA Automotive Payment Banner" class="payment-banner-img" loading="eager">
                <div class="payment-banner-overlay"></div>
                <div class="payment-banner-content">
                    <span class="banner-tagline-top">TRANSAKSI AMAN</span>
                    <span class="banner-tagline-bottom">PENJUALAN LEBIH MUDAH</span>
                    <div class="banner-accent-line"></div>
                </div>
            </div>
        </div>
    </header>

    @if(session('status'))
        <div class="admin-alert is-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="admin-alert is-error" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Enam Statistik Premium --}}
    <section class="payment-kpi-grid" aria-label="Statistik Pembayaran">
        {{-- 1. Dana Online Terverifikasi (Hijau Pastel) --}}
        <div class="payment-kpi-card is-green">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Dana Online Terverifikasi</span>
                <strong class="kpi-value">Rp {{ number_format($stats['online_verified'], 0, ',', '.') }}</strong>
                <div class="kpi-subtext is-up">
                    <span>{{ $stats['online_verified_count'] ?? 0 }} transaksi</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </div>
            </div>
        </div>

        {{-- 2. Dana Offline Terverifikasi (Biru Pastel) --}}
        <div class="payment-kpi-card is-blue">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Dana Offline Terverifikasi</span>
                <strong class="kpi-value">Rp {{ number_format($stats['offline_verified'], 0, ',', '.') }}</strong>
                <div class="kpi-subtext">
                    <span>{{ $stats['offline_verified_count'] ?? 0 }} transaksi</span>
                </div>
            </div>
        </div>

        {{-- 3. Menunggu Pembayaran (Kuning/Amber Pastel) --}}
        <div class="payment-kpi-card is-amber">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Menunggu Pembayaran</span>
                <strong class="kpi-value">{{ $stats['waiting_payment'] }}</strong>
                <div class="kpi-subtext">
                    <span>transaksi</span>
                </div>
            </div>
        </div>

        {{-- 4. Menunggu Konfirmasi (Merah Pastel) --}}
        <div class="payment-kpi-card is-red">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Menunggu Konfirmasi</span>
                <strong class="kpi-value">{{ $stats['waiting_confirmation'] }}</strong>
                <div class="kpi-subtext">
                    <span>transaksi</span>
                </div>
            </div>
        </div>

        {{-- 5. Transaksi Lunas (Hijau Pastel) --}}
        <div class="payment-kpi-card is-emerald">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Transaksi Lunas</span>
                <strong class="kpi-value">{{ $stats['paid_transactions'] }}</strong>
                <div class="kpi-subtext">
                    <span>transaksi</span>
                </div>
            </div>
        </div>

        {{-- 6. Total Nilai Transaksi (Ungu Pastel) --}}
        <div class="payment-kpi-card is-purple">
            <div class="kpi-icon-box">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Nilai Transaksi</span>
                <strong class="kpi-value">Rp {{ number_format($stats['total_transaction_value'], 0, ',', '.') }}</strong>
                <div class="kpi-subtext is-up">
                    <span>{{ $stats['total_transaction_count'] }} transaksi</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Container Card --}}
    <main class="payment-main-card">
        {{-- Tab Pembayaran (Online | Offline) --}}
        <div class="payment-tabs-bar">
            <a href="{{ route('admin.payments.index', array_merge(request()->query(), ['tab' => 'online', 'online_page' => 1])) }}"
               class="payment-tab-item {{ $tab === 'online' ? 'is-active' : '' }}" aria-label="PEMBAYARAN ONLINE">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                <span>Pembayaran Online</span>
                <span class="sr-only" style="display:none">PEMBAYARAN ONLINE</span>
            </a>
            <a href="{{ route('admin.payments.index', array_merge(request()->query(), ['tab' => 'offline', 'offline_page' => 1])) }}"
               class="payment-tab-item {{ $tab === 'offline' ? 'is-active' : '' }}" aria-label="PEMBAYARAN OFFLINE">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Pembayaran Offline</span>
                <span class="sr-only" style="display:none">PEMBAYARAN OFFLINE</span>
            </a>
        </div>

        {{-- Filter Toolbar --}}
        <form class="payment-filter-toolbar" method="get" action="{{ route('admin.payments.index') }}">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if(request('per_page'))
                <input type="hidden" name="per_page" value="{{ request('per_page') }}">
            @endif

            {{-- Input Pencarian --}}
            <div class="filter-input-wrap search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode pembayaran, nama pelanggan, atau nomor pesanan.." class="form-input">
            </div>

            {{-- Dropdown Status --}}
            <div class="filter-input-wrap select-box">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    @if($tab === 'online')
                        @foreach(['settlement' => 'Terverifikasi (Settlement)', 'capture' => 'Terverifikasi (Capture)', 'pending' => 'Menunggu Pembayaran', 'created' => 'Dibuat', 'failure' => 'Gagal', 'expire' => 'Kedaluwarsa', 'cancel' => 'Dibatalkan'] as $val => $txt)
                            <option value="{{ $val }}" @selected($status === $val)>{{ $txt }}</option>
                        @endforeach
                    @else
                        @foreach(['terverifikasi' => 'Terverifikasi', 'menunggu' => 'Menunggu Konfirmasi', 'ditolak' => 'Ditolak'] as $val => $txt)
                            <option value="{{ $val }}" @selected($status === $val)>{{ $txt }}</option>
                        @endforeach
                    @endif
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            {{-- Tanggal Mulai --}}
            <div class="filter-input-wrap date-box">
                <input type="date" name="from" value="{{ $from }}" class="form-input date-input" placeholder="dd/mm/yyyy" title="Tanggal Mulai">
            </div>

            {{-- Tanggal Selesai --}}
            <div class="filter-input-wrap date-box">
                <input type="date" name="to" value="{{ $to }}" class="form-input date-input" placeholder="dd/mm/yyyy" title="Tanggal Selesai">
            </div>

            {{-- Tombol Aksi --}}
            <div class="filter-actions-wrap">
                <button type="submit" class="btn-filter-apply">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Terapkan</span>
                </button>
                <a href="{{ route('admin.payments.index', ['tab' => $tab]) }}" class="btn-filter-reset">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
                    <span>Reset</span>
                </a>
            </div>
        </form>

        {{-- Tabel Pembayaran --}}
        <div class="payment-table-responsive">
            <table class="payment-table">
                <thead>
                    <tr>
                        <th class="th-code">KODE PEMBAYARAN</th>
                        <th class="th-customer">PELANGGAN / MEMBER</th>
                        <th class="th-car">MOBIL</th>
                        <th class="th-method">METODE PEMBAYARAN</th>
                        <th class="th-amount">NOMINAL</th>
                        <th class="th-proof">BUKTI</th>
                        <th class="th-date">
                            <span class="th-sortable">
                                <span>TANGGAL</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="7 15 12 20 17 15"></polyline><polyline points="7 9 12 4 17 9"></polyline></svg>
                            </span>
                        </th>
                        <th class="th-status" style="text-align: center;">STATUS</th>
                        <th class="th-action" style="text-align: center;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $row)
                        @php
                            $isOnline = ($tab === 'online');
                            if ($isOnline) {
                                $gateway = $row;
                                $code = $gateway->order_id;
                                $customerName = $gateway->transaksi?->pelanggan?->nama_lengkap ?? $gateway->membership?->pelanggan?->nama_lengkap ?? 'Pelanggan Online';
                                $customerCode = $gateway->transaksi?->pelanggan?->kode_pelanggan ?? ($gateway->membership ? 'Member #M'.str_pad($gateway->membership->id_membership, 4, '0', STR_PAD_LEFT) : 'Member #Online');
                                $car = $gateway->transaksi?->penjualanMobil?->mobil ?? $gateway->transaksi?->pesananMobilMember?->mobil;
                                $methodType = $gateway->payment_type ? ucwords(str_replace('_', ' ', $gateway->payment_type)) : 'Midtrans Snap';
                                $amount = (float) $gateway->gross_amount;
                                $proofText = 'Midtrans Gateway';
                                $dateStr = $gateway->created_at?->translatedFormat('d M Y H:i');
                                $rawStatus = strtolower((string) $gateway->status_provider);
                                $isVerified = in_array($rawStatus, ['settlement', 'capture']);
                                $isWaiting = in_array($rawStatus, ['pending', 'created']);
                                $statusLabel = $isVerified ? 'Terverifikasi' : ($isWaiting ? 'Menunggu' : ucwords(str_replace('_', ' ', $rawStatus)));
                                $rowId = 'online-' . $gateway->id_pembayaran_gateway;
                            } else {
                                $payment = $row;
                                $code = $payment->kode_pembayaran ?: ($payment->transaksi?->pesananMobilMember?->nomor_pesanan ?: 'PAY-'.str_pad($payment->id_pembayaran, 6, '0', STR_PAD_LEFT));
                                $customerName = $payment->transaksi?->pelanggan?->nama_lengkap ?: 'Pelanggan Test YZA';
                                $customerCode = $payment->transaksi?->pelanggan?->kode_pelanggan ?: 'Member #M'.str_pad($payment->transaksi?->id_pelanggan ?? 1, 4, '0', STR_PAD_LEFT);
                                $car = $payment->transaksi?->penjualanMobil?->mobil ?? $payment->transaksi?->pesananMobilMember?->mobil;
                                $methodType = ucfirst((string) $payment->metode);
                                $amount = (float) $payment->jumlah;
                                $hasProof = !empty($payment->bukti_transfer);
                                $proofText = $hasProof ? '1 file' : 'Tidak ada';
                                $dateStr = $payment->tanggal ? $payment->tanggal->translatedFormat('d M Y H:i') : $payment->created_at?->translatedFormat('d M Y H:i');
                                $rawStatus = strtolower((string) $payment->status);
                                $isVerified = ($rawStatus === 'terverifikasi');
                                $isWaiting = ($rawStatus === 'menunggu');
                                $statusLabel = $isVerified ? 'Terverifikasi' : ($isWaiting ? 'Menunggu' : ucwords(str_replace('_', ' ', $rawStatus)));
                                $rowId = 'offline-' . $payment->id_pembayaran;
                            }

                            $fotoMobil = $car?->foto?->first()?->url() ?? $car?->image;
                            if (!$fotoMobil && $car) {
                                if (str_contains(strtolower($car->model ?? ''), 'supra')) {
                                    $fotoMobil = asset('assets/images/vehicles/toyota-gr-supra/black/front.png');
                                } else {
                                    $fotoMobil = asset('assets/images/hero/yza-hero-black-v2.png');
                                }
                            }
                        @endphp
                        <tr>
                            {{-- Kode Pembayaran --}}
                            <td class="td-code">
                                <button type="button" class="btn-open-payment-modal payment-code-link" data-open-modal="{{ $rowId }}" title="Lihat detail transaksi">
                                    {{ $code }}
                                </button>
                            </td>

                            {{-- Pelanggan / Member --}}
                            <td class="td-customer">
                                <strong class="customer-name">{{ $customerName }}</strong>
                                <span class="customer-subtext">{{ $customerCode }}</span>
                            </td>

                            {{-- Mobil --}}
                            <td class="td-car">
                                @if($car)
                                    <div class="car-cell">
                                        <img src="{{ $fotoMobil }}" alt="{{ $car->merk }} {{ $car->model }}" class="car-thumb" loading="lazy">
                                        <div class="car-meta">
                                            <strong class="car-title">{{ $car->merk }} {{ $car->model }}</strong>
                                            <span class="car-subtext">{{ $car->kode_mobil ?: ($car->nomor_polisi ?: ($car->tahun ?: '-')) }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="empty-dash" title="Bukan transaksi mobil">-</span>
                                @endif
                            </td>

                            {{-- Metode Pembayaran --}}
                            <td class="td-method">
                                <div class="method-badge">
                                    @if(str_contains(strtolower($methodType), 'tunai') || str_contains(strtolower($methodType), 'cash'))
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="12" x="2" y="6" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
                                    @elseif(str_contains(strtolower($methodType), 'transfer'))
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m16 3 4 4-4 4"></path><path d="M20 7H4"></path><path d="m8 21-4-4 4-4"></path><path d="M4 17h16"></path></svg>
                                    @else
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                    @endif
                                    <span>{{ $methodType }}</span>
                                </div>
                            </td>

                            {{-- Nominal --}}
                            <td class="td-amount">
                                <strong class="nominal-text">Rp {{ number_format($amount, 0, ',', '.') }}</strong>
                            </td>

                            {{-- Bukti --}}
                            <td class="td-proof">
                                @if(!$isOnline && !empty($payment->bukti_transfer))
                                    <button type="button" class="proof-pill has-file btn-open-payment-modal" data-open-modal="{{ $rowId }}" title="Lihat bukti pembayaran">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        <span>1 file</span>
                                    </button>
                                @else
                                    <span class="proof-pill empty">{{ $proofText }}</span>
                                @endif
                            </td>

                            {{-- Tanggal --}}
                            <td class="td-date">
                                <span>{{ $dateStr }}</span>
                            </td>

                            {{-- Status --}}
                            <td class="td-status" style="text-align: center;">
                                @if($isVerified)
                                    <span class="badge-status is-paid">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        <span>{{ $statusLabel }}</span>
                                    </span>
                                @elseif($isWaiting)
                                    <span class="badge-status is-waiting">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <span>{{ $statusLabel }}</span>
                                    </span>
                                @else
                                    <span class="badge-status is-cancelled">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        <span>{{ $statusLabel }}</span>
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="td-action">
                                <div class="action-cell">
                                    <button type="button" class="btn-detail-red btn-open-payment-modal" data-open-modal="{{ $rowId }}">
                                        <span>Detail</span>
                                    </button>

                                    <div class="action-dropdown-wrap" data-dropdown-wrap>
                                        <button type="button" class="btn-more-options" data-dropdown-toggle aria-label="Opsi lainnya" aria-expanded="false">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                                        </button>
                                        <div class="action-dropdown-menu" data-dropdown-menu hidden>
                                            <button type="button" class="dropdown-item btn-open-payment-modal" data-open-modal="{{ $rowId }}" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                                <span>Detail Pembayaran</span>
                                            </button>

                                            @if(!$isOnline && $payment->status === 'menunggu')
                                                <button type="button" class="dropdown-item is-approve" data-decision="confirm" data-id="{{ $payment->id_pembayaran }}" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left; color: #16A34A;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    <span>Konfirmasi Pembayaran</span>
                                                </button>
                                                <button type="button" class="dropdown-item is-reject" data-decision="reject" data-id="{{ $payment->id_pembayaran }}" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left; color: #DC2626;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                                                    <span>Tolak Pembayaran</span>
                                                </button>
                                            @endif

                                            @if($isOnline)
                                                <form method="post" action="{{ route('admin.payments.online.sync', $gateway) }}" style="margin: 0;">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item" style="width: 100%; border: 0; background: transparent; cursor: pointer; text-align: left;">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                                                        <span>Sync Status Midtrans</span>
                                                    </button>
                                                </form>
                                            @endif

                                            @if(!$isOnline && $payment->transaksi && Route::has('admin.invoice.car-sale'))
                                                <a href="{{ route('admin.invoice.car-sale', $payment->transaksi) }}" target="_blank" class="dropdown-item">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                                    <span>Lihat Invoice</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="table-empty-cell">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                    </div>
                                    <h3>Tidak Ada Transaksi Pembayaran</h3>
                                    <p>Belum ada data pembayaran {{ $tab === 'online' ? 'online' : 'offline' }} yang sesuai dengan kriteria filter Anda.</p>
                                    <a href="{{ route('admin.payments.index', ['tab' => $tab]) }}" class="btn-empty-reset">Reset Filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination & Per-Page --}}
        <div class="payment-table-footer">
            <div class="footer-info">
                Menampilkan <strong>{{ $items->firstItem() ?: 0 }}</strong> – <strong>{{ $items->lastItem() ?: 0 }}</strong> dari <strong>{{ $items->total() }}</strong> transaksi
            </div>

            <div class="footer-controls">
                <div class="pagination-links">
                    {{ $items->links('pagination::bootstrap-4') }}
                </div>

                {{-- Dropdown Per-Halaman --}}
                <form method="get" action="{{ route('admin.payments.index') }}" class="per-page-form">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="q" value="{{ $q }}">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="from" value="{{ $from }}">
                    <input type="hidden" name="to" value="{{ $to }}">
                    <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                        <option value="10" @selected($perPage == 10)>10 per halaman</option>
                        <option value="25" @selected($perPage == 25)>25 per halaman</option>
                        <option value="50" @selected($perPage == 50)>50 per halaman</option>
                    </select>
                </form>
            </div>
        </div>
    </main>

    {{-- Detail Pop-up Modals untuk Setiap Transaksi --}}
    @foreach($items as $row)
        @php
            $isOnline = ($tab === 'online');
            if ($isOnline) {
                $g = $row;
                $mCode = $g->order_id;
                $mCustName = $g->transaksi?->pelanggan?->nama_lengkap ?? $g->membership?->pelanggan?->nama_lengkap ?? 'Pelanggan Online';
                $mCustCode = $g->transaksi?->pelanggan?->kode_pelanggan ?? ($g->membership ? 'Member #M'.str_pad($g->membership->id_membership, 4, '0', STR_PAD_LEFT) : 'Member #Online');
                $mCustPhone = $g->transaksi?->pelanggan?->no_hp ?? '-';
                $mCar = $g->transaksi?->penjualanMobil?->mobil ?? $g->transaksi?->pesananMobilMember?->mobil;
                $mMethod = $g->payment_type ? ucwords(str_replace('_', ' ', $g->payment_type)) : 'Midtrans Snap Gateway';
                $mAmount = (float) $g->gross_amount;
                $mDate = $g->created_at?->translatedFormat('d F Y, H:i');
                $mRawStatus = strtolower((string) $g->status_provider);
                $mIsVerified = in_array($mRawStatus, ['settlement', 'capture']);
                $mIsWaiting = in_array($mRawStatus, ['pending', 'created']);
                $mStatusBadge = $mIsVerified ? 'Terverifikasi (Settlement)' : ($mIsWaiting ? 'Menunggu Pembayaran' : ucwords(str_replace('_', ' ', $mRawStatus)));
                $mModalId = 'online-' . $g->id_pembayaran_gateway;
                $mProof = null;
            } else {
                $p = $row;
                $mCode = $p->kode_pembayaran ?: ('PAY-'.str_pad($p->id_pembayaran, 6, '0', STR_PAD_LEFT));
                $mCustName = $p->transaksi?->pelanggan?->nama_lengkap ?: 'Pelanggan Test YZA';
                $mCustCode = $p->transaksi?->pelanggan?->kode_pelanggan ?: 'Member #M'.str_pad($p->transaksi?->id_pelanggan ?? 1, 4, '0', STR_PAD_LEFT);
                $mCustPhone = $p->transaksi?->pelanggan?->no_hp ?? '-';
                $mCar = $p->transaksi?->penjualanMobil?->mobil ?? $p->transaksi?->pesananMobilMember?->mobil;
                $mMethod = ucfirst((string) $p->metode);
                $mAmount = (float) $p->jumlah;
                $mDate = $p->tanggal ? $p->tanggal->translatedFormat('d F Y, H:i') : $p->created_at?->translatedFormat('d F Y, H:i');
                $mRawStatus = strtolower((string) $p->status);
                $mIsVerified = ($mRawStatus === 'terverifikasi');
                $mIsWaiting = ($mRawStatus === 'menunggu');
                $mStatusBadge = $mIsVerified ? 'Terverifikasi' : ($mIsWaiting ? 'Menunggu Konfirmasi' : ucwords(str_replace('_', ' ', $mRawStatus)));
                $mModalId = 'offline-' . $p->id_pembayaran;
                $mProof = $p->bukti_transfer;
            }

            $mFotoMobil = $mCar?->foto?->first()?->url() ?? $mCar?->image;
            if (!$mFotoMobil && $mCar) {
                if (str_contains(strtolower($mCar->model ?? ''), 'supra')) {
                    $mFotoMobil = asset('assets/images/vehicles/toyota-gr-supra/black/front.png');
                } else {
                    $mFotoMobil = asset('assets/images/hero/yza-hero-black-v2.png');
                }
            }
        @endphp

        <div class="admin-payment-modal" id="modal-{{ $mModalId }}" data-payment-modal hidden>
            <div class="admin-payment-backdrop" data-close-payment-modal></div>
            <div class="admin-payment-dialog" role="dialog" aria-modal="true" aria-labelledby="modal-title-{{ $mModalId }}">
                <div class="modal-top-accent-bar"></div>

                {{-- Modal Header --}}
                <div class="modal-header-section">
                    <div>
                        <div class="modal-badge-group">
                            <span class="modal-brand-badge">YZA FINANCIAL</span>
                            <span class="modal-order-number">#{{ $mCode }}</span>
                        </div>
                        <h2 id="modal-title-{{ $mModalId }}" class="modal-main-title">Detail Transaksi Pembayaran</h2>
                        <div class="modal-timestamp">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Tercatat pada {{ $mDate }} WIB</span>
                        </div>
                    </div>
                    <button type="button" class="modal-close-btn" data-close-payment-modal aria-label="Tutup detail" title="Tutup (Esc)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>

                {{-- Summary Ribbon --}}
                <div class="modal-summary-ribbon">
                    <div class="ribbon-status-group">
                        <span class="ribbon-label">Status Verifikasi:</span>
                        @if($mIsVerified)
                            <span class="badge-status is-paid">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>{{ $mStatusBadge }}</span>
                            </span>
                        @elseif($mIsWaiting)
                            <span class="badge-status is-waiting">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <span>{{ $mStatusBadge }}</span>
                            </span>
                        @else
                            <span class="badge-status is-cancelled">
                                <span>{{ $mStatusBadge }}</span>
                            </span>
                        @endif
                    </div>
                    <div class="ribbon-price-card">
                        <span class="price-card-label">NOMINAL PEMBAYARAN</span>
                        <span class="price-card-value">Rp {{ number_format($mAmount, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Body Columns --}}
                <div class="modal-scrollable-body">
                    <div class="modal-detail-columns">
                        {{-- Kolom Kiri: Pelanggan & Kendaraan --}}
                        <div class="modal-col">
                            <div class="card-section-head">
                                <div class="section-head-icon is-blue">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                                <div>
                                    <h3 class="section-head-title">Data Pembayar</h3>
                                    <p class="section-head-sub">Informasi member dan kendaraan terkait</p>
                                </div>
                            </div>

                            <div class="customer-profile-card">
                                <div class="customer-avatar-circle">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                </div>
                                <div>
                                    <h4 class="customer-full-name">{{ $mCustName }}</h4>
                                    <span class="customer-id-tag">{{ $mCustCode }}</span>
                                </div>
                            </div>

                            @if($mCar)
                                <div class="vehicle-mini-showcase">
                                    <img src="{{ $mFotoMobil }}" alt="{{ $mCar->merk }} {{ $mCar->model }}" class="vehicle-mini-img">
                                    <div class="vehicle-mini-info">
                                        <strong>{{ $mCar->merk }} {{ $mCar->model }} {{ $mCar->tahun ?: '' }}</strong>
                                        <small>{{ $mCar->tipe ?: 'Unit Kendaraan' }} &bull; {{ $mCar->nomor_polisi ?: $mCar->kode_mobil }}</small>
                                    </div>
                                </div>
                            @else
                                <div class="vehicle-mini-showcase is-non-car" style="background: #F8FAFC; border: 1px dashed #CBD5E1; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 10px; margin-top: 14px;">
                                    <span style="font-size: 18px; font-weight: 800; color: #94A3B8; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; background: #E2E8F0; border-radius: 50%;">-</span>
                                    <div>
                                        <strong style="display: block; font-size: 13px; color: #475569;">Bukan Pembayaran Unit Mobil</strong>
                                        <small style="font-size: 11px; color: #94A3B8;">Aktivasi membership / layanan operasional</small>
                                    </div>
                                </div>
                            @endif

                            @if(!$isOnline && !empty($mProof))
                                <div class="proof-preview-box">
                                    <span class="proof-label">Bukti Transfer / Dokumen:</span>
                                    <a href="{{ asset('storage/' . $mProof) }}" target="_blank" class="proof-link-card">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                        <span>Buka Dokumen Bukti (Klik untuk Melihat)</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kolom Kanan: Rincian Keuangan --}}
                        <div class="modal-col">
                            <div class="card-section-head">
                                <div class="section-head-icon is-green">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                                </div>
                                <div>
                                    <h3 class="section-head-title">Detail Finansial</h3>
                                    <p class="section-head-sub">Metode, rekening, dan catatan transaksi</p>
                                </div>
                            </div>

                            <div class="spec-grid-layout">
                                <div class="spec-tile">
                                    <span class="spec-label">Metode Pembayaran</span>
                                    <strong class="spec-value">{{ $mMethod }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Jenis Saluran</span>
                                    <strong class="spec-value">{{ $isOnline ? 'Online (Midtrans Payment)' : 'Offline (Kasir / Bank)' }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Nominal Transaksi</span>
                                    <strong class="spec-value text-red">Rp {{ number_format($mAmount, 0, ',', '.') }}</strong>
                                </div>
                                <div class="spec-tile">
                                    <span class="spec-label">Waktu Pembayaran</span>
                                    <strong class="spec-value">{{ $mDate }}</strong>
                                </div>
                                @if(!$isOnline && !empty($p->receipt_reference))
                                    <div class="spec-tile is-wide">
                                        <span class="spec-label">No. Referensi Kwitansi</span>
                                        <strong class="spec-value font-mono">{{ $p->receipt_reference }}</strong>
                                    </div>
                                @endif
                                @if(!$isOnline && !empty($p->catatan))
                                    <div class="spec-tile is-wide">
                                        <span class="spec-label">Catatan Admin</span>
                                        <strong class="spec-value">{{ $p->catatan }}</strong>
                                    </div>
                                @endif
                                @if($isOnline && !empty($g->provider_transaction_id))
                                    <div class="spec-tile is-wide">
                                        <span class="spec-label">Midtrans Transaction ID</span>
                                        <strong class="spec-value font-mono">{{ $g->provider_transaction_id }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="modal-footer-section">
                    <div class="footer-left">
                        @if(!$isOnline && $p->transaksi && Route::has('admin.invoice.car-sale'))
                            <a href="{{ route('admin.invoice.car-sale', $p->transaksi) }}" target="_blank" class="btn-invoice-link">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                                <span>Buka Invoice Penjualan</span>
                            </a>
                        @endif
                    </div>

                    <div class="footer-right">
                        @if(!$isOnline && $p->status === 'menunggu')
                            <button type="button" class="btn-modal-action is-reject" data-decision="reject" data-id="{{ $p->id_pembayaran }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                <span>Tolak</span>
                            </button>
                            <button type="button" class="btn-modal-action is-confirm" data-decision="confirm" data-id="{{ $p->id_pembayaran }}">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <span>Konfirmasi Dana Diterima</span>
                            </button>
                        @endif
                        <button type="button" class="btn-modal-action is-close" data-close-payment-modal>
                            <span>Tutup</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Konfirmasi / Penolakan Pembayaran Offline --}}
    <div class="admin-decision-modal" id="decision-modal" hidden>
        <div class="admin-decision-backdrop" data-close-decision-modal></div>
        <div class="admin-decision-dialog" role="dialog" aria-modal="true" aria-labelledby="decision-modal-title">
            <div class="decision-icon-badge" id="decision-badge">!</div>
            <h3 id="decision-modal-title">Konfirmasi Status Pembayaran</h3>
            <p id="decision-modal-desc">Pastikan transaksi keuangan telah diperiksa dengan seksama.</p>

            {{-- Form Konfirmasi --}}
            <form id="form-confirm-payment" method="post" action="" style="display: none; margin-top: 16px;">
                @csrf
                <div class="form-group-modal">
                    <label for="receipt_ref_input">No. Referensi Penerimaan / Kwitansi (Opsional):</label>
                    <input type="text" id="receipt_ref_input" name="receipt_reference" placeholder="Contoh: KWT-2026-001" maxlength="100">
                </div>
                <div class="modal-decision-actions">
                    <button type="button" class="btn-cancel" data-close-decision-modal>Batal</button>
                    <button type="submit" class="btn-submit-confirm">Ya, Konfirmasi Dana</button>
                </div>
            </form>

            {{-- Form Penolakan --}}
            <form id="form-reject-payment" method="post" action="" style="display: none; margin-top: 16px;">
                @csrf
                <div class="form-group-modal">
                    <label for="reject_reason_input">Alasan Penolakan (Wajib Diisi):</label>
                    <textarea id="reject_reason_input" name="rejection_reason" rows="3" placeholder="Tuliskan alasan penolakan pembayaran..." required maxlength="1000"></textarea>
                </div>
                <div class="modal-decision-actions">
                    <button type="button" class="btn-cancel" data-close-decision-modal>Batal</button>
                    <button type="submit" class="btn-submit-reject">Tolak Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ========================================================
   YZA AUTOMOTIVE — PREMIUM ADMIN PAYMENT MANAGEMENT STYLES
   ======================================================== */
.admin-payment-page {
    display: flex;
    flex-direction: column;
    gap: 22px;
    width: 100%;
}

/* Header & Banner */
.admin-payment-header {
    display: flex;
    align-items: stretch;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}
.admin-payment-header-left {
    flex: 1 1 380px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.admin-payment-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #64748B;
    margin-bottom: 12px;
}
.admin-payment-breadcrumb a {
    color: #64748B;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: color 0.15s ease;
}
.admin-payment-breadcrumb a:hover {
    color: #ED172B;
}
.breadcrumb-separator {
    color: #CBD5E1;
}
.breadcrumb-current {
    color: #0F172A;
    font-weight: 700;
}
.admin-payment-heading {
    margin: 0;
    font-size: 30px;
    font-weight: 800;
    color: #0F172A;
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.admin-payment-subheading {
    margin: 8px 0 0;
    font-size: 13.5px;
    color: #64748B;
    line-height: 1.5;
    max-width: 620px;
}

/* Header Banner */
.admin-payment-header-right {
    flex: 0 0 380px;
    min-width: 320px;
}
@media (max-width: 900px) {
    .admin-payment-header-right {
        flex: 1 1 100%;
    }
}
.admin-payment-banner {
    position: relative;
    height: 108px;
    border-radius: 16px;
    overflow: hidden;
    background: #0B1018;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
}
.payment-banner-img {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    width: 65%;
    object-fit: cover;
    object-position: right center;
    opacity: 0.88;
}
.payment-banner-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #0B1018 35%, rgba(11, 16, 24, 0.7) 65%, transparent 100%);
}
.payment-banner-content {
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
.admin-alert.is-error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #991B1B;
}

/* ========================================================
   ENAM STATISTIK PREMIUM (6 KPI CARDS)
   ======================================================== */
.payment-kpi-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 14px;
}
@media (max-width: 1400px) {
    .payment-kpi-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (max-width: 768px) {
    .payment-kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 480px) {
    .payment-kpi-grid {
        grid-template-columns: 1fr;
    }
}

.payment-kpi-card {
    border-radius: 16px;
    padding: 16px 16px 14px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid transparent;
}
.payment-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Card Themes */
.payment-kpi-card.is-green {
    background: #ECFDF5;
    border-color: #D1FAE5;
}
.payment-kpi-card.is-green .kpi-icon-box {
    background: #D1FAE5;
    color: #059669;
}
.payment-kpi-card.is-blue {
    background: #EFF6FF;
    border-color: #DBEAFE;
}
.payment-kpi-card.is-blue .kpi-icon-box {
    background: #DBEAFE;
    color: #2563EB;
}
.payment-kpi-card.is-amber {
    background: #FFFBEB;
    border-color: #FEF3C7;
}
.payment-kpi-card.is-amber .kpi-icon-box {
    background: #FEF3C7;
    color: #D97706;
}
.payment-kpi-card.is-red {
    background: #FFF1F2;
    border-color: #FFE4E6;
}
.payment-kpi-card.is-red .kpi-icon-box {
    background: #FEE2E2;
    color: #E11D48;
}
.payment-kpi-card.is-emerald {
    background: #F0FDF4;
    border-color: #DCFCE7;
}
.payment-kpi-card.is-emerald .kpi-icon-box {
    background: #DCFCE7;
    color: #16A34A;
}
.payment-kpi-card.is-purple {
    background: #FAF5FF;
    border-color: #F3E8FF;
}
.payment-kpi-card.is-purple .kpi-icon-box {
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
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.kpi-value {
    font-size: 20px;
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
.payment-kpi-card.is-purple .kpi-subtext.is-up {
    color: #9333EA;
}

/* ========================================================
   MAIN PAYMENT CARD CONTAINER
   ======================================================== */
.payment-main-card {
    background: #FFFFFF;
    border-radius: 20px;
    border: 1px solid #E2E8F0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Tab Bar */
.payment-tabs-bar {
    display: flex;
    align-items: center;
    gap: 12px;
}
.payment-tab-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.18s ease;
    border: 1px solid transparent;
}
.payment-tab-item.is-active {
    background: #ED172B;
    color: #FFFFFF;
    box-shadow: 0 4px 14px rgba(237, 23, 43, 0.28);
}
.payment-tab-item:not(.is-active) {
    background: #F8FAFC;
    color: #475569;
    border-color: #E2E8F0;
}
.payment-tab-item:not(.is-active):hover {
    background: #F1F5F9;
    color: #0F172A;
    border-color: #CBD5E1;
}

/* Filter Toolbar */
.payment-filter-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.filter-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.filter-input-wrap.search-box {
    flex: 1 1 280px;
}
.filter-input-wrap.search-box svg {
    position: absolute;
    left: 14px;
    color: #94A3B8;
    pointer-events: none;
}
.filter-input-wrap.search-box .form-input {
    padding-left: 38px;
}
.filter-input-wrap.select-box {
    flex: 0 0 170px;
}
.select-chevron {
    position: absolute;
    right: 12px;
    color: #94A3B8;
    pointer-events: none;
}
.filter-input-wrap.date-box {
    flex: 0 0 145px;
}
.form-input, .form-select {
    width: 100%;
    height: 42px;
    padding: 0 14px;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    background: #FFFFFF;
    color: #0F172A;
    font-family: inherit;
    font-size: 13px;
    box-sizing: border-box;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.form-select {
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    padding-right: 30px;
}
.form-input:focus, .form-select:focus {
    outline: none;
    border-color: #ED172B;
    box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.1);
}
.filter-actions-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-filter-apply {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 42px;
    padding: 0 20px;
    border-radius: 10px;
    background: #ED172B;
    color: #FFFFFF;
    border: 0;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(237, 23, 43, 0.25);
    transition: background 0.15s ease;
}
.btn-filter-apply:hover {
    background: #D91325;
}
.btn-filter-reset {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 42px;
    padding: 0 16px;
    border-radius: 10px;
    background: #FFFFFF;
    color: #475569;
    border: 1px solid #E2E8F0;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-filter-reset:hover {
    background: #F8FAFC;
    color: #0F172A;
    border-color: #CBD5E1;
}

/* ========================================================
   TABLE STYLING
   ======================================================== */
.payment-table-responsive {
    overflow-x: auto;
    border-radius: 14px;
    border: 1px solid #F1F5F9;
}
.payment-table {
    width: 100%;
    min-width: 1020px;
    border-collapse: separate;
    border-spacing: 0;
}
.payment-table thead tr th {
    background: #F8FAFC;
    color: #475569;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.05em;
    padding: 14px 16px;
    border-bottom: 1px solid #E2E8F0;
    text-align: left;
}
.th-sortable {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}
.payment-table tbody tr {
    transition: background 0.15s ease;
}
.payment-table tbody tr:hover {
    background: #F8FAFC;
}
.payment-table tbody tr td {
    padding: 14px 16px;
    border-bottom: 1px solid #F1F5F9;
    vertical-align: middle;
    font-size: 13px;
    color: #334155;
}

/* Table Cells */
.payment-code-link {
    background: transparent;
    border: 0;
    padding: 0;
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
    cursor: pointer;
    text-align: left;
}
.payment-code-link:hover {
    color: #ED172B;
    text-decoration: underline;
}
.customer-name {
    display: block;
    color: #0F172A;
    font-size: 13.5px;
    font-weight: 700;
}
.customer-subtext {
    display: block;
    margin-top: 2px;
    font-size: 11.5px;
    color: #64748B;
}
.car-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.car-thumb {
    width: 52px;
    height: 34px;
    border-radius: 6px;
    object-fit: contain;
    background: #F1F5F9;
    border: 1px solid #E2E8F0;
    flex-shrink: 0;
}
.car-meta {
    display: flex;
    flex-direction: column;
}
.car-title {
    color: #0F172A;
    font-size: 13px;
    font-weight: 700;
}
.car-subtext {
    font-size: 11px;
    color: #64748B;
}
.empty-dash {
    display: inline-block;
    color: #94A3B8;
    font-weight: 700;
    font-size: 15px;
    line-height: 1;
}
.method-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: #1E293B;
}
.nominal-text {
    color: #0F172A;
    font-size: 13.5px;
    font-weight: 800;
}
.proof-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    border-radius: 6px;
}
.proof-pill.has-file {
    padding: 4px 10px;
    background: #F1F5F9;
    color: #2563EB;
    border: 1px solid #DBEAFE;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
}
.proof-pill.has-file:hover {
    background: #EFF6FF;
    border-color: #93C5FD;
}
.proof-pill.empty {
    color: #94A3B8;
    font-style: normal;
}

/* Badges Status */
.badge-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 700;
}
.badge-status.is-paid {
    background: #DCFCE7;
    color: #15803D;
    border: 1px solid #BBF7D0;
}
.badge-status.is-waiting {
    background: #FEF3C7;
    color: #D97706;
    border: 1px solid #FDE68A;
}
.badge-status.is-cancelled {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FECACA;
}

/* Actions in Table */
.action-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-detail-red {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 14px;
    border-radius: 8px;
    background: #ED172B;
    color: #FFFFFF;
    border: 0;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(237, 23, 43, 0.25);
    transition: background 0.15s ease;
}
.btn-detail-red:hover {
    background: #D91325;
}
.action-dropdown-wrap {
    position: relative;
}
.btn-more-options {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    color: #64748B;
    display: grid;
    place-items: center;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-more-options:hover {
    background: #F8FAFC;
    color: #0F172A;
    border-color: #CBD5E1;
}
.action-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    z-index: 50;
    min-width: 190px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 6px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.action-dropdown-menu[hidden] {
    display: none !important;
}
.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: background 0.15s ease;
}
.dropdown-item:hover {
    background: #F8FAFC;
    color: #0F172A;
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
}
.empty-state-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #F1F5F9;
    color: #94A3B8;
    display: grid;
    place-items: center;
    margin-bottom: 12px;
}
.empty-state h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    color: #0F172A;
}
.empty-state p {
    margin: 6px 0 16px;
    font-size: 13px;
    color: #64748B;
}
.btn-empty-reset {
    display: inline-flex;
    padding: 8px 16px;
    border-radius: 8px;
    background: #ED172B;
    color: #FFFFFF;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
}

/* Footer Pagination & Per Page */
.payment-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding-top: 8px;
}
.footer-info {
    font-size: 12.5px;
    color: #64748B;
}
.footer-controls {
    display: flex;
    align-items: center;
    gap: 16px;
}
.per-page-select {
    height: 34px;
    padding: 0 10px;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: #FFFFFF;
    color: #334155;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.pagination-links .pagination {
    margin: 0;
    display: flex;
    gap: 4px;
    list-style: none;
    padding: 0;
}
.pagination-links .page-item .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border-radius: 8px;
    border: 1px solid #E2E8F0;
    background: #FFFFFF;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}
.pagination-links .page-item.active .page-link {
    background: #ED172B;
    border-color: #ED172B;
    color: #FFFFFF;
}
.pagination-links .page-item.disabled .page-link {
    opacity: 0.5;
    pointer-events: none;
}

/* ========================================================
   POP-UP DETAIL MODAL (LUXURY YZA STYLING)
   ======================================================== */
.admin-payment-modal, .admin-decision-modal {
    position: fixed;
    inset: 0;
    z-index: 120;
    display: grid;
    place-items: center;
    padding: 20px;
    box-sizing: border-box;
}
.admin-decision-modal {
    z-index: 200;
}
.admin-payment-modal[hidden], .admin-decision-modal[hidden] {
    display: none !important;
}
.admin-payment-backdrop, .admin-decision-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(11, 16, 24, 0.75);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
.admin-payment-dialog {
    position: relative;
    width: min(880px, calc(100vw - 32px));
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
.modal-header-section {
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
.modal-close-btn {
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
.modal-close-btn:hover {
    background: #FEE2E2;
    color: #DC2626;
    border-color: #FECACA;
    transform: rotate(90deg) scale(1.06);
}

/* Modal Ribbon */
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
.ribbon-status-group {
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

/* Modal Scrollable Body */
.modal-scrollable-body {
    padding: 24px 28px;
    overflow-y: auto;
    max-height: calc(85vh - 210px);
}
.modal-detail-columns {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 24px;
}
@media (max-width: 768px) {
    .modal-detail-columns {
        grid-template-columns: 1fr;
    }
}
.modal-col {
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
.section-head-icon.is-blue { background: #EFF6FF; color: #2563EB; }
.section-head-icon.is-green { background: #ECFDF5; color: #059669; }

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

.customer-profile-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
}
.customer-avatar-circle {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #EFF6FF;
    color: #2563EB;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.customer-full-name {
    margin: 0;
    font-size: 14.5px;
    font-weight: 800;
    color: #0F172A;
}
.customer-id-tag {
    display: block;
    margin-top: 2px;
    font-size: 11.5px;
    color: #64748B;
}

.vehicle-mini-showcase {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 12px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}
.vehicle-mini-img {
    width: 60px;
    height: 40px;
    object-fit: contain;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
}
.vehicle-mini-info strong {
    display: block;
    font-size: 13px;
    color: #0F172A;
}
.vehicle-mini-info small {
    display: block;
    color: #64748B;
    font-size: 11.5px;
}

.proof-preview-box {
    padding: 12px 14px;
    border-radius: 12px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}
.proof-label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    color: #64748B;
    margin-bottom: 6px;
}
.proof-link-card {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #2563EB;
    font-weight: 700;
    font-size: 12.5px;
    text-decoration: none;
}
.proof-link-card:hover {
    text-decoration: underline;
}

.spec-grid-layout {
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
    display: block;
    margin-top: 3px;
    font-size: 13px;
    font-weight: 700;
    color: #0F172A;
    word-break: break-word;
}
.spec-value.text-red {
    color: #ED172B;
    font-weight: 800;
}
.font-mono {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
}

/* Modal Footer */
.modal-footer-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 28px;
    background: #F8FAFC;
    border-top: 1px solid #E2E8F0;
    flex-shrink: 0;
}
.footer-left, .footer-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
.btn-invoice-link {
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
.btn-invoice-link:hover {
    background: #F1F5F9;
}
.btn-modal-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 18px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    border: 0;
    transition: all 0.15s ease;
}
.btn-modal-action.is-close {
    background: #FFFFFF;
    color: #334155;
    border: 1px solid #CBD5E1;
}
.btn-modal-action.is-close:hover {
    background: #F1F5F9;
    color: #0F172A;
}
.btn-modal-action.is-confirm {
    background: #16A34A;
    color: #FFFFFF;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
}
.btn-modal-action.is-confirm:hover {
    background: #15803D;
}
.btn-modal-action.is-reject {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
}
.btn-modal-action.is-reject:hover {
    background: #FCA5A5;
}

/* ========================================================
   DECISION CONFIRM / REJECT MODAL
   ======================================================== */
.admin-decision-dialog {
    position: relative;
    width: min(500px, calc(100vw - 32px));
    padding: 32px;
    border-radius: 20px;
    background: #FFFFFF;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    text-align: center;
}
.decision-icon-badge {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto 16px;
    display: grid;
    place-items: center;
    font-size: 28px;
    font-weight: 900;
}
.decision-icon-badge.is-confirm {
    background: #DCFCE7;
    color: #16A34A;
}
.decision-icon-badge.is-reject {
    background: #FEE2E2;
    color: #DC2626;
}
.admin-decision-dialog h3 {
    margin: 0 0 8px;
    font-size: 22px;
    font-weight: 800;
    color: #0F172A;
}
.admin-decision-dialog p {
    margin: 0;
    font-size: 13.5px;
    color: #64748B;
    line-height: 1.5;
}
.form-group-modal {
    text-align: left;
    margin-bottom: 20px;
}
.form-group-modal label {
    display: block;
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
}
.form-group-modal input, .form-group-modal textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    font-size: 13px;
    font-family: inherit;
    box-sizing: border-box;
}
.form-group-modal input:focus, .form-group-modal textarea:focus {
    outline: none;
    border-color: #ED172B;
}
.modal-decision-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
}
.btn-cancel {
    padding: 10px 20px;
    border-radius: 9px;
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}
.btn-submit-confirm {
    padding: 10px 22px;
    border-radius: 9px;
    border: 0;
    background: #16A34A;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
}
.btn-submit-reject {
    padding: 10px 22px;
    border-radius: 9px;
    border: 0;
    background: #DC2626;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
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

    // Modal Detail Pop-up Handling
    var closeAllPaymentModals = function () {
        document.querySelectorAll('.admin-payment-modal').forEach(function (m) {
            m.setAttribute('hidden', '');
        });
        document.body.style.overflow = '';
    };

    document.querySelectorAll('.btn-open-payment-modal').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Close any open dropdowns
            document.querySelectorAll('[data-dropdown-menu]').forEach(function (m) {
                m.setAttribute('hidden', '');
            });
            var targetId = this.getAttribute('data-open-modal');
            var targetModal = document.getElementById('modal-' + targetId);
            if (targetModal) {
                closeAllPaymentModals();
                targetModal.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    document.querySelectorAll('[data-close-payment-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            closeAllPaymentModals();
        });
    });

    // Decision Confirm / Reject Modal Handling
    var decisionModal = document.getElementById('decision-modal');
    var formConfirm = document.getElementById('form-confirm-payment');
    var formReject = document.getElementById('form-reject-payment');
    var decisionTitle = document.getElementById('decision-modal-title');
    var decisionDesc = document.getElementById('decision-modal-desc');
    var decisionBadge = document.getElementById('decision-badge');

    var closeDecision = function () {
        if (decisionModal) decisionModal.setAttribute('hidden', '');
        if (formConfirm) formConfirm.style.display = 'none';
        if (formReject) formReject.style.display = 'none';
        var hasPaymentModal = document.querySelector('.admin-payment-modal:not([hidden])');
        if (!hasPaymentModal) {
            document.body.style.overflow = '';
        }
    };

    document.querySelectorAll('[data-decision]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var actionType = this.getAttribute('data-decision');
            var paymentId = this.getAttribute('data-id');

            if (actionType === 'confirm') {
                decisionTitle.textContent = 'Konfirmasi Pembayaran';
                decisionDesc.textContent = 'Apakah Anda yakin ingin memverifikasi penerimaan dana untuk pembayaran #' + paymentId + '?';
                decisionBadge.textContent = '✓';
                decisionBadge.className = 'decision-icon-badge is-confirm';
                formConfirm.action = '{{ url("admin/pembayaran") }}/' + paymentId + '/confirm';
                formConfirm.style.display = 'block';
                formReject.style.display = 'none';
            } else if (actionType === 'reject') {
                decisionTitle.textContent = 'Tolak Pembayaran';
                decisionDesc.textContent = 'Apakah Anda yakin ingin menolak pembayaran #' + paymentId + '? Berikan alasan penolakan.';
                decisionBadge.textContent = '✕';
                decisionBadge.className = 'decision-icon-badge is-reject';
                formReject.action = '{{ url("admin/pembayaran") }}/' + paymentId + '/reject';
                formReject.style.display = 'block';
                formConfirm.style.display = 'none';
            }

            if (decisionModal) {
                decisionModal.removeAttribute('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    document.querySelectorAll('[data-close-decision-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            closeDecision();
        });
    });

    // Escape Key Handler
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (decisionModal && !decisionModal.hasAttribute('hidden')) {
                closeDecision();
            } else {
                closeAllPaymentModals();
            }
        }
    });
});
</script>
@endpush
