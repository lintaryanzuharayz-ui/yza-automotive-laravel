@extends('layouts.app')
@section('title', 'Notifikasi · YZA Automotive')
@section('content')
@php
    $typeMeta = [
        'servis' => ['label' => 'Servis', 'icon' => '✦'],
        'booking' => ['label' => 'Booking', 'icon' => '◷'],
        'pembayaran' => ['label' => 'Pembayaran', 'icon' => '✓'],
        'stok' => ['label' => 'Koleksi', 'icon' => '◆'],
    ];
@endphp
<div class="notification-page">
    <style>
        .notification-page{width:100%;max-width:none;margin:0;padding:10px 0 32px;color:#17191d}
        .notification-hero{position:relative;overflow:hidden;display:flex;align-items:flex-end;justify-content:space-between;gap:24px;min-height:230px;padding:34px 38px;margin-bottom:22px;border-radius:24px;background:radial-gradient(circle at 82% 15%,#4a1118 0,#18191d 38%,#0c0d10 100%);color:#fff;box-shadow:0 20px 45px #15161a1c}
        .notification-hero:after{content:'';position:absolute;right:-60px;bottom:-110px;width:350px;height:350px;border:1px solid #ffffff1f;border-radius:50%;box-shadow:0 0 0 28px #ffffff08,0 0 0 56px #ffffff05}
        .notification-hero-copy,.notification-summary{position:relative;z-index:1}
        .notification-hero .eyebrow{color:#ff5862;font-size:10px;letter-spacing:.2em;text-transform:uppercase;font-weight:800}
        .notification-hero h1{max-width:600px;margin:11px 0 8px;font-size:clamp(30px,4vw,48px);line-height:.98;letter-spacing:-.05em}
        .notification-hero p{max-width:540px;margin:0;color:#bfc1c7;font-size:13px;line-height:1.6}
        .notification-summary{display:grid;grid-template-columns:repeat(2,1fr);min-width:230px;border:1px solid #ffffff24;border-radius:16px;background:#ffffff0b;backdrop-filter:blur(10px)}
        .notification-summary div{padding:16px 18px}.notification-summary div+div{border-left:1px solid #ffffff1c}.notification-summary strong,.notification-summary span{display:block}.notification-summary strong{font-size:27px;letter-spacing:-.04em}.notification-summary span{margin-top:4px;color:#aeb0b6;font-size:10px;letter-spacing:.08em;text-transform:uppercase}
        .notification-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:14px}.notification-toolbar h2{margin:0;font-size:18px;letter-spacing:-.03em}.notification-toolbar p{margin:4px 0 0;color:#7b7f88;font-size:12px}.notification-toolbar-actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;flex-wrap:wrap}.notification-filters{display:flex;gap:7px;flex-wrap:wrap}.notification-filter{padding:8px 12px;border:1px solid #dedfe3;border-radius:999px;background:#fff;color:#727680;font-size:11px;font-weight:800}.notification-filter.is-active{border-color:#e31d2b;background:#fff0f1;color:#d01c2a}.notification-mark-all{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 13px;border:1px solid #e31d2b;border-radius:999px;background:#e31d2b;color:#fff;font:inherit;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 7px 18px #e31d2b24;transition:transform .2s ease,background .2s ease,box-shadow .2s ease}.notification-mark-all:hover{background:#c91826;box-shadow:0 10px 22px #e31d2b30;transform:translateY(-1px)}.notification-mark-all-done{border-color:#dfe1e5;background:#f6f7f8;color:#7f838b;box-shadow:none;cursor:default}.notification-feedback{margin:-2px 0 14px;padding:10px 13px;border:1px solid #bde6c8;border-radius:11px;background:#effaf2;color:#23743a;font-size:12px;font-weight:700}
        .notification-list{display:grid;gap:11px}.notification-item{position:relative;display:flex;align-items:flex-start;gap:15px;padding:18px 20px;border:1px solid #e4e5e8;border-radius:17px;background:#fff;box-shadow:0 5px 18px #15161a08;transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}.notification-item:hover{transform:translateY(-2px);border-color:#d4d6da;box-shadow:0 14px 28px #15161a12}.notification-item.is-unread{border-color:#f0c3c7;box-shadow:0 8px 24px #e31d2b0d}.notification-item.is-read{opacity:.72}.notification-item-icon{display:grid;place-items:center;width:40px;height:40px;flex:none;border-radius:13px;background:#f1f2f4;color:#787d87;font-size:16px;font-weight:800}.notification-item.is-unread .notification-item-icon{background:#ffe7e9;color:#e31d2b}.notification-item-copy{min-width:0;flex:1}.notification-item-kicker{display:flex;align-items:center;gap:8px;margin-bottom:5px;color:#979aa2;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.notification-unread-dot{width:6px;height:6px;border-radius:50%;background:#e31d2b}.notification-item h2{margin:0 0 5px;font-size:15px;line-height:1.3;letter-spacing:-.01em}.notification-item p{max-width:780px;margin:0;color:#676c76;font-size:12px;line-height:1.55}.notification-item time{display:block;margin-top:11px;color:#a1a4ac;font-size:10px}.notification-item-action{flex:none}.notification-item-action button{padding:9px 12px;border:1px solid #e0e1e4;border-radius:9px;background:#fff;color:#4c5058;font:inherit;font-size:11px;font-weight:800;cursor:pointer}.notification-item-action button:hover{border-color:#e31d2b;color:#e31d2b}.notification-item-content{display:contents;color:inherit;text-decoration:none}.notification-empty{text-align:center;padding:55px 25px;border:1px dashed #d6d8dc;border-radius:17px;background:#fff;color:#777b84}.notification-empty strong{display:block;margin-bottom:5px;color:#282b30;font-size:16px}.notification-page .pagination{margin-top:22px}
        @media(max-width:700px){.notification-page{padding-top:0}.notification-hero{display:block;min-height:0;padding:26px 24px}.notification-summary{margin-top:24px;min-width:0}.notification-toolbar{display:block}.notification-toolbar-actions{justify-content:flex-start;margin-top:12px}.notification-mark-all{width:100%}.notification-filters{width:100%;margin-top:3px}.notification-item{padding:16px}.notification-item-action{position:absolute;right:16px;top:16px}.notification-item-copy{padding-right:74px}.notification-item p{font-size:11px}.notification-hero h1{font-size:34px}}
        .member-main-content>.notification-page{box-sizing:border-box;width:100%!important;max-width:none!important;margin-left:0!important;margin-right:0!important}.notification-page .notification-item h2{margin:0 0 5px!important;color:#17191d!important;font-size:15px!important;line-height:1.3!important;letter-spacing:-.01em!important}
        .notification-page .notification-hero h1{max-width:none!important;margin:11px 0 8px!important;font-size:clamp(42px,4.2vw,68px)!important;line-height:.98!important;white-space:nowrap!important}
        .notification-page .notification-hero-copy{transform:translateY(-16px)}.notification-page .notification-hero h1{margin-top:17px!important;margin-bottom:13px!important}.notification-page .notification-hero p{margin-top:0!important;line-height:1.7!important}
        @media(max-width:700px){.notification-page .notification-hero-copy{transform:none}.notification-page .notification-hero h1{font-size:34px!important;white-space:normal!important}}
        .notification-item-icon.is-alert{background:#ffe7e9;color:#e31d2b}.notification-item-icon.is-success{background:#e3f7ed;color:#12955a}.notification-item-icon.is-tools{background:#e8edf5;color:#53657d}.notification-item-icon.is-progress{background:#fff0d8;color:#c27a12}.notification-item-icon.is-neutral{background:#f1f2f4;color:#787d87}
    </style>
    <section class="notification-hero">
        <div class="notification-hero-copy"><span class="eyebrow">YZA Automotive · Member Lounge</span><h1>Notifikasi Member</h1><p>Informasi terbaru seputar layanan, kendaraan, dan keanggotaan Anda.</p></div>
        <div class="notification-summary"><div><strong>{{ $notifications->total() }}</strong><span>Total kabar</span></div><div><strong>{{ $unreadCount }}</strong><span>Belum dibaca</span></div></div>
    </section>
    <div class="notification-toolbar"><div><h2>Aktivitas terbaru</h2><p>Informasi paling baru dari perjalanan layanan YZA Anda.</p></div><div class="notification-toolbar-actions">
        @if($unreadCount > 0)
            <form class="notification-mark-all-form" method="post" action="{{ route('notifications.read-all') }}">@csrf @method('PATCH')<button class="notification-mark-all" type="submit"><span aria-hidden="true">✓</span> Tandai semua telah dibaca</button></form>
        @else
            <span class="notification-mark-all notification-mark-all-done"><span aria-hidden="true">✓</span> Semua telah dibaca</span>
        @endif
        <div class="notification-filters"><a class="notification-filter {{ $filter !== 'unread' ? 'is-active' : '' }}" href="{{ route('notifications.index') }}">Semua</a><a class="notification-filter {{ $filter === 'unread' ? 'is-active' : '' }}" href="{{ route('notifications.index', ['filter' => 'unread']) }}">Belum dibaca</a></div>
    </div></div>
    @if(session('status'))<div class="notification-feedback" role="status">{{ session('status') }}</div>@endif
    <div class="notification-list">
        @forelse($notifications as $notification)
            @php($meta = $typeMeta[$notification->tipe] ?? ['label' => 'Update', 'icon' => '•'])
            @php($notificationTitle = strtolower($notification->judul))
            @php($iconMeta = match (true) { str_contains($notificationTitle, 'pembayaran') || str_contains($notificationTitle, 'ditolak') => ['class' => 'is-alert', 'icon' => '!'], str_contains($notificationTitle, 'diterima') || str_contains($notificationTitle, 'terverifikasi') => ['class' => 'is-success', 'icon' => '✓'], str_contains($notificationTitle, 'diperlukan') => ['class' => 'is-tools', 'icon' => '⚒'], str_contains($notificationTitle, 'dikerjakan') || str_contains($notificationTitle, 'pemeriksaan') => ['class' => 'is-progress', 'icon' => '⚙'], default => ['class' => 'is-neutral', 'icon' => $meta['icon']] })
            <article class="notification-item {{ $notification->is_read ? 'is-read' : 'is-unread' }}">
                <a class="notification-item-content" href="{{ route('notifications.open', $notification) }}">
                    <span class="notification-item-icon {{ $iconMeta['class'] }}" aria-hidden="true">{{ $iconMeta['icon'] }}</span>
                    <span class="notification-item-copy"><span class="notification-item-kicker">@if(!$notification->is_read)<i class="notification-unread-dot"></i>@endif {{ $meta['label'] }}</span><h2>{{ $notification->judul }}</h2><p>{{ $notification->pesan }}</p><time>{{ $notification->created_at?->format('d M Y · H:i') }}</time></span>
                </a>
                @if(!$notification->is_read)<form class="notification-item-action" method="post" action="{{ route('notifications.read', $notification) }}">@csrf @method('PATCH')<button type="submit">Tandai dibaca</button></form>@endif
            </article>
        @empty
            <div class="notification-empty"><strong>Belum ada kabar untuk Anda</strong>Notifikasi servis dan aktivitas akun akan tampil di sini.</div>
        @endforelse
    </div>
    {{ $notifications->links() }}
</div>
@endsection
