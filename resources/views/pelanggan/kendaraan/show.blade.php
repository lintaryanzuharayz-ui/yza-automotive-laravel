@extends('layouts.app')

@section('title', 'Detail Kendaraan · YZA Automotive')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/vehicle-detail.css') }}?v={{ filemtime(public_path('assets/css/pages/vehicle-detail.css')) }}">
@endpush

@section('content')
@php
    $vehicleName = trim($kendaraan->merk.' '.$kendaraan->model);
    $vehicleYear = $kendaraan->tahun ?: '—';
    $vehicleColor = $kendaraan->warna ?: 'Belum diisi';
    $vehicleMileage = number_format((int) ($kendaraan->kilometer ?? 0), 0, ',', '.').' km';
    $upcomingLabel = $upcomingBooking?->jenisServis?->nama_servis ?: 'Belum Dijadwalkan';
    $upcomingDescription = $upcomingBooking
        ? $upcomingBooking->tanggal?->translatedFormat('d M Y').' · '.substr((string) $upcomingBooking->jam, 0, 5)
        : 'Jadwalkan servis untuk perawatan optimal.';
    $specs = [
        ['icon' => '▱', 'label' => 'Merek', 'value' => $kendaraan->merk ?: 'Belum diisi'],
        ['icon' => '⚙', 'label' => 'Model', 'value' => $kendaraan->model ?: 'Belum diisi'],
        ['icon' => '▣', 'label' => 'Tahun', 'value' => $vehicleYear],
        ['icon' => '◉', 'label' => 'Warna', 'value' => $vehicleColor],
        ['icon' => '⛽', 'label' => 'Jenis BBM', 'value' => $kendaraan->bahan_bakar ?: 'Belum diisi'],
        ['icon' => '♧', 'label' => 'Transmisi', 'value' => $kendaraan->transmisi ?: 'Belum diisi'],
        ['icon' => '▤', 'label' => 'Nomor Polisi', 'value' => $kendaraan->nomor_polisi ?: 'Belum diisi'],
        ['icon' => '▥', 'label' => 'Nomor Rangka', 'value' => $kendaraan->nomor_rangka ?: 'Belum tersedia'],
        ['icon' => '▥', 'label' => 'Nomor Mesin', 'value' => $kendaraan->nomor_mesin ?: 'Belum tersedia'],
        ['icon' => '●', 'label' => 'Status', 'value' => strtoupper($kendaraan->status ?: '—')],
    ];
@endphp

