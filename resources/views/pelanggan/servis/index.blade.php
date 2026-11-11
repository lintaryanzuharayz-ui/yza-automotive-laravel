@extends('layouts.app')
@section('title', 'Servis Saya · YZA Automotive')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/pages/service-history.css') }}?v={{ filemtime(public_path('assets/css/pages/service-history.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/pages/service-history-overrides.css') }}?v={{ filemtime(public_path('assets/css/pages/service-history-overrides.css')) }}">
@php
    $returnToGarage = request('from') === 'garage';
    $vehicleName = trim(($selectedVehicle?->merk ?: '') . ' ' . ($selectedVehicle?->model ?: '')) ?: 'Semua Kendaraan';
    $vehiclePlate = $selectedVehicle?->nomor_polisi ?: 'Nomor polisi belum diisi';
    $lastServiceDate = $lastService?->tanggal_masuk?->format('d M Y') ?: 'Belum ada';
    $nextServiceLabel = $nextBooking?->tanggal?->format('d M Y') ?: 'Belum dijadwalkan';
    $statusLabel = fn ($status) => \App\Services\ServiceStatusPresenter::label($status);
@endphp
<div class="service-history-page">
    <header class="service-history-header"><div class="service-history-header-top"><a class="service-history-back" href="{{ route('pelanggan.kendaraan.index') }}" aria-label="Kembali ke Kendaraan Saya">←</a><div><span class="service-history-eyebrow">LAYANAN KENDARAAN</span><h1>Servis Saya</h1><p>Riwayat perawatan kendaraan Anda</p></div></div></header>
    <section class="service-history-overview" aria-label="Ringkasan kendaraan dan servis">
        <div class="service-history-vehicle">
            <div class="service-history-vehicle-art">@if($vehicleImage)<img src="{{ $vehicleImage }}" alt="{{ $vehicleName }}">@else<span aria-hidden="true">🚗</span>@endif</div>
            <div class="service-history-vehicle-copy"><span>KENDARAAN SAYA</span><strong>{{ $vehicleName }}</strong><small>{{ $vehiclePlate }}</small></div>
            <form class="service-history-switcher" method="get"><label for="service-vehicle">Ganti Kendaraan</label><select id="service-vehicle" name="kendaraan" onchange="this.form.submit()">@foreach($vehicles as $vehicle)<option value="{{ $vehicle->id_kendaraan_pelanggan }}" @selected((int) $selectedVehicleId === (int) $vehicle->id_kendaraan_pelanggan)>{{ $vehicle->merk }} {{ $vehicle->model }}</option>@endforeach</select></form>
        </div>
        <article class="service-history-stat is-red"><span aria-hidden="true">⚒</span><small>Total Servis</small><strong>{{ $serviceCount }}</strong><em>Kali servis</em></article>
        <article class="service-history-stat is-green"><span aria-hidden="true">▣</span><small>Servis Terakhir</small><strong>{{ $lastServiceDate }}</strong><em>{{ $lastService ? $statusLabel($lastService->status) : 'Belum ada riwayat' }}</em></article>
        <article class="service-history-stat is-blue"><span aria-hidden="true">▣</span><small>Servis Berikutnya</small><strong>{{ $nextServiceLabel }}</strong><em>{{ $nextBooking?->jenisServis?->nama_servis ?: 'Jadwalkan servis rutin untuk perawatan optimal' }}</em></article>
    </section>
    <div class="service-history-section-heading"><div><h2>Riwayat Servis</h2><p>Catatan perawatan {{ $vehicleName }}</p></div><span class="service-history-sort">Terbaru terlebih dahulu</span></div>
    <section class="service-history-list" aria-label="Daftar riwayat servis">
        @forelse($rows as $service)
            @php
                $serviceStatus = strtolower((string) $service->status);
                $statusClass = str_contains($serviceStatus, 'selesai') ? 'is-complete' : (str_contains($serviceStatus, 'periksa') ? 'is-review' : 'is-progress');
                $serviceType = $service->bookingServis?->jenisServis?->nama_servis ?: 'Servis kendaraan';
                $serviceTotal = $service->transaksi?->total ?? $service->total_biaya ?? 0;
            @endphp
            @php($primaryAssignment = $service->assignments->where('assignment_role', 'primary')->sortByDesc('id_service_work_order_assignment')->first())
            @php($assistantNames = $service->assignments->where('assignment_role', 'assistant')->sortBy('assigned_at')->pluck('petugas.nama_lengkap')->filter()->unique()->values())
            <article class="service-history-item"><div class="service-history-date"><b>{{ $service->tanggal_masuk?->format('d M Y') ?: '—' }}</b><span>{{ $service->tanggal_masuk?->format('H:i') ?: '—' }}</span></div><div class="service-history-item-main"><div class="service-history-item-title"><strong>{{ $service->kode_servis }}</strong><span>{{ $serviceType }}</span><b class="service-history-status {{ $statusClass }}">{{ $statusLabel($service->status) }}</b></div><p>{{ $service->keluhan ?: 'Perawatan dan pemeriksaan kendaraan di bengkel resmi YZA Automotive.' }}</p><small>Mekanik utama: {{ $primaryAssignment?->petugas?->nama_lengkap ?: $service->petugas?->nama_lengkap ?: 'Belum ditugaskan' }}@if($assistantNames->isNotEmpty()) · Pendamping: {{ $assistantNames->join(', ') }}@endif</small><small>{{ $service->tanggal_selesai ? 'Selesai '.$service->tanggal_selesai->format('d M Y H:i') : 'Belum selesai' }}</small></div><div class="service-history-cost"><small>Total Biaya</small><strong>Rp {{ number_format((float) $serviceTotal, 0, ',', '.') }}</strong></div><a class="service-history-detail" href="{{ route('pelanggan.servis.show', ['servis' => $service, 'from' => $returnToGarage ? 'garage' : null]) }}"><span aria-hidden="true">▤</span> Lihat Detail</a></article>
        @empty
            <div class="service-history-empty"><span aria-hidden="true">⚒</span><strong>Belum ada riwayat servis</strong><p>Riwayat perawatan kendaraan akan muncul setelah servis tercatat.</p><a href="{{ route('pelanggan.booking-servis.create', ['kendaraan' => $selectedVehicleId]) }}">Jadwalkan Servis</a></div>
        @endforelse
    </section>
    @if($rows->hasPages())<div class="service-history-pagination">{{ $rows->links() }}</div>@endif
</div>
@endsection
