@extends('layouts.app')

@push('styles')
<style>
    .admin-notification-page { max-width: 1500px; margin: 0 auto; }
    .admin-notification-hero { display: flex; justify-content: space-between; gap: 28px; align-items: center; margin-bottom: 28px; padding: 34px 38px; border-radius: 20px; color: #fff; background: linear-gradient(105deg, #101318 0%, #171922 48%, #4f111b 100%); box-shadow: 0 16px 34px rgba(15, 23, 42, .12); }
    .admin-notification-hero .eyebrow { color: #ff5365; }
    .admin-notification-hero h1 { margin: 6px 0 8px; color: #fff; font-size: clamp(32px, 4vw, 54px); letter-spacing: -.05em; }
    .admin-notification-hero p { max-width: 620px; margin: 0; color: #cbd5e1; font-size: 14px; }
    .admin-notification-stats { display: flex; flex: 0 0 auto; border: 1px solid rgba(255,255,255,.22); border-radius: 14px; overflow: hidden; }
    .admin-notification-stat { min-width: 108px; padding: 16px 18px; background: rgba(255,255,255,.06); }
    .admin-notification-stat + .admin-notification-stat { border-left: 1px solid rgba(255,255,255,.22); }
    .admin-notification-stat strong { display: block; color: #fff; font-size: 25px; }
    .admin-notification-stat span { display: block; margin-top: 4px; color: #aeb8c5; font-size: 9px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
    .admin-notification-section-heading { display: flex; justify-content: space-between; align-items: end; gap: 20px; margin-bottom: 14px; }
    .admin-notification-section-heading h2 { margin: 0; color: #172331; font-size: 22px; }
    .admin-notification-section-heading p { margin: 4px 0 0; color: #8492a0; font-size: 13px; }
    .admin-notification-filter { display: flex; gap: 8px; }
    .admin-notification-filter a { padding: 8px 13px; border: 1px solid #dce4eb; border-radius: 999px; color: #64748b; background: #fff; font-size: 12px; font-weight: 800; text-decoration: none; }
    .admin-notification-filter a.is-active, .admin-notification-filter a:hover { border-color: #ed172b; color: #ed172b; background: #fff5f6; }
    .admin-notification-list { display: grid; }
    .admin-notification-page .admin-notification-card-actions { display: grid; justify-items: end; gap: 9px; }
    .admin-notification-page .admin-notification-card-actions form { margin: 0; }
    .admin-notification-page .admin-notification-card-actions button { padding: 8px 11px; border: 1px solid #dce4eb; border-radius: 8px; color: #64748b; background: #fff; font-size: 11px; font-weight: 800; cursor: pointer; }
    .admin-notification-page .admin-notification-card-actions button:hover { border-color: #ed172b; color: #ed172b; }
    .admin-notification-page .admin-notification-card-actions a { color: #dc152c; font-size: 12px; font-weight: 900; text-decoration: none; }
    .admin-notification-page .admin-notification-card-actions a:hover { text-decoration: underline; }
    .admin-notification-empty { padding: 40px 20px; border: 1px dashed #d9e2e9; border-radius: 14px; color: #8492a0; background: #fff; text-align: center; }
    .admin-notification-pagination { margin-top: 24px; }
    @media (max-width: 700px) { .admin-notification-hero { align-items: flex-start; flex-direction: column; padding: 26px 22px; } .admin-notification-stats { width: 100%; } .admin-notification-stat { flex: 1; } .admin-notification-section-heading { align-items: flex-start; flex-direction: column; } .admin-notification-card-actions { grid-column: 2; justify-items: start !important; } }
</style>
@endpush

@section('content')
<div class="admin-notification-page" data-admin-notification-page>
    <header class="admin-notification-hero">
        <div>
            <span class="eyebrow">PUSAT NOTIFIKASI</span>
            <h1>Pusat Notifikasi</h1>
            <p>Pantau booking, pembayaran, inventori, dan aktivitas operasional yang membutuhkan perhatian.</p>
        </div>
        <div class="admin-notification-stats" aria-label="Ringkasan notifikasi">
            <div class="admin-notification-stat"><strong>{{ $notifications->total() }}</strong><span>Total Kabar</span></div>
            <div class="admin-notification-stat"><strong>{{ $unreadCount }}</strong><span>Belum Dibaca</span></div>
        </div>
    </header>

    <div class="admin-notification-section-heading">
        <div><h2>Aktivitas terbaru</h2><p>Informasi terbaru dari seluruh modul operasional YZA.</p></div>
        <nav class="admin-notification-filter" aria-label="Filter notifikasi"><a class="{{ $filter !== 'unread' ? 'is-active' : '' }}" href="{{ route('admin.notifikasi.index') }}">Semua</a><a class="{{ $filter === 'unread' ? 'is-active' : '' }}" href="{{ route('admin.notifikasi.index', ['filter' => 'unread']) }}">Belum dibaca</a></nav>
    </div>

    <div class="admin-notification-list" data-admin-notification-list>
        @forelse($notifications as $notification)
            <article class="admin-notification-card {{ $notification->is_read ? '' : 'is-unread' }}" data-notification-id="{{ $notification->id_notifikasi }}">
                <span class="admin-notification-card-icon" aria-hidden="true">{{ $notification->tipe === 'booking' ? '⚙' : '!' }}</span>
                <div class="admin-notification-card-content">
                    <small>{{ $notification->tipe === 'booking' ? 'BOOKING SERVIS' : 'AKTIVITAS ADMIN' }}</small>
                    <strong>{{ $notification->judul }}</strong>
                    <p>{{ $notification->pesan }}</p>
                    <time>{{ $notification->created_at?->format('d M Y · H:i') }}</time>
                    <a class="admin-notification-card-link" href="{{ route('admin.notifikasi.open', $notification) }}">{{ $notification->tipe === 'booking' ? 'Lihat Booking →' : 'Buka Detail →' }}</a>
                </div>
                <div class="admin-notification-card-actions">
                    @if(!$notification->is_read)
                        <form method="post" action="{{ route('admin.notifikasi.read', $notification) }}">@csrf @method('PATCH')<button type="submit">Tandai dibaca</button></form>
                    @endif
                </div>
            </article>
        @empty
            <div class="admin-notification-empty">Belum ada notifikasi operasional.</div>
        @endforelse
    </div>

    <div class="admin-notification-pagination">{{ $notifications->links() }}</div>
</div>
@endsection
