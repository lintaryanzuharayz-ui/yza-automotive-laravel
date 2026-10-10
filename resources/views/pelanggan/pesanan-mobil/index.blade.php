@extends('layouts.app')
@section('content')
<div class="container py-4"><h1>Pesanan Mobil Saya</h1><p class="text-muted">Pantau verifikasi, pembayaran, PDI, dan serah-terima kendaraan Anda.</p>
@forelse($orders as $order)<div class="card mb-3"><div class="card-body d-flex justify-content-between gap-3"><div><strong>{{ $order->nomor_pesanan }}</strong><div>{{ $order->snapshot_merk }} {{ $order->snapshot_model }} ({{ $order->snapshot_tahun ?: '-' }})</div></div><div><span class="badge bg-dark">{{ ucwords(str_replace('_',' ', $order->status)) }}</span><a class="btn btn-sm btn-outline-danger ms-2" href="{{ route('pelanggan.pesanan-mobil.show', $order) }}">Detail</a></div></div></div>@empty<div class="alert alert-light border">Belum ada pesanan mobil.</div>@endforelse
{{ $orders->links() }}</div>
@endsection
