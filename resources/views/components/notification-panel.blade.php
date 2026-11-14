@auth
@php
    $panelMember = auth()->user()->roleKey() === 'pelanggan';
    $panelAdmin = !$panelMember && auth()->user()->roleKey() !== 'owner' && auth()->user()->hasPermission('dashboard.view');
    $panelPrefix = $panelAdmin ? 'admin.notifikasi' : 'notifications';
    $panelQuery = app(\App\Services\NotificationStateService::class)->query(auth()->user());
    $panelState = $notificationState ?? app(\App\Services\NotificationStateService::class)->summary(auth()->user());
    $panelUnread = $notificationState['unread_count'] ?? (clone $panelQuery)->where('is_read', false)->count();
    $panelItems = $panelQuery->latest('id_notifikasi')->limit(4)->get()->map(fn ($item) => [
        'id' => $item->id_notifikasi, 'title' => $item->judul, 'message' => $item->pesan,
        'type' => $item->tipe, 'is_read' => $item->is_read, 'created_at' => $item->created_at?->toIso8601String(),
        'open_url' => route($panelPrefix.'.open', $item),
    ]);
    $panelInitial = ['notifications' => $panelItems, 'unread_count' => $panelUnread,
        'total_count' => $panelState['total_count']];
@endphp
<link rel="stylesheet" href="{{ asset('assets/css/notification-panel.css') }}?v={{ filemtime(public_path('assets/css/notification-panel.css')) }}">
<section id="yza-notification-panel" class="yza-notification-panel {{ $panelMember ? 'yza-notification-panel--member' : 'yza-notification-panel--backoffice' }}"
         data-yza-notification-panel data-sync-url="{{ route($panelPrefix.'.sync') }}" hidden aria-label="Notifikasi" tabindex="-1">
    <header class="ynp-header"><div><small>YZA AUTOMOTIVE</small><h2>Notifikasi</h2></div><span class="ynp-count" data-panel-count>{{ $panelUnread }} belum dibaca</span></header>
    <div class="ynp-filters" role="group" aria-label="Filter notifikasi">
        <button type="button" class="is-active" data-panel-filter="all" aria-pressed="true"><span aria-hidden="true">☷</span> Semua <span data-panel-total></span></button>
        <button type="button" data-panel-filter="unread" aria-pressed="false"><span aria-hidden="true">✦</span> Belum dibaca</button>
        <button type="button" disabled title="Kategori Penting belum tersedia"><span aria-hidden="true">★</span> Penting</button>
    </div>
    <div class="ynp-list" data-panel-list aria-live="polite"></div>
    <footer class="ynp-footer"><a href="{{ route($panelPrefix.'.index') }}">Lihat semua notifikasi <span aria-hidden="true">→</span></a></footer>
    <script type="application/json" data-panel-initial>@json($panelInitial)</script>
</section>
<script src="{{ asset('assets/js/notification-panel.js') }}?v={{ filemtime(public_path('assets/js/notification-panel.js')) }}"></script>
@endauth
