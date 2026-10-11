@extends('layouts.app')
@section('title', 'Pesanan Mobil - Admin YZA Automotive')
@section('admin-page-title', 'Pesanan Mobil')
@section('content')
<div class="admin-orders-page">
    <div class="admin-page-heading"><div><div class="admin-breadcrumb-light">Dashboard / Penjualan Mobil / Pesanan Mobil</div><h1>Pesanan Mobil Member</h1><p>Kelola dan pantau pesanan mobil dari member, verifikasi pembayaran, dan proses serah-terima.</p></div><div class="admin-total-card"><span>▣</span><div><small>Total Pesanan</small><strong>{{ number_format($stats['total']) }}</strong><em>pesanan</em></div></div></div>
    <div class="admin-order-stats">
        @foreach([['Semua Pesanan','total','', 'Total pesanan member','neutral','≡'],['Menunggu Verifikasi','menunggu_verifikasi','menunggu_verifikasi','Perlu diverifikasi','warning','◷'],['Dikonfirmasi','dikonfirmasi','menunggu_pembayaran','Siap diproses','success','✓'],['Dalam Proses','proses','lunas','Sedang dipersiapkan','info','▣'],['Selesai','selesai','diserahkan','Pesanan selesai','danger','▰']] as [$label,$key,$filter,$hint,$tone,$icon])
            <a class="admin-order-stat is-{{ $tone }}" href="{{ route('admin.pesanan-mobil.index', $filter === '' ? [] : ['status' => $filter]) }}"><span class="admin-stat-icon">{{ $icon }}</span><div><small>{{ $label }}</small><strong>{{ number_format($stats[$key]) }}</strong><em>{{ $hint }}</em></div></a>
        @endforeach
    </div>
    <section class="admin-orders-card">
        <div class="admin-section-heading"><div><span class="admin-section-icon">≡</span><div><h2>Daftar Pesanan Mobil</h2><p>Semua data pesanan mobil dari member.</p></div></div><a class="admin-primary-button" href="{{ route('admin.penjualan-mobil.create') }}">＋ Tambah Pesanan <span>⌄</span></a></div>
        <form class="admin-order-filters" method="get" action="{{ route('admin.pesanan-mobil.index') }}">
            <label class="admin-search-field"><span>⌕</span><input name="q" value="{{ $keyword }}" placeholder="Cari berdasarkan Order ID, nama pelanggan, atau mobil..."></label>
            <label><select name="status"><option value="">Semua Status</option>@foreach(['menunggu_verifikasi','menunggu_pembayaran','lunas','pdi_lulus','pdi_gagal','siap_diambil','diserahkan','ditolak'] as $option)<option value="{{ $option }}" @selected($status === $option)>{{ ucwords(str_replace('_',' ', $option)) }}</option>@endforeach</select></label>
            <label><input type="date" name="from" value="{{ request('from') }}" aria-label="Tanggal mulai"></label>
            <label><input type="date" name="to" value="{{ request('to') }}" aria-label="Tanggal akhir"></label>
            <button class="admin-reset-button" type="submit">Terapkan</button><a class="admin-reset-button" href="{{ route('admin.pesanan-mobil.index') }}">↻ Reset</a>
        </form>
        <div class="admin-orders-table-wrap"><table class="admin-orders-table"><thead><tr><th>#</th><th>Order ID</th><th>Customer</th><th>Mobil</th><th>Tanggal Pesan</th><th>Pembayaran</th><th>Status</th><th>Total</th><th>Aksi</th></tr></thead><tbody>
            @forelse($orders as $order)
                <tr><td>{{ $orders->firstItem() + $loop->index }}</td><td><a class="admin-order-id" href="{{ route('admin.pesanan-mobil.show', $order) }}">{{ $order->nomor_pesanan }} ↗</a></td><td><strong>{{ $order->pelanggan?->nama_lengkap ?: $order->nama_pembeli }}</strong><small>{{ $order->email_pembeli }}</small></td><td><div class="admin-car-cell">@if($order->mobil?->foto?->first())<img src="{{ $order->mobil->foto->first()->url() }}" alt="{{ $order->snapshot_merk }} {{ $order->snapshot_model }}">@else<span class="admin-car-placeholder">▣</span>@endif<div><strong>{{ $order->snapshot_merk }} {{ $order->snapshot_model }}</strong><small>{{ $order->snapshot_tipe ?: 'New Car' }}</small></div></div></td><td>{{ $order->created_at?->format('d M Y') }}<small>{{ $order->created_at?->format('H:i') }}</small></td><td><span class="admin-status-pill is-payment">{{ $order->transaksi?->pembayarans?->first()?->status ? ucwords(str_replace('_',' ',$order->transaksi->pembayarans->first()->status)) : 'Menunggu Pembayaran' }}</span></td><td><span class="admin-status-pill is-order">{{ ucwords(str_replace('_',' ', $order->status)) }}</span></td><td><strong>Rp {{ number_format((float) $order->total_tagihan, 0, ',', '.') }}</strong></td><td><a class="admin-more-button" href="{{ route('admin.pesanan-mobil.show', $order) }}" aria-label="Buka pesanan {{ $order->nomor_pesanan }}">•••</a></td></tr>
            @empty
                <tr><td colspan="9" class="admin-empty-row">Belum ada pesanan yang sesuai filter.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="admin-table-footer"><span>Menampilkan {{ $orders->firstItem() ?: 0 }}-{{ $orders->lastItem() ?: 0 }} dari {{ $orders->total() }} pesanan</span>{{ $orders->links() }}</div>
    </section>
</div>
@endsection
