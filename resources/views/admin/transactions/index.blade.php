@extends('layouts.app')

@php
    $pageTitle = $invoiceMode ? 'Invoice & Kwitansi' : 'Semua Transaksi';
    $pageDescription = $invoiceMode ? 'Kelola dokumen invoice dan kwitansi resmi dari seluruh transaksi YZA Automotive.' : 'Kelola dan pantau seluruh transaksi dari sumber keuangan YZA Automotive.';
    $filterAction = $invoiceMode ? route('admin.invoices.index') : route('admin.transaksi.index');
@endphp

@section('title', $pageTitle . ' - Admin YZA Automotive')
@section('admin-page-title', $pageTitle)

@section('content')
<div class="admin-transactions-page {{ $invoiceMode ? 'is-invoice-mode' : '' }}">
    <div class="transactions-breadcrumb"><span class="transactions-breadcrumb-mark">‹</span><span>Keuangan</span><span class="transactions-breadcrumb-divider">›</span><strong>{{ $pageTitle }}</strong></div>

    <header class="transactions-page-header">
        <div class="transactions-heading-copy"><span class="transactions-eyebrow">KEUANGAN</span><h1>{{ $pageTitle }}</h1><p>{{ $pageDescription }}</p></div>
        <div class="transactions-hero"><div class="transactions-hero-copy"><span>YZA AUTOMOTIVE</span><h2>Setiap Transaksi<br>Menggerakkan <em>Perjalanan</em></h2><p>Dari penjualan, servis, hingga trade-in — semua tercatat dalam satu sistem.</p></div></div>
    </header>

    @if(!$invoiceMode)
    <section class="transactions-stats" aria-label="Ringkasan transaksi">
        @php $statCards = [
            ['label' => 'Total Transaksi', 'value' => $transactionStats['total'], 'caption' => 'Seluruh transaksi', 'tone' => 'red', 'icon' => '▣', 'trend' => '↑ 12%'],
            ['label' => 'Penjualan Mobil', 'value' => $transactionStats['sales'], 'caption' => 'Transaksi penjualan', 'tone' => 'pink', 'icon' => '▱', 'trend' => '↑ 8%'],
            ['label' => 'Servis', 'value' => $transactionStats['service'], 'caption' => 'Transaksi servis', 'tone' => 'rose', 'icon' => '✣', 'trend' => '↑ 15%'],
            ['label' => 'Trade-In', 'value' => $transactionStats['trade_in'], 'caption' => 'Transaksi trade-in', 'tone' => 'blue', 'icon' => '⇄', 'trend' => '↑ 9%'],
            ['label' => 'Pembayaran Lunas', 'value' => $transactionStats['paid'], 'caption' => 'Transaksi selesai', 'tone' => 'green', 'icon' => '✓', 'trend' => '↑ 14%'],
            ['label' => 'Belum Bayar', 'value' => $transactionStats['unpaid'], 'caption' => 'Menunggu pembayaran', 'tone' => 'orange', 'icon' => '×', 'trend' => '↓ 6%'],
        ]; @endphp
        @foreach($statCards as $card)<article class="transaction-stat-card tone-{{ $card['tone'] }}"><div class="transaction-stat-icon">{{ $card['icon'] }}</div><div class="transaction-stat-main"><div class="transaction-stat-topline"><span>{{ $card['label'] }}</span><b>{{ $card['trend'] }}</b></div><strong>{{ number_format($card['value'], 0, ',', '.') }}</strong><small>{{ $card['caption'] }}</small></div></article>@endforeach
    </section>
    @endif

    <section class="transactions-panel">
        <div class="transactions-panel-heading"><div class="transactions-section-icon">▣</div><div><h2>{{ $invoiceMode ? 'Daftar Invoice & Kwitansi' : 'Daftar Transaksi' }}</h2><p>{{ $invoiceMode ? 'Akses dokumen resmi berdasarkan jenis transaksi.' : 'Ringkasan transaksi dari sumber keuangan YZA Automotive.' }}</p></div><div class="transactions-panel-meta"><span>Menampilkan {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} dari {{ $transactions->total() }} transaksi</span><button type="button" class="transactions-export" data-print-transactions><span>⇩</span> Export <b>⌄</b></button></div></div>

        <form method="get" action="{{ $filterAction }}" class="transactions-filter-bar">
            <label class="transactions-search-field"><span>⌕</span><input name="q" value="{{ $q }}" placeholder="Cari kode transaksi, nama pelanggan, atau jenis transaksi..."></label>
            <select name="type" aria-label="Jenis transaksi"><option value="">Semua Jenis</option>@foreach($types as $item)<option value="{{ $item }}" @selected($type === $item)>{{ ucwords(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            <select name="status" aria-label="Status transaksi"><option value="">Semua Status</option>@foreach($statuses as $item)<option value="{{ $item }}" @selected($status === $item)>{{ ucwords(str_replace('_', ' ', $item)) }}</option>@endforeach</select>
            <label class="transactions-date-field"><span>▣</span><input type="date" name="from" value="{{ $from }}" aria-label="Tanggal mulai"><i>–</i><input type="date" name="to" value="{{ $to }}" aria-label="Tanggal selesai"></label>
            @if($invoiceMode)<input type="hidden" name="invoice_mode" value="1">@endif<input type="hidden" name="per_page" value="{{ $perPage }}">
            <a class="transactions-reset" href="{{ $filterAction }}{{ $invoiceMode ? '?invoice_mode=1' : '' }}">↻ <span>Reset</span></a><button class="transactions-apply" type="submit">⌕ <span>Terapkan Filter</span></button>
        </form>

        <div class="transactions-table-wrap"><table class="transactions-table"><thead><tr><th class="transactions-check"><input type="checkbox" aria-label="Pilih semua"></th><th>Kode</th><th>Tanggal <small>↕</small></th><th>Pelanggan</th><th>Jenis</th><th>Total <small>↕</small></th><th>Status <small>↕</small></th><th>Aksi</th></tr></thead><tbody>
        @forelse($transactions as $transaction)
            @php
                $invoiceUrl = match($transaction->jenis_transaksi) { 'servis' => route('admin.invoice.servis', $transaction), 'cuci_mobil' => route('admin.invoice.carwash', $transaction), 'penjualan_sparepart' => route('admin.invoice.sparepart', $transaction), 'penjualan_mobil' => route('admin.invoice.car-sale', $transaction), 'trade_in' => $transaction->tradeIn ? route('admin.invoice.trade-in', $transaction->tradeIn) : null, 'pembelian_mobil' => $transaction->pembelianMobilBekas ? route('admin.invoice.used-car', $transaction->pembelianMobilBekas) : null, default => null };
                $typeLabel = ucwords(str_replace('_', ' ', $transaction->jenis_transaksi)); $statusLabel = ucwords(str_replace('_', ' ', $transaction->status));
                $statusTone = in_array($transaction->status, ['lunas', 'terverifikasi', 'selesai'], true) ? 'success' : (in_array($transaction->status, ['belum_bayar', 'menunggu_pembayaran'], true) ? 'warning' : ($transaction->status === 'dibatalkan' ? 'danger' : 'info'));
                $typeIcon = in_array($transaction->jenis_transaksi, ['penjualan_mobil', 'pembelian_mobil'], true) ? '▱' : ($transaction->jenis_transaksi === 'trade_in' ? '⇄' : '✣'); $customerName = $transaction->pelanggan?->nama_lengkap ?: 'Walk-in Customer';
            @endphp
            <tr><td class="transactions-check"><input type="checkbox" aria-label="Pilih {{ $transaction->kode_transaksi }}"></td><td><strong class="transaction-code">{{ $transaction->kode_transaksi }}</strong></td><td><span class="transaction-date">{{ $transaction->tanggal?->format('d M Y') }}</span><small>{{ $transaction->tanggal?->format('H:i') }}</small></td><td><div class="transaction-customer"><span class="transaction-avatar">{{ mb_strtoupper(mb_substr($customerName, 0, 1)) }}</span><span><strong>{{ $customerName }}</strong><small>{{ $transaction->pelanggan?->kode_pelanggan ?: 'Pelanggan' }}</small></span></div></td><td><div class="transaction-kind"><span>{{ $typeIcon }}</span>{{ $typeLabel }}</div></td><td><strong class="transaction-total">Rp {{ number_format((float) $transaction->total, 0, ',', '.') }}</strong></td><td><span class="transaction-status status-{{ $statusTone }}"><i></i>{{ $statusLabel }}</span></td><td><div class="transaction-actions">@if($invoiceUrl)<a href="{{ $invoiceUrl }}" class="transaction-detail">◉&nbsp; Detail</a>@else<span class="transaction-detail is-disabled">◉&nbsp; Detail</span>@endif<button type="button" class="transaction-more" aria-label="Pilihan lainnya">⋮</button></div></td></tr>
        @empty
            <tr><td colspan="8" class="transactions-empty"><span>⌕</span><strong>Belum ada transaksi yang sesuai</strong><small>Coba ubah kata kunci atau filter yang dipilih.</small></td></tr>
        @endforelse
        </tbody></table></div>

        <div class="transactions-pagination"><span>Tampilkan</span><select onchange="window.location.href=this.value"><option value="{{ $transactions->appends(request()->except('per_page'))->url(1) }}&per_page=10" @selected($perPage === 10)>10</option><option value="{{ $transactions->appends(request()->except('per_page'))->url(1) }}&per_page=20" @selected($perPage === 20)>20</option><option value="{{ $transactions->appends(request()->except('per_page'))->url(1) }}&per_page=50" @selected($perPage === 50)>50</option></select><span>per halaman</span><div>{{ $transactions->onEachSide(1)->links('pagination::bootstrap-4') }}</div></div>
    </section>
</div>
@push('scripts')<script>document.querySelector('[data-print-transactions]')?.addEventListener('click', () => window.print());</script>@endpush
@endsection
