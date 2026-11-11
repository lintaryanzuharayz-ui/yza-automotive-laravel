@extends('layouts.app')
@section('title','Katalog Mobil Member · YZA Automotive')
@section('body-class', 'member-catalog-page-shell')
@section('content')
<div class="member-catalog-page">
    <div class="member-catalog-top">
    <section class="member-catalog-hero"><span class="eyebrow">YZA AUTOMOTIVE SHOWROOM</span><h1>Temukan Mobil <b>Pilihan Terbaik YZA</b></h1><p>Temukan kendaraan baru, bekas, premium, hingga performance car pilihan YZA Automotive.</p></section>
    <section class="member-catalog-controls" aria-label="Filter katalog mobil">
        <form method="get" action="{{ route('pelanggan.katalog-mobil.index') }}" class="member-catalog-filter">
            <input name="q" value="{{ request('q') }}" placeholder="Cari merk, model, tipe..." aria-label="Cari mobil">
            <select name="kondisi" aria-label="Filter kondisi"><option value="">Semua kondisi</option><option value="baru" @selected(request('kondisi') === 'baru')>Baru</option><option value="bekas" @selected(request('kondisi') === 'bekas')>Bekas</option></select>
            <select name="merk" aria-label="Filter merek"><option value="">Semua merek</option>@foreach($brands as $brand)<option value="{{ $brand }}" @selected(request('merk') === $brand)>{{ $brand }}</option>@endforeach</select>
            <select name="sort" aria-label="Urutkan"><option value="terbaru" @selected(request('sort','terbaru') === 'terbaru')>Terbaru</option><option value="harga_termurah" @selected(request('sort') === 'harga_termurah')>Harga termurah</option><option value="harga_tertinggi" @selected(request('sort') === 'harga_tertinggi')>Harga tertinggi</option><option value="tahun_terbaru" @selected(request('sort') === 'tahun_terbaru')>Tahun terbaru</option></select>
            <button type="submit" class="yza-button">Filter</button>
        </form>
        <div class="member-catalog-chips"><a class="{{ !request('kondisi') && !request('sport') ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index') }}">▣ &nbsp; Semua Mobil ({{ $allTotalCars }})</a><a class="{{ request('kondisi') === 'baru' ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index',['kondisi'=>'baru']) }}">▤ &nbsp; Baru ({{ $newCars }})</a><a class="{{ request('kondisi') === 'bekas' ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index',['kondisi'=>'bekas']) }}">♢ &nbsp; Bekas ({{ $usedCars }})</a><a class="{{ request('sport') ? 'is-active' : '' }}" href="{{ route('pelanggan.katalog-mobil.index',['sport'=>1]) }}">✦ &nbsp; Sport ({{ $sportCars }})</a><span>Menampilkan {{ $visibleCars }} dari {{ $totalCars }} kendaraan</span></div>
    </section>
    </div>
    <section class="member-catalog-results"><div class="member-catalog-heading"><div><span class="eyebrow">YZA AUTOMOTIVE COLLECTION</span><h2>TEMUKAN MOBIL PILIHAN ANDA</h2><p>Jelajahi koleksi kendaraan baru, bekas, premium, dan performance YZA Automotive.</p></div>@if(request()->hasAny(['q','kondisi','merk','sort','sport']))<a href="{{ route('pelanggan.katalog-mobil.index') }}">Reset Filter</a>@endif</div>@if($cars->isEmpty())<div class="member-empty"><strong>Belum ada kendaraan yang sesuai</strong><span>Silakan ubah filter atau kata pencarian Anda.</span></div>@else<div class="member-catalog-grid">@foreach($cars as $mobil) @include('mobil._card',['mobil'=>$mobil,'variant'=>$mobil->showcase_slug?'performance':'default']) @endforeach</div>@if($lastPage>1)<nav class="member-catalog-pagination" aria-label="Pagination">@for($page=1;$page<=$lastPage;$page++)<a class="{{ $page===$currentPage?'is-active':'' }}" href="{{ request()->fullUrlWithQuery(['page'=>$page]) }}">{{ $page }}</a>@endfor</nav>@endif @endif</section>
