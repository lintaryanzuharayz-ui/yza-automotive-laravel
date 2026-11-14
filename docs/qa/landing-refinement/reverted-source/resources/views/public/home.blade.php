<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>YZA Automotive · Showroom Premium</title>
    <link rel="icon" href="{{ asset('assets/images/branding/logo-yza.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/variables.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/base.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/components.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/pages/appointment-final.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/showroom.css?v=services-membership-promo-20261004') }}">
    @include('components.responsive-foundation')
    <link rel="stylesheet" href="{{ asset('assets/css/pages/landing-original-restore.css') }}?v={{ filemtime(public_path('assets/css/pages/landing-original-restore.css')) }}">
</head>
<body class="showroom landing">
@if(true)
<div id="yza-intro" class="yza-intro" hidden aria-hidden="true" aria-modal="true" aria-label="Welcome to YZA Automotive" role="dialog">
    <div class="yza-intro-backdrop" aria-hidden="true"><img src="{{ asset('assets/images/hero/yza-intro-reference-match.png') }}" alt=""></div>
    <div class="yza-intro-vignette" aria-hidden="true"></div>
    <div class="yza-intro-premium">PREMIUM<br><span>CARS</span><i></i></div>
    <div class="yza-intro-content">
        <div class="yza-intro-brand"><img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"><div class="yza-intro-welcome"><i></i><span>SELAMAT DATANG DI</span><i></i></div><h1><span>YZA</span> <em>AUTOMOTIVE</em></h1><small>DRIVE YOUR EXPERIENCE</small></div>
        <div class="yza-intro-services" aria-label="Layanan YZA Automotive">
            <div class="yza-intro-service"><b aria-hidden="true"><svg class="yza-service-svg" viewBox="0 0 72 72" focusable="false"><path class="yza-service-frame" d="M18 4h36l14 12v40L54 68H18L4 56V16Z"/><path class="yza-service-symbol" d="M14 43h44l-4-15H18l-4 15Zm5-15 5-8h24l5 8M14 43v9h44v-9M22 52v5m28-5v5M22 37h.1m28 0h.1"/><circle class="yza-service-symbol" cx="23" cy="43" r="3"/><circle class="yza-service-symbol" cx="49" cy="43" r="3"/></svg></b><strong>JUAL BELI<br><span>MOBIL BERKUALITAS</span></strong></div>
            <div class="yza-intro-service"><b aria-hidden="true"><svg class="yza-service-svg" viewBox="0 0 72 72" focusable="false"><path class="yza-service-frame" d="M18 4h36l14 12v40L54 68H18L4 56V16Z"/><path class="yza-service-symbol" transform="translate(-1.5 0)" d="M51 16a10 10 0 0 0-12 12L19 48l5 5 20-20a10 10 0 0 0 12-12l-7 7-5-5 7-7Z"/></svg></b><strong>SERVIS &amp;<br><span>PERAWATAN</span></strong></div>
            <div class="yza-intro-service"><b aria-hidden="true"><svg class="yza-service-svg" viewBox="0 0 72 72" focusable="false"><path class="yza-service-frame" d="M18 4h36l14 12v40L54 68H18L4 56V16Z"/><path class="yza-service-symbol" transform="translate(5 0)" d="m34 16 2 6a15 15 0 0 1 5 2l5-4 4 4-4 5a15 15 0 0 1 2 5l6 2v6l-6 2a15 15 0 0 1-2 5l4 5-4 4-5-4a15 15 0 0 1-5 2l-2 6h-6l-2-6a15 15 0 0 1-5-2l-5 4-4-4 4-5a15 15 0 0 1-2-5l-6-2v-6l6-2a15 15 0 0 1 2-5l-4-5 4-4 5 4a15 15 0 0 1 5-2l2-6h6Zm-3 14a7 7 0 1 0 0 14 7 7 0 0 0 0-14Z"/></svg></b><strong>MODIFIKASI<br><span>&amp; AKSESORIS</span></strong></div>
        </div>
        <button class="yza-intro-enter" type="button"><span class="yza-intro-car-icon" aria-hidden="true"><svg class="yza-button-svg" viewBox="0 0 72 72" focusable="false"><path d="M14 43h44l-4-15H18l-4 15Zm5-15 5-8h24l5 8M14 43v9h44v-9M22 52v5m28-5v5"/><circle cx="23" cy="43" r="3"/><circle cx="49" cy="43" r="3"/></svg></span><b>MASUK KE SHOWROOM</b><span class="yza-intro-arrow" aria-hidden="true"><svg viewBox="0 0 32 20"><path d="M1 10h27M20 2l9 8-9 8"/></svg></span></button>
        <div class="yza-intro-bottom"><span></span><p>TEMUKAN MOBIL IMPIAN ANDA, DENGAN PENGALAMAN YANG LEBIH BERMAKNA</p><span></span></div>
    </div>
    <div class="yza-intro-door yza-intro-door--left" aria-hidden="true"></div><div class="yza-intro-door yza-intro-door--right" aria-hidden="true"></div>
