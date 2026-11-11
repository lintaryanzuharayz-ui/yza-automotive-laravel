@extends('layouts.app')

@section('title', 'Booking Servis · Operasional YZA Automotive')
@section('admin-page-title', 'Booking Servis')

@section('content')
<div class="admin-booking-page">
    <style>
        .admin-booking-page {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* Header */
        .booking-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .booking-header-left {
            flex: 1 1 380px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .booking-breadcrumb {
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
        .booking-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .booking-title-icon {
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
        .booking-heading {
            margin: 0;
            font-size: 30px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }
        .booking-subheading {
            margin: 6px 0 0;
            font-size: 13.5px;
            color: #64748B;
            line-height: 1.5;
            max-width: 620px;
        }

        /* Header Banner */
        .booking-header-right {
            flex: 0 0 380px;
            min-width: 320px;
        }
        @media (max-width: 900px) {
            .booking-header-right { flex: 1 1 100%; }
        }
        .booking-banner {
            position: relative;
            height: 108px;
            border-radius: 16px;
            overflow: hidden;
            background: #0B1018;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
        }
        .booking-banner-img {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 65%;
            object-fit: cover;
            object-position: right center;
            opacity: 0.88;
        }
        .booking-banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, #0B1018 35%, rgba(11, 16, 24, 0.7) 65%, transparent 100%);
        }
        .booking-banner-content {
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

        /* 6 KPI Cards */
        .booking-kpi-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 14px;
        }
        @media (max-width: 1400px) { .booking-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 768px) { .booking-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 480px) { .booking-kpi-grid { grid-template-columns: 1fr; } }

        .booking-kpi-card {
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
        .booking-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }
        .booking-kpi-card.is-red { background: #FFF1F2; border-color: #FFE4E6; }
        .booking-kpi-card.is-red .kpi-icon-box { background: #FEE2E2; color: #E11D48; }
        .booking-kpi-card.is-amber { background: #FFFBEB; border-color: #FEF3C7; }
        .booking-kpi-card.is-amber .kpi-icon-box { background: #FEF3C7; color: #D97706; }
        .booking-kpi-card.is-blue { background: #EFF6FF; border-color: #DBEAFE; }
        .booking-kpi-card.is-blue .kpi-icon-box { background: #DBEAFE; color: #2563EB; }
        .booking-kpi-card.is-purple { background: #FAF5FF; border-color: #F3E8FF; }
        .booking-kpi-card.is-purple .kpi-icon-box { background: #F3E8FF; color: #9333EA; }
        .booking-kpi-card.is-green { background: #ECFDF5; border-color: #D1FAE5; }
        .booking-kpi-card.is-green .kpi-icon-box { background: #D1FAE5; color: #059669; }

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
        .booking-main-card {
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .booking-card-header {
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
        .booking-filter-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
            width: 100%;
        }
        @media (max-width: 1024px) {
            .booking-filter-toolbar { flex-wrap: wrap; }
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
        .booking-table-wrapper {
            overflow-x: auto;
            border: 1px solid #F1F5F9;
            border-radius: 14px;
        }
        .booking-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 13px;
            text-align: left;
        }
        .booking-table thead tr th {
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
        .booking-table tbody tr {
            transition: background 0.15s ease;
        }
        .booking-table tbody tr:hover {
            background: #F8FAFC;
        }
        .booking-table tbody tr td {
            padding: 14px;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
            color: #1E293B;
        }
        .booking-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Badges */
        .badge-code {
            font-family: monospace;
            font-size: 12.5px;
            font-weight: 700;
            color: #0F172A;
            background: #F1F5F9;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
        }
        .badge-booking-status {
            font-size: 11.5px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            display: inline-block;
            text-transform: capitalize;
        }
        .badge-booking-status.is-menunggu { background: #FEF3C7; color: #B45309; }
        .badge-booking-status.is-diterima { background: #EFF6FF; color: #1D4ED8; }
        .badge-booking-status.is-diproses { background: #F3E8FF; color: #7E22CE; }
        .badge-booking-status.is-selesai { background: #ECFDF5; color: #047857; }
        .badge-booking-status.is-ditolak { background: #FEF2F2; color: #B91C1C; }
        .badge-booking-status.is-dibatalkan { background: #F1F5F9; color: #64748B; }

        /* Action Buttons */
        .table-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-table-action {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #E2E8F0;
            background: #FFFFFF;
            color: #0F172A;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-table-action:hover {
            background: #0F172A;
            color: #FFFFFF;
            border-color: #0F172A;
        }
    </style>

    <nav class="service-operations-nav" aria-label="Navigasi Service Operations">
        <a class="is-active" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index', ['status' => 'menunggu']) }}"><span>01</span><strong>Booking Menunggu</strong><small>Review dan konfirmasi</small></a>
        <a href="{{ route($role==='admin'?'admin.servis.index':'petugas.servis.index') }}"><span>02</span><strong>Work Order</strong><small>Pekerjaan bengkel aktif</small></a>
        @if($role==='admin')<a href="{{ route('admin.servis.schedule') }}"><span>03</span><strong>Jadwal Bengkel</strong><small>Kalender dan kapasitas bengkel</small></a>@else<span class="is-disabled"><span>03</span><strong>Jadwal Bengkel</strong><small>Khusus admin</small></span>@endif
    </nav>
    <style>.service-operations-nav{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:4px}.service-operations-nav a,.service-operations-nav>span{display:grid;grid-template-columns:34px 1fr;column-gap:10px;align-items:center;padding:14px 16px;border:1px solid #e2e8f0;border-radius:14px;background:#fff;color:#334155;text-decoration:none}.service-operations-nav a:hover{border-color:#ed172b}.service-operations-nav a.is-active{border-color:#fecdd3;background:#fff5f6}.service-operations-nav span{grid-row:1/3;display:grid;place-items:center;width:30px;height:30px;border-radius:9px;background:#f1f5f9;color:#64748b;font-size:11px;font-weight:900}.service-operations-nav .is-active>span{background:#ed172b;color:#fff}.service-operations-nav strong{font-size:13px}.service-operations-nav small{color:#94a3b8;font-size:11px}.service-operations-nav .is-disabled{opacity:.7;cursor:not-allowed}@media(max-width:700px){.service-operations-nav{grid-template-columns:1fr}}
</style>

    {{-- Header --}}
    <header class="booking-header">
        <div class="booking-header-left">
            <nav class="booking-breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-back-arrow" aria-hidden="true">&lsaquo;</span>
                <span class="breadcrumb-item">Layanan & Operasional</span>
                <span class="breadcrumb-separator">&gt;</span>
                <span class="breadcrumb-current">Booking Servis</span>
            </nav>

            <div class="booking-title-group">
                <div class="booking-title-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <h1 class="booking-heading">Antrian Booking Servis Member</h1>
                    <p class="booking-subheading">Kelola permohonan booking servis kendaraan pelanggan, verifikasi kepemilikan unit, dan mulai Work Order bengkel.</p>
                </div>
            </div>
        </div>

        <div class="booking-header-right">
            <div class="booking-banner">
                <img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt="YZA Workshop Booking" class="booking-banner-img" loading="eager">
                <div class="booking-banner-overlay"></div>
                <div class="booking-banner-content">
                    <span class="banner-tagline-top">LAYANAN PRIORITAS</span>
                    <span class="banner-tagline-bottom">BOOKING SERVIS BENGKEL</span>
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
    <section class="booking-kpi-grid" aria-label="Statistik Antrian Booking Servis">
        {{-- Card 1: Total Pengajuan --}}
        <a class="booking-kpi-card is-red" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index') }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Total Booking</span>
                <strong class="kpi-value">{{ number_format($stats['total'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Semua permohonan</span>
                </div>
            </div>
        </a>

        {{-- Card 2: Menunggu Konfirmasi --}}
        <a class="booking-kpi-card is-amber" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index', ['status' => 'menunggu']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Menunggu Review</span>
                <strong class="kpi-value">{{ number_format($stats['menunggu'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #D97706;">
                    <span>Perlu dikonfirmasi</span>
                </div>
            </div>
        </a>

        {{-- Card 3: Diterima / Siap Masuk --}}
        <a class="booking-kpi-card is-blue" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index', ['status' => 'diterima']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Diterima Bengkel</span>
                <strong class="kpi-value">{{ number_format($stats['diterima'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #2563EB;">
                    <span>Siap mulai WO</span>
                </div>
            </div>
        </a>

        {{-- Card 4: Diproses (Work Order Aktif) --}}
        <a class="booking-kpi-card is-purple" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index', ['status' => 'diproses']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Sedang Diproses</span>
                <strong class="kpi-value">{{ number_format($stats['diproses'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #7E22CE;">
                    <span>Dalam pengerjaan</span>
                </div>
            </div>
        </a>

        {{-- Card 5: Selesai --}}
        <a class="booking-kpi-card is-green" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index', ['status' => 'selesai']) }}">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Servis Selesai</span>
                <strong class="kpi-value">{{ number_format($stats['selesai'] ?? 0) }}</strong>
                <div class="kpi-subtext" style="color: #059669;">
                    <span>Pekerjaan tuntas</span>
                </div>
            </div>
        </a>

        {{-- Card 6: Ditolak --}}
        <div class="booking-kpi-card is-red">
            <div class="kpi-icon-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
            </div>
            <div class="kpi-content">
                <span class="kpi-label">Ditolak</span>
                <strong class="kpi-value">{{ number_format($stats['ditolak'] ?? 0) }}</strong>
                <div class="kpi-subtext">
                    <span>Pengajuan ditolak</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Container Card --}}
    <main class="booking-main-card">
        {{-- Card Header --}}
        <div class="booking-card-header">
            <div class="card-header-left">
                <div class="card-header-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <h2 class="card-header-title">Daftar Antrian Booking Servis</h2>
                    <p class="card-header-subtitle">Permohonan servis terjadwal dari member pemilik kendaraan YZA</p>
                </div>
            </div>

            <div class="header-action-group">
                <a class="btn-action-primary" href="{{ route(request()->is('admin/*')?'admin.servis.index':'petugas.servis.index') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    <span>Daftar Work Order</span>
                </a>
                <a class="btn-action-secondary" href="{{ route('admin.sparepart.index') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                    <span>Stok Sparepart</span>
                </a>
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <form class="booking-filter-toolbar" method="get" action="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index') }}">
            <div class="filter-wrap search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari kode booking, nama pelanggan, nopol, atau tipe kendaraan...">
            </div>

            <div class="filter-wrap select-box">
                <select name="status" class="filter-select">
                    <option value="">Semua Status Booking</option>
                    @foreach(['menunggu','diterima','ditolak','dibatalkan','diproses','selesai'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ match($s) { 'menunggu' => 'Menunggu Konfirmasi', 'diterima' => 'Booking Diterima', 'ditolak' => 'Booking Ditolak', 'dibatalkan' => 'Dibatalkan Pelanggan', 'diproses' => 'Work Order Aktif', 'selesai' => 'Selesai', default => ucfirst($s) } }}</option>
                    @endforeach
                </select>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="select-chevron"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </div>

            <button type="submit" class="btn-filter-submit">Filter</button>
            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index') }}" class="btn-filter-reset">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="booking-table-wrapper">
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Kode Booking</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan & Nopol</th>
                        <th>Jenis Layanan Servis</th>
                        <th>Jadwal Servis</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $b)
                        <tr>
                            <td>
                                <span class="badge-code">{{ $b->kode_booking }}</span>
                            </td>
                            <td>
                                <strong style="color: #0F172A; font-size: 13.5px; display: block;">{{ $b->pelanggan?->nama_lengkap ?: '—' }}</strong>
                                <span style="color: #64748B; font-size: 12px;">{{ $b->pelanggan?->no_hp ?: '—' }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong style="color: #0F172A; font-size: 13px;">{{ $b->kendaraan?->merk }} {{ $b->kendaraan?->model }}</strong>
                                    <span style="color: #475569; font-size: 12px; display: block;">{{ $b->kendaraan?->nomor_polisi ?: 'Tanpa nomor plat' }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #1E293B;">{{ $b->jenisServis?->nama_servis ?: 'Pemeriksaan Umum' }}</span>
                            </td>
                            <td>
                                <span style="color: #334155; font-weight: 500;">
                                    {{ $b->tanggal ? \Carbon\Carbon::parse($b->tanggal)->format('d M Y') : '—' }} · {{ substr((string)$b->jam, 0, 5) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-booking-status is-{{ $b->status }}">
                                    {{ match($b->status) { 'menunggu' => 'Menunggu Konfirmasi', 'diterima' => 'Booking Diterima', 'ditolak' => 'Booking Ditolak', 'dibatalkan' => 'Dibatalkan Pelanggan', 'diproses' => 'Work Order Aktif', 'selesai' => 'Selesai', default => ucfirst($b->status) } }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a class="btn-table-action" href="{{ route($role==='admin'?'admin.booking-servis.show':'petugas.booking-servis.show', $b) }}">
                                    Kelola &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px 20px; color: #64748B;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" style="margin-bottom: 8px;"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                <p style="margin: 0; font-weight: 600;">Belum ada booking servis pada antrian ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div>
            {{ $rows->links() }}
        </div>
    </main>
</div>
@endsection