</div>
@endsection
@push('styles')
<style>
/* 1. MEMBER CATALOG HERO & FILTERS */
.member-catalog-page {
    max-width: 1740px;
    margin: 0 auto;
}
.member-catalog-hero {
    padding: 24px;
    border-radius: 20px;
    background: linear-gradient(110deg, #121419e8, #12141955), url('{{ asset('assets/images/lower-landing/showroom-premium.png') }}') center/cover;
    color: #fff;
}
.member-catalog-hero h1 {
    margin: 12px 0;
    font-size: clamp(1.875rem, 3.2vw, 2.75rem);
    letter-spacing: -0.06em;
}
.member-catalog-hero p {
    max-width: 720px;
    color: #e4e7eb;
    font-size: 15px;
    line-height: 1.55;
}
.member-catalog-status {
    display: inline-flex;
    gap: 12px;
    margin-top: 18px;
    padding: 10px 16px;
    border-radius: 999px;
    background: #ffffff20;
    font-size: 13px;
}
.member-catalog-controls {
    margin: 22px 0;
}
.member-catalog-filter {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1.2fr auto;
    gap: 12px;
    padding: 14px;
    background: #fff;
    border: 1px solid var(--member-line);
    border-radius: 16px;
}
.member-catalog-filter input,
.member-catalog-filter select {
    min-height: 54px;
    padding: 0 15px;
    border: 1px solid var(--member-line);
    border-radius: 9px;
    background: #fff;
    color: var(--member-ink);
    font: inherit;
}
.member-catalog-filter .yza-button {
    min-height: 54px;
    padding-inline: 22px;
}
.member-catalog-chips {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 14px;
}
.member-catalog-chips a {
    padding: 9px 15px;
    border: 1px solid var(--member-line);
    border-radius: 999px;
    color: var(--member-ink);
    font-weight: 700;
}
.member-catalog-chips span {
    margin-left: auto;
    color: var(--member-muted);
}

/* 2. MEMBER PAGE SHELL & CANVAS */
body.member-catalog-page-shell .member-main-content {
    padding-bottom: 0 !important;
    background: #090a0c !important;
}
body.member-catalog-page-shell .member-catalog-page {
    max-width: none !important;
    margin: 0 !important;
    padding: 0 28px !important;
    background: #090a0c !important;
}
body.member-catalog-page-shell .member-catalog-top {
    margin: 0 -28px !important;
    padding: 16px 28px 38px !important;
    background: #f5f6f7 !important;
}
body.member-catalog-page-shell .member-catalog-results {
    margin: 0 -28px !important;
    padding: 28px clamp(28px, 5vw, 86px) 84px !important;
    border: 0 !important;
    border-radius: 0 !important;
    background: radial-gradient(circle at 82% 0%, rgba(229, 29, 42, 0.14), transparent 34%), #090a0c !important;
    color: #ffffff !important;
}

/* 3. SECTION HEADING (MATCH GAMBAR 1 & 2) */
.member-catalog-heading {
    width: min(1640px, calc(100% - 64px)) !important;
    max-width: 1640px !important;
    margin: 0 auto 20px !important;
    display: flex !important;
    align-items: flex-end !important;
    justify-content: space-between !important;
    gap: 20px !important;
}
.member-catalog-heading .eyebrow {
    display: block !important;
    color: #ef233c !important;
    font-size: 12px !important;
    font-weight: 850 !important;
    letter-spacing: 0.22em !important;
    text-transform: uppercase !important;
    margin-bottom: 5px !important;
}
.member-catalog-heading h2 {
    margin: 0 0 6px !important;
    color: #ffffff !important;
    font-size: clamp(30px, 2.3vw, 42px) !important;
    font-weight: 850 !important;
    letter-spacing: -0.03em !important;
    line-height: 1.1 !important;
}
.member-catalog-heading p {
    margin: 0 !important;
    color: rgba(255, 255, 255, 0.68) !important;
    font-size: 15px !important;
    line-height: 1.4 !important;
}
.member-catalog-heading a {
    color: #ef233c !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    text-decoration: none !important;
    white-space: nowrap !important;
    padding: 8px 16px !important;
    border: 1px solid rgba(239, 35, 60, 0.4) !important;
    border-radius: 999px !important;
    transition: all 0.2s ease !important;
}
.member-catalog-heading a:hover {
    background: #ef233c !important;
    color: #ffffff !important;
}

/* 4. FOUR-COLUMN GRID (MATCH GAMBAR 1 & 2) */
.member-catalog-grid {
    display: grid !important;
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    gap: 26px !important;
    width: min(1640px, calc(100% - 64px)) !important;
    max-width: 1640px !important;
    margin: 0 auto !important;
    align-items: stretch !important;
}

/* 5. CARD GEOMETRY & ELEVATION */
.member-catalog-page .catalog-card {
    position: relative !important;
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
    width: 100% !important;
    min-height: 495px !important;
    background: #ffffff !important;
    border-radius: 18px !important;
    border: 1px solid rgba(255, 255, 255, 0.08) !important;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.32) !important;
    overflow: hidden !important;
    transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease !important;
}
.member-catalog-page .catalog-card:hover {
    transform: translateY(-6px) !important;
    box-shadow: 0 24px 54px rgba(0, 0, 0, 0.45) !important;
    border-color: rgba(239, 35, 60, 0.3) !important;
}

