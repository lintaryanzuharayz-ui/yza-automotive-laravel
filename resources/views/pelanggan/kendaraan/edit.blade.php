@extends('layouts.app')
@section('title', 'Edit Kendaraan · YZA Automotive')
@section('content')
@php($returnToGarage = request('from') === 'garage')
<link rel="stylesheet" href="{{ asset('assets/css/pages/vehicle-edit.css') }}?v={{ filemtime(public_path('assets/css/pages/vehicle-edit.css')) }}">
<div class="vehicle-edit-page">
    <section class="vehicle-edit-hero" aria-labelledby="vehicle-edit-title">
        <div class="vehicle-edit-hero-inner">
            <a class="vehicle-edit-back" href="{{ $returnToGarage ? route('pelanggan.kendaraan.index') : route('pelanggan.kendaraan.show', $item) }}" aria-label="Kembali" title="Kembali"><span aria-hidden="true">←</span></a>
            <div class="vehicle-edit-hero-copy">
                <nav class="vehicle-edit-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('pelanggan.kendaraan.index') }}">Kendaraan Saya</a><span aria-hidden="true">›</span><span>Edit Kendaraan</span></nav>
                <h1 id="vehicle-edit-title">Edit Kendaraan</h1>
                <p>Perbarui informasi kendaraan Anda agar data selalu akurat.</p>
            </div>
            <div class="vehicle-edit-hero-mark" aria-hidden="true"><span>MORE</span><span>THAN</span><span>A CAR</span><small>A LIFESTYLE</small><i></i></div>
        </div>
    </section>
    <form class="vehicle-edit-layout" method="post" action="{{ route('pelanggan.kendaraan.update', $item) }}">
        @method('PUT')
        @include('pelanggan.kendaraan._form')
    </form>
</div>
@endsection
