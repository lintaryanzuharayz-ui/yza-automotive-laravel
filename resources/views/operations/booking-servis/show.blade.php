@extends('layouts.app')

@section('title', 'Detail Booking '.$booking->kode_booking.' · Service Advisor · YZA Automotive')

@section('content')
<div class="advisor-booking-detail-page">
    <style>
        .advisor-booking-detail-page {
            max-width: 1480px;
            margin: 0 auto;
            padding: 8px 16px 56px;
            font-family: inherit;
            color: #0f172a;
        }

        /* Hero Header */
        .adv-hero {
            background: linear-gradient(135deg, #090a0f 0%, #151821 55%, #1e2330 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 26px 32px;
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
        .adv-hero::before {
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
        .adv-hero-eyebrow {
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
        .adv-hero-eyebrow::before {
            content: '';
            width: 6px;
            height: 6px;
            background: #ef1b2d;
            border-radius: 50%;
        }
        .adv-hero h1 {
            font-size: clamp(24px, 3.2vw, 32px);
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 6px;
            color: #ffffff;
            line-height: 1.2;
        }
        .adv-hero p {
            color: #94a3b8;
            font-size: 13.5px;
            margin: 0;
            line-height: 1.5;
        }

        /* 2-Column Dynamic Layout */
        .adv-layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(360px, 1fr);
            gap: 24px;
            align-items: flex-start;
        }

        /* Clean SaaS Cards */
        .adv-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            transition: border-color 0.15s ease;
        }
        .adv-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f1f5f9;
        }
        .adv-card-title {
            font-size: 16px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Spec / Info Fact Items */
        .adv-spec-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .adv-spec-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
        }
        .adv-spec-item small {
            display: block;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }
        .adv-spec-item strong {
            display: block;
            font-size: 14.5px;
            font-weight: 800;
            color: #0f172a;
        }
        .adv-spec-item span {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        .adv-plat-pill {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            display: inline-block;
            background: #0f172a;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
            letter-spacing: 0.05em;
        }
        .adv-mono-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Customer Complaint Box */
        .adv-complaint-box {
            background: #f8fafc;
            border-left: 3px solid #ef1b2d;
            border-radius: 0 12px 12px 0;
            padding: 16px;
            font-size: 13.5px;
            line-height: 1.6;
            color: #334155;
            margin-top: 8px;
        }

        /* Modular Check-in Form Inputs */
        .adv-odometer-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 20px;
        }
        .adv-odometer-input-wrap {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.15s ease;
            margin-top: 8px;
        }
        .adv-odometer-input-wrap:focus-within {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 4px rgba(239, 27, 45, 0.12);
        }
        .adv-odometer-field {
            flex: 1;
            border: none;
            padding: 14px 18px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            outline: none;
        }
        .adv-odometer-unit {
            padding: 14px 18px;
            font-weight: 800;
            font-size: 14px;
            color: #64748b;
            background: #f1f5f9;
            border-left: 1px solid #e2e8f0;
        }

        /* Visual Checklist Grid */
        .adv-checklist-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 14px 0 20px;
        }
        .adv-check-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .adv-check-pill:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .adv-check-pill input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #ef1b2d;
            cursor: pointer;
        }
        .adv-check-pill span {
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
        }

        .adv-form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 13.5px;
            outline: none;
            box-sizing: border-box;
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .adv-form-control:focus {
            border-color: #ef1b2d;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.12);
        }

        /* Status & Timeline Sidebar */
        .adv-sticky-sidebar {
            position: sticky;
            top: 24px;
        }
        .adv-timeline {
            position: relative;
            padding-left: 20px;
            margin: 18px 0;
        }
        .adv-timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: #e2e8f0;
        }
        .adv-timeline-step {
            position: relative;
            margin-bottom: 16px;
        }
        .adv-timeline-step:last-child {
            margin-bottom: 0;
        }
        .adv-timeline-dot {
            position: absolute;
            left: -20px;
            top: 3px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #cbd5e1;
        }
        .adv-timeline-step.is-done .adv-timeline-dot {
            border-color: #10b981;
            background: #10b981;
        }
        .adv-timeline-step.is-active .adv-timeline-dot {
            border-color: #ef1b2d;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(239, 27, 45, 0.2);
        }
        .adv-timeline-step strong {
            display: block;
            font-size: 13px;
            color: #0f172a;
        }
        .adv-timeline-step small {
            display: block;
            font-size: 11px;
            color: #64748b;
        }

        /* Badges & Buttons */
        .adv-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .adv-badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .badge-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }
        .badge-pending .adv-badge-dot { background: #f59e0b; }
        .badge-confirmed {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }
        .badge-confirmed .adv-badge-dot { background: #3b82f6; }
        .badge-completed {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.25);
        }
        .badge-completed .adv-badge-dot { background: #10b981; }
        .badge-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.25);
        }
        .badge-cancelled .adv-badge-dot { background: #ef4444; }

        .adv-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 11px;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .adv-btn-primary { background: #ef1b2d; color: #fff; }
        .adv-btn-primary:hover { background: #dc1829; box-shadow: 0 4px 14px rgba(239, 27, 45, 0.3); }
        .adv-btn-dark { background: #0f172a; color: #fff; }
        .adv-btn-dark:hover { background: #1e293b; }
        .adv-btn-success { background: #059669; color: #fff; }
        .adv-btn-success:hover { background: #047857; }
        .adv-btn-outline { background: #fff; color: #0f172a; border-color: #cbd5e1; }
        .adv-btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
        .adv-btn-danger-outline { background: #fff; color: #ef1b2d; border-color: #fecaca; }
        .adv-btn-danger-outline:hover { background: #fef2f2; }

        @media (max-width: 1024px) {
            .adv-layout-grid { grid-template-columns: 1fr; }
            .adv-sticky-sidebar { position: static; }
        }
        @media (max-width: 640px) {
            .adv-spec-grid { grid-template-columns: 1fr; }
            .adv-checklist-grid { grid-template-columns: 1fr; }
            .adv-hero { padding: 20px; }
        }
    </style>

    <!-- Header Section -->
    <header class="adv-hero">
        <div>
            <div class="adv-hero-eyebrow">Service Advisor Intake Check-in</div>
            <h1>Booking {{ $booking->kode_booking }}</h1>
            <p>{{ $booking->kendaraan?->merk }} {{ $booking->kendaraan?->model }} · {{ $booking->kendaraan?->nomor_polisi ?: 'Plat Nomor Belum Diisi' }}</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
            @php
                $badge = match($booking->status) {
                    'menunggu' => ['cls' => 'badge-pending', 'lbl' => 'Pending (Menunggu)'],
                    'diterima' => ['cls' => 'badge-confirmed', 'lbl' => 'Dikonfirmasi (Siap Check-in)'],
                    'diproses' => ['cls' => 'badge-confirmed', 'lbl' => 'Work Order Aktif'],
                    'selesai' => ['cls' => 'badge-completed', 'lbl' => 'Selesai'],
                    'ditolak', 'dibatalkan' => ['cls' => 'badge-cancelled', 'lbl' => ucfirst($booking->status)],
                    default => ['cls' => 'badge-pending', 'lbl' => ucfirst($booking->status)],
                };
            @endphp
            <span class="adv-badge {{ $badge['cls'] }}">
                <span class="adv-badge-dot"></span>
                {{ $badge['lbl'] }}
            </span>
            <a href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index') }}" class="adv-btn adv-btn-outline" style="border-color:rgba(255,255,255,0.25); color:#fff; background:rgba(255,255,255,0.06);">
                ← Kembali ke Antrean
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

    <!-- Dynamic 2-Column SaaS Layout -->
    <div class="adv-layout-grid">
        <!-- Main Workspace (Left Column) -->
        <div>
            <!-- Card 1: Data Spesifikasi Unit & Pelanggan -->
            <div class="adv-card">
                <div class="adv-card-header">
                    <h2 class="adv-card-title">🚗 Identitas Kendaraan & Pemilik</h2>
                    <span class="adv-plat-pill">{{ $booking->kendaraan?->nomor_polisi ?: 'PLAT —' }}</span>
                </div>
                <div class="adv-spec-grid">
                    <div class="adv-spec-item">
                        <small>Nama Customer / Pemilik</small>
                        <strong>{{ $booking->pelanggan?->nama_lengkap ?: 'Customer Umum' }}</strong>
                        <span>📞 {{ $booking->pelanggan?->no_telepon ?: '—' }}</span>
                    </div>
                    <div class="adv-spec-item">
                        <small>Unit Kendaraan</small>
                        <strong>{{ $booking->kendaraan?->merk }} {{ $booking->kendaraan?->model }}</strong>
                        <span>Tahun Produksi: {{ $booking->kendaraan?->tahun ?: '—' }}</span>
                    </div>
                    <div class="adv-spec-item">
                        <small>Paket Layanan Servis</small>
                        <strong>{{ $booking->jenisServis?->nama_servis ?: 'General Service' }}</strong>
                        <span>Estimasi Durasi: ± 1-2 Jam</span>
                    </div>
                    <div class="adv-spec-item">
                        <small>Jadwal Kedatangan Booking</small>
                        <strong>{{ \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('d M Y') }}</strong>
                        <span>Pukul {{ substr((string)$booking->jam, 0, 5) }} WIB</span>
                    </div>
                </div>

                <div style="margin-top:20px;">
                    <small style="font-weight:700; text-transform:uppercase; font-size:11px; color:#64748b;">Keluhan & Catatan Masuk Pelanggan:</small>
                    <div class="adv-complaint-box">
                        "{{ $booking->keluhan ?: 'Tidak ada catatan keluhan khusus yang dicantumkan saat reservasi online.' }}"
                    </div>
                </div>
            </div>

            <!-- Card 2: Modular Intake Check-in Form -->
            <div class="adv-card">
                <div class="adv-card-header">
                    <h2 class="adv-card-title">📋 Formulir Serah Terima Fisik (Intake Check-in)</h2>
                    <span style="font-size:12px; color:#64748b;">Langkah Service Advisor</span>
                </div>

                @if($booking->status === 'menunggu')
                    <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:16px; margin-bottom:16px;">
                        <strong style="color:#92400e; font-size:14px; display:block;">Reservasi Masih Berstatus Pending</strong>
                        <p style="color:#b45309; font-size:12.5px; margin:4px 0 0;">
                            Silakan konfirmasi penerimaan reservasi terlebih dahulu sebelum melakukan serah terima fisik kendaraan.
                        </p>
                    </div>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <form method="POST" action="{{ route($role==='admin'?'admin.booking-servis.action':'petugas.booking-servis.action', [$booking, 'confirm']) }}" onsubmit="return confirm('Konfirmasi dan terima booking ini?')">
                            @csrf
                            <button type="submit" class="adv-btn adv-btn-success">✓ Konfirmasi & Terima Booking</button>
                        </form>
                        <form method="POST" action="{{ route($role==='admin'?'admin.booking-servis.action':'petugas.booking-servis.action', [$booking, 'reject']) }}" onsubmit="return confirm('Tolak booking ini?')">
                            @csrf
                            <button type="submit" class="adv-btn adv-btn-danger-outline">✕ Tolak Reservasi</button>
                        </form>
                    </div>

                @elseif($booking->status === 'diterima' && !$booking->servis)
                    <form method="POST" action="{{ route($role==='admin'?'admin.servis.from-booking':'petugas.servis.from-booking', $booking) }}" id="intake-form">
                        @csrf
                        
                        <!-- Input Odometer Modern -->
                        <div class="adv-odometer-box">
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#0f172a;" for="kilometer">
                                Angka Kilometer Odometer Saat Masuk Bengkel <span style="color:#ef1b2d;">*</span>
                            </label>
                            <div class="adv-odometer-input-wrap">
                                <input class="adv-odometer-field" type="number" id="kilometer" name="kilometer" min="{{ (int)($booking->kendaraan?->kilometer ?? 0) }}" value="{{ old('kilometer', $booking->kendaraan?->kilometer) }}" required placeholder="Contoh: 45000">
                                <span class="adv-odometer-unit">KM</span>
                            </div>
                            <small style="color:#64748b; font-size:11.5px; margin-top:6px; display:block;">
                                Odometer tercatat sebelumnya: <strong>{{ number_format((int)($booking->kendaraan?->kilometer ?? 0)) }} km</strong>.
                            </small>
                        </div>

                        <!-- Visual Checklist Kondisi Fisik -->
                        <div>
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#0f172a; margin-bottom:4px;">
                                Visual Checklist Kondisi Awal Unit (Centang untuk menambahkan catatan otomatis)
                            </label>
                            <div class="adv-checklist-grid">
                                <label class="adv-check-pill">
                                    <input type="checkbox" onchange="toggleChecklistItem(this, 'Bahan bakar 1/2 tangki atau lebih')">
                                    <span>⛽ Bahan Bakar Aman (≥ 1/2)</span>
                                </label>
                                <label class="adv-check-pill">
                                    <input type="checkbox" onchange="toggleChecklistItem(this, 'Ban serep & toolkit lengkap')">
                                    <span>🛞 Ban Serep & Toolkit Lengkap</span>
                                </label>
                                <label class="adv-check-pill">
                                    <input type="checkbox" onchange="toggleChecklistItem(this, 'Barang berharga telah dikosongkan dari kabin')">
                                    <span>👜 Barang Berharga Dikosongkan</span>
                                </label>
                                <label class="adv-check-pill">
                                    <input type="checkbox" onchange="toggleChecklistItem(this, 'STNK asli terlampir bersama kunci')">
                                    <span>📄 STNK Terlampir</span>
                                </label>
                                <label class="adv-check-pill">
                                    <input type="checkbox" onchange="toggleChecklistItem(this, 'Kondisi bodi eksterior mulus tanpa baret signifikan')">
                                    <span>✨ Bodi Eksterior Mulus</span>
                                </label>
                                <label class="adv-check-pill">
                                    <input type="checkbox" onchange="toggleChecklistItem(this, 'Terdapat baret/lecet pemakaian pada bodi kendaraan')">
                                    <span>🔍 Ada Baret / Lecet Halus</span>
                                </label>
                            </div>
                        </div>

                        <!-- Catatan Tambahan -->
                        <div style="margin-bottom:22px;">
                            <label style="display:block; font-size:12.5px; font-weight:700; color:#0f172a; margin-bottom:6px;" for="catatan">
                                Catatan Tambahan / Hasil Pemeriksaan Serah Terima
                            </label>
                            <textarea class="adv-form-control" id="catatan" name="catatan" rows="4" placeholder="Catat kondisi fisik unit saat diterima dari customer...">{{ old('catatan', $booking->catatan) }}</textarea>
                        </div>

                        <button type="submit" class="adv-btn adv-btn-primary" style="width:100%; padding:14px; font-size:14px;">
                            🚗 Simpan Check-in & Terbitkan Work Order (WO)
                        </button>
                    </form>

                @elseif($booking->servis)
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                        <div>
                            <span style="font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase;">Work Order Telah Terbit</span>
                            <div class="adv-mono-code" style="font-size:18px; color:#0f172a; margin-top:2px;">{{ $booking->servis->kode_servis }}</div>
                            <span style="font-size:12px; color:#64748b;">Status: {{ \App\Services\ServiceStatusPresenter::label($booking->servis->status) }}</span>
                        </div>
                        <a href="{{ route($role==='admin'?'admin.servis.show':'petugas.servis.show', $booking->servis) }}" class="adv-btn adv-btn-dark">
                            Buka Work Order Teknisi →
                        </a>
                    </div>
                @else
                    <p style="color:#64748b; font-size:13px; margin:0;">Status reservasi ini sudah ditutup.</p>
                @endif
            </div>
        </div>

        <!-- Sticky Sidebar (Right Column) -->
        <div class="adv-sticky-sidebar">
            <div class="adv-card">
                <div class="adv-card-header">
                    <h2 class="adv-card-title">⚡ Ringkasan Status Alur</h2>
                    <span class="adv-mono-code" style="font-size:11px; color:#64748b;">{{ $booking->kode_booking }}</span>
                </div>

                <div class="adv-timeline">
                    <div class="adv-timeline-step is-done">
                        <div class="adv-timeline-dot"></div>
                        <strong>1. Reservasi Booking Masuk</strong>
                        <small>{{ $booking->created_at?->format('d M Y, H:i') }}</small>
                    </div>
                    <div class="adv-timeline-step {{ in_array($booking->status, ['diterima', 'diproses', 'selesai']) ? 'is-done' : ($booking->status === 'menunggu' ? 'is-active' : '') }}">
                        <div class="adv-timeline-dot"></div>
                        <strong>2. Konfirmasi Service Advisor</strong>
                        <small>{{ $booking->status === 'menunggu' ? 'Menunggu konfirmasi slot' : 'Terkonfirmasi siap datang' }}</small>
                    </div>
                    <div class="adv-timeline-step {{ $booking->servis ? 'is-done' : ($booking->status === 'diterima' ? 'is-active' : '') }}">
                        <div class="adv-timeline-dot"></div>
                        <strong>3. Intake Check-in & Terbit WO</strong>
                        <small>{{ $booking->servis ? 'WO: '.$booking->servis->kode_servis : 'Serah terima fisik unit di bengkel' }}</small>
                    </div>
                    <div class="adv-timeline-step {{ $booking->servis && in_array($booking->servis->status, ['dikerjakan', 'selesai']) ? 'is-done' : '' }}">
                        <div class="adv-timeline-dot"></div>
                        <strong>4. Diagnosis & Pengerjaan Mekanik</strong>
                        <small>Inspeksi & alokasi suku cadang</small>
                    </div>
                    <div class="adv-timeline-step {{ $booking->status === 'selesai' ? 'is-done' : '' }}">
                        <div class="adv-timeline-dot"></div>
                        <strong>5. Selesai & Kasir Finance</strong>
                        <small>Quality check & pembayaran</small>
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:16px; border-top:1px solid #f1f5f9;">
                    <div style="font-size:12px; color:#64748b; margin-bottom:8px;">Petugas Service Advisor:</div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:50%; background:#0f172a; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px;">
                            {{ strtoupper(substr(auth()->user()->displayName(), 0, 1)) }}
                        </div>
                        <div>
                            <strong style="display:block; font-size:13px; color:#0f172a;">{{ auth()->user()->displayName() }}</strong>
                            <small style="color:#64748b; font-size:11px;">Service Advisor On Duty</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleChecklistItem(checkbox, text) {
        const textarea = document.getElementById('catatan');
        if (!textarea) return;

        let lines = textarea.value.split('\n').filter(l => l.trim() !== '');
        const bullet = '• ' + text;

        if (checkbox.checked) {
            if (!lines.includes(bullet)) {
                lines.push(bullet);
            }
        } else {
            lines = lines.filter(l => l !== bullet);
        }

        textarea.value = lines.join('\n');
    }
</script>
@endsection
