@extends('layouts.app')

@section('title', 'Aktivitas & Tugas Admin - YZA Automotive')
@section('admin-page-title', 'Aktivitas & Tugas Admin')

@section('content')
<div class="workspace-page admin-activity-page" data-admin-activity-page>
    <nav class="admin-sales-breadcrumb workspace-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}">⌂</a><span>›</span><span>Dashboard</span><span>›</span><strong>Aktivitas &amp; Tugas Admin</strong>
    </nav>
    <header class="workspace-hero" aria-labelledby="activity-page-title">
        <div class="workspace-hero-copy"><span class="workspace-eyebrow">OPERASIONAL YZA AUTOMOTIVE</span><h1 id="activity-page-title">Aktivitas &amp; <em>Tugas Admin</em></h1><p>Pantau aktivitas operasional, pekerjaan yang memerlukan tindakan, dan riwayat aktivitas administrasi YZA Automotive.</p></div>
        <div class="workspace-stats" aria-label="Ringkasan aktivitas">
            <div class="workspace-stat workspace-stat-waiting"><span class="workspace-stat-icon"><x-order-icon name="clock" /></span><div><strong>{{ number_format($stats['menunggu']) }}</strong><span>Tugas Menunggu</span></div><i class="workspace-stat-chart"><b></b><b></b><b></b></i></div>
            <div class="workspace-stat workspace-stat-progress"><span class="workspace-stat-icon"><x-order-icon name="refresh" /></span><div><strong>{{ number_format($stats['diproses']) }}</strong><span>Sedang Diproses</span></div><i class="workspace-stat-chart"><b></b><b></b><b></b></i></div>
            <div class="workspace-stat workspace-stat-action"><span class="workspace-stat-icon"><x-order-icon name="alert" /></span><div><strong>{{ number_format($stats['tindakan']) }}</strong><span>Perlu Tindakan</span></div><i class="workspace-stat-chart"><b></b><b></b><b></b></i></div>
            <div class="workspace-stat workspace-stat-done"><span class="workspace-stat-icon"><x-order-icon name="check-circle" /></span><div><strong>{{ number_format($stats['selesai']) }}</strong><span>Selesai</span></div><i class="workspace-stat-chart"><b></b><b></b><b></b></i></div>
        </div>
    </header>
    <div class="workspace-layout">
        <section class="workspace-panel workspace-task-panel" aria-labelledby="task-panel-title">
            <div class="workspace-panel-head"><div class="workspace-panel-heading"><span class="workspace-panel-icon">▣</span><div><h2 id="task-panel-title">Daftar tugas operasional</h2><p>Ringkasan pesanan dan pembayaran sesuai akses Anda. KPI mengikuti rentang tanggal; prioritas diturunkan dari tahap workflow.</p></div></div><span class="workspace-count"><b>{{ $tasks->count() }}</b> Item</span></div>
            <form class="workspace-filter" method="get" action="{{ route('admin.aktivitas.index') }}">
                <label class="workspace-search"><span aria-hidden="true">⌕</span><input name="q" value="{{ $q }}" placeholder="Cari pesanan, pelanggan, atau mobil..." aria-label="Cari tugas"></label>
                <select name="type" aria-label="Filter jenis"><option value="">Semua jenis</option>@foreach(['Pesanan','Pembayaran','PDI','Pengambilan'] as $option)<option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>@endforeach</select>
                <select name="status" aria-label="Filter status"><option value="">Semua status</option><option value="menunggu" @selected($status === 'menunggu')>Menunggu</option><option value="diproses" @selected($status === 'diproses')>Diproses</option><option value="tindakan" @selected($status === 'tindakan')>Perlu tindakan</option><option value="selesai" @selected($status === 'selesai')>Selesai</option></select>
                <button type="submit" class="workspace-apply"><span>≡</span> Terapkan</button><a class="workspace-reset" href="{{ route('admin.aktivitas.index') }}">Reset</a>
            </form>
            <div class="task-list">
                @forelse($tasks as $task)
                    <article class="task-item" data-task-resource="{{ $task['resource_type'] }}" data-task-id="{{ $task['resource_id'] }}" data-task-status="{{ $task['status'] }}"><span class="task-marker is-{{ $task['status'] }}" aria-hidden="true"><x-order-icon :name="match($task['status']) { 'menunggu' => 'clock', 'diproses' => 'refresh', 'tindakan' => 'alert', 'selesai' => 'check-circle', default => 'circle-x' }" /></span><div class="task-copy"><div class="task-meta"><span>{{ $task['type'] }}</span><b class="task-priority {{ $task['priority'] === 'Tinggi' ? 'is-high' : '' }}">{{ $task['priority'] }}</b></div><strong>{{ $task['title'] }}</strong><p>{{ $task['detail'] }}</p><small>{{ $task['date']?->format('d M Y · H:i') }}</small></div><div class="task-trailing"><span class="task-status is-{{ $task['status'] }}">{{ match($task['status']) { 'menunggu' => 'Menunggu', 'diproses' => 'Sedang diproses', 'tindakan' => 'Perlu tindakan', 'selesai' => 'Selesai', default => 'Dibatalkan' } }}</span><a class="task-action" href="{{ $task['url'] }}">{{ auth()->user()->roleKey() === 'owner' ? 'Lihat workflow' : 'Buka workflow' }} <span>→</span></a></div></article>
                @empty
                    <div class="workspace-empty"><div class="workspace-empty-art"><span>▤</span><b>⌕</b></div><strong>Tidak ada tugas yang cocok dengan filter saat ini.</strong><p>Coba ubah kata kunci pencarian atau filter yang dipilih.</p></div>
                @endforelse
            </div>
        </section>
        <aside class="workspace-panel workspace-history-panel" aria-labelledby="history-panel-title">
            <div class="workspace-panel-head"><div class="workspace-panel-heading"><span class="workspace-panel-icon">◷</span><div><h2 id="history-panel-title">Riwayat aktivitas</h2><p>Notifikasi akun yang tercatat, bukan audit log atau bukti tugas selesai.</p></div></div></div>
            <div class="history-list">
                @forelse($history as $item)
                    <article class="history-item"><span class="history-icon {{ $item->is_read ? 'is-read' : 'is-unread' }}" aria-label="{{ $item->is_read ? 'Notifikasi dibaca' : 'Notifikasi belum dibaca' }}"><x-order-icon name="bell" /></span><div><strong>{{ $item->judul }}</strong><p>{{ Str::limit($item->pesan, 90) }}</p><time>{{ $item->created_at?->format('d M Y · H:i') }}</time></div></article>
                @empty
                    <div class="workspace-empty workspace-history-empty">Belum ada riwayat tercatat.</div>
                @endforelse
            </div><div class="workspace-history-footer"><a class="workspace-link" href="{{ route('admin.notifikasi.index') }}">Lihat pusat notifikasi <span>→</span></a></div>
        </aside>
    </div>
</div>
@endsection
