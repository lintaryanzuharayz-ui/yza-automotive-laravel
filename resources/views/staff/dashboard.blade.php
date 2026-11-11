@extends('layouts.app')
@section('title', 'Dashboard '.ucwords(str_replace('_', ' ', $role)).' · YZA Automotive')
@section('admin-page-title', 'Dashboard '.ucwords(str_replace('_', ' ', $role)))
@section('content')
<section class="admin-command-center">
    <section class="admin-hero"><div class="admin-hero-copy"><span class="admin-eyebrow">YZA AUTOMOTIVE</span><h1>Selamat Datang, <em>{{ $user->displayName() }}</em></h1><p>Dashboard {{ ucwords(str_replace('_', ' ', $role)) }} dengan data operasional sesuai tanggung jawab Anda.</p></div><div class="admin-hero-art" aria-hidden="true"><img src="{{ asset('assets/images/hero/yza-admin-sales-banner.jpg') }}" alt=""><span></span></div></section>
    <section class="admin-service-grid" aria-label="Ringkasan metrik">
        @foreach($metrics as $metric)<article class="admin-service-card"><div class="admin-service-card-top"><span class="admin-metric-icon tone-red">•</span><span class="admin-metric-kicker">DATA AKTUAL</span></div><strong>{{ $metric['value'] }}</strong><div><b>{{ $metric['label'] }}</b><span>{{ $metric['hint'] }}</span></div></article>@endforeach
    </section>
    <section class="admin-panel"><div class="admin-panel-heading"><div><span class="admin-section-kicker">ROLE ACCESS</span><h2>Ruang kerja {{ ucwords(str_replace('_', ' ', $role)) }}</h2><p>Menu yang tampil mengikuti permission server-side. Data kosong ditampilkan apa adanya.</p></div></div><div class="admin-empty-state"><span aria-hidden="true">✓</span><div><strong>Dashboard role-aware aktif</strong><small>Gunakan navigasi di samping untuk membuka modul yang diizinkan.</small></div></div></section>
</section>
@endsection