<div class="vehicle-detail-page">
    <section class="vehicle-detail-hero" aria-labelledby="vehicle-detail-title">
        <div class="vehicle-detail-hero-inner">
            <a class="vehicle-detail-back" href="{{ route('pelanggan.kendaraan.index') }}" aria-label="Kembali ke Kendaraan Saya">←</a>
            <div>
                <nav class="vehicle-detail-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('pelanggan.kendaraan.index') }}">Kendaraan Saya</a><span aria-hidden="true">›</span><span>Detail Kendaraan</span></nav>
                <h1 id="vehicle-detail-title">Detail Kendaraan</h1>
                <p>Informasi lengkap dan kelola kendaraan Anda di YZA Automotive.</p>
            </div>
            <div class="vehicle-detail-hero-mark" aria-hidden="true"><span>MORE</span><span>THAN</span><span>A CAR</span><small>A LIFESTYLE</small><i></i></div>
        </div>
    </section>

    <main class="vehicle-detail-shell">
        <section class="vehicle-profile-card" aria-labelledby="vehicle-profile-title">
            <div class="vehicle-profile-media">
                <img src="{{ $vehicleImage }}" alt="{{ $vehicleName }} {{ $vehicleColor }}" class="vehicle-profile-image">
                <div class="vehicle-media-caption"><span class="vehicle-media-icon" aria-hidden="true">▧</span><span><strong>{{ $vehicleName }}</strong><small>{{ $vehicleColor }}</small></span></div>
            </div>
            <div class="vehicle-profile-info">
                <div class="vehicle-profile-topline">
                    <div class="vehicle-detail-badges"><span class="vehicle-detail-status {{ $kendaraan->status === 'aktif' ? 'is-active' : 'is-inactive' }}"><i></i>{{ strtoupper($kendaraan->status ?: '—') }}</span>@if($verifiedOwnership)<span class="vehicle-detail-verified"><b aria-hidden="true">✓</b> Verified Ownership</span>@endif</div>
                    <span class="vehicle-detail-id">ID Kendaraan <strong>#{{ $kendaraan->getKey() }}</strong><button type="button" class="vehicle-copy-id" data-copy-id="{{ $kendaraan->getKey() }}" aria-label="Salin ID kendaraan">▣</button></span>
                </div>
                <h2 id="vehicle-profile-title">{{ $vehicleName }}</h2>
                <p class="vehicle-profile-subline">{{ $kendaraan->nomor_polisi ?: 'Nomor polisi belum diisi' }} <span aria-hidden="true">•</span> {{ $vehicleYear }}</p>

                <section class="vehicle-spec-panel vehicle-spec-panel--detailed" aria-labelledby="vehicle-spec-title">
                    <h3 id="vehicle-spec-title"><span aria-hidden="true">▤</span> Detail Spesifikasi</h3>
                    <dl class="vehicle-spec-grid">@foreach($specs as $spec)<div><span class="vehicle-spec-icon" aria-hidden="true">{{ $spec['icon'] }}</span><dt>{{ $spec['label'] }}</dt><b aria-hidden="true">:</b><dd>{{ $spec['value'] }}</dd></div>@endforeach<div><span class="vehicle-spec-icon" aria-hidden="true">◌</span><dt>Odometer</dt><b aria-hidden="true">:</b><dd>{{ $vehicleMileage }}</dd></div><div><span class="vehicle-spec-icon" aria-hidden="true">✓</span><dt>Kepemilikan</dt><b aria-hidden="true">:</b><dd>{{ $verifiedOwnership ? 'Verified' : 'Belum diverifikasi' }}</dd></div></dl>
                </section>
            </div>

            <div class="vehicle-insight-grid vehicle-insight-grid--colored">
                <a class="vehicle-insight-card is-link is-blue" href="{{ route('pelanggan.servis.index', ['kendaraan' => $kendaraan->getKey()]) }}"><span class="vehicle-insight-icon" aria-hidden="true">▤</span><div><h3>Riwayat Servis</h3><p>{{ $serviceCount ? $serviceCount.' riwayat servis kendaraan' : 'Lihat riwayat perawatan kendaraan' }}</p></div><b aria-hidden="true">›</b></a>
                <article class="vehicle-insight-card is-green"><span class="vehicle-insight-icon" aria-hidden="true">▤</span><div><h3>Dokumen Kendaraan</h3><p>STNK, BPKB dan dokumen lainnya</p></div><b aria-hidden="true">›</b></article>
                <article class="vehicle-insight-card is-yellow"><span class="vehicle-insight-icon" aria-hidden="true">♢</span><div><h3>Asuransi</h3><p>Informasi polis dan perlindungan</p></div><b aria-hidden="true">›</b></article>
                <a class="vehicle-insight-card is-link is-pink" href="{{ route('pelanggan.booking-servis.create', ['kendaraan' => $kendaraan->getKey()]) }}"><span class="vehicle-insight-icon" aria-hidden="true">⚒</span><div><h3>Jadwalkan Servis</h3><p>{{ $upcomingBooking ? $upcomingLabel.' · '.$upcomingDescription : 'Booking servis di bengkel resmi' }}</p></div><b aria-hidden="true">›</b></a>
            </div>

        </section>
    </main>
</div>

@if($kendaraan->status === 'aktif')
    <div class="vehicle-confirm-modal" data-deactivate-modal hidden role="dialog" aria-modal="true" aria-labelledby="deactivate-title">
        <div class="vehicle-confirm-dialog"><button type="button" class="vehicle-confirm-close" data-deactivate-close aria-label="Tutup">×</button><span class="vehicle-confirm-icon" aria-hidden="true">!</span><h2 id="deactivate-title">Nonaktifkan kendaraan?</h2><p>Kendaraan ini akan disembunyikan dari kendaraan aktif dan tidak dapat digunakan untuk pengajuan layanan sampai diaktifkan kembali oleh sistem.</p><div class="vehicle-confirm-actions"><button type="button" class="vehicle-detail-action" data-deactivate-close>Batal</button><form method="post" action="{{ route('pelanggan.kendaraan.deactivate', $kendaraan) }}">@csrf @method('PATCH')<button class="vehicle-detail-action is-danger" type="submit">Ya, Nonaktifkan</button></form></div></div>
    </div>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const copyButton = document.querySelector('[data-copy-id]');
    copyButton?.addEventListener('click', async () => { try { await navigator.clipboard.writeText(copyButton.dataset.copyId); const original = copyButton.textContent; copyButton.textContent = '✓'; setTimeout(() => { copyButton.textContent = original; }, 1200); } catch (_) {} });
    const modal = document.querySelector('[data-deactivate-modal]');
    const open = () => { if (!modal) return; modal.hidden = false; document.body.classList.add('vehicle-modal-open'); modal.querySelector('[data-deactivate-close]')?.focus(); };
    const close = () => { if (!modal) return; modal.hidden = true; document.body.classList.remove('vehicle-modal-open'); };
    document.querySelector('[data-deactivate-trigger]')?.addEventListener('click', open);
    modal?.querySelectorAll('[data-deactivate-close]').forEach((button) => button.addEventListener('click', close));
    modal?.addEventListener('click', (event) => { if (event.target === modal) close(); });
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
})();
</script>
@endpush
