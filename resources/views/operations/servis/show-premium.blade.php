@extends('layouts.app')

@section('title', 'Work Order '.$servis->kode_servis.' · YZA Automotive')

@section('content')
<div class="wo-premium-page">
    <style>
        .wo-premium-page {
            max-width: 1540px;
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
            font-size: 13.5px;
            margin: 0;
            line-height: 1.5;
        }

        /* 2-Column Command Center Layout */
        .wo-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(380px, 1fr);
            gap: 24px;
            align-items: flex-start;
        }

        /* Cards & Surfaces */
        .wo-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease;
        }
        .wo-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .wo-card-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Vehicle Fact Badges */
        .wo-facts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .wo-fact-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
        }
        .wo-fact-item small {
            display: block;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }
        .wo-fact-item strong {
            display: block;
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .wo-complaint-box {
            background: #f8fafc;
            border-left: 3px solid #ef1b2d;
            border-radius: 0 10px 10px 0;
            padding: 14px;
            font-size: 13px;
            line-height: 1.5;
            color: #334155;
        }

        /* Forms & Inputs */
        .wo-form-group {
            margin-bottom: 16px;
        }
        .wo-form-group label {
            display: block;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .wo-form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13px;
            outline: none;
            box-sizing: border-box;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .wo-form-control:focus {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.12);
        }

        /* Quick Symptom Chips */
        .wo-symptom-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin: 8px 0 14px;
        }
        .wo-chip {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .wo-chip:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* Modern Tables */
        .wo-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 12px;
            margin-top: 12px;
            background: #ffffff;
        }
        .wo-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            text-align: left;
        }
        .wo-table th {
            background: #f8fafc;
            color: #475569;
            padding: 10px 14px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid #e2e8f0;
        }
        .wo-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .wo-table tbody tr:hover {
            background: #f8fafc;
        }
        .wo-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Monospace Identifiers */
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
        .wo-mono-price {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-weight: 700;
            color: #0f172a;
        }

        /* Highlighted Estimated Cost Container Widget */
        .wo-cost-widget {
            background: linear-gradient(135deg, #090a0f 0%, #151821 60%, #1e2330 100%);
            color: #ffffff;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
        }
        .wo-cost-widget::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle, rgba(239, 27, 45, 0.18) 0%, rgba(239, 27, 45, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .wo-cost-widget-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .wo-cost-stat-callout {
            margin: 10px 0 18px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .wo-cost-stat-label {
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }
        .wo-cost-stat-number {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: clamp(26px, 2.5vw, 32px);
            font-weight: 900;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-top: 4px;
        }
        .wo-cost-stat-number span {
            color: #ef1b2d;
        }
        .wo-cost-breakdown-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            padding: 6px 0;
            color: #cbd5e1;
        }
        .wo-cost-breakdown-row strong {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            color: #ffffff;
        }

        /* Status Badges */
        .wo-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
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

        /* Buttons */
        .wo-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .wo-btn-primary { background: #ef1b2d; color: #fff; }
        .wo-btn-primary:hover { background: #dc1829; box-shadow: 0 4px 14px rgba(239, 27, 45, 0.3); }
        .wo-btn-dark { background: #0f172a; color: #fff; }
        .wo-btn-dark:hover { background: #1e293b; }
        .wo-btn-success { background: #059669; color: #fff; }
        .wo-btn-success:hover { background: #047857; box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3); }
        .wo-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .wo-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
        .wo-btn-sm { padding: 6px 12px; font-size: 11.5px; border-radius: 8px; }

        @media (max-width: 1024px) {
            .wo-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .wo-facts-grid { grid-template-columns: 1fr; }
            .wo-hero { padding: 20px; }
        }
    </style>

    <!-- Header Section -->
    <header class="wo-hero">
        <div>
            <div class="wo-hero-eyebrow">Work Order Command Center · {{ $servis->kode_servis }}</div>
            <h1>{{ $servis->kendaraan?->merk }} {{ $servis->kendaraan?->model }}</h1>
            <p>{{ $servis->kendaraan?->nomor_polisi ?: 'Plat Nomor Belum Diisi' }} · Pemilik: {{ $servis->pelanggan?->nama_lengkap ?: 'Customer' }} · Odometer Masuk: {{ number_format((int)$servis->kilometer) }} km</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            @php
                $badge = match($servis->status) {
                    'pemeriksaan' => ['cls' => 'badge-inspection', 'lbl' => 'Inspeksi & Diagnosis Awal'],
                    'dikerjakan' => ['cls' => 'badge-working', 'lbl' => 'Sedang Dikerjakan (Bay Aktif)'],
                    'selesai' => ['cls' => 'badge-completed', 'lbl' => 'Servis Selesai'],
                    default => ['cls' => 'badge-inspection', 'lbl' => ucfirst($servis->status)],
                };
            @endphp
            <span class="wo-badge {{ $badge['cls'] }}">
                <span class="wo-badge-dot"></span>
                {{ $badge['lbl'] }}
            </span>
            <a href="{{ route(request()->is('admin/*') ? 'admin.servis.index' : 'petugas.servis.index') }}" class="wo-btn wo-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
                ← Daftar Work Order
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

    <!-- Command Center 2-Column Grid -->
    <div class="wo-layout">
        <!-- Left Column: Specs, Diagnosis & Jasa Servis -->
        <div>
            <!-- Card 1: Data Unit & Customer -->
            <div class="wo-card">
                <div class="wo-card-header">
                    <h2 class="wo-card-title">🚗 Identitas Kendaraan & Riwayat Masuk</h2>
                    <span class="wo-plat-pill">{{ $servis->kendaraan?->nomor_polisi ?: 'PLAT —' }}</span>
                </div>
                <div class="wo-facts-grid">
                    <div class="wo-fact-item">
                        <small>Pelanggan</small>
                        <strong>{{ $servis->pelanggan?->nama_lengkap ?: 'Customer' }}</strong>
                        <span style="font-size:12px; color:#64748b;">📞 {{ $servis->pelanggan?->no_telepon ?: '—' }}</span>
                    </div>
                    <div class="wo-fact-item">
                        <small>Unit Mobil</small>
                        <strong>{{ $servis->kendaraan?->merk }} {{ $servis->kendaraan?->model }}</strong>
                        <span style="font-size:12px; color:#64748b;">Tahun {{ $servis->kendaraan?->tahun ?: '—' }}</span>
                    </div>
                    <div class="wo-fact-item">
                        <small>Waktu Masuk Bengkel</small>
                        <strong>{{ $servis->tanggal_masuk?->translatedFormat('d M Y, H:i') ?? $servis->created_at?->format('d/m/Y H:i') }} WIB</strong>
                    </div>
                    <div class="wo-fact-item">
                        <small>Odometer Saat Masuk</small>
                        <strong style="font-family:ui-monospace, monospace;">{{ number_format((int)$servis->kilometer) }} km</strong>
                    </div>
                </div>

                <div style="margin-top:16px;">
                    <small style="font-weight:700; text-transform:uppercase; font-size:11px; color:#64748b;">Keluhan Pelanggan Saat Reservasi / Check-in:</small>
                    <div class="wo-complaint-box" style="margin-top:6px;">
                        "{{ $servis->keluhan ?: 'Tidak ada keluhan khusus dari pelanggan.' }}"
                    </div>
                </div>
            </div>

            <!-- Card 2: Box Diagnosis & Pemeriksaan Teknis -->
            <div class="wo-card">
                <div class="wo-card-header">
                    <h2 class="wo-card-title">🔍 Diagnosis & Temuan Kerusakan Fisik</h2>
                    @if($servis->status === 'pemeriksaan')
                        <span style="font-size:11px; font-weight:700; color:#d97706; background:rgba(245,158,11,0.1); padding:3px 8px; border-radius:6px;">Tahap Inspeksi</span>
                    @endif
                </div>

                @if($servis->status === 'pemeriksaan')
                    <p style="font-size:13px; color:#64748b; margin-bottom:12px;">
                        Lakukan inspeksi pada komponen utama. Klik chip diagnosa cepat di bawah untuk memasukkan temuan umum secara instan:
                    </p>

                    <!-- Quick Symptom Chips -->
                    <div class="wo-symptom-chips">
                        <button type="button" class="wo-chip" onclick="appendDiagnosis('• Kampas rem depan/belakang menipis aus')">+ Kampas Rem Aus</button>
                        <button type="button" class="wo-chip" onclick="appendDiagnosis('• Oli mesin kotor & interval pergantian terlewati')">+ Ganti Oli & Filter</button>
                        <button type="button" class="wo-chip" onclick="appendDiagnosis('• Bushing arm & link stabilizer oblak/retak')">+ Kaki-kaki Oblak</button>
                        <button type="button" class="wo-chip" onclick="appendDiagnosis('• Busi aus & filter udara kotor')">+ Tune-up Busi/Filter</button>
                        <button type="button" class="wo-chip" onclick="appendDiagnosis('• Kondisi tegangan aki lemah / drop')">+ Pengecekan Aki</button>
                        <button type="button" class="wo-chip" onclick="appendDiagnosis('• Filter AC kotor & hembusan kurang dingin')">+ Servis AC Kabin</button>
                    </div>

                    <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.servis.inspection' : 'petugas.servis.inspection', $servis) }}">
                        @csrf
                        <div class="wo-form-group">
                            <label for="hasil_pemeriksaan">Hasil Pemeriksaan / Temuan Kerusakan Mekanik <span style="color:#ef1b2d;">*</span></label>
                            <textarea class="wo-form-control" name="hasil_pemeriksaan" id="hasil_pemeriksaan" rows="4" required placeholder="Catat kondisi komponen aus/rusak yang ditemukan saat inspeksi lift...">{{ old('hasil_pemeriksaan', $servis->hasil_pemeriksaan) }}</textarea>
                        </div>
                        <div class="wo-form-group">
                            <label for="catatan">Catatan / Rekomendasi Tambahan Teknisi</label>
                            <textarea class="wo-form-control" name="catatan" id="catatan" rows="2" placeholder="Catatan estimasi pengerjaan atau saran suku cadang pengganti...">{{ old('catatan', $servis->catatan) }}</textarea>
                        </div>
                        <button type="submit" class="wo-btn wo-btn-dark" style="width:100%; padding:12px;">
                            ✓ Simpan Diagnosis & Masuk Tahap Pengerjaan (Bay)
                        </button>
                    </form>
                @else
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px;">
                        <small style="font-weight:700; text-transform:uppercase; font-size:11px; color:#64748b;">Temuan Kerusakan Tercatat:</small>
                        <div style="margin:6px 0 14px; font-size:13.5px; color:#0f172a; line-height:1.6; white-space:pre-line;">
                            {{ $servis->hasil_pemeriksaan ?: 'Belum ada catatan temuan inspeksi.' }}
                        </div>
                        @if($servis->catatan)
                            <small style="font-weight:700; text-transform:uppercase; font-size:11px; color:#64748b;">Rekomendasi Teknisi:</small>
                            <div style="margin:4px 0 0; font-size:13px; color:#475569;">
                                {{ $servis->catatan }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Card 3: Rincian Jasa Servis -->
            <div class="wo-card">
                <div class="wo-card-header">
                    <h2 class="wo-card-title">🛠️ Alokasi Jasa & Tenaga Kerja Mekanik</h2>
                    <span style="font-size:12px; color:#64748b;">{{ $servis->details->count() }} Item Pekerjaan</span>
                </div>

                @if($servis->status === 'dikerjakan')
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:18px; margin-bottom:18px;">
                        <strong style="display:block; font-size:13px; color:#0f172a; margin-bottom:12px;">+ Tambah Pekerjaan / Jasa Servis</strong>
                        <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.servis.detail.store' : 'petugas.servis.detail.store', $servis) }}">
                            @csrf
                            <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:12px; margin-bottom:12px;">
                                <div>
                                    <label style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Pilih Paket Servis</label>
                                    <select class="wo-form-control" name="id_jenis_servis" onchange="if(this.selectedOptions[0].dataset.name){document.getElementById('input-desc').value=this.selectedOptions[0].dataset.name;document.getElementById('input-cost').value=this.selectedOptions[0].dataset.price;}">
                                        <option value="">-- Jasa Kustom / Manual --</option>
                                        @foreach(App\Models\JenisServis::where('status','aktif')->orderBy('nama_servis')->get() as $st)
                                            <option value="{{ $st->id_jenis_servis }}" data-name="{{ $st->nama_servis }}" data-price="{{ (float)$st->estimasi_harga }}">
                                                {{ $st->nama_servis }} (Rp {{ number_format($st->estimasi_harga, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Tarif Jasa (Rp) *</label>
                                    <input class="wo-form-control" type="number" min="0" step="0.01" id="input-cost" name="biaya" placeholder="Tarif jasa perbaikan" required>
                                </div>
                            </div>
                            <div style="margin-bottom:14px;">
                                <label style="font-size:11.5px; font-weight:700; color:#475569; display:block; margin-bottom:4px;">Deskripsi Item Pekerjaan *</label>
                                <input class="wo-form-control" type="text" id="input-desc" name="deskripsi_pekerjaan" placeholder="Contoh: Kuras & Ganti Oli Mesin, Spooring Balancing 4 Roda" required>
                            </div>
                            <button type="submit" class="wo-btn wo-btn-dark wo-btn-sm">+ Simpan Jasa Pekerjaan</button>
                        </form>
                    </div>
                @endif

                <div class="wo-table-wrap">
                    <table class="wo-table">
                        <thead>
                            <tr>
                                <th>Item Pekerjaan</th>
                                <th>Kategori</th>
                                <th style="text-align:right;">Tarif Jasa (Rp)</th>
                                @if($servis->status === 'dikerjakan')
                                    <th style="width:30px;"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servis->details as $d)
                                <tr>
                                    <td>
                                        <strong style="color:#0f172a;">{{ $d->deskripsi_pekerjaan }}</strong>
                                        @if($d->catatan)
                                            <small style="color:#64748b; display:block;">{{ $d->catatan }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span style="font-size:11px; color:#64748b; background:#f1f5f9; padding:2px 8px; border-radius:6px;">
                                            {{ $d->jenisServis?->nama_servis ?: 'Kustom' }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;">
                                        <span class="wo-mono-price">{{ number_format((float)$d->biaya, 0, ',', '.') }}</span>
                                    </td>
                                    @if($servis->status === 'dikerjakan')
                                        <td style="text-align:center;">
                                            <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.servis.detail.destroy' : 'petugas.servis.detail.destroy', [$servis, $d]) }}" onsubmit="return confirm('Hapus detail jasa ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background:none; border:none; color:#ef1b2d; cursor:pointer; font-weight:800; font-size:14px;" title="Hapus">✕</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:20px; color:#64748b;">Belum ada rincian jasa pekerjaan teknisi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Spare Parts & Highlighted Cost Widget -->
        <div>
            <!-- Highlighted Estimated Cost Container Widget -->
            @php
                $biayaJasa = (float)$servis->details->sum('biaya');
                $biayaPart = (float)$servis->sparepartItems->sum('subtotal');
                $totalEstimasi = $biayaJasa + $biayaPart;
            @endphp
            <div class="wo-cost-widget">
                <div class="wo-cost-widget-title">
                    <span>⚡ ESTIMASI BIAYA REAL-TIME</span>
                    <span style="background:rgba(239,27,45,0.2); color:#ff6b77; padding:2px 8px; border-radius:999px; font-size:10px;">COMMAND CENTER</span>
                </div>

                <div class="wo-cost-stat-callout">
                    <div class="wo-cost-stat-label">Total Tagihan Servis Saat Ini</div>
                    <div class="wo-cost-stat-number">
                        <span>Rp</span> {{ number_format($totalEstimasi, 0, ',', '.') }}
                    </div>
                </div>

                <div class="wo-cost-breakdown-row">
                    <span>Subtotal Jasa Teknisi:</span>
                    <strong>Rp {{ number_format($biayaJasa, 0, ',', '.') }}</strong>
                </div>
                <div class="wo-cost-breakdown-row">
                    <span>Subtotal Suku Cadang:</span>
                    <strong>Rp {{ number_format($biayaPart, 0, ',', '.') }}</strong>
                </div>

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.1);">
                    @if($servis->status === 'dikerjakan')
                        <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.servis.finalize' : 'petugas.servis.finalize', $servis) }}" onsubmit="return confirm('Pastikan seluruh jasa dan spare part sudah selesai diinput. Finalisasi pengerjaan sekarang?')">
                            @csrf
                            <button type="submit" class="wo-btn wo-btn-success" style="width:100%; padding:13px; font-size:13.5px;">
                                ✓ Finalisasi Servis & Buat Tagihan
                            </button>
                        </form>
                    @elseif($servis->transaksi)
                        <a href="{{ route(request()->is('admin/*') ? 'admin.invoice.servis' : 'petugas.invoice.servis', $servis->transaksi) }}" target="_blank" class="wo-btn wo-btn-primary" style="width:100%; padding:12px; box-sizing:border-box;">
                            📄 Buka / Cetak Invoice Resmi →
                        </a>
                    @else
                        <span style="font-size:12px; color:#94a3b8; text-align:center; display:block;">
                            Status Servis: <strong>{{ \App\Services\ServiceStatusPresenter::label($servis->status) }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Card 4: Suku Cadang & Spare Parts Alokasi -->
            <div class="wo-card">
                <div class="wo-card-header">
                    <h2 class="wo-card-title">⚙️ Alokasi Suku Cadang (Part)</h2>
                    <span style="font-size:12px; color:#64748b;">{{ $servis->sparepartItems->count() }} Part Terpasang</span>
                </div>

                @if($servis->status === 'dikerjakan')
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:16px; margin-bottom:16px;">
                        <strong style="display:block; font-size:12.5px; color:#0f172a; margin-bottom:8px;">+ Ambil Part dari Gudang</strong>
                        <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.servis.sparepart.store' : 'petugas.servis.sparepart.store', $servis) }}">
                            @csrf
                            <div class="wo-form-group">
                                <select class="wo-form-control" name="id_sparepart" required>
                                    <option value="">-- Pilih Suku Cadang Tersedia --</option>
                                    @foreach(App\Models\Sparepart::where('status','aktif')->where('stok','>',0)->orderBy('nama_sparepart')->get() as $part)
                                        <option value="{{ $part->id_sparepart }}">
                                            {{ $part->nama_sparepart }} (Stok: {{ $part->stok }} {{ $part->satuan }}) · Rp {{ number_format((float)$part->harga_jual, 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <input class="wo-form-control" type="number" min="1" value="1" name="jumlah" placeholder="Qty" style="width:90px;" required>
                                <button type="submit" class="wo-btn wo-btn-dark wo-btn-sm" style="flex:1;">+ Tambahkan ke WO</button>
                            </div>
                        </form>
                    </div>
                @endif

                <div class="wo-table-wrap">
                    <table class="wo-table">
                        <thead>
                            <tr>
                                <th>Spare Part</th>
                                <th style="text-align:center;">Qty</th>
                                <th style="text-align:right;">Subtotal</th>
                                @if($servis->status === 'dikerjakan')
                                    <th style="width:24px;"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servis->sparepartItems as $item)
                                <tr>
                                    <td>
                                        <strong style="color:#0f172a;">{{ $item->sparepart?->nama_sparepart }}</strong>
                                        <small style="color:#64748b; font-size:10.5px; display:block;">@ Rp {{ number_format((float)$item->harga_satuan, 0, ',', '.') }}</small>
                                    </td>
                                    <td style="text-align:center; font-weight:700;">{{ $item->jumlah }}</td>
                                    <td style="text-align:right;">
                                        <span class="wo-mono-price">{{ number_format((float)$item->subtotal, 0, ',', '.') }}</span>
                                    </td>
                                    @if($servis->status === 'dikerjakan')
                                        <td style="text-align:center;">
                                            <form method="POST" action="{{ route(request()->is('admin/*') ? 'admin.servis.sparepart.destroy' : 'petugas.servis.sparepart.destroy', [$servis, $item]) }}" onsubmit="return confirm('Hapus spare part ini dari servis?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="background:none; border:none; color:#ef1b2d; cursor:pointer; font-weight:800;" title="Hapus">✕</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; padding:18px; color:#64748b;">Belum ada spare part yang dialokasikan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function appendDiagnosis(text) {
        const textarea = document.getElementById('hasil_pemeriksaan');
        if (!textarea) return;
        
        let val = textarea.value.trim();
        if (val.length > 0) {
            val += '\n' + text;
        } else {
            val = text;
        }
        textarea.value = val;
    }
</script>
@endsection
