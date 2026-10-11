@extends('layouts.app')
@section('title', 'Dashboard Admin - YZA Automotive')
@section('admin-page-title', 'Dashboard Utama')
@section('content')
<div class="admin-command-center">
    <section class="admin-hero"><span class="eyebrow">Management Overview</span><h1>Selamat Datang, {{ $user->displayName() }}</h1><p>Pusat kendali operasional YZA Automotive. Pantau pesanan, unit, pelanggan, layanan, dan transaksi dari satu tempat.</p></section>
    <section class="admin-service-grid" aria-label="Ringkasan metrik">
        @foreach($metrics as $metric)<article class="admin-service-card"><small>{{ $metric['label'] }}</small><strong>{{ number_format($metric['value']) }}</strong><span>{{ $metric['hint'] }}</span></article>@endforeach
    </section>
    <div class="admin-dashboard-grid">
        <section class="admin-panel"><div class="admin-panel-heading"><div><h2>Pesanan terbaru</h2><p>Order yang masuk dari member, berdasarkan data terbaru.</p></div><a class="admin-panel-link" href="{{ route('admin.pesanan-mobil.index') }}">Lihat semua -&gt;</a></div>
            @forelse($pendingOrders as $order)<a class="admin-attention" href="{{ route('admin.pesanan-mobil.show', $order) }}"><div><strong>{{ $order->nomor_pesanan }}</strong><small>{{ $order->pelanggan?->nama_lengkap ?: 'Pelanggan' }} - {{ $order->mobil?->merk }} {{ $order->mobil?->model }}</small></div><span class="admin-count">{{ str_replace('_', ' ', $order->status) }}</span></a>@empty<p>Belum ada pesanan mobil yang tercatat.</p>@endforelse
        </section>
        <section class="admin-panel"><h2>Perlu perhatian</h2><div class="admin-attention"><div><strong>Pembayaran menunggu verifikasi</strong><small>Transaksi yang belum ditinjau admin</small></div><span class="admin-count">{{ number_format($pendingPayments) }}</span></div>@foreach($serviceData['attention']->take(4) as $item)<div class="admin-attention"><div><strong>{{ $item['label'] }}</strong><small>{{ $item['detail'] }}</small></div><span class="admin-count">!</span></div>@endforeach</section>
    </div>
    <section class="admin-panel" style="margin-top:20px"><div class="admin-panel-heading"><div><h2>Akses cepat</h2><p>Buka modul yang paling sering digunakan.</p></div></div><div class="admin-action-grid"><a class="admin-action" href="{{ route('admin.pesanan-mobil.index') }}">Kelola Pesanan Mobil -&gt;</a><a class="admin-action" href="{{ route('admin.mobil.index') }}">Kelola Katalog &amp; Stok -&gt;</a><a class="admin-action" href="{{ route('admin.booking.index') }}">Lihat Booking Showroom -&gt;</a><a class="admin-action" href="{{ route('admin.laporan-servis.index') }}">Buka Laporan Operasional -&gt;</a></div></section>
</div>
@endsection
