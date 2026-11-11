@extends('layouts.app')
@section('title', 'YZA Automotive — Executive Dashboard')
@section('admin-page-title', 'Executive Overview')
@section('owner-breadcrumb', 'Executive Overview')
@section('content')
@php
    $rupiah = fn ($amount) => 'Rp '.number_format((float) $amount, 0, ',', '.');
    $reportPeriod = ['from' => $period['from']->toDateString(), 'to' => $period['to']->toDateString()];
    $stats = [
        ['Penjualan Final', 'final_sales', 'car', 'Periode terpilih · unit', false],
        ['Kendaraan Diserahkan', 'handed_over', 'check', 'Penjualan final dalam periode · unit', false],
        ['Nilai Penjualan Final', 'final_sales_value', 'chart', 'Periode terpilih · nilai transaksi', true],
        ['Pesanan Aktif', 'active_orders', 'orders', 'Dibuat dalam periode · belum final', false],
        ['Pembayaran Terverifikasi', 'verified_payments', 'wallet', 'Tanggal verifikasi dalam periode', true],
        ['Kendaraan Tersedia', 'available_stock', 'car', 'Stok aktual saat halaman dibuka · unit', false],
        ['Work Order Aktif', 'active_work_orders', 'tool', 'Snapshot aktual · pemeriksaan / dikerjakan', false],
        ['Pelanggan / Member', 'customers', 'users', 'Snapshot aktual · seluruh pelanggan', false],
    ];
    $inventoryTotal = array_sum(array_column($ownerInventory, 'units'));
    $inventoryLabels = ['tersedia' => 'Tersedia', 'dibooking' => 'Dipesan', 'terjual' => 'Terjual'];
