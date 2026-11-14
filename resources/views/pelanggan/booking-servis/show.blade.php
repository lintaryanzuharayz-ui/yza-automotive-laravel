@extends('layouts.app')

@section('title', 'Booking Servis Berhasil · YZA Automotive')

@php
    $statusLabels = [
        'menunggu' => 'Menunggu Konfirmasi YZA',
        'diterima' => 'Booking Dikonfirmasi',
        'ditolak' => 'Booking Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'diproses' => 'Servis Sedang Diproses',
        'selesai' => 'Selesai',
    ];
    $bookingStatus = $booking_servis->status;
    $statusLabel = $statusLabels[$bookingStatus] ?? ucfirst($bookingStatus);
    $statusStage = match ($bookingStatus) {
        'diterima', 'diproses', 'selesai' => 3,
        default => 2,
    };
    $isNegativeStatus = in_array($bookingStatus, ['ditolak', 'dibatalkan'], true);
    $vehicleName = trim(($booking_servis->kendaraan?->merk ?: '') . ' ' . ($booking_servis->kendaraan?->model ?: '')) ?: 'Belum diisi';
    $vehiclePlate = $booking_servis->kendaraan?->nomor_polisi ?: 'Belum diisi';
    $vehicleMileage = number_format((int) ($booking_servis->kendaraan?->kilometer ?? 0), 0, ',', '.') . ' km';
    $serviceName = $booking_servis->jenisServis?->nama_servis ?: '—';
    $dateLabel = $booking_servis->tanggal?->translatedFormat('d F Y') ?: '—';
    $timeLabel = substr((string) $booking_servis->jam, 0, 5) . ' WIB';
    $submittedAtLabel = $booking_servis->created_at?->translatedFormat('d F Y, H:i') ?: 'Baru saja';
@endphp

