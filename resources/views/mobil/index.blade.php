@extends('layouts.app')

@section('title', 'Katalog Mobil · YZA Automotive')


@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/public-navbar.css') }}?v=public-navbar-20261004">
@endpush

@section('catalog-no-sidebar')
@endsection

@section('catalog-header')
<x-public-navbar />
@endsection

@section('catalog-sidebar')
<aside class="catalog-sidebar" aria-label="Navigasi katalog"><nav class="catalog-side-menu"><a class="is-active" href="{{ route('mobil.index') }}"><span>▣</span>Katalog Mobil</a><a href="{{ url('/#promo') }}"><span>♢</span>Promo Terbaru</a><a href="{{ url('/#simulasi') }}"><span>▤</span>Simulasi Kredit</a><a href="{{ url('/#layanan') }}"><span>♧</span>Layanan Kami</a><a href="{{ url('/#tentang') }}"><span>ⓘ</span>Tentang Kami</a></nav><a class="catalog-side-promo" href="{{ url('/#kontak') }}"><span>Temukan<br>Mobil Impian<br>Anda Bersama<br><b>YZA Automotive</b></span><small>Kualitas Terbaik<br>Layanan Terpercaya</small></a></aside>
@endsection

@section('content')
<div class="catalog-page">
    <section class="catalog-hero"><div class="catalog-hero-copy"><span class="catalog-kicker">YZA AUTOMOTIVE SHOWROOM</span><h1>Temukan Mobil <b>Pilihan Terbaik YZA</b></h1><p>Temukan kendaraan baru, bekas, premium, hingga performance car pilihan YZA Automotive.</p></div></section>
    <section class="catalog-filter-panel" aria-label="Kontrol katalog mobil">
    <form class="catalog-filter" method="get" action="{{ route('mobil.index') }}" id="catalog-filter">
        <label class="catalog-search"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Cari merk, model, tipe..." aria-label="Cari mobil"></label>
        <label><span class="sr-only">Kondisi</span><select name="kondisi"><option value="">Semua kondisi</option><option value="baru" @selected(request('kondisi') === 'baru')>Baru</option><option value="bekas" @selected(request('kondisi') === 'bekas')>Bekas</option></select></label>
        <label><span class="sr-only">Merek</span><select name="merk"><option value="">Semua merek</option>@foreach($brands as $brand)<option value="{{ $brand }}" @selected(request('merk') === $brand)>{{ $brand }}</option>@endforeach</select></label>
        <label><span class="sr-only">Urutan</span><select name="sort"><option value="terbaru" @selected(request('sort', 'terbaru') === 'terbaru')>Urutkan: Terbaru</option><option value="harga_termurah" @selected(request('sort') === 'harga_termurah')>Harga Termurah</option><option value="harga_tertinggi" @selected(request('sort') === 'harga_tertinggi')>Harga Tertinggi</option><option value="tahun_terbaru" @selected(request('sort') === 'tahun_terbaru')>Tahun Terbaru</option></select></label>
        <button class="catalog-filter-button" type="submit"><span>⌑</span>Filter</button>
    </form>
    <div class="catalog-filter-secondary">
    <div class="catalog-chips" aria-label="Filter cepat">
        <a class="{{ !request('kondisi') && !request('sport') ? 'is-active' : '' }}" href="{{ route('mobil.index') }}">▣ &nbsp; Semua Mobil ({{ $allTotalCars }})</a>
        <a class="{{ request('kondisi') === 'baru' ? 'is-active' : '' }}" href="{{ route('mobil.index', ['kondisi' => 'baru']) }}">▤ &nbsp; Baru ({{ $newCars }})</a>
        <a class="{{ request('kondisi') === 'bekas' ? 'is-active' : '' }}" href="{{ route('mobil.index', ['kondisi' => 'bekas']) }}">♢ &nbsp; Bekas ({{ $usedCars }})</a>
        <a class="{{ request('sport') ? 'is-active' : '' }}" href="{{ route('mobil.index', ['sport' => 1]) }}">✦ &nbsp; Sport ({{ $sportCars }})</a>
    </div>
    <strong class="catalog-total">Menampilkan {{ $visibleCars }} dari {{ $totalCars }} kendaraan</strong>
    </div>
    </section>
    <section class="catalog-inventory" aria-labelledby="collection-title">
        <div class="catalog-section-heading"><div><span>YZA AUTOMOTIVE COLLECTION</span><h2 id="collection-title">TEMUKAN MOBIL PILIHAN ANDA</h2><p>Jelajahi koleksi kendaraan baru, bekas, premium, dan performance YZA Automotive.</p></div>@if(request()->hasAny(['q','kondisi','merk','sort','sport']))<a class="catalog-reset" href="{{ route('mobil.index') }}">Reset Filter</a>@endif</div>
        @if($cars->isEmpty())
            <div class="catalog-empty"><div>⌕</div><h2>Belum ada kendaraan yang sesuai</h2><p>Silakan ubah filter atau kata pencarian Anda.</p><a class="catalog-reset-button" href="{{ route('mobil.index') }}">Reset Filter</a></div>
        @else
            <div class="catalog-grid">@foreach($cars as $mobil) @include('mobil._card', ['mobil' => $mobil, 'variant' => $mobil->showcase_slug ? 'performance' : 'default']) @endforeach</div>
            @if($lastPage > 1)
                <nav class="catalog-pagination" aria-label="Pagination katalog">
                    @for($page = 1; $page <= $lastPage; $page++)
                        <a class="{{ $page === $currentPage ? 'is-active' : '' }}" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}" aria-label="Halaman {{ $page }}" @if($page === $currentPage) aria-current="page" @endif>{{ $page }}</a>
                    @endfor
                </nav>
            @endif
        @endif
    </section>
</div>
<x-public-footer />
@endsection

