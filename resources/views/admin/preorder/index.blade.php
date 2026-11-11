@extends('layouts.app')
@section('title', 'Pre-Order Kendaraan · Admin YZA Automotive')
@section('admin-page-title', 'Pre-Order Kendaraan')

@push('styles')
<style>
.admin-content:has(> .admin-preorder-page) > .admin-page-heading{display:none!important}
.admin-preorder-page{width:100%;max-width:1680px;margin:0 auto;padding:4px 0 24px;box-sizing:border-box;gap:18px}
.admin-preorder-page .admin-sales-header{display:grid;grid-template-columns:minmax(0,1fr) minmax(520px,1.12fr);align-items:start;gap:26px;min-width:0}
.admin-preorder-page .admin-sales-header-left,.admin-preorder-page .admin-sales-header-right{min-width:0}
.admin-preorder-page .admin-sales-title-group{align-items:flex-start;gap:0}
.admin-preorder-page .admin-sales-icon-box{display:none}
.admin-preorder-page .admin-sales-heading{font-size:35px;line-height:1.08;letter-spacing:-.045em}
.admin-preorder-page .admin-sales-subheading{font-size:13px;line-height:1.45}
.admin-preorder-page .admin-sales-banner{width:100%;height:268px;border-radius:18px;box-shadow:0 14px 30px rgba(15,23,42,.14)}
.admin-preorder-page .admin-preorder-hero-copy{top:30px;left:30px;width:min(430px,46%)}
.admin-preorder-page .admin-preorder-hero-actions{margin-top:16px;gap:9px}
.admin-preorder-page .admin-preorder-benefits{right:25px;bottom:22px;left:auto;gap:18px;max-width:350px}
.admin-preorder-page .admin-preorder-benefits span{font-size:16px}
.admin-preorder-page .admin-preorder-benefits b{font-size:9px}
.admin-preorder-page .admin-preorder-stats{grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.admin-preorder-page .admin-preorder-stats .admin-stat-card{min-width:0;border-radius:16px;box-shadow:0 9px 24px rgba(15,23,42,.06)}
.admin-preorder-page .admin-sales-card{width:100%;min-width:0;box-sizing:border-box;overflow:hidden;border-radius:18px;box-shadow:0 11px 30px rgba(15,23,42,.065)}
.admin-preorder-page .admin-sales-card-header,.admin-preorder-page .admin-sales-filters,.admin-preorder-page .admin-sales-table-wrapper,.admin-preorder-page .admin-sales-pagination{min-width:0}
.admin-preorder-page .card-header-left{min-width:0}
.admin-preorder-page .card-header-left>div:last-child{min-width:0}
.admin-preorder-page .card-header-subtitle{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.admin-preorder-page .admin-sales-filters{display:grid;grid-template-columns:minmax(230px,1.7fr) minmax(170px,.85fr) minmax(250px,1.1fr) auto;gap:10px;align-items:center}
.admin-preorder-page .filter-search-wrap,.admin-preorder-page .filter-select-wrap,.admin-preorder-page .filter-date-wrap{min-width:0;width:100%;box-sizing:border-box}
.admin-preorder-page .admin-sales-table-wrapper{width:100%;max-width:100%;overflow-x:auto;overflow-y:hidden;border-radius:12px}
.admin-preorder-page .admin-preorder-table{width:100%;min-width:1180px;table-layout:fixed}
.admin-preorder-page .admin-preorder-table th{width:auto;white-space:nowrap}
.admin-preorder-page .admin-preorder-table .th-no{width:56px}
.admin-preorder-page .admin-preorder-table th,.admin-preorder-page .admin-preorder-table td{padding:18px 16px;vertical-align:middle}
.admin-preorder-page .admin-preorder-table .td-no{white-space:nowrap;text-align:center}
.admin-preorder-page .admin-preorder-table .car-cell,.admin-preorder-page .admin-preorder-table .customer-cell{min-width:0;gap:12px}
.admin-preorder-page .admin-preorder-table .car-thumb{width:88px;height:58px;flex:0 0 88px;object-fit:cover;border-radius:10px}
.admin-preorder-page .admin-preorder-table .car-meta,.admin-preorder-page .admin-preorder-table .customer-meta{min-width:0}
.admin-preorder-page .admin-preorder-table .car-title,.admin-preorder-page .admin-preorder-table .customer-name{display:block;white-space:normal;overflow-wrap:break-word;line-height:1.5}
.admin-preorder-page .preorder-product-key{display:block;margin-top:5px;font-size:11px;font-weight:400;color:#64748b;line-height:1.5;overflow-wrap:anywhere}
.admin-preorder-page .admin-preorder-table .customer-subtext{display:block;margin-top:4px;white-space:normal;overflow-wrap:break-word}
.admin-preorder-page .admin-preorder-table .customer-avatar{width:38px;height:38px;flex:0 0 38px;font-size:14px}
.admin-preorder-page .admin-preorder-table .badge-status{max-width:100%;box-sizing:border-box;white-space:normal;line-height:1.4;text-align:center}
.admin-preorder-page .admin-preorder-table .td-total{white-space:nowrap;font-size:13px}
.admin-preorder-page .admin-sales-table-wrapper{scrollbar-width:thin;scrollbar-color:#cbd5e1 transparent}
.admin-preorder-page .card-header-subtitle{white-space:normal;line-height:1.6}
.admin-preorder-page .admin-sales-filters{grid-template-columns:minmax(240px,1fr) minmax(180px,.65fr) minmax(290px,.85fr) auto}
.admin-navigation-unified .admin-shell:has(.admin-preorder-page) .admin-topbar{background:#fff!important;backdrop-filter:none!important}
@media(max-width:1500px){.admin-preorder-page .admin-sales-filters{grid-template-columns:minmax(0,1fr) minmax(180px,.6fr)}.admin-preorder-page .filter-actions{justify-content:flex-end}}
.admin-preorder-page .admin-preorder-note{margin:0;border-radius:15px;box-shadow:0 8px 22px rgba(15,23,42,.045)}
@media(max-width:1250px){.admin-preorder-page .admin-sales-header{grid-template-columns:1fr}.admin-preorder-page .admin-sales-header-right{width:100%}.admin-preorder-page .admin-preorder-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.admin-preorder-page .admin-preorder-benefits{right:24px;max-width:320px;gap:13px}}
@media(max-width:760px){.admin-preorder-page{padding-inline:8px}.admin-preorder-page .admin-sales-heading{font-size:29px}.admin-preorder-page .admin-sales-banner{height:330px}.admin-preorder-page .admin-preorder-hero-copy{top:23px;left:22px;width:calc(100% - 44px)}.admin-preorder-page .admin-preorder-benefits{right:22px;bottom:18px;left:22px;max-width:none;justify-content:space-between;gap:9px}.admin-preorder-page .admin-preorder-stats{grid-template-columns:1fr 1fr;gap:9px}.admin-preorder-page .admin-sales-filters{grid-template-columns:1fr}.admin-preorder-page .card-header-subtitle{white-space:normal}.admin-preorder-page .admin-sales-card{padding-inline:12px}}
</style>
<link rel="stylesheet" href="{{ asset('assets/css/preorder-management.css') }}?v={{ filemtime(public_path('assets/css/preorder-management.css')) }}">
@endpush

@section('content')
<div class="admin-sales-page admin-preorder-page">
    <header class="admin-sales-header">
        <div class="admin-sales-header-left">
            <nav class="admin-sales-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 0 2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></a>
                <span class="breadcrumb-separator">/</span><a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span class="breadcrumb-separator">/</span><a href="{{ route('admin.penjualan-mobil.index') }}">Penjualan Mobil</a>
                <span class="breadcrumb-separator">/</span><span class="breadcrumb-current">Pre-Order</span>
            </nav>
            <div class="admin-sales-title-group">
                <div class="admin-sales-icon-box" aria-hidden="true"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg></div>
                <div><h1 class="admin-sales-heading">Pre-Order <span>Kendaraan</span></h1><p class="admin-sales-subheading">Pantau permintaan kendaraan dari member, spesifikasi pilihan, dan status ketersediaan kendaraan.</p></div>
            </div>
        </div>
    </header>

    <section class="admin-orders-stats-grid admin-preorder-stats" aria-label="Statistik Pre-Order">
        @foreach([
            ['label' => 'Pre-Order Aktif', 'value' => $stats['active'], 'tone' => 'red', 'icon' => '▱', 'hint' => 'Seluruh permintaan aktif global'],
            ['label' => 'Menunggu Stok', 'value' => $stats['waiting'], 'tone' => 'yellow', 'icon' => '◷', 'hint' => 'Belum tersedia di inventori'],
            ['label' => 'Perlu Tindak Lanjut', 'value' => $stats['follow_up'], 'tone' => 'blue', 'icon' => '!', 'hint' => 'Stok tersedia atau dinotifikasi'],
            ['label' => 'Lanjut ke Checkout', 'value' => $stats['checkout'], 'tone' => 'green', 'icon' => '✓', 'hint' => 'Mengikuti alur pembelian resmi'],
        ] as $stat)
            <div class="admin-stat-card is-{{ $stat['tone'] }}"><div class="admin-stat-top"><div class="admin-stat-icon is-{{ $stat['tone'] }}" aria-hidden="true">{{ $stat['icon'] }}</div><div class="admin-stat-info"><span class="admin-stat-label">{{ $stat['label'] }}</span><strong class="admin-stat-value" data-preorder-stat="{{ $loop->index }}">{{ number_format($stat['value']) }}</strong></div></div><div class="admin-stat-bottom"><span class="admin-stat-status is-{{ $stat['tone'] }}-text">{{ $stat['hint'] }}</span></div></div>
        @endforeach
    </section>

    <section class="admin-sales-card" id="preorder-list">
        <div class="admin-sales-card-header"><div class="card-header-left"><div class="card-header-icon" aria-hidden="true">▱</div><div><h2 class="card-header-title">Daftar Pre-Order Kendaraan</h2><p class="card-header-subtitle">Kelola permintaan kendaraan dan pantau ketersediaan sesuai spesifikasi pelanggan.</p></div></div></div>

        <form class="admin-sales-filters" method="get" action="{{ route('admin.preorder-mobil.index') }}">
            <div class="filter-search-wrap"><svg class="filter-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg><input type="search" name="q" value="{{ $keyword }}" class="filter-search-input" placeholder="Cari kendaraan, member, atau kode pre-order..." aria-label="Cari pre-order"></div>
            <div class="filter-select-wrap"><select name="status" class="filter-select" aria-label="Filter status"><option value="">Semua Status</option><option value="menunggu_stok" @selected($status === 'menunggu_stok')>Menunggu Stok</option><option value="stok_tersedia" @selected($status === 'stok_tersedia')>Stok Tersedia</option><option value="notifikasi_terkirim" @selected($status === 'notifikasi_terkirim')>Notifikasi Terkirim</option><option value="dilanjutkan_ke_checkout" @selected($status === 'dilanjutkan_ke_checkout')>Lanjut ke Checkout</option><option value="dibatalkan" @selected($status === 'dibatalkan')>Dibatalkan</option></select><svg class="select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
            <div class="filter-date-wrap"><svg class="filter-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg><input type="date" name="from" value="{{ $from }}" class="filter-date-input" aria-label="Dari tanggal" title="Dari tanggal"><span class="date-sep">–</span><input type="date" name="to" value="{{ $to }}" class="filter-date-input" aria-label="Sampai tanggal" title="Sampai tanggal"></div>
            <div class="filter-actions"><button type="submit" class="btn-filter-apply is-red">Terapkan</button><a href="{{ route('admin.preorder-mobil.index') }}" class="btn-filter-reset">↻ <span>Reset</span></a></div>
        </form>

        <div class="admin-sales-table-wrapper" tabindex="0" role="region" aria-label="Daftar pre-order, geser untuk melihat seluruh kolom"><table class="admin-sales-table admin-preorder-table"><colgroup><col style="width:56px"><col style="width:260px"><col style="width:230px"><col style="width:210px"><col style="width:160px"><col style="width:140px"><col style="width:124px"></colgroup><thead><tr><th class="th-no" scope="col">ID</th><th class="th-car" scope="col">Kendaraan</th><th class="th-customer" scope="col">Member</th><th class="th-spec" scope="col">Spesifikasi Pilihan</th><th class="th-status" scope="col">Status</th><th class="th-total" scope="col">Terdaftar</th><th class="preorder-action-column" scope="col">Aksi</th></tr></thead><tbody>
            @forelse($preorders as $item)
                @php
                    $photo = $item->presentation_image;
                @endphp
                @php($statusLabel = ucwords(str_replace('_', ' ', $item->status)))
                <tr data-preorder-row="{{ $item->id_pre_order }}"><td class="td-no"><strong>#{{ $item->id_pre_order }}</strong></td><td><div class="car-cell"><img class="car-thumb" src="{{ $photo }}" alt="{{ $item->nama_mobil }}" loading="lazy"><div class="car-meta"><strong class="car-title">{{ $item->nama_mobil }}</strong><span class="preorder-product-key">{{ $item->product_key }}</span></div></div></td><td><div class="customer-cell"><div class="customer-avatar" aria-hidden="true">{{ strtoupper(substr($item->pelanggan?->nama_lengkap ?: 'P', 0, 1)) }}</div><div class="customer-meta"><strong class="customer-name">{{ $item->pelanggan?->nama_lengkap ?: 'Member tidak tersedia' }}</strong><span class="customer-subtext">{{ $item->pelanggan?->kode_pelanggan ?: 'Member YZA' }}</span></div></div></td><td><div class="preorder-spec"><strong>{{ $item->varian ?: 'Varian mengikuti ketersediaan' }}</strong><span>{{ $item->tahun ?: 'Tahun —' }} · {{ $item->warna ?: 'Warna mengikuti ketersediaan' }}</span></div></td><td class="td-status"><span class="badge-status {{ $item->status === 'menunggu_stok' ? 'is-waiting' : ($item->status === 'dibatalkan' ? 'is-cancelled' : ($item->status === 'dilanjutkan_ke_checkout' ? 'is-success' : 'is-confirmed')) }}">{{ $statusLabel }}</span></td><td class="td-total"><div>{{ $item->created_at?->translatedFormat('d M Y') }}</div><small class="sale-date">{{ $item->created_at?->format('H:i') }}</small></td><td class="preorder-action-column"><div class="preorder-row-actions"><button type="button" data-preorder-open="detail" data-detail-url="{{ route('admin.preorder-mobil.detail', $item) }}" data-stock-url="{{ route('admin.preorder-mobil.stock', $item) }}" data-action-url="{{ route('admin.preorder-mobil.action', $item) }}" aria-haspopup="dialog" aria-label="Tinjau pre-order {{ $item->nama_mobil }}">Tinjau</button></div></td></tr>
            @empty
                <tr><td colspan="7" class="table-empty-cell"><div class="empty-state"><div class="empty-state-icon">⌕</div><h3>Tidak Ada Pre-Order</h3><p>Belum ada data yang sesuai dengan filter pencarian Anda.</p><a href="{{ route('admin.preorder-mobil.index') }}" class="btn-empty-reset">Reset Filter</a></div></td></tr>
            @endforelse
        </tbody></table></div>
        <div class="admin-sales-pagination"><div class="pagination-info">Menampilkan <strong>{{ $preorders->firstItem() ?: 0 }}</strong> – <strong>{{ $preorders->lastItem() ?: 0 }}</strong> dari <strong>{{ $preorders->total() }}</strong> pre-order</div><div class="pagination-links">{{ $preorders->links('pagination::bootstrap-4') }}</div></div>
    </section>

    <aside class="admin-preorder-note"><div class="admin-preorder-note-icon">i</div><div><strong>Informasi Pre-Order</strong><p>Permintaan pre-order mencatat minat dan spesifikasi kendaraan pelanggan. Ketersediaan stok dan harga harus mengikuti informasi resmi yang telah diverifikasi.</p></div></aside>
</div>
@include('admin.preorder.management-modal')
@endsection
@push('scripts')
<script src="{{ asset('assets/js/preorder-management.js') }}?v={{ filemtime(public_path('assets/js/preorder-management.js')) }}" defer></script>
@endpush
