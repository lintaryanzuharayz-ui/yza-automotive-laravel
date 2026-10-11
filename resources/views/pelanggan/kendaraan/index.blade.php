@extends('layouts.app')

@section('title', 'Kendaraan Saya')

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
        <a class="garage-add-button" href="{{ route('pelanggan.katalog-mobil.index') }}"><span aria-hidden="true">+</span> Tambah Kendaraan</a>
    </section>
    <div class="garage-list">
        @forelse($items as $item)
            <article class="garage-vehicle-card">
                <div class="garage-vehicle-media"><img src="{{ $item->presentation_image }}" alt="{{ $item->merk }} {{ $item->model }}" loading="lazy"></div>
                <div class="garage-vehicle-content">
                    <div class="garage-vehicle-badges"><span class="garage-status-badge"><i></i>{{ strtoupper($item->status) }}</span>@if($item->is_verified_ownership)<span class="garage-ownership-badge"><b aria-hidden="true">✓</b> Verified Ownership</span>@endif</div>
                    <h2>{{ $item->merk }} {{ $item->model }}</h2>
                    <p class="garage-vehicle-subline">{{ $item->nomor_polisi ?: 'Nomor polisi belum diisi' }} <span aria-hidden="true">•</span> {{ $item->tahun ?: 'Tahun belum diisi' }}</p>
                    <div class="garage-stats" aria-label="Spesifikasi kendaraan"><div><span class="garage-stat-icon" aria-hidden="true">◌</span><strong>{{ number_format((int)$item->kilometer, 0, ',', '.') }} km</strong><small>Total Jarak</small></div><div><span class="garage-stat-icon" aria-hidden="true">◉</span><strong>{{ $item->warna ?: 'Belum diisi' }}</strong><small>Warna</small></div><div><span class="garage-stat-icon" aria-hidden="true">▣</span><strong>{{ $item->tahun ?: '—' }}</strong><small>Tahun</small></div></div>
                    <div class="garage-actions"><a class="garage-action garage-action-primary" href="{{ route('pelanggan.kendaraan.show', $item) }}"><span aria-hidden="true">▤</span> Detail <b aria-hidden="true">→</b></a><a class="garage-action" href="{{ route('pelanggan.booking-servis.create') }}"><span aria-hidden="true">⚒</span> Jadwalkan Servis</a><a class="garage-action" href="{{ route('pelanggan.servis.index') }}"><span aria-hidden="true">◷</span> Riwayat</a><a class="garage-action" href="{{ route('pelanggan.kendaraan.edit', $item) }}"><span aria-hidden="true">／</span> Edit</a></div>
                </div>
            </article>
        @empty
            <section class="garage-empty" aria-labelledby="garage-empty-title"><div class="garage-empty-icon" aria-hidden="true">⌁</div><span class="garage-eyebrow">MY GARAGE</span><h2 id="garage-empty-title">Belum Ada Kendaraan</h2><p>Belum ada kendaraan yang terhubung dengan akun Anda.</p><a class="garage-add-button" href="{{ route('pelanggan.katalog-mobil.index') }}"><span aria-hidden="true">+</span> Lihat Koleksi Mobil</a></section>
        @endforelse
    </div>
    @if($items->hasPages())<div class="garage-pagination">{{ $items->links() }}</div>@endif
</div>
@endsection
