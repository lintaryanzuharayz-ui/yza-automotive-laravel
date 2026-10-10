@php
    $memberState = $memberState ?? $landingMemberState ?? 'guest';
    if ($memberState === 'guest' && auth()->check() && auth()->user()->roleName() === 'pelanggan') {
        $memberState = auth()->user()->pelanggan?->membership?->status ?? 'guest';
    }

    $isLanding = request()->is('/');
    $isMobil = request()->routeIs('mobil.index', 'mobil.show', 'mobil.showcase');
    $memberHref = $memberState === 'active'
        ? route('pelanggan.member')
        : (in_array($memberState, ['pending_payment', 'cancelled'], true)
            ? route('pelanggan.membership.activation')
            : ($isLanding ? '#yza-member' : url('/#yza-member')));
    $href = static fn (string $section): string => $isLanding ? '#'.$section : url('/#'.$section);
@endphp

<header class="show-nav public-navbar {{ $memberState === 'active' ? '' : 'public-navbar--guest' }}" data-public-navbar>
    <a class="show-brand" href="{{ url('/') }}" aria-label="YZA Automotive">
        <img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive">
        <span><strong>YZA</strong><small>Automotive</small></span>
    </a>
    @if($memberState === 'active')
        <button class="show-mobile-toggle show-search" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="show-nav-links">☰</button>
        <nav class="show-nav-links" id="show-nav-links" aria-label="Navigasi publik">
            <a class="{{ $isLanding ? 'active' : '' }}" href="{{ $href('beranda') }}" @if($isLanding) aria-current="page" @endif>Beranda</a>
            <a class="{{ $isMobil ? 'active' : '' }}" href="{{ $isLanding ? '#mobil' : route('mobil.index') }}" @if($isMobil) aria-current="page" @endif>Mobil</a>
            <a href="{{ $memberHref }}">YZA Member</a>
            <a href="{{ $href('jual-mobil') }}">Jual Mobil</a>
            <a href="{{ $href('jual-mobil') }}">Trade-In</a>
            <a href="{{ $href('servis') }}">Servis</a>
            <a href="{{ $href('tentang') }}">Tentang Kami</a>
            <a href="{{ $href('kontak') }}">Kontak</a>
        </nav>
    @endif
    <div class="show-actions">
        <a class="show-login" href="{{ route('login') }}"><span>MASUK MEMBER</span><b aria-hidden="true">→</b></a>
        <a class="show-cta show-member-primary" href="{{ route('register') }}">BUAT MEMBER</a>
    </div>
</header>

@once
    <script>
        (() => {
            const nav = document.querySelector('[data-public-navbar]');
            const toggle = nav?.querySelector('.show-mobile-toggle');
            const links = nav?.querySelector('.show-nav-links');
            if (!toggle || !links) return;
            toggle.addEventListener('click', () => {
                const open = links.classList.toggle('open');
                toggle.setAttribute('aria-expanded', String(open));
            });
            links.addEventListener('click', () => {
                links.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        })();
    </script>
@endonce
