@extends('layouts.app')

@section('title', 'Katalog Suku Cadang · Gudang & Bengkel · YZA Automotive')

@section('content')
<div class="petugas-sparepart-page">
    <style>
        .petugas-sparepart-page {
            max-width: 1520px;
            margin: 0 auto;
            padding: 8px 16px 56px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Hero Header */
        .sp-hero {
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
        .sp-hero::before {
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
        .sp-hero-eyebrow {
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
        .sp-hero-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #ef1b2d;
            border-radius: 50%;
        }
        .sp-hero h1 {
            font-size: clamp(24px, 3.2vw, 34px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .sp-hero p {
            color: #94a3b8;
            font-size: 14px;
            margin: 0;
            max-width: 640px;
            line-height: 1.5;
        }

        /* KPI Callout Grid */
        .sp-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .sp-kpi-card {
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
        .sp-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }
        .sp-kpi-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 6px;
        }
        .sp-kpi-value {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
            line-height: 1;
        }
        .sp-kpi-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Workspace Card */
        .sp-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }

        /* Toolbar */
        .sp-toolbar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 1px solid #f1f5f9;
        }
        .sp-input {
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            color: #0f172a;
            min-width: 280px;
            flex: 1;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .sp-input:focus {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.12);
        }

        /* Modern Table */
        .sp-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 14px;
            background: #ffffff;
        }
        .sp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
            min-width: 920px;
        }
        .sp-table th {
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
        .sp-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .sp-table tbody tr {
            transition: background 0.15s ease;
        }
        .sp-table tbody tr:hover {
            background: #f8fafc;
        }
        .sp-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Monospace Identifiers */
        .sp-mono-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .sp-mono-price {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            color: #0f172a;
        }

        /* Status Badges */
        .sp-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .sp-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-safe {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .badge-safe .sp-badge-dot { background: #10b981; }
        .badge-low {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .badge-low .sp-badge-dot { background: #f59e0b; }
        .badge-out {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .badge-out .sp-badge-dot { background: #ef4444; }

        .sp-btn {
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
        .sp-btn-primary { background: #ef1b2d; color: #fff; }
        .sp-btn-primary:hover { background: #dc1829; }
        .sp-btn-dark { background: #0f172a; color: #fff; }
        .sp-btn-dark:hover { background: #1e293b; }
        .sp-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .sp-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }

        @media (max-width: 1024px) {
            .sp-kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .sp-kpi-grid { grid-template-columns: 1fr; }
            .sp-hero { padding: 22px; }
        }
    </style>

    <!-- Header Section -->
    <header class="sp-hero">
        <div>
            <div class="sp-hero-eyebrow">Inventory Control & Warehouse System</div>
            <h1>Katalog Suku Cadang Bengkel</h1>
            <p>Pencarian suku cadang, pengecekan ketersediaan stok fisik di rak gudang, dan rincian harga eceran resmi.</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="{{ route('petugas.inventory.adjustment') }}" class="sp-btn sp-btn-primary">
                ⚖️ Form Opname Stok
            </a>
            <a href="{{ route('petugas.dashboard') }}?tab=warehouse" class="sp-btn sp-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
                ← Dashboard Gudang
            </a>
        </div>
    </header>

    <!-- Visual Indicator Cards -->
    @php
        $totalItems = $items->total();
        $criticalCount = \App\Models\Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->where('stok', '>', 0)->count();
        $outOfStockCount = \App\Models\Sparepart::where('status', 'aktif')->where('stok', '<=', 0)->count();
    @endphp
    <div class="sp-kpi-grid">
        <div class="sp-kpi-card">
            <div>
                <div class="sp-kpi-label">Total Item Suku Cadang</div>
                <div class="sp-kpi-value">{{ number_format($totalItems) }}</div>
            </div>
            <div class="sp-kpi-icon" style="background:#f1f5f9; color:#0f172a;">📦</div>
        </div>
        <div class="sp-kpi-card">
            <div>
                <div class="sp-kpi-label">Part Kritis (Low Stock)</div>
                <div class="sp-kpi-value" style="color:#d97706;">{{ number_format($criticalCount) }}</div>
            </div>
            <div class="sp-kpi-icon" style="background:rgba(245, 158, 11, 0.1); color:#d97706;">⚠</div>
        </div>
        <div class="sp-kpi-card">
            <div>
                <div class="sp-kpi-label">Stok Habis (0 Unit)</div>
                <div class="sp-kpi-value" style="color:#dc2626;">{{ number_format($outOfStockCount) }}</div>
            </div>
            <div class="sp-kpi-icon" style="background:rgba(239, 68, 68, 0.1); color:#dc2626;">🚫</div>
        </div>
        <div class="sp-kpi-card">
            <div>
                <div class="sp-kpi-label">Suku Cadang Aktif</div>
                <div class="sp-kpi-value" style="color:#059669;">{{ number_format($totalItems - $outOfStockCount) }}</div>
            </div>
            <div class="sp-kpi-icon" style="background:rgba(16, 185, 129, 0.1); color:#059669;">✓</div>
        </div>
    </div>

    <!-- Main Workspace Card -->
    <div class="sp-card">
        <form class="sp-toolbar" method="GET">
            <div style="display:flex; gap:10px; flex:1; flex-wrap:wrap;">
                <input class="sp-input" name="q" value="{{ request('q') }}" placeholder="Cari nama suku cadang, kode part, atau merk...">
                <button type="submit" class="sp-btn sp-btn-dark">🔍 Cari Spare Part</button>
                @if(request('q'))
                    <a href="{{ route('petugas.sparepart.index') }}" class="sp-btn sp-btn-outline">Reset</a>
                @endif
            </div>
        </form>

        <div class="sp-table-wrap">
            <table class="sp-table">
                <thead>
                    <tr>
                        <th style="width:40px;">No</th>
                        <th>Kode Part</th>
                        <th>Nama Suku Cadang</th>
                        <th>Kategori</th>
                        <th>Merk</th>
                        <th style="text-align:center;">Stok Gudang</th>
                        <th style="text-align:right;">Harga Jual (Rp)</th>
                        <th>Status Stok</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $stok = (int)$item->stok;
                            $min = (int)$item->stok_minimum;
                            if ($stok <= 0) {
                                $badge = ['cls' => 'badge-out', 'lbl' => 'Habis (0)'];
                            } elseif ($stok <= $min) {
                                $badge = ['cls' => 'badge-low', 'lbl' => 'Kritis / Menipis'];
                            } else {
                                $badge = ['cls' => 'badge-safe', 'lbl' => 'Stok Aman'];
                            }
                        @endphp
                        <tr>
                            <td style="color:#64748b; font-size:12px;">{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                            <td>
                                <div class="sp-mono-code">{{ $item->kode_sparepart }}</div>
                                <small style="color:#64748b; font-size:11px;">Rak: <strong>{{ $item->lokasi_rak ?: 'Utama' }}</strong></small>
                            </td>
                            <td>
                                <strong style="color:#0f172a;">{{ $item->nama_sparepart }}</strong>
                            </td>
                            <td>
                                <span style="font-size:11.5px; color:#475569; background:#f1f5f9; padding:2px 8px; border-radius:6px;">
                                    {{ $item->kategori?->nama_kategori ?: 'General' }}
                                </span>
                            </td>
                            <td>
                                <span style="font-size:12px; color:#64748b;">{{ $item->merk ?: 'Original Parts' }}</span>
                            </td>
                            <td style="text-align:center;">
                                <strong style="font-family:ui-monospace, monospace; font-size:15px; color: {{ $stok <= $min ? '#dc2626' : '#0f172a' }};">
                                    {{ number_format($stok) }}
                                </strong>
                                <small style="color:#64748b; font-size:10.5px; display:block;">{{ $item->satuan ?: 'Pcs' }} (Min: {{ $min }})</small>
                            </td>
                            <td style="text-align:right;">
                                <span class="sp-mono-price">{{ number_format((float)$item->harga_jual, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="sp-badge {{ $badge['cls'] }}">
                                    <span class="sp-badge-dot"></span>
                                    {{ $badge['lbl'] }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <div style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                    <a href="{{ route('petugas.inventory.adjustment', $item) }}" class="sp-btn sp-btn-outline" style="padding:6px 10px; font-size:11px;" title="Sesuaikan Stok">
                                        ⚖️ Opname
                                    </a>
                                    <a href="{{ route('petugas.sparepart.show', $item) }}" class="sp-btn sp-btn-dark" style="padding:6px 12px; font-size:11px;">
                                        Detail →
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center; padding:44px 20px; color:#64748b;">
                                <div style="font-size:32px; margin-bottom:8px;">📦</div>
                                <strong style="display:block; font-size:15px; color:#0f172a; margin-bottom:4px;">Tidak Ada Spare Part</strong>
                                <p style="font-size:13px; margin:0;">Tidak ditemukan data suku cadang yang sesuai pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:24px; padding-top:16px; border-top:1px solid #f1f5f9;">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