</div>
<noscript><style>#yza-intro{display:none!important}</style></noscript>
@endif
<x-public-navbar :member-state="$landingMemberState ?? 'guest'" />
@php
    $featuredCars = $featuredCars ?? collect();
    $memberPrimaryHref = match ($landingMemberState) { 'active' => route('pelanggan.member'), 'pending_payment', 'cancelled' => route('pelanggan.membership.activation'), default => route('register', ['from' => 'membership']) };
    $memberPrimaryLabel = match ($landingMemberState) { 'active' => 'BUKA MEMBER CENTER', 'pending_payment' => 'AKTIFKAN YZA MEMBER', 'cancelled' => 'LIHAT STATUS MEMBERSHIP', default => 'BERGABUNG JADI MEMBER' };
    $contactSettings = app(\App\Services\PengaturanService::class);
    $contactAddress = trim((string) $contactSettings->get('alamat_perusahaan', ''));
    $contactPhone = trim((string) $contactSettings->get('no_hp_perusahaan', '')) ?: '0877 8430 4417';
    $contactEmail = filter_var($contactSettings->get('email_perusahaan', ''), FILTER_VALIDATE_EMAIL) ?: '';
    $contactMapUrl = 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($contactAddress);
@endphp
<main id="beranda">
<section class="show-hero"><div class="show-hero-inner"><div class="hero-copy-premium"><span class="show-kicker">YZA AUTOMOTIVE SHOWROOM</span><h1>Temukan mobil<br><em>impian Anda.</em></h1><p>Koleksi mobil berkualitas, layanan profesional, dan pengalaman otomotif terbaik hanya di YZA Automotive.</p><div class="hero-buttons"><a class="show-cta" href="#mobil">Lihat Koleksi Mobil&nbsp; →</a><a class="show-ghost" href="#janji-temu">Jadwalkan Janji Temu</a></div><div class="hero-proof-line"><span><b>●</b> Koleksi pilihan terkurasi</span><span><b>●</b> Layanan profesional</span></div></div><div class="hero-car-stage">@php($featured = $featuredHeroCar ?? null)<div class="hero-3d-canvas" data-black-label-scene data-model-url="" aria-label="Podium showroom 3D"></div><div class="hero-podium-back" aria-hidden="true"></div><img class="hero-360-car" src="{{ asset('assets/images/catalog/toyota-gr-supra/front.png') }}" alt="Toyota GR Supra 2024"><div class="hero-car-contact-shadow" aria-hidden="true"></div><div class="hero-podium-front" aria-hidden="true"></div><div class="hero-vehicle-info" data-vehicle-id="{{ $featured?->id_mobil ?? '' }}">@if($featured)<button class="hero-favorite" type="button" data-featured-favorite aria-label="Tambahkan {{ $featured->model }} ke favorit" aria-pressed="false">♡</button><small>{{ strtoupper($featured->merk) }}</small><h2>{{ $featured->model }}</h2><p>{{ $featured->tahun ?: 'Premium collection' }} · {{ strtoupper($featured->transmisi ?: 'Automatic') }}</p><strong>Rp {{ number_format((float) $featured->harga_jual,0,',','.') }}</strong><a href="{{ route('mobil.showcase', ['slug' => 'toyota-gr-supra', 'source' => 'landing']) }}">Lihat Detail</a>@else<small>YZA AUTOMOTIVE</small><h2>Premium Collection</h2><p>Showroom pilihan YZA</p>@endif</div></div></div></section>
<section class="show-section featured-catalog" id="mobil"><div class="show-container"><div class="show-section-heading"><div><span class="section-kicker">Mobil pilihan</span><h2>Pilihan kendaraan <em>untuk Anda</em></h2></div><div class="featured-carousel-actions"><div class="featured-carousel-controls" aria-label="Navigasi koleksi"><button class="featured-carousel-button" type="button" data-carousel-prev aria-label="Mobil sebelumnya">←</button><button class="featured-carousel-button" type="button" data-carousel-next aria-label="Mobil berikutnya">→</button></div><a class="show-link" href="{{ route('mobil.index') }}"><span>Lihat Semua Mobil</span><b aria-hidden="true">→</b></a></div></div><div class="featured-carousel" data-sport-carousel data-carousel-count="{{ $featuredCars->count() }}"><div class="featured-carousel-viewport"><div class="featured-carousel-track" data-carousel-track>@forelse ($featuredCars as $vehicle)<article class="featured-card{{ $vehicle['isSport'] ? ' is-sport' : '' }}"><a class="featured-media" href="{{ $vehicle['url'] }}">@if($vehicle['photo'])<img src="{{ $vehicle['photo'] }}" alt="{{ $vehicle['name'] }}" draggable="false">@else<span class="vehicle-photo-placeholder"><b>{{ $vehicle['brand'] }}</b></span>@endif<span class="condition-pill">{{ $vehicle['condition'] }}</span>@if($vehicle['isSport'])<span class="sport-pill">SPORT</span>@endif<span class="featured-fav" aria-label="Favorit">♡</span></a><div class="featured-body"><a class="featured-body-link" href="{{ $vehicle['url'] }}"><h3>{{ $vehicle['name'] }}</h3><small>{{ $vehicle['specification'] }}</small></a><div class="featured-bottom"><a class="featured-price-link" href="{{ $vehicle['url'] }}"><strong>Rp {{ number_format($vehicle['price'],0,',','.') }}</strong></a><a class="round-arrow" href="{{ $vehicle['url'] }}" aria-label="Lihat detail">→</a></div></div></article>@empty<div class="featured-card"><div class="featured-body"><h3>Belum ada mobil tersedia</h3></div></div>@endforelse</div></div><div class="featured-carousel-footer" aria-label="Progress mobil pilihan"><div class="featured-carousel-progress" data-carousel-progress role="progressbar" aria-label="Progress mobil pilihan" aria-valuemin="1" aria-valuemax="{{ max(1, $featuredCars->count()) }}" aria-valuenow="1"><span class="is-active"></span></div><span class="featured-carousel-counter" data-carousel-counter>01 / {{ str_pad((string) max(1, $featuredCars->count()), 2, '0', STR_PAD_LEFT) }}</span><span class="featured-carousel-hint">Geser horizontal untuk melihat mobil lainnya</span></div></div></div></section>
<section class="show-section services-section" id="layanan"><span id="servis" class="landing-anchor" aria-hidden="true"></span><div class="show-container"><div class="services-heading"><span class="section-kicker">Layanan kami</span><h2>Layanan Kami</h2><p>Solusi lengkap untuk semua kebutuhan otomotif Anda.</p></div><div class="services-grid">@foreach ([['⚙','Servis Berkala','Perawatan rutin untuk performa maksimal.'],['▦','Perbaikan Mesin','Diagnosa dan perbaikan oleh teknisi ahli.'],['◉','Ganti Oli','Oli berkualitas untuk performa optimal.'],['◎','Rem & Suspensi','Keamanan berkendara lebih terjamin.'],['▣','AC Mobil','Dingin dan nyaman di setiap perjalanan.'],['▤','Diagnosa Komputer','Teknologi terbaru untuk hasil akurat.']] as $service)<article class="service-card-premium"><i aria-hidden="true"><x-landing-icon :name="['wrench','engine','oil','brakes','snowflake','scan'][$loop->index]" /></i><h3>{{ $service[1] }}</h3><p>{{ $service[2] }}</p></article>@endforeach</div><div class="services-action"><a class="show-cta" href="#janji-temu" data-appointment-service="Service">Jadwalkan Servis <span aria-hidden="true">→</span></a><a class="services-member-link" href="#yza-member">Kenali benefit YZA Member ↗</a></div></div></section>
<section class="trade-banner" id="jual-mobil"><div class="show-container trade-layout"><div class="trade-copy"><span class="section-kicker">Jual mobil Anda</span><h2>Jual atau tukar tambah.<br><em>Mulai langkah berikutnya.</em></h2><p>Dapatkan estimasi harga terbaik dengan proses cepat, aman, dan transparan.</p><a class="show-cta" href="{{ route('login') }}">Nilai Mobil Sekarang&nbsp; →</a></div><div class="benefit-list"><div><i aria-hidden="true"><x-landing-icon name="bolt" /></i><span><b>Proses Cepat</b>Tanpa ribet dan banyak dokumen.</span></div><div><i aria-hidden="true"><x-landing-icon name="tag" /></i><span><b>Harga Kompetitif</b>Sesuai kondisi pasar terkini.</span></div><div><i aria-hidden="true"><x-landing-icon name="shield" /></i><span><b>Aman &amp; Terpercaya</b>Transaksi profesional dan transparan.</span></div></div></div></section>
<section class="show-section experience" id="test-drive"><div class="show-container experience-layout"><div class="experience-copy"><span class="section-kicker">YZA AUTOMOTIVE — TEST DRIVE EXPERIENCE</span><h2>Rasakan sensasi<br><em>berkendara langsung.</em></h2><p>Rasakan pengalaman berada di balik kemudi mobil impian Anda. Jelajahi performa, kenyamanan, dan karakter kendaraan melalui layanan Test Drive YZA Automotive.</p><a class="show-cta" href="{{ route('pelanggan.test-drive.create') }}">Jadwalkan Test Drive&nbsp; →</a></div><div class="video-tile" data-video-tile><video class="video-tile-media" data-video-player muted playsinline preload="none" poster="{{ asset('assets/images/catalog/toyota-gr-supra/front.png') }}" aria-label="Video pengalaman Test Drive YZA Automotive"><source src="{{ asset('assets/videos/test-drive.mp4') }}" type="video/mp4"></video><button class="video-play" data-video-open type="button" aria-label="Putar video Test Drive YZA Automotive">▶</button><button class="video-mute" data-video-mute type="button" aria-label="Aktifkan suara video" aria-pressed="false"><svg class="video-mute-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4Z"></path><path class="video-mute-wave" d="M16 9.5c1.5 1.4 1.5 3.6 0 5"></path><path class="video-mute-wave" d="M18.5 7c3 2.8 3 7.2 0 10"></path><path class="video-mute-slash" d="m4 4 16 16"></path></svg></button><span class="video-tile-label"><b>Pengalaman Test Drive</b><small>TEST DRIVE HIGHLIGHT</small></span></div></div></section>
<section class="yza-member-section" id="yza-member" data-member-section><div class="show-container yza-member-layout"><div class="yza-member-copy"><span class="section-kicker">YZA EXCLUSIVE MEMBERSHIP</span><h2>Lebih dekat dengan<br><em>pengalaman YZA.</em></h2><p class="yza-member-description">Bergabung dengan YZA Member dan nikmati pengalaman otomotif yang lebih lengkap melalui akses layanan eksklusif, promo spesial, YZA Points, service reminder, serta berbagai keuntungan lainnya.</p>@if($landingMemberState === 'pending_payment')<p class="yza-member-state">Membership Anda belum aktif. Selesaikan pembayaran aktivasi untuk menikmati benefit YZA Member.</p>@elseif($landingMemberState === 'active')<p class="yza-member-state is-active">✓ YZA Member Active</p>@endif<div class="yza-member-price"><span>AKTIVASI MEMBER</span><strong>Rp {{ number_format((float) ($membershipFee ?? 249000), 0, ',', '.') }}</strong><small>Sekali aktivasi • Tidak ada biaya bulanan.</small></div><div class="yza-member-actions"><a class="show-cta" href="{{ $memberPrimaryHref }}">♙&nbsp; {{ $memberPrimaryLabel }}&nbsp; →</a></div></div><div class="yza-member-visual"><div class="yza-member-card" aria-label="Kartu YZA MEMBER"><img src="{{ asset('assets/images/branding/logo-yza.png') }}" alt="YZA Automotive"><span class="yza-member-card-line"></span><small>YZA AUTOMOTIVE</small><strong>YZA <em>MEMBER</em></strong><b>YZA-MBR-000001</b><em>PREMIUM AUTOMOTIVE EXPERIENCE</em><span class="yza-member-chip" aria-hidden="true"></span><span class="yza-member-card-status">MEMBER EXCLUSIVE</span></div><div class="yza-member-benefits" id="yza-member-benefits">@foreach ([['⌑','PROMO EKSKLUSIF','Nikmati promo khusus member pada layanan dan transaksi pilihan.'],['⚙','LAYANAN LEBIH LENGKAP','Gunakan berbagai fasilitas YZA Automotive dalam satu akun.'],['↔','TRADE-IN','Dapatkan akses proses trade-in melalui sistem YZA.'],['✦','YZA POINTS','Kumpulkan Points dari setiap transaksi eligible yang berhasil.'],['▣','TEST DRIVE & MOBIL','Kelola kebutuhan kendaraan dan test drive dengan lebih mudah.'],['⌁','SERVICE REMINDER','Pantau riwayat kendaraan dan pengingat servis Anda.']] as $benefit)<div class="yza-member-benefit"><i aria-hidden="true"><x-landing-icon :name="['tag','wrench','exchange','spark','car','clock'][$loop->index]" /></i><span><b>{{ $benefit[1] }}</b><small>{{ $benefit[2] }}</small></span></div>@endforeach</div></div></div></section>
<section class="show-section testimonials" id="testimoni"><div class="show-container"><div class="show-section-heading"><div><span class="section-kicker">Kata mereka</span><h2>Ulasan pelanggan <em>YZA</em></h2></div></div><div class="testimonial-grid">@foreach ([['testimonial-andi.png','Andi Pratama','Pelayanan di YZA Automotive sangat profesional dan ramah. Prosesnya cepat, transparan, dan mobilnya berkualitas. Sangat puas!'],['testimonial-siti.png','Siti Rahmawati','Saya melakukan trade-in di sini dan prosesnya sangat mudah. Harga yang ditawarkan juga sangat kompetitif. Recommended banget!'],['testimonial-budi.png','Budi Santoso','Servis mobil di YZA selalu memuaskan. Teknisi ahli, hasil rapi, dan selalu diberi penjelasan yang jelas. Mobil jadi terasa seperti baru lagi!']] as $testimonial)<article class="testimonial-card"><div class="testimonial-person"><img class="testimonial-photo" src="{{ asset('assets/images/lower-landing/'.$testimonial[0]) }}" alt="{{ $testimonial[1] }}"><span><b>{{ $testimonial[1] }}</b><small>Pelanggan</small></span></div><div class="stars" aria-label="5 dari 5 bintang">★★★★★</div><div class="testimonial-quote"><span class="quote-open" aria-hidden="true">“</span><p>{{ $testimonial[2] }}</p><span class="quote-close" aria-hidden="true">”</span></div></article>@endforeach</div></div></section>
@include('public.partials.visit')
</main>
@include('public.partials.footer')
<script>document.querySelector('.hero-3d-canvas')?.remove();document.querySelector('.hero-car-stage')?.setAttribute('data-viewer','static-featured-vehicle');</script>
<script>
(() => {
    const navLinks = [...document.querySelectorAll('.show-nav-links a[href^="#"]')];
    const sections = navLinks
        .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
        .filter(Boolean);
    if (!navLinks.length || !sections.length) return;

    const setActive = (activeLink) => { navLinks.forEach(link => { const active = link === activeLink; link.classList.toggle('active', active); if (active) link.setAttribute('aria-current','location'); else link.removeAttribute('aria-current'); }); };

    navLinks.forEach((link) => link.addEventListener('click', () => {
        setActive(link);
        document.getElementById('show-nav-links')?.classList.remove('open');
    }));

    const initialLink = navLinks.find((link) => link.getAttribute('href') === window.location.hash);
    if (initialLink) setActive(initialLink);

    const observer = new IntersectionObserver((entries) => {
        const visible = entries
            .filter((entry) => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
        if (!visible) return;
        const activeLink = navLinks.find((link) => link.getAttribute('href') === `#${visible.target.id}`);
        if (activeLink) setActive(activeLink);
    }, { rootMargin: '-18% 0px -62% 0px', threshold: [0.15, 0.35, 0.6] });

    sections.forEach((section) => observer.observe(section));
})();

document.querySelector('[data-appointment-service]')?.addEventListener('click', function(){ const field=document.getElementById('appointment-layanan'); if(field)field.value=this.dataset.appointmentService; });
document.getElementById('appointment-form')?.addEventListener('submit',function(e){e.preventDefault();if(!this.reportValidity())return;const d=new FormData(this);const message=['Halo YZA Automotive,','','Saya ingin membuat janji temu.','',`Nama: ${d.get('nama')}`,`No. WhatsApp: ${d.get('no_hp')}`,`Kebutuhan: ${d.get('layanan')}`,`Tanggal Janji: ${d.get('tanggal')}`,`Jam: ${d.get('jam')}`,'','Catatan:',d.get('catatan')||'-','','Mohon konfirmasi jadwal saya.','','Terima kasih.'].join('\n');window.location.href=@json(yza_whatsapp_url()).replace(/\/$/,'')+'?text='+encodeURIComponent(message)});
</script>
<script>
(() => {
 const tile=document.querySelector('[data-video-tile]'), player=tile?.querySelector('video'), play=tile?.querySelector('[data-video-open]'), mute=tile?.querySelector('[data-video-mute]');
 if(!player||!play)return;
 const sync=()=>{const playing=!player.paused&&!player.ended;tile.classList.toggle('is-playing',playing);play.textContent=playing?'Ⅱ':'▶';play.setAttribute('aria-label',playing?'Jeda video test drive YZA Automotive':'Putar video test drive YZA Automotive');mute?.setAttribute('aria-label',player.muted?'Aktifkan suara video':'Matikan suara video');mute?.setAttribute('aria-pressed',String(!player.muted));};
 const toggle=()=>{if(player.paused)player.play().catch(sync);else player.pause();};
 play.addEventListener('click',toggle);player.addEventListener('click',toggle);mute?.addEventListener('click',()=>{player.muted=!player.muted;sync();});
 ['play','pause','ended','volumechange'].forEach(event=>player.addEventListener(event,sync));
 if('IntersectionObserver' in window)new IntersectionObserver(entries=>{if(!entries[0].isIntersecting)player.pause();}).observe(tile);
 document.addEventListener('visibilitychange',()=>{if(document.hidden)player.pause();});sync();
})();
</script>
<script>
(() => {
    const card = document.querySelector('.hero-vehicle-info[data-vehicle-id]');
    const heart = card?.querySelector('[data-featured-favorite]');
    if (!card || !heart) return;

    const vehicleId = String(card.dataset.vehicleId || '');
    const storageKey = 'yza_favorite_cars';
    const readFavorites = () => {
        try {
            const value = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(value) ? value.map(String) : [];
        } catch (error) { return []; }
    };
    const writeFavorites = (favorites) => {
        try { localStorage.setItem(storageKey, JSON.stringify(favorites)); } catch (error) {}
    };
    const setFavoriteState = (active) => {
        heart.textContent = active ? '♥' : '♡';
        heart.setAttribute('aria-pressed', String(active));
        heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${card.querySelector('h2')?.textContent.trim() || 'mobil'} ${active ? 'dari' : 'ke'} favorit`);
        heart.classList.toggle('is-favorited', active);
    };

    setFavoriteState(readFavorites().includes(vehicleId));
    heart.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const favorites = readFavorites();
        const active = !favorites.includes(vehicleId);
        writeFavorites(active ? [...favorites, vehicleId] : favorites.filter((id) => id !== vehicleId));
        setFavoriteState(active);
        heart.classList.remove('is-popping');
        window.requestAnimationFrame(() => heart.classList.add('is-popping'));
    });
})();

(() => {
    const storageKey = 'yza_favorite_cars';
    const readFavorites = () => {
        try { const value = JSON.parse(localStorage.getItem(storageKey) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch (error) { return []; }
    };
    const writeFavorites = (favorites) => { try { localStorage.setItem(storageKey, JSON.stringify(favorites)); } catch (error) {} };
    const setState = (heart, active, name) => {
        heart.textContent = active ? '♥' : '♡';
        heart.classList.toggle('is-favorited', active);
        heart.setAttribute('role', 'button');
        heart.setAttribute('tabindex', '0');
        heart.setAttribute('aria-pressed', String(active));
        heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${name} ${active ? 'dari' : 'ke'} favorit`);
    };
    const toggle = (heart, event) => {
        event.preventDefault();
        event.stopPropagation();
        const card = heart.closest('.featured-card');
        const vehicleId = (() => {
            const href = card?.querySelector('.featured-media')?.href || '';
            try { return new URL(href, window.location.href).pathname; } catch (error) { return href; }
        })();
        const name = card?.querySelector('h3')?.textContent.trim() || 'mobil';
        const favorites = readFavorites();
        const active = !favorites.includes(vehicleId);
        writeFavorites(active ? [...favorites, vehicleId] : favorites.filter((favorite) => favorite !== vehicleId));
        setState(heart, active, name);
        heart.classList.remove('is-popping');
        window.requestAnimationFrame(() => heart.classList.add('is-popping'));
    };
    document.querySelectorAll('.featured-card .featured-fav').forEach((heart) => {
        const media = heart.closest('.featured-media');
        if (!media) return;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = heart.className;
        button.innerHTML = heart.innerHTML;
        [...heart.attributes].forEach((attribute) => {
            if (attribute.name !== 'class') button.setAttribute(attribute.name, attribute.value);
        });
        media.after(button);
        heart.remove();
    });
    document.querySelectorAll('.featured-card .featured-fav').forEach((heart) => {
        const card = heart.closest('.featured-card');
        const link = card?.querySelector('.featured-media');
        const vehicleId = (() => {
            const href = link?.href || '';
            try { return new URL(href, window.location.href).pathname; } catch (error) { return href; }
        })();
        const name = card?.querySelector('h3')?.textContent.trim() || 'mobil';
        setState(heart, readFavorites().includes(vehicleId), name);
    });
    document.addEventListener('click', (event) => { const heart = event.target.closest?.('.featured-card .featured-fav'); if (heart) toggle(heart, event); });
    document.addEventListener('keydown', (event) => { const heart = event.target.closest?.('.featured-card .featured-fav'); if (heart && (event.key === 'Enter' || event.key === ' ')) toggle(heart, event); });
})();

