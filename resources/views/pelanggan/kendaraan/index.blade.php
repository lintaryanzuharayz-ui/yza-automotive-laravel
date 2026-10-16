@extends('layouts.app')

@section('title', 'Kendaraan Saya')

@push('styles')
<style>
    .garage-add-button .garage-button-label,
    .garage-action-primary .garage-button-label {
        display: inline !important;
        color: #fff !important;
        font-size: inherit !important;
        line-height: 1.2 !important;
        opacity: 1 !important;
        visibility: visible !important;
        text-indent: 0 !important;
    }
    .garage-stat-icon.garage-stat-color {
        display: inline-grid !important;
        place-items: center;
        width: 25px;
        height: 25px;
        margin: 3px 0;
        border: 3px solid #fff;
        border-radius: 50%;
        background: var(--vehicle-color, #64748b);
        box-shadow: 0 0 0 1px rgba(25, 32, 40, .28), inset 0 0 0 1px rgba(255,255,255,.28), 0 4px 10px rgba(25, 32, 40, .12);
        font-size: 0 !important;
    }
</style>
@endpush

@section('content')
<div class="garage-page">
    <section class="garage-hero" aria-labelledby="garage-title">
        <div class="garage-hero-copy"><span class="garage-eyebrow">MY GARAGE</span><h1 id="garage-title">Kendaraan Saya</h1><p>Kelola kendaraan yang terhubung dengan akun YZA Member Anda.</p></div>
        <div class="garage-hero-slogan" aria-hidden="true"><span>MORE</span><span>THAN</span><span>A CAR</span><b>A LIFESTYLE</b><i></i></div>
    </section>
    <section class="garage-summary" aria-label="Ringkasan kendaraan">
        <div class="garage-summary-item"><span class="garage-summary-icon" aria-hidden="true">⌁</span><div><strong>{{ $items->total() }} Kendaraan</strong><span>Dalam Koleksi Anda</span></div></div>
        <div class="garage-summary-divider" aria-hidden="true"></div>
        <div class="garage-summary-item"><span class="garage-summary-icon is-shield" aria-hidden="true">✓</span><div><strong>Member Terverifikasi</strong><span>{{ $hasVerifiedPurchase ? 'Kendaraan terhubung dengan akun YZA' : 'Belum ada kendaraan terverifikasi' }}</span></div></div>
        <a class="garage-add-button" href="{{ route('pelanggan.katalog-mobil.index') }}"><span aria-hidden="true">+</span><span class="garage-button-label">Tambah Kendaraan</span></a>
    </section>
    <div class="garage-list">
        @forelse($items as $item)
            @php
                $vehicleColorText = strtolower((string) ($item->warna ?? ''));
                $vehicleColorSwatch = '#64748b';
                if (str_contains($vehicleColorText, 'merah') || str_contains($vehicleColorText, 'red')) $vehicleColorSwatch = '#e31e34';
                elseif (str_contains($vehicleColorText, 'biru') || str_contains($vehicleColorText, 'blue')) $vehicleColorSwatch = '#2563eb';
                elseif (str_contains($vehicleColorText, 'hijau') || str_contains($vehicleColorText, 'green')) $vehicleColorSwatch = '#16845a';
                elseif (str_contains($vehicleColorText, 'kuning') || str_contains($vehicleColorText, 'yellow')) $vehicleColorSwatch = '#eab308';
                elseif (str_contains($vehicleColorText, 'putih') || str_contains($vehicleColorText, 'white')) $vehicleColorSwatch = '#f8fafc';
                elseif (str_contains($vehicleColorText, 'silver') || str_contains($vehicleColorText, 'abu') || str_contains($vehicleColorText, 'grey') || str_contains($vehicleColorText, 'gray')) $vehicleColorSwatch = '#94a3b8';
                elseif (str_contains($vehicleColorText, 'hitam') || str_contains($vehicleColorText, 'black')) $vehicleColorSwatch = '#17191d';
            @endphp
            <article class="garage-vehicle-card">
                <div class="garage-vehicle-media"><img src="{{ $item->presentation_image }}" alt="{{ $item->merk }} {{ $item->model }}" loading="lazy"></div>
                <div class="garage-vehicle-content">
                    <div class="garage-vehicle-badges"><span class="garage-status-badge"><i></i>{{ strtoupper($item->status) }}</span>@if($item->is_verified_ownership)<span class="garage-ownership-badge"><b aria-hidden="true">✓</b> Verified Ownership</span>@endif</div>
                    <h2>{{ $item->merk }} {{ $item->model }}</h2>
                    <p class="garage-vehicle-subline">{{ $item->nomor_polisi ?: 'Nomor polisi belum diisi' }} <span aria-hidden="true">•</span> {{ $item->tahun ?: 'Tahun belum diisi' }}</p>
                    <div class="garage-stats" aria-label="Spesifikasi kendaraan"><div><span class="garage-stat-icon" aria-hidden="true">◌</span><strong>{{ number_format((int)$item->kilometer, 0, ',', '.') }} km</strong><small>Total Jarak</small></div><div><span class="garage-stat-icon garage-stat-color" style="--vehicle-color: {{ $vehicleColorSwatch }}" aria-hidden="true"></span><strong>{{ $item->warna ?: 'Belum diisi' }}</strong><small>Warna</small></div><div><span class="garage-stat-icon" aria-hidden="true">▣</span><strong>{{ $item->tahun ?: '—' }}</strong><small>Tahun</small></div></div>
                    <div class="garage-actions"><a class="garage-action garage-action-primary" href="{{ route('pelanggan.kendaraan.show', $item) }}"><span aria-hidden="true">▤</span><span class="garage-button-label">Detail</span><b aria-hidden="true">→</b></a><a class="garage-action" href="{{ route('pelanggan.booking-servis.create', ['kendaraan' => $item->getKey(), 'from' => 'garage']) }}"><span aria-hidden="true">⚒</span> Jadwalkan Servis</a><a class="garage-action" href="{{ route('pelanggan.servis.index', ['kendaraan' => $item->getKey(), 'from' => 'garage']) }}"><span aria-hidden="true">◷</span> Riwayat</a><a class="garage-action" href="{{ route('pelanggan.kendaraan.edit', ['kendaraan' => $item, 'from' => 'garage']) }}"><span aria-hidden="true">／</span> Edit</a>@if($item->status === 'aktif')<a class="garage-action garage-action-trade" href="{{ route('pelanggan.pengajuan-mobil.create', ['kendaraan' => $item->getKey(), 'from' => 'garage']) }}"><span aria-hidden="true">⇄</span> Ajukan Trade-In</a>@endif</div>
                </div>
            </article>
        @empty
            <section class="garage-empty" aria-labelledby="garage-empty-title"><div class="garage-empty-icon" aria-hidden="true">⌁</div><span class="garage-eyebrow">MY GARAGE</span><h2 id="garage-empty-title">Belum Ada Kendaraan</h2><p>Belum ada kendaraan yang terhubung dengan akun Anda.</p><a class="garage-add-button" href="{{ route('pelanggan.katalog-mobil.index') }}"><span aria-hidden="true">+</span><span class="garage-button-label">Lihat Koleksi Mobil</span></a></section>
        @endforelse
    </div>
    @if($items->hasPages())<div class="garage-pagination">{{ $items->links() }}</div>@endif
</div>
@endsection
