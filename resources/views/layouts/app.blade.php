<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vehicle-badges.css') }}?v=stock-badges-20261010">
    @php
        $isMemberShell = auth()->check() && auth()->user()->roleName() === 'pelanggan' && (request()->routeIs('pelanggan.*') || request()->routeIs('notifications.*')) && !View::hasSection('member-portal');
        $isAdminShell = auth()->check() && auth()->user()->roleKey() !== 'pelanggan' && (request()->is('admin/*') || request()->is('sales/*') || request()->is('owner/*') || request()->routeIs('staff.dashboard'));
        $isCheckoutPage = $isMemberShell && request()->routeIs('pelanggan.pesanan-mobil.create');
    @endphp
    @if($isMemberShell)
        <link rel="stylesheet" href="{{ asset('assets/css/member-portal.css') }}?v={{ filemtime(public_path('assets/css/member-portal.css')) }}">
        <link rel="stylesheet" href="{{ asset('assets/css/member-navbar-final.css') }}?v={{ filemtime(public_path('assets/css/member-navbar-final.css')) }}">
    @endif
    @if($isAdminShell)
        <link rel="stylesheet" href="{{ asset('assets/css/admin-portal.css') }}?v={{ filemtime(public_path('assets/css/admin-portal.css')) }}">
    @endif
    @stack('styles')
