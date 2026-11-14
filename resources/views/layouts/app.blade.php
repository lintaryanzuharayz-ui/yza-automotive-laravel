<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="{{ asset('assets/js/rupiah-input.js') }}?v={{ filemtime(public_path('assets/js/rupiah-input.js')) }}" defer></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vehicle-badges.css') }}?v=stock-badges-20261010">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
    </style>
    @php
        $notificationState = auth()->check() ? \Illuminate\Support\Facades\DB::transaction(fn () => app(\App\Services\NotificationStateService::class)->summary(auth()->user())) : [];
        $isMemberShell = auth()->check() && auth()->user()->roleName() === 'pelanggan' && (request()->routeIs('pelanggan.*') || request()->routeIs('notifications.*')) && !View::hasSection('member-portal');
        $isAdminShell = auth()->check() && auth()->user()->roleKey() !== 'pelanggan' && (request()->is('admin/*') || request()->is('sales/*') || request()->is('owner/*') || request()->is('petugas/*') || request()->routeIs('staff.dashboard', 'account.password', 'notifications.*'));
        $isCheckoutPage = $isMemberShell && request()->routeIs('pelanggan.pesanan-mobil.create');
        $isOwnerShell = $isAdminShell && (auth()->user()->roleKey() === 'owner' || request()->is('owner/*'));
    @endphp
    @if($isMemberShell)
        <link rel="stylesheet" href="{{ asset('assets/css/member-portal.css') }}?v={{ filemtime(public_path('assets/css/member-portal.css')) }}">
        <link rel="stylesheet" href="{{ asset('assets/css/member-navbar-final.css') }}?v={{ filemtime(public_path('assets/css/member-navbar-final.css')) }}">
    @endif
    @if($isAdminShell)
        <link rel="stylesheet" href="{{ asset('assets/css/admin-portal.css') }}?v={{ filemtime(public_path('assets/css/admin-portal.css')) }}">
    @endif
    @stack('styles')
    @if($isOwnerShell)
        <link rel="stylesheet" href="{{ asset('assets/css/owner-executive.css') }}?v={{ filemtime(public_path('assets/css/owner-executive.css')) }}">
    @endif
    @include('components.responsive-foundation')
    @if($isAdminShell && !$isOwnerShell)
        <link rel="stylesheet" href="{{ asset('assets/css/admin-navigation.css') }}?v={{ filemtime(public_path('assets/css/admin-navigation.css')) }}">
    @endif