@push('styles')
<style>
:root{--catalog-red:#e51d2a;--catalog-ink:#101113;--catalog-muted:#6c7480}.catalog-header{height:76px;display:flex;align-items:center;gap:34px;padding:0 28px;background:#0d0e10;color:#fff;border-bottom:1px solid rgba(255,255,255,.08)}.catalog-brand{display:flex;align-items:center;gap:12px;min-width:290px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.catalog-brand img{width:48px;height:48px;object-fit:contain}.catalog-brand span{display:grid;font-size:20px;line-height:1}.catalog-brand b{color:#ef2630}.catalog-brand small{margin-top:5px;color:#c5c5c5;font-size:8px;letter-spacing:.22em;font-weight:500}.catalog-nav{display:flex;align-self:stretch;align-items:center;gap:8px;flex:1}.catalog-nav a{display:flex;align-items:center;gap:10px;height:52px;padding:0 21px;color:#d4d4d4;font-size:14px;font-weight:600;white-space:nowrap;border-radius:9px}.catalog-nav a span{font-size:20px}.catalog-nav a.is-active{color:#fff;background:linear-gradient(110deg,#88121b,#e51d2a);box-shadow:0 8px 20px rgba(229,29,42,.22)}.catalog-account{display:flex;align-items:center;gap:15px;white-space:nowrap;font-size:13px}.catalog-search-mark,.catalog-user-mark{font-size:25px}.catalog-user-mark{border-left:1px solid rgba(255,255,255,.18);padding-left:15px}.catalog-sidebar{width:258px;flex:0 0 258px;padding:28px 18px 22px;background:#151618;color:#e4e4e4;display:flex;flex-direction:column;min-height:calc(100vh - 76px)}.catalog-side-menu{display:grid;gap:7px}.catalog-side-menu a{display:flex;align-items:center;gap:16px;padding:15px 18px;border-left:3px solid transparent;color:#c4c7cc;font-size:15px;font-weight:650;border-radius:6px}.catalog-side-menu a span{width:19px;text-align:center;font-size:20px}.catalog-side-menu a.is-active{color:#fff;border-left-color:#ff2532;background:linear-gradient(90deg,rgba(153,20,29,.88),rgba(52,12,17,.25))}.catalog-side-promo{position:relative;isolation:isolate;display:flex;flex-direction:column;justify-content:space-between;min-height:395px;margin-top:auto;padding:28px 20px;border:1px solid rgba(255,41,52,.68);border-radius:9px;overflow:hidden;background:linear-gradient(180deg,rgba(10,10,12,.2),rgba(8,8,9,.94)),url('{{ asset('assets/images/lower-landing/showroom-premium.png') }}') center/cover;color:#fff;box-shadow:0 0 22px rgba(230,28,42,.12)}.catalog-side-promo span{font-size:20px;font-weight:800;line-height:1.3}.catalog-side-promo span b{color:#ff2632}.catalog-side-promo small{font-size:12px;line-height:1.55;color:#c9c9c9}.catalog-page{min-height:calc(100vh - 76px);padding:0 28px 48px;background:#f5f7f9;color:var(--catalog-ink)}.catalog-hero{display:flex;align-items:center;min-height:142px;max-width:1700px;margin:0 auto 16px;padding:30px 28px;border-radius:0 0 14px 14px;overflow:hidden;background:linear-gradient(90deg,#fff 4%,rgba(255,255,255,.88) 34%,rgba(255,255,255,.2) 64%),url('{{ asset('assets/images/lower-landing/showroom-premium.png') }}') 70% 45%/cover}.catalog-hero-copy{position:relative;z-index:2}.catalog-kicker{color:var(--catalog-red);font-size:10px;font-weight:800;letter-spacing:.25em}.catalog-hero h1{margin:7px 0 4px;font-size:44px;line-height:1;font-weight:850}.catalog-hero h1 b{color:var(--catalog-red)}.catalog-hero p{margin:0;color:#697786;font-size:18px}.catalog-filter{display:grid;grid-template-columns:minmax(240px,1.1fr) minmax(150px,.75fr) minmax(150px,.9fr) minmax(180px,1fr) 140px auto;align-items:center;gap:12px;max-width:1700px;margin:0 auto;padding:12px 14px;background:rgba(255,255,255,.9);border:1px solid #e2e6eb;border-radius:13px;box-shadow:0 7px 22px rgba(31,47,63,.06)}.catalog-filter label{display:flex;align-items:center;min-width:0}.catalog-filter input,.catalog-filter select{width:100%;height:43px;padding:0 15px;border:1px solid #dce1e7;border-radius:8px;background:#fff;color:#303842;font:600 13px Arial}.catalog-search{position:relative}.catalog-search span{position:absolute;left:14px;font-size:25px;color:#202831}.catalog-search input{padding-left:43px}.catalog-filter-button{height:43px;border:0;border-radius:8px;background:linear-gradient(110deg,#b81925,#ee1e2c);color:#fff;font:800 13px Arial;cursor:pointer}.catalog-filter-button span{font-size:18px;margin-right:8px}.catalog-total{padding:0 10px;color:#53606c;font-size:12px;white-space:nowrap}.catalog-chips{display:flex;gap:10px;max-width:1700px;margin:15px auto 18px}.catalog-chips a{padding:10px 19px;border:1px solid #d8dfe6;border-radius:999px;background:#fff;color:#59636e;font-size:13px;font-weight:750}.catalog-chips a.is-active{border-color:transparent;background:linear-gradient(110deg,#c91826,#f02431);color:#fff;box-shadow:0 7px 14px rgba(229,29,42,.18)}.catalog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(196px,1fr));gap:18px;max-width:1700px;margin:0 auto}.catalog-card{min-width:0;overflow:hidden;background:#fff;border:1px solid #e3e7eb;border-radius:12px;box-shadow:0 5px 16px rgba(33,47,60,.08);transition:transform .2s ease,box-shadow .2s ease}.catalog-card:hover{transform:translateY(-4px);box-shadow:0 14px 28px rgba(33,47,60,.15)}.catalog-card-image{position:relative;display:block;height:135px;background:#e9edf0;overflow:hidden}.catalog-card-image img{width:100%;height:100%;display:block;object-fit:cover}.catalog-condition{position:absolute;top:10px;left:10px;padding:6px 10px;border-radius:999px;background:#ef2630;color:#fff;font-size:11px;font-weight:800}.catalog-condition.is-used{background:#292d31}.catalog-heart{position:absolute;top:9px;right:9px;width:34px;height:34px;border:2px solid #fff;border-radius:50%;background:rgba(17,19,21,.58);color:#fff;font-size:23px;line-height:1;cursor:pointer}.catalog-card-body{padding:10px 12px 12px}.catalog-card-body h2{height:39px;margin:0 0 6px;font-size:16px;line-height:1.18;font-weight:850}.catalog-meta{display:flex;align-items:center;gap:5px;margin:0;color:#66727c;font-size:11px}.catalog-meta span{color:var(--catalog-red)}.catalog-meta i{font-style:normal;color:#a4aab0}.catalog-price{display:block;margin:8px 0 10px;color:var(--catalog-red);font-size:16px;line-height:1.1}.catalog-detail-button{display:flex;align-items:center;justify-content:space-between;height:35px;overflow:hidden;border-radius:999px;background:#171b1f;color:#fff;font-size:11px;font-weight:800}.catalog-detail-button span{padding-left:17px}.catalog-detail-button b{display:grid;place-items:center;align-self:stretch;width:36px;background:var(--catalog-red);font-size:19px}.catalog-empty{grid-column:1/-1;padding:70px;text-align:center;background:#fff;border-radius:14px}.sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:1200px){.catalog-header{gap:16px;padding:0 18px}.catalog-brand{min-width:230px}.catalog-nav a{padding:0 11px;font-size:12px}.catalog-sidebar{width:220px;flex-basis:220px}.catalog-filter{grid-template-columns:1.4fr 1fr 1fr 1fr 110px}.catalog-total{grid-column:1/-1}.catalog-grid{grid-template-columns:repeat(auto-fill,minmax(185px,1fr))}}
@media(max-width:800px){.catalog-header{height:66px}.catalog-brand{min-width:0}.catalog-brand span{font-size:16px}.catalog-brand img{width:37px;height:37px}.catalog-brand small,.catalog-nav,.catalog-account{display:none}.yza-body{display:block}.catalog-sidebar{width:100%;min-height:0;padding:12px 14px}.catalog-side-menu{display:flex;overflow:auto;gap:5px}.catalog-side-menu a{flex:0 0 auto;padding:11px 13px;font-size:12px}.catalog-side-promo{display:none}.catalog-page{padding:0 13px 32px}.catalog-hero{min-height:145px;margin:0 -13px 12px;padding:25px 18px;background:linear-gradient(90deg,#fff 12%,rgba(255,255,255,.72)),url('{{ asset('assets/images/lower-landing/showroom-premium.png') }}') center/cover}.catalog-hero h1{font-size:35px}.catalog-hero p{font-size:14px}.catalog-filter{grid-template-columns:1fr 1fr;padding:10px}.catalog-search,.catalog-filter label:nth-child(4),.catalog-filter-button{grid-column:1/-1}.catalog-total{grid-column:1/-1}.catalog-chips{overflow:auto;margin:12px 0 15px;padding-bottom:3px}.catalog-chips a{white-space:nowrap;padding:9px 14px}.catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.catalog-card-image{height:112px}.catalog-card-body{padding:9px}.catalog-card-body h2{font-size:14px}.catalog-price{font-size:13px}.catalog-detail-button{height:32px;font-size:10px}.catalog-detail-button span{padding-left:11px}.catalog-detail-button b{width:31px}}
@media(max-width:420px){.catalog-grid{grid-template-columns:1fr}.catalog-card-image{height:155px}}
.yza-content{width:auto;min-width:0;padding:0}.yza-shell{overflow-x:hidden}
@media(max-width:1400px) and (min-width:801px){.catalog-filter{grid-template-columns:minmax(180px,1.35fr) repeat(3,minmax(120px,1fr)) 110px}.catalog-total{grid-column:1/-1;padding:4px 2px}}
.yza-body-full{display:block;background:#fff}.catalog-header{height:82px;padding:0 30px;gap:28px}.catalog-brand{min-width:285px}.catalog-contact{display:flex;align-items:center;gap:18px;margin-left:8px;padding:13px 20px;border-radius:9px;background:linear-gradient(110deg,#f0182b,#ff2634);color:#fff;font-weight:800}.catalog-contact span{font-size:20px}.catalog-page{min-height:calc(100vh - 82px);padding:8px 16px 42px;background:#fff}.catalog-hero{min-height:208px;max-width:none;margin:0 0 10px;padding:34px 38px;border-radius:16px;background:linear-gradient(90deg,rgba(5,8,11,.9) 0%,rgba(5,8,11,.78) 26%,rgba(5,8,11,.15) 65%),url('{{ asset('assets/images/lower-landing/showroom-premium.png') }}') center 48%/cover}.catalog-kicker{font-size:12px;letter-spacing:.31em}.catalog-hero h1{margin:10px 0 8px;color:#fff;font-size:58px}.catalog-hero p{color:#f4f4f4;font-size:20px;line-height:1.4}.catalog-hero p span{font-size:18px}.catalog-filter{max-width:none;grid-template-columns:minmax(330px,1.55fr) minmax(190px,.78fr) minmax(190px,.9fr) minmax(230px,1fr) 140px auto;gap:12px;margin:0 0 8px;padding:12px 16px;border-radius:14px}.catalog-filter input,.catalog-filter select,.catalog-filter-button{height:44px}.catalog-total{font-size:13px}.catalog-chips{max-width:none;margin:8px 0 17px;padding-left:2px}.catalog-chips a{padding:10px 20px;font-size:14px}.catalog-grid{max-width:none;grid-template-columns:repeat(7,minmax(0,1fr));gap:14px}.catalog-card-image{height:122px}.catalog-card-body{padding:10px 14px 13px}.catalog-card-body h2{font-size:16px}.catalog-meta{font-size:12px}.catalog-price{font-size:16px;margin:7px 0 10px}.catalog-detail-button{height:36px;font-size:12px}.catalog-detail-button b{width:40px}
@media(max-width:1500px) and (min-width:1201px){.catalog-grid{grid-template-columns:repeat(6,minmax(0,1fr))}.catalog-header{gap:18px}.catalog-nav a{padding:0 14px}}
@media(max-width:1400px) and (min-width:801px){.catalog-contact{display:none}.catalog-header{gap:12px;padding:0 20px}.catalog-brand{min-width:230px}.catalog-nav{gap:2px}.catalog-nav a{padding:0 10px;font-size:12px}.catalog-account{font-size:12px}}
@media(max-width:1200px) and (min-width:801px){.catalog-grid{grid-template-columns:repeat(5,minmax(0,1fr))}.catalog-hero h1{font-size:48px}.catalog-contact{display:none}}
@media(max-width:800px){.catalog-header{height:68px;padding:0 16px}.catalog-page{min-height:calc(100vh - 68px);padding:8px 13px 32px}.catalog-hero{min-height:190px;margin:0 0 12px;padding:25px 20px;border-radius:13px}.catalog-hero h1{font-size:39px}.catalog-hero p{font-size:15px}.catalog-hero p span{font-size:13px}.catalog-filter{margin:0;grid-template-columns:1fr 1fr}.catalog-chips{margin:12px 0 15px}.catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.catalog-card-image{height:112px}}
.catalog-sidebar{width:210px;flex-basis:210px;padding:24px 14px 18px}.catalog-side-menu a{padding:13px 12px;font-size:14px}.catalog-side-promo{min-height:360px;padding:22px 16px}.catalog-side-promo span{font-size:18px}.catalog-page{min-height:calc(100vh - 82px);padding:18px 28px 80px;background:#f5f6f7}.catalog-hero{min-height:250px;margin:0 0 18px;padding:38px 42px;border-radius:16px;background:linear-gradient(90deg,rgba(5,8,11,.92) 0%,rgba(5,8,11,.82) 28%,rgba(5,8,11,.12) 73%),url('{{ asset('assets/images/lower-landing/showroom-premium.png') }}') center/cover}.catalog-hero h1{max-width:720px;font-size:54px;line-height:1.03}.catalog-hero p{max-width:590px;font-size:18px;line-height:1.5}.catalog-filter{position:sticky;top:82px;z-index:20;max-width:none;margin:0 -2px 16px;grid-template-columns:minmax(230px,1.5fr) minmax(140px,.8fr) minmax(140px,.9fr) minmax(180px,1fr) 125px auto;padding:14px 16px;background:rgba(255,255,255,.96);box-shadow:0 8px 24px rgba(30,38,46,.1);backdrop-filter:blur(10px)}.catalog-chips{max-width:none;margin:0 0 28px}.catalog-section-heading{display:flex;align-items:end;justify-content:space-between;gap:24px;margin-bottom:22px}.catalog-section-heading>div>span{color:var(--catalog-red);font-size:12px;font-weight:850;letter-spacing:.22em}.catalog-section-heading h2{margin:7px 0 5px;font-size:30px;line-height:1.1}.catalog-section-heading p{margin:0;color:#6c7480;font-size:15px}.catalog-section-heading>a{color:var(--catalog-red);font-size:14px;font-weight:800;white-space:nowrap}.catalog-performance{margin:38px -28px 64px;padding:58px 28px 64px;background:radial-gradient(circle at 78% 0%,rgba(183,19,32,.16),transparent 38%),#0d0e10;color:#fff}.catalog-performance .catalog-section-heading{max-width:1500px;margin:0 auto 24px}.catalog-performance .catalog-section-heading p{color:#b3b6bd}.catalog-performance .catalog-performance-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:22px;max-width:1500px;margin:0 auto}.catalog-collection{max-width:1500px;margin:0 auto}.catalog-collection .catalog-section-heading{margin-bottom:24px}.catalog-grid{max-width:none;grid-template-columns:repeat(5,minmax(0,1fr));gap:22px}.catalog-card{border-radius:14px;box-shadow:0 7px 20px rgba(33,47,60,.09)}.catalog-card-image{height:auto;aspect-ratio:16/10}.catalog-card-image img{transition:transform .24s ease}.catalog-card:hover .catalog-card-image img{transform:scale(1.02)}.catalog-card-body{padding:15px 16px 16px}.catalog-card-body h3{height:44px;margin:0 0 8px;color:#101113;font-size:18px;line-height:1.2;font-weight:850;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.catalog-meta{font-size:13px}.catalog-meta i{color:#a4aab0;font-style:normal}.catalog-price{margin:10px 0 13px;font-size:18px;font-weight:850}.catalog-detail-button{height:44px;font-size:13px}.catalog-detail-button span{padding-left:18px}.catalog-detail-button b{width:44px}.catalog-card-performance{background:#15171a;border-color:rgba(255,44,55,.24);box-shadow:0 12px 30px rgba(255,30,40,.1)}.catalog-card-performance .catalog-card-body h3{color:#fff}.catalog-card-performance .catalog-meta{color:#c4c8ce}.catalog-card-performance .catalog-card-image{aspect-ratio:16/10}.catalog-card-performance .catalog-condition{font-size:10px;letter-spacing:.04em}.catalog-empty{padding:80px 20px}.catalog-empty>div{font-size:34px;color:var(--catalog-red)}.catalog-reset,.catalog-reset-button{color:var(--catalog-red);font-weight:800}.catalog-reset-button{display:inline-flex;margin-top:16px;padding:12px 20px;border-radius:999px;background:var(--catalog-red);color:#fff!important}
@media(max-width:1399px) and (min-width:1101px){.catalog-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.catalog-page{padding-left:22px;padding-right:22px}.catalog-performance{margin-left:-22px;margin-right:-22px;padding-left:22px;padding-right:22px}}
@media(max-width:1100px) and (min-width:801px){.catalog-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.catalog-performance .catalog-performance-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.catalog-filter{top:82px}.catalog-hero h1{font-size:46px}}
@media(max-width:800px){.catalog-sidebar{width:100%;flex-basis:auto}.catalog-page{padding:13px 13px 48px}.catalog-hero{min-height:220px;padding:28px 22px}.catalog-hero h1{font-size:38px}.catalog-hero p{font-size:15px}.catalog-filter{position:static;margin:0 0 14px;grid-template-columns:1fr 1fr}.catalog-performance{margin:30px -13px 44px;padding:42px 13px 48px}.catalog-performance .catalog-section-heading,.catalog-section-heading{display:block;margin-bottom:20px}.catalog-section-heading>a{display:inline-block;margin-top:15px}.catalog-performance .catalog-performance-grid{grid-template-columns:1fr 1fr;gap:13px}.catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}.catalog-card-body{padding:12px}.catalog-card-body h3{font-size:15px}.catalog-price{font-size:15px}.catalog-detail-button{height:40px;font-size:11px}.catalog-detail-button b{width:38px}}
.yza-body-full{display:block;width:100%;background:#fff}.catalog-page{width:100%;box-sizing:border-box;padding-left:28px;padding-right:28px}.catalog-hero{max-width:none}.catalog-filter{max-width:none}.catalog-performance{margin-left:-28px;margin-right:-28px;padding-left:clamp(28px,5vw,86px);padding-right:clamp(28px,5vw,86px)}.catalog-performance .catalog-performance-grid{max-width:1600px}.catalog-grid{grid-template-columns:repeat(4,minmax(0,1fr));max-width:1600px;gap:24px}.catalog-collection{max-width:1600px}.catalog-card-image{aspect-ratio:16/10}.catalog-card-body{padding:17px 18px 18px}.catalog-card-body h3{font-size:19px}.catalog-price{font-size:19px}.catalog-detail-button{height:46px;font-size:13px}.catalog-detail-button b{width:46px}.catalog-header{position:sticky;top:0;z-index:30}
@media(max-width:1199px) and (min-width:801px){.catalog-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.catalog-performance .catalog-performance-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:800px){.catalog-page{padding-left:13px;padding-right:13px}.catalog-performance{margin-left:-13px;margin-right:-13px}.catalog-grid{grid-template-columns:1fr;gap:16px}.catalog-performance .catalog-performance-grid{grid-template-columns:1fr}.catalog-card-image{aspect-ratio:16/10}.catalog-card-body h3{font-size:18px}}
</style>
<style>
.catalog-card{position:relative}
.catalog-heart{z-index:4;display:grid;place-items:center;padding:0;border:2px solid rgba(255,255,255,.9);background:rgba(17,19,21,.68);font-family:inherit;transition:color .2s ease,background .2s ease,transform .2s ease,border-color .2s ease}
.catalog-heart:hover,.catalog-heart.is-favorited{border-color:#ff2632;background:#ed1c2a;color:#fff;transform:scale(1.08)}
.catalog-card{border:0;border-radius:16px;background:#fff;color:#17191c;box-shadow:0 12px 35px rgba(0,0,0,.12)}
.catalog-card:hover{transform:translateY(-5px);box-shadow:0 18px 42px rgba(0,0,0,.18)}
.catalog-card-image{height:auto;aspect-ratio:1.36;min-height:220px;background:#eceff1}
.catalog-card-image img{object-fit:cover;object-position:center;width:100%;height:100%}
.catalog-card-badges{position:absolute;top:14px;left:14px;display:flex;align-items:center;gap:8px;z-index:2}
.catalog-condition,.catalog-sport-badge{position:static;display:inline-flex;align-items:center;min-height:25px;padding:5px 9px;border-radius:6px;text-transform:uppercase;font-size:10px;font-weight:850;letter-spacing:.03em}
.catalog-condition{background:#111315;color:#fff}
.catalog-sport-badge{background:#f0212e;color:#fff}
.catalog-heart{top:14px;right:14px;width:40px;height:40px;border:0;background:rgba(17,19,21,.78);font-size:23px}
.catalog-card-body{padding:18px 18px 18px;background:#fff}
.catalog-brand-row{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:34px;margin-bottom:10px}
.catalog-brand-row img{display:block;width:auto;max-width:78px;height:30px;object-fit:contain;object-position:left center}
.catalog-variant-pill{display:inline-flex;align-items:center;min-height:28px;padding:0 12px;border:1px solid #e8e8eb;border-radius:999px;background:#f5f5f7;color:#52545a;font-size:10px;font-weight:750;letter-spacing:.04em;white-space:nowrap}
.catalog-card-body h3{height:auto;min-height:44px;margin:0 0 8px;color:#1b1d22;font-size:18px;line-height:1.2;font-weight:850;letter-spacing:-.02em}
.catalog-meta{display:flex;align-items:center;gap:5px;min-height:18px;margin:0;color:#92959c;font-size:12px;font-weight:500;white-space:nowrap}
.catalog-meta i{color:#c5c7ca}
.catalog-price{display:block;margin:15px 0 14px;color:#f01f2d;font-size:21px;font-weight:900;line-height:1.05;letter-spacing:-.02em}
.catalog-detail-button{display:flex;align-items:center;justify-content:flex-end;width:54px;height:54px;margin-left:auto;border-radius:50%;background:#111315;color:#fff;font-size:0;transition:background .2s ease,transform .2s ease}
.catalog-detail-button:hover{background:#f0212e;transform:translateX(2px) scale(1.03)}
.catalog-detail-button span{display:none}
.catalog-detail-button b{display:grid;place-items:center;width:100%;height:100%;background:transparent;font-size:25px;font-weight:500}
.catalog-card-performance{background:#fff;border:0;box-shadow:0 12px 35px rgba(0,0,0,.12)}
.catalog-card-performance .catalog-card-body{background:#fff}
.catalog-card-performance .catalog-card-body h3,.catalog-card-performance .catalog-meta{color:#1b1d22}
.catalog-card-performance .catalog-meta{color:#92959c}
.catalog-card-performance .catalog-card-image{aspect-ratio:1.36}
@media(max-width:1399px) and (min-width:801px){.catalog-card-image{min-height:205px}.catalog-price{font-size:19px}}
@media(max-width:800px){.catalog-card-image,.catalog-card-performance .catalog-card-image{min-height:0;aspect-ratio:1.36}.catalog-card-body{padding:15px 14px 16px}.catalog-card-body h3{font-size:16px;min-height:40px}.catalog-price{font-size:18px}.catalog-detail-button{width:48px;height:48px}.catalog-brand-row img{max-width:70px;height:27px}.catalog-variant-pill{padding-inline:9px;font-size:9px}.catalog-meta{font-size:11px}}
@media(max-width:420px){.catalog-card-body h3{font-size:18px}.catalog-card-image{aspect-ratio:1.42}}
.catalog-card-body{min-height:218px;display:flex;flex-direction:column}
.catalog-brand-row{min-height:48px;margin-bottom:13px;gap:10px}
.catalog-brand-row img{flex:0 1 50%;width:50%;max-width:50%;min-width:0;height:43px;object-fit:contain;object-position:left center}
.catalog-variant-pill{flex:0 0 44%;width:44%;max-width:44%;min-width:0;min-height:31px;padding:5px 9px;border-color:#e1e2e6;background:linear-gradient(135deg,#fafafa 0%,#f1f2f5 100%);box-shadow:inset 0 1px 0 rgba(255,255,255,.9),0 2px 6px rgba(18,20,24,.04);font-size:9px;line-height:1.15;text-align:center;white-space:normal;overflow-wrap:anywhere}
.catalog-card-body h3{font-size:17px;line-height:1.22;min-height:43px;margin-bottom:8px}
.catalog-meta{font-size:11.5px;letter-spacing:.005em}
.catalog-price{margin:14px 0 14px;font-size:21px}
.catalog-detail-button{flex:0 0 auto}
@media(max-width:800px){.catalog-card-body{min-height:205px}.catalog-brand-row{min-height:43px}.catalog-brand-row img{max-width:50%;height:38px}.catalog-variant-pill{flex-basis:46%;width:46%;max-width:46%;font-size:9px}.catalog-card-body h3{font-size:16px}.catalog-price{font-size:18px}}
@media(max-width:420px){.catalog-brand-row img{max-width:52%;height:40px}.catalog-variant-pill{flex-basis:44%;width:44%;max-width:44%}.catalog-card-body{min-height:214px}}

/* Brand marks: use the mapped brand SVG at a readable, consistent size. */
.catalog-brand-logo{display:flex;align-items:center;justify-content:flex-start;flex:0 1 52%;width:52%;max-width:52%;min-width:0;height:54px;padding:7px 12px;border:1px solid #e1e5ea;border-radius:14px;background:linear-gradient(145deg,#fff 0%,#f3f5f7 100%);box-shadow:0 5px 14px rgba(20,28,38,.08),inset 0 1px 0 rgba(255,255,255,.95);overflow:hidden}
.catalog-brand-logo img{display:block;width:100%;height:100%;max-width:100%;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;object-fit:contain;object-position:left center;filter:drop-shadow(0 1px 1px rgba(0,0,0,.08))}
.catalog-brand-row .catalog-variant-pill{flex:0 1 42%;width:42%;max-width:42%;min-height:34px;padding:5px 9px}
.catalog-inventory .catalog-brand-row{min-height:54px;margin-bottom:12px}
.catalog-inventory .catalog-brand-logo{flex-basis:52%;width:52%;max-width:52%;height:54px;padding:7px 12px;border-radius:14px}
.catalog-inventory .catalog-brand-logo img{width:100%;max-width:100%;height:100%;max-height:none;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;object-fit:contain;object-position:left center}
.catalog-inventory .catalog-brand-row .catalog-variant-pill{flex:0 1 42%;width:42%;max-width:42%;min-height:34px}
@media(max-width:800px){.catalog-brand-logo,.catalog-inventory .catalog-brand-logo{flex-basis:52%;width:52%;max-width:52%;height:46px;padding:6px 9px;border-radius:12px}.catalog-brand-row .catalog-variant-pill,.catalog-inventory .catalog-brand-row .catalog-variant-pill{flex-basis:44%;width:44%;max-width:44%;min-height:30px}.catalog-inventory .catalog-brand-row{min-height:46px}}
@media(max-width:420px){.catalog-brand-logo,.catalog-inventory .catalog-brand-logo{height:48px;padding-inline:8px}}

/* Unified dark inventory: one result source, one catalog surface. */
:root{--yza-page-width:min(95vw,1680px)}
.catalog-page{--yza-top-width:min(96vw,1840px)}
.catalog-hero,.catalog-filter,.catalog-chips{width:var(--yza-top-width);max-width:none}
.catalog-hero{min-height:280px;padding:38px;border-radius:16px;background-position:center center}
.catalog-hero h1{font-size:clamp(44px,3.35vw,62px);line-height:1.02}
.catalog-hero p{font-size:18px;line-height:1.45}
.catalog-filter{grid-template-columns:minmax(300px,1.5fr) minmax(180px,.9fr) minmax(180px,.9fr) minmax(220px,1.1fr) 125px auto;min-height:74px;padding:12px 14px;border-radius:15px;gap:12px}
.catalog-filter input,.catalog-filter select,.catalog-filter-button{height:50px}
.catalog-filter input,.catalog-filter select{font-size:13px}
.catalog-filter-button{min-width:125px}
.catalog-total{padding-left:6px;font-size:12px}
.catalog-chips{margin:14px auto 38px;gap:12px}
.catalog-chips a{padding:11px 19px}
.catalog-inventory{--yza-page-width:min(95vw,1680px);position:relative;margin:0 -28px;padding:76px 0 88px;background:radial-gradient(circle at 82% 0%,rgba(229,29,42,.14),transparent 34%),#090a0c;color:#fff}
.catalog-hero,.catalog-filter,.catalog-chips{width:var(--yza-page-width);max-width:none;box-sizing:border-box}
.catalog-inventory .catalog-section-heading,.catalog-inventory .catalog-grid,.catalog-inventory .catalog-pagination{width:var(--yza-page-width);max-width:none;margin-left:auto;margin-right:auto}
.catalog-inventory .catalog-section-heading{margin-bottom:34px}
.catalog-inventory .catalog-section-heading>div>span{color:#f0212e;font-size:12px;letter-spacing:.24em;font-weight:850}
.catalog-inventory .catalog-section-heading h2{margin:8px 0 7px;color:#fff;font-size:clamp(32px,3vw,48px);font-weight:850;letter-spacing:-.03em}
.catalog-inventory .catalog-section-heading p{color:rgba(255,255,255,.66);font-size:15px}
.catalog-inventory .catalog-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:22px}
.catalog-inventory .catalog-card{border:1px solid rgba(255,255,255,.06);box-shadow:0 18px 45px rgba(0,0,0,.3)}
.catalog-inventory .catalog-card:hover{box-shadow:0 22px 50px rgba(0,0,0,.4)}
.catalog-inventory .catalog-empty{background:#fff}
.catalog-pagination{display:flex;justify-content:center;gap:8px;margin:48px auto 0}
.catalog-pagination a{display:grid;place-items:center;min-width:40px;height:40px;padding:0 12px;border:1px solid rgba(255,255,255,.18);border-radius:999px;color:rgba(255,255,255,.78);font-size:13px;font-weight:800;transition:background .2s ease,color .2s ease,border-color .2s ease}
.catalog-pagination a:hover,.catalog-pagination a.is-active{border-color:#f0212e;background:#f0212e;color:#fff}
@media(max-width:1100px) and (min-width:801px){.catalog-inventory .catalog-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:22px}}
@media(max-width:800px){.catalog-page{--yza-top-width:100%}.catalog-hero,.catalog-filter,.catalog-chips{width:100%}.catalog-hero{min-height:220px;padding:28px 22px}.catalog-hero h1{font-size:38px}.catalog-hero p{font-size:15px}.catalog-filter{min-height:0;padding:10px}.catalog-filter input,.catalog-filter select,.catalog-filter-button{height:44px}.catalog-chips{margin:12px 0 30px}.catalog-inventory{--yza-page-width:100%;margin:0 -13px;padding:56px 13px 64px}.catalog-inventory .catalog-section-heading,.catalog-inventory .catalog-grid,.catalog-inventory .catalog-pagination{width:100%}.catalog-inventory .catalog-section-heading{margin-bottom:26px}.catalog-inventory .catalog-section-heading h2{font-size:32px}.catalog-inventory .catalog-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}}
@media(max-width:520px){.catalog-inventory .catalog-grid{grid-template-columns:1fr}}

/* Final top-area alignment: use the full catalog canvas while keeping the
   inventory surface and card layout unchanged. */
.yza-body-full .yza-content{padding:0 22px 0}
.catalog-page{padding:16px 0 80px}
.catalog-hero,.catalog-filter,.catalog-chips{width:100%;max-width:none}
.catalog-hero{margin:0 0 18px}
.catalog-filter{margin:0 auto 14px}
.catalog-chips{justify-content:center;margin:0 auto 38px}
@media(max-width:800px){.yza-body-full .yza-content{padding:0 13px}.catalog-page{padding:13px 0 48px}.catalog-chips{justify-content:flex-start}}

/* Premium control panel: search, filters, quick filters and count share one surface. */
.catalog-filter-panel{width:100%;margin:0 auto 28px;padding:14px 16px 15px;background:#fff;border:1px solid #e3e7ec;border-radius:16px;box-shadow:0 10px 30px rgba(25,35,45,.07)}
.catalog-filter-panel .catalog-filter{width:100%;margin:0;padding:0;background:transparent;border:0;border-radius:0;box-shadow:none;grid-template-columns:minmax(320px,1.6fr) minmax(180px,.9fr) minmax(180px,.9fr) minmax(220px,1.1fr) 125px;gap:10px}
.catalog-filter-panel .catalog-filter input,.catalog-filter-panel .catalog-filter select,.catalog-filter-panel .catalog-filter-button{height:50px;border-radius:10px}
.catalog-filter-panel .catalog-filter input,.catalog-filter-panel .catalog-filter select{border-color:#d9dde3;color:#303842;background:#fff;transition:border-color .2s ease,box-shadow .2s ease}
.catalog-filter-panel .catalog-filter input:focus,.catalog-filter-panel .catalog-filter select:focus{outline:0;border-color:#ef1f2f;box-shadow:0 0 0 3px rgba(239,31,47,.1)}
.catalog-filter-panel .catalog-filter-button{min-width:125px;transition:transform .2s ease,box-shadow .2s ease,background .2s ease}
.catalog-filter-panel .catalog-filter-button:hover{transform:translateY(-1px);box-shadow:0 7px 14px rgba(229,29,42,.18);background:#d71927}
.catalog-filter-secondary{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:13px}
.catalog-filter-panel .catalog-chips{flex:1;display:flex;align-items:center;justify-content:flex-start;width:auto;margin:0;gap:10px;padding:0}
.catalog-filter-panel .catalog-chips a{min-height:40px;padding:0 16px;display:inline-flex;align-items:center;border-radius:999px;font-size:13px;transition:transform .2s ease,border-color .2s ease,color .2s ease}
.catalog-filter-panel .catalog-chips a:not(.is-active):hover{border-color:#e51d2a;color:#e51d2a;transform:translateY(-1px)}
.catalog-filter-panel .catalog-total{flex:0 0 auto;padding:0;color:#68707a;font-size:13px;font-weight:700;white-space:nowrap}
@media(max-width:1400px) and (min-width:801px){.catalog-filter-panel .catalog-filter{grid-template-columns:minmax(260px,1.5fr) minmax(160px,.9fr) minmax(160px,.9fr) minmax(190px,1.1fr) 120px}.catalog-filter-secondary{gap:12px}}
@media(max-width:800px){.catalog-filter-panel{margin-bottom:24px;padding:12px 10px 13px;border-radius:14px}.catalog-filter-panel .catalog-filter{grid-template-columns:1fr 1fr;gap:9px}.catalog-filter-panel .catalog-search,.catalog-filter-panel .catalog-filter label:nth-child(4),.catalog-filter-panel .catalog-filter-button{grid-column:1/-1}.catalog-filter-secondary{display:block;margin-top:12px}.catalog-filter-panel .catalog-chips{width:100%;overflow-x:auto;gap:8px;padding-bottom:3px}.catalog-filter-panel .catalog-chips a{flex:0 0 auto;min-height:38px;padding:0 14px;font-size:12px}.catalog-filter-panel .catalog-total{display:block;margin-top:11px;font-size:12px}}

/* Compact premium card proportions: preserve the existing visual identity. */
.catalog-inventory .catalog-card{display:flex;flex-direction:column;min-width:0}
.catalog-inventory .catalog-card-image{aspect-ratio:16/10;height:auto;flex:0 0 auto}
.catalog-inventory .catalog-card-body{display:flex;flex:1;flex-direction:column;padding:14px 16px 15px}
.catalog-inventory .catalog-brand-row{min-height:34px;margin-bottom:11px}
.catalog-inventory .catalog-brand-row img{height:32px;max-height:32px}
.catalog-inventory .catalog-variant-pill{min-height:29px;padding:5px 9px;font-size:9px}
.catalog-inventory .catalog-card-body h3{height:43px;min-height:43px;margin:0 0 7px;font-size:17px;line-height:1.24;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.catalog-inventory .catalog-meta{min-height:16px;margin:0;font-size:11.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.catalog-inventory .catalog-card-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto;padding-top:13px;border-top:1px solid #f0f1f3}
.catalog-inventory .catalog-price{margin:0;color:var(--catalog-red);font-size:20px;line-height:1.1;font-weight:850;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.catalog-inventory .catalog-detail-button{flex:0 0 48px;width:48px;height:48px;border-radius:50%;justify-content:center;background:#111315;transition:background .2s ease,transform .2s ease}
.catalog-inventory .catalog-detail-button span{display:none}
.catalog-inventory .catalog-detail-button b{width:auto;background:transparent;font-size:22px}
.catalog-inventory .catalog-card:hover .catalog-detail-button{background:var(--catalog-red);transform:translateX(2px)}
@media(max-width:800px){.catalog-inventory .catalog-card-body{padding:12px 13px 14px}.catalog-inventory .catalog-card-body h3{font-size:16px}.catalog-inventory .catalog-price{font-size:18px}.catalog-inventory .catalog-detail-button{flex-basis:44px;width:44px;height:44px}}

/* Refined brand and variant tags. */
.catalog-inventory .catalog-brand-row{align-items:center;gap:12px;min-height:38px;margin-bottom:12px}
.catalog-inventory .catalog-brand-row img{flex:0 0 106px;width:106px;max-width:106px;height:34px;max-height:34px;padding:6px 9px;object-fit:contain;object-position:center;border:1px solid #edf0f3;border-radius:10px;background:linear-gradient(145deg,#fff,#f7f8fa);box-shadow:0 3px 8px rgba(20,28,38,.07),inset 0 1px 0 rgba(255,255,255,.95)}
.catalog-inventory .catalog-variant-pill{flex:0 1 auto;width:auto;max-width:52%;min-height:32px;padding:0 12px;border:1px solid #e3e6eb;border-radius:10px;background:#f8f9fb;color:#4a5663;box-shadow:inset 0 1px 0 rgba(255,255,255,.95);font-size:9px;font-weight:800;letter-spacing:.07em;line-height:1.15;text-align:center;white-space:normal}
@media(max-width:800px){.catalog-inventory .catalog-brand-row img{flex-basis:92px;width:92px;max-width:92px;height:31px;max-height:31px;padding:5px 7px}.catalog-inventory .catalog-variant-pill{max-width:52%;min-height:29px;padding:0 9px;font-size:8px}}

/* Final brand-logo sizing: this comes after the inventory rules so it cannot be clipped by older card styles. */
.catalog-inventory .catalog-brand-logo{display:flex;align-items:center;justify-content:flex-start;flex:0 1 52%;width:52%;max-width:52%;min-width:0;height:54px;padding:7px 12px;border:1px solid #e1e5ea;border-radius:14px;background:linear-gradient(145deg,#fff 0%,#f3f5f7 100%);box-shadow:0 5px 14px rgba(20,28,38,.08),inset 0 1px 0 rgba(255,255,255,.95);overflow:hidden}
.catalog-inventory .catalog-brand-logo img{display:block;flex:none;width:100%;max-width:100%;height:100%;max-height:none;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;object-fit:contain;object-position:left center;filter:drop-shadow(0 1px 1px rgba(0,0,0,.08))}
.catalog-inventory .catalog-brand-row .catalog-variant-pill{flex:0 1 42%;width:42%;max-width:42%;min-height:34px}
@media(max-width:800px){.catalog-inventory .catalog-brand-logo{flex-basis:52%;width:52%;max-width:52%;height:46px;padding:6px 9px;border-radius:12px}.catalog-inventory .catalog-brand-logo img{width:100%;max-width:100%;height:100%;max-height:none;padding:0}.catalog-inventory .catalog-brand-row .catalog-variant-pill{flex-basis:44%;width:44%;max-width:44%;min-height:30px}}
@media(max-width:420px){.catalog-inventory .catalog-brand-logo{height:48px;padding-inline:8px}}
/* Premium split technical badge: scoped so existing status pills remain untouched. */
.vehicle-tech-badge{display:inline-flex;align-items:stretch;height:38px;max-width:56%;min-width:0;overflow:hidden;border-radius:9px;background:#15171a;box-shadow:0 6px 16px rgba(0,0,0,.16),0 3px 8px rgba(229,31,42,.1);white-space:nowrap}
.vehicle-tech-badge__label,.vehicle-tech-badge__value{display:flex;align-items:center;justify-content:center;min-width:0;color:#fff;text-transform:uppercase;font-size:11px;font-weight:800;line-height:1;letter-spacing:.08em}
.vehicle-tech-badge__label{position:relative;z-index:1;flex:0 0 auto;padding:0 22px 0 16px;background:#e51f2a;clip-path:polygon(0 0,100% 0,88% 100%,0 100%);letter-spacing:.1em}
.vehicle-tech-badge__value{flex:1 1 auto;padding:0 16px 0 8px;background:#17191d;overflow:hidden;text-overflow:ellipsis;letter-spacing:.06em}
.catalog-card:hover .vehicle-tech-badge{box-shadow:0 8px 18px rgba(0,0,0,.18),0 4px 9px rgba(229,31,42,.12)}
.vehicle-tech-badge--long{flex:1 1 100%;max-width:100%}
.catalog-inventory .catalog-brand-logo{display:flex;align-items:center;justify-content:flex-start;flex:0 1 40%;width:40%;max-width:40%;height:36px;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;overflow:visible}
.catalog-inventory .catalog-brand-logo img{width:100%;max-width:100%;height:100%;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
@media(max-width:1450px){.catalog-brand-row{flex-wrap:wrap}.vehicle-tech-badge{max-width:100%;flex:1 1 100%}}
@media(max-width:900px){.vehicle-tech-badge{max-width:100%;flex:1 1 100%;height:36px}.vehicle-tech-badge__label,.vehicle-tech-badge__value{font-size:10px}.vehicle-tech-badge__label{padding-left:13px;padding-right:19px}.vehicle-tech-badge__value{padding-right:12px}}
@media(max-width:600px){.catalog-brand-row{flex-wrap:wrap;align-items:center}.vehicle-tech-badge{max-width:100%;flex:1 1 100%;height:34px}.vehicle-tech-badge__label,.vehicle-tech-badge__value{font-size:9px}.vehicle-tech-badge__label{padding-left:12px;padding-right:18px}.vehicle-tech-badge__value{padding-left:8px;padding-right:11px}}

/* Brand artwork itself stays transparent; the wrapper only reserves alignment space. */
.catalog-inventory .catalog-brand-logo{height:54px;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;overflow:visible}
.catalog-inventory .catalog-brand-logo img{width:100%;max-width:100%;height:100%;max-height:none;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none;filter:none}
@media(max-width:800px){.catalog-inventory .catalog-brand-logo{height:46px}}
@media(max-width:420px){.catalog-inventory .catalog-brand-logo{height:48px}}

/* Public dark footer: continues the catalog background without changing catalog cards or pagination. */
.catalog-page:has(+ .catalog-footer-shell){padding-bottom:0}
.catalog-footer-shell{width:100vw;margin-left:calc(50% - 50vw);color:#fff;border-top:1px solid rgba(255,255,255,.1);background:radial-gradient(circle at 82% 0%,rgba(229,29,42,.13),transparent 31%),linear-gradient(180deg,#151a21 0%,#10141a 100%)}
.catalog-footer-wrap{width:min(94vw,1680px);margin:0 auto}
.catalog-assistance{display:flex;align-items:center;justify-content:space-between;gap:34px;padding:32px 36px;border:1px solid rgba(239,31,47,.38);border-radius:18px;background:linear-gradient(135deg,#20262e,#171c23);box-shadow:0 18px 45px rgba(0,0,0,.3)}
.catalog-assistance-copy{min-width:0}.catalog-footer-kicker{display:block;margin-bottom:10px;color:#f0212e;font-size:11px;font-weight:850;letter-spacing:.24em}.catalog-assistance h2{margin:0 0 8px;color:#fff;font-size:clamp(22px,2.2vw,34px);line-height:1.1;letter-spacing:-.02em}.catalog-assistance p{max-width:720px;margin:0;color:rgba(255,255,255,.62);font-size:14px;line-height:1.65}
.catalog-footer-cta{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;gap:18px;min-height:54px;padding:0 24px;border-radius:11px;background:#e51d2a;color:#fff;font-size:13px;font-weight:850;letter-spacing:.04em;box-shadow:0 8px 20px rgba(229,29,42,.2);transition:background .2s ease,transform .2s ease}.catalog-footer-cta span:last-child{font-size:22px;transition:transform .2s ease}.catalog-footer-cta:hover{background:#f32635;color:#fff;transform:translateY(-1px)}.catalog-footer-cta:hover span:last-child{transform:translateX(3px)}
.catalog-footer{margin-top:42px;padding:72px 0 30px;border-top:1px solid rgba(255,255,255,.1);background:rgba(7,10,14,.16)}.catalog-footer-accent{width:104px;height:2px;margin-bottom:30px;background:#e51d2a}.catalog-footer-grid{display:grid;grid-template-columns:1.45fr repeat(3,1fr);gap:0}.catalog-footer-brand{min-width:0;padding-right:42px}.catalog-footer-logo{display:inline-flex;align-items:center;height:52px;margin-bottom:18px}.catalog-footer-logo img{display:block;width:auto;height:52px;max-width:220px;object-fit:contain}.catalog-footer-brand>strong{display:block;color:#fff;font-size:18px;letter-spacing:.08em}.catalog-footer-brand>strong em{color:#f0212e;font-style:normal}.catalog-footer-tagline{display:block;margin-top:7px;color:rgba(255,255,255,.46);font-size:10px;font-weight:750;letter-spacing:.18em}.catalog-footer-brand p{max-width:330px;margin:18px 0 18px;color:rgba(255,255,255,.55);font-size:13px;line-height:1.7}.catalog-footer-contact{display:inline-flex;align-items:center;gap:8px;color:#fff;font-size:12px;font-weight:750}.catalog-footer-contact span{color:#4ade80;font-size:20px}.catalog-footer-contact:hover{color:#f0212e}
.catalog-footer-links{display:flex;flex-direction:column;align-items:flex-start;gap:12px;min-height:190px;padding:0 34px;border-left:1px solid rgba(255,255,255,.11)}.catalog-footer-links h3{margin:5px 0 9px;color:#fff;font-size:12px;letter-spacing:.17em}.catalog-footer-links a{color:rgba(255,255,255,.62);font-size:13px;line-height:1.35;transition:color .2s ease,transform .2s ease}.catalog-footer-links a:hover{color:#f0212e;transform:translateX(2px)}
.catalog-footer-bottom{display:flex;justify-content:space-between;gap:20px;margin-top:58px;padding-top:22px;border-top:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.42);font-size:11px}.catalog-footer-bottom a{color:rgba(255,255,255,.62)}.catalog-footer-bottom a:hover{color:#f0212e}
@media(max-width:900px){.catalog-assistance{align-items:flex-start}.catalog-footer-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:0}.catalog-footer-brand{padding-right:28px}.catalog-footer-links{min-height:170px;padding:0 24px}.catalog-footer-links:nth-child(3){border-top:1px solid rgba(255,255,255,.11);padding-top:28px}.catalog-footer-links:nth-child(4){border-top:1px solid rgba(255,255,255,.11);padding-top:28px}}
@media(max-width:600px){.catalog-footer-wrap{width:auto;margin:0 13px}.catalog-assistance{display:block;padding:26px 22px}.catalog-assistance p{font-size:13px}.catalog-footer-cta{width:100%;margin-top:23px}.catalog-footer{padding:55px 0 25px}.catalog-footer-grid{grid-template-columns:1fr;gap:0}.catalog-footer-brand{padding:0 0 30px}.catalog-footer-links{min-height:0;padding:25px 0;border-left:0;border-top:1px solid rgba(255,255,255,.11)}.catalog-footer-links:nth-child(3),.catalog-footer-links:nth-child(4){padding-top:25px}.catalog-footer-logo{height:48px}.catalog-footer-logo img{height:48px}.catalog-footer-bottom{display:flex;flex-direction:column;gap:10px;margin-top:42px;line-height:1.5}}

</style>
@endpush

@push('scripts')
<script>
(() => {
    const storageKey = 'yza_favorite_cars';
    const readFavorites = () => {
        try { const value = JSON.parse(localStorage.getItem(storageKey) || '[]'); return Array.isArray(value) ? value.map(String) : []; } catch (error) { return []; }
    };
    const writeFavorites = (favorites) => { try { localStorage.setItem(storageKey, JSON.stringify(favorites)); } catch (error) {} };
    document.querySelectorAll('[data-favorite-id]').forEach((heart) => {
        const id = String(heart.dataset.favoriteId);
        const name = heart.dataset.favoriteName || 'mobil';
        const setState = (active) => {
            heart.textContent = active ? '♥' : '♡';
            heart.classList.toggle('is-favorited', active);
            heart.setAttribute('aria-pressed', String(active));
            heart.setAttribute('aria-label', `${active ? 'Hapus' : 'Tambahkan'} ${name} ${active ? 'dari' : 'ke'} favorit`);
        };
        setState(readFavorites().includes(id));
        heart.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const favorites = readFavorites();
            const active = !favorites.includes(id);
            writeFavorites(active ? [...favorites, id] : favorites.filter((favorite) => favorite !== id));
            setState(active);
        });
    });
})();
</script>
@endpush
