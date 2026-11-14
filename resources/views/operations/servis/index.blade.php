@extends('layouts.app')

@section('title', 'Daftar Work Order Servis · Mekanik & Bengkel · YZA Automotive')

@section('content')
<div class="mechanic-wo-page">
    <style>
        .mechanic-wo-page {
            max-width: 1520px;
            margin: 0 auto;
            padding: 8px 16px 56px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Hero Header */
        .wo-hero {
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
        .wo-hero::before {
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
        .wo-hero-eyebrow {
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
        .wo-hero-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #ef1b2d;
            border-radius: 50%;
        }
        .wo-hero h1 {
            font-size: clamp(24px, 3.2vw, 34px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .wo-hero p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
            max-width: 640px;
            line-height: 1.5;
        }

        /* KPI Callout Grid */
        .wo-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .wo-kpi-card {
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
        .wo-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }
        .wo-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 6px;
        }
        .wo-kpi-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            line-height: 1;
        }
        .wo-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Main Workspace Card */
        .wo-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        /* Minimalist Modern Table */
        .wo-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
        }
        .wo-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
            min-width: 920px;
        }
        .wo-table th {
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
        .wo-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .wo-table tbody tr {
            transition: background 0.15s ease;
        }
        .wo-table tbody tr:hover {
            background: #f8fafc;
        }
        .wo-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Identifiers */
        .wo-mono-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .wo-plat-pill {
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
        .wo-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .wo-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-inspection {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .badge-inspection .wo-badge-dot { background: #f59e0b; }
        .badge-working {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }
        .badge-working .wo-badge-dot { background: #3b82f6; }
        .badge-completed {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .badge-completed .wo-badge-dot { background: #10b981; }

        .wo-btn {
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
        .wo-btn-dark { background: #0f172a; color: #fff; }
        .wo-btn-dark:hover { background: #1e293b; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2); }
        .wo-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .wo-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }

        @media (max-width: 1024px) {
            .wo-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .wo-kpi-grid { grid-template-columns: 1fr; }
            .wo-hero { padding: 22px; }
        }
    </style>

    <!-- Header Section -->
    <header class="wo-hero">
        <div>
            <div class="wo-hero-eyebrow">Workshop & Mechanic Command Center</div>
            <h1>Daftar Work Order Servis</h1>
            <p>Pantau seluruh penugasan pengerjaan unit bengkel, diagnosis kerusakan fisik, alokasi suku cadang, dan pencatatan jasa teknisi.</p>
        </div>
        <a href="{{ route('petugas.dashboard') }}?tab=mechanic" class="wo-btn wo-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
            ← Kembali ke Command Center
        </a>
    </header>

    @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:14px; padding:14px 18px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- KPI Metric Stat Cards -->
    @php
        $totalWo = $rows->total();
        $inspectionCount = \App\Models\Servis::where('status', 'pemeriksaan')->count();
        $workingCount = \App\Models\Servis::where('status', 'dikerjakan')->count();
        $doneCount = \App\Models\Servis::where('status', 'selesai')->count();
    @endphp
    <div class="wo-kpi-grid">
        <div class="wo-kpi-card">
            <div>
                <div class="wo-kpi-label">Total Work Order</div>
                <div class="wo-kpi-value">{{ number_format($totalWo) }}</div>
            </div>
            <div class="wo-kpi-icon" style="background:#f1f5f9; color:#0f172a;">📋</div>
        </div>
        <div class="wo-kpi-card">
            <div>
                <div class="wo-kpi-label">Diagnosis & Inspeksi</div>
                <div class="wo-kpi-value" style="color:#d97706;">{{ number_format($inspectionCount) }}</div>
            </div>
            <div class="wo-kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">🔍</div>
        </div>
        <div class="wo-kpi-card">
            <div>
                <div class="wo-kpi-label">Sedang Dikerjakan (Bay)</div>
                <div class="wo-kpi-value" style="color:#2563eb;">{{ number_format($workingCount) }}</div>
            </div>
            <div class="wo-kpi-icon" style="background:rgba(59, 130, 246, 0.1); color:#2563eb;">⚡</div>
        </div>
        <div class="wo-kpi-card">
            <div>
                <div class="wo-kpi-label">Selesai Servis</div>
                <div class="wo-kpi-value" style="color:#059669;">{{ number_format($doneCount) }}</div>
            </div>
            <div class="wo-kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:#059669;">✓</div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="wo-card">
        <div class="wo-table-wrap">
            <table class="wo-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Kode Work Order</th>
                        <th>Pelanggan</th>
                        <th>Kendaraan Unit</th>
                        <th>Odometer Masuk</th>
                        <th>Waktu Masuk Bengkel</th>
                        <th>Status Pengerjaan</th>
                        <th style="text-align:right;">Aksi Mekanik</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $s)
                        @php
                            $badge = match($s->status) {
                                'pemeriksaan' => ['cls' => 'badge-inspection', 'lbl' => 'Pemeriksaan / Diagnosis'],
                                'dikerjakan' => ['cls' => 'badge-working', 'lbl' => 'Sedang Dikerjakan'],
                                'selesai' => ['cls' => 'badge-completed', 'lbl' => 'Servis Selesai'],
                                default => ['cls' => 'badge-completed', 'lbl' => ucfirst($s->status)],
                            };
                            $showUrl = route(request()->is('admin/*') ? 'admin.servis.show' : 'petugas.servis.show', $s);
                        @endphp
                        <tr>
                            <td style="color:#64748b; font-size:12px;">{{ $loop->iteration + ($rows->currentPage() - 1) * $rows->perPage() }}</td>
                            <td>
                                <div class="wo-mono-code">{{ $s->kode_servis }}</div>
                                <small style="color:#64748b; font-size:11px;">Mekanik: {{ $s->petugas?->nama_lengkap ?: 'Tim Bengkel' }}</small>
                            </td>
                            <td>
                                <strong style="color:#0f172a; display:block;">{{ $s->pelanggan?->nama_lengkap ?: 'Customer' }}</strong>
                                <small style="color:#64748b; font-size:11px;">📞 {{ $s->pelanggan?->no_telepon ?: '—' }}</small>
                            </td>
                            <td>
                                <span class="wo-plat-pill">{{ $s->kendaraan?->nomor_polisi ?: 'Plat —' }}</span>
                                <div style="font-size:12px; color:#475569; margin-top:3px;">
                                    {{ $s->kendaraan?->merk }} {{ $s->kendaraan?->model }}
                                </div>
                            </td>
                            <td>
                                <span style="font-family:ui-monospace, monospace; font-weight:700; color:#0f172a;">
                                    {{ number_format((int)($s->kilometer ?? 0)) }} km
                                </span>
                            </td>
                            <td>
                                <strong style="color:#0f172a;">{{ $s->tanggal_masuk?->translatedFormat('d M Y') ?? $s->created_at?->format('d/m/Y') }}</strong>
                                <small style="color:#64748b; font-size:11px; display:block;">⏰ {{ $s->tanggal_masuk?->format('H:i') ?? $s->created_at?->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <span class="wo-badge {{ $badge['cls'] }}">
                                    <span class="wo-badge-dot"></span>
                                    {{ $badge['lbl'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ $showUrl }}" class="wo-btn wo-btn-dark">
                                    Buka Work Order →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:44px 20px; color:#64748b;">
                                <div style="font-size:32px; margin-bottom:8px;">🔧</div>
                                <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Tidak Ada Work Order</strong>
                                <p style="font-size:13px; margin:0;">Belum ada kendaraan yang masuk dalam daftar pengerjaan servis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:24px; padding-top:16px; border-top:1px solid #f1f5f9;">
            {{ $rows->links() }}
        </div>
    </div>
</div>
@endsection
