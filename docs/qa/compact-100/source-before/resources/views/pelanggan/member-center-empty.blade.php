@extends('layouts.app')

@section('title', 'Member Center · YZA Automotive')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/member-home.css') }}?v={{ filemtime(public_path('assets/css/member-home.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/showroom.css') }}?v=garage-showcase-20261010">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.yza-home *').forEach(function (element) {
    const backgroundImage = getComputedStyle(element).backgroundImage;
    if (!backgroundImage || backgroundImage === 'none' || !backgroundImage.includes('url(')) return;
    const imageLayers = backgroundImage.match(/url\([^)]*\)/g);
    if (imageLayers && imageLayers.length) {
      element.style.backgroundImage = imageLayers.join(', ');
    }
  });

  const accessHeading = Array.from(document.querySelectorAll('.yza-home h2, .yza-home h3'))
    .find(function (element) { return /Layanan Eksklusif YZA/i.test(element.textContent || ''); });
  const accessSection = accessHeading && (accessHeading.closest('section') || accessHeading.parentElement);
  if (accessSection) {
    const serviceImages = [
      '/assets/images/member/service-bengkel.png',
      '/assets/images/member/service-test-drive.png',
      '/assets/images/member/service-car-wash.png',
      '/assets/images/member/service-trade-in.png'
    ];
    const serviceCards = Array.from(accessSection.querySelectorAll('*'))
      .filter(function (element) {
        const rect = element.getBoundingClientRect();
        return rect.width > 250 && rect.height > 120 && rect.height < 420;
      })
      .filter(function (element) {
        return !Array.from(element.children).some(function (child) {
          const childRect = child.getBoundingClientRect();
          return childRect.width > 250 && childRect.height > 120 && childRect.height < 420;
        });
      })
      .slice(0, 4);
    serviceCards.forEach(function (card, index) {
      if (!serviceImages[index]) return;
      card.style.backgroundImage = 'url("' + serviceImages[index] + '")';
      card.style.backgroundSize = 'cover';
      card.style.backgroundPosition = 'center';
      card.style.backgroundRepeat = 'no-repeat';
    });
  }

  document.querySelectorAll('.yza-home a, .yza-home button').forEach(function (element) {
    if (!/lihat\s+semua/i.test(element.textContent || '')) return;

    let container = element;
    for (let i = 0; i < 5 && container; i += 1) {
      const text = (container.textContent || '').replace(/\s+/g, ' ');
      if (/Aktivitas\s*&\s*Jadwal|Pengingat\s*Servis/i.test(text)) {
        element.remove();
        container.classList.add('yza-clear-panel');
        break;
      }
      container = container.parentElement;
    }
  });
});
</script>
@endpush

@section('content')
@php
    $membership = $customer->membership;
    $memberStatus = strtoupper((string) ($membership?->status ?: 'aktif'));
    $memberStatusLabel = $memberStatus === 'ACTIVE' ? 'AKTIF' : str_replace('_', ' ', $memberStatus);
    $services = [
        ['title' => 'Booking Servis', 'copy' => 'Jadwalkan perawatan kendaraan YZA Anda.', 'cta' => 'Booking Servis', 'image' => 'service-bengkel.png', 'route' => 'pelanggan.booking-servis.index', 'locked' => !($hasVerifiedOwnership ?? false)],
        ['title' => 'Test Drive', 'copy' => 'Rasakan langsung performa kendaraan pilihan.', 'cta' => 'Jadwalkan Test Drive', 'image' => 'service-test-drive.png', 'route' => 'pelanggan.test-drive.index', 'locked' => false],
        ['title' => 'Car Wash', 'copy' => 'Jaga kendaraan tetap bersih dan prima.', 'cta' => 'Booking Car Wash', 'image' => 'service-car-wash.png', 'route' => 'pelanggan.booking-cuci.index', 'locked' => !($hasVerifiedOwnership ?? false)],
        ['title' => 'Trade-In', 'copy' => 'Ajukan kendaraan lama untuk upgrade berikutnya.', 'cta' => 'Ajukan Trade-In', 'image' => 'service-trade-in.png', 'route' => 'pelanggan.trade-in.create', 'locked' => !($hasVerifiedOwnership ?? false)],
    ];
    $statusCopy = ['active' => 'Membership aktif', 'pending_payment' => 'Menunggu aktivasi', 'cancelled' => 'Membership dibatalkan'];
@endphp