/* 6. CARD IMAGE AREA */
.member-catalog-page .catalog-card-image {
    position: relative !important;
    display: block !important;
    width: 100% !important;
    height: 242px !important;
    min-height: 242px !important;
    max-height: 242px !important;
    background: #e9edf0 !important;
    overflow: hidden !important;
    flex: 0 0 242px !important;
}
.member-catalog-page .catalog-card-image img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    object-position: center !important;
    transition: transform 0.35s ease !important;
}
.member-catalog-page .catalog-card:hover .catalog-card-image img {
    transform: scale(1.035) !important;
}

/* 7. BADGES ON PHOTO */
.member-catalog-page .catalog-card-badges {
    position: absolute !important;
    top: 14px !important;
    left: 14px !important;
    display: flex !important;
    flex-direction: column !important;
    gap: 6px !important;
    z-index: 2 !important;
    max-width: calc(100% - 64px) !important;
    pointer-events: none !important;
}
.member-catalog-page .catalog-condition {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 5px 11px !important;
    border-radius: 5px !important;
    background: #ef233c !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    letter-spacing: 0.04em !important;
    line-height: 1.1 !important;
    box-shadow: 0 4px 10px rgba(239, 35, 60, 0.3) !important;
    width: fit-content !important;
}
.member-catalog-page .catalog-sport-badge {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 5px 11px !important;
    border-radius: 5px !important;
    background: #111315 !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    letter-spacing: 0.04em !important;
    line-height: 1.1 !important;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.28) !important;
    width: fit-content !important;
}

/* 8. FAVORITE BUTTON (TOP RIGHT) */
.member-catalog-page .catalog-heart {
    position: absolute !important;
    top: 14px !important;
    right: 14px !important;
    z-index: 3 !important;
    width: 44px !important;
    height: 44px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: 1px solid rgba(255, 255, 255, 0.85) !important;
    border-radius: 50% !important;
    background: rgba(10, 12, 15, 0.72) !important;
    backdrop-filter: blur(4px) !important;
    -webkit-backdrop-filter: blur(4px) !important;
    color: #ffffff !important;
    font-size: 26px !important;
    line-height: 1 !important;
    cursor: pointer !important;
    transition: transform 0.18s ease, background 0.18s ease, border-color 0.18s ease !important;
    padding: 0 !important;
}
.member-catalog-page .catalog-heart:hover,
.member-catalog-page .catalog-heart.is-favorited {
    background: #ef233c !important;
    border-color: #ef233c !important;
    color: #ffffff !important;
    transform: scale(1.08) !important;
}

/* 9. CARD BODY */
.member-catalog-page .catalog-card-body {
    padding: 20px 22px 22px !important;
    display: flex !important;
    flex-direction: column !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    background: #ffffff !important;
}

