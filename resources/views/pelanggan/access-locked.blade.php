@extends('layouts.app')
@section('title', 'Layanan Eksklusif YZA')
@section('content')
<section class="member-access-locked" aria-labelledby="ownership-lock-title">
    <div class="member-access-lock-icon">🔒</div>
    <span class="eyebrow">YZA VERIFIED OWNER</span>
    <h1 id="ownership-lock-title">Miliki Mobil YZA untuk Menikmati Layanan Eksklusif</h1>
    <p>Layanan ini tersedia khusus bagi pelanggan yang telah membeli mobil melalui YZA Automotive. Temukan mobil impian Anda dan nikmati layanan eksklusif kami.</p>
    <div class="member-access-lock-actions">
        <a class="yza-button" href="{{ $catalogUrl }}">Lihat Koleksi Mobil</a>
        <a class="action-link" href="{{ $backUrl }}">Kembali ke Member Center</a>
    </div>
</section>
@endsection
