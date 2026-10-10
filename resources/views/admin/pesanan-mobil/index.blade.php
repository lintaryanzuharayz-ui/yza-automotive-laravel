@extends('layouts.app')
@section('content')
<div class="container py-4"><h1>Pesanan Mobil Member</h1>@forelse($orders as $order)<div class="card mb-2"><div class="card-body d-flex justify-content-between"><div><strong>{{ $order->nomor_pesanan }}</strong> · {{ $order->pelanggan?->nama_lengkap }} · {{ $order->mobil?->merk }} {{ $order->mobil?->model }}</div><div><span class="badge bg-dark">{{ $order->status }}</span> <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.pesanan-mobil.show', $order) }}">Buka</a></div></div></div>@empty<div class="alert alert-light border">Belum ada pesanan.</div>@endforelse{{ $orders->links() }}</div>
@endsection