</head>
<body class="@yield('body-class') @if($isMemberShell) member-portal-page member-shell-page @endif @if($isAdminShell) admin-portal-page @endif">
    @if (View::hasSection('member-portal'))
        @yield('content')
    @elseif($isMemberShell)
        @php
            $memberUser = auth()->user();
            $memberName = $memberUser->pelanggan?->nama_lengkap ?: $memberUser->username;
            $memberPageTitle = match (true) {
                request()->routeIs('pelanggan.kendaraan.*') => 'Kendaraan Saya',
                request()->routeIs('pelanggan.booking.*') => 'Booking Saya',
                request()->routeIs('pelanggan.booking-servis.*') => 'Booking Servis',
                request()->routeIs('pelanggan.test-drive.*') => 'Test Drive',
                request()->routeIs('pelanggan.booking-cuci.*') => 'Car Wash',
                request()->routeIs('pelanggan.trade-in.*', 'pelanggan.pengajuan-mobil.*') => 'Trade-In',
                request()->routeIs('pelanggan.promo.*') => 'Promo Member',
                request()->routeIs('pelanggan.poin') => 'YZA Points',
                request()->routeIs('pelanggan.profile') => 'Profil Member',
                request()->routeIs('pelanggan.membership.*') => 'Membership',
                request()->routeIs('pelanggan.servis.*') => 'Riwayat Servis',
                default => 'Member Center',
            };
            $memberNotifications = \App\Models\Notifikasi::where('id_user', $memberUser->id_user)->latest('created_at')->limit(5)->get();
            $memberNotificationUnreadCount = $memberNotifications->where('is_read', false)->count();
        @endphp
        <div class="member-app-shell member-horizontal-shell" data-member-shell>
            <div class="member-workspace">
                <header class="member-topbar">
                    <div class="member-topbar-left"><button class="member-mobile-toggle" type="button" data-member-mobile-toggle aria-label="Buka navigasi" aria-expanded="false">☰</button><a class="member-topbar-brand" href="{{ route('pelanggan.member') }}" aria-label="Kembali ke Beranda YZA Automotive"><img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive"><span><strong>YZA AUTOMOTIVE</strong><small>Premium Member Portal</small></span></a></div>
                    @include('components.member-horizontal-nav')
                    <div class="member-topbar-right"><div class="member-notification-wrap"><button class="member-notification" type="button" data-member-notification-toggle data-member-notification-sync-url="{{ route('notifications.sync') }}" data-member-notification-latest="{{ (int) ($memberNotifications->max('id_notifikasi') ?: 0) }}" aria-expanded="false" aria-label="Buka notifikasi" title="Notifikasi"><span aria-hidden="true">🔔</span><i data-member-notification-count aria-hidden="true" @if($memberNotificationUnreadCount < 1) hidden @endif>{{ $memberNotificationUnreadCount > 9 ? '9+' : $memberNotificationUnreadCount }}</i></button><div class="member-notification-menu" data-member-notification-menu hidden><div class="member-notification-menu-head"><div><small>YZA AUTOMOTIVE</small><strong>Notifikasi</strong></div><span data-member-notification-unread-label>{{ $memberNotificationUnreadCount }} belum dibaca</span></div><div class="member-notification-filters" aria-label="Filter notifikasi"><a class="is-active" href="{{ route('notifications.index') }}">☷ &nbsp; Semua</a><a href="{{ route('notifications.index', ['filter' => 'unread']) }}">● &nbsp; Belum dibaca</a><button type="button" aria-pressed="false">★ &nbsp; Penting</button></div><div class="member-notification-list" data-member-notification-list>@forelse($memberNotifications as $notification)@php($notificationTitle = strtolower($notification->judul))@php($notificationIcon = match (true) { str_contains($notificationTitle, 'pembayaran') || str_contains($notificationTitle, 'ditolak') => ['class' => 'is-alert', 'symbol' => '!'], str_contains($notificationTitle, 'diterima') || str_contains($notificationTitle, 'terverifikasi') => ['class' => 'is-success', 'symbol' => '✓'], str_contains($notificationTitle, 'diperlukan') => ['class' => 'is-tools', 'symbol' => '⚒'], str_contains($notificationTitle, 'dikerjakan') || str_contains($notificationTitle, 'pemeriksaan') => ['class' => 'is-progress', 'symbol' => '⚙'], default => ['class' => 'is-neutral', 'symbol' => '✓'] })<a class="member-notification-item {{ $notification->is_read ? '' : 'is-unread' }}" data-notification-id="{{ $notification->id_notifikasi }}" href="{{ route('notifications.open', $notification) }}"><span class="member-notification-type-icon {{ $notificationIcon['class'] }}" aria-hidden="true">{{ $notificationIcon['symbol'] }}</span><div><strong>{{ $notification->judul }}</strong><small>{{ Str::limit($notification->pesan, 72) }}</small><time>{{ $notification->created_at?->format('d M Y · H:i') }}</time></div><b class="member-notification-chevron" aria-hidden="true">›</b></a>@empty<div class="member-notification-empty">Belum ada notifikasi baru.</div>@endforelse</div><a class="member-notification-all" href="{{ route('notifications.index') }}">Lihat semua notifikasi <span aria-hidden="true">→</span></a></div></div><div class="member-profile-wrap"><button class="member-profile-trigger" type="button" data-member-profile-toggle aria-expanded="false"><span class="member-avatar">{{ strtoupper(substr($memberName, 0, 1)) }}</span><span><strong>{{ $memberName }}</strong><small>YZA MEMBER</small></span><b>⌄</b></button><div class="member-profile-menu" data-member-profile-menu hidden><a href="{{ route('pelanggan.profile') }}">Profil Saya</a><a href="{{ route('pelanggan.member') }}">Member Center</a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form></div></div></div>
                </header>
                <main class="member-main-content">
                    @if(session('offline_payment_submitted') || session('status') || $errors->any())
                        <div class="member-toast-stack" data-member-toast-stack>
                            @if(session('offline_payment_submitted'))
                                <div class="member-toast member-toast-success" role="status" data-member-toast>
                                    <span class="member-toast-icon" aria-hidden="true">✓</span>
                                    <div class="member-toast-copy"><strong>Pengajuan pembayaran berhasil diterima</strong><span>Metode: {{ session('offline_payment_method') === 'tunai' ? 'Tunai di Dealer' : 'Transfer Langsung' }}. Pesanan Anda sedang menunggu verifikasi admin.</span><small>Anda tidak perlu mengajukan ulang pembayaran.</small></div>
                                    <button class="member-toast-close" type="button" data-member-toast-close aria-label="Tutup pemberitahuan">&times;</button>
                                </div>
                            @elseif(session('status'))
                                <div class="member-toast member-toast-success" role="status" data-member-toast>
                                    <span class="member-toast-icon" aria-hidden="true">✓</span>
                                    <div class="member-toast-copy"><strong>Informasi pesanan</strong><span>{{ session('status') }}</span></div>
                                    <button class="member-toast-close" type="button" data-member-toast-close aria-label="Tutup pemberitahuan">&times;</button>
                                </div>
                            @endif
                            @if($errors->any())
                                <div class="member-toast member-toast-error" role="alert" data-member-toast>
                                    <span class="member-toast-icon" aria-hidden="true">!</span>
                                    <div class="member-toast-copy"><strong>Permintaan belum dapat diproses</strong><span>{{ $errors->first() }}</span></div>
                                    <button class="member-toast-close" type="button" data-member-toast-close aria-label="Tutup pemberitahuan">&times;</button>
                                </div>
                            @endif
                        </div>
                    @endif
                    @yield('content')
                </main>
                @unless($isCheckoutPage)
                    @include('components.member-footer')
                @endunless
            </div>
            @if($isCheckoutPage)
                @include('components.member-footer')
            @endif
            <div class="member-overlay" data-member-overlay></div>
            <div class="member-live-toast-stack" data-member-live-toast-stack aria-live="polite" aria-atomic="false"></div>
        </div>
    @elseif($isAdminShell)
        @php($adminUser = auth()->user())
        @php($adminBadges = app(\App\Services\AdminNotificationBadgeService::class)->for((int) $adminUser->id_user))
        <div class="admin-shell" data-admin-shell>
            @include('components.admin-sidebar')
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button class="admin-toggle" type="button" data-admin-toggle aria-label="Buka navigasi admin">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="admin-topbar-search">
                        <svg class="admin-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" placeholder="Cari menu, pelanggan, mobil, atau transaksi..." data-admin-quick-search aria-label="Cari menu admin">
                        <kbd>Ctrl K</kbd>
                    </div>
                </div>
                <div class="admin-topbar-right">
                    <a class="admin-notification-button" href="{{ route('admin.notifikasi.index') }}" aria-label="Notifikasi" data-admin-notification-link data-admin-notification-sync-url="{{ route('admin.notifikasi.sync') }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        @if(($adminBadges['notifications'] ?? 0) > 0)
                            <b data-admin-notification-count>{{ ($adminBadges['notifications'] ?? 0) > 99 ? '99+' : $adminBadges['notifications'] }}</b>
                        @else
                            <b data-admin-notification-count hidden>0</b>
                        @endif
                    </a>
                    <div class="admin-user-menu" data-admin-user-menu>
                        <button class="admin-user-trigger" type="button" data-admin-user-toggle aria-expanded="false" aria-label="Menu administrator">
                            <span class="admin-avatar">{{ strtoupper(substr($adminUser->displayName(), 0, 1)) }}</span>
                            <div class="admin-user-info">
                                <strong>{{ $adminUser->displayName() }}</strong>
                                <small>Administrator</small>
                            </div>
                            <svg class="admin-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="admin-dropdown" data-admin-dropdown hidden>
                            <div class="admin-dropdown-header">
                                <strong>{{ $adminUser->displayName() }}</strong>
                                <span>{{ $adminUser->email ?? 'Administrator YZA' }}</span>
                            </div>
                            <a class="admin-dropdown-item" href="{{ route('admin.pengaturan.index') }}">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                <span>Pengaturan Sistem</span>
                            </a>
                            <form method="post" action="{{ route('logout') }}" class="admin-dropdown-logout-form">
                                @csrf
                                <button type="submit" class="admin-dropdown-item is-logout">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>
            <div class="admin-live-toast-stack" data-admin-live-toast-stack aria-live="polite" aria-atomic="false"></div>
            <main class="admin-main"><div class="admin-content"><div class="admin-page-heading"><h1>@yield('admin-page-title')</h1></div>@if(session('status'))<div class="yza-alert" role="status">{{ session('status') }}</div>@endif @if($errors->any())<div class="yza-alert yza-alert-error" role="alert">{{ $errors->first() }}</div>@endif @yield('content')</div></main>
            <footer class="admin-footer"><span>© {{ now()->year }} YZA Automotive</span><span>Admin Management System</span></footer>
            <div class="admin-backdrop" data-admin-backdrop></div>
        </div>
    @else
        <div class="yza-shell">
            @hasSection('catalog-header')
                @yield('catalog-header')
            @else
                <header class="yza-header">
                    <a class="yza-brand" href="{{ url('/') }}">
                        <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
                        <span>YZA Automotive</span>
                    </a>
                    <div class="yza-header-actions">
                        @auth
                            <form method="post" action="{{ route('logout') }}" class="yza-logout-form">
                                @csrf
                                <button class="yza-logout-button" type="submit">Keluar</button>
                            </form>
                        @endauth
                        <button class="yza-menu-toggle" type="button" aria-label="Buka navigasi" aria-expanded="false">☰</button>
                    </div>
                </header>
            @endif

            <div class="yza-body @if(View::hasSection('catalog-no-sidebar')) yza-body-full @endif">
                @if (!View::hasSection('catalog-no-sidebar'))
                    @hasSection('catalog-sidebar')
                        @yield('catalog-sidebar')
                    @else
                        <aside class="yza-sidebar" aria-label="Navigasi utama">
                            <div class="yza-sidebar-placeholder">Navigasi akan diaktifkan pada tahap authentication dan modul.</div>
                        </aside>
                    @endif
                @endif
                <main class="yza-content">
                    @if (session('status'))
                        <div class="yza-alert" role="status">{{ session('status') }}</div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
    @endif
    <script src="{{ asset('js/app.js') }}" defer></script>
    @if($isMemberShell)
    <script>
    (() => {
        const shell = document.querySelector('[data-member-shell]');
        if (!shell) return;
        const groups = [...shell.querySelectorAll('[data-member-nav-group]')];
        groups.forEach(group => group.querySelector('.member-nav-button')?.addEventListener('click', () => {
            const open = group.classList.contains('is-open');
            groups.forEach(item => { item.classList.remove('is-open'); item.querySelector('.member-nav-button')?.setAttribute('aria-expanded', 'false'); const sub = item.querySelector('.member-subnav'); if (sub) sub.hidden = true; });
            if (!open) { group.classList.add('is-open'); group.querySelector('.member-nav-button')?.setAttribute('aria-expanded', 'true'); const sub = group.querySelector('.member-subnav'); if (sub) sub.hidden = false; }
        }));
        const dropdowns = [...shell.querySelectorAll('.member-horizontal-menu')];
        dropdowns.forEach(menu => menu.addEventListener('toggle', () => {
            if (!menu.open) return;
            dropdowns.forEach(other => { if (other !== menu) other.removeAttribute('open'); });
        }));
        shell.addEventListener('click', event => {
            if (event.target.closest('.member-horizontal-menu')) return;
            dropdowns.forEach(menu => menu.removeAttribute('open'));
        });
        const notificationToggle = shell.querySelector('[data-member-notification-toggle]'); const notificationMenu = shell.querySelector('[data-member-notification-menu]');
        const orderLiveState = shell.querySelector('[data-order-live-state]');
        const notificationCount = shell.querySelector('[data-member-notification-count]'); const notificationUnreadLabel = shell.querySelector('[data-member-notification-unread-label]'); const notificationList = shell.querySelector('[data-member-notification-list]'); const liveToastStack = shell.querySelector('[data-member-live-toast-stack]');
        const syncUrl = notificationToggle?.dataset.memberNotificationSyncUrl; let latestNotificationId = Number(notificationToggle?.dataset.memberNotificationLatest || 0); let pollTimer = null; let pollInFlight = false; let pollDelay = 5000; let initialized = false; const displayedToastIds = new Set();
        const readableDate = value => { if (!value) return ''; const date = new Date(value); return Number.isNaN(date.getTime()) ? '' : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }); };
        const setNotificationCount = count => { const value = Number(count) || 0; if (notificationCount) { notificationCount.textContent = value > 99 ? '99+' : String(value); notificationCount.hidden = value < 1; } if (notificationUnreadLabel) notificationUnreadLabel.textContent = `${value} belum dibaca`; };
        const addNotificationItem = notification => { if (!notificationList || notificationList.querySelector(`[data-notification-id="${notification.id}"]`)) return; notificationList.querySelector('.member-notification-empty')?.remove(); const item = document.createElement('a'); item.className = `member-notification-item${notification.is_read ? '' : ' is-unread'}`; item.dataset.notificationId = notification.id; item.href = notification.open_url || notification.url || '#'; const icon = document.createElement('span'); icon.setAttribute('aria-hidden', 'true'); icon.textContent = notification.is_read ? '✓' : '!'; const content = document.createElement('div'); const title = document.createElement('strong'); title.textContent = notification.title; const message = document.createElement('small'); message.textContent = notification.message.length > 72 ? `${notification.message.slice(0, 69)}...` : notification.message; const time = document.createElement('time'); time.textContent = readableDate(notification.created_at); content.append(title, message, time); item.append(icon, content); notificationList.prepend(item); };
        const showNotificationToast = notification => { if (!liveToastStack || displayedToastIds.has(notification.id)) return; displayedToastIds.add(notification.id); const toast = document.createElement('div'); toast.className = 'member-toast member-toast-success member-live-toast'; toast.dataset.memberLiveToast = notification.id; const icon = document.createElement('span'); icon.className = 'member-toast-icon'; icon.textContent = '!'; const copy = document.createElement('div'); copy.className = 'member-toast-copy'; const title = document.createElement('strong'); title.textContent = notification.title; const message = document.createElement('span'); message.textContent = notification.message; const link = document.createElement('a'); link.href = notification.open_url || notification.url || '#'; link.textContent = 'Lihat detail →'; copy.append(title, message, link); const close = document.createElement('button'); close.className = 'member-toast-close'; close.type = 'button'; close.setAttribute('aria-label', 'Tutup notifikasi'); close.innerHTML = '&times;'; close.addEventListener('click', () => toast.remove()); toast.append(icon, copy, close); liveToastStack.append(toast); window.setTimeout(() => { toast.classList.add('is-closing'); window.setTimeout(() => toast.remove(), 280); }, 7000); };
        const syncMemberOrderState = async () => { if (!orderLiveState || document.hidden) return; try { const response = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }); if (!response.ok) return; const html = await response.text(); const nextDocument = new DOMParser().parseFromString(html, 'text/html'); const nextState = nextDocument.querySelector('[data-order-live-state]')?.dataset.orderLiveState; if (nextState && nextState !== orderLiveState.dataset.orderLiveState) window['location']['reload'](); } catch (_) {} };
        const syncMemberNotifications = async () => { if (!syncUrl || pollInFlight || document.hidden) return; pollInFlight = true; try { const url = new URL(syncUrl, window.location.origin); url.searchParams.set('after_id', String(latestNotificationId)); const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }); if (!response.ok) throw new Error(`Notification sync failed: ${response.status}`); const data = await response.json(); setNotificationCount(data.unread_count); const notifications = Array.isArray(data.notifications) ? data.notifications : []; const incoming = notifications.filter(item => Number(item.id) > latestNotificationId).sort((a, b) => Number(a.id) - Number(b.id)); if (initialized) incoming.forEach(notification => { addNotificationItem(notification); showNotificationToast(notification); }); else notifications.forEach(addNotificationItem); latestNotificationId = Math.max(latestNotificationId, Number(data.latest_id) || 0, ...notifications.map(item => Number(item.id) || 0)); initialized = true; pollDelay = 5000; } catch (_) { pollDelay = Math.min(pollDelay + 5000, 15000); } finally { pollInFlight = false; window.clearTimeout(pollTimer); if (!document.hidden && syncUrl) pollTimer = window.setTimeout(syncMemberNotifications, pollDelay); } };
        if (orderLiveState) { const orderStateTimer = window.setInterval(syncMemberOrderState, 5000); document.addEventListener('visibilitychange', () => { if (!document.hidden) syncMemberOrderState(); }); window.addEventListener('beforeunload', () => window.clearInterval(orderStateTimer), { once: true }); }
        document.addEventListener('visibilitychange', () => { if (!document.hidden) { pollDelay = 5000; syncMemberNotifications(); } else window.clearTimeout(pollTimer); }); syncMemberNotifications();
        const sidebar = shell.querySelector('[data-member-mobile-drawer]'); const overlay = shell.querySelector('[data-member-overlay]'); const mobile = shell.querySelector('[data-member-mobile-toggle]'); const mobileClose = shell.querySelector('[data-member-mobile-close]');
        const closeDrawer = () => { sidebar?.classList.remove('is-open'); sidebar?.setAttribute('aria-hidden','true'); overlay?.classList.remove('is-visible'); document.body.classList.remove('member-drawer-open'); mobile?.setAttribute('aria-expanded','false'); };
        mobile?.addEventListener('click', () => { sidebar?.classList.add('is-open'); sidebar?.setAttribute('aria-hidden','false'); overlay?.classList.add('is-visible'); document.body.classList.add('member-drawer-open'); mobile?.setAttribute('aria-expanded','true'); sidebar?.querySelector('a')?.focus(); }); mobileClose?.addEventListener('click', closeDrawer); overlay?.addEventListener('click', closeDrawer); sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeDrawer));
        document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeDrawer(); closeProfile(); } });
        const closeNotification = () => { if (notificationMenu) notificationMenu.hidden = true; notificationToggle?.setAttribute('aria-expanded', 'false'); };
        notificationToggle?.addEventListener('click', event => { event.stopPropagation(); const open = notificationMenu?.hidden ?? true; if (notificationMenu) notificationMenu.hidden = !open; notificationToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); closeProfile(); });
        const profileToggle = shell.querySelector('[data-member-profile-toggle]'); const profileMenu = shell.querySelector('[data-member-profile-menu]');
        const closeProfile = () => { if (profileMenu) profileMenu.hidden = true; profileToggle?.setAttribute('aria-expanded', 'false'); };
        profileToggle?.addEventListener('click', event => { event.stopPropagation(); const open = profileMenu?.hidden ?? true; if (profileMenu) profileMenu.hidden = !open; profileToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); closeNotification(); });
        document.addEventListener('click', event => { if (profileMenu && !profileMenu.contains(event.target) && !profileToggle?.contains(event.target)) closeProfile(); if (notificationMenu && !notificationMenu.contains(event.target) && !notificationToggle?.contains(event.target)) closeNotification(); });

        const dismissToast = (toast) => { if (!toast || toast.classList.contains('is-closing')) return; toast.classList.add('is-closing'); window.setTimeout(() => toast.remove(), 280); };
        shell.querySelectorAll('[data-member-toast]').forEach(toast => {
            toast.querySelector('[data-member-toast-close]')?.addEventListener('click', () => dismissToast(toast));
            window.setTimeout(() => dismissToast(toast), 4000);
        });
    })();
    </script>
    @endif
    @if($isAdminShell)
    <script>
    (() => {
        const shell = document.querySelector('[data-admin-shell]');
        if (!shell) return;
        const paymentLiveState = document.querySelector('[data-admin-payment-live-state]');
        if (paymentLiveState) {
            const syncPaymentPage = async () => {
                if (document.hidden || document.activeElement?.matches('input, textarea, select, button')) return;
                const modal = document.querySelector('.payment-modal:not([hidden]), [data-payment-modal]:not([hidden])');
                if (modal) return;
                try {
                    const response = await fetch(window.location.href, { cache: 'no-store', headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                    if (!response.ok) return;
                    const html = await response.text();
                    const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                    const nextState = nextDocument.querySelector('[data-admin-payment-live-state]')?.dataset.adminPaymentLiveState;
                    if (nextState && nextState !== paymentLiveState.dataset.adminPaymentLiveState) window['location']['reload']();
                } catch (_) {}
            };
            const paymentSyncTimer = window.setInterval(syncPaymentPage, 5000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) syncPaymentPage(); });
            window.addEventListener('beforeunload', () => window.clearInterval(paymentSyncTimer), { once: true });
        }
        const sidebar = shell.querySelector('[data-admin-sidebar]');
        const toggle = shell.querySelector('[data-admin-toggle]');
        const backdrop = shell.querySelector('[data-admin-backdrop]');
        const closeSidebar = () => {
            sidebar?.classList.remove('is-open');
            backdrop?.classList.remove('is-visible');
        };
        toggle?.addEventListener('click', () => {
            sidebar?.classList.toggle('is-open');
            backdrop?.classList.toggle('is-visible');
        });
        backdrop?.addEventListener('click', closeSidebar);
        sidebar?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeSidebar));

        // User dropdown menu
        const userMenu = shell.querySelector('[data-admin-user-menu]');
        const userToggle = userMenu?.querySelector('[data-admin-user-toggle]');
        const userDropdown = userMenu?.querySelector('[data-admin-dropdown]');
        const closeUserMenu = () => {
            if (userDropdown) userDropdown.hidden = true;
            userToggle?.setAttribute('aria-expanded', 'false');
        };
        userToggle?.addEventListener('click', (e) => {
            e.stopPropagation();
            const isHidden = userDropdown?.hidden ?? true;
            if (userDropdown) userDropdown.hidden = !isHidden;
            userToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });
        document.addEventListener('click', (e) => {
            if (userMenu && !userMenu.contains(e.target)) closeUserMenu();
        });

        // Quick search focus (Ctrl+K or Cmd+K)
        const searchInput = shell.querySelector('[data-admin-quick-search]');
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                searchInput?.focus();
            }
            if (e.key === 'Escape') {
                closeUserMenu();
                closeSidebar();
            }
        });

        // Lightweight near-real-time admin notification sync.
        const notificationLink = shell.querySelector('[data-admin-notification-link]');
        const notificationCount = shell.querySelector('[data-admin-notification-count]');
        const toastStack = shell.querySelector('[data-admin-live-toast-stack]');
        const notificationList = document.querySelector('[data-admin-notification-list]');
        const syncUrl = notificationLink?.dataset.adminNotificationSyncUrl;
        let latestNotificationId = 0;
        let pollTimer = null;
        let pollInFlight = false;
        let pollDelay = 5000;
        let initialized = false;
        const displayedToastIds = new Set();

        const setNotificationCount = (count) => {
            if (!notificationCount) return;
            const value = Number(count) || 0;
            notificationCount.textContent = value > 99 ? '99+' : String(value);
            notificationCount.hidden = value < 1;
        };

        const readableDate = (value) => {
            if (!value) return '';
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? '' : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
        };

        const addNotificationCard = (notification) => {
            if (!notificationList || notificationList.querySelector(`[data-notification-id="${notification.id}"]`)) return;
            const card = document.createElement('article');
            card.className = `admin-notification-card${notification.is_read ? '' : ' is-unread'}`;
            card.dataset.notificationId = notification.id;

            const icon = document.createElement('span');
            icon.className = 'admin-notification-card-icon';
            icon.textContent = notification.type === 'booking' ? '⚙' : '!';
            const content = document.createElement('div');
            content.className = 'admin-notification-card-content';
            const category = document.createElement('small');
            category.textContent = notification.type === 'booking' ? 'BOOKING SERVIS' : 'AKTIVITAS ADMIN';
            const title = document.createElement('strong');
            title.textContent = notification.title;
            const message = document.createElement('p');
            message.textContent = notification.message;
            const meta = document.createElement('time');
            meta.textContent = readableDate(notification.created_at);
            const link = document.createElement('a');
            link.className = 'admin-notification-card-link';
            link.href = notification.open_url || notification.url || '#';
            link.textContent = notification.type === 'booking' ? 'Lihat Booking →' : 'Buka Detail →';
            content.append(category, title, message, meta, link);
            card.append(icon, content);
            notificationList.prepend(card);
        };

        const showNotificationToast = (notification) => {
            if (!toastStack || displayedToastIds.has(notification.id)) return;
            displayedToastIds.add(notification.id);
            const toast = document.createElement('div');
            toast.className = 'admin-live-toast';
            const title = document.createElement('strong');
            title.textContent = notification.title;
            const message = document.createElement('p');
            message.textContent = notification.message;
            const link = document.createElement('a');
            link.href = notification.open_url || notification.url || '#';
            link.textContent = notification.type === 'booking' ? 'Lihat Booking →' : 'Buka Detail →';
            toast.append(title, message, link);
            toastStack.append(toast);
            requestAnimationFrame(() => toast.classList.add('is-visible'));
            window.setTimeout(() => { toast.classList.remove('is-visible'); window.setTimeout(() => toast.remove(), 250); }, 7500);
        };

        const schedulePoll = () => {
            window.clearTimeout(pollTimer);
            if (!document.hidden && syncUrl) pollTimer = window.setTimeout(syncNotifications, pollDelay);
        };

        const syncNotifications = async () => {
            if (!syncUrl || pollInFlight || document.hidden) return;
            pollInFlight = true;
            try {
                const url = new URL(syncUrl, window.location.origin);
                url.searchParams.set('after_id', String(latestNotificationId));
                const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (!response.ok) throw new Error(`Notification sync failed: ${response.status}`);
                const data = await response.json();
                setNotificationCount(data.unread_count);
                const notifications = Array.isArray(data.notifications) ? data.notifications : [];
                const incoming = notifications.filter(item => Number(item.id) > latestNotificationId).sort((a, b) => Number(a.id) - Number(b.id));
                if (initialized) incoming.forEach(notification => { addNotificationCard(notification); showNotificationToast(notification); });
                else notifications.forEach(addNotificationCard);
                latestNotificationId = Math.max(latestNotificationId, Number(data.latest_id) || 0, ...notifications.map(item => Number(item.id) || 0));
                initialized = true;
                pollDelay = 5000;
            } catch (error) {
                pollDelay = Math.min(pollDelay + 5000, 15000);
            } finally {
                pollInFlight = false;
                schedulePoll();
            }
        };

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) { pollDelay = 5000; syncNotifications(); }
            else window.clearTimeout(pollTimer);
        });
        syncNotifications();
    })();
    </script>
    @endif
    @stack('scripts')
</body>
</html>
