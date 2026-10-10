@php
    // Presentation gallery is resolved by VehicleGalleryService; the view only renders it.
    $identity = strtolower($mobil->merk.' '.$mobil->model);
    $catalogFallbacks = [
        'raize' => asset('assets/images/hero/yza-detail-red-bright-generated.png'),
        'civic' => asset('assets/images/catalog/inventory/honda-civic.png'),
        'fortuner' => asset('assets/images/catalog/inventory/toyota-fortuner.png'),
        'xl7' => asset('assets/images/catalog/inventory/suzuki-xl7.png'),
    ];
    $landingVehicleImage = $showcaseImage ?? asset('assets/images/hero/yza-hero-reference-v3.png');
    foreach ($catalogFallbacks as $keyword => $catalogImage) {
        if (str_contains($identity, $keyword)) {
            $landingVehicleImage = $catalogImage;
            break;
        }
    }
    $galleryData = $galleryData ?? ['default' => ['label' => $mobil->warna ?: 'Pilihan warna tersedia', 'swatch' => '#20a9c7', 'images' => [['type' => 'front', 'label' => 'Front view', 'src' => $landingVehicleImage]]]];
    $defaultColorKey = $defaultColorKey ?? array_key_first($galleryData);
    $defaultGallery = $galleryData[$defaultColorKey];
    $primaryImage = $defaultGallery['images'][0]['src'];
    $brand = strtoupper((string) ($mobil->merk ?: 'YZA AUTOMOTIVE'));
    $brandLogo = \App\Support\BrandLogo::path($mobil->merk, $mobil->model);
    $uiIcon = static function (string $name): string {
        if ($name === 'whatsapp') {
            return '<img class="detail-inline-icon detail-whatsapp-icon" src="'.asset('assets/images/social/whatsapp.svg').'" alt="" aria-hidden="true">';
        }
        $paths = [
            'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8 14h2M12 14h2M8 17h2"/>',
            'trade' => '<path d="M4 8h15M15 4l4 4-4 4M20 16H5M9 12l-4 4 4 4"/>',
            'engine' => '<path d="M4 10h3l2-3h5l2 3h2a2 2 0 0 1 2 2v4h-3v2H8v-2H5v-4a2 2 0 0 1 2-2Z"/><path d="M13 7V4h3M3 13H1M20 10V7"/>',
            'year' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16M8 14h2M12 14h2M8 17h2"/>',
            'gear' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1-1.8 3.1-.2-.1a2 2 0 0 1-2.5.2l-.2-.1a2 2 0 0 1-1.1 1.1H10a2 2 0 0 1-1.1-1.1l-.2.1a2 2 0 0 1-2.5-.2l-.2.1-1.8-3.1.1-.1a2 2 0 0 1 .2-2.5l.1-.2a2 2 0 0 1 0-.6l-.1-.2A2 2 0 0 1 4.3 9l-.1-.1L6 5.8l.2.1a2 2 0 0 1 2.5-.2l.2.1A2 2 0 0 1 10 4.7V4h4v.7a2 2 0 0 1 1.1 1.1l.2-.1a2 2 0 0 1 2.5.2l.2-.1 1.8 3.1-.1.1a2 2 0 0 1-.2 2.5l-.1.2a2 2 0 0 1 0 .6l.1.2a2 2 0 0 1-.1 2.5Z"/>',
            'fuel' => '<path d="M6 20V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v15M6 20h11M9 7h5v4H9zM16 7h2l2 3v7a1 1 0 0 1-2 0v-5h-2"/>',
            'color' => '<path d="M12 4a8 8 0 1 0 0 16h1.2a1.8 1.8 0 0 0 1.2-3.1 1.8 1.8 0 0 1 1.2-3.1H18a2 2 0 0 0 2-2A8 8 0 0 0 12 4Z"/><circle cx="7.5" cy="11" r=".8"/><circle cx="10" cy="7.5" r=".8"/><circle cx="14" cy="7.5" r=".8"/>',
            'condition' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="m8 13 2.5 2.5L16 10M8 8h8"/>',
            'status' => '<path d="m12 3 7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3Z"/><path d="m9 12 2 2 4-4"/>',
        ];
        $path = $paths[$name] ?? '';
        $iconClass = $name === 'whatsapp' ? 'detail-inline-icon detail-whatsapp-icon' : 'detail-inline-icon';
        return '<svg class="'.$iconClass.'" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'.$path.'</svg>';
    };
    $modelName = (string) ($mobil->model ?: 'Premium Vehicle');
    $condition = ucfirst((string) ($mobil->kondisi ?: 'Bekas'));
    $price = 'Rp '.number_format((float) $mobil->harga_jual, 0, ',', '.');
    $whatsappText = 'Halo YZA Automotive, saya ingin konsultasi mengenai '.$mobil->merk.' '.$mobil->model.'.';
    $colorName = $defaultGallery['label'];
    $tradeInEntryUrl = route('trade-in.entry', array_filter([
        'mobil' => $mobil->id_mobil,
        'slug' => request()->route('slug'),
    ], fn ($value) => filled($value)));
    $isActiveTradeInMember = auth()->check()
        && auth()->user()->roleName() === 'pelanggan'
        && auth()->user()->pelanggan?->membership?->isActive();
    $tradeInMembershipUrl = route('trade-in.membership-entry', array_filter([
        'mobil' => $mobil->id_mobil,
        'slug' => request()->route('slug'),
    ], fn ($value) => filled($value)));
    $tradeInCtaUrl = $isActiveTradeInMember ? $tradeInEntryUrl : $tradeInMembershipUrl;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Detail {{ $mobil->merk }} {{ $mobil->model }} dari YZA Automotive.">
    <title>{{ $mobil->merk }} {{ $mobil->model }} · YZA Automotive</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/public-navbar.css') }}?v=public-navbar-20261004">
    <link rel="stylesheet" href="{{ asset('assets/css/mobil-detail.css') }}?v=detail-wide-20261004">
