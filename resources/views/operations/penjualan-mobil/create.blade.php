@extends('layouts.app')
@section('title', 'Lanjutkan Penjualan Mobil · YZA Automotive')
@section('admin-page-title', 'Lanjutkan Penjualan Mobil')
@section('content')
@php
    $salesRoutePrefix = request()->routeIs('admin.*') ? 'admin' : 'petugas';
    $orderCount = $sourceOrders->count();
@endphp
<div class="admin-sale-create-page admin-order-continuation-page">
    <div class="sale-create-breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="breadcrumb-home" aria-label="Dashboard">⌂</a>
        <b>›</b>
        <a href="{{ route($salesRoutePrefix.'.penjualan-mobil.index') }}">Penjualan Mobil</a>
        <b>›</b>
        <strong>Lanjutkan dari Pesanan Member</strong>
    </div>

    <div class="sale-create-page-header">
        <div class="sale-create-title-icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 3c-.1.3-.1.7-.1 1.1v4c0 .6.4 1 1 1h2"></path>
                <circle cx="7" cy="17" r="2"></circle>
                <path d="M9 17h6"></path>
                <circle cx="17" cy="17" r="2"></circle>
            </svg>
        </div>
        <div>
            <span class="sale-create-eyebrow">YZA AUTOMOTIVE · MEMBER TRANSACTIONS</span>
            <h1>Lanjutkan Penjualan Mobil</h1>
            <p>Buka dan lanjutkan transaksi penjualan kendaraan yang berasal dari pesanan/SPK YZA Member yang telah disetujui.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="sale-create-alert" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Main Container Card --}}
    <div class="continuation-grid">
        <section class="continuation-card">
            <div class="continuation-card-header">
                <div class="header-icon" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <div>
                    <h2>Lanjutkan penjualan dari pesanan</h2>
                    <p>Pesanan yang disetujui sudah memiliki transaksi. Buka transaksi tersebut tanpa membuat invoice atau reservasi baru. Pesanan awal diverifikasi di menu Pesanan Mobil.</p>
                </div>
            </div>

            <div class="continuation-card-body">
                @if($orderCount > 0)
                    <form method="get" action="{{ route($salesRoutePrefix.'.penjualan-mobil.create') }}" class="continuation-form sale-source-orders">
                        <label for="select-order" class="continuation-label">
                            <span>Pilih Pesanan Member yang Disetujui <em>*</em></span>
                            <small>Tersedia {{ $orderCount }} pesanan aktif yang siap dilanjutkan</small>
                        </label>
                        <div class="continuation-input-group">
                            <select name="id_pesanan" id="select-order" required aria-label="Pesanan yang memenuhi syarat" class="continuation-select">
                                <option value="">-- Pilih pesanan yang sudah disetujui --</option>
                                @foreach($sourceOrders as $order)
                                    <option value="{{ $order->id_pesanan }}" @selected(request('id_pesanan') == $order->id_pesanan)>
                                        {{ $order->nomor_pesanan }} · {{ $order->pelanggan?->nama_lengkap ?? 'Member' }} · {{ $order->snapshot_merk }} {{ $order->snapshot_model }} ({{ ucfirst(str_replace('_', ' ', $order->status)) }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-open-sale">
                                <span>Buka Transaksi</span>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="continuation-empty">
                        <div class="empty-icon" aria-hidden="true">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2"></rect>
                                <path d="m9 12 2 2 4-4"></path>
                            </svg>
                        </div>
                        <h3>Tidak Ada Pesanan yang Perlu Dilanjutkan</h3>
                        <p>Seluruh transaksi penjualan baru dibuat secara otomatis saat pesanan/SPK disetujui oleh admin atau petugas yang berwenang.</p>
                        <div class="empty-actions">
                            <a href="{{ route($salesRoutePrefix.'.pesanan-mobil.index') }}" class="btn-to-orders">
                                <span>Buka Pesanan Mobil</span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                            </a>
                            <a href="{{ route($salesRoutePrefix.'.penjualan-mobil.index') }}" class="btn-to-sales">
                                <span>Kembali ke Daftar Penjualan</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- Side Policy Information Panel --}}
        <aside class="policy-card">
            <div class="policy-card-header">
                <span class="policy-badge" aria-hidden="true">✓</span>
                <h3>Aturan Transaksi Resmi YZA</h3>
            </div>
            <div class="policy-card-body">
                <ul class="policy-steps">
                    <li>
                        <strong>1. Wajib Member Aktif</strong>
                        <p>Seluruh pembelian kendaraan hanya dapat dilakukan oleh pelanggan dengan status YZA Member aktif dan pembayaran terverifikasi.</p>
                    </li>
                    <li>
                        <strong>2. Pengajuan SPK Resmi</strong>
                        <p>Pesanan diajukan oleh Member atau dicatat oleh petugas penjualan resmi di menu Pesanan Mobil.</p>
                    </li>
                    <li>
                        <strong>3. Approval & Transaksi Otomatis</strong>
                        <p>Persetujuan SPK oleh admin akan otomatis menciptakan transaksi penjualan dan nomor penjualan yang sah.</p>
                    </li>
                    <li>
                        <strong>4. Pembayaran & PDI</strong>
                        <p>Pembayaran diverifikasi secara bertahap dan pemeriksaan pra-serah terima (PDI) dilaksanakan sebelum kendaraan diserahkan.</p>
                    </li>
                </ul>
                <div class="policy-footer">
                    <a href="{{ route($salesRoutePrefix.'.pesanan-mobil.index') }}" class="policy-link">
                        <span>Kelola SPK & Pesanan Mobil</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
