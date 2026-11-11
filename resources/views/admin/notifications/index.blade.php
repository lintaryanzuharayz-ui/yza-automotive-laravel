@extends('layouts.app')
@section('title', 'Pusat Notifikasi · Admin YZA Automotive')
@section('admin-page-title', 'Pusat Notifikasi')

@section('content')
<div class="admin-notification-page" data-admin-notification-page>
    <nav class="admin-sales-breadcrumb admin-notification-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" aria-label="Dashboard"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 0 2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></a><span class="breadcrumb-separator">/</span><span class="breadcrumb-current">Pusat Notifikasi</span>
    </nav>

    <header class="admin-notification-hero">
        <div class="admin-notification-hero-copy"><span class="admin-notification-eyebrow">PUSAT NOTIFIKASI</span><h1>Pusat Notifikasi</h1><p>Pantau booking, pembayaran, inventori, dan aktivitas operasional yang membutuhkan perhatian.</p></div>
        <div class="admin-notification-stats" aria-label="Ringkasan notifikasi"><div class="admin-notification-stat"><span class="admin-notification-stat-icon">♧</span><strong data-notification-total>{{ $totalCount }}</strong><small>TOTAL KABAR</small></div><div class="admin-notification-stat"><span class="admin-notification-stat-icon">✉</span><strong data-notification-unread-total>{{ $unreadCount }}</strong><small>BELUM DIBACA</small></div></div>
    </header>

    <div class="admin-notification-section-heading"><div><h2>Aktivitas terbaru</h2><p>Informasi terbaru dari seluruh modul operasional YZA.</p></div><nav class="admin-notification-filter" aria-label="Filter notifikasi"><a class="{{ $filter !== 'unread' ? 'is-active' : '' }}" href="{{ route('admin.notifikasi.index') }}">☷ &nbsp; Semua</a><a class="{{ $filter === 'unread' ? 'is-active' : '' }}" href="{{ route('admin.notifikasi.index', ['filter' => 'unread']) }}">▰ &nbsp; Belum dibaca <b data-notification-unread-badge @if($unreadCount < 1) hidden @endif>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</b></a></nav></div>

    <div class="admin-notification-list" data-admin-notification-list>
        @forelse($notifications as $notification)
            @php
                $title = strtolower($notification->judul.' '.$notification->pesan);
                $isBooking = $notification->referensi_tipe === 'booking_servis' || $notification->tipe === 'booking';
                $isStock = $notification->tipe === 'stok' || $notification->referensi_tipe === 'sparepart';
                $isCritical = str_contains($title, 'habis') || str_contains($title, 'kritis');
                $tone = $isCritical ? 'critical' : ($isBooking ? 'booking' : ($isStock ? 'warning' : 'default'));
                $icon = $isBooking ? '▣' : ($isCritical ? '△' : ($isStock ? '▱' : '•'));
                $category = $isBooking ? 'BOOKING SERVIS' : ($isStock ? 'AKTIVITAS ADMIN' : strtoupper($notification->tipe ?: 'INFORMASI'));
            @endphp
            <article class="admin-notification-card is-{{ $tone }} {{ $notification->is_read ? '' : 'is-unread' }}" data-notification-id="{{ $notification->id_notifikasi }}">
                <div class="admin-notification-card-icon" aria-hidden="true">{{ $icon }}@if(!$notification->is_read)<i></i>@endif</div>
                <div class="admin-notification-card-content"><span class="admin-notification-category">{{ $category }}</span><strong>{{ $notification->judul }}</strong><p>{{ $notification->pesan }}</p><time>{{ $notification->created_at?->format('d M Y · H:i') }}</time><a class="admin-notification-card-link" href="{{ route('admin.notifikasi.open', $notification) }}">{{ $isBooking ? 'Lihat Booking' : 'Buka Detail' }} <span aria-hidden="true">→</span></a></div>
                <div class="admin-notification-card-actions">@if(!$notification->is_read)<form data-notification-read-form method="post" action="{{ route('admin.notifikasi.read', $notification) }}">@csrf @method('PATCH')<button type="submit"><span aria-hidden="true">✉</span> Tandai dibaca</button></form>@endif<button type="button" class="admin-notification-more" aria-label="Opsi notifikasi">⋮</button></div>
                @if(!$notification->is_read)<span class="admin-notification-new">Baru</span>@endif
            </article>
        @empty
            <div class="admin-notification-empty"><div>✓</div><h3>Belum Ada Notifikasi</h3><p>Belum ada aktivitas operasional yang perlu ditampilkan.</p></div>
        @endforelse
    </div>
    <div class="admin-notification-pagination">{{ $notifications->links('pagination::bootstrap-4') }}</div>
</div>
@endsection
