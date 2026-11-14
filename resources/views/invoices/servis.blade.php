@extends('layouts.app')

@section('title', 'Invoice Resmi '.$transaksi->kode_transaksi.' · YZA Automotive')

@section('content')
<div class="invoice-container">
    <style>
        .invoice-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 16px 20px 80px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Toolbar */
        .invoice-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        /* Invoice Paper Document */
        .invoice-paper {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 20px;
            padding: 48px 52px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
            position: relative;
        }

        /* Header & Brand Identity */
        .inv-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 28px;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .inv-brand h1 {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: -0.03em;
            margin: 0;
            color: #0f172a;
            text-transform: uppercase;
        }
        .inv-brand span {
            color: #ef1b2d;
        }
        .inv-brand p {
            font-size: 12px;
            color: #64748b;
            margin: 6px 0 0;
            line-height: 1.6;
        }
        .inv-meta-right {
            text-align: right;
        }
        .inv-badge-title {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .inv-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 15px;
            font-weight: 800;
            color: #ef1b2d;
            margin-top: 4px;
        }

        /* Customer & Vehicle Info Grid */
        .inv-grid-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
            margin-bottom: 32px;
            padding-bottom: 28px;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 13px;
        }
        .inv-info-block small {
            display: block;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.08em;
            margin-bottom: 6px;
        }
        .inv-info-block strong {
            display: block;
            font-size: 15px;
            color: #0f172a;
        }
        .inv-info-block span {
            display: block;
            color: #475569;
            margin-top: 3px;
            line-height: 1.5;
        }

        /* Line Items Table */
        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 28px;
        }
        .inv-table thead th {
            background: #0f172a;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 12px 16px;
            text-align: left;
        }
        .inv-table tbody td {
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .inv-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Grand Total Summary */
        .inv-summary-wrap {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 24px;
            margin-bottom: 28px;
        }
        .inv-summary-table {
            width: 360px;
            border-collapse: collapse;
            font-size: 13px;
        }
        .inv-summary-table td {
            padding: 7px 0;
        }
        .inv-summary-table tr.grand-total {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-size: 16px;
            font-weight: 800;
        }
        .inv-summary-table tr.grand-total td {
            padding: 12px 0;
            color: #ef1b2d;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 18px;
        }

        /* Clean Typographic Watermark Stamp */
        .inv-stamp {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 2px solid;
            border-radius: 12px;
            padding: 10px 22px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 16px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            transform: rotate(-3deg);
            margin-top: 10px;
        }
        .stamp-paid {
            border-color: #059669;
            color: #059669;
            background: rgba(16, 185, 129, 0.08);
        }
        .stamp-unpaid {
            border-color: #ef1b2d;
            color: #ef1b2d;
            background: rgba(239, 27, 45, 0.08);
        }

        /* Footer & Signatures */
        .inv-footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 12px;
            color: #64748b;
        }

        .btn-print {
            background: #0f172a;
            color: #ffffff;
            border: none;
            padding: 11px 22px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s ease;
        }
        .btn-print:hover {
            background: #1e293b;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
        }

        /* Print Media Styles */
        @media print {
            body { background: #ffffff !important; }
            .yza-header, .member-topbar, .invoice-toolbar, .yza-sidebar { display: none !important; }
            .invoice-container { padding: 0 !important; max-width: 100% !important; }
            .invoice-paper { border: none !important; box-shadow: none !important; padding: 20px 0 !important; }
        }
        @media (max-width: 640px) {
            .invoice-paper { padding: 26px 20px; }
            .inv-grid-info { grid-template-columns: 1fr; }
            .inv-meta-right { text-align: left; }
            .inv-summary-table { width: 100%; }
        }
    </style>

    <!-- Toolbar: Back & Print Button -->
    <div class="invoice-toolbar">
        <a href="{{ route('petugas.dashboard') }}?tab=finance" style="color:#0f172a; text-decoration:none; font-size:13px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
            ← Kembali ke Dashboard Finance
        </a>
        <button type="button" class="btn-print" onclick="window.print()">
            🖨️ Cetak / Unduh Dokumen Invoice Resmi (PDF)
        </button>
    </div>

    <!-- Official Invoice Document -->
    <article class="invoice-paper">
        <!-- Header & Kop Perusahaan -->
        <header class="inv-header">
            <div class="inv-brand">
                <h1>YZA <span>AUTOMOTIVE</span></h1>
                <p>
                    Authorized Workshop & Certified Pre-Owned Dealership<br>
                    Jl. Veteran Otomotif No. 506, Jakarta Selatan<br>
                    Layanan Kasir: (021) 8899-7700 · Email: finance@yza-automotive.com
                </p>
            </div>
            <div class="inv-meta-right">
                <div class="inv-badge-title">INVOICE FAKTUR SERVIS</div>
                <div class="inv-code">{{ $transaksi->kode_transaksi }}</div>
                <div style="font-size:12px; color:#64748b; margin-top:4px;">
                    Tanggal Faktur: <strong>{{ \Carbon\Carbon::parse($transaksi->tanggal)->translatedFormat('d F Y') }}</strong>
                </div>
            </div>
        </header>

        <!-- Data Info Grid -->
        <div class="inv-grid-info">
            <div class="inv-info-block">
                <small>DITAGIHKAN KEPADA PELANGGAN:</small>
                <strong>{{ $transaksi->pelanggan?->nama_lengkap ?: 'Customer Umum (Walk-in)' }}</strong>
                <span>No. Kontak: {{ $transaksi->pelanggan?->no_telepon ?: '—' }}</span>
                <span>Alamat Domisili: {{ $transaksi->pelanggan?->alamat ?: 'Indonesia' }}</span>
            </div>
            <div class="inv-info-block">
                <small>RINCIAN KENDARAAN & WORK ORDER:</small>
                <strong>{{ $transaksi->servis?->kendaraan?->merk }} {{ $transaksi->servis?->kendaraan?->model }}</strong>
                <span>Nomor Polisi: <strong>{{ $transaksi->servis?->kendaraan?->nomor_polisi ?: '—' }}</strong></span>
                <span>No. Work Order: {{ $transaksi->servis?->kode_servis ?: '—' }} · Odometer: {{ number_format((int)($transaksi->servis?->kilometer ?? 0)) }} km</span>
            </div>
        </div>

        <!-- Tabel Rincian Jasa & Spare Part -->
        <table class="inv-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Deskripsi Jasa Teknisi & Suku Cadang</th>
                    <th style="text-align:center; width:60px;">Qty</th>
                    <th style="text-align:right; width:150px;">Harga Satuan (Rp)</th>
                    <th style="text-align:right; width:160px;">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                
                <!-- Item Jasa -->
                @foreach($transaksi->servis?->details ?? [] as $d)
                    <tr>
                        <td style="color:#64748b;">{{ $no++ }}</td>
                        <td>
                            <strong style="color:#0f172a;">{{ $d->deskripsi_pekerjaan }}</strong>
                            <small style="color:#64748b; display:block;">Jasa Layanan & Inspeksi Teknisi Bengkel</small>
                        </td>
                        <td style="text-align:center; font-weight:700;">1</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace;">{{ number_format((float)$d->biaya, 0, ',', '.') }}</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:800; color:#0f172a;">{{ number_format((float)$d->biaya, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <!-- Item Spare Part -->
                @foreach($transaksi->servis?->sparepartItems ?? [] as $i)
                    <tr>
                        <td style="color:#64748b;">{{ $no++ }}</td>
                        <td>
                            <strong style="color:#0f172a;">{{ $i->sparepart?->nama_sparepart ?: 'Spare Part Asli' }}</strong>
                            <small style="color:#64748b; display:block;">Suku Cadang Asli (Part SKU: {{ $i->sparepart?->kode_sparepart }})</small>
                        </td>
                        <td style="text-align:center; font-weight:700;">{{ $i->jumlah }}</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace;">{{ number_format((float)$i->harga_satuan, 0, ',', '.') }}</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:800; color:#0f172a;">{{ number_format((float)$i->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                @if($no === 1)
                    <tr>
                        <td colspan="5" style="text-align:center; padding:24px; color:#64748b;">Tidak ada rincian jasa atau suku cadang tercatat.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Summary & Typographic Status Stamp -->
        @php
            $paid = (float)$transaksi->pembayarans->where('status', 'terverifikasi')->sum('jumlah_bayar');
            $remaining = max(0, (float)$transaksi->total - $paid);
            $isPaid = $remaining <= 0 || $transaksi->status === 'lunas';
        @endphp
        <div class="inv-summary-wrap">
            <div>
                <!-- Clean Typography Stamp -->
                <div class="inv-stamp {{ $isPaid ? 'stamp-paid' : 'stamp-unpaid' }}">
                    <span>{{ $isPaid ? '✓ LUNAS · FULLY PAID' : 'BELUM LUNAS · UNPAID' }}</span>
                </div>

                <div style="margin-top:16px; font-size:12px; color:#64748b; max-width:340px; line-height:1.6;">
                    Terima kasih atas kepercayaan Anda mempercayakan perawatan unit kendaraan di <strong>YZA Automotive</strong>. Seluruh pekerjaan jasa perbaikan dan penggantian suku cadang asli dijamin dengan garansi operasional 14 hari kerja.
                </div>
            </div>

            <div>
                <table class="inv-summary-table">
                    <tr>
                        <td style="color:#64748b;">Subtotal Jasa & Suku Cadang:</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:700;">Rp {{ number_format((float)$transaksi->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if((float)$transaksi->diskon > 0)
                        <tr>
                            <td style="color:#059669;">Diskon Member / Promo:</td>
                            <td style="text-align:right; color:#059669; font-family:ui-monospace, monospace; font-weight:700;">- Rp {{ number_format((float)$transaksi->diskon, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="grand-total">
                        <td>TOTAL TAGIHAN:</td>
                        <td style="text-align:right;">Rp {{ number_format((float)$transaksi->total, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#64748b; padding-top:10px;">Total Pembayaran Terverifikasi:</td>
                        <td style="text-align:right; color:#059669; font-family:ui-monospace, monospace; font-weight:700; padding-top:10px;">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#64748b;">Sisa Yang Harus Dibayar:</td>
                        <td style="text-align:right; font-family:ui-monospace, monospace; font-weight:800; color:{{ $remaining > 0 ? '#ef1b2d' : '#059669' }};">
                            Rp {{ number_format($remaining, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Riwayat Pembayaran -->
        @if($transaksi->pembayarans->isNotEmpty())
            <div style="margin-top:28px; padding-top:18px; border-top:1px dashed #e2e8f0;">
                <small style="font-weight:800; text-transform:uppercase; font-size:11px; color:#64748b; display:block; margin-bottom:8px;">Histori Pembayaran Diterima:</small>
                <div style="display:grid; gap:8px; font-size:12px;">
                    @foreach($transaksi->pembayarans as $p)
                        <div style="display:flex; justify-content:space-between; background:#f8fafc; padding:10px 14px; border-radius:10px;">
                            <span>{{ $p->created_at?->format('d/m/Y H:i') }} · <strong>{{ strtoupper(str_replace('_',' ',$p->metode_pembayaran ?: 'Tunai')) }}</strong></span>
                            <span>Status: <strong>{{ ucfirst($p->status) }}</strong> · <strong style="color:#059669; font-family:ui-monospace, monospace;">Rp {{ number_format((float)$p->jumlah_bayar, 0, ',', '.') }}</strong></span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Footer & Signatures -->
        <footer class="inv-footer">
            <div>
                <div>Waktu Cetak Dokumen: {{ now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB</div>
                <div>Petugas Kasir / Finance: <strong>{{ $transaksi->petugas?->nama_lengkap ?: auth()->user()->displayName() }}</strong></div>
            </div>
            <div style="text-align:center; min-width:180px;">
                <div style="height:60px;"></div>
                <div style="border-top:1px solid #0f172a; padding-top:6px; font-weight:800; font-size:12px; color:#0f172a; text-transform:uppercase; letter-spacing:0.04em;">
                    YZA Finance & Cashier Dept.
                </div>
            </div>
        </footer>
    </article>
</div>
@endsection
