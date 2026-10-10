@php
    $isPerformance = ($variant ?? 'default') === 'performance';
    $isMemberCatalog = request()->routeIs('pelanggan.katalog-mobil.*');
    $catalogReturnUrl = request()->getRequestUri() ?: ($isMemberCatalog ? route('pelanggan.katalog-mobil.index') : route('mobil.index'));
    $detailQuery = ['source' => $isMemberCatalog ? 'member-catalog' : 'catalog', 'return_to' => $catalogReturnUrl];
    $detailUrl = $isMemberCatalog && $mobil->id_mobil
        ? route('pelanggan.katalog-mobil.show', array_merge(['mobil' => $mobil], $detailQuery))
        : ($isMemberCatalog && $mobil->showcase_slug
            ? route('pelanggan.katalog-mobil.showcase', ['slug' => $mobil->showcase_slug])
        : ($mobil->showcase_slug
            ? route('mobil.showcase', array_merge(['slug' => $mobil->showcase_slug], $detailQuery))
            : route('mobil.show', array_merge(['mobil' => $mobil], $detailQuery))));
    $photo = $mobil->image ?? optional($mobil->primaryPhoto())->url();
    $identity = strtolower(trim($mobil->merk.' '.$mobil->model.' '.$mobil->tipe));
    if (str_contains($identity, 'fortuner')) {
        $photo = asset('assets/images/vehicles/fortuner/blue/front.png');
    }
    if (str_contains($identity, 'xl7')) {
        $photo = asset('assets/images/vehicles/xl7/maroon/front.png');
    }
    if (str_contains($identity, 'xpander')) {
        $photo = asset('assets/images/vehicles/xpander/gray/front.png');
    }
    if (str_contains($identity, 'hr-v') || str_contains($identity, 'hrv')) {
        $photo = asset('assets/images/vehicles/hrv/white/front.png');
    }
    if (str_contains($identity, 'brio')) {
        $photo = asset('assets/images/vehicles/brio/orange/front.png');
    }
    if (str_contains($identity, 'innova') && str_contains($identity, 'zenix')) {
        $photo = asset('assets/images/vehicles/innova-zenix/silver/front.png');
    }
    if (str_contains($identity, 'avanza')) {
        $photo = asset('assets/images/vehicles/avanza/white/front.png');
    }
    if (str_contains($identity, 'mobilio')) {
        $photo = asset('assets/images/vehicles/mobilio/blue/front.png');
    }
    if (str_contains($identity, 'xenia')) {
        $photo = asset('assets/images/vehicles/xenia/silver/front.png');
    }
    $photo = $photo ?: asset('assets/images/lower-landing/showroom-premium.png');
    $price = (float) $mobil->harga_jual;
    $brand = trim((string) $mobil->merk) ?: 'YZA Automotive';
    $variantLabel = trim((string) ($mobil->tipe ?: ($isPerformance ? 'Performance' : 'Collection')));
    $badgeParts = \App\Support\VehicleBadge::parts($mobil);
    $title = trim($brand.' '.$mobil->model);
    $displayTitle = $title.($variantLabel !== '' ? ' · '.$variantLabel : '');
    $brandLogo = \App\Support\BrandLogo::path($brand, $mobil->model);
    $availability = $mobil->getAttribute('availability') ?? app(\App\Services\VehicleAvailabilityService::class)->for($mobil);
@endphp
<article class="catalog-card {{ $isPerformance ? 'catalog-card-performance' : '' }}">
    <a class="catalog-card-image" href="{{ $detailUrl }}">
        <img src="{{ $photo }}" alt="{{ $title }}" loading="lazy">
        <span class="catalog-card-badges">
            @if($mobil->kondisi === 'baru')<span class="catalog-condition">BARU</span>@endif
            @if($isPerformance)<span class="catalog-sport-badge">SPORT HIGHLIGHT</span>@endif
            @if($mobil->showcase_slug)<span class="vehicle-showcase-badge">KOLEKSI SHOWROOM</span>@endif
            @if($availability['is_limited_edition'] ?? false)<span class="vehicle-limited-badge">LIMITED EDITION</span>@endif
            <span class="vehicle-stock-badge {{ $availability['status_class'] ?? 'is-available' }}">{{ $availability['label'] ?? 'TERSEDIA' }}</span>
        </span>
    </a>
    <button class="catalog-heart" type="button" data-favorite-id="{{ $mobil->id_mobil }}" data-favorite-name="{{ $title }}" aria-label="Tambahkan {{ $title }} ke favorit" aria-pressed="false">♡</button>
    <div class="catalog-card-body">
        <div class="catalog-brand-row">
            <span class="catalog-brand-logo" title="{{ $brand }}">
                <img src="{{ $brandLogo }}" alt="Logo {{ $brand }}" loading="lazy">
            </span>
            <span class="vehicle-tech-badge {{ strlen($badgeParts['value']) > 7 ? 'vehicle-tech-badge--long' : '' }}" aria-label="{{ $badgeParts['label'].' '.$badgeParts['value'] }}"><span class="vehicle-tech-badge__label">{{ strtoupper($badgeParts['label']) }}</span><span class="vehicle-tech-badge__value">{{ strtoupper($badgeParts['value']) }}</span></span>
        </div>
        <a href="{{ $detailUrl }}"><h3>{{ $displayTitle }}</h3></a>
        <p class="catalog-meta">{{ $mobil->tahun ?: '-' }} <i>•</i> {{ $variantLabel ?: 'Collection' }} <i>•</i> {{ ucfirst($mobil->transmisi ?: 'automatic') }}</p>
        <div class="catalog-card-footer">
            <strong class="catalog-price">{{ $price > 0 ? 'Rp '.number_format($price, 0, ',', '.') : 'Hubungi Kami' }}</strong>
            <a class="catalog-detail-button" href="{{ $detailUrl }}" aria-label="Lihat detail {{ $title }}"><span>Lihat Detail</span><b aria-hidden="true">→</b></a>
        </div>
    </div>
</article>