@push('styles')
<style>
    .member-booking-result { width: calc(100% - 72px); max-width: 1600px; margin: 0 auto; padding: 22px 0 72px; }
    .member-main-content > .member-booking-result { width: 100%; max-width: none !important; margin-left: 0; margin-right: 0; box-sizing: border-box; }
    .member-booking-result-card { overflow: visible; border: 0; border-radius: 0; background: transparent; box-shadow: none; }
    .member-booking-result-hero { position: relative; display: grid; grid-template-columns: 106px minmax(0, 1fr); gap: 28px; align-items: center; min-height: 265px; padding: 42px 58px; border-radius: 24px; isolation: isolate; color: #fff; background: linear-gradient(90deg, rgba(7, 13, 21, .99) 0%, rgba(15, 17, 23, .9) 44%, rgba(19, 17, 23, .32) 100%), url('/assets/images/hero/yza-detail-red-generated.png') right center / cover; }
    .member-booking-result-hero::after { position: absolute; inset: auto 0 0; height: 4px; content: ''; background: #e91c38; z-index: -1; }
    .member-booking-result-icon { display: grid; place-items: center; width: 86px; height: 86px; border: 6px solid rgba(255, 255, 255, .35); border-radius: 50%; color: #fff; background: #16c76a; box-shadow: 0 0 0 10px rgba(22, 199, 106, .14), 0 13px 30px rgba(22, 199, 106, .35); font-size: 48px; font-weight: 900; line-height: 1; }
    .member-booking-result-hero-content { max-width: 840px; }
    .member-booking-result h1 { margin: 0; color: #fff; font-size: clamp(31px, 3.65vw, 58px); font-weight: 950; letter-spacing: -.055em; line-height: 1.04; white-space: nowrap; }
    .member-booking-result-hero p { margin: 14px 0 0; color: #d7e0e8; font-size: 17px; }
    .member-booking-code { display: inline-flex; margin-top: 24px; padding: 9px 14px; border: 1px solid #ff173e; border-radius: 8px; color: #ff3153; background: rgba(10, 14, 22, .5); font-size: 14px; font-weight: 900; letter-spacing: .08em; }
    .member-booking-result-body { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; padding: 36px 58px 28px; }
    .member-booking-result-section { min-height: 286px; padding: 30px; border: 1px solid #d5e0e8; border-radius: 16px; background: #fff; }
    .member-booking-result-section-heading { display: flex; gap: 12px; align-items: center; margin-bottom: 22px; }
    .member-booking-result-section-icon { display: grid; flex: 0 0 auto; place-items: center; width: 46px; height: 46px; border-radius: 13px; color: #e91c38; background: #fff0f2; }
    .member-booking-result-section-icon svg { width: 25px; height: 25px; fill: none; stroke: currentColor; stroke-linecap: round; stroke-linejoin: round; stroke-width: 1.8; }
    .member-booking-result-section h2 { margin: 0; color: #e31d3b; font-size: 30px; letter-spacing: -.035em; text-transform: uppercase; }
    .member-booking-result-section dl { display: grid; gap: 0; margin: 0; }
    .member-booking-result-section dl div { display: flex; justify-content: space-between; gap: 24px; padding: 17px 0; border-top: 1px solid #e5ebf0; }
    .member-booking-result-section dt { color: #66798a; font-size: 16px; }
    .member-booking-result-section dd { margin: 0; color: #142232; font-size: 16px; font-weight: 900; text-align: right; }
    .member-booking-status { display: inline-flex; padding: 9px 13px; border-radius: 999px; color: #8a5a12; background: #fff1d7; font-size: 13px; font-weight: 900; }
    .member-booking-status.is-negative { color: #a9162c; background: #ffe8ec; }
    .member-booking-timeline { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; margin: 0 58px 28px; padding: 22px 24px; border: 1px solid #e0e7ed; border-radius: 16px; background: #fbfcfd; }
    .member-booking-timeline-step { position: relative; display: flex; gap: 12px; align-items: center; min-width: 0; color: #8a98a6; }
    .member-booking-timeline-step:not(:last-child)::after { position: absolute; top: 15px; left: 34px; right: 0; height: 2px; content: ''; background: #dce4ea; }
    .member-booking-timeline-step.is-complete { color: #e91c38; }
    .member-booking-timeline-step.is-complete::after { background: #63c496; }
    .member-booking-timeline-step.is-current { color: #e9a20f; }
    .member-booking-timeline-number { position: relative; z-index: 1; display: grid; flex: 0 0 auto; place-items: center; width: 31px; height: 31px; border: 1px solid #d7e0e7; border-radius: 50%; color: #82909d; background: #fff; font-size: 12px; font-weight: 900; }
    .member-booking-timeline-step.is-complete .member-booking-timeline-number { border-color: #e91c38; color: #fff; background: #e91c38; }
    .member-booking-timeline-step.is-current .member-booking-timeline-number { border-color: #e9a20f; color: #fff; background: #e9a20f; }
    .member-booking-timeline-label { position: relative; z-index: 1; display: grid; gap: 3px; color: inherit; background: #fbfcfd; font-size: 15px; font-weight: 850; padding-right: 12px; }
    .member-booking-timeline-label strong { color: #172331; font-size: 15px; }
    .member-booking-timeline-label small { color: #7c8c9a; font-size: 12px; font-weight: 700; }
    .member-booking-result-actions { display: flex; flex-wrap: wrap; gap: 13px; padding: 0 58px 32px; }
    .member-booking-result-actions a { display: inline-flex; align-items: center; justify-content: center; min-height: 56px; padding: 0 28px; border-radius: 10px; font-size: 15px; font-weight: 900; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease, background-color .2s ease; }
    .member-booking-result-actions a:hover { transform: translateY(-2px); }
    .member-booking-result-actions .primary { color: #fff; background: #e91c38; box-shadow: 0 10px 18px rgba(233, 28, 56, .2); }
    .member-booking-result-actions .primary:hover { background: #c9142e; box-shadow: 0 13px 22px rgba(233, 28, 56, .28); }
    .member-booking-result-actions .secondary { border: 1px solid #d8e0e7; color: #314252; background: #fff; }
    .member-booking-preference { display: flex; justify-content: space-between; gap: 24px; align-items: center; margin: 0 58px 42px; padding: 22px 25px; border: 1px solid #e0e7ed; border-radius: 16px; background: #fbfcfd; }
    .member-booking-preference h2 { margin: 0 0 7px; color: #172331; font-size: 25px; }
    .member-booking-preference p { margin: 0; color: #66798a; font-size: 15px; }
    .member-booking-preference-value { flex: 0 0 auto; color: #d71c35 !important; font-size: 15px !important; font-weight: 900; }
    .member-booking-alert { display: none; width: min(96vw, 1480px); margin: 0 auto 18px; padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 700; }
    .member-booking-alert.success { color: #126b47; border: 1px solid #bfe8d2; background: #effbf4; }
    .member-booking-alert.error { display: block; color: #a9162c; border: 1px solid #ffc6cf; background: #fff1f3; }
    .member-booking-cancel { display: inline-flex; align-items: center; min-height: 50px; margin-left: auto; padding: 0 18px; border: 1px solid #ffc1c9; border-radius: 10px; color: #c9142e; background: #fff; font: inherit; font-size: 13px; font-weight: 900; cursor: pointer; transition: transform .2s ease, background-color .2s ease; }
    .member-booking-cancel:hover { transform: translateY(-2px); background: #fff1f3; }
    @media (max-width: 900px) { .member-booking-result-body { grid-template-columns: 1fr; } .member-booking-result-hero, .member-booking-result-body { padding-left: 32px; padding-right: 32px; } .member-booking-timeline, .member-booking-result-actions, .member-booking-preference { margin-left: 32px; margin-right: 32px; } }
    @media (max-width: 620px) { .member-booking-result { width: auto; padding: 18px 12px 48px; } .member-booking-result-hero { grid-template-columns: 1fr; gap: 18px; min-height: 0; padding: 30px 24px; background-position: 70% center; } .member-booking-result h1 { font-size: 38px; white-space: normal; } .member-booking-result-body { padding: 22px 24px 20px; } .member-booking-timeline { grid-template-columns: 1fr; gap: 16px; margin: 0 24px 24px; } .member-booking-timeline-step:not(:last-child)::after { top: 31px; bottom: -16px; left: 15px; right: auto; width: 2px; height: auto; } .member-booking-result-actions { margin: 0; padding: 0 24px 26px; } .member-booking-result-actions a { width: 100%; } .member-booking-preference { align-items: flex-start; flex-direction: column; margin: 0 24px 28px; } }
    @media (prefers-reduced-motion: reduce) { .member-booking-result-actions a { transition: none; } }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="member-booking-alert success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="member-booking-alert error">{{ $errors->first() }}</div>
@endif
<main class="member-booking-result">
    <article class="member-booking-result-card">
        <header class="member-booking-result-hero">
            <span class="member-booking-result-icon" aria-hidden="true">✓</span>
            <div class="member-booking-result-hero-content">
                <h1>{{ session('success') ? 'Booking Servis Berhasil Diajukan' : 'Detail Booking Servis' }}</h1>
                <p>Booking Anda tercatat dan akan diproses oleh tim YZA Automotive.</p>
                <span class="member-booking-code">{{ $booking_servis->kode_booking }}</span>
            </div>
        </header>

        <div class="member-booking-result-body">
            <section class="member-booking-result-section">
                <div class="member-booking-result-section-heading">
                    <span class="member-booking-result-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 16.5v-4l2-5h12l2 5v4M6 16.5h12M7 8.5l1.2-3h7.6l1.2 3M7 19v-2.5M17 19v-2.5M5.5 12.5h.01M18.5 12.5h.01"/><path d="M3 16.5h18"/></svg></span>
                    <h2>Kendaraan</h2>
                </div>
                <dl>
                    <div><dt>Kendaraan</dt><dd>{{ $vehicleName }}</dd></div>
                    <div><dt>No Polisi</dt><dd>{{ $vehiclePlate }}</dd></div>
                    <div><dt>Kilometer Terakhir</dt><dd>{{ $vehicleMileage }}</dd></div>
                </dl>
            </section>

            <section class="member-booking-result-section">
                <div class="member-booking-result-section-heading">
                    <span class="member-booking-result-section-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m14.7 6.3 3-3 3 3-3 3M16.2 7.8 9 15M5 4v4M3 6h4M7 20h10M8.5 15.5h7M8 12h8"/><path d="M5 10a5 5 0 0 0 5 5h.5"/></svg></span>
                    <h2>Jadwal &amp; Servis</h2>
                </div>
                <dl>
                    <div><dt>Jenis Servis</dt><dd>{{ $serviceName }}</dd></div>
                    <div><dt>Tanggal</dt><dd>{{ $dateLabel }}</dd></div>
                    <div><dt>Waktu Kedatangan</dt><dd>{{ $timeLabel }}</dd></div>
                    <div><dt>Status</dt><dd><span class="member-booking-status {{ $isNegativeStatus ? 'is-negative' : '' }}">{{ $statusLabel }}</span></dd></div>
                </dl>
            </section>
        </div>

        <div class="member-booking-timeline" aria-label="Status booking">
            <div class="member-booking-timeline-step is-complete"><span class="member-booking-timeline-number">✓</span><span class="member-booking-timeline-label"><strong>Diajukan</strong><small>{{ $submittedAtLabel }}</small></span></div>
            <div class="member-booking-timeline-step {{ $statusStage >= 2 ? ($statusStage === 2 ? 'is-current' : 'is-complete') : '' }}"><span class="member-booking-timeline-number">{{ $statusStage >= 2 ? '◷' : '02' }}</span><span class="member-booking-timeline-label"><strong>Menunggu Konfirmasi</strong><small>Tim YZA Automotive</small></span></div>
            <div class="member-booking-timeline-step {{ $statusStage >= 3 ? 'is-current' : '' }}"><span class="member-booking-timeline-number">03</span><span class="member-booking-timeline-label"><strong>Dijadwalkan</strong><small>Segera setelah dikonfirmasi</small></span></div>
        </div>

        <div class="member-booking-result-actions">
            <a class="primary" href="{{ route('pelanggan.booking-servis.index') }}">Lihat Booking <span aria-hidden="true">&nbsp;→</span></a>
            <a class="secondary" href="{{ route('pelanggan.kendaraan.index') }}">Kembali ke Kendaraan Saya</a>
            @if(in_array($bookingStatus, ['menunggu', 'diterima'], true))
                <form method="post" action="{{ route('pelanggan.booking-servis.cancel', $booking_servis) }}">
                    @csrf
                    @method('PATCH')
                    <button class="member-booking-cancel" type="submit">Batalkan Booking</button>
                </form>
            @endif
        </div>

        <section class="member-booking-preference">
            <div>
                <h2>Preferensi Teknisi</h2>
                <p>{{ $booking_servis->preferredPetugas ? 'Preferensi teknisi member. Penugasan teknisi final dilakukan oleh YZA Automotive.' : 'YZA Automotive akan menentukan teknisi yang sesuai.' }}</p>
            </div>
            @if($booking_servis->preferredPetugas)
                <p class="member-booking-preference-value">{{ $booking_servis->preferredPetugas->nama_lengkap }}</p>
            @else
                <p class="member-booking-preference-value">Tidak Ada Preferensi</p>
            @endif
        </section>
    </article>
</main>
@endsection
