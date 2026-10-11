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
        $isMemberShell = auth()->check() && auth()->user()->roleName() === 'pelanggan' && request()->routeIs('pelanggan.*') && !View::hasSection('member-portal');
        $isCheckoutPage = $isMemberShell && request()->routeIs('pelanggan.pesanan-mobil.create');
    @endphp
    @if($isMemberShell)
        <link rel="stylesheet" href="{{ asset('assets/css/member-portal.css') }}?v={{ filemtime(public_path('assets/css/member-portal.css')) }}">
        <link rel="stylesheet" href="{{ asset('assets/css/member-navbar-final.css') }}?v={{ filemtime(public_path('assets/css/member-navbar-final.css')) }}">
    @endif
    @stack('styles')
</head>
<body class="@yield('body-class') @if($isMemberShell) member-portal-page member-shell-page @endif">
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
        @endphp
        <div class="member-app-shell member-horizontal-shell" data-member-shell>
            <div class="member-workspace">
                <header class="member-topbar">
                    <div class="member-topbar-left"><button class="member-mobile-toggle" type="button" data-member-mobile-toggle aria-label="Buka navigasi" aria-expanded="false">☰</button><a class="member-topbar-brand" href="{{ route('pelanggan.member') }}" aria-label="Kembali ke Beranda YZA Automotive"><img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive"><span><strong>YZA AUTOMOTIVE</strong><small>Premium Member Portal</small></span></a></div>
                    @include('components.member-horizontal-nav')
                    <div class="member-topbar-right"><span class="member-notification" aria-label="Notifikasi">♧<i aria-hidden="true"></i></span><div class="member-profile-wrap"><button class="member-profile-trigger" type="button" data-member-profile-toggle aria-expanded="false"><span class="member-avatar">{{ strtoupper(substr($memberName, 0, 1)) }}</span><span><strong>{{ $memberName }}</strong><small>YZA MEMBER</small></span><b>⌄</b></button><div class="member-profile-menu" data-member-profile-menu hidden><a href="{{ route('pelanggan.profile') }}">Profil Saya</a><a href="{{ route('pelanggan.member') }}">Member Center</a><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Logout</button></form></div></div></div>
                </header>
                <main class="member-main-content">
                    @if(session('status'))<div class="member-alert" role="status">{{ session('status') }}</div>@endif
                    @if($errors->any())<div class="member-alert" role="alert">{{ $errors->first() }}</div>@endif
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
                    <button class="yza-menu-toggle" type="button" aria-label="Buka navigasi" aria-expanded="false">☰</button>
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
        const sidebar = shell.querySelector('[data-member-mobile-drawer]'); const overlay = shell.querySelector('[data-member-overlay]'); const mobile = shell.querySelector('[data-member-mobile-toggle]'); const mobileClose = shell.querySelector('[data-member-mobile-close]');
        const closeDrawer = () => { sidebar?.classList.remove('is-open'); sidebar?.setAttribute('aria-hidden','true'); overlay?.classList.remove('is-visible'); document.body.classList.remove('member-drawer-open'); mobile?.setAttribute('aria-expanded','false'); };
        mobile?.addEventListener('click', () => { sidebar?.classList.add('is-open'); sidebar?.setAttribute('aria-hidden','false'); overlay?.classList.add('is-visible'); document.body.classList.add('member-drawer-open'); mobile?.setAttribute('aria-expanded','true'); sidebar?.querySelector('a')?.focus(); }); mobileClose?.addEventListener('click', closeDrawer); overlay?.addEventListener('click', closeDrawer); sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeDrawer));
        document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeDrawer(); closeProfile(); } });
        const profileToggle = shell.querySelector('[data-member-profile-toggle]'); const profileMenu = shell.querySelector('[data-member-profile-menu]');
        const closeProfile = () => { if (profileMenu) profileMenu.hidden = true; profileToggle?.setAttribute('aria-expanded', 'false'); };
        profileToggle?.addEventListener('click', event => { event.stopPropagation(); const open = profileMenu?.hidden ?? true; if (profileMenu) profileMenu.hidden = !open; profileToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); });
        document.addEventListener('click', event => { if (profileMenu && !profileMenu.contains(event.target) && !profileToggle?.contains(event.target)) closeProfile(); });
    })();
    </script>
    @endif
    @stack('scripts')
</body>
</html>
