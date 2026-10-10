@php
    $memberCustomer = auth()->user()?->pelanggan;
    $servicesActive = request()->routeIs('pelanggan.booking-servis.*', 'pelanggan.test-drive.*', 'pelanggan.booking-cuci.*', 'pelanggan.trade-in.*');
    $activityActive = request()->routeIs('pelanggan.booking.*', 'pelanggan.booking.index');
    $benefitActive = request()->routeIs('pelanggan.promo.*', 'pelanggan.poin');
@endphp
<aside class="member-sidebar" data-member-sidebar aria-label="Navigasi YZA Member">
    <div class="member-sidebar-brand">
        <a href="{{ route('pelanggan.member') }}" class="member-brand-link">
            <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
            <span><strong>YZA AUTOMOTIVE</strong><small>Premium Member Portal</small></span>
        </a>
    </div>

    <nav class="member-sidebar-nav" aria-label="Menu member">
        <a class="member-nav-link {{ request()->routeIs('pelanggan.kendaraan.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.kendaraan.index') }}"><span class="member-nav-icon">▣</span><span>Kendaraan Saya</span></a>

        <div class="member-nav-group {{ $servicesActive ? 'is-open' : '' }}" data-member-nav-group>
            <button class="member-nav-link member-nav-button {{ $servicesActive ? 'is-active' : '' }}" type="button" aria-expanded="{{ $servicesActive ? 'true' : 'false' }}"><span class="member-nav-icon">⚙</span><span>Layanan</span><b>⌄</b></button>
            <div class="member-subnav" {{ $servicesActive ? '' : 'hidden' }}>
                <a class="{{ request()->routeIs('pelanggan.booking-servis.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.booking-servis.index') }}">Booking Servis</a>
                <a class="{{ request()->routeIs('pelanggan.test-drive.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.test-drive.index') }}">Test Drive</a>
                <a class="{{ request()->routeIs('pelanggan.booking-cuci.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.booking-cuci.index') }}">Car Wash</a>
                <a class="{{ request()->routeIs('pelanggan.trade-in.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.trade-in.index') }}">Trade-In</a>
            </div>
        </div>

        <div class="member-nav-group {{ $activityActive ? 'is-open' : '' }}" data-member-nav-group>
            <button class="member-nav-link member-nav-button {{ $activityActive ? 'is-active' : '' }}" type="button" aria-expanded="{{ $activityActive ? 'true' : 'false' }}"><span class="member-nav-icon">◷</span><span>Aktivitas</span><b>⌄</b></button>
            <div class="member-subnav" {{ $activityActive ? '' : 'hidden' }}>
                <a class="{{ request()->routeIs('pelanggan.booking.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.booking.index') }}">Booking Saya</a>
                <a href="{{ route('pelanggan.member') }}#transaksi">Transaksi &amp; Pembayaran</a>
            </div>
        </div>

        <div class="member-nav-group {{ $benefitActive ? 'is-open' : '' }}" data-member-nav-group>
            <button class="member-nav-link member-nav-button {{ $benefitActive ? 'is-active' : '' }}" type="button" aria-expanded="{{ $benefitActive ? 'true' : 'false' }}"><span class="member-nav-icon">♕</span><span>Keuntungan Member</span><b>⌄</b></button>
            <div class="member-subnav" {{ $benefitActive ? '' : 'hidden' }}>
                <a class="{{ request()->routeIs('pelanggan.promo.*') ? 'is-active' : '' }}" href="{{ route('pelanggan.promo.index') }}">Promo Member</a>
                <a class="{{ request()->routeIs('pelanggan.poin') ? 'is-active' : '' }}" href="{{ route('pelanggan.poin') }}">YZA Points</a>
            </div>
        </div>
    </nav>

    <div class="member-sidebar-footer">
        <div class="member-access-card">
            <span class="member-access-icon">♕</span>
            <strong>EXCLUSIVE MEMBER ACCESS</strong>
            <p>Dapatkan pengalaman otomotif premium bersama YZA Automotive.</p>
        </div>
        <div class="member-sidebar-status"><span>Status Membership</span><strong>{{ strtoupper($memberCustomer?->membership?->status ?? 'guest') }}</strong></div>
    </div>
</aside>
