@extends('layouts.app')

@section('title', 'Riwayat & Mutasi Stok Inventori · Admin YZA Automotive')
@section('admin-page-title', 'Riwayat Perubahan Stok')

@section('content')
<div class="admin-inventory-history">
    <style>
        .admin-inventory-history {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .hist-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .hist-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }
        .hist-breadcrumb a { color: #64748B; text-decoration: none; font-weight: 500; }
        .hist-breadcrumb span.current { color: #0F172A; font-weight: 700; }
        .hist-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .hist-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0F172A, #1E293B);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .hist-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .hist-title p {
            font-size: 13px;
            color: #64748B;
            margin: 4px 0 0 0;
        }

        .btn-premium {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-premium-secondary {
            background: #FFFFFF;
            color: #334155 !important;
            border: 1px solid #E2E8F0;
        }
        .btn-premium-secondary:hover { background: #F8FAFC; }

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
        .premium-table tbody tr:hover { background: #F8FAFC; }

        .sku-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            background: #F1F5F9;
            color: #0F172A;
            border-radius: 4px;
            border: 1px solid #E2E8F0;
        }
        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .type-badge.masuk { background: #D1FAE5; color: #047857; }
        .type-badge.servis, .type-badge.keluar { background: #FEE2E2; color: #B91C1C; }
        .type-badge.penyesuaian { background: #FEF3C7; color: #B45309; }

        .delta-val {
            font-weight: 800;
            font-size: 14px;
        }
        .delta-val.plus { color: #059669; }
        .delta-val.minus { color: #DC2626; }

        .balance-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            background: #F1F5F9;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .pagination-container {
            padding: 16px 20px;
            border-top: 1px solid #F1F5F9;
        }
    </style>

    <div class="hist-header">
        <div>
            <div class="hist-breadcrumb">
                <a href="{{ route('admin.sparepart.index') }}">Inventori</a>
                <span>›</span>
                <span class="current">Riwayat & Mutasi Stok</span>
            </div>
            <div class="hist-title-group">
                <div class="hist-title-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="hist-title">
                    <h1>Riwayat Mutasi Stok Sparepart</h1>
                    <p>Audit trail lengkap pergerakan inventori dari stok masuk, pengerjaan servis, dan penyesuaian</p>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.sparepart.index') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-arrow-left"></i> Master Sparepart
            </a>
            <a href="{{ route('admin.stok-masuk.index') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-boxes"></i> Stok Masuk
            </a>
        </div>
    </div>

    <div class="card-main">
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Waktu & Tanggal</th>
                        <th>Kode & Nama Sparepart</th>
                        <th>Jenis Transaksi</th>
                        <th style="text-align: right;">Perubahan (Delta)</th>
                        <th style="text-align: center;">Mutasi Saldo (Sebelum → Sesudah)</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $h)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #0F172A;">
                                    {{ \Carbon\Carbon::parse($h->tanggal)->translatedFormat('d M Y') }}
                                </div>
                                <div style="font-size: 11px; color: #94A3B8;">
                                    {{ \Carbon\Carbon::parse($h->tanggal)->format('H:i:s') }} WIB
                                </div>
                            </td>
                            <td>
                                <span class="sku-badge">{{ $h->sparepart?->kode_sparepart ?? '-' }}</span>
                                <div style="font-weight: 600; color: #1E293B; margin-top: 4px;">
                                    {{ $h->sparepart?->nama_sparepart ?? 'Sparepart Telah Dihapus' }}
                                </div>
                            </td>
                            <td>
                                @if($h->jenis_transaksi === 'masuk')
                                    <span class="type-badge masuk"><i class="fas fa-arrow-down" style="font-size: 8px;"></i> Masuk</span>
                                @elseif($h->jenis_transaksi === 'servis' || $h->jenis_transaksi === 'keluar')
                                    <span class="type-badge servis"><i class="fas fa-arrow-up" style="font-size: 8px;"></i> Pemakaian Servis</span>
                                @else
                                    <span class="type-badge penyesuaian"><i class="fas fa-sliders-h" style="font-size: 8px;"></i> {{ ucfirst($h->jenis_transaksi) }}</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($h->jumlah_perubahan > 0)
                                    <span class="delta-val plus">+{{ $h->jumlah_perubahan }}</span>
                                @else
                                    <span class="delta-val minus">{{ $h->jumlah_perubahan }}</span>
                                @endif
                                <span style="font-size: 11px; color: #64748B;">{{ $h->sparepart?->satuan ?? 'unit' }}</span>
                            </td>
                            <td style="text-align: center;">
                                <div class="balance-badge">
                                    <span>{{ $h->stok_sebelum }}</span>
                                    <i class="fas fa-arrow-right" style="font-size: 9px; color: #94A3B8;"></i>
                                    <strong style="color: #0F172A;">{{ $h->stok_sesudah }}</strong>
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #475569;">
                                    {{ $h->keterangan ?? '-' }}
                                </div>
                                @if($h->referensi_tipe)
                                    <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">
                                        Ref: {{ class_basename($h->referensi_tipe) }} #{{ $h->referensi_id }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px 20px; color: #64748B;">
                                <div style="font-size: 32px; color: #CBD5E1; margin-bottom: 12px;"><i class="fas fa-receipt"></i></div>
                                <div style="font-weight: 600; font-size: 15px; color: #334155;">Belum Ada Riwayat Mutasi Stok</div>
                                <p style="font-size: 13px; color: #94A3B8; margin-top: 4px;">Setiap pergerakan stok dari transaksi masuk atau servis akan otomatis tercatat di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($history->hasPages())
            <div class="pagination-container">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