(function initSportCarousel(){
 const section=document.querySelector('[data-sport-carousel]')?.closest('.featured-catalog'), viewport=section?.querySelector('.featured-carousel-viewport'), track=section?.querySelector('[data-carousel-track]');if(!viewport||!track)return;
 const previous=section.querySelector('[data-carousel-prev]'),next=section.querySelector('[data-carousel-next]'),counter=section.querySelector('[data-carousel-counter]'),progress=section.querySelector('[data-carousel-progress]'),cards=[...track.children];
 viewport.classList.add('native-carousel');track.classList.add('native-carousel-track');viewport.tabIndex=0;viewport.setAttribute('aria-label','Koleksi kendaraan, geser untuk melihat mobil lainnya');
 const step=()=>cards[0].getBoundingClientRect().width+parseFloat(getComputedStyle(track).gap||0);
 const update=()=>{const max=viewport.scrollWidth-viewport.clientWidth,index=Math.min(cards.length,Math.max(1,Math.round(viewport.scrollLeft/step())+1));if(counter)counter.textContent=String(index).padStart(2,'0')+' / '+String(cards.length).padStart(2,'0');progress?.setAttribute('aria-valuenow',String(index));if(previous)previous.disabled=viewport.scrollLeft<=1;if(next)next.disabled=viewport.scrollLeft>=max-1;};
 const move=direction=>viewport.scrollBy({left:direction*step(),behavior:matchMedia('(prefers-reduced-motion:reduce)').matches?'auto':'smooth'});
 previous?.addEventListener('click',()=>move(-1));next?.addEventListener('click',()=>move(1));viewport.addEventListener('scroll',update,{passive:true});window.addEventListener('resize',update);viewport.addEventListener('keydown',e=>{if(e.target!==viewport)return;if(e.key==='ArrowRight'||e.key==='ArrowLeft'){e.preventDefault();move(e.key==='ArrowRight'?1:-1);}});update();
})();
</script>
<script>
(() => {
    const drawServiceIcons = () => {
        document.querySelectorAll('.yza-service-canvas, .yza-button-canvas').forEach((canvas) => {
            const context = canvas.getContext('2d');
            if (!context) return;
            const ratio = window.devicePixelRatio || 1;
            const size = 72;
            const isButton = canvas.classList.contains('yza-button-canvas');
            canvas.width = size * ratio;
            canvas.height = size * ratio;
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, size, size);
            if (isButton) {
                context.translate(36, 36);
                context.scale(1.6, 1.6);
                context.translate(-36, -36);
            }
            context.lineCap = 'round';
            context.lineJoin = 'round';
            if (!isButton) {
                context.strokeStyle = '#f22631';
                context.lineWidth = 1.8;
                context.beginPath();
                context.moveTo(17, 9); context.lineTo(7, 17); context.lineTo(7, 55); context.lineTo(17, 63);
                context.moveTo(55, 9); context.lineTo(65, 17); context.lineTo(65, 55); context.lineTo(55, 63);
                context.stroke();
            }
            context.strokeStyle = '#fff';
            context.lineWidth = 2.2;
            const icon = canvas.dataset.serviceIcon;
            if (icon === 'car') {
                context.beginPath();
                context.moveTo(22, 41); context.lineTo(24.5, 29); context.lineTo(29, 24); context.lineTo(43, 24); context.lineTo(48, 29); context.lineTo(50, 41); context.lineTo(50, 47); context.lineTo(22, 47); context.closePath();
                context.moveTo(25, 29); context.lineTo(47, 29);
                context.moveTo(22, 38); context.lineTo(50, 38);
                context.moveTo(28, 47); context.lineTo(28, 51); context.moveTo(44, 47); context.lineTo(44, 51);
                context.stroke();
                context.beginPath(); context.arc(28, 41, 1.2, 0, Math.PI * 2); context.arc(44, 41, 1.2, 0, Math.PI * 2); context.stroke();
            } else if (icon === 'wrench') {
                context.beginPath();
                context.moveTo(52, 15);
                context.bezierCurveTo(47, 11, 39, 13, 37, 18);
                context.lineTo(43, 24);
                context.lineTo(25, 42);
                context.lineTo(20, 40);
                context.lineTo(16, 45);
                context.lineTo(22, 51);
                context.lineTo(27, 47);
                context.lineTo(25, 42);
                context.lineTo(43, 24);
                context.lineTo(49, 30);
                context.bezierCurveTo(54, 28, 56, 20, 52, 15);
                context.lineTo(46, 20);
                context.lineTo(41, 15);
                context.closePath();
                context.stroke();
            } else {
                context.beginPath();
                for (let i = 0; i < 24; i += 1) {
                    const angle = -Math.PI / 2 + i * Math.PI / 12;
                    const radius = i % 3 === 1 ? 19 : i % 3 === 2 ? 15 : 18;
                    const x = 36 + Math.cos(angle) * radius;
                    const y = 36 + Math.sin(angle) * radius;
                    if (i === 0) context.moveTo(x, y); else context.lineTo(x, y);
                }
                context.closePath();
                context.moveTo(41, 36); context.arc(36, 36, 5, 0, Math.PI * 2);
                context.stroke();
            }
        });
    };

    const initYzaIntro = () => {
        const intro=document.getElementById('yza-intro'), opener=document.querySelector('[data-intro-replay]');if(!intro||!opener)return;drawServiceIcons();
        const enter=intro.querySelector('.yza-intro-enter');
        const close=()=>{intro.hidden=true;intro.setAttribute('aria-hidden','true');document.body.classList.remove('yza-intro-playing','opening-active');document.documentElement.classList.remove('yza-intro-playing');opener.focus();};
        opener.addEventListener('click',()=>{intro.classList.remove('is-opening');intro.hidden=false;intro.setAttribute('aria-hidden','false');document.body.classList.add('yza-intro-playing');document.documentElement.classList.add('yza-intro-playing');enter?.focus();});enter?.addEventListener('click',close);
        intro.addEventListener('keydown',e=>{if(e.key==='Escape')close();if(e.key==='Tab'){e.preventDefault();enter?.focus();}});
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initYzaIntro, { once: true });
    } else {
        initYzaIntro();
    }
})();
</script>
</body></html>