@endphp
<div class="oe-dashboard">
    <header class="oe-page-header">
        <div><span class="oe-eyebrow">YZA EXECUTIVE INTELLIGENCE</span><h1>Executive Overview<span>.</span></h1><p>Pantau performa penjualan, pembayaran, operasional, dan inventori Anda.</p></div>
        @if(Route::has('owner.sales-report') && auth()->user()->hasPermission('reports.view'))<a class="oe-button oe-button-dark" href="{{ route('owner.sales-report', $reportPeriod) }}">Lihat Rekap Penjualan <x-owner-icon name="arrow"/></a>@endif
    </header>
    <section class="oe-period-bar" aria-label="Periode laporan">
        <div><span class="oe-period-dot"></span><strong>Periode laporan</strong><span>{{ $period['from']->format('d M Y') }} — {{ $period['to']->format('d M Y') }}</span></div>
        <form method="get" class="oe-period-form"><label><span>Dari</span><input type="date" name="from" value="{{ $reportPeriod['from'] }}" required></label><span class="oe-date-divider" aria-hidden="true">—</span><label><span>Sampai</span><input type="date" name="to" value="{{ $reportPeriod['to'] }}" required></label><button class="oe-button oe-button-red" type="submit">Terapkan Periode</button></form>
    </section>
    <section class="oe-kpis" aria-label="Ringkasan kinerja bisnis">
        @foreach($stats as [$label, $key, $icon, $scope, $money])
        <article class="oe-kpi {{ $loop->index === 2 ? 'oe-kpi-featured' : '' }}" data-kpi="{{ $key }}"><div class="oe-kpi-top"><h2>{{ $label }}</h2><span class="oe-kpi-icon"><x-owner-icon :name="$icon"/></span></div><strong class="oe-kpi-value {{ $money ? 'is-money' : '' }}">{{ $money ? $rupiah($kpis[$key]) : number_format($kpis[$key], 0, ',', '.') }}</strong><p>{{ $scope }}</p></article>
        @endforeach
    </section>
    <div class="oe-section-label"><span>BUSINESS ANALYTICS</span><span>Data aktual · sumber transaksi YZA Automotive</span></div>
    <div class="oe-analytics-grid">
        <section class="oe-panel oe-sales-panel"><header class="oe-panel-heading"><div><span class="oe-eyebrow">SALES INTELLIGENCE</span><h2>Sales Performance</h2><p>Tren penjualan kendaraan berdasarkan periode laporan.</p></div><span class="oe-chip">Bulanan</span></header>
            @if(count($monthlySales))
            <div class="oe-chart-heading"><span><i class="oe-legend-dot"></i>Unit kendaraan terjual</span><strong>{{ number_format($kpis['final_sales'], 0, ',', '.') }} unit</strong></div>
            <div class="oe-chart" data-owner-chart="units"><script type="application/json">@json($monthlySales)</script></div>
            <div class="oe-chart-heading"><span><i class="oe-legend-dot navy"></i>Nilai transaksi final</span><strong>{{ $rupiah($kpis['final_sales_value']) }}</strong></div>
            <div class="oe-chart oe-chart-value" data-owner-chart="amount"><script type="application/json">@json($monthlySales)</script></div>
            <details class="oe-chart-data"><summary>Lihat data grafik</summary><div class="oe-table-wrap"><table><thead><tr><th>Bulan</th><th>Unit</th><th>Nilai final</th></tr></thead><tbody>@foreach($monthlySales as $month)<tr><td>{{ $month['period'] }}</td><td>{{ $month['units'] }}</td><td>{{ $rupiah($month['amount']) }}</td></tr>@endforeach</tbody></table></div></details>
            @else<div class="oe-empty"><x-owner-icon name="chart"/><strong>Belum ada penjualan final</strong><p>Tidak ada transaksi final pada periode ini. Pilih periode lain untuk melihat tren.</p></div>@endif
            <p class="oe-footnote">Unit dan rupiah memakai grafik terpisah. Nilai transaksi final bukan laba perusahaan.</p>
        </section>
        <section class="oe-panel oe-finance-panel"><header class="oe-panel-heading"><div><span class="oe-eyebrow">FINANCIAL OVERVIEW</span><h2>Revenue & Payment</h2><p>Penerimaan dan status pembayaran.</p></div></header>
            <div class="oe-finance-hero"><span>Nilai penjualan final</span><strong>{{ $rupiah($kpis['final_sales_value']) }}</strong><small>Berdasarkan tanggal penjualan</small></div>
            <dl class="oe-finance-list"><div><dt><span class="oe-status-dot"></span>Pembayaran terverifikasi<small>Tanggal verifikasi dalam periode</small></dt><dd>{{ $rupiah($kpis['verified_payments']) }}</dd></div><div><dt><span class="oe-status-dot pending"></span>Pembayaran menunggu<small>Dibuat dalam periode · status saat ini</small></dt><dd>{{ $rupiah($payments['pending']) }}</dd></div><div><dt>Pendapatan servis sah<small>Tanggal verifikasi dalam periode</small></dt><dd>{{ $rupiah($service['revenue']) }}</dd></div><div><dt>Midtrans settlement / capture<small>Gross gateway · dibuat dalam periode</small></dt><dd>{{ $rupiah($payments['midtrans_settled']) }}</dd></div></dl>
            <p class="oe-info-note">Pembayaran mencakup seluruh jenis transaksi. Gross gateway bukan saldo rekening atau pencairan dana; angka di atas bukan laba.</p><a class="oe-text-link" href="{{ route('owner.payment-report', $reportPeriod + ['date_basis' => 'verified_at']) }}">Lihat laporan pembayaran <x-owner-icon name="arrow"/></a>
        </section>
    </div>
    <div class="oe-two-grid">
        <section class="oe-panel"><header class="oe-panel-heading"><div><span class="oe-eyebrow">VEHICLE PORTFOLIO</span><h2>Inventory Overview</h2><p>Komposisi stok aktual, terlepas dari periode laporan.</p></div><span class="oe-panel-icon"><x-owner-icon name="car"/></span></header>
            <div class="oe-inventory-total"><strong>{{ number_format($inventoryTotal, 0, ',', '.') }}</strong><span>kendaraan tercatat</span></div>
            <div class="oe-inventory-list">@forelse($ownerInventory as $item)<div class="oe-stock-row"><div><span>{{ $inventoryLabels[$item['status']] ?? ucfirst(str_replace('_', ' ', $item['status'])) }}</span><strong>{{ $item['units'] }} <small>unit</small></strong></div><progress class="oe-stock-progress stock-{{ $item['status'] }}" value="{{ $item['units'] }}" max="{{ max($inventoryTotal, 1) }}" aria-label="{{ $inventoryLabels[$item['status']] ?? $item['status'] }}"></progress></div>@empty<p class="oe-empty-inline">Belum ada kendaraan tercatat.</p>@endforelse</div>
            <a class="oe-text-link" href="{{ route('owner.inventory-report') }}">Lihat inventori kendaraan <x-owner-icon name="arrow"/></a>
        </section>
        <section class="oe-panel"><header class="oe-panel-heading"><div><span class="oe-eyebrow">SERVICE OPERATIONS</span><h2>Operational Performance</h2><p>Ringkasan pekerjaan dan layanan bengkel.</p></div><span class="oe-panel-icon"><x-owner-icon name="tool"/></span></header>
            <div class="oe-operations"><div><strong>{{ $kpis['active_work_orders'] }}</strong><span>Work Order aktif</span><small>Snapshot · pemeriksaan / dikerjakan</small></div><div><strong>{{ $service['waiting_qc'] }}</strong><span>Menunggu QC</span><small>Dibuat dalam periode · QC pending / in_progress</small></div><div><strong>{{ $service['completed_work_orders'] }}</strong><span>Work Order selesai</span><small>Tanggal selesai dalam periode</small></div><div><strong>{{ $service['bookings'] }}</strong><span>Booking servis</span><small>Dibuat dalam periode laporan</small></div></div>
            <a class="oe-text-link" href="{{ route('owner.service-report', $reportPeriod) }}">Lihat laporan servis <x-owner-icon name="arrow"/></a>
        </section>
    </div>
    <div class="oe-two-grid">
        <section class="oe-panel"><header class="oe-panel-heading"><div><span class="oe-eyebrow">BUSINESS PULSE</span><h2>Recent Business Activity</h2><p>Aktivitas sah terbaru dalam periode laporan.</p></div><span class="oe-chip">{{ count($ownerActivities) }} terbaru</span></header>
            <ol class="oe-activity-list">@forelse($ownerActivities as $activity)<li><span class="oe-activity-icon"><x-owner-icon :name="$activity['icon']"/></span><div><strong>{{ $activity['title'] }}</strong><span>{{ $activity['reference'] }} · {{ $activity['status'] }}</span><time datetime="{{ $activity['date']->toIso8601String() }}">{{ $activity['date']->copy()->setTimezone('Asia/Jakarta')->format('d M Y · H:i') }} WIB</time></div><a href="{{ route($activity['route'], $reportPeriod + ['q' => $activity['reference']] + ($activity['route'] === 'owner.payment-report' ? ['date_basis' => 'verified_at'] : [])) }}" aria-label="Lihat laporan {{ $activity['reference'] }}" title="Lihat laporan sumber"><x-owner-icon name="arrow"/></a></li>@empty<li class="oe-empty-inline">Belum ada aktivitas final pada periode ini.</li>@endforelse</ol>
        </section>
        <section class="oe-panel"><header class="oe-panel-heading"><div><span class="oe-eyebrow">PEOPLE & PERFORMANCE</span><h2>Sales Performance Overview</h2><p>Kontribusi penjualan final pada periode terpilih.</p></div></header>
            <div class="oe-table-wrap"><table class="oe-sales-table"><thead><tr><th>Sales</th><th>Unit final</th><th>Nilai final</th></tr></thead><tbody>@forelse(array_slice($performance, 0, 5) as $row)<tr><td><span class="oe-sales-initial">{{ mb_substr($row['name'], 0, 1) }}</span>{{ $row['name'] }}</td><td>{{ $row['units'] }}</td><td>{{ $rupiah($row['amount']) }}</td></tr>@empty<tr><td colspan="3" class="oe-empty-inline">Belum ada sales aktif untuk ditampilkan.</td></tr>@endforelse</tbody></table></div><a class="oe-text-link" href="{{ route('owner.performance-report', $reportPeriod) }}">Lihat performa sales <x-owner-icon name="arrow"/></a>
            <div class="oe-models"><h3>Model terlaris</h3>@forelse(array_slice($models, 0, 3) as $model)<div><span>{{ $model['label'] }}</span><strong>{{ $model['units'] }} unit</strong></div>@empty<p>Belum ada penjualan final dalam periode ini.</p>@endforelse</div>
        </section>
    </div>
    <details class="oe-methodology"><summary>Definisi metrik & cakupan data</summary><p>Penjualan final memakai transaksi penjualan selesai, transaksi tidak dibatalkan, serta pesanan member sudah diserahkan (jika terkait pesanan member). Kendaraan diserahkan adalah bagian dari penjualan final periode ini, bukan semua serah-terima berdasarkan tanggal penyerahan. Pesanan aktif adalah pesanan yang dibuat dalam periode dan belum diserahkan, dibatalkan, atau ditolak. Pembayaran terverifikasi memakai arah masuk, status terverifikasi, dan tanggal verifikasi. Stok, Work Order aktif, serta pelanggan adalah snapshot aktual. WO aktif berstatus pemeriksaan atau dikerjakan; WO selesai berstatus selesai atau ditutup. Grafik tidak menampilkan perbandingan tren tanpa data pembanding.</p><p>Profil Akun membuka pengaturan password existing. Detail transaksi individual tidak ditambahkan; aktivitas menautkan laporan sumber read-only. Tautan pembayaran dari dashboard memakai tanggal verifikasi untuk rekonsiliasi KPI; laporan juga menyediakan pilihan tanggal pembayaran.</p></details>
</div>
@endsection