</head>
<body class="@yield('body-class') @if($isMemberShell) member-portal-page member-shell-page @endif @if($isAdminShell) admin-portal-page @endif @if($isAdminShell && !$isOwnerShell) admin-navigation-unified @endif">
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
            $memberNotificationUnreadCount = $notificationState['unread_count'] ?? 0;
        @endphp
        <div class="member-app-shell member-horizontal-shell" data-member-shell>
            <div class="member-workspace">
                <header class="member-topbar">
                    <div class="member-topbar-left"><button class="member-mobile-toggle" type="button" data-member-mobile-toggle aria-label="Buka navigasi" aria-expanded="false">☰</button><a class="member-topbar-brand" href="{{ route('pelanggan.member') }}" aria-label="Kembali ke Beranda YZA Automotive"><img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive"><span><strong>YZA AUTOMOTIVE</strong><small>Premium Member Portal</small></span></a></div>
                    @include('components.member-horizontal-nav')
                    <div class="member-topbar-right"><div class="member-notification-wrap"><button class="member-notification" type="button" data-member-notification-toggle data-member-notification-sync-url="{{ route('notifications.sync') }}" aria-expanded="false" aria-label="Buka notifikasi" title="Notifikasi"><span aria-hidden="true">🔔</span><i data-member-notification-count aria-hidden="true" @if($memberNotificationUnreadCount < 1) hidden @endif>{{ $memberNotificationUnreadCount > 99 ? '99+' : $memberNotificationUnreadCount }}</i></button></div><div class="member-profile-wrap"><button class="member-profile-trigger" type="button" data-member-profile-toggle aria-expanded="false"><span class="member-avatar">{{ strtoupper(substr($memberName, 0, 1)) }}</span><span><strong>{{ $memberName }}</strong><small>YZA MEMBER</small></span><b>⌄</b></button><div class="member-profile-menu" data-member-profile-menu hidden><a href="{{ route('pelanggan.profile') }}">Profil Saya</a><a href="{{ route('pelanggan.member') }}">Member Center</a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form></div></div></div>
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
    @elseif($isOwnerShell)
        @include('components.owner-shell')
    @elseif($isAdminShell)
        @php($adminUser = auth()->user())
        @php($adminBadges = array_merge($notificationState['pending_counts'] ?? [], ['notifications' => $notificationState['unread_count'] ?? 0]))
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
                                <small>{{ \App\Support\AdminNavigation::roleLabel($adminUser->roleKey()) }}</small>
                            </div>
                            <svg class="admin-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="admin-dropdown" data-admin-dropdown hidden>
                            <div class="admin-dropdown-header">
                                <strong>{{ $adminUser->displayName() }}</strong>
                                <span>{{ $adminUser->email ?? 'Administrator YZA' }}</span>
                            </div>
                            @if($adminUser->hasPermission('staff.manage'))
                            <a class="admin-dropdown-item" href="{{ route('admin.pengaturan.index') }}">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                <span>Pengaturan Sistem</span>
                            </a>
                            @else
                            <a class="admin-dropdown-item" href="{{ route('account.password') }}">Profil Akun &amp; Password</a>
                            @endif
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
    @include('components.notification-panel')
    @include('components.notification-toasts')
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
        const sidebar = shell.querySelector('[data-member-mobile-drawer]'); const overlay = shell.querySelector('[data-member-overlay]'); const mobile = shell.querySelector('[data-member-mobile-toggle]'); const mobileClose = shell.querySelector('[data-member-mobile-close]');
        const closeDrawer = () => { if (sidebar?.contains(document.activeElement)) mobile?.focus(); sidebar?.classList.remove('is-open'); sidebar?.setAttribute('aria-hidden','true'); if (sidebar) sidebar.inert = true; overlay?.classList.remove('is-visible'); document.body.classList.remove('member-drawer-open'); mobile?.setAttribute('aria-expanded','false'); };
        mobile?.addEventListener('click', () => { if (sidebar) sidebar.inert = false; sidebar?.setAttribute('aria-hidden','false'); sidebar?.classList.add('is-open'); overlay?.classList.add('is-visible'); document.body.classList.add('member-drawer-open'); mobile?.setAttribute('aria-expanded','true'); sidebar?.querySelector('[data-member-mobile-close]')?.focus(); }); mobileClose?.addEventListener('click', closeDrawer); overlay?.addEventListener('click', closeDrawer); sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeDrawer));
        document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeDrawer(); closeProfile(); } });
        const closeNotification = () => window.YzaNotificationPanel?.close();
        const profileToggle = shell.querySelector('[data-member-profile-toggle]'); const profileMenu = shell.querySelector('[data-member-profile-menu]');
        const closeProfile = () => { if (profileMenu) profileMenu.hidden = true; profileToggle?.setAttribute('aria-expanded', 'false'); };
        profileToggle?.addEventListener('click', event => { event.stopPropagation(); const open = profileMenu?.hidden ?? true; if (profileMenu) profileMenu.hidden = !open; profileToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); closeNotification(); });
        document.addEventListener('click', event => { if (profileMenu && !profileMenu.contains(event.target) && !profileToggle?.contains(event.target)) closeProfile(); });

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

        // Preserve the independent sidebar position between admin page navigations.
        // The active route is still rendered server-side; this only restores/reveals it
        // inside the sidebar scroll container and never touches the main workspace.
        const sidebarNav = shell.querySelector('.admin-sidebar-nav-scroll');
        const sidebarScrollKey = 'yza_admin_sidebar_scroll_top';
        if (sidebarNav && sidebarNav.dataset.smartScrollReady !== '1') {
            sidebarNav.dataset.smartScrollReady = '1';
            let saveTimer = null;
            const writeSidebarPosition = () => {
                try { window.sessionStorage.setItem(sidebarScrollKey, String(Math.max(0, sidebarNav.scrollTop))); } catch (_) {}
            };
            const saveSidebarPosition = () => {
                window.clearTimeout(saveTimer);
                saveTimer = window.setTimeout(writeSidebarPosition, 80);
            };
            const clamp = (value) => Math.max(0, Math.min(Number(value) || 0, sidebarNav.scrollHeight - sidebarNav.clientHeight));
            const activeItem = sidebarNav.querySelector('.admin-nav-item.is-active');
            const revealActiveItemIfNeeded = () => {
                if (!activeItem || sidebarNav.clientHeight <= 0) return;
                const navRect = sidebarNav.getBoundingClientRect();
                const itemRect = activeItem.getBoundingClientRect();
                const edgePadding = 12;
                if (itemRect.top < navRect.top + edgePadding) {
                    sidebarNav.scrollTop = clamp(sidebarNav.scrollTop + itemRect.top - navRect.top - edgePadding);
                } else if (itemRect.bottom > navRect.bottom - edgePadding) {
                    sidebarNav.scrollTop = clamp(sidebarNav.scrollTop + itemRect.bottom - navRect.bottom + edgePadding);
                }
            };
            const restoreSidebarPosition = () => {
                let storedPosition = null;
                try { storedPosition = window.sessionStorage.getItem(sidebarScrollKey); } catch (_) {}
                if (storedPosition !== null) sidebarNav.scrollTop = clamp(storedPosition);
                revealActiveItemIfNeeded();
            };
            sidebarNav.addEventListener('scroll', saveSidebarPosition, { passive: true });
            sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', writeSidebarPosition));
            window.addEventListener('pagehide', writeSidebarPosition, { once: true });
            window.requestAnimationFrame(() => window.requestAnimationFrame(restoreSidebarPosition));
        }

        const sidebarCollapseToggle = shell.querySelector('[data-admin-sidebar-collapse]');
        const sidebarCollapseKey = 'yza_admin_sidebar_collapsed';
        const desktopSidebarQuery = window.matchMedia('(min-width: 1025px)');
        const readCollapsedState = () => {
            try { return window.localStorage.getItem(sidebarCollapseKey) === '1'; } catch (_) { return false; }
        };
        const writeCollapsedState = (collapsed) => {
            try { window.localStorage.setItem(sidebarCollapseKey, collapsed ? '1' : '0'); } catch (_) {}
        };
        const setSidebarCollapsed = (collapsed, persist = false) => {
            const effectiveCollapsed = desktopSidebarQuery.matches && collapsed;
            shell.classList.toggle('is-sidebar-collapsed', effectiveCollapsed);
            sidebarCollapseToggle?.setAttribute('aria-expanded', effectiveCollapsed ? 'false' : 'true');
            sidebarCollapseToggle?.setAttribute('aria-label', effectiveCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar');
            if (sidebarCollapseToggle) sidebarCollapseToggle.title = effectiveCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar';
            if (persist && desktopSidebarQuery.matches) writeCollapsedState(effectiveCollapsed);
            if (!effectiveCollapsed && sidebarNav) window.requestAnimationFrame(() => window.requestAnimationFrame(() => {
                const active = sidebarNav.querySelector('.admin-nav-item.is-active');
                if (active) {
                    const navRect = sidebarNav.getBoundingClientRect();
                    const itemRect = active.getBoundingClientRect();
                    const padding = 12;
                    if (itemRect.top < navRect.top + padding) sidebarNav.scrollTop = Math.max(0, sidebarNav.scrollTop + itemRect.top - navRect.top - padding);
                    else if (itemRect.bottom > navRect.bottom - padding) sidebarNav.scrollTop = Math.min(sidebarNav.scrollHeight - sidebarNav.clientHeight, sidebarNav.scrollTop + itemRect.bottom - navRect.bottom + padding);
                }
            }));
        };
        setSidebarCollapsed(readCollapsedState());
        sidebarCollapseToggle?.addEventListener('click', () => setSidebarCollapsed(!shell.classList.contains('is-sidebar-collapsed'), true));
        desktopSidebarQuery.addEventListener?.('change', () => setSidebarCollapsed(readCollapsedState()));

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

    })();
    </script>
    @endif
    @stack('scripts')
    @include('components.notification-sync')
</body>
</html>
