@extends('layouts.app')

@section('title', 'Kendaraan Saya')

@section('content')
<div class="page-header">
    <div><span class="eyebrow">MY GARAGE</span><h1>Kendaraan Saya</h1><p>Kelola kendaraan yang terhubung dengan akun YZA Member Anda.</p></div>
    <a class="yza-button" href="{{ route('pelanggan.katalog-mobil.index') }}">+ &nbsp;Lihat Koleksi Mobil</a>
</div>

<div class="yza-card-grid">
    @forelse($items as $item)
        <article class="yza-card"><span class="badge {{ $item->status === 'aktif' ? 'badge-success' : 'badge-warning' }}">{{ $item->status }}</span><h2>{{ $item->merk }} {{ $item->model }}</h2><p>{{ $item->nomor_polisi ?: 'Nomor polisi belum diisi' }} · {{ $item->tahun ?: 'Tahun belum diisi' }}</p><p>{{ number_format((int) $item->kilometer, 0, ',', '.') }} km · {{ $item->warna ?: 'Warna belum diisi' }}</p><div class="member-card-actions"><a class="action-link" href="{{ route('pelanggan.kendaraan.show', $item) }}">Detail</a><a class="action-link" href="{{ route('pelanggan.kendaraan.edit', $item) }}">Edit</a></div></article>
    @empty
        <section class="vehicle-empty-experience">
            <div class="vehicle-empty-hero"><div class="vehicle-empty-hero-image" role="img" aria-label="Toyota GR Supra putih di showroom YZA"></div></div>
            <div class="vehicle-empty-copy"><h2>Belum Ada Kendaraan YZA Terverifikasi</h2><p>Miliki mobil melalui YZA Automotive untuk membuka berbagai kemudahan layanan seperti<br class="vehicle-empty-desktop-break"> Booking Servis, Car Wash, Service Reminder, Data Kendaraan, dan Trade-In.</p><div class="vehicle-empty-actions"><a class="yza-button" href="{{ route('pelanggan.katalog-mobil.index') }}"><span aria-hidden="true">🚘</span> Lihat Koleksi Mobil <span aria-hidden="true">→</span></a><a class="vehicle-empty-secondary" href="{{ route('pelanggan.test-drive.create') }}"><span aria-hidden="true">▣</span> Booking Test Drive</a></div></div>
            <div class="vehicle-empty-features" aria-label="Keunggulan layanan YZA"><div><span>🛠</span><strong>Booking Servis</strong><small>Mudah dan cepat</small></div><i></i><div><span>▤</span><strong>Riwayat Servis</strong><small>Riwayat lengkap</small></div><i></i><div><span>♧</span><strong>Service Reminder</strong><small>Tidak lewat jadwal</small></div><i></i><div><span>🚘</span><strong>Data Kendaraan</strong><small>Selalu ter-update</small></div><i></i><div><span>↔</span><strong>Trade-In</strong><small>Proses mudah</small></div></div>
        </section>
        <section class="vehicle-empty-links" aria-label="Informasi layanan YZA">
            <a href="{{ route('pelanggan.promo.index') }}" style="--vehicle-link-image:url('{{ asset('assets/images/member/service-bengkel.png') }}')"><span>◇</span><div><strong>Keuntungan Member</strong><small>Akses layanan eksklusif, promo spesial,<br>dan berbagai privilese menarik lainnya.</small></div><b>→</b></a>
            <a href="{{ route('pelanggan.katalog-mobil.index') }}" style="--vehicle-link-image:url('{{ asset('assets/images/member/service-test-drive.png') }}')"><span>▣</span><div><strong>Langkah Selanjutnya</strong><small>Lihat koleksi mobil, ajukan test drive,<br>atau konsultasi dengan tim kami.</small></div><b>→</b></a>
            <a href="{{ route('pelanggan.servis.index') }}" style="--vehicle-link-image:url('{{ asset('assets/images/member/service-car-wash.png') }}')"><span>⚙</span><div><strong>Layanan Siap Digunakan</strong><small>Setelah kendaraan terverifikasi,<br>semua layanan langsung bisa Anda akses.</small></div><b>→</b></a>
        </section>
    @endforelse
</div>

{{ $items->links() }}
@endsection
