@extends('layouts.app')

@section('title', $mobil->merk.' '.$mobil->model.' · YZA Member')

@php
    $identity = strtolower($mobil->merk.' '.$mobil->model);
    $catalogFallbacks = [
        'raize' => asset('assets/images/hero/yza-detail-red-bright-generated.png'),
        'civic' => asset('assets/images/catalog/inventory/honda-civic.png'),
        'fortuner' => asset('assets/images/catalog/inventory/toyota-fortuner.png'),
        'xl7' => asset('assets/images/catalog/inventory/suzuki-xl7.png'),
    ];
    $landingVehicleImage = asset('assets/images/hero/yza-hero-reference-v3.png');
    foreach ($catalogFallbacks as $keyword => $catalogImage) {
        if (str_contains($identity, $keyword)) {
            $landingVehicleImage = $catalogImage;
            break;
        }
    }
    $galleryData = $galleryData ?? ['default' => ['label' => $mobil->warna ?: 'Pilihan warna tersedia', 'swatch' => '#20a9c7', 'images' => [['type' => 'front', 'label' => 'Front view', 'src' => $landingVehicleImage]]]];
    $defaultColorKey = $defaultColorKey ?? array_key_first($galleryData);
    $defaultGallery = $galleryData[$defaultColorKey] ?? reset($galleryData);
    $primaryImage = $defaultGallery['images'][0]['src'] ?? $landingVehicleImage;
    $brand = strtoupper((string) ($mobil->merk ?: 'YZA AUTOMOTIVE'));
    $brandLogo = \App\Support\BrandLogo::path($mobil->merk, $mobil->model);
    $modelName = (string) ($mobil->model ?: 'Premium Vehicle');
    $condition = ucfirst((string) ($mobil->kondisi ?: 'Bekas'));
    $price = 'Rp '.number_format((float) $mobil->harga_jual, 0, ',', '.');
    $colorName = $defaultGallery['label'] ?? ($mobil->warna ?: 'Pilihan warna tersedia');
    $availability = $mobil->getAttribute('availability') ?? app(\App\Services\VehicleAvailabilityService::class)->for($mobil);
    $statusMeta = ['label' => ucwords(strtolower($availability['label'] ?? 'Tersedia')), 'class' => $availability['status_class'] ?? 'is-neutral'];
    $isAvailable = (bool) ($availability['can_purchase'] ?? false);
    $canPreorder = (bool) ($availability['can_preorder'] ?? (($availability['stock'] ?? 0) === 0));
    $isLimitedEdition = (bool) ($availability['is_limited_edition'] ?? false);
    $whatsappText = 'Halo YZA Automotive, saya ingin konsultasi mengenai '.$mobil->merk.' '.$mobil->model.'.';
    $uiIcon = static function (string $name): string {
        if ($name === 'whatsapp') {
            return '<img class="detail-inline-icon detail-whatsapp-icon" src="'.asset('assets/images/social/whatsapp.svg').'" alt="" aria-hidden="true">';
        }
        $paths = [
            'engine' => '<path d="M4 10h3l2-3h5l2 3h2a2 2 0 0 1 2 2v4h-3v2H8v-2H5v-4a2 2 0 0 1 2-2Z"/><path d="M13 7V4h3M3 13H1M20 10V7"/>',
            'year' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8 14h2M12 14h2M8 17h2"/>',
            'gear' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1-1.8 3.1-.2-.1a2 2 0 0 1-2.5.2l-.2-.1a2 2 0 0 1-1.1 1.1H10a2 2 0 0 1-1.1-1.1l-.2.1a2 2 0 0 1-2.5-.2l-.2.1-1.8-3.1.1-.1a2 2 0 0 1 .2-2.5l.1-.2a2 2 0 0 1 0-.6l-.1-.2A2 2 0 0 1 4.3 9l-.1-.1L6 5.8l.2.1a2 2 0 0 1 2.5-.2l.2.1A2 2 0 0 1 10 4.7V4h4v.7a2 2 0 0 1 1.1 1.1l.2-.1a2 2 0 0 1 2.5.2l.2-.1 1.8 3.1-.1.1a2 2 0 0 1-.2 2.5l-.1.2a2 2 0 0 1 0 .6l.1.2a2 2 0 0 1-.1 2.5Z"/>',
            'fuel' => '<path d="M6 20V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v15M6 20h11M9 7h5v4H9zM16 7h2l2 3v7a1 1 0 0 1-2 0v-5h-2"/>',
            'color' => '<path d="M12 4a8 8 0 1 0 0 16h1.2a1.8 1.8 0 0 0 1.2-3.1 1.8 1.8 0 0 1 1.2-3.1H18a2 2 0 0 0 2-2A8 8 0 0 0 12 4Z"/><circle cx="7.5" cy="11" r=".8"/><circle cx="10" cy="7.5" r=".8"/><circle cx="14" cy="7.5" r=".8"/>',
            'condition' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="m8 13 2.5 2.5L16 10M8 8h8"/>',
            'status' => '<path d="m12 3 7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9 12 2 2 4-4"/>',
        ];
        return '<svg class="detail-inline-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'.($paths[$name] ?? '').'</svg>';
    };
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/mobil-detail.css') }}?v=detail-wide-20260404">
    <style>
        .member-main-content:has(.member-detail-parity) { width: 100%; max-width: none!important; padding: 0!important; background: #08090b; }
        .member-main-content:has(.member-detail-parity)> .member-detail-parity { width: 100%; max-width: none!important; margin: 0; }
        .member-detail-parity { min-height: 100%; background: linear-gradient(115deg, #08090b 0%, #12070a 100%); }
        .member-detail-parity .detail-page { width: 100%; max-width: none; margin: 0; padding: 34px clamp(28px, 3.2vw, 58px) 76px; }
        .member-detail-parity .detail-showcase { grid-template-columns: minmax(520px, .9fr) minmax(0, 1.1fr); gap: clamp(24px, 2.2vw, 38px); }
        .member-detail-parity .detail-gallery-frame { border: 2px solid rgba(239,31,47,.72); border-radius: 22px; box-shadow: 0 0 0 1px rgba(255,255,255,.12), 0 24px 70px rgba(0,0,0,.48), 0 0 30px rgba(239,31,47,.16); }
        .member-detail-parity .detail-gallery-frame::after { content: ''; position: absolute; z-index: 3; inset: 10px; border: 1px solid rgba(255,255,255,.24); border-radius: 15px; pointer-events: none; }
        .member-detail-parity .detail-action-row { display: block; }
        .member-detail-parity .member-purchase-status { display: flex; grid-column: 1 / -1; width: 100%; min-width: 0; cursor: pointer; opacity: 1; }
        .member-detail-parity .member-purchase-status .detail-button-text { min-width: 0; white-space: nowrap; text-align: center; }
        .member-detail-parity .member-purchase-status:hover { transform: none; }
        .member-detail-parity .detail-button-primary,
        .member-detail-parity .member-purchase-status { justify-content: center; }
        .member-detail-parity .detail-actions-reference .detail-button-primary { display: grid; grid-template-columns: 40px minmax(0,1fr) 40px; align-items: center; }
        .member-detail-parity .detail-actions-reference .detail-button-ghost { display: grid; grid-template-columns: 32px minmax(0,1fr) 28px; align-items: center; }
        .member-detail-parity .detail-actions-reference .detail-button-ghost::before { content: ''; display: block; }
        .member-detail-parity .detail-button-primary > span:last-child,
        .member-detail-parity .member-purchase-status > span:last-child { position: static; }
        .member-detail-parity .detail-button-primary .detail-button-text,
        .member-detail-parity .member-purchase-status .detail-button-text { min-width: 0; white-space: nowrap; text-align: center; }
        .member-detail-parity .detail-status-badge,
        .member-detail-parity .detail-spec-status .detail-status-tag { border-color: rgba(72,210,139,.4); color: #c6f9d5; }
        .member-detail-parity .detail-status-badge.is-preorder,
        .member-detail-parity .detail-spec-status .detail-status-tag.is-preorder { border-color: rgba(246,185,72,.55); color: #ffe0a0; background: rgba(246,185,72,.1); }
        .member-detail-parity .detail-status-badge.is-empty,
        .member-detail-parity .detail-spec-status .detail-status-tag.is-empty { border-color: rgba(255,93,101,.5); color: #ffb5b9; background: rgba(239,31,47,.1); }
        .member-detail-parity .detail-status-badge.is-unverified,
        .member-detail-parity .detail-spec-status .detail-status-tag.is-unverified { border-color: rgba(217,180,90,.65); color: #ffe0a0; background: rgba(217,180,90,.1); }
        .member-detail-parity .detail-status-badge.is-neutral,
        .member-detail-parity .detail-spec-status .detail-status-tag.is-neutral { border-color: rgba(255,255,255,.3); color: #dedede; background: rgba(255,255,255,.06); }
        .member-detail-parity .detail-spec-status .detail-status-tag { display: inline-flex; align-items: center; justify-content: center; justify-self: end; min-width: 92px; padding: 7px 12px; border: 1px solid rgba(72,210,139,.4); border-radius: 999px; font-size: .75rem; font-weight: 800; }
        body.preorder-modal-open { overflow: hidden; }
        .preorder-modal[hidden] { display: none; }
        .preorder-modal { position: fixed; z-index: 1000; inset: 0; display: grid; place-items: center; padding: 24px; }
        .preorder-modal-backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.76); backdrop-filter: blur(8px); animation: preorder-fade .2s ease-out; }
        .preorder-dialog { position: relative; width: min(760px, 100%); max-height: min(720px, calc(100vh - 48px)); overflow: auto; color: #fff; background: linear-gradient(145deg,#181014,#090a0c 62%); border: 1px solid rgba(239,31,47,.78); border-radius: 22px; box-shadow: 0 30px 100px rgba(0,0,0,.7), 0 0 35px rgba(239,31,47,.18); padding: 28px; animation: preorder-rise .26s cubic-bezier(.2,.8,.2,1); }
        .preorder-modal-close { position: absolute; top: 16px; right: 16px; width: 38px; height: 38px; border: 1px solid rgba(255,255,255,.28); border-radius: 50%; background: rgba(255,255,255,.06); color: #fff; font-size: 24px; line-height: 1; cursor: pointer; }
        .preorder-modal-brand { display: flex; align-items: center; gap: 10px; color: #fff; font-size: 11px; font-weight: 900; letter-spacing: .14em; }
        .preorder-modal-brand img { width: 45px; height: 35px; object-fit: contain; }
        .preorder-modal-content { display: grid; grid-template-columns: minmax(190px,.7fr) minmax(0,1.3fr); gap: 24px; margin-top: 24px; align-items: center; }
        .preorder-modal-visual { overflow: hidden; min-height: 220px; border: 1px solid rgba(255,255,255,.16); border-radius: 16px; background: #0b0c0e; }
        .preorder-modal-visual img { display: block; width: 100%; height: 100%; min-height: 220px; object-fit: cover; }
        .preorder-modal-copy .detail-kicker { color: #ff2634; font-size: 10px; font-weight: 900; letter-spacing: .18em; }
        .preorder-modal-copy h2 { margin: 8px 0 4px; font-size: clamp(26px,4vw,40px); letter-spacing: -.04em; }
        .preorder-modal-copy h3 { margin: 0 0 14px; color: #f3f3f3; font-size: 18px; }
        .preorder-modal-copy p { color: #c4c8cf; font-size: 13px; line-height: 1.65; }
        .preorder-modal-meta { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 18px 0; padding: 12px 14px; border: 1px solid rgba(255,93,101,.45); border-radius: 12px; background: rgba(239,31,47,.08); }
        .preorder-modal-meta span { color: #ffb5b9; font-size: 11px; font-weight: 900; letter-spacing: .1em; }
        .preorder-modal-meta strong { color: #ff2634; font-size: 18px; }
        .preorder-modal-submit { appearance: none; display: flex; justify-content: space-between; align-items: center; width: 100%; min-height: 50px; padding: 0 17px; border: 0; border-radius: 10px; background: #ef1f2f; color: #fff; font-weight: 900; cursor: pointer; transition: transform .2s, background .2s; }
        .preorder-modal-submit:hover { background: #ff3040; transform: translateY(-2px); }
        .preorder-modal-submit:disabled { cursor: wait; opacity: .7; transform: none; }
        .preorder-modal-secondary { display: flex; flex-wrap: wrap; gap: 14px; margin-top: 14px; }
        .preorder-modal-secondary a { color: #dfe2e7; font-size: 11px; font-weight: 800; text-decoration: none; }
        .preorder-modal-secondary a:hover { color: #ff5260; }
        .preorder-modal-feedback { min-height: 20px; margin: 9px 0 3px; font-size: 12px; line-height: 1.4; }
        .preorder-modal-feedback.is-pending { color: #f3cc7a; }.preorder-modal-feedback.is-success { color: #72e1a0; }.preorder-modal-feedback.is-error { color: #ff9aa2; }
        @keyframes preorder-fade { from { opacity: 0; } to { opacity: 1; } } @keyframes preorder-rise { from { opacity: 0; transform: translateY(16px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @media (max-width: 1000px) { .member-detail-parity .detail-showcase { grid-template-columns: 1fr; } }
        @media (max-width: 700px) { .member-detail-parity .detail-page { padding: 24px 16px 52px; }.preorder-modal { padding: 12px; }.preorder-dialog { max-height: calc(100vh - 24px); padding: 22px 18px; border-radius: 18px; }.preorder-modal-content { grid-template-columns: 1fr; gap: 16px; }.preorder-modal-visual,.preorder-modal-visual img { min-height: 150px; max-height: 190px; }.preorder-modal-copy h2 { font-size: 29px; }.preorder-modal-meta strong { font-size: 15px; } }
    </style>
@endpush

@section('content')
<div class="yza-detail-page member-detail-parity">
    <main class="detail-page">
        <section class="detail-heading">
            <x-vehicle-back-button :url="route('pelanggan.katalog-mobil.index')" label="Kembali ke Katalog Member" />
            <div class="detail-heading-grid">
                <div class="detail-heading-copy"><p class="detail-kicker">YZA AUTOMOTIVE · PREMIUM COLLECTION</p><h1>{{ $mobil->merk }} {{ $modelName }}</h1><p class="detail-subtitle">Unit pilihan dengan pemeriksaan dealer dan pengalaman berkendara yang lebih percaya diri.</p></div>
                <div class="detail-heading-price"><span>Harga terbaik</span><strong>{{ $price }}</strong></div>
            </div>
        </section>
        <section class="detail-showcase" aria-label="Detail kendaraan">
            <div class="detail-info-column">
                <div class="detail-panel detail-summary-panel" data-detail-vehicle-id="{{ $mobil->id_mobil }}">
                    <div class="detail-brand-header"><div class="detail-brand-identity"><span class="detail-brand-logo"><img src="{{ $brandLogo }}" alt="Logo {{ $brand }}"></span><div><span class="detail-label">Brand</span><strong>{{ $brand }}</strong><small>AUTOMOTIVE</small></div><button class="detail-favorite" type="button" data-detail-favorite aria-label="Tambahkan ke favorit" aria-pressed="false">♡</button></div><div class="detail-statuses"><span class="detail-condition"><i></i>{{ $condition }}</span>@if($isLimitedEdition)<span class="detail-limited-badge">LIMITED EDITION</span>@endif<span class="detail-available-badge detail-status-badge {{ $statusMeta['class'] }}"><i></i>{{ $statusMeta['label'] }}</span>@if($availability['detail'] ?? false)<small class="detail-stock-copy">{{ $availability['detail'] }}</small>@endif</div></div>
                    <p class="detail-description">{{ $mobil->deskripsi ?: 'Kendaraan terawat dengan karakter berkendara yang nyaman untuk aktivitas harian maupun perjalanan akhir pekan.' }}</p>
                <div class="detail-actions detail-actions-reference"><a class="detail-button detail-button-primary" href="{{ yza_whatsapp_url($whatsappText) }}" target="_blank" rel="noopener">{!! $uiIcon('whatsapp') !!}<span class="detail-button-text">Tanya via WhatsApp</span><span>→</span></a><div class="detail-action-row">@if($isAvailable)<a class="detail-button detail-button-ghost" href="{{ route('pelanggan.pesanan-mobil.create', ['mobil' => $mobil->id_mobil]) }}"><span class="detail-button-text">Beli Sekarang</span><span>→</span></a>@elseif($canPreorder)<button class="detail-button detail-button-ghost member-purchase-status" type="button" data-preorder-trigger aria-haspopup="dialog" aria-controls="preorder-modal"><span class="detail-button-text">Pre-order Sekarang</span><span>→</span></button>@else<span class="detail-button detail-button-ghost member-purchase-status" aria-disabled="true"><span class="detail-button-text">{{ $statusMeta['label'] }}</span><span>→</span></span>@endif</div></div>
                </div>
                <div class="detail-panel detail-spec-panel"><div class="detail-section-head"><div><p class="detail-kicker">SPESIFIKASI KENDARAAN</p><h2>Detail kendaraan</h2></div><span class="detail-code">{{ $mobil->kode_mobil ?: 'YZA-UNIT' }}</span></div><div class="detail-spec-grid detail-spec-grid-reference"><div><span class="detail-spec-icon">{!! $uiIcon('engine') !!}</span><span>Tipe</span><strong>{{ $mobil->tipe ?: '-' }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('year') !!}</span><span>Tahun</span><strong>{{ $mobil->tahun ?: '-' }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('gear') !!}</span><span>Transmisi</span><strong>{{ strtoupper($mobil->transmisi ?: '-') }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('fuel') !!}</span><span>Bahan bakar</span><strong>{{ ucfirst($mobil->bahan_bakar ?: '-') }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('color') !!}</span><span>Warna</span><strong id="detail-spec-color">{{ $colorName }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('condition') !!}</span><span>Kondisi</span><strong>{{ $condition }}</strong></div><div class="detail-spec-status"><span class="detail-spec-icon">{!! $uiIcon('status') !!}</span><span>Status</span><strong class="detail-status-tag {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</strong></div></div></div>
                <div class="detail-panel detail-color-panel"><div class="detail-section-head"><div><p class="detail-kicker">PILIHAN WARNA</p><h2>Personalisasi</h2></div><span id="selected-color">{{ $colorName }}</span></div><div class="detail-swatches" role="group" aria-label="Pilihan warna kendaraan">@foreach($galleryData as $colorKey => $color)<button class="detail-swatch {{ $colorKey === $defaultColorKey ? 'is-active' : '' }}" type="button" data-color-key="{{ $colorKey }}" data-color="{{ $color['label'] }}" style="--swatch:{{ $color['swatch'] }}" aria-label="Pilih warna {{ $color['label'] }}" aria-pressed="{{ $colorKey === $defaultColorKey ? 'true' : 'false' }}"></button>@endforeach</div></div>
            </div>
            <div class="detail-gallery-column"><div class="detail-gallery-frame"><div class="detail-gallery-topline"><span class="detail-gallery-counter" id="detail-gallery-counter">01 / {{ str_pad((string) count($defaultGallery['images'] ?? []), 2, '0', STR_PAD_LEFT) }}</span><span class="detail-gallery-mark"><b>YZA</b> SHOWROOM VIEW</span></div><button class="detail-gallery-arrow detail-gallery-arrow-prev" type="button" aria-label="Foto sebelumnya"><span aria-hidden="true">←</span></button><img id="detail-main-image" src="{{ $primaryImage }}" alt="{{ $mobil->merk }} {{ $modelName }}" class="detail-main-image"><button class="detail-gallery-arrow detail-gallery-arrow-next" type="button" aria-label="Foto berikutnya"><span aria-hidden="true">→</span></button><div class="detail-image-shade"></div><div class="detail-gallery-caption"><span>{{ $mobil->merk }} {{ $modelName }}</span><small id="detail-gallery-caption">{{ ($defaultGallery['images'][0]['label'] ?? 'Front view') }} · YZA Automotive</small></div></div><div id="detail-thumbnails" class="detail-thumbnails" role="list" aria-label="Galeri foto kendaraan">@foreach($defaultGallery['images'] ?? [] as $index => $image)<button class="detail-thumbnail {{ $index === 0 ? 'is-active' : '' }}" type="button" data-index="{{ $index }}" role="listitem" aria-label="Lihat {{ $image['label'] }}"><img src="{{ $image['src'] }}" alt="{{ $image['label'] }} {{ $mobil->model }}"><span>0{{ $index + 1 }}</span></button>@endforeach</div><div class="detail-gallery-note"><span>✦</span> Pilih warna dan sudut kendaraan untuk melihat detail showroom.</div></div>
        </section>
    </main>
</div>
@if($canPreorder)
<div class="preorder-modal" id="preorder-modal" data-preorder-modal hidden aria-hidden="true">
    <div class="preorder-modal-backdrop" data-preorder-close></div>
    <section class="preorder-dialog" role="dialog" aria-modal="true" aria-labelledby="preorder-title" aria-describedby="preorder-description" tabindex="-1">
        <button class="preorder-modal-close" type="button" data-preorder-close aria-label="Tutup pre-order">×</button>
        <div class="preorder-modal-brand"><img src="{{ asset('images/branding/logo-yza.png') }}" alt="YZA Automotive"><span>YZA AUTOMOTIVE</span></div>
        <div class="preorder-modal-content"><div class="preorder-modal-visual"><img src="{{ $primaryImage }}" alt="{{ $mobil->merk }} {{ $modelName }}"></div><div class="preorder-modal-copy"><span class="detail-kicker">PREMIUM MEMBER BENEFIT</span><h2 id="preorder-title">PRE-ORDER EKSKLUSIF</h2><h3>{{ $mobil->merk }} {{ $modelName }}</h3><p id="preorder-description">Dapatkan notifikasi saat stok kendaraan ini kembali tersedia. Pre-order tidak memerlukan pembayaran dan tidak menjamin reservasi unit.</p><div class="preorder-modal-meta"><span>STOK HABIS</span><strong>{{ $price }}</strong></div><div class="preorder-modal-feedback" data-preorder-feedback role="status" aria-live="polite"></div><form data-preorder-form method="post" action="{{ route('pelanggan.preorder.store') }}">@csrf @if($mobil->showcase_slug)<input type="hidden" name="slug" value="{{ $mobil->showcase_slug }}">@else<input type="hidden" name="mobil" value="{{ $mobil->id_mobil }}">@endif<button class="preorder-modal-submit" type="submit" data-preorder-submit>DAFTAR PRE-ORDER <span>→</span></button></form><div class="preorder-modal-secondary"><a href="{{ yza_whatsapp_url($whatsappText) }}" target="_blank" rel="noopener">KONSULTASI VIA WHATSAPP</a><a href="{{ route('pelanggan.preorder.index') }}" data-preorder-list-link hidden>LIHAT PRE-ORDER SAYA</a></div></div></div>
    </section>
</div>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const panel = document.querySelector('[data-detail-vehicle-id]');
    const heart = panel?.querySelector('[data-detail-favorite]');
    if (panel && heart) {
        const key = 'yza_favorite_cars';
        const ids = [String(panel.dataset.detailVehicleId || ''), window.location.pathname].filter(Boolean);
        const read = () => { try { const value = JSON.parse(localStorage.getItem(key) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch { return []; } };
        const write = value => { try { localStorage.setItem(key, JSON.stringify(value)); } catch {} };
        const render = active => { heart.textContent = active ? '♥' : '♡'; heart.classList.toggle('is-favorited', active); heart.setAttribute('aria-pressed', String(active)); };
        render(read().some(item => ids.includes(item)));
        heart.addEventListener('click', () => { const favorites = read(); const active = !favorites.some(item => ids.includes(item)); write(active ? [...new Set([...favorites, ...ids])] : favorites.filter(item => !ids.includes(item))); render(active); });
    }
    const galleryData = @json($galleryData);
    const fallbackImage = @json($landingVehicleImage);
    const state = { color: @json($defaultColorKey), index: 0 };
    const mainImage = document.getElementById('detail-main-image');
    const thumbnailsRoot = document.getElementById('detail-thumbnails');
    const colorLabel = document.getElementById('selected-color');
    const specColor = document.getElementById('detail-spec-color');
    const counter = document.getElementById('detail-gallery-counter');
    const caption = document.getElementById('detail-gallery-caption');
    const swatches = [...document.querySelectorAll('.detail-swatch')];
    const renderGallery = () => {
        const gallery = galleryData[state.color] || galleryData.default; const images = gallery?.images || []; if (!images.length) return;
        state.index = Math.min(state.index, images.length - 1); const image = images[state.index]; mainImage.src = image.src || fallbackImage; mainImage.alt = `${image.label} {{ $mobil->model }}`; counter.textContent = `${String(state.index + 1).padStart(2, '0')} / ${String(images.length).padStart(2, '0')}`; caption.textContent = `${image.label} · YZA Automotive`;
        thumbnailsRoot.innerHTML = images.map((item, index) => `<button class="detail-thumbnail ${index === state.index ? 'is-active' : ''}" type="button" data-index="${index}" role="listitem" aria-label="Lihat ${item.label}"><img src="${item.src}" alt="${item.label} {{ $mobil->model }}"><span>0${index + 1}</span></button>`).join('');
    };
    const changeColor = swatch => { state.color = swatch.dataset.colorKey; state.index = 0; swatches.forEach(item => { const active = item === swatch; item.classList.toggle('is-active', active); item.setAttribute('aria-pressed', active ? 'true' : 'false'); }); colorLabel.textContent = swatch.dataset.color; if (specColor) specColor.textContent = swatch.dataset.color; renderGallery(); };
    swatches.forEach(swatch => swatch.addEventListener('click', () => changeColor(swatch)));
    thumbnailsRoot.addEventListener('click', event => { const thumbnail = event.target.closest('.detail-thumbnail'); if (!thumbnail) return; state.index = Number(thumbnail.dataset.index); renderGallery(); });
    document.querySelector('.detail-gallery-arrow-prev')?.addEventListener('click', () => { const count = (galleryData[state.color] || galleryData.default).images.length; state.index = (state.index - 1 + count) % count; renderGallery(); });
    document.querySelector('.detail-gallery-arrow-next')?.addEventListener('click', () => { const count = (galleryData[state.color] || galleryData.default).images.length; state.index = (state.index + 1) % count; renderGallery(); });
    mainImage.addEventListener('error', () => { if (mainImage.src !== fallbackImage) mainImage.src = fallbackImage; });
    renderGallery();

    const preorderModal = document.querySelector('[data-preorder-modal]');
    const preorderTrigger = document.querySelector('[data-preorder-trigger]');
    const preorderDialog = preorderModal?.querySelector('.preorder-dialog');
    const preorderForm = preorderModal?.querySelector('[data-preorder-form]');
    const preorderSubmit = preorderModal?.querySelector('[data-preorder-submit]');
    const preorderFeedback = preorderModal?.querySelector('[data-preorder-feedback]');
    const preorderListLink = preorderModal?.querySelector('[data-preorder-list-link]');
    let preorderSubmitting = false;
    const closePreorder = () => { if (!preorderModal || preorderSubmitting) return; preorderModal.hidden = true; preorderModal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('preorder-modal-open'); preorderTrigger?.focus(); };
    const openPreorder = () => { if (!preorderModal) return; preorderModal.hidden = false; preorderModal.setAttribute('aria-hidden', 'false'); document.body.classList.add('preorder-modal-open'); preorderFeedback.textContent = ''; preorderDialog?.focus(); };
    preorderTrigger?.addEventListener('click', openPreorder);
    preorderModal?.querySelectorAll('[data-preorder-close]').forEach(item => item.addEventListener('click', closePreorder));
    document.addEventListener('keydown', event => { if (!preorderModal || preorderModal.hidden) return; if (event.key === 'Escape') { closePreorder(); return; } if (event.key !== 'Tab') return; const focusables = [...preorderDialog.querySelectorAll('button:not([hidden]), a:not([hidden]), input:not([hidden])')].filter(item => !item.disabled); if (!focusables.length) return; const first = focusables[0]; const last = focusables[focusables.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } });
    preorderForm?.addEventListener('submit', async event => { event.preventDefault(); if (preorderSubmitting) return; preorderSubmitting = true; preorderSubmit.disabled = true; preorderSubmit.classList.add('is-loading'); preorderSubmit.querySelector('span').textContent = '…'; preorderFeedback.textContent = 'Menyimpan pre-order…'; preorderFeedback.className = 'preorder-modal-feedback is-pending'; try { const response = await fetch(preorderForm.action, { method: 'POST', body: new FormData(preorderForm), headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }); const data = await response.json(); if (!response.ok || !data.ok) throw new Error(data.message || 'Pre-order belum dapat disimpan.'); preorderFeedback.textContent = data.message; preorderFeedback.className = 'preorder-modal-feedback is-success'; preorderSubmit.hidden = true; preorderListLink.hidden = false; } catch (error) { preorderFeedback.textContent = error.message || 'Terjadi kesalahan. Silakan coba lagi.'; preorderFeedback.className = 'preorder-modal-feedback is-error'; preorderSubmitting = false; preorderSubmit.disabled = false; preorderSubmit.classList.remove('is-loading'); preorderSubmit.querySelector('span').textContent = '→'; } });
})();
</script>
@endpush
