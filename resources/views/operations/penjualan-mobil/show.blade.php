@extends('layouts.app')
@section('content')
<div class="page-header"><div><span class="eyebrow">Penjualan Mobil</span><h1>{{ $sale->kode_penjualan }}</h1><p>{{ $sale->pelanggan?->nama_lengkap ?: 'Data pelanggan tidak tersedia' }} · {{ $vehicle['name'] }}</p></div></div>
<article class="card detail-card">
    <img src="{{ $vehicle['image'] }}" alt="{{ $vehicle['image_alt'] }}" style="width:min(100%,420px);aspect-ratio:16/10;object-fit:cover;border-radius:12px" onerror="this.onerror=null;this.src='{{ asset('assets/images/vehicle-photo-unavailable.svg') }}'">
    <p>{{ $vehicle['source'] }}</p>
    <p>{{ $vehicle['photo_source'] }}</p>
    @if(!$vehicle['has_photo'])<p>Foto model dan warna yang sesuai belum tersedia.</p>@endif
    @if($vehicle['warning'])<p class="alert alert-error">{{ $vehicle['warning'] }}</p>@endif
    <div class="info-grid">
        @foreach(['unit_code'=>'Kode unit terhubung','name'=>'Kendaraan','variant'=>'Varian','year'=>'Tahun','color'=>'Warna','order_number'=>'Pesanan member','order_status'=>'Status pesanan'] as $field=>$label)
        <div class="info-item"><small>{{ $label }}</small><strong>{{ $vehicle[$field] ?: 'Belum tercatat' }}</strong></div>
        @endforeach
        <div class="info-item"><small>Harga snapshot penjualan</small><strong>{{ money($sale->harga_mobil) }}</strong></div>
        <div class="info-item"><small>Diskon</small><strong>{{ money($sale->diskon) }}</strong></div>
        <div class="info-item"><small>Total</small><strong>{{ money($sale->total_akhir) }}</strong></div>
        <div class="info-item"><small>Status penjualan</small><strong>{{ ucfirst($sale->status) }}</strong></div>
        <div class="info-item"><small>Status transaksi</small><strong>{{ $sale->transaksi ? ucfirst(str_replace('_',' ',$sale->transaksi->status)) : 'Belum tercatat' }}</strong></div>
    </div>
</article>
@endsection
