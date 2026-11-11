@extends('layouts.app')

@section('title', 'Pesanan Mobil Saya | YZA Automotive')
@section('body-class', 'member-orders-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/member-orders.css') }}?v={{ filemtime(public_path('assets/css/member-orders.css')) }}">
@endpush

@section('content')
<div class="member-orders-shell">
    <section class="member-orders-hero" aria-labelledby="orders-title">
        <div class="member-orders-hero-overlay"></div>
        <div class="member-orders-hero-copy">
            <span class="member-orders-kicker">YZA AUTOMOTIVE · PREMIUM MEMBER PORTAL</span>
            <h1 id="orders-title" aria-label="Pesanan Mobil Saya">Pesanan Mobil <em>Saya</em></h1>
            <p>Pantau verifikasi, pembayaran, PDI, dan serah terima kendaraan Anda.</p>
        </div>
        <div class="member-orders-summary" aria-label="Ringkasan pesanan">
            <div class="member-orders-summary-card"><span class="summary-icon"><x-member-home-icon name="car" /></span><span><small>Total Pesanan</small><span class="summary-value"><strong>{{ $total }}</strong><em>Mobil</em></span></span></div>
            <div class="member-orders-summary-card is-action"><span class="summary-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 6v6h5"/></svg></span><span><small>Butuh Tindakan</small><span class="summary-value"><strong>{{ $action }}</strong><em>Pesanan</em></span></span></div>
            <div class="member-orders-summary-card is-complete"><span class="summary-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 5 5L20 6"/></svg></span><span><small>Selesai</small><span class="summary-value"><strong>{{ $completed }}</strong><em>Pesanan</em></span></span></div>
        </div>
    </section>

    <section class="member-orders-toolbar" aria-label="Pencarian dan filter pesanan">
        <form method="get" action="{{ route('pelanggan.pesanan-mobil.index') }}" class="member-orders-filter-form">
            <label class="orders-search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/></svg><input type="search" name="q" value="{{ $keyword }}" placeholder="Cari nama mobil atau nomor pesanan..." aria-label="Cari pesanan"></label>
            <label class="orders-select"><span class="sr-only">Filter status</span><select name="status"><option value="">Semua Status</option>@foreach(['menunggu_verifikasi','menunggu_pembayaran','lunas','pdi_lulus','pdi_gagal','siap_diambil','diserahkan','ditolak','dibatalkan'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>@endforeach</select></label>
            <label class="orders-select"><span class="sr-only">Urutkan pesanan</span><select name="sort"><option value="newest" @selected($sort === 'newest')>Urutkan: Terbaru</option><option value="oldest" @selected($sort === 'oldest')>Urutkan: Terlama</option><option value="action" @selected($sort === 'action')>Urutkan: Butuh Tindakan</option><option value="completed" @selected($sort === 'completed')>Urutkan: Selesai</option></select></label>
            <button class="member-orders-filter-button" type="submit">Terapkan</button>
            @if($keyword || $status || $sort !== 'newest')<a class="member-orders-reset" href="{{ route('pelanggan.pesanan-mobil.index') }}">Reset</a>@endif
        </form>
    </section>

    <section class="member-orders-list" aria-live="polite">
        @forelse($orders as $order)
            @php($meta = $order->presentation_status)
            <article class="member-order-card" data-order-card>
                <a class="member-order-media" href="{{ route('pelanggan.pesanan-mobil.show', $order) }}" aria-label="Lihat detail {{ $order->snapshot_merk }} {{ $order->snapshot_model }}">
                    <img src="{{ $order->presentation_image }}" alt="{{ $order->snapshot_merk }} {{ $order->snapshot_model }} warna {{ $order->presentation_color }}" loading="lazy">
                    <span class="member-order-photo-count">@if($order->presentation_photo_count)▧ {{ $order->presentation_photo_count }} Foto @else Foto belum tersedia @endif</span>
                </a>
                <div class="member-order-main">
                    <div class="member-order-identity">
                        <div><span class="member-order-number">{{ $order->nomor_pesanan }}</span><button type="button" class="member-copy-order" data-copy-order="{{ $order->nomor_pesanan }}" aria-label="Salin nomor pesanan" title="Salin nomor pesanan"><svg class="copy-mark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg><svg class="copy-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></button></div>
                        <h2>{{ $order->snapshot_merk }} {{ $order->snapshot_model }}</h2>
                        <p>{{ $order->snapshot_tipe ?: 'Premium Vehicle' }} · {{ $order->snapshot_tahun ?: 'Tahun kendaraan' }}</p>
                    </div>
                    <div class="member-order-specs"><span><small>Warna</small><strong>@if($order->presentation_swatch)<i class="color-swatch" style="--swatch: {{ $order->presentation_swatch }}" aria-hidden="true"></i>@endif{{ $order->presentation_color }}</strong></span><span><small>Transmisi</small><strong>⚙ {{ $order->presentation_transmission }}</strong></span><span><small>Tipe</small><strong>▣ {{ $order->snapshot_tipe ?: 'Belum tercatat' }}</strong></span></div>
                    <ol class="member-order-timeline" aria-label="Tahapan pesanan">
                        @foreach($order->presentation_timeline as $step)<li class="timeline-{{ $step['state'] }}"><span class="timeline-dot">{{ $step['icon'] }}</span><strong>{{ $step['label'] }}</strong>@if($step['date'])<small>{{ $step['date'] }}</small>@endif</li>@endforeach
                    </ol>
                </div>
                <aside class="member-order-aside">
                    <span class="member-order-status tone-{{ $meta['tone'] }}">{{ $meta['label'] }}</span>
                    <small class="member-order-date">Dipesan pada {{ $order->created_at?->format('d M Y') }}</small>
                    <div class="member-order-now"><small>Saat ini:</small><strong>{{ $meta['label'] }}</strong><p>{{ $meta['description'] }}</p></div>
                    <a class="member-order-detail-button {{ $meta['tone'] === 'danger' ? 'is-dark' : '' }}" href="{{ route('pelanggan.pesanan-mobil.show', $order) }}">Lihat Detail <span>→</span></a>
                </aside>
            </article>
        @empty
            <div class="member-orders-empty"><span><x-member-home-icon name="car" /></span>@if($keyword || $status)<h2>Pesanan tidak ditemukan</h2><p>Coba kata kunci atau status lain untuk menemukan pesanan Anda.</p><a href="{{ route('pelanggan.pesanan-mobil.index') }}">Tampilkan Semua Pesanan <b>→</b></a>@else<h2>Belum Ada Pesanan Mobil</h2><p>Pesanan kendaraan Anda akan tampil di sini setelah checkout berhasil.</p><a href="{{ route('pelanggan.katalog-mobil.index') }}">Jelajahi Koleksi Mobil <b>→</b></a>@endif</div>
        @endforelse
    </section>

    @if($orders->total() > 0)
        <nav class="member-orders-pagination" aria-label="Navigasi halaman pesanan">
            <span class="member-orders-page-summary">Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan</span>
            <div class="member-orders-page-controls">
                @if($orders->onFirstPage())
                    <span class="is-disabled" aria-disabled="true">← Sebelumnya</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}">← Sebelumnya</a>
                @endif
                <span class="is-current" aria-current="page">{{ $orders->currentPage() }}</span>
                @if($orders->hasMorePages())
                    <a class="is-next" href="{{ $orders->nextPageUrl() }}">Selanjutnya →</a>
                @else
                    <span class="is-disabled" aria-disabled="true">Selanjutnya →</span>
                @endif
            </div>
        </nav>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    document.querySelectorAll('[data-copy-order]').forEach(button => {
        let feedbackTimer;
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copyOrder);
                clearTimeout(feedbackTimer);
                button.classList.add('is-copied');
                button.setAttribute('aria-label', 'Nomor pesanan tersalin');
                button.title = 'Nomor pesanan tersalin';
                feedbackTimer = setTimeout(() => {
                    button.classList.remove('is-copied');
                    button.setAttribute('aria-label', 'Salin nomor pesanan');
                    button.title = 'Salin nomor pesanan';
                }, 1200);
            } catch (error) {
                button.title = 'Belum tersalin. Coba salin kembali.';
            }
        });
    });
})();
</script>
@endpush