</head>
<body class="yza-detail-page">
    <div class="detail-orb detail-orb-one"></div><div class="detail-orb detail-orb-two"></div>
    <x-public-navbar />
    <main class="detail-page">
        <section class="detail-heading">
            <x-vehicle-back-button :url="$backUrl ?? route('mobil.index')" :label="$backLabel ?? 'Kembali ke Semua Mobil'" />
            <div class="detail-heading-grid">
                <div class="detail-heading-copy"><p class="detail-kicker">YZA AUTOMOTIVE · PREMIUM COLLECTION</p><h1>{{ $mobil->merk }} {{ $modelName }}</h1><p class="detail-subtitle">Unit pilihan dengan pemeriksaan dealer dan pengalaman berkendara yang lebih percaya diri.</p></div>
                <div class="detail-heading-price"><span>Harga terbaik</span><strong>{{ $price }}</strong></div>
            </div>
        </section>
        <section class="detail-showcase" aria-label="Detail kendaraan">
            <div class="detail-info-column">
                <div class="detail-panel detail-summary-panel" data-detail-vehicle-id="{{ $mobil->id_mobil }}">
                    <div class="detail-brand-header">
                        <div class="detail-brand-identity"><span class="detail-brand-logo"><img src="{{ $brandLogo }}" alt="Logo {{ $brand }}"></span><div><span class="detail-label">Brand</span><strong>{{ $brand }}</strong><small>AUTOMOTIVE</small></div><button class="detail-favorite" type="button" data-detail-favorite aria-label="Tambahkan {{ $mobil->merk }} {{ $modelName }} ke favorit" aria-pressed="false">♡</button></div>
                        <div class="detail-statuses"><span class="detail-condition"><i></i>{{ $condition }}</span><span class="detail-available-badge"><i></i>Tersedia</span></div>
                    </div>
                    <p class="detail-description">{{ $mobil->deskripsi ?: 'Kendaraan terawat dengan karakter berkendara yang nyaman untuk aktivitas harian maupun perjalanan akhir pekan.' }}</p>
                    <div class="detail-actions detail-actions-reference"><a class="detail-button detail-button-primary" href="{{ yza_whatsapp_url($whatsappText) }}" target="_blank" rel="noopener">{!! $uiIcon('whatsapp') !!}<span class="detail-button-text">Tanya via WhatsApp</span><span>→</span></a><div class="detail-action-row"><a class="detail-button detail-button-ghost" href="{{ url('/#janji-temu') }}">{!! $uiIcon('calendar') !!}<span class="detail-button-text">Jadwalkan kunjungan</span><span>→</span></a><a class="detail-button detail-button-ghost" href="{{ $tradeInCtaUrl }}" data-trade-in-trigger>{!! $uiIcon('trade') !!}<span class="detail-button-text">Trade-in / Tukar Tambah</span><span>→</span></a></div></div>
                </div>
                <div class="detail-panel detail-spec-panel"><div class="detail-section-head"><div><p class="detail-kicker">SPESIFIKASI KENDARAAN</p><h2>Detail kendaraan</h2></div><span class="detail-code">{{ $mobil->kode_mobil ?: 'YZA-UNIT' }}</span></div><div class="detail-spec-grid detail-spec-grid-reference"><div><span class="detail-spec-icon">{!! $uiIcon('engine') !!}</span><span>Tipe</span><strong>{{ $mobil->tipe ?: '-' }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('year') !!}</span><span>Tahun</span><strong>{{ $mobil->tahun ?: '-' }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('gear') !!}</span><span>Transmisi</span><strong>{{ strtoupper($mobil->transmisi ?: '-') }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('fuel') !!}</span><span>Bahan bakar</span><strong>{{ ucfirst($mobil->bahan_bakar ?: '-') }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('color') !!}</span><span>Warna</span><strong id="detail-spec-color">{{ $colorName }}</strong></div><div><span class="detail-spec-icon">{!! $uiIcon('condition') !!}</span><span>Kondisi</span><strong>{{ $condition }}</strong></div><div class="detail-spec-status"><span class="detail-spec-icon">{!! $uiIcon('status') !!}</span><span>Status</span><strong class="detail-available">Tersedia</strong></div></div></div>
                <div class="detail-panel detail-color-panel"><div class="detail-section-head"><div><p class="detail-kicker">PILIHAN WARNA</p><h2>Personalisasi</h2></div><span id="selected-color">{{ $colorName }}</span></div><div class="detail-swatches" role="group" aria-label="Pilihan warna kendaraan">@foreach($galleryData as $colorKey => $color)<button class="detail-swatch {{ $colorKey === $defaultColorKey ? 'is-active' : '' }}" type="button" data-color-key="{{ $colorKey }}" data-color="{{ $color['label'] }}" style="--swatch:{{ $color['swatch'] }}" aria-label="Pilih warna {{ $color['label'] }}" aria-pressed="{{ $colorKey === $defaultColorKey ? 'true' : 'false' }}"></button>@endforeach</div></div>
            </div>
            <div class="detail-gallery-column">
                <div class="detail-gallery-frame"><div class="detail-gallery-topline"><span class="detail-gallery-counter" id="detail-gallery-counter">01 / {{ str_pad((string) count($defaultGallery['images']), 2, '0', STR_PAD_LEFT) }}</span><span class="detail-gallery-mark"><b>YZA</b> SHOWROOM VIEW</span></div><button class="detail-gallery-arrow detail-gallery-arrow-prev" type="button" aria-label="Foto sebelumnya"><span aria-hidden="true">←</span></button><img id="detail-main-image" src="{{ $primaryImage }}" alt="{{ $mobil->merk }} {{ $modelName }}" class="detail-main-image"><button class="detail-gallery-arrow detail-gallery-arrow-next" type="button" aria-label="Foto berikutnya"><span aria-hidden="true">→</span></button><div class="detail-image-shade"></div><div class="detail-gallery-caption"><span>{{ $mobil->merk }} {{ $modelName }}</span><small id="detail-gallery-caption">{{ $defaultGallery['images'][0]['label'] }} · YZA Automotive</small></div></div>
                <div id="detail-thumbnails" class="detail-thumbnails" role="list" aria-label="Galeri foto kendaraan">@foreach($defaultGallery['images'] as $index => $image)<button class="detail-thumbnail {{ $index === 0 ? 'is-active' : '' }}" type="button" data-index="{{ $index }}" role="listitem" aria-label="Lihat {{ $image['label'] }}"><img src="{{ $image['src'] }}" alt="{{ $image['label'] }} {{ $mobil->model }}"><span>0{{ $index + 1 }}</span></button>@endforeach</div>
                <div class="detail-gallery-note"><span>✦</span> Pilih warna dan sudut kendaraan untuk melihat detail showroom.</div>
            </div>
        </section>
        <section class="detail-bottom-cta"><div><p class="detail-kicker">READY TO DRIVE?</p><h2>Jadikan kendaraan ini milik Anda.</h2><p>Tim YZA Automotive siap membantu menjawab pertanyaan, menjadwalkan kunjungan, dan memproses pembelian.</p></div><div class="detail-bottom-actions"><a class="detail-button detail-button-primary" href="{{ yza_whatsapp_url($whatsappText) }}" target="_blank" rel="noopener">Hubungi showroom <span>↗</span></a><a class="detail-button detail-button-ghost" href="{{ $tradeInCtaUrl }}" data-trade-in-trigger>Tukar tambah <span>→</span></a></div></section>
    </main>
    <x-trade-in-membership-modal :entry-url="$tradeInEntryUrl" :membership-url="$tradeInMembershipUrl" :is-active-member="$isActiveTradeInMember" />
    <script>
        (() => {
            const panel = document.querySelector('[data-detail-vehicle-id]');
            const heart = panel?.querySelector('[data-detail-favorite]');
            if (panel && heart) {
                const ids = [...new Set([String(panel.dataset.detailVehicleId || ''), window.location.pathname].filter(Boolean))];
                const key = 'yza_favorite_cars';
                const read = () => { try { const value = JSON.parse(localStorage.getItem(key) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch (error) { return []; } };
                const write = (value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch (error) {} };
                const render = (active) => { heart.textContent = active ? '♥' : '♡'; heart.classList.toggle('is-favorited', active); heart.setAttribute('aria-pressed', String(active)); heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} {{ $mobil->merk }} {{ $modelName }} ${active ? 'dari' : 'ke'} favorit`); };
                render(read().some((favorite) => ids.includes(favorite)));
                heart.addEventListener('click', () => {
                    const favorites = read();
                    const active = !favorites.some((favorite) => ids.includes(favorite));
                    const next = active
                        ? [...new Set([...favorites, ...ids])]
                        : favorites.filter((favorite) => !ids.includes(favorite));
                    write(next);
                    render(active);
                });
            }

            const galleryData = @json($galleryData);
            const fallbackImage = @json($landingVehicleImage);
            // Keep the selected angle stable while the user previews another paint.
            const state = { color: @json($defaultColorKey), index: 0, selectedColor: @json($defaultColorKey), selectedAngle: 'front' };
            const mainImage = document.getElementById('detail-main-image');
            const thumbnailsRoot = document.getElementById('detail-thumbnails');
            const colorLabel = document.getElementById('selected-color');
            const specColor = document.getElementById('detail-spec-color');
            const counter = document.getElementById('detail-gallery-counter');
            const caption = document.getElementById('detail-gallery-caption');
            const swatches = [...document.querySelectorAll('.detail-swatch')];
            const transitionImage = (src, alt) => {
                mainImage.classList.add('is-switching');
                // Update immediately so an old rear image can never overwrite the
                // newly selected color after a delayed animation callback.
                mainImage.src = src || fallbackImage;
                mainImage.alt = alt;
                window.requestAnimationFrame(() => mainImage.classList.remove('is-switching'));
            };
            const renderGallery = () => {
                const gallery = galleryData[state.color] || galleryData.default;
                const images = gallery.images || [];
                if (!images.length) return;
                state.index = Math.min(state.index, images.length - 1);
                const image = images[state.index];
                state.selectedColor = state.color;
                state.selectedAngle = image.type;
                transitionImage(image.src, `${image.label} {{ $mobil->model }}`);
                counter.textContent = `${String(state.index + 1).padStart(2, '0')} / ${String(images.length).padStart(2, '0')}`;
                caption.textContent = `${image.label} · YZA Automotive`;
                thumbnailsRoot.innerHTML = images.map((item, index) => `<button class="detail-thumbnail ${index === state.index ? 'is-active' : ''}" type="button" data-index="${index}" role="listitem" aria-label="Lihat ${item.label}"><img src="${item.src}" alt="${item.label} {{ $mobil->model }}"><span>0${index + 1}</span></button>`).join('');
            };
            const changeColor = (swatch) => {
                state.color = swatch.dataset.colorKey;
                // A color change starts at the first image of the selected variant.
                // This prevents the previous variant's active angle from leaking into the new set.
                state.index = 0;
                swatches.forEach((item) => { const active = item === swatch; item.classList.toggle('is-active', active); item.setAttribute('aria-pressed', active ? 'true' : 'false'); });
                colorLabel.textContent = swatch.dataset.color;
                if (specColor) specColor.textContent = swatch.dataset.color;
                renderGallery();
            };
            swatches.forEach((swatch) => swatch.addEventListener('click', () => changeColor(swatch)));
            thumbnailsRoot.addEventListener('click', (event) => {
                const thumbnail = event.target.closest('.detail-thumbnail');
                if (!thumbnail) return;
                state.index = Number(thumbnail.dataset.index);
                renderGallery();
            });
            document.querySelector('.detail-gallery-arrow-prev')?.addEventListener('click', () => { const count = (galleryData[state.color] || galleryData.default).images.length; state.index = (state.index - 1 + count) % count; renderGallery(); });
            document.querySelector('.detail-gallery-arrow-next')?.addEventListener('click', () => { const count = (galleryData[state.color] || galleryData.default).images.length; state.index = (state.index + 1) % count; renderGallery(); });
            mainImage.addEventListener('error', () => { if (mainImage.src !== fallbackImage) { mainImage.src = fallbackImage; } });
            renderGallery();
        })();
    </script>
</body>
</html>
