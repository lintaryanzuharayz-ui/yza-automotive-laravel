@extends('layouts.app')
@section('title', 'Dashboard Sales · YZA Automotive')
@section('admin-page-title', 'Dashboard Sales')
@section('content')
<div class="admin-page-intro"><p>Ringkasan prospek, test drive, pesanan, dan pencapaian yang berasal dari data penjualan YZA.</p></div>
@if($awardRanking)<section class="admin-panel"><div class="admin-panel-heading"><div><small>SALES EXCELLENCE AWARDS {{ $awardYear }}</small><h2>Peringkat Anda #{{ $awardRanking['rank'] }}</h2></div><a class="admin-button admin-button-secondary" href="{{ route('sales.awards') }}">Lihat leaderboard</a></div><div class="admin-detail-list"><div><span>Unit final terserah-terima</span><strong>{{ $awardRanking['units'] }}</strong></div><div><span>Pencapaian target</span><strong>{{ $awardRanking['achievement'] === null ? 'Belum tersedia' : $awardRanking['achievement'].'%' }}</strong></div><div><span>Status data</span><strong>{{ ucfirst($awardRanking['eligibility']) }}</strong></div></div></section>@endif
<div class="admin-stat-grid">
    @foreach([['Prospek aktif','active_leads'],['Prospek baru bulan ini','new_leads_month'],['Test drive terjadwal','test_drives_scheduled'],['Test drive selesai','test_drives_completed'],['Pesanan ditangani','orders_handled'],['Serah terima selesai','successful_sales']] as [$label,$key])
        <article class="admin-stat-card"><small>{{ $label }}</small><strong>{{ number_format($stats[$key] ?? 0, 0, ',', '.') }}</strong><span>Data aktual sistem</span></article>
    @endforeach
</div>
<div class="admin-grid-2">
    <section class="admin-panel"><div class="admin-panel-heading"><div><small>TARGET PERIODE AKTIF</small><h2>Pencapaian Sales</h2></div></div><div class="admin-detail-list"><div><span>Target unit</span><strong>{{ number_format($stats['target']?->target_unit ?? 0, 0, ',', '.') }}</strong></div><div><span>Realisasi unit sah</span><strong>{{ number_format($stats['realized_units'] ?? 0, 0, ',', '.') }}</strong></div><div><span>Pencapaian</span><strong>{{ number_format($stats['target_percentage'] ?? 0, 1, ',', '.') }}%</strong></div></div></section>
    <section class="admin-panel"><div class="admin-panel-heading"><div><small>AKTIVITAS TERBARU</small><h2>Prospek</h2></div><a class="admin-button admin-button-secondary" href="{{ route('sales.leads.index') }}">Lihat semua</a></div>@forelse($stats['recent_leads'] as $lead)<div class="admin-list-row"><div><strong>{{ $lead->nama_prospek }}</strong><span>{{ $lead->mobil?->merk }} {{ $lead->mobil?->model }}</span></div><b>{{ ucwords(str_replace('_',' ', $lead->status)) }}</b></div>@empty<p class="admin-empty">Belum ada prospek.</p>@endforelse</section>
</div>
@endsection
