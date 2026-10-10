@extends('layouts.app')
@section('content')
<div class="page-header"><div><span class="eyebrow">Booking Servis</span><h1>{{ $booking_servis->kode_booking }}</h1></div></div>
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-error">{{ $errors->first() }}</div>
@endif
<div class="card content-card">
<p>Kendaraan: {{ $booking_servis->kendaraan?->merk }} {{ $booking_servis->kendaraan?->model }}</p>
<p>Jenis: {{ $booking_servis->jenisServis?->nama_servis ?: '—' }}</p>
<p>Jadwal: {{ $booking_servis->tanggal }} {{ $booking_servis->jam }}</p>
<p>Status: {{ $booking_servis->status }}</p>
<p>Keluhan: {{ $booking_servis->keluhan ?: '—' }}</p>
@if(in_array($booking_servis->status, ['menunggu','diterima'], true))
<form method="post" action="{{ route('pelanggan.booking-servis.cancel', $booking_servis) }}">@csrf @method('PATCH')<button class="button button-danger">Batalkan Booking</button></form>
@endif
</div>
@endsection