<div class="yza-home" data-yza-home>
    <section class="yza-home-hero" aria-labelledby="yza-home-title">
        <div class="yza-home-hero-copy">
            <span class="yza-home-kicker">YZA PREMIUM MEMBER</span>
            <h1 id="yza-home-title">Selamat Datang Kembali,<br><em>{{ $customer->nama_lengkap }}</em></h1>
            <p>Nikmati pengalaman otomotif eksklusif, kelola kendaraan Anda, dan akses seluruh layanan premium YZA Automotive.</p>
            <div class="yza-home-member-meta">
                <span class="yza-home-status"><i></i>{{ $memberStatusLabel }} MEMBER</span>
                <span>Member sejak {{ optional($memberSince)->format('d M Y') ?: '-' }}</span>
            </div>
        </div>
        <article class="yza-home-member-card" aria-label="Kartu membership digital">
            <div class="yza-member-card-top">
                <img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive">
                <span>PREMIUM <b>MEMBER</b></span>
            </div>
            <small class="yza-member-card-label">MEMBER ID</small>
            <strong class="yza-member-card-code">{{ $memberId }}</strong>
            <i class="yza-member-card-rule"></i>
            <small class="yza-member-card-label">NAME</small>
            <strong class="yza-member-card-name">{{ strtoupper($customer->nama_lengkap) }}</strong>
            <div class="yza-member-card-meta">
                <small>MEMBER SINCE<strong>{{ optional($memberSince)->format('d M Y') ?: '-' }}</strong></small>
                <small>STATUS<strong>{{ $memberStatusLabel }}</strong></small>
            </div>
        </article>
    </section>

    <section class="yza-home-stats" aria-label="Ringkasan member">
        <a href="{{ route('pelanggan.kendaraan.index') }}"><span class="yza-home-stat-icon">▣</span><small>KENDARAAN YZA</small><strong>{{ ($verifiedVehicles ?? collect())->count() }}</strong><em>Unit terverifikasi</em></a>
        <a href="{{ route('pelanggan.booking.index') }}"><span class="yza-home-stat-icon">□</span><small>BOOKING AKTIF</small><strong>{{ $activeBookingCount }}</strong><em>Perlu perhatian</em></a>
        <a href="{{ route('pelanggan.poin') }}"><span class="yza-home-stat-icon">✦</span><small>YZA POINTS</small><strong>{{ number_format((int) ($pointAccount?->saldo ?? 0), 0, ',', '.') }}</strong><em>Points tersedia</em></a>
        <a href="{{ route('pelanggan.promo.index') }}"><span class="yza-home-stat-icon">◇</span><small>PROMO MEMBER</small><strong>{{ $activePromoCount }}</strong><em>Promo aktif</em></a>
    </section>

    <section class="yza-home-section" aria-labelledby="yza-home-services-title">
        <div class="yza-home-section-head"><div><span class="yza-home-kicker">MEMBER ACCESS</span><h2 id="yza-home-services-title">Layanan Eksklusif YZA</h2><p>Temukan layanan otomotif yang sesuai dengan kebutuhan kendaraan dan perjalanan Anda.</p></div><a href="{{ route('pelanggan.katalog-mobil.index') }}">Jelajahi Koleksi Mobil <b>→</b></a></div>
        <div class="yza-home-service-grid">
            @foreach($services as $service)
                <article class="yza-home-service-card {{ $service['locked'] ? 'is-locked' : '' }}" style="--service-image: url('{{ asset('assets/images/member/'.$service['image']) }}')">
                    <div><span>{{ $service['locked'] ? 'TERSEDIA SETELAH PEMBELIAN YZA' : 'YZA MEMBER' }}</span><h3>{{ $service['title'] }}</h3><p>{{ $service['copy'] }}</p></div>
                    @if($service['locked'])<span class="yza-home-locked">Belum tersedia</span>@else<a href="{{ route($service['route']) }}">{{ $service['cta'] }} <b>→</b></a>@endif
                </article>
            @endforeach
        </div>
    </section>

      @php
        $catalogCars = app(\App\Http\Controllers\Public\MobilController::class)->sportShowcases()->take(10)->values();
      @endphp
      <section class="showroom featured-catalog yza-home-garage-showcase" aria-labelledby="yza-home-garage-title">
        <div class="show-container">
            <div class="show-section-heading">
                <div><span class="section-kicker">MY YZA GARAGE</span><h2 id="yza-home-garage-title">Pilihan Mobil Sport <em>YZA</em></h2><p>Jelajahi koleksi mobil sport eksklusif pilihan YZA Automotive.</p></div>
                <div class="featured-carousel-actions"><a class="show-link" href="{{ route('pelanggan.katalog-mobil.index') }}"><span>Lihat Semua Mobil</span><b aria-hidden="true">→</b></a></div>
            </div>
          <div class="featured-carousel" data-member-sport-carousel data-carousel-count="{{ ($catalogCars ?? collect())->count() }}">
                <div class="featured-carousel-viewport" data-member-carousel-viewport>
                    <div class="featured-carousel-track" data-member-carousel-track>
                        @forelse(($catalogCars ?? collect()) as $car)
                            @php
                                $garageTitle = trim($car->merk.' '.$car->model);
                                $garageUrl = $car->showcase_slug
                                    ? route('pelanggan.katalog-mobil.showcase', ['slug' => $car->showcase_slug, 'source' => 'member-home', 'return_to' => request()->getRequestUri()])
                                    : route('pelanggan.katalog-mobil.show', ['mobil' => $car, 'source' => 'member-home', 'return_to' => request()->getRequestUri()]);
                                $garagePhoto = $car->image ?? optional($car->primaryPhoto())->url();
                                $garageSpec = trim(($car->tahun ?: '-').' · '.($car->tipe ?: 'Performance').' · '.ucfirst($car->transmisi ?: 'Automatic'));
                            @endphp
                            <article class="featured-card is-sport">
                                <a class="featured-media" href="{{ $garageUrl }}"><img src="{{ $garagePhoto }}" alt="{{ $garageTitle }}" loading="lazy" draggable="false"><span class="condition-pill">{{ strtoupper($car->kondisi ?: 'TERSEDIA') }}</span><span class="sport-pill">SPORT HIGHLIGHT</span></a>
                                <button class="featured-fav" type="button" data-member-favorite="{{ $garageUrl }}" data-member-favorite-name="{{ $garageTitle }}" aria-label="Tambahkan {{ $garageTitle }} ke favorit" aria-pressed="false">♡</button>
                                <div class="featured-body"><a class="featured-body-link" href="{{ $garageUrl }}"><h3>{{ $garageTitle }}</h3><small>{{ $garageSpec }}</small></a><div class="featured-bottom"><a class="featured-price-link" href="{{ $garageUrl }}"><strong>{{ (float) $car->harga_jual > 0 ? 'Rp '.number_format((float) $car->harga_jual, 0, ',', '.') : 'Hubungi Kami' }}</strong></a><a class="round-arrow" href="{{ $garageUrl }}" aria-label="Lihat detail {{ $garageTitle }}">→</a></div></div>
                            </article>
                        @empty
                            <div class="featured-card"><div class="featured-body"><h3>Belum ada mobil sport tersedia</h3></div></div>
                        @endforelse
          </div>
          @if(($verifiedVehicles ?? collect())->isNotEmpty())
            <p class="yza-home-garage-owned-note">
              <strong>Kendaraan terverifikasi Anda:</strong>
              {{ $verifiedVehicles->pluck('mobil')->filter()->map(fn ($vehicle) => trim($vehicle->merk.' '.$vehicle->model))->implode(', ') }}
              <a href="{{ route('pelanggan.kendaraan.index') }}">Lihat garasi →</a>
            </p>
          @endif
        </div>
                <div class="featured-carousel-footer"><div class="featured-carousel-progress" data-member-carousel-progress></div><span class="featured-carousel-counter" data-member-carousel-counter>01 / {{ str_pad((string) max(1, ($catalogCars ?? collect())->count()), 2, '0', STR_PAD_LEFT) }}</span></div>
            </div>
        </div>
    </section>

    <section class="yza-home-lower-grid" aria-label="Aktivitas dan pengingat member">
        <article class="yza-home-panel yza-home-image-panel yza-home-activity-panel">
            <div class="yza-home-panel-icon" aria-hidden="true">▣</div>
            <div class="yza-home-section-head"><div><span class="yza-home-kicker">ACTIVITY &amp; SCHEDULE</span><h2>Aktivitas &amp; Jadwal Anda</h2></div><a href="{{ route('pelanggan.booking.index') }}">Lihat Semua <b>→</b></a></div>
            @forelse($upcomingActivities->take(3) as $activity)
                <div class="yza-home-activity"><span>{{ strtoupper($activity['type']) }}</span><div><strong>{{ $activity['vehicle'] ?: $activity['type'] }}</strong><small>{{ $activity['code'] }} · {{ optional($activity['date'])->format('d M Y') }}</small></div><b>{{ $activity['statusLabel'] }}</b></div>
            @empty
                <div class="yza-home-panel-empty"><strong>Belum ada aktivitas mendatang.</strong><span>Booking dan jadwal layanan Anda akan tampil di sini.</span></div>
            @endforelse
            <a class="yza-home-panel-button" href="{{ route('pelanggan.booking.index') }}">Lihat Semua <b>→</b></a>
        </article>
        <article class="yza-home-panel yza-home-image-panel yza-home-reminder-panel">
            <div class="yza-home-panel-icon" aria-hidden="true">⚒</div>
            <div class="yza-home-section-head"><div><span class="yza-home-kicker">SERVICE REMINDER</span><h2>Pengingat Servis</h2></div><a href="{{ route('pelanggan.pengingat-servis') }}">Lihat Semua <b>→</b></a></div>
            @forelse(collect($reminders)->take(3) as $reminder)
                <div class="yza-home-reminder"><strong>{{ $reminder['vehicle']->merk }} {{ $reminder['vehicle']->model }}</strong><small>{{ $reminder['nextDate'] ? 'Servis berikutnya · '.$reminder['nextDate']->format('d M Y') : $reminder['statusLabel'] }}</small></div>
            @empty
                <div class="yza-home-panel-empty"><strong>Belum ada pengingat servis.</strong><span>Pengingat muncul dari riwayat servis kendaraan terverifikasi.</span></div>
            @endforelse
            <a class="yza-home-panel-button" href="{{ route('pelanggan.pengingat-servis') }}">Lihat Semua <b>→</b></a>
        </article>
    </section>

    <section class="yza-home-section yza-home-benefits-section" aria-labelledby="yza-home-benefit-title">
        <div class="yza-home-section-head"><div><span class="yza-home-kicker">EXCLUSIVE MEMBER BENEFITS</span><h2 id="yza-home-benefit-title">Keuntungan Eksklusif Membership</h2><p>Promo aktual dan benefit yang tersedia untuk member YZA.</p></div><a href="{{ route('pelanggan.promo.index') }}">Semua Promo <b>→</b></a></div>
        <div class="yza-home-promo-grid">
            @forelse($promoPreview->take(3) as $promo)
                <a href="{{ route('pelanggan.promo.show', $promo) }}"><span>PROMO MEMBER</span><strong>{{ $promo->nama }}</strong><small>{{ $promo->deskripsi ?: 'Benefit khusus member YZA Automotive.' }}</small><b>Berlaku s/d {{ optional($promo->selesai_at)->format('d M Y') }} →</b></a>
            @empty
                <a href="{{ route('pelanggan.promo.index') }}"><span>PROMO MEMBER</span><strong>Promo Servis Member</strong><small>Benefit katalog servis untuk YZA Member.</small><b>Lihat promo →</b></a>
                <a href="{{ route('pelanggan.promo.index') }}"><span>PROMO MEMBER</span><strong>Promo Car Wash Member</strong><small>Benefit katalog car wash untuk YZA Member.</small><b>Lihat promo →</b></a>
                <a href="{{ route('pelanggan.promo.index') }}"><span>PROMO MEMBER</span><strong>Promo Spare Part Member</strong><small>Benefit katalog spare part untuk YZA Member.</small><b>Lihat promo →</b></a>
            @endforelse
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
(() => {
    const carousel = document.querySelector('[data-member-sport-carousel]');
    if (!carousel) return;
    const viewport = carousel.querySelector('[data-member-carousel-viewport]');
    const track = carousel.querySelector('[data-member-carousel-track]');
    const cards = [...track.querySelectorAll('.featured-card')];
    const counter = carousel.querySelector('[data-member-carousel-counter]');
    const progress = carousel.querySelector('[data-member-carousel-progress]');
    const update = () => {
        const first = cards[0];
        if (!first) return;
        const step = first.getBoundingClientRect().width + parseFloat(getComputedStyle(track).gap || '0');
        const index = Math.min(cards.length, Math.max(1, Math.round(viewport.scrollLeft / step) + 1));
        if (counter) counter.textContent = `${String(index).padStart(2, '0')} / ${String(cards.length).padStart(2, '0')}`;
        if (progress) progress.style.setProperty('--garage-progress', `${(index / Math.max(1, cards.length)) * 100}%`);
    };
    viewport.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update, { passive: true });
    update();
    carousel.querySelectorAll('[data-member-favorite]').forEach((button) => {
        const key = button.dataset.memberFavorite;
        const name = button.dataset.memberFavoriteName || 'mobil';
        const read = () => { try { return JSON.parse(localStorage.getItem('yza_favorite_cars') || '[]'); } catch { return []; } };
        const render = () => { const active = read().includes(key); button.textContent = active ? '♥' : '♡'; button.classList.toggle('is-favorited', active); button.setAttribute('aria-pressed', String(active)); };
        render();
        button.addEventListener('click', (event) => { event.preventDefault(); event.stopPropagation(); const values = read(); const active = !values.includes(key); localStorage.setItem('yza_favorite_cars', JSON.stringify(active ? [...values, key] : values.filter((value) => value !== key))); render(); button.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${name} ${active ? 'dari' : 'ke'} favorit`); });
    });
})();
</script>
@endpush
