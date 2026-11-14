@extends('layouts.app')

@section('title', 'Antrean Booking Servis · Service Advisor · YZA Automotive')

@section('content')
<div class="advisor-booking-page">
    <style>
        .advisor-booking-page {
            max-width: 1520px;
            margin: 0 auto;
            padding: 8px 16px 56px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Hero Section */
        .adv-hero {
            background: linear-gradient(135deg, #090a0f 0%, #151821 55%, #1e2330 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 28px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }
        .adv-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(239, 27, 45, 0.2) 0%, rgba(239, 27, 45, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .adv-hero-eyebrow {
            color: #ef1b2d;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            margin-bottom: 6px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .adv-hero-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #ef1b2d;
            border-radius: 50%;
        }
        .adv-hero h1 {
            font-size: clamp(24px, 3.2vw, 34px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .adv-hero p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
            max-width: 640px;
            line-height: 1.5;
        }

        /* KPI Callout Grid */
        .adv-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .adv-kpi-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.2s ease;
        }
        .adv-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }
        .adv-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 6px;
        }
        .adv-kpi-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            line-height: 1;
        }
        .adv-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Workspace Card */
        .adv-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        /* Toolbar */
        .adv-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }
        .adv-filter-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            flex: 1;
        }
        .adv-input, .adv-select {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            color: #0f172a;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .adv-input:focus, .adv-select:focus {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.12);
        }
        .adv-input {
            min-width: 280px;
            flex: 1;
        }

        /* View Toggle Buttons */
        .adv-view-switcher {
            display: inline-flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            gap: 4px;
        }
        .adv-view-btn {
            border: none;
            background: transparent;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .adv-view-btn.is-active {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
        }

        /* Minimalist Table */
        .adv-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
        }
        .adv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
            min-width: 920px;
        }
        .adv-table th {
            background: #f8fafc;
            color: #475569;
            padding: 13px 16px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }
        .adv-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .adv-table tbody tr {
            transition: background 0.15s ease;
        }
        .adv-table tbody tr:hover {
            background: #f8fafc;
        }
        .adv-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Monospace Identifiers */
        .adv-mono-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .adv-plat-pill {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }

        /* Semi-transparent Status Badges */
        .adv-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .adv-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .badge-pending .adv-badge-dot { background: #f59e0b; }
        
        .badge-confirmed {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }
        .badge-confirmed .adv-badge-dot { background: #3b82f6; }

        .badge-processing {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
            border: 1px solid rgba(139, 92, 246, 0.25);
        }
        .badge-processing .adv-badge-dot { background: #8b5cf6; }

        .badge-completed {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .badge-completed .adv-badge-dot { background: #10b981; }

        .badge-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .badge-cancelled .adv-badge-dot { background: #ef4444; }

        /* Interactive Queue Cards View */
        .adv-queue-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 18px;
        }
        .adv-queue-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }
        .adv-queue-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
            border-color: #cbd5e1;
        }
        .adv-queue-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .adv-queue-body {
            margin-bottom: 16px;
        }
        .adv-queue-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12.5px;
            padding: 4px 0;
        }
        .adv-queue-complaint {
            background: #f8fafc;
            border-left: 3px solid #ef1b2d;
            border-radius: 0 8px 8px 0;
            padding: 10px 12px;
            font-size: 12px;
            color: #334155;
            margin-top: 10px;
            line-height: 1.4;
        }
        .adv-queue-footer {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            padding-top: 12px;
            border-top: 1px solid #f1f5f9;
        }

        /* Buttons */
        .adv-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .adv-btn-primary { background: #ef1b2d; color: #fff; }
        .adv-btn-primary:hover { background: #dc1829; box-shadow: 0 2px 8px rgba(239, 27, 45, 0.3); }
        .adv-btn-dark { background: #0f172a; color: #fff; }
        .adv-btn-dark:hover { background: #1e293b; }
        .adv-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .adv-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
        .adv-btn-success { background: #059669; color: #fff; }
        .adv-btn-success:hover { background: #047857; }
        .adv-btn-danger-outline { background: #fff; color: #ef1b2d; border-color: #fecaca; }
        .adv-btn-danger-outline:hover { background: #fef2f2; }

        @media (max-width: 1024px) {
            .adv-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .adv-kpi-grid { grid-template-columns: 1fr; }
            .adv-hero { padding: 22px; }
            .adv-queue-grid { grid-template-columns: 1fr; }
        }
    </style>

    <!-- Header Section -->
    <header class="adv-hero">
        <div>
            <div class="adv-hero-eyebrow">Service Advisor Command Hub</div>
            <h1>Antrean Booking Servis</h1>
            <p>Kelola seluruh agenda reservasi servis berkala & perbaikan, konfirmasi slot kedatangan customer, serta lakukan check-in fisik kendaraan.</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <a href="{{ route('petugas.dashboard') }}?tab=advisor" class="adv-btn adv-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
                ← Kembali ke Command Center
            </a>
        </div>
    </header>

    @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:14px; padding:14px 18px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- KPI Metric Cards Grid -->
    @php
        $totalAll = $rows->total();
        $pendingCount = \App\Models\BookingServis::where('status', 'menunggu')->count();
        $confirmedCount = \App\Models\BookingServis::where('status', 'diterima')->count();
        $activeWoCount = \App\Models\BookingServis::where('status', 'diproses')->count();
    @endphp
    <div class="adv-kpi-grid">
        <div class="adv-kpi-card">
            <div>
                <div class="adv-kpi-label">Total Booking Masuk</div>
                <div class="adv-kpi-value">{{ number_format($totalAll) }}</div>
            </div>
            <div class="adv-kpi-icon" style="background:#f1f5f9; color:#0f172a;">📋</div>
        </div>
        <div class="adv-kpi-card">
            <div>
                <div class="adv-kpi-label">Menunggu Konfirmasi</div>
                <div class="adv-kpi-value" style="color:#d97706;">{{ number_format($pendingCount) }}</div>
            </div>
            <div class="adv-kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">⏳</div>
        </div>
        <div class="adv-kpi-card">
            <div>
                <div class="adv-kpi-label">Siap Check-in / Diterima</div>
                <div class="adv-kpi-value" style="color:#2563eb;">{{ number_format($confirmedCount) }}</div>
            </div>
            <div class="adv-kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:#2563eb;">🚗</div>
        </div>
        <div class="adv-kpi-card">
            <div>
                <div class="adv-kpi-label">Work Order Aktif</div>
                <div class="adv-kpi-value" style="color:#7c3aed;">{{ number_format($activeWoCount) }}</div>
            </div>
            <div class="adv-kpi-icon" style="background:rgba(139, 92, 246, 0.1); color:#7c3aed;">🔧</div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="adv-card">
        <!-- Toolbar: Search, Filters & View Toggle -->
        <div class="adv-toolbar">
            <form class="adv-filter-form" method="GET">
                <input class="adv-input" name="q" value="{{ request('q') }}" placeholder="Cari kode booking, nama customer, no. polisi, atau model...">
                <select class="adv-select" name="status">
                    <option value="">Semua Status Reservasi</option>
                    @foreach(['menunggu' => 'Pending (Menunggu)', 'diterima' => 'Dikonfirmasi (Siap Check-in)', 'diproses' => 'Diproses (Work Order)', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'] as $val => $lbl)
                        <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="adv-btn adv-btn-dark">Filter</button>
                @if(request('q') || request('status'))
                    <a href="{{ route('petugas.booking-servis.index') }}" class="adv-btn adv-btn-outline">Reset</a>
                @endif
            </form>

            <div class="adv-view-switcher" role="tablist">
                <button type="button" class="adv-view-btn is-active" id="btn-view-table" onclick="setViewMode('table')">
                    ☰ Tabel Interaktif
                </button>
                <button type="button" class="adv-view-btn" id="btn-view-cards" onclick="setViewMode('cards')">
                    ▦ Kartu Antrean
                </button>
            </div>
        </div>

        <!-- 1. Table View -->
        <div id="view-table-container">
            <div class="adv-table-wrap">
                <table class="adv-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Kode Booking</th>
                            <th>Customer & Kontak</th>
                            <th>Kendaraan Unit</th>
                            <th>Layanan & Catatan Keluhan</th>
                            <th>Jadwal Kedatangan</th>
                            <th>Status Reservasi</th>
                            <th style="text-align:right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $b)
                            @php
                                $badge = match($b->status) {
                                    'menunggu' => ['cls' => 'badge-pending', 'lbl' => 'Pending'],
                                    'diterima' => ['cls' => 'badge-confirmed', 'lbl' => 'Dikonfirmasi'],
                                    'diproses' => ['cls' => 'badge-processing', 'lbl' => 'WO Aktif'],
                                    'selesai' => ['cls' => 'badge-completed', 'lbl' => 'Selesai'],
                                    'ditolak' => ['cls' => 'badge-cancelled', 'lbl' => 'Ditolak'],
                                    'dibatalkan' => ['cls' => 'badge-cancelled', 'lbl' => 'Dibatalkan'],
                                    default => ['cls' => 'badge-pending', 'lbl' => ucfirst($b->status)],
                                };
                                $detailRoute = $role === 'admin' ? route('admin.booking-servis.show', $b) : route('petugas.booking-servis.show', $b);
                            @endphp
                            <tr>
                                <td style="color:#64748b; font-size:12px;">{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                                <td>
                                    <div class="adv-mono-code">{{ $b->kode_booking }}</div>
                                    <small style="color:#64748b; font-size:11px;">Dibuat: {{ $b->created_at?->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <strong style="color:#0f172a; display:block;">{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</strong>
                                    <small style="color:#64748b; font-size:11px;">📞 {{ $b->pelanggan?->no_telepon ?: '—' }}</small>
                                </td>
                                <td>
                                    <span class="adv-plat-pill">{{ $b->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                    <div style="font-size:12px; color:#475569; margin-top:3px;">
                                        {{ $b->kendaraan?->merk }} {{ $b->kendaraan?->model }}
                                        <span style="color:#94a3b8;">· {{ number_format((int)($b->kendaraan?->kilometer ?? 0)) }} km</span>
                                    </div>
                                </td>
                                <td>
                                    <strong style="color:#0f172a; font-size:12.5px;">{{ $b->jenisServis?->nama_servis ?: 'General Service' }}</strong>
                                    <div style="color:#64748b; font-size:11.5px; max-width:260px; line-height:1.3; margin-top:2px;" title="{{ $b->keluhan }}">
                                        {{ Str::limit($b->keluhan ?: 'Tidak ada keluhan khusus', 36) }}
                                    </div>
                                </td>
                                <td>
                                    <strong style="color:#0f172a;">{{ \Carbon\Carbon::parse($b->tanggal)->translatedFormat('d M Y') }}</strong>
                                    <div style="color:#64748b; font-size:11.5px;">⏰ {{ substr((string)$b->jam, 0, 5) }} WIB</div>
                                </td>
                                <td>
                                    <span class="adv-badge {{ $badge['cls'] }}">
                                        <span class="adv-badge-dot"></span>
                                        {{ $badge['lbl'] }}
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px; justify-content:flex-end; align-items:center;">
                                        @if($b->status === 'menunggu')
                                            <form method="POST" action="{{ route($role==='admin'?'admin.booking-servis.action':'petugas.booking-servis.action', [$b, 'confirm']) }}" onsubmit="return confirm('Konfirmasi booking {{ $b->kode_booking }}?')">
                                                @csrf
                                                <button type="submit" class="adv-btn adv-btn-success" title="Terima Reservasi">✓ Terima</button>
                                            </form>
                                        @elseif($b->status === 'diterima' && !$b->servis)
                                            <a class="adv-btn adv-btn-primary" href="{{ $detailRoute }}" title="Mobil Tiba - Check-in Sekarang">Check-in 🚗</a>
                                        @endif
                                        <a class="adv-btn adv-btn-outline" href="{{ $detailRoute }}">Detail →</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:44px 20px; color:#64748b;">
                                    <div style="font-size:32px; margin-bottom:8px;">🚗</div>
                                    <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Tidak Ada Antrean Booking</strong>
                                    <p style="font-size:13px; margin:0;">Tidak ditemukan jadwal reservasi servis dengan kriteria filter saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Queue Cards View (Kanban / Cards) -->
        <div id="view-cards-container" style="display:none;">
            <div class="adv-queue-grid">
                @forelse($rows as $b)
                    @php
                        $badge = match($b->status) {
                            'menunggu' => ['cls' => 'badge-pending', 'lbl' => 'Pending'],
                            'diterima' => ['cls' => 'badge-confirmed', 'lbl' => 'Siap Check-in'],
                            'diproses' => ['cls' => 'badge-processing', 'lbl' => 'WO Aktif'],
                            'selesai' => ['cls' => 'badge-completed', 'lbl' => 'Selesai'],
                            'ditolak' => ['cls' => 'badge-cancelled', 'lbl' => 'Ditolak'],
                            'dibatalkan' => ['cls' => 'badge-cancelled', 'lbl' => 'Dibatalkan'],
                            default => ['cls' => 'badge-pending', 'lbl' => ucfirst($b->status)],
                        };
                        $detailRoute = $role === 'admin' ? route('admin.booking-servis.show', $b) : route('petugas.booking-servis.show', $b);
                    @endphp
                    <div class="adv-queue-card">
                        <div>
                            <div class="adv-queue-header">
                                <div>
                                    <span class="adv-mono-code">{{ $b->kode_booking }}</span>
                                    <div style="font-size:11px; color:#64748b; margin-top:2px;">{{ \Carbon\Carbon::parse($b->tanggal)->translatedFormat('d M Y') }} · {{ substr((string)$b->jam, 0, 5) }} WIB</div>
                                </div>
                                <span class="adv-badge {{ $badge['cls'] }}">
                                    <span class="adv-badge-dot"></span>
                                    {{ $badge['lbl'] }}
                                </span>
                            </div>

                            <div class="adv-queue-body">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                    <span class="adv-plat-pill">{{ $b->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                    <span style="font-size:12px; font-weight:700; color:#0f172a;">{{ $b->kendaraan?->merk }} {{ $b->kendaraan?->model }}</span>
                                </div>

                                <div class="adv-queue-info-row">
                                    <span style="color:#64748b;">Customer:</span>
                                    <strong>{{ $b->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</strong>
                                </div>
                                <div class="adv-queue-info-row">
                                    <span style="color:#64748b;">Layanan:</span>
                                    <span style="color:#0f172a; font-weight:600;">{{ $b->jenisServis?->nama_servis ?: 'General Service' }}</span>
                                </div>
                                <div class="adv-queue-info-row">
                                    <span style="color:#64748b;">Odometer:</span>
                                    <span>{{ number_format((int)($b->kendaraan?->kilometer ?? 0)) }} km</span>
                                </div>

                                @if($b->keluhan)
                                    <div class="adv-queue-complaint">
                                        <strong>Keluhan:</strong> "{{ Str::limit($b->keluhan, 70) }}"
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="adv-queue-footer">
                            @if($b->status === 'menunggu')
                                <form method="POST" action="{{ route($role==='admin'?'admin.booking-servis.action':'petugas.booking-servis.action', [$b, 'confirm']) }}">
                                    @csrf
                                    <button type="submit" class="adv-btn adv-btn-success">✓ Terima</button>
                                </form>
                            @elseif($b->status === 'diterima' && !$b->servis)
                                <a href="{{ $detailRoute }}" class="adv-btn adv-btn-primary">Check-in Fisik 🚗</a>
                            @endif
                            <a href="{{ $detailRoute }}" class="adv-btn adv-btn-outline">Detail →</a>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align:center; padding:44px 20px; color:#64748b;">
                        <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Tidak Ada Antrean Booking</strong>
                        <p style="font-size:13px; margin:0;">Tidak ditemukan jadwal reservasi servis dengan kriteria filter saat ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div style="margin-top:24px; padding-top:16px; border-top:1px solid #f1f5f9;">
            {{ $rows->links() }}
        </div>
    </div>
</div>

<script>
    function setViewMode(mode) {
        const table = document.getElementById('view-table-container');
        const cards = document.getElementById('view-cards-container');
        const btnTable = document.getElementById('btn-view-table');
        const btnCards = document.getElementById('btn-view-cards');

        if (mode === 'cards') {
            table.style.display = 'none';
            cards.style.display = 'block';
            btnCards.classList.add('is-active');
            btnTable.classList.remove('is-active');
            localStorage.setItem('yza_adv_booking_view', 'cards');
        } else {
            table.style.display = 'block';
            cards.style.display = 'none';
            btnTable.classList.add('is-active');
            btnCards.classList.remove('is-active');
            localStorage.setItem('yza_adv_booking_view', 'table');
        }
    }

    // Persist user view preference
    document.addEventListener('DOMContentLoaded', () => {
        const pref = localStorage.getItem('yza_adv_booking_view');
        if (pref === 'cards') {
            setViewMode('cards');
        }
    });
</script>
@endsection
