@extends('layouts.app')
@section('content')
<div class="page-header"><h1>Ajukan Mobil Anda</h1><p>Mulai proses Trade-In dengan mengirimkan data kendaraan lama Anda.</p></div>
@if($targetCar)
    <article class="card trade-in-target-card"><span class="eyebrow">MOBIL TUJUAN</span><h2>{{ $targetCar->merk }} {{ $targetCar->model }}</h2><strong>{{ money($targetCar->harga_jual) }}</strong><p>Mobil pilihan Anda akan tetap tersimpan sebagai tujuan Trade-In.</p></article>
@endif
<form class="card master-form" method="post" action="{{ route('pelanggan.pengajuan-mobil.store') }}">
    @csrf
    @if($targetCar)<input type="hidden" name="target_mobil" value="{{ $targetCar->id_mobil }}">@endif
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    <label class="form-label" for="id_kendaraan_pelanggan">Kendaraan lama</label>
    <select class="form-control" id="id_kendaraan_pelanggan" name="id_kendaraan_pelanggan" required><option value="">Pilih kendaraan</option>@foreach($vehicles as $v)<option value="{{ $v->id_kendaraan_pelanggan }}">{{ $v->merk }} {{ $v->model }} Â· {{ $v->nomor_polisi }}</option>@endforeach</select>
    <input class="form-control" name="harga_harapan" type="number" min="0" placeholder="Harga harapan" required>
    <textarea class="form-control" name="catatan" placeholder="Catatan"></textarea>
    <button class="button button-primary" type="submit">Kirim Pengajuan Trade-In</button>
</form>
@endsection