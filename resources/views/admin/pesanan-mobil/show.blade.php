@extends('layouts.app')
@section('title', 'Detail Pesanan - Admin YZA Automotive')
@section('admin-page-title', 'Detail Pesanan')
@section('content')
<div class="container py-4">
    <h1>{{ $order->nomor_pesanan }}</h1>
    <p>{{ $order->pelanggan?->nama_lengkap }} &middot; {{ $order->snapshot_merk }} {{ $order->snapshot_model }} &middot; Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</p>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card mb-3"><div class="card-body"><strong>Status: {{ ucwords(str_replace('_', ' ', $order->status)) }}</strong>@if($order->transaksi)<p class="mt-3">Transaksi: {{ $order->transaksi->status }} &middot; <a href="{{ route('admin.invoice.car-sale', $order->transaksi) }}">Invoice</a></p>@endif</div></div>
    @if($order->transaksi && $order->transaksi->status === 'lunas')
        <div class="card mb-3"><div class="card-body"><h4>PDI</h4><form method="post" action="{{ route('admin.pesanan-mobil.pdi', $order) }}">@csrf<div class="row">@foreach(['mesin','eksterior','interior','rem','ban','kelistrikan','lampu','ac','dokumen','final_quality'] as $item)<div class="col-md-4 form-check"><input class="form-check-input" type="checkbox" name="{{ $item }}" value="1" id="{{ $item }}" @checked($order->pdi?->$item)><label class="form-check-label" for="{{ $item }}">{{ ucwords(str_replace('_',' ', $item)) }}</label></div>@endforeach</div><textarea class="form-control my-3" name="catatan" placeholder="Catatan PDI"></textarea><button class="btn btn-dark">Simpan PDI</button></form></div></div>
    @endif
    @if(in_array($order->status, ['lunas','pdi_lulus','siap_diambil'], true))
        <div class="card mb-3"><div class="card-body"><h4>Pengambilan &amp; Serah Terima</h4><form method="post" action="{{ route('admin.pesanan-mobil.schedule', $order) }}" class="mb-3">@csrf<input class="form-control" type="datetime-local" name="jadwal_pengambilan" value="{{ $order->jadwal_pengambilan?->format('Y-m-d\TH:i') }}"><button class="btn btn-outline-secondary mt-2">Simpan Jadwal</button></form>@if($order->status === 'siap_diambil')<form method="post" action="{{ route('admin.pesanan-mobil.handover', $order) }}">@csrf<input class="form-control mb-2" name="nama_penerima" placeholder="Nama penerima" required><input class="form-control mb-2" name="nomor_identitas" placeholder="Nomor identitas"><textarea class="form-control mb-2" name="catatan" placeholder="Catatan serah-terima"></textarea><button class="btn btn-danger">Finalisasi Serah Terima</button></form>@endif</div></div>
    @endif
</div>
@endsection
