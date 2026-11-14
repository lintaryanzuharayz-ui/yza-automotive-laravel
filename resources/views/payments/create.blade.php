@extends('layouts.app')

@section('title', 'Pencatatan & Verifikasi Pembayaran · Finance · YZA Automotive')

@section('content')
<div class="finance-payment-page">
    <style>
        .finance-payment-page {
            max-width: 1480px;
            margin: 0 auto;
            padding: 8px 16px 56px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Hero Header */
        .fin-hero {
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
        .fin-hero::before {
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
        .fin-hero-eyebrow {
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
        .fin-hero-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #ef1b2d;
            border-radius: 50%;
        }
        .fin-hero h1 {
            font-size: clamp(24px, 3.2vw, 32px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .fin-hero p {
            color: #94a3b8;
            font-size: 13.5px;
            margin: 0;
            line-height: 1.5;
        }

        /* 2-Column Dynamic Grid */
        .fin-layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(380px, 1fr);
            gap: 24px;
            align-items: flex-start;
        }

        /* Cards */
        .fin-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }
        .fin-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .fin-card-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Financial Metric Callout Cards */
        .fin-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .fin-summary-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
        }
        .fin-summary-item small {
            display: block;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .fin-summary-item strong {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            display: block;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Form Inputs */
        .fin-form-group {
            margin-bottom: 18px;
        }
        .fin-form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .fin-form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13.5px;
            outline: none;
            box-sizing: border-box;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .fin-form-control:focus {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.12);
        }

        /* Proof of Payment Preview Box */
        .fin-preview-box {
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            margin-top: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .fin-preview-box:hover {
            border-color: #ef1b2d;
            background: #fff;
        }
        .fin-preview-img {
            max-width: 100%;
            max-height: 240px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            display: none;
            margin-top: 12px;
        }

        /* Settlement Status Pill */
        .fin-settle-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .badge-paid {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .badge-unpaid {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }

        /* History Table */
        .fin-history-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 14px;
        }
        .fin-history-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
        }
        .fin-history-table th {
            background: #f8fafc;
            color: #475569;
            padding: 10px 14px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .fin-history-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .fin-history-table tr:last-child td {
            border-bottom: none;
        }

        .fin-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .fin-btn-primary { background: #ef1b2d; color: #fff; }
        .fin-btn-primary:hover { background: #dc1829; box-shadow: 0 4px 14px rgba(239, 27, 45, 0.3); }
        .fin-btn-dark { background: #0f172a; color: #fff; }
        .fin-btn-dark:hover { background: #1e293b; }
        .fin-btn-success { background: #059669; color: #fff; }
        .fin-btn-success:hover { background: #047857; }
        .fin-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .fin-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
        .fin-btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

        @media (max-width: 1024px) {
            .fin-layout-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .fin-summary-grid { grid-template-columns: 1fr; }
            .fin-hero { padding: 20px; }
        }
    </style>

    @php
        $paid = (float)$transaksi->pembayarans->where('status', 'terverifikasi')->sum('jumlah_bayar');
        $remaining = max(0, (float)$transaksi->total - $paid);
        $isPaid = $remaining <= 0 || $transaksi->status === 'lunas';
        $backUrl = route('petugas.dashboard') . '?tab=finance';
    @endphp

    <!-- Header Section -->
    <header class="fin-hero">
        <div>
            <div class="fin-hero-eyebrow">Finance & Cashier Settlement</div>
            <h1>Pencatatan Pembayaran · {{ $transaksi->kode_transaksi }}</h1>
            <p>Pelanggan: {{ $transaksi->pelanggan?->nama_lengkap ?: 'Customer' }} · Transaksi: {{ ucfirst(str_replace('_',' ',$transaksi->jenis_transaksi)) }}</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            <span class="fin-settle-badge {{ $isPaid ? 'badge-paid' : 'badge-unpaid' }}">
                {{ $isPaid ? '✓ LUNAS' : 'SISA TAGIHAN: Rp ' . number_format($remaining, 0, ',', '.') }}
            </span>
            <a href="{{ $backUrl }}" class="fin-btn fin-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
                ← Kembali ke Finance
            </a>
        </div>
    </header>

    @if(session('success'))
        <div style="background:#ecfdf5; border:1px solid #a7f3d0; color:#065f46; border-radius:14px; padding:14px 18px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
            <span>✓</span> {{ session('success') }}
        </div>
    @endif
    @if(isset($errors) && $errors->any())
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:14px; padding:14px 18px; margin-bottom:20px; font-weight:600; font-size:13.5px; display:flex; align-items:center; gap:8px;">
            <span>⚠</span> {{ $errors->first() }}
        </div>
    @endif

    <!-- 2-Column Dynamic Layout -->
    <div class="fin-layout-grid">
        <!-- Main Form (Left Column) -->
        <div class="fin-card">
            <div class="fin-card-header">
                <h2 class="fin-card-title">💳 Input Pembayaran Kasir / Transfer</h2>
                <span style="font-size:12px; color:#64748b;">Kasir: {{ auth()->user()->displayName() }}</span>
            </div>

            <!-- Financial Callout Grid -->
            <div class="fin-summary-grid">
                <div class="fin-summary-item">
                    <small>Total Tagihan Transaksi</small>
                    <strong>Rp {{ number_format((float)$transaksi->total, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-summary-item">
                    <small>Sudah Terverifikasi</small>
                    <strong style="color:#059669;">Rp {{ number_format($paid, 0, ',', '.') }}</strong>
                </div>
                <div class="fin-summary-item">
                    <small>Sisa Belum Lunas</small>
                    <strong style="color:{{ $remaining > 0 ? '#ef1b2d' : '#059669' }};">
                        Rp {{ number_format($remaining, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            @php
                $action = route(request()->is('admin/*') ? 'admin.payment.store' : 'petugas.payment.store', $transaksi);
            @endphp
            <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                @csrf
                
                <div class="fin-form-group">
                    <label for="metode">Metode Pembayaran <span style="color:#ef1b2d;">*</span></label>
                    <select name="metode" id="metode" class="fin-form-control" required>
                        <option value="transfer">Transfer Bank (BCA / Mandiri / BNI / BRI)</option>
                        <option value="tunai">Tunai / Cash Kasir</option>
                        <option value="kredit">Kartu Kredit / Debit EDC</option>
                        <option value="lainnya">Lainnya / QRIS</option>
                    </select>
                </div>

                <div class="fin-form-group">
                    <label for="jumlah">Jumlah Pembayaran Diterima (Rp) <span style="color:#ef1b2d;">*</span></label>
                    <input type="number" min="1" step="0.01" name="jumlah" id="jumlah" class="fin-form-control" value="{{ old('jumlah', $remaining > 0 ? $remaining : $transaksi->total) }}" required placeholder="Contoh: 350000" style="font-family:ui-monospace, monospace; font-size:18px; font-weight:800;">
                    <small style="color:#64748b; font-size:11.5px; margin-top:4px; display:block;">
                        Default otomatis terisi sesuai sisa tagihan.
                    </small>
                </div>

                <div class="fin-form-group">
                    <label for="bukti_transfer">Bukti Transfer / Struk Pembayaran (Live Preview)</label>
                    <input type="file" name="bukti_transfer" id="bukti_transfer" class="fin-form-control" accept="image/*,.pdf" onchange="previewTransferProof(this)">
                    <div class="fin-preview-box" id="preview-box" onclick="document.getElementById('bukti_transfer').click()">
                        <div id="preview-placeholder">
                            <span style="font-size:24px;">📸</span>
                            <div style="font-size:12.5px; font-weight:700; color:#0f172a; margin-top:4px;">Klik untuk memilih file struk / transfer</div>
                            <small style="color:#64748b;">Mendukung format JPG, PNG, atau PDF</small>
                        </div>
                        <img id="preview-img" class="fin-preview-img" alt="Pratinjau Bukti Transfer">
                    </div>
                </div>

                <div class="fin-form-group">
                    <label for="catatan">Catatan / Keterangan Pembayaran</label>
                    <textarea name="catatan" id="catatan" class="fin-form-control" rows="2" placeholder="Tuliskan nomor referensi mutasi atau nama pengirim transfer...">{{ old('catatan') }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; padding-top:18px; border-top:1px solid #f1f5f9;">
                    <a href="{{ $backUrl }}" class="fin-btn fin-btn-outline">Batal</a>
                    <button type="submit" class="fin-btn fin-btn-primary">✓ Simpan & Catat Pembayaran</button>
                </div>
            </form>
        </div>

        <!-- Sidebar Mutasi & Invoice (Right Column) -->
        <div>
            <!-- Riwayat Mutasi Pembayaran & Verifikasi -->
            <div class="fin-card">
                <div class="fin-card-header">
                    <h2 class="fin-card-title">📜 Riwayat Mutasi Tagihan</h2>
                    <span style="font-size:12px; color:#64748b;">{{ $transaksi->pembayarans->count() }} Mutasi</span>
                </div>

                @if($transaksi->pembayarans->isNotEmpty())
                    <div class="fin-history-wrap">
                        <table class="fin-history-table">
                            <thead>
                                <tr>
                                    <th>Metode & Waktu</th>
                                    <th style="text-align:right;">Nominal (Rp)</th>
                                    <th style="text-align:center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transaksi->pembayarans as $p)
                                    <tr>
                                        <td>
                                            <strong style="color:#0f172a; display:block;">{{ strtoupper(str_replace('_',' ',$p->metode_pembayaran ?: 'Tunai')) }}</strong>
                                            <small style="color:#64748b; font-size:11px;">{{ $p->created_at?->format('d/m/Y H:i') }}</small>
                                            @if($p->bukti_transfer)
                                                <div style="margin-top:4px;">
                                                    <a href="{{ asset('storage/' . $p->bukti_transfer) }}" target="_blank" style="font-size:11px; font-weight:700; color:#ef1b2d; text-decoration:underline;">
                                                        🔍 Lihat Bukti Transfer
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:700; color:#0f172a;">
                                            {{ number_format((float)$p->jumlah_bayar, 0, ',', '.') }}
                                        </td>
                                        <td style="text-align:center;">
                                            @if($p->status === 'terverifikasi')
                                                <span class="fin-settle-badge badge-paid">✓ Terverifikasi</span>
                                            @elseif($p->status === 'ditolak')
                                                <span class="fin-settle-badge badge-unpaid">✕ Ditolak</span>
                                            @else
                                                <div style="display:flex; flex-direction:column; gap:4px; align-items:center;">
                                                    <span style="font-size:11px; color:#d97706; font-weight:700;">Pending</span>
                                                    <div style="display:inline-flex; gap:4px;">
                                                        <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.payment.verify' : 'petugas.payment.verify', $p) }}">
                                                            @csrf
                                                            <button type="submit" class="fin-btn fin-btn-success fin-btn-sm">✓ Terima</button>
                                                        </form>
                                                        <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.payment.reject' : 'petugas.payment.reject', $p) }}">
                                                            @csrf
                                                            <button type="submit" class="fin-btn fin-btn-outline fin-btn-sm" style="color:#ef1b2d;">✕</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="text-align:center; padding:24px; color:#64748b;">
                        Belum ada mutasi pembayaran yang masuk untuk transaksi ini.
                    </div>
                @endif

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid #f1f5f9;">
                    <a href="{{ route(request()->is('admin/*') ? 'admin.invoice.servis' : 'petugas.invoice.servis', $transaksi) }}" target="_blank" class="fin-btn fin-btn-dark" style="width:100%; box-sizing:border-box;">
                        📄 Pratinjau & Cetak Invoice Resmi →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewTransferProof(input) {
        const file = input.files[0];
        const previewImg = document.getElementById('preview-img');
        const placeholder = document.getElementById('preview-placeholder');

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
            placeholder.style.display = 'block';
            if (file) {
                placeholder.querySelector('div').textContent = 'File terpilih: ' + file.name;
            }
        }
    }
</script>
@endsection
