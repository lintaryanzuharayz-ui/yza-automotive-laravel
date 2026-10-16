@extends('layouts.app')

@section('title', 'Master Sparepart & Inventori · Admin YZA Automotive')
@section('admin-page-title', 'Sparepart & Inventori')

@section('content')
<div class="admin-sparepart-page">
    <style>
        .admin-sparepart-page {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Header */
        .sparepart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .sparepart-header-left {
            flex: 1 1 380px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .sparepart-breadcrumb {
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
        .sparepart-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .sparepart-title-icon {
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
        .sparepart-heading {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .sparepart-subheading {
            margin: 6px 0 0;
            font-size: 13.5px;
            color: #64748B;
            line-height: 1.5;
            max-width: 620px;
        }

        /* Header Banner */
        .sparepart-header-right {
            flex: 0 0 380px;
            min-width: 320px;
        }
        @media (max-width: 900px) {
            .sparepart-header-right { flex: 1 1 100%; }
        }
        .sparepart-banner {
            position: relative;
            height: 108px;
            border-radius: 16px;
            overflow: hidden;
            background: #0B1018;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
        }
        .sparepart-banner-img {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 65%;
            object-fit: cover;
            object-position: right center;
            opacity: 0.88;
        }
        .sparepart-banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #0B1018 35%, rgba(11, 16, 24, 0.7) 65%, transparent 100%);
        }
        .sparepart-banner-content {
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

        /* Alerts */
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

        /* Enam Kartu Statistik Inventori (6 KPI Cards) */
        .sparepart-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
        }
        @media (max-width: 1400px) { .sparepart-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 768px) { .sparepart-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 480px) { .sparepart-kpi-grid { grid-template-columns: 1fr; } }

        .sparepart-kpi-card {
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
        .sparepart-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }
        .sparepart-kpi-card.is-red { background: #FFF1F2; border-color: #FFE4E6; }
        .sparepart-kpi-card.is-red .kpi-icon-box { background: #FEE2E2; color: #E11D48; }
        .sparepart-kpi-card.is-green { background: #ECFDF5; border-color: #D1FAE5; }
        .sparepart-kpi-card.is-green .kpi-icon-box { background: #D1FAE5; color: #059669; }
        .sparepart-kpi-card.is-amber { background: #FFFBEB; border-color: #FEF3C7; }
        .sparepart-kpi-card.is-amber .kpi-icon-box { background: #FEF3C7; color: #D97706; }
        .sparepart-kpi-card.is-blue { background: #EFF6FF; border-color: #DBEAFE; }
        .sparepart-kpi-card.is-blue .kpi-icon-box { background: #DBEAFE; color: #2563EB; }
        .sparepart-kpi-card.is-purple { background: #FAF5FF; border-color: #F3E8FF; }
        .sparepart-kpi-card.is-purple .kpi-icon-box { background: #F3E8FF; color: #9333EA; }
        .sparepart-kpi-card.is-dark { background: #F8FAFC; border-color: #E2E8F0; }
        .sparepart-kpi-card.is-dark .kpi-icon-box { background: #E2E8F0; color: #475569; }

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
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .kpi-value {
            font-size: 22px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .kpi-subtext {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 600;
            color: #64748B;
            margin-top: 6px;
        }

        /* Container Card */
        .sparepart-main-card {
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sparepart-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
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
        }
        .card-header-subtitle {
            margin: 3px 0 0;
            font-size: 13.5px;
            color: #64748B;
        }
        .header-action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn-action-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            background: #ED172B;
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(237, 23, 43, 0.25);
            transition: all 0.15s ease;
            border: none;
            cursor: pointer;
        }
        .btn-action-primary:hover {
            background: #D91325;
            color: #FFFFFF;
            transform: translateY(-1px);
        }
        .btn-action-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 40px;
            padding: 0 15px;
            border-radius: 10px;
            background: #F1F5F9;
            color: #1E293B;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid #E2E8F0;
            transition: all 0.15s ease;
        }
        .btn-action-secondary:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        /* Filter Toolbar */
        .sparepart-filter-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
            width: 100%;
        }
        @media (max-width: 1024px) {
            .sparepart-filter-toolbar { flex-wrap: wrap; }
        }
        .filter-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .filter-wrap.search-box {
            flex: 2 1 200px;
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
        .filter-wrap.select-box {
            flex: 1.2 1 130px;
            min-width: 120px;
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
            font-size: 13px;
            box-sizing: border-box;
            appearance: none;
        }
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #ED172B;
            box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.12);
        }
        .btn-filter-submit {
            height: 40px;
            padding: 0 18px;
            border-radius: 9px;
            background: #0F172A;
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            flex-shrink: 0;
            transition: background 0.15s ease;
        }
        .btn-filter-submit:hover { background: #1E293B; }
        .btn-filter-reset {
            height: 40px;
            padding: 0 14px;
            border-radius: 9px;
            background: #F1F5F9;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #E2E8F0;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
        }
        .btn-filter-reset:hover { background: #E2E8F0; color: #0F172A; }

        /* Table Wrapper */
        .sparepart-table-wrapper {
            overflow-x: auto;
            border: 1px solid #F1F5F9;
            border-radius: 14px;
        }
        .sparepart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            text-align: left;
        }
        .sparepart-table thead tr th {
            background: #F8FAFC;
            color: #475569;
            font-weight: 700;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 13px 14px;
            border-bottom: 1px solid #E2E8F0;
            white-space: nowrap;
        }
        .sparepart-table tbody tr {
            transition: background 0.15s ease;
        }
        .sparepart-table tbody tr:hover {
            background: #F8FAFC;
        }
        .sparepart-table tbody tr td {
            padding: 14px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #1E293B;
        }
        .sparepart-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Thumb Image */
        .part-thumb-wrap {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            overflow: hidden;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .part-thumb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Badges */
        .badge-sku {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            color: #0F172A;
            background: #F1F5F9;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
        }
        .badge-category {
            font-size: 11.5px;
            font-weight: 700;
            color: #4338CA;
            background: #EEF2FF;
            padding: 3px 9px;
            border-radius: 999px;
            display: inline-block;
        }
        .badge-status {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
        }
        .badge-status.is-active { background: #ECFDF5; color: #047857; }
        .badge-status.is-inactive { background: #F1F5F9; color: #64748B; }

        .stock-indicator {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .stock-num {
            font-weight: 800;
            font-size: 13.5px;
            color: #0F172A;
        }
        .stock-tag {
            font-size: 10.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            width: fit-content;
        }
        .stock-tag.is-aman { background: #ECFDF5; color: #047857; }
        .stock-tag.is-menipis { background: #FFFBEB; color: #B45309; }
        .stock-tag.is-habis { background: #FEF2F2; color: #B91C1C; }

        /* Action Buttons */
        .table-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-table-action {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            color: #334155;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-table-action:hover {
            background: #F8FAFC;
            color: #0F172A;
            border-color: #CBD5E1;
        }
        .btn-table-action.is-danger {
            color: #DC2626;
            border-color: #FECACA;
        }
        .btn-table-action.is-danger:hover {
            background: #FEF2F2;
            color: #B91C1C;
        }
    </style>

    {{-- Header --}}
    <header class="sparepart-header">
        <div class="sparepart-header-left">
            <nav class="sparepart-breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-back-arrow" aria-hidden="true">&lsaquo;</span>
                <span class="breadcrumb-item">Layanan & Operasional</span>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current">Sparepart & Inventori</span>
            </nav>

            <div class="sparepart-title-group">
                <div class="sparepart-title-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"><polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <div>
                    <h1 class="sparepart-heading">Master Sparepart & Inventori</h1>
                    <p class="sparepart-subheading">Kelola katalog suku cadang, pantau ketersediaan stok fisik, penerimaan barang, dan integrasi dengan work order bengkel.</p>
                </div>
            </div>
        </div>

        <div class="sparepart-header-right">
            <div class="sparepart-banner">
                <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="YZA Workshop & Spareparts" class="sparepart-banner-img" loading="eager">
                <div class="sparepart-banner-overlay"></div>
                <div class="sparepart-banner-content">
                    <span class="banner-tagline-top">INVENTORI BENGKEL RESMI</span>
                    <span class="banner-tagline-bottom">SUKU CADANG & PERLENGKAPAN</span>
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

    {{-- 6 KPI Cards --}}
    <section class="sparepart-kpi-grid" aria-label="Statistik Sparepart & Stok">
        {{-- Card 1: Total Jenis Sparepart --}}
        <a class="sparepart-kpi-card is-red" href="{{ route('admin.sparepart.index') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Jenis Sparepart</span>
                <strong class="kpi-value">{{ number_format($stats['total_jenis'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Katalog suku cadang</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Total Stok Tersedia --}}
        <a class="sparepart-kpi-card is-green" href="{{ route('admin.inventory.stock') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Stok Fisik</span>
                <strong class="kpi-value">{{ number_format($stats['total_stok'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Unit barang siap pakai</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Stok Menipis --}}
        <a class="sparepart-kpi-card is-amber" href="{{ route('admin.sparepart.index', ['stok' => 'menipis']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Stok Menipis</span>
                <strong class="kpi-value">{{ number_format($stats['stok_menipis'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #D97706;">
                    <span>&le; batas minimum stok</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Stok Habis --}}
        <a class="sparepart-kpi-card is-red" href="{{ route('admin.sparepart.index', ['stok' => 'habis']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Stok Habis</span>
                <strong class="kpi-value">{{ number_format($stats['stok_habis'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #DC2626;">
                    <span>Stok = 0 (perlu restock)</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Barang Masuk --}}
        <a class="sparepart-kpi-card is-blue" href="{{ route('admin.stok-masuk.index') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Barang Masuk</span>
                <strong class="kpi-value">{{ number_format($stats['barang_masuk'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Total penerimaan stok</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Barang Dipakai Servis --}}
        <a class="sparepart-kpi-card is-purple" href="{{ route('admin.inventory.history') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Dipakai Servis</span>
                <strong class="kpi-value">{{ number_format($stats['barang_servis'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Suku cadang terpasang</span>
                </div>
            </div>
        </a>

        <a class="sparepart-kpi-card is-amber" href="{{ route('admin.sparepart.index') }}">
            <div class="kpi-icon-box"><span style="font-size:18px; font-weight:800;">✓</span></div>
            <div class="kpi-content">
                <span class="kpi-label">Perlu Review Fitment</span>
                <strong class="kpi-value">{{ number_format($stats['fitment_review'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color:#D97706;"><span>Belum punya fit terverifikasi</span></div>
            </div>
        </a>
    </section>

    {{-- Main Container Card --}}
    <main class="sparepart-main-card">
        {{-- Card Header --}}
        <div class="sparepart-card-header">
            <div class="card-header-left">
                <div class="card-header-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16.5 9.4 7.55 4.24a1.79 1.79 0 0 0-2.5 1.48v12.56a1.79 1.79 0 0 0 2.5 1.48l8.95-5.16a1.8 1.8 0 0 0 0-3.16Z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="card-header-title">Daftar Suku Cadang & Perlengkapan</h2>
                    <p class="card-header-subtitle">Inventori resmi sparepart bengkel YZA Automotive</p>
                </div>
            </div>

            <div class="header-action-group">
                <a class="btn-action-primary" href="{{ route('admin.sparepart.create') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Tambah Sparepart</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.stok-masuk.create') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path></svg>
                    <span>Stok Masuk</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.inventory.adjustment') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    <span>Penyesuaian</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.inventory.history') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <span>Riwayat Stok</span>
                </a>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <form class="sparepart-filter-toolbar" method="get" action="{{ route('admin.sparepart.index') }}">
            <div class="filter-wrap search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari kode SKU, nama barang, atau merk...">
            </div>

            <div class="filter-wrap select-box">
                <select name="kategori" class="filter-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id_kategori }}" @selected(request('kategori') == $c->id_kategori)>{{ $c->nama_kategori }}</option>
                    @endforeach
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-wrap select-box">
                <select name="stok" class="filter-select">
                    <option value="">Semua Status Stok</option>
                    <option value="aman" @selected(request('stok') === 'aman')>Aman (> Minimum)</option>
                    <option value="menipis" @selected(request('stok') === 'menipis')>Menipis (&le; Minimum)</option>
                    <option value="habis" @selected(request('stok') === 'habis')>Habis (0)</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-wrap select-box">
                <select name="status" class="filter-select">
                    <option value="">Semua Status Master</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <div class="filter-wrap select-box">
                <select name="compatibility" class="filter-select">
                    <option value="">Semua Kompatibilitas</option>
                    <option value="with_fitment" @selected(request('compatibility') === 'with_fitment')>Memiliki Fitment</option>
                    <option value="without_fitment" @selected(request('compatibility') === 'without_fitment')>Belum Memiliki Fitment</option>
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <button type="submit" class="btn-filter-submit">Filter</button>
            @if(request()->hasAny(['q', 'kategori', 'stok', 'status', 'compatibility']))
                <a href="{{ route('admin.sparepart.index') }}" class="btn-filter-reset">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="sparepart-table-wrapper">
            <table class="sparepart-table">
                <thead>
                    <tr>
                        <th style="width: 55px;">Foto</th>
                        <th>Kode SKU</th>
                        <th>Nama Barang & Merk</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok Fisik</th>
                        <th>Minimum</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>
                                <div class="part-thumb-wrap">
                                    @if($item->photoUrl())
                                        <img src="{{ $item->photoUrl() }}" alt="{{ $item->nama_sparepart }}" class="part-thumb-img">
                                    @else
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge-sku">{{ $item->kode_sparepart }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #0F172A; font-size: 13.5px; display: block;">{{ $item->nama_sparepart }}</strong>
                                    <span style="color: #64748B; font-size: 12px;">Merk: {{ $item->merk ?: '—' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge-category">{{ $item->kategori->nama_kategori }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #475569;">{{ $item->satuan }}</span>
                            </td>
                            <td>
                                <span style="color: #64748B; font-size: 12.5px;">Rp {{ number_format((float)$item->harga_beli, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <strong style="color: #0F172A; font-size: 13px;">Rp {{ number_format((float)$item->harga_jual, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                <div class="stock-indicator">
                                    <span class="stock-num">{{ $item->stok }} {{ $item->satuan }}</span>
                                    <span class="stock-tag is-{{ $item->stockLevel() }}">
                                        {{ ucfirst($item->stockLevel()) }}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span style="color: #64748B; font-weight: 600;">{{ $item->stok_minimum }} {{ $item->satuan }}</span>
                            </td>
                            <td>
                                <span class="badge-status {{ $item->status === 'aktif' ? 'is-active' : 'is-inactive' }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div class="table-actions" style="justify-content: flex-end;">
                                    <a href="{{ route('admin.sparepart.show', $item) }}" class="btn-table-action" title="Detail">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.sparepart.edit', $item) }}" class="btn-table-action" title="Edit">
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('admin.sparepart.toggle', $item) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-table-action {{ $item->status === 'aktif' ? 'is-danger' : '' }}" onclick="return confirm('Apakah Anda yakin ingin mengubah status suku cadang ini?')">
                                            {{ $item->status === 'aktif' ? 'Nonaktif' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" style="margin-bottom: 8px;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                <p style="margin: 0; font-weight: 600;">Belum ada data suku cadang yang sesuai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div>
            {{ $items->links() }}
        </div>
    </main>
</div>
@endsection