/* 10. BRAND ROW & SPLIT TECH BADGE */
.member-catalog-page .catalog-brand-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    min-height: 38px !important;
    margin-bottom: 12px !important;
    flex-wrap: nowrap !important;
}
.member-catalog-page .catalog-brand-logo {
    display: flex !important;
    align-items: center !important;
    justify-content: flex-start !important;
    height: 36px !important;
    max-width: 80px !important;
    flex: 0 0 auto !important;
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
}
.member-catalog-page .catalog-brand-logo img {
    display: block !important;
    height: 100% !important;
    max-height: 36px !important;
    width: auto !important;
    max-width: 80px !important;
    object-fit: contain !important;
    object-position: left center !important;
}
.member-catalog-page .vehicle-tech-badge {
    display: inline-flex !important;
    align-items: stretch !important;
    height: 32px !important;
    border-radius: 7px !important;
    overflow: hidden !important;
    background: #17191d !important;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12) !important;
    flex: 0 0 auto !important;
}
.member-catalog-page .vehicle-tech-badge__label {
    background: #e51d2a !important;
    color: #ffffff !important;
    padding: 0 11px !important;
    font-size: 11px !important;
    font-weight: 850 !important;
    letter-spacing: 0.06em !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-transform: uppercase !important;
    white-space: nowrap !important;
    line-height: 1 !important;
}
.member-catalog-page .vehicle-tech-badge__value {
    background: #17191d !important;
    color: #ffffff !important;
    padding: 0 12px !important;
    font-size: 10.5px !important;
    font-weight: 850 !important;
    letter-spacing: 0.06em !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-transform: uppercase !important;
    overflow: visible !important;
    text-overflow: clip !important;
    line-height: 1 !important;
}

/* 11. VEHICLE NAME (H3) */
.member-catalog-page .catalog-card-body h3 {
    font-size: 23px !important;
    font-weight: 850 !important;
    color: #101113 !important;
    line-height: 1.25 !important;
    letter-spacing: -0.015em !important;
    margin: 0 0 8px !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
    overflow: hidden !important;
    min-height: 56px !important;
    height: auto !important;
}
.member-catalog-page .catalog-card-body > a {
    text-decoration: none !important;
    color: inherit !important;
}

/* 12. METADATA */
.member-catalog-page .catalog-meta {
    font-size: 14.5px !important;
    color: #8c949e !important;
    margin: 0 0 16px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    min-height: 20px !important;
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
}
.member-catalog-page .catalog-meta i {
    font-style: normal !important;
    color: #c4c8ce !important;
    margin: 0 4px !important;
}

/* 13. CARD FOOTER: PRICE & BIG CIRCULAR DETAIL BUTTON */
.member-catalog-page .catalog-card-footer {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 12px !important;
    margin-top: auto !important;
    padding-top: 12px !important;
    border-top: none !important;
}
.member-catalog-page .catalog-price {
    color: #e51d2a !important;
    font-size: 26px !important;
    font-weight: 900 !important;
    line-height: 1 !important;
    letter-spacing: -0.02em !important;
    margin: 0 !important;
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: unset !important;
    flex: 1 1 auto !important;
}
.member-catalog-page .catalog-detail-button {
    width: 66px !important;
    height: 66px !important;
    min-width: 66px !important;
    min-height: 66px !important;
    flex: 0 0 66px !important;
    border-radius: 50% !important;
    background: #111315 !important;
    color: #ffffff !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
    transition: background 0.2s ease, transform 0.2s ease !important;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.22) !important;
    padding: 0 !important;
    overflow: hidden !important;
}
.member-catalog-page .catalog-detail-button:hover {
    background: #ef233c !important;
    transform: scale(1.05) translateY(-2px) !important;
    box-shadow: 0 8px 20px rgba(239, 35, 60, 0.35) !important;
}
.member-catalog-page .catalog-detail-button span {
    display: none !important;
}
.member-catalog-page .catalog-detail-button b {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #ffffff !important;
    font-size: 26px !important;
    font-weight: 400 !important;
    background: transparent !important;
    width: 100% !important;
    height: 100% !important;
    line-height: 1 !important;
}

