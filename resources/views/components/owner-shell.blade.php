@php
    $ownerUser = auth()->user();
    $ownerUnread = $notificationState['unread_count'] ?? app(\App\Services\NotificationStateService::class)->summary($ownerUser)['unread_count'];
    $ownerGroups = [
        ['Overview', [['Executive Dashboard', 'owner.dashboard', 'grid']]],
        ['Sales & Performance', [['Rekap Penjualan', 'owner.sales-report', 'car'], ['Performa Sales', 'owner.performance-report', 'chart'], ['Sales Excellence Awards', 'owner.sales-awards', 'award']]],
        ['Operations', [['Laporan Servis', 'owner.service-report', 'tool'], ['Inventori Kendaraan', 'owner.inventory-report', 'orders']]],
        ['Financial', [['Pendapatan & Pembayaran', 'owner.payment-report', 'wallet']]],
        ['Account', [['Profil Akun', 'account.password', 'user']]],
    ];
@endphp
<div class="admin-shell owner-shell" data-admin-shell data-owner-shell>
    <a class="oe-skip" href="#owner-main">Lewati navigasi</a>
    <aside class="oe-sidebar" id="owner-navigation" aria-label="Navigasi eksekutif">
        <div class="oe-brand-row">
            <a class="oe-brand" href="{{ route('owner.dashboard') }}" aria-label="YZA Automotive, dashboard">
                <img src="{{ asset('images/branding/logo-yza.png') }}" alt="" width="52" height="52">
                <span><strong>YZA <b>AUTOMOTIVE</b></strong><small>Executive Management</small></span>
            </a>
            <button class="oe-collapse" type="button" data-owner-collapse aria-controls="owner-navigation" aria-expanded="true" aria-label="Ciutkan sidebar" title="Ciutkan sidebar">‹</button>
            <button class="oe-close" type="button" data-owner-close aria-label="Tutup navigasi"><x-owner-icon name="close"/></button>
        </div>
        <nav class="oe-nav">
            @foreach($ownerGroups as [$group, $links])
                @if($group === 'Account' || $ownerUser->hasPermission('reports.view'))
                <div class="oe-nav-group"><h2>{{ $group }}</h2>
                    @foreach($links as [$label, $routeName, $icon])
                        @if(Route::has($routeName))
                        <a href="{{ route($routeName) }}" class="oe-nav-link {{ request()->routeIs($routeName) ? 'is-active' : '' }}" title="{{ $label }}" aria-label="{{ $label }}" @if(request()->routeIs($routeName)) aria-current="page" @endif><x-owner-icon :name="$icon"/><span>{{ $label }}</span></a>
                        @endif
                    @endforeach
                </div>
                @endif
            @endforeach
        </nav>
        <div class="oe-sidebar-note"><span></span><span>Akses eksekutif · Read-only</span></div>
    </aside>
    <div class="oe-workspace">
        <header class="oe-topbar">
            <div class="oe-topbar-context"><button type="button" class="oe-mobile-toggle" data-owner-open aria-controls="owner-navigation" aria-expanded="false" aria-label="Buka navigasi"><x-owner-icon name="menu"/></button><span>Workspace <b>/</b> <strong>@yield('owner-breadcrumb', 'Executive Management')</strong></span></div>
            <div class="oe-topbar-actions">
                <a class="admin-notification-button" href="{{ route('notifications.index') }}" aria-label="Notifikasi" data-admin-notification-link data-admin-notification-sync-url="{{ route('notifications.sync') }}"><x-owner-icon name="bell"/><b data-admin-notification-count @if(!$ownerUnread) hidden @endif>{{ $ownerUnread > 99 ? '99+' : $ownerUnread }}</b></a>
                <div class="admin-user-menu" data-admin-user-menu>
                    <button class="admin-user-trigger" type="button" data-admin-user-toggle aria-expanded="false" aria-label="Menu akun Owner"><span class="oe-avatar">{{ strtoupper(mb_substr($ownerUser->displayName(), 0, 1)) }}</span><span class="oe-user-copy"><strong>{{ $ownerUser->displayName() }}</strong><small>{{ $ownerUser->roleKey() === 'super_admin' ? 'Super Admin' : 'Executive Management' }}</small></span><span aria-hidden="true">⌄</span></button>
                    <div class="admin-dropdown" data-admin-dropdown hidden><div class="admin-dropdown-header"><strong>{{ $ownerUser->displayName() }}</strong><span>Akun eksekutif</span></div><a class="admin-dropdown-item" href="{{ route('account.password') }}">Profil Akun & Password</a><form method="post" action="{{ route('logout') }}">@csrf<button class="admin-dropdown-item is-logout" type="submit">Keluar</button></form></div>
                </div>
            </div>
        </header>
        <main class="oe-main" id="owner-main" tabindex="-1">
            @unless(request()->routeIs('owner.dashboard'))<h1 class="oe-report-title">@yield('admin-page-title')</h1>@endunless
            @if(session('status'))<div class="yza-alert" role="status">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="yza-alert yza-alert-error" role="alert">{{ $errors->first() }}</div>@endif
            @yield('content')
        </main>
        <footer class="oe-footer"><span>© {{ now('Asia/Jakarta')->year }} YZA Automotive</span><span>Executive Intelligence</span></footer>
    </div>
    <div class="admin-live-toast-stack" data-admin-live-toast-stack aria-live="polite"></div>
    <div class="oe-overlay" data-owner-overlay hidden></div>
</div>
<script src="{{ asset('assets/js/owner-executive.js') }}?v={{ filemtime(public_path('assets/js/owner-executive.js')) }}" defer></script>
