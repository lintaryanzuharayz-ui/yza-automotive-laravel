@extends('layouts.app')

@section('title', 'Stok Masuk Sparepart · Admin YZA Automotive')
@section('admin-page-title', 'Stok Masuk Sparepart')

@section('content')
<div class="admin-inventory-page">
    <style>
        .admin-inventory-page {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .inv-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }
        .inv-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }
        .inv-breadcrumb a { color: #64748B; text-decoration: none; font-weight: 500; }
        .inv-breadcrumb span.current { color: #0F172A; font-weight: 700; }
        .inv-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .inv-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0F172A, #1E293B);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }
        .inv-title-text h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .inv-title-text p {
            font-size: 13px;
            color: #64748B;
            margin: 4px 0 0 0;
        }
        .inv-header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-premium {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-premium-primary {
            background: #E11D48;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
        }
        .btn-premium-primary:hover {
            background: #BE123C;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(225, 29, 72, 0.35);
        }
        .btn-premium-secondary {
            background: #FFFFFF;
            color: #334155 !important;
            border: 1px solid #E2E8F0;
        }
        .btn-premium-secondary:hover {
            background: #F8FAFC;
            border-color: #CBD5E1;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .kpi-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
        }
        .kpi-card.total::before { background: #0F172A; }
        .kpi-card.draft::before { background: #F59E0B; }
        .kpi-card.received::before { background: #10B981; }
        .kpi-card.cancelled::before { background: #64748B; }
        .kpi-info span {
            font-size: 12px;
            font-weight: 600;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .kpi-info h3 {
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin: 4px 0 0 0;
        }
        .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .kpi-card.total .kpi-icon { background: #F1F5F9; color: #0F172A; }
        .kpi-card.draft .kpi-icon { background: #FEF3C7; color: #D97706; }
        .kpi-card.received .kpi-icon { background: #D1FAE5; color: #059669; }
        .kpi-card.cancelled .kpi-icon { background: #F1F5F9; color: #64748B; }

        /* Table Card */
        .card-main {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .premium-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .premium-table th {
            background: #F8FAFC;
            padding: 14px 20px;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #E2E8F0;
        }
        .premium-table td {
            padding: 16px 20px;
            font-size: 13px;
            color: #1E293B;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }
        .premium-table tbody tr:hover {
            background: #F8FAFC;
        }
        .code-badge {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 8px;
            background: #F1F5F9;
            color: #0F172A;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .status-badge.draft { background: #FEF3C7; color: #B45309; }
        .status-badge.selesai, .status-badge.diterima { background: #D1FAE5; color: #047857; }
        .status-badge.dibatalkan { background: #FEE2E2; color: #B91C1C; }

        .action-btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #0F172A;
            background: #F1F5F9;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .action-btn-sm:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        .pagination-container {
            padding: 16px 20px;
            border-top: 1px solid #F1F5F9;
        }

        @media (max-width: 992px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 576px) {
            .kpi-grid { grid-template-columns: 1fr; }
        }
    </style>

    <!-- Header -->
    <div class="inv-header">
        <div class="inv-header-left">
            <div class="inv-breadcrumb">
                <a href="{{ route('admin.sparepart.index') }}">Inventori</a>
                <span>›</span>
                <span class="current">Stok Masuk</span>
            </div>
            <div class="inv-title-group">
                <div class="inv-title-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="inv-title-text">
                    <h1>Stok Masuk Sparepart</h1>
                    <p>Penerimaan barang dan penambahan saldo fisik inventori YZA Automotive</p>
                </div>
            </div>
        </div>
        <div class="inv-header-actions">
            <a href="{{ route('admin.inventory.adjustment') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-sliders-h"></i> Penyesuaian Stok
            </a>
            <a href="{{ route('admin.inventory.history') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-history"></i> Riwayat Stok
            </a>
            <a href="{{ route('admin.stok-masuk.create') }}" class="btn-premium btn-premium-primary">
                <i class="fas fa-plus"></i> Buat Draft Stok Masuk
            </a>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="kpi-grid">
        <div class="kpi-card total">
            <div class="kpi-info">
                <span>Total Penerimaan</span>
                <h3>{{ \App\Models\StokMasuk::count() }}</h3>
            </div>
            <div class="kpi-icon"><i class="fas fa-clipboard-list"></i></div>
        </div>
        <div class="kpi-card draft">
            <div class="kpi-info">
                <span>Draft Menunggu</span>
                <h3>{{ \App\Models\StokMasuk::where('status', 'draft')->count() }}</h3>
            </div>
            <div class="kpi-icon"><i class="fas fa-clock"></i></div>
        </div>
        <div class="kpi-card received">
            <div class="kpi-info">
                <span>Selesai Diterima</span>
                <h3>{{ \App\Models\StokMasuk::where('status', 'selesai')->count() }}</h3>
            </div>
            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="kpi-card cancelled">
            <div class="kpi-info">
                <span>Dibatalkan</span>
                <h3>{{ \App\Models\StokMasuk::where('status', 'dibatalkan')->count() }}</h3>
            </div>
            <div class="kpi-icon"><i class="fas fa-ban"></i></div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-main">
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Kode Penerimaan</th>
                        <th>Tanggal Masuk</th>
                        <th>Supplier</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th>Total Nilai</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td>
                                <span class="code-badge">{{ $r->kode_stok_masuk }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0F172A;">{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M Y') }}</div>
                                <div style="font-size: 11px; color: #94A3B8;">{{ \Carbon\Carbon::parse($r->tanggal)->format('H:i') }} WIB</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1E293B;">{{ $r->supplier?->nama_supplier ?? 'Tanpa Supplier' }}</div>
                                @if($r->supplier?->telepon)
                                    <div style="font-size: 11px; color: #64748B;">{{ $r->supplier->telepon }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="color: #475569;">{{ $r->petugas?->nama_lengkap ?? 'Administrator' }}</span>
                            </td>
                            <td>
                                @if($r->status === 'draft')
                                    <span class="status-badge draft"><i class="fas fa-circle" style="font-size: 6px;"></i> Draft</span>
                                @elseif($r->status === 'selesai')
                                    <span class="status-badge selesai"><i class="fas fa-check" style="font-size: 8px;"></i> Selesai</span>
                                @else
                                    <span class="status-badge dibatalkan"><i class="fas fa-times" style="font-size: 8px;"></i> {{ ucfirst($r->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #0F172A;">Rp {{ number_format($r->total_biaya, 0, ',', '.') }}</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.stok-masuk.show', $r) }}" class="action-btn-sm">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px 20px; color: #64748B;">
                                <div style="font-size: 32px; color: #CBD5E1; margin-bottom: 12px;"><i class="fas fa-inbox"></i></div>
                                <div style="font-weight: 600; font-size: 15px; color: #334155;">Belum Ada Data Stok Masuk</div>
                                <p style="font-size: 13px; color: #94A3B8; margin-top: 4px;">Mulai dengan membuat draft penerimaan barang baru.</p>
                                <a href="{{ route('admin.stok-masuk.create') }}" class="btn-premium btn-premium-primary" style="margin-top: 16px;">
                                    <i class="fas fa-plus"></i> Buat Draft Pertama
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div class="pagination-container">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
