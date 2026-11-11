@extends('layouts.app')

@section('title', 'Detail Booking ' . $booking->kode_booking . ' · YZA Automotive')
@section('admin-page-title', 'Detail Booking Servis')

@section('content')
<div class="admin-booking-detail-page">
    <style>
        .admin-booking-detail-page {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .b-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .b-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748B;
            margin-bottom: 8px;
        }
        .b-back-arrow { font-size: 16px; font-weight: 700; color: #94A3B8; }
        .b-heading {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            color: #0F172A;
            letter-spacing: -0.02em;
        }

        .admin-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
        }
        .admin-alert.is-success {
            background: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }
        .admin-alert.is-error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        .b-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.8fr);
            gap: 20px;
        }
        @media (max-width: 900px) {
            .b-grid { grid-template-columns: 1fr; }
        }

        .b-card {
            background: #FFFFFF;
            border-radius: 18px;
            border: 1px solid #E2E8F0;
            padding: 22px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .b-card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: #0F172A;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .b-card-title span {
            color: #ED172B;
            font-size: 12px;
            font-weight: 800;
        }

        .facts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }
        .fact-item small {
            display: block;
            color: #64748B;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .fact-item strong {
            display: block;
            margin-top: 4px;
            font-size: 14px;
            color: #0F172A;
        }

        .complaint-box {
            background: #F8FAFC;
            border: 1px solid #F1F5F9;
            border-radius: 12px;
            padding: 14px;
            font-size: 13.5px;
            color: #334155;
            line-height: 1.6;
        }

        .badge-status {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            text-transform: uppercase;
        }
        .badge-status.is-menunggu { background: #FEF3C7; color: #B45309; }
        .badge-status.is-diterima { background: #EFF6FF; color: #1D4ED8; }
        .badge-status.is-diproses { background: #F3E8FF; color: #7E22CE; }
        .badge-status.is-selesai { background: #ECFDF5; color: #047857; }
        .badge-status.is-ditolak { background: #FEF2F2; color: #B91C1C; }
        .badge-status.is-dibatalkan { background: #F1F5F9; color: #64748B; }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            font-size: 13px;
            color: #0F172A;
            box-sizing: border-box;
        }
        .form-control:focus {
            outline: none;
            border-color: #ED172B;
            box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.12);
        }

        .btn-submit-primary {
            height: 42px;
            padding: 0 20px;
            border-radius: 10px;
            background: #ED172B;
            color: #FFFFFF;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 4px 12px rgba(237, 23, 43, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-submit-primary:hover {
            background: #D91325;
            transform: translateY(-1px);
        }
        .btn-submit-danger {
            height: 42px;
            padding: 0 18px;
            border-radius: 10px;
            background: #FEF2F2;
            color: #DC2626;
            font-size: 13px;
            font-weight: 700;
            border: 1px solid #FECACA;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-submit-danger:hover {
            background: #FEE2E2;
        }
        .btn-secondary {
            height: 40px;
            padding: 0 16px;
            border-radius: 10px;
            background: #F1F5F9;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            border: 1px solid #E2E8F0;
        }
    </style>

    {{-- Breadcrumb & Top Bar --}}
    <div class="b-header">
        <div>
            <nav class="b-breadcrumb">
                <span class="b-back-arrow">&lsaquo;</span>
                <a href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index') }}" style="color: #64748B; text-decoration: none;">Booking Servis</a>
                <span style="color: #CBD5E1;">&gt;</span>
                <span style="color: #0F172A; font-weight: 700;">{{ $booking->kode_booking }}</span>
            </nav>
            <h1 class="b-heading">Detail Pengajuan Booking #{{ $booking->kode_booking }}</h1>
        </div>

        <a class="btn-secondary" href="{{ route($role==='admin'?'admin.booking-servis.index':'petugas.booking-servis.index') }}">
            &larr; Kembali ke Antrian
        </a>
    </div>

    @if(session('success'))
        <div class="admin-alert is-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="admin-alert is-error">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="b-grid">
        {{-- Kolom Kiri: Detail Pelanggan & Kendaraan --}}
        <main style="display: flex; flex-direction: column; gap: 20px;">
            <section class="b-card">
                <h2 class="b-card-title"><span>01</span> Pelanggan & Kendaraan</h2>
                <div class="facts-grid">
                    <div class="fact-item">
                        <small>Nama Pelanggan</small>
                        <strong>{{ $booking->pelanggan?->nama_lengkap ?: 'Walk-in' }}</strong>
                    </div>
                    <div class="fact-item">
                        <small>Nomor WhatsApp / HP</small>
                        <strong>{{ $booking->pelanggan?->no_hp ?: '—' }}</strong>
                    </div>
                    <div class="fact-item">
                        <small>Unit Kendaraan</small>
                        <strong>{{ $booking->kendaraan?->merk }} {{ $booking->kendaraan?->model }} ({{ $booking->kendaraan?->tahun }})</strong>
                    </div>
                    <div class="fact-item">
                        <small>Nomor Polisi</small>
                        <strong style="color: #ED172B;">{{ $booking->kendaraan?->nomor_polisi ?: 'Belum diisi' }}</strong>
                    </div>
                </div>
            </section>

            <section class="b-card">
                <h2 class="b-card-title"><span>02</span> Layanan & Jadwal</h2>
                <div class="facts-grid">
                    <div class="fact-item">
                        <small>Jenis Servis Diminta</small>
                        <strong>{{ $booking->jenisServis?->nama_servis ?: 'Pemeriksaan Umum' }}</strong>
                    </div>
                    <div class="fact-item">
                        <small>Estimasi Biaya Jasa</small>
                        <strong>Rp {{ number_format((float)($booking->jenisServis?->estimasi_harga ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <div class="fact-item">
                        <small>Tanggal Booking</small>
                        <strong>{{ $booking->tanggal ? \Carbon\Carbon::parse($booking->tanggal)->format('d F Y') : '—' }}</strong>
                    </div>
                    <div class="fact-item">
                        <small>Jam Kedatangan</small>
                        <strong>{{ substr((string)$booking->jam, 0, 5) }} WIB</strong>
                    </div>
                    <div class="fact-item">
                        <small>Preferensi Teknisi Pelanggan</small>
                        <strong>{{ $booking->preferredPetugas?->nama_lengkap ?: 'Tidak ada preferensi' }}</strong>
                    </div>
                </div>
            </section>

            <section class="b-card">
                <h2 class="b-card-title"><span>03</span> Keluhan & Catatan Pelanggan</h2>
                <div class="complaint-box">
                    <strong>Keluhan:</strong><br>
                    {{ $booking->keluhan ?: 'Tidak ada keluhan tertulis dari customer.' }}
                </div>
                @if($booking->catatan)
                    <div class="complaint-box" style="margin-top: 8px;">
                        <strong>Catatan Tambahan:</strong><br>
                        {{ $booking->catatan }}
                    </div>
                @endif
            </section>
        </main>

        {{-- Kolom Kanan: Status & Tindakan Operasional --}}
        <aside style="display: flex; flex-direction: column; gap: 20px;">
            <section class="b-card">
                <h2 class="b-card-title"><span>04</span> Status Booking</h2>
                <div>
                    <span class="badge-status is-{{ $booking->status }}">
                        {{ match($booking->status) { 'menunggu' => 'Menunggu Konfirmasi', 'diterima' => 'Booking Diterima', 'ditolak' => 'Booking Ditolak', 'dibatalkan' => 'Dibatalkan Pelanggan', 'diproses' => 'Work Order Aktif', 'selesai' => 'Selesai', default => ucfirst($booking->status) } }}
                    </span>
                </div>

                {{-- Action 1: Menunggu -> Confirm / Reject --}}
                @if($booking->status === 'menunggu')
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 14px; display: flex; flex-direction: column; gap: 10px;">
                        <p style="margin: 0; font-size: 13px; color: #64748B;">Konfirmasi apakah jadwal dan slot servis tersedia untuk pelanggan ini:</p>
                        <div style="display: flex; gap: 10px;">
                            <form method="post" action="{{ route($role==='admin'?'admin.booking-servis.action':'petugas.booking-servis.action', [$booking, 'confirm']) }}" style="flex: 1;">
                                @csrf
                                <button type="submit" class="btn-submit-primary" style="width: 100%;">
                                    &check; Terima Booking
                                </button>
                            </form>
                            <form method="post" action="{{ route($role==='admin'?'admin.booking-servis.action':'petugas.booking-servis.action', [$booking, 'reject']) }}">
                                @csrf
                                <button type="submit" class="btn-submit-danger" onclick="return confirm('Tolak permohonan booking ini?')">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>

                {{-- Action 2: Diterima -> Start Work Order --}}
                @elseif($booking->status === 'diterima' && !$booking->servis)
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 14px;">
                        <p style="margin: 0 0 12px; font-size: 13px; color: #047857; font-weight: 600;">
                            &check; Booking telah diterima. Buat Work Order untuk memulai pemeriksaan teknisi:
                        </p>
                        <form method="post" action="{{ route($role==='admin'?'admin.servis.from-booking':'petugas.servis.from-booking', $booking) }}" style="display: flex; flex-direction: column; gap: 12px;">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Kilometer Saat Masuk</label>
                                <input class="form-control" type="number" min="0" name="kilometer" value="{{ $booking->kendaraan?->kilometer ?: 0 }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Catatan Teknisi Saat Penerimaan</label>
                                <textarea class="form-control" name="catatan" rows="3" placeholder="Catat kondisi fisik kendaraan atau keluhan langsung"></textarea>
                            </div>
                            @if($role==='admin')
                                <div class="form-group"><label class="form-label">Teknisi Penanggung Jawab</label><select class="form-control" name="id_petugas" required><option value="">Pilih teknisi eligible</option>@foreach($eligibleTechnicians as $technician)<option value="{{ $technician['id'] }}" @selected($booking->preferredPetugas?->id_petugas===$technician['id'])>{{ $technician['display_name'] }} · {{ implode(', ', $technician['specialization']) ?: 'General Service' }}{{ $booking->preferredPetugas?->id_petugas===$technician['id'] ? ' · PREFERENSI PELANGGAN' : '' }}</option>@endforeach</select></div>
                                <div class="form-group"><label class="form-label">Service Advisor (opsional)</label><select class="form-control" name="id_service_advisor"><option value="">Belum ditugaskan</option>@foreach($advisors as $advisor)<option value="{{ $advisor['id'] }}">{{ $advisor['display_name'] }} · {{ $advisor['title'] ?: 'Service Advisor' }}</option>@endforeach</select></div>
                            @endif
                            <button type="submit" class="btn-submit-primary" style="width: 100%;">
                                Mulai Work Order Bengkel &rarr;
                            </button>
                        </form>
                    </div>

                {{-- Action 3: Work Order Sudah Dibuat --}}
                @elseif($booking->servis)
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 14px;">
                        <p style="margin: 0 0 10px; font-size: 13px; color: #1D4ED8; font-weight: 600;">
                            Kendaraan ini sudah memiliki Work Order: <strong>{{ $booking->servis->kode_servis }}</strong>
                        </p>
                        <a class="btn-submit-primary" style="width: 100%; box-sizing: border-box;" href="{{ route($role==='admin'?'admin.servis.show':'petugas.servis.show', $booking->servis) }}">
                            Buka Work Order Servis &rarr;
                        </a>
                        <p style="margin: 10px 0 0; font-size: 12px; color: #64748B;">Work Order Aktif · {{ $booking->servis->petugas?->nama_lengkap ?: 'Belum ditugaskan' }}</p>
                    </div>
                @else
                    <p style="margin: 0; font-size: 13px; color: #64748B;">Tidak ada aksi operasional lebih lanjut untuk status ini.</p>
                @endif
            </section>
        </aside>
    </div>
</div>
@endsection
