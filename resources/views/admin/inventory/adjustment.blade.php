@extends('layouts.app')

@section('title', 'Penyesuaian Stok (Stock Opname) · Inventory Gudang · YZA Automotive')

@section('content')
<div class="warehouse-adj-page">
    <style>
        .warehouse-adj-page {
            max-width: 1440px;
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
            font-size: clamp(24px, 3.2vw, 32px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .wh-hero p {
            color: #94a3b8;
            font-size: 13.5px;
            margin: 0;
            line-height: 1.5;
        }

        /* 2-Column Dynamic Grid */
        .wh-adj-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(360px, 1fr);
            gap: 24px;
            align-items: flex-start;
        }

        /* Cards */
        .wh-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
        }
        .wh-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .wh-card-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Form Inputs */
        .wh-form-group {
            margin-bottom: 18px;
        }
        .wh-form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .wh-form-control {
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
        .wh-form-control:focus {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.12);
        }

        /* Live Delta Calculation Gauge */
        .wh-delta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            text-align: center;
        }
        .wh-delta-item small {
            display: block;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }
        .wh-delta-item strong {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        /* Part Profile Card Details */
        .wh-part-profile-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .wh-part-stat-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .wh-part-stat-row:last-child {
            border-bottom: none;
        }
        .wh-part-stat-row span {
            color: #64748b;
        }
        .wh-part-stat-row strong {
            color: #0f172a;
        }

        .wh-btn {
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
        .wh-btn-primary { background: #ef1b2d; color: #fff; }
        .wh-btn-primary:hover { background: #dc1829; box-shadow: 0 4px 14px rgba(239, 27, 45, 0.3); }
        .wh-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .wh-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }

        @media (max-width: 1024px) {
            .wh-adj-grid { grid-template-columns: 1fr; }
        }
    </style>

    <!-- Header Section -->
    <header class="wh-hero">
        <div>
            <div class="wh-hero-eyebrow">Inventory Control & Audit</div>
            <h1>Penyesuaian Stok (Stock Opname)</h1>
            <p>Formulir resmi audit fisik persediaan bengkel, sinkronisasi selisih buku dengan stok aktual di rak gudang, dan berita acara koreksi.</p>
        </div>
        <a href="{{ route('admin.inventory.stock') }}" class="wh-btn wh-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
            ← Katalog Stok Gudang
        </a>
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
    <div class="wh-adj-grid">
        <!-- Main Form (Left Column) -->
        <div class="wh-card">
            <div class="wh-card-header">
                <h2 class="wh-card-title">⚖️ Formulir Berita Acara Opname Fisik</h2>
                <span style="font-size:12px; color:#64748b;">ID Petugas: {{ auth()->user()->displayName() }}</span>
            </div>

            <form method="POST" action="{{ route('admin.inventory.adjust') }}">
                @csrf
                
                <div class="wh-form-group">
                    <label for="id_sparepart">Pilih Suku Cadang yang Disesuaikan <span style="color:#ef1b2d;">*</span></label>
                    <select name="id_sparepart" id="id_sparepart" class="wh-form-control" required onchange="handlePartSelect(this)">
                        <option value="">-- Cari atau Pilih Suku Cadang --</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id_sparepart }}"
                                @selected(old('id_sparepart', $selected?->id_sparepart) == $part->id_sparepart)
                                data-code="{{ $part->kode_sparepart }}"
                                data-name="{{ $part->nama_sparepart }}"
                                data-stock="{{ (int)$part->stok }}"
                                data-unit="{{ $part->satuan }}"
                                data-shelf="{{ $part->lokasi_rak ?: 'Utama' }}"
                                data-cat="{{ $part->kategori?->nama_kategori ?: 'General' }}"
                                data-price="{{ (float)$part->harga_jual }}"
                                data-buy="{{ (float)$part->harga_beli }}">
                                [{{ $part->kode_sparepart }}] {{ $part->nama_sparepart }} (Stok Saat Ini: {{ $part->stok }} {{ $part->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Live Delta Calculation Gauge -->
                <div class="wh-delta-box">
                    <div class="wh-delta-item">
                        <small>Stok Sistem Saat Ini</small>
                        <strong id="display-system-stock">{{ (int)($selected?->stok ?? 0) }}</strong>
                    </div>
                    <div class="wh-delta-item">
                        <small>Hasil Fisik Opname</small>
                        <strong id="display-new-stock" style="color:#2563eb;">{{ (int)old('stok_baru', $selected?->stok ?? 0) }}</strong>
                    </div>
                    <div class="wh-delta-item">
                        <small>Selisih Penyesuaian</small>
                        <strong id="display-delta-stock" style="color:#64748b;">0</strong>
                    </div>
                </div>

                <div class="wh-form-group">
                    <label for="stok_baru">Stok Fisik Aktual Hasil Hitung Opname <span style="color:#ef1b2d;">*</span></label>
                    <input type="number" min="0" name="stok_baru" id="stok_baru" class="wh-form-control" value="{{ old('stok_baru', $selected?->stok ?? 0) }}" required placeholder="Masukkan angka stok fisik yang ada di rak gudang" oninput="calculateDelta()">
                    <small style="color:#64748b; font-size:11.5px; margin-top:4px; display:block;">
                        Pastikan telah melakukan perhitungan fisik ganda bersama kepala gudang.
                    </small>
                </div>

                <div class="wh-form-group">
                    <label for="alasan">Klasifikasi Alasan Penyesuaian <span style="color:#ef1b2d;">*</span></label>
                    <select name="alasan" id="alasan" class="wh-form-control" required>
                        <option value="opname" @selected(old('alasan') === 'opname')>Stock Opname Rutin (Selisih Fisik)</option>
                        <option value="rusak" @selected(old('alasan') === 'rusak')>Barang Rusak / Kadaluwarsa / Cacat Pabrik</option>
                        <option value="hilang" @selected(old('alasan') === 'hilang')>Penyusutan / Selisih Tak Ditemukan</option>
                        <option value="koreksi" @selected(old('alasan') === 'koreksi')>Koreksi Kesalahan Input Data Sebelumnya</option>
                    </select>
                </div>

                <div class="wh-form-group">
                    <label for="catatan">Catatan Detail Berita Acara Opname</label>
                    <textarea name="catatan" id="catatan" class="wh-form-control" rows="3" placeholder="Tuliskan nomor berita acara atau keterangan penyebab selisih persediaan...">{{ old('catatan') }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px; padding-top:18px; border-top:1px solid #f1f5f9;">
                    <a href="{{ route('admin.inventory.stock') }}" class="wh-btn wh-btn-outline">Batal</a>
                    <button type="submit" class="wh-btn wh-btn-primary">✓ Simpan Penyesuaian Stok</button>
                </div>
            </form>
        </div>

        <!-- Sidebar Part Summary (Right Column) -->
        <div>
            <div class="wh-card">
                <div class="wh-card-header">
                    <h2 class="wh-card-title">📦 Info Spesifikasi Part</h2>
                    <span id="profile-code" style="font-family:ui-monospace, monospace; font-size:12px; font-weight:800; color:#ef1b2d;">
                        {{ $selected?->kode_sparepart ?: 'PRT-...' }}
                    </span>
                </div>

                <div class="wh-part-profile-box">
                    <strong id="profile-name" style="font-size:16px; color:#0f172a; display:block; margin-bottom:12px;">
                        {{ $selected?->nama_sparepart ?: 'Belum ada part yang dipilih' }}
                    </strong>

                    <div class="wh-part-stat-row">
                        <span>Kategori Spare Part:</span>
                        <strong id="profile-cat">{{ $selected?->kategori?->nama_kategori ?: '—' }}</strong>
                    </div>
                    <div class="wh-part-stat-row">
                        <span>Lokasi Rak Penyimpanan:</span>
                        <strong id="profile-shelf">{{ $selected?->lokasi_rak ?: 'Utama' }}</strong>
                    </div>
                    <div class="wh-part-stat-row">
                        <span>Satuan Satuan:</span>
                        <strong id="profile-unit">{{ $selected?->satuan ?: 'Pcs' }}</strong>
                    </div>
                    <div class="wh-part-stat-row">
                        <span>Harga Beli (Modal):</span>
                        <strong id="profile-buy">Rp {{ number_format((float)($selected?->harga_beli ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="wh-part-stat-row">
                        <span>Harga Jual Retail:</span>
                        <strong id="profile-price">Rp {{ number_format((float)($selected?->harga_jual ?? 0), 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:14px; font-size:12.5px; color:#1e40af; line-height:1.5;">
                    💡 <strong>Prosedur Standar Gudang YZA:</strong> Setiap koreksi selisih stok di atas 5 unit wajib dikonfirmasi bersama Kepala Bagian Operasional Bengkel.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentSystemStock = {{ (int)($selected?->stok ?? 0) }};

    function handlePartSelect(select) {
        const option = select.selectedOptions[0];
        if (!option || !option.value) return;

        currentSystemStock = parseInt(option.dataset.stock || 0);
        document.getElementById('display-system-stock').textContent = currentSystemStock;
        document.getElementById('stok_baru').value = currentSystemStock;
        
        document.getElementById('profile-code').textContent = option.dataset.code;
        document.getElementById('profile-name').textContent = option.dataset.name;
        document.getElementById('profile-cat').textContent = option.dataset.cat;
        document.getElementById('profile-shelf').textContent = option.dataset.shelf;
        document.getElementById('profile-unit').textContent = option.dataset.unit;
        document.getElementById('profile-buy').textContent = 'Rp ' + Number(option.dataset.buy || 0).toLocaleString('id-ID');
        document.getElementById('profile-price').textContent = 'Rp ' + Number(option.dataset.price || 0).toLocaleString('id-ID');

        calculateDelta();
    }

    function calculateDelta() {
        const newStockVal = document.getElementById('stok_baru').value;
        const newStock = newStockVal === '' ? 0 : parseInt(newStockVal);
        document.getElementById('display-new-stock').textContent = newStock;

        const delta = newStock - currentSystemStock;
        const deltaEl = document.getElementById('display-delta-stock');

        if (delta > 0) {
            deltaEl.textContent = '+' + delta;
            deltaEl.style.color = '#059669';
        } else if (delta < 0) {
            deltaEl.textContent = delta;
            deltaEl.style.color = '#dc2626';
        } else {
            deltaEl.textContent = '0';
            deltaEl.style.color = '#64748b';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        calculateDelta();
    });
</script>
@endsection
