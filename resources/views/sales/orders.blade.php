@extends('layouts.app')
@section('title', 'Pesanan Mobil · YZA Automotive')
@section('admin-page-title', 'Pesanan Mobil')
@section('content')
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Pesanan</th><th>Pelanggan</th><th>Mobil</th><th>Sales</th><th>Status pembelian</th><th>Serah terima</th></tr></thead><tbody>@forelse($orders as $order)<tr><td><strong>{{ $order->nomor_pesanan }}</strong><small>{{ $order->created_at?->format('d M Y') }}</small></td><td>{{ $order->pelanggan?->nama_lengkap ?: $order->nama_pembeli }}</td><td>{{ $order->snapshot_merk }} {{ $order->snapshot_model }}</td><td>{{ $order->sales?->nama_lengkap ?: 'Digital / belum ditugaskan' }}</td><td>{{ ucwords(str_replace('_',' ', $order->status)) }}</td><td>{{ $order->status === 'diserahkan' ? 'Selesai' : 'Belum' }}</td></tr>@empty<tr><td colspan="6" class="admin-empty">Belum ada pesanan.</td></tr>@endforelse</tbody></table></div>{{ $orders->links() }}</section>
@endsection
