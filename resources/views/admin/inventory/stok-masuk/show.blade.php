@extends('layouts.app')

@section('title', 'Detail Stok Masuk ' . $stok_masuk->kode_stok_masuk . ' · Admin YZA Automotive')
@section('admin-page-title', 'Detail Stok Masuk')

@section('content')
<div class="admin-stok-masuk-show">
    <style>
        .admin-stok-masuk-show {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .show-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .show-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }
        .show-breadcrumb a { color: #64748B; text-decoration: none; font-weight: 500; }
        .show-breadcrumb span.current { color: #0F172A; font-weight: 700; }
        .show-title-group {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .show-title-icon {
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
        .show-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0F172A;
            margin: 0;
            letter-spacing: -0.02em;
        }
        .show-title p {
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
        .btn-premium-primary {
            background: #E11D48;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.25);
        }
        .btn-premium-primary:hover {
            background: #BE123C;
            transform: translateY(-1px);
        }
        .btn-premium-success {
            background: #059669;
            color: #FFFFFF !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }
        .btn-premium-success:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        .btn-premium-danger {
            background: #DC2626;
            color: #FFFFFF !important;
        }
        .btn-premium-danger:hover { background: #B91C1C; }

        /* Meta Information Cards */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .meta-card {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 18px 20px;
        }
        .meta-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .meta-val {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            margin-top: 6px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-pill.draft { background: #FEF3C7; color: #B45309; }
        .status-pill.selesai { background: #D1FAE5; color: #047857; }
        .status-pill.dibatalkan { background: #FEE2E2; color: #B91C1C; }

        /* Content Card */
        .card-main {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            overflow: hidden;
        }
        .card-header-custom {
            padding: 20px 24px;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-header-title {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-header-title i { color: #E11D48; }

        /* Form Add Item */
        .add-item-box {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 14px;
            padding: 20px;
            margin: 24px;
        }
        .add-item-title {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .add-item-grid {
            display: grid;
            grid-template-columns: 3fr 1.5fr 2fr auto;
            gap: 14px;
            align-items: end;
        }
        .form-control-custom {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 10px;
            font-size: 13px;
            color: #0F172A;
            background: #FFFFFF;
            outline: none;
            box-sizing: border-box;
        }
        .form-control-custom:focus {
            border-color: #E11D48;
            box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.1);
        }

        /* Items Table */
        .premium-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .premium-table th {
            background: #F8FAFC;
            padding: 14px 24px;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #E2E8F0;
        }
        .premium-table td {
            padding: 16px 24px;
            font-size: 13px;
            color: #1E293B;
            border-bottom: 1px solid #F1F5F9;
            vertical-align: middle;
        }
        .sku-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 6px;
            background: #F1F5F9;
            color: #0F172A;
            border-radius: 4px;
            border: 1px solid #E2E8F0;
            margin-right: 6px;
        }

        .summary-bar {
            padding: 20px 24px;
            background: #F8FAFC;
            border-top: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .summary-total-label {
            font-size: 14px;
            color: #64748B;
            font-weight: 600;
        }
        .summary-total-val {
            font-size: 22px;
            font-weight: 800;
            color: #0F172A;
        }

        .footer-actions {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            background: #FFFFFF;
        }

        @media (max-width: 992px) {
            .meta-grid { grid-template-columns: repeat(2, 1fr); }
            .add-item-grid { grid-template-columns: 1fr; }
        }
    </style>

    <!-- Header -->
    <div class="show-header">
        <div>
            <div class="show-breadcrumb">
                <a href="{{ route('admin.sparepart.index') }}">Inventori</a>
                <span>›</span>
                <a href="{{ route('admin.stok-masuk.index') }}">Stok Masuk</a>
                <span>›</span>
                <span class="current">{{ $stok_masuk->kode_stok_masuk }}</span>
            </div>
            <div class="show-title-group">
                <div class="show-title-icon">
                    <i class="fas fa-truck-loading"></i>
                </div>
                <div class="show-title">
                    <h1>{{ $stok_masuk->kode_stok_masuk }}</h1>
                    <p>{{ $stok_masuk->supplier?->nama_supplier ?? 'Tanpa Supplier' }} · {{ \Carbon\Carbon::parse($stok_masuk->tanggal)->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.stok-masuk.index') }}" class="btn-premium btn-premium-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #D1FAE5; border: 1px solid #6EE7B7; color: #065F46; padding: 14px 18px; border-radius: 12px; font-size: 13px; font-weight: 600;">
            <i class="fas fa-check-circle" style="margin-right: 6px;"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 14px 18px; border-radius: 12px; font-size: 13px; font-weight: 500;">
            <i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> {{ $errors->first() }}
        </div>
    @endif

    <!-- Meta Information Cards -->
    <div class="meta-grid">
        <div class="meta-card">
            <div class="meta-label">Status Dokumen</div>
            <div class="meta-val">
                @if($stok_masuk->status === 'draft')
                    <span class="status-pill draft"><i class="fas fa-circle" style="font-size: 6px;"></i> Draft</span>
                @elseif($stok_masuk->status === 'selesai')
                    <span class="status-pill selesai"><i class="fas fa-check" style="font-size: 8px;"></i> Selesai (Diterima)</span>
                @else
                    <span class="status-pill dibatalkan"><i class="fas fa-times" style="font-size: 8px;"></i> {{ ucfirst($stok_masuk->status) }}</span>
                @endif
            </div>
        </div>
        <div class="meta-card">
            <div class="meta-label">Supplier / Vendor</div>
            <div class="meta-val">{{ $stok_masuk->supplier?->nama_supplier ?? 'Pengadaan Mandiri' }}</div>
        </div>
        <div class="meta-card">
            <div class="meta-label">Petugas Penerima</div>
            <div class="meta-val">{{ $stok_masuk->petugas?->nama_lengkap ?? 'Admin' }}</div>
        </div>
        <div class="meta-card">
            <div class="meta-label">Total Pengadaan</div>
            <div class="meta-val" style="color: #E11D48;">Rp {{ number_format($stok_masuk->total_biaya, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($stok_masuk->catatan)
        <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px 20px; font-size: 13px; color: #475569;">
            <strong style="color: #0F172A;">Catatan / Referensi:</strong> {{ $stok_masuk->catatan }}
        </div>
    @endif

    <!-- Card Items -->
    <div class="card-main">
        <div class="card-header-custom">
            <div class="card-header-title">
                <i class="fas fa-list-ol"></i> Rincian Barang Masuk
            </div>
            <span style="font-size: 13px; color: #64748B; font-weight: 600;">
                {{ $stok_masuk->detail->count() }} Macam Item
            </span>
        </div>

        @if($stok_masuk->status === 'draft')
            <!-- Add Item Form -->
            <div class="add-item-box">
                <div class="add-item-title">
                    <i class="fas fa-plus-circle" style="color: #E11D48;"></i> Tambah Sparepart ke Dokumen Penerimaan
                </div>
                <form method="post" action="{{ route('admin.stok-masuk.detail.store', $stok_masuk) }}">
                    @csrf
                    <div class="add-item-grid">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Pilih Sparepart</label>
                            <select class="form-control-custom" name="id_sparepart" required id="sparepart_selector">
                                <option value="">-- Cari Sparepart --</option>
                                @foreach(\App\Models\Sparepart::where('status','aktif')->orderBy('nama_sparepart')->get() as $p)
                                    <option value="{{ $p->id_sparepart }}" data-price="{{ (int)$p->harga_beli }}">
                                        {{ $p->kode_sparepart }} · {{ $p->nama_sparepart }} (Stok saat ini: {{ $p->stok }} {{ $p->satuan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Jumlah / Qty</label>
                            <input class="form-control-custom" type="number" name="jumlah" min="1" placeholder="Qty" required value="1">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Harga Beli Satuan (Rp)</label>
                            <input class="form-control-custom" type="number" name="harga_beli" step="1" min="0" placeholder="Harga Beli" required id="harga_beli_input">
                        </div>
                        <div>
                            <button type="submit" class="btn-premium btn-premium-primary" style="height: 42px;">
                                <i class="fas fa-plus"></i> Tambahkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <script>
                document.getElementById('sparepart_selector')?.addEventListener('change', function() {
                    const opt = this.options[this.selectedIndex];
                    const pr = opt.getAttribute('data-price');
                    if (pr && document.getElementById('harga_beli_input')) {
                        document.getElementById('harga_beli_input').value = pr;
                    }
                });
            </script>
        @endif

        <!-- Table of Items -->
        <table class="premium-table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama & SKU Sparepart</th>
                    <th>Satuan</th>
                    <th style="text-align: right;">Jumlah Masuk</th>
                    <th style="text-align: right;">Harga Beli Satuan</th>
                    <th style="text-align: right;">Subtotal</th>
                    <th style="text-align: center; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stok_masuk->detail as $index => $d)
                    <tr>
                        <td style="color: #94A3B8; font-weight: 600;">{{ $index + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <span class="sku-tag">{{ $d->sparepart?->kode_sparepart }}</span>
                                <span style="font-weight: 600; color: #0F172A;">{{ $d->sparepart?->nama_sparepart }}</span>
                            </div>
                        </td>
                        <td>
                            <span style="color: #64748B;">{{ $d->sparepart?->satuan ?? '-' }}</span>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0F172A;">
                            +{{ $d->jumlah }}
                        </td>
                        <td style="text-align: right; color: #475569;">
                            Rp {{ number_format($d->harga_beli, 0, ',', '.') }}
                        </td>
                        <td style="text-align: right; font-weight: 800; color: #0F172A;">
                            Rp {{ number_format($d->subtotal, 0, ',', '.') }}
                        </td>
                        <td style="text-align: center;">
                            @if($stok_masuk->status === 'draft')
                                <form method="post" action="{{ route('admin.stok-masuk.detail.destroy', $d) }}" onsubmit="return confirm('Hapus item ini dari draft?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #EF4444; cursor: pointer; padding: 4px 8px; border-radius: 6px; transition: background 0.2s;" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 11px; color: #94A3B8; font-style: italic;">Terkunci</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 36px 20px; color: #64748B;">
                            Belum ada item ditambahkan ke dokumen ini. Silakan pilih sparepart di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Bar -->
        <div class="summary-bar">
            <div class="summary-total-label">Total Nilai Pembelian Dokumen Ini:</div>
            <div class="summary-total-val">Rp {{ number_format($stok_masuk->total_biaya, 0, ',', '.') }}</div>
        </div>

        @if($stok_masuk->status === 'draft')
            <div class="footer-actions">
                <form method="post" action="{{ route('admin.stok-masuk.cancel', $stok_masuk) }}" onsubmit="return confirm('Yakin ingin membatalkan draft penerimaan ini?');">
                    @csrf
                    <button type="submit" class="btn-premium btn-premium-danger">
                        <i class="fas fa-ban"></i> Batalkan Draft
                    </button>
                </form>

                @if($stok_masuk->detail->isNotEmpty())
                    <form id="form-finalize-stok" method="post" action="{{ route('admin.stok-masuk.finalize', $stok_masuk) }}">
                        @csrf
                        <button type="submit" class="btn-premium btn-premium-success" onclick="return confirm('Apakah Anda yakin ingin memfinalisasi penerimaan stok ini? Stok fisik sparepart akan otomatis bertambah.');">
                            <i class="fas fa-check-double"></i> Finalisasi & Terima Stok Fisik
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
