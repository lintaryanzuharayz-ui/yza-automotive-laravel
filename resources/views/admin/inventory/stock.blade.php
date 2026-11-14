@extends('layouts.app')

@section('title', 'Katalog Stok Spare Part · Inventory Gudang · YZA Automotive')

@section('content')
<div class="warehouse-stock-page">
    <style>
        .warehouse-stock-page {
            max-width: 1520px;
            margin: 0 auto;
            padding: 8px 16px 56px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Hero Header */
        .wh-hero {
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
        .wh-hero::before {
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
        .wh-hero-eyebrow {
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
        .wh-hero-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #ef1b2d;
            border-radius: 50%;
        }
        .wh-hero h1 {
            font-size: clamp(24px, 3.2vw, 34px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .wh-hero p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
            max-width: 640px;
            line-height: 1.5;
        }

        /* Visual Indicator Cards (KPIs) */
        .wh-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .wh-kpi-card {
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
        .wh-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }
        .wh-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 6px;
        }
        .wh-kpi-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            line-height: 1;
        }
        .wh-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Workspace Card */
        .wh-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        /* Toolbar */
        .wh-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }

        /* Modern Table */
        .wh-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
        }
        .wh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
            min-width: 920px;
        }
        .wh-table th {
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
        .wh-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .wh-table tbody tr {
            transition: background 0.15s ease;
        }
        .wh-table tbody tr:hover {
            background: #f8fafc;
        }
        .wh-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Identifiers */
        .wh-mono-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .wh-mono-price {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            color: #0f172a;
        }

        /* Status Badges */
        .wh-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .wh-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-safe {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .badge-safe .wh-badge-dot { background: #10b981; }
        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .badge-warning .wh-badge-dot { background: #f59e0b; }
        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .badge-danger .wh-badge-dot { background: #ef4444; }

        .wh-btn {
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
        .wh-btn-primary { background: #ef1b2d; color: #fff; }
        .wh-btn-primary:hover { background: #dc1829; }
        .wh-btn-dark { background: #0f172a; color: #fff; }
        .wh-btn-dark:hover { background: #1e293b; }
        .wh-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .wh-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }

        @media (max-width: 1024px) {
            .wh-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .wh-kpi-grid { grid-template-columns: 1fr; }
            .wh-hero { padding: 22px; }
        }
    </style>

    <!-- Header Section -->
    <header class="wh-hero">
        <div>
            <div class="wh-hero-eyebrow">Inventory Control & Warehouse System</div>
            <h1>Katalog Stok Spare Part</h1>
            <p>Monitoring saldo ketersediaan suku cadang asli, low-stock threshold alert, histori keluar-masuk, serta stock opname bengkel.</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('admin.inventory.adjustment') }}" class="wh-btn wh-btn-primary">
                ⚖️ Form Opname / Adjustment Stok
            </a>
            <a href="{{ route('petugas.dashboard') }}?tab=warehouse" class="wh-btn wh-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
                ← Kembali ke Command Center
            </a>
        </div>
    </header>

    @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:14px; padding:14px 18px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Visual Indicator Cards -->
    @php
        $totalItems = $parts->total();
        $criticalCount = \App\Models\Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->where('stok', '>', 0)->count();
        $outOfStockCount = \App\Models\Sparepart::where('status', 'aktif')->where('stok', '<=', 0)->count();
        $totalValuation = \App\Models\Sparepart::where('status', 'aktif')->selectRaw('SUM(stok * harga_beli) as total_val')->value('total_val') ?? 0;
    @endphp
    <div class="wh-kpi-grid">
        <div class="wh-kpi-card">
            <div>
                <div class="wh-kpi-label">Total Item Suku Cadang</div>
                <div class="wh-kpi-value">{{ number_format($totalItems) }}</div>
            </div>
            <div class="wh-kpi-icon" style="background:#f1f5f9; color:#0f172a;">📦</div>
        </div>
        <div class="wh-kpi-card">
            <div>
                <div class="wh-kpi-label">Part Kritis (Low Stock)</div>
                <div class="wh-kpi-value" style="color:#d97706;">{{ number_format($criticalCount) }}</div>
            </div>
            <div class="wh-kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">⚠</div>
        </div>
        <div class="wh-kpi-card">
            <div>
                <div class="wh-kpi-label">Stok Habis (Kosong)</div>
                <div class="wh-kpi-value" style="color:#dc2626;">{{ number_format($outOfStockCount) }}</div>
            </div>
            <div class="wh-kpi-icon" style="background:rgba(239, 68, 68, 0.1); color:#dc2626;">🚫</div>
        </div>
        <div class="wh-kpi-card">
            <div>
                <div class="wh-kpi-label">Estimasi Nilai Valuasi Aset</div>
                <div class="wh-kpi-value" style="font-size:22px; color:#059669;">Rp {{ number_format($totalValuation / 1000000, 1) }}M</div>
            </div>
            <div class="wh-kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:#059669;">💰</div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="wh-card">
        <div class="wh-toolbar">
            <h2 style="font-size:16px; font-weight:800; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                <span>📋 Data Master Suku Cadang</span>
                <span style="font-size:12px; color:#64748b; font-weight:600;">(Halaman {{ $parts->currentPage() }} dari {{ $parts->lastPage() }})</span>
            </h2>
            <div style="display:flex; gap:10px;">
                <a href="{{ route('admin.inventory.history') }}" class="wh-btn wh-btn-outline">
                    📜 Riwayat Mutasi Stok
                </a>
            </div>
        </div>

        <div class="wh-table-wrap">
            <table class="wh-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Kode & Nama Spare Part</th>
                        <th>Kategori & Lokasi Rak</th>
                        <th style="text-align:center;">Status Ketersediaan</th>
                        <th style="text-align:right;">Sisa Stok Fisik</th>
                        <th style="text-align:right;">Harga Beli (Rp)</th>
                        <th style="text-align:right;">Harga Jual (Rp)</th>
                        <th style="text-align:right;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $part)
                        @php
                            $isOut = $part->stok <= 0;
                            $isLow = !$isOut && $part->stok <= $part->stok_minimum;
                            $badge = match(true) {
                                $isOut => ['cls' => 'badge-danger', 'lbl' => 'Stok Habis'],
                                $isLow => ['cls' => 'badge-warning', 'lbl' => 'Mendekati Minimum'],
                                default => ['cls' => 'badge-safe', 'lbl' => 'Stok Aman'],
                            };
                        @endphp
                        <tr>
                            <td style="color:#64748b; font-size:12px;">{{ $loop->iteration + ($parts->currentPage() - 1) * $parts->perPage() }}</td>
                            <td>
                                <div class="wh-mono-code">{{ $part->kode_sparepart }}</div>
                                <strong style="color:#0f172a; font-size:13.5px; display:block; margin-top:2px;">{{ $part->nama_sparepart }}</strong>
                            </td>
                            <td>
                                <span style="font-size:11.5px; color:#475569; background:#f1f5f9; padding:2px 8px; border-radius:6px;">
                                    {{ $part->kategori?->nama_kategori ?: 'General' }}
                                </span>
                                <small style="color:#64748b; display:block; margin-top:3px;">Rak: <strong>{{ $part->lokasi_rak ?: 'Utama' }}</strong></small>
                            </td>
                            <td style="text-align:center;">
                                <span class="wh-badge {{ $badge['cls'] }}">
                                    <span class="wh-badge-dot"></span>
                                    {{ $badge['lbl'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <span style="font-family:ui-monospace, monospace; font-size:16px; font-weight:800; color:{{ $isOut ? '#dc2626' : ($isLow ? '#d97706' : '#0f172a') }};">
                                    {{ number_format((int)$part->stok) }}
                                </span>
                                <small style="color:#64748b; display:block; font-size:10.5px;">Min: {{ $part->stok_minimum }} {{ $part->satuan }}</small>
                            </td>
                            <td style="text-align:right;">
                                <span class="wh-mono-price" style="color:#64748b;">{{ number_format((float)$part->harga_beli, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align:right;">
                                <span class="wh-mono-price">{{ number_format((float)$part->harga_jual, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('admin.inventory.adjustment', $part) }}" class="wh-btn wh-btn-outline" title="Lakukan Koreksi Stok Fisik">
                                    ⚖️ Opname
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:44px 20px; color:#64748b;">
                                <div style="font-size:32px; margin-bottom:8px;">📦</div>
                                <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Katalog Suku Cadang Kosong</strong>
                                <p style="font-size:13px; margin:0;">Belum ada data master suku cadang yang terdaftar di sistem.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:24px; padding-top:16px; border-top:1px solid #f1f5f9;">
            {{ $parts->links() }}
        </div>
    </div>
</div>
@endsection