.admin-order-continuation-page {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 10px 0 36px;
    color: #142038;
    box-sizing: border-box;
}
.sale-create-breadcrumb {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 14px;
    color: #7b8ba2;
    font-size: 12px;
}
.sale-create-breadcrumb a {
    color: #51627b;
    text-decoration: none;
    font-weight: 600;
}
.sale-create-breadcrumb a:hover {
    color: #ed172b;
}
.sale-create-breadcrumb b {
    color: #b2bdca;
}
.sale-create-breadcrumb strong {
    color: #263650;
    font-weight: 700;
}
.sale-create-page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}
.sale-create-title-icon {
    display: grid;
    width: 48px;
    height: 48px;
    place-items: center;
    border-radius: 14px;
    color: #fff;
    background: #ed172b;
    box-shadow: 0 8px 20px rgba(237, 23, 43, 0.22);
    font-size: 22px;
    flex-shrink: 0;
}
.sale-create-eyebrow {
    display: block;
    margin-bottom: 4px;
    color: #ed172b;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: 0.16em;
}
.sale-create-page-header h1 {
    margin: 0;
    color: #142038;
    font-size: 26px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.02em;
}
.sale-create-page-header p {
    margin: 4px 0 0;
    color: #64748b;
    font-size: 13px;
}
.sale-create-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 13px 18px;
    border: 1px solid #fecdd3;
    border-radius: 12px;
    color: #b42338;
    background: #fff1f3;
    font-size: 13px;
    font-weight: 600;
}
.continuation-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.8fr) minmax(320px, 1fr);
    gap: 22px;
    align-items: start;
}
.continuation-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.continuation-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 28px;
    border-bottom: 1px solid #F1F5F9;
}
.continuation-card-header .header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(237, 23, 43, 0.08);
    color: #ED172B;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.continuation-card-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #1E293B;
}
.continuation-card-header p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #64748B;
    line-height: 1.45;
}
.continuation-card-body {
    padding: 28px;
}
.continuation-label {
    display: block;
    margin-bottom: 12px;
}
.continuation-label span {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #1E293B;
}
.continuation-label span em {
    color: #ED172B;
    font-style: normal;
}
.continuation-label small {
    display: block;
    margin-top: 3px;
    color: #64748B;
    font-size: 12px;
}
.continuation-input-group {
    display: flex;
    gap: 12px;
    align-items: center;
}
.continuation-select {
    flex: 1;
    min-width: 0;
    height: 46px;
    padding: 0 16px;
    border: 1px solid #CBD5E1;
    border-radius: 11px;
    background: #FFFFFF;
    color: #1E293B;
    font-size: 13px;
    font-weight: 600;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.continuation-select:focus {
    border-color: #ED172B;
    box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.12);
}
.btn-open-sale {
    height: 46px;
    padding: 0 22px;
    border: none;
    border-radius: 11px;
    background: #ED172B;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 6px 16px rgba(237, 23, 43, 0.25);
    transition: background 0.18s, transform 0.18s;
    white-space: nowrap;
}
.btn-open-sale:hover {
    background: #CB1125;
    transform: translateY(-1px);
}
.continuation-empty {
    text-align: center;
    padding: 32px 16px;
}
.continuation-empty .empty-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    display: grid;
    place-items: center;
}
.continuation-empty h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    color: #1E293B;
}
.continuation-empty p {
    margin: 8px auto 22px;
    max-width: 420px;
    font-size: 13px;
    color: #64748B;
    line-height: 1.5;
}
.empty-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}
.btn-to-orders {
    height: 42px;
    padding: 0 20px;
    border-radius: 10px;
    background: #ED172B;
    color: #FFFFFF;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 5px 14px rgba(237, 23, 43, 0.25);
}
.btn-to-sales {
    height: 42px;
    padding: 0 18px;
    border: 1px solid #CBD5E1;
    border-radius: 10px;
    background: #FFFFFF;
    color: #334155;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
}
.policy-card {
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 20px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.policy-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 24px;
    border-bottom: 1px solid #F1F5F9;
    background: #F8FAFC;
}
.policy-badge {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #10B981;
    color: #FFFFFF;
    display: grid;
    place-items: center;
    font-size: 12px;
    font-weight: 800;
}
.policy-card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    color: #1E293B;
}
.policy-card-body {
    padding: 22px 24px;
}
.policy-steps {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: 16px;
}
.policy-steps strong {
    display: block;
    font-size: 13px;
    color: #1E293B;
    margin-bottom: 3px;
}
.policy-steps p {
    margin: 0;
    font-size: 12px;
    color: #64748B;
    line-height: 1.45;
}
.policy-footer {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px dashed #E2E8F0;
}
.policy-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #ED172B;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}
.policy-link:hover {
    text-decoration: underline;
}
@media (max-width: 900px) {
    .continuation-grid {
        grid-template-columns: 1fr;
    }
    .continuation-input-group {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-open-sale {
        justify-content: center;
    }
}
</style>
@endpush
