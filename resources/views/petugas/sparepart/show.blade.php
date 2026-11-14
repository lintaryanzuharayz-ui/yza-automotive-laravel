@extends('layouts.app')

@section('title', 'Detail Spare Part · '.$item->nama_sparepart.' · YZA Automotive')

@section('content')
<div class="petugas-sparepart-detail">
    <style>
        .petugas-sparepart-detail {
            max-width: 960px;
            margin: 0 auto;
            padding: 8px 12px 48px;
            font-family: inherit;
            color: #0f172a;
        }
        .sp-hero {
            background: linear-gradient(135deg, #090a0f 0%, #151821 60%, #1e2330 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 26px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.15);
            margin-bottom: 24px;
        }
        .sp-hero-eyebrow {
            color: #ef1b2d;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 6px;
        }
        .sp-hero h1 {
            font-size: clamp(24px, 3vw, 32px);
            font-weight: 800;
            margin: 0 0 6px;
            color: #ffffff;
        }
        .sp-hero p {
            color: #94a3b8;
            font-size: 13.5px;
            margin: 0;
        }
        .sp-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 26px 30px;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
        }
        .sp-card-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .sp-grid-facts {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .sp-fact-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
        }
        .sp-fact-item small {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .sp-fact-item strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }
        .sp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .sp-btn-primary { background: #ef1b2d; color: #fff; }
        .sp-btn-primary:hover { background: #dc1829; }
        .sp-btn-outline { background: #fff; color: #0f172a; border-color: #e2e8f0; }
        .sp-btn-outline:hover { background: #f8fafc; }
        .sp-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
        }
        .sp-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-safe { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-safe .sp-badge-dot { background: #10b981; }
        .badge-low { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .badge-low .sp-badge-dot { background: #f59e0b; }
        .badge-out { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-out .sp-badge-dot { background: #ef1b2d; }
        @media (max-width: 640px) {
            .sp-grid-facts { grid-template-columns: 1fr; }
            .sp-hero { padding: 20px; }
        }
    </style>

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

    <header class="sp-hero">
        <div>
            <div class="sp-hero-eyebrow">Detail Suku Cadang</div>
            <h1>{{ $item->nama_sparepart }}</h1>
            <p>Kode Part: {{ $item->kode_sparepart }} · Kategori: {{ $item->kategori?->nama_kategori ?: 'General' }}</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <span class="sp-badge {{ $badge['cls'] }}">
                <span class="sp-badge-dot"></span>
                {{ $badge['lbl'] }}
            </span>
            <a href="{{ route('petugas.sparepart.index') }}" class="sp-btn sp-btn-outline" style="border-color:rgba(255,255,255,0.2); color:#fff; background:transparent;">
                ← Kembali
            </a>
        </div>
    </header>

    <div class="sp-card">
        <h2 class="sp-card-title">Spesifikasi & Informasi Ketersediaan</h2>
        <div class="sp-grid-facts">
            <div class="sp-fact-item">
                <small>Kode Suku Cadang</small>
                <strong>{{ $item->kode_sparepart }}</strong>
            </div>
            <div class="sp-fact-item">
                <small>Kategori Spare Part</small>
                <strong>{{ $item->kategori?->nama_kategori ?: 'General' }}</strong>
            </div>
            <div class="sp-fact-item">
                <small>Merk / Produsen</small>
                <strong>{{ $item->merk ?: 'Original Parts' }}</strong>
            </div>
            <div class="sp-fact-item">
                <small>Harga Eceran Resmi (Jual)</small>
                <strong style="color:#ef1b2d;">Rp {{ number_format((float)$item->harga_jual, 0, ',', '.') }}</strong>
            </div>
            <div class="sp-fact-item">
                <small>Stok Fisik Saat Ini</small>
                <strong style="font-size:18px; color: {{ $stok <= $min ? '#ef1b2d' : '#059669' }};">
                    {{ number_format($stok) }} {{ $item->satuan ?: 'Pcs' }}
                </strong>
            </div>
            <div class="sp-fact-item">
                <small>Batas Minimum Pengadaan</small>
                <strong>{{ number_format($min) }} {{ $item->satuan ?: 'Pcs' }}</strong>
            </div>
        </div>

        <div style="margin-top:24px; padding-top:18px; border-top:1px solid #f1f5f9; display:flex; justify-content:flex-end; gap:10px;">
            <a href="{{ route('petugas.inventory.adjustment', $item) }}" class="sp-btn sp-btn-primary">
                ⚡ Lakukan Penyesuaian Stok (Opname)
            </a>
        </div>
    </div>
</div>
@endsection
