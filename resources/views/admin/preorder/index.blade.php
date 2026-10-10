@extends('layouts.app')
@section('title', 'Pre-order Kendaraan · Admin')
@section('content')
<div class="page-header"><div><span class="eyebrow">Inventori & Customer Intent</span><h1>Pre-order Kendaraan</h1><p>Menunggu stok: <strong>{{ $waiting }}</strong>. Tidak ada angka stok atau harga yang dibuat dari pre-order.</p></div></div>
<div class="card content-card"><div class="table-wrap"><table class="data-table"><thead><tr><th>Kendaraan</th><th>Member</th><th>Spesifikasi</th><th>Status</th><th>Terdaftar</th></tr></thead><tbody>@forelse($preorders as $item)<tr><td><strong>{{ $item->nama_mobil }}</strong><small>{{ $item->showcase_slug ?: ($item->mobil?->kode_mobil ?: 'Identitas katalog') }}</small></td><td>{{ $item->pelanggan?->nama_lengkap ?: '—' }}</td><td>{{ $item->varian ?: '—' }} · {{ $item->tahun ?: '—' }} · {{ $item->warna ?: '—' }}</td><td>{{ strtoupper(str_replace('_',' ', $item->status)) }}</td><td>{{ $item->created_at?->format('d M Y H:i') }}</td></tr>@empty<tr><td colspan="5">Belum ada pre-order.</td></tr>@endforelse</tbody></table></div>{{ $preorders->links() }}</div>
@endsection