/* 14. PAGINATION IN SHOWROOM VIEW */
.member-catalog-pagination {
    width: min(1640px, calc(100% - 64px)) !important;
    max-width: 1640px !important;
    margin: 48px auto 0 !important;
    display: flex !important;
    justify-content: center !important;
    gap: 8px !important;
}
.member-catalog-pagination a {
    display: grid !important;
    place-items: center !important;
    min-width: 44px !important;
    height: 44px !important;
    padding: 0 14px !important;
    border: 1px solid rgba(255, 255, 255, 0.22) !important;
    border-radius: 999px !important;
    color: rgba(255, 255, 255, 0.85) !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
}
.member-catalog-pagination a:hover,
.member-catalog-pagination a.is-active {
    border-color: #ef233c !important;
    background: #ef233c !important;
    color: #ffffff !important;
}

/* 15. RESPONSIVE BREAKPOINTS */
@media (max-width: 1280px) and (min-width: 901px) {
    .member-catalog-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 20px !important;
    }
    .member-catalog-page .catalog-card { min-height: 480px !important; }
    .member-catalog-page .catalog-price { font-size: 23px !important; }
    .member-catalog-page .catalog-detail-button { width: 58px !important; height: 58px !important; flex-basis: 58px !important; }
    .member-catalog-page .catalog-detail-button b { font-size: 22px !important; }
}
@media (max-width: 900px) and (min-width: 561px) {
    .member-catalog-filter { grid-template-columns: 1fr 1fr; }
    .member-catalog-filter input, .member-catalog-filter .yza-button { grid-column: 1/-1; }
    .member-catalog-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 16px !important;
    }
    .member-catalog-page .catalog-card-image { height: 210px !important; min-height: 210px !important; max-height: 210px !important; flex-basis: 210px !important; }
    .member-catalog-page .catalog-card { min-height: 460px !important; }
    .member-catalog-page .catalog-card-body { padding: 18px 20px 20px !important; }
    .member-catalog-page .catalog-card-body h3 { font-size: 19px !important; min-height: 48px !important; }
    .member-catalog-page .catalog-price { font-size: 21px !important; }
    .member-catalog-page .catalog-detail-button { width: 52px !important; height: 52px !important; flex-basis: 52px !important; }
    .member-catalog-page .catalog-detail-button b { font-size: 20px !important; }
}
@media (max-width: 560px) {
    body.member-catalog-page-shell .member-catalog-results { padding: 28px 0 64px !important; margin: 0 -13px !important; }
    .member-catalog-heading { width: calc(100% - 24px) !important; margin-bottom: 24px !important; display: block !important; }
    .member-catalog-heading h2 { font-size: 28px !important; }
    .member-catalog-grid {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
        width: calc(100% - 24px) !important;
    }
    .member-catalog-page .catalog-card-image { height: 220px !important; min-height: 220px !important; max-height: 220px !important; flex-basis: 220px !important; }
    .member-catalog-page .catalog-card { min-height: 440px !important; }
    .member-catalog-page .catalog-card-body { padding: 18px !important; }
    .member-catalog-page .catalog-card-body h3 { font-size: 18px !important; min-height: auto !important; }
    .member-catalog-page .catalog-price { font-size: 20px !important; }
    .member-catalog-page .catalog-detail-button { width: 48px !important; height: 48px !important; flex-basis: 48px !important; }
    .member-catalog-page .catalog-detail-button b { font-size: 18px !important; }
}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const key = 'yza_favorite_cars';
    const read = () => { try { const value = JSON.parse(localStorage.getItem(key) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch (error) { return []; } };
    const write = value => { try { localStorage.setItem(key, JSON.stringify(value)); } catch (error) {} };
    document.querySelectorAll('[data-favorite-id]').forEach(button => {
        const id = String(button.dataset.favoriteId);
        const render = active => { button.textContent = active ? '♥' : '♡'; button.classList.toggle('is-favorited', active); button.setAttribute('aria-pressed', String(active)); };
        render(read().includes(id));
        button.addEventListener('click', event => { event.preventDefault(); event.stopPropagation(); const favorites = read(); const active = !favorites.includes(id); write(active ? [...favorites, id] : favorites.filter(item => item !== id)); render(active); });
    });
})();
</script>
@endpush
