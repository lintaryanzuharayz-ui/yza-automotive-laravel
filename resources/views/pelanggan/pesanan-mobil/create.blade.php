@extends('layouts.app')

@section('title', 'Checkout Kendaraan · YZA Automotive')
@section('body-class', 'member-checkout-page')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/member-checkout.css') }}?v={{ filemtime(public_path('assets/css/member-checkout.css')) }}">
@endpush

@php
    $vehicleName = trim($mobil->merk.' '.$mobil->model);
    $vehicleMeta = implode(' · ', array_filter([$mobil->tipe ?: 'Varian standar', $mobil->tahun ?: '-', $mobil->warna ?: 'Warna mengikuti unit']));
    $price = 'Rp '.number_format((float) $mobil->harga_jual, 0, ',', '.');
    $images = $defaultGallery['images'] ?? [];
    $uiSpecs = [
        ['icon' => '◈', 'label' => 'Tipe', 'value' => $mobil->tipe ?: 'Varian standar'],
        ['icon' => '◌', 'label' => 'Tahun', 'value' => $mobil->tahun ?: '-'],
        ['icon' => '⚙', 'label' => 'Transmisi', 'value' => strtoupper($mobil->transmisi ?: '-')],
        ['icon' => '●', 'label' => 'Warna', 'value' => $mobil->warna ?: 'Pilihan tersedia'],
    ];
@endphp

@section('content')
<div class="checkout-wrap">
    <header class="checkout-heading">
        <div>
            <h1>Checkout Kendaraan</h1>
            <p>Lengkapi data pembeli untuk melanjutkan pemesanan kendaraan impian Anda.</p>
        </div>
        <div class="checkout-steps" aria-label="Tahapan checkout">
            <div class="checkout-step is-active"><span class="checkout-step-number">1</span><span>Data Pembeli</span></div>
            <div class="checkout-step"><span class="checkout-step-number">2</span><span>Konfirmasi</span></div>
            <div class="checkout-step"><span class="checkout-step-number">3</span><span>Selesai</span></div>
        </div>
    </header>

    <main class="checkout-main">
        <article class="checkout-vehicle-card">
            <div class="checkout-vehicle-top">
                <span class="checkout-order-pill">Pesanan Baru</span>
                <h2>{{ $vehicleName }}</h2>
                <p class="checkout-vehicle-meta">{{ $vehicleMeta }}</p>
                <img class="checkout-vehicle-image" src="{{ $primaryImage }}" alt="{{ $vehicleName }}">
            </div>
            <div class="checkout-vehicle-body">
                <div class="checkout-dots" aria-label="Galeri kendaraan">
                    @foreach($images as $index => $image)
                        <span class="checkout-dot {{ $index === 0 ? 'is-active' : '' }}"></span>
                    @endforeach
                </div>
                <div class="checkout-specs">
                    @foreach($uiSpecs as $spec)
                        <div class="checkout-spec"><span class="checkout-spec-icon" aria-hidden="true">{{ $spec['icon'] }}</span><span>{{ $spec['label'] }}</span><strong>{{ $spec['value'] }}</strong></div>
                    @endforeach
                </div>
                <div class="checkout-price"><span>Harga Kendaraan</span><strong>{{ $price }}</strong></div>
            </div>
        </article>

        <section class="checkout-form-card">
            <div class="checkout-form-column">
                <div class="checkout-section-title"><span class="checkout-section-icon" aria-hidden="true">♙</span><div><h2>Data Pembeli</h2><p>Mohon lengkapi data diri dengan benar untuk proses pemesanan.</p></div></div>
                @if($errors->any())<div class="checkout-errors" role="alert">{{ $errors->first() }}</div>@endif
                <form id="checkout-form" method="post" action="{{ route('pelanggan.pesanan-mobil.store', $mobil) }}">
                    @csrf
                    <div class="checkout-field"><label for="nama_pembeli">Nama lengkap <em>*</em></label><input id="nama_pembeli" class="checkout-control" name="nama_pembeli" value="{{ old('nama_pembeli', $customer->nama_lengkap) }}" required autocomplete="name"></div>
                    <div class="checkout-field"><label for="email_pembeli">Email</label><input id="email_pembeli" class="checkout-control" type="email" name="email_pembeli" value="{{ old('email_pembeli', $customer->user?->email) }}" autocomplete="email"></div>
                    <div class="checkout-field"><label for="telepon_pembeli">Nomor telepon <em>*</em></label><input id="telepon_pembeli" class="checkout-control" name="telepon_pembeli" value="{{ old('telepon_pembeli', $customer->no_hp) }}" required autocomplete="tel"></div>
                    <div class="checkout-field"><label for="alamat_pembeli">Alamat lengkap <em>*</em></label><textarea id="alamat_pembeli" class="checkout-control" name="alamat_pembeli" required autocomplete="street-address">{{ old('alamat_pembeli', $customer->alamat) }}</textarea></div>
                    <label class="checkout-check" for="terms"><input id="terms" type="checkbox" name="syarat_disetujui" value="1" required @checked(old('syarat_disetujui'))><span>Saya memahami harga dan spesifikasi disimpan sebagai snapshot, lalu menunggu verifikasi admin.</span></label>
                </form>
            </div>
            <aside class="checkout-summary-column">
                <div class="checkout-section-title"><span class="checkout-section-icon" aria-hidden="true">▣</span><div><h2>Ringkasan Pesanan</h2><p>Pastikan detail pesanan Anda sudah benar sebelum dikirim.</p></div></div>
                <div class="checkout-summary-box">
                    <div class="checkout-summary-head"><div><strong>{{ $vehicleName }}</strong><small>{{ $vehicleMeta }}</small></div><span>{{ $price }}</span></div>
                    <div class="checkout-summary-list">
                        @foreach($uiSpecs as $spec)<div class="checkout-summary-row"><span>{{ $spec['label'] }}</span><strong>{{ $spec['value'] }}</strong></div>@endforeach
                        <div class="checkout-summary-row"><span>Ketersediaan</span><strong>{{ $stock['label'] }}</strong></div>
                    </div>
                    <div class="checkout-summary-total"><span>Total Harga</span><strong>{{ $price }}</strong></div>
                </div>
                <button class="checkout-submit" type="submit" form="checkout-form"><span aria-hidden="true">➤</span> Kirim Pesanan <span aria-hidden="true">→</span></button>
                <p class="checkout-privacy"><span aria-hidden="true">🔒</span><span>Data Anda aman dan hanya digunakan untuk proses pemesanan oleh tim YZA Automotive.</span></p>
            </aside>
        </section>
    </main>

    <div class="checkout-trust" aria-label="Jaminan layanan YZA Automotive">
        <div class="checkout-trust-item"><span class="checkout-trust-icon" aria-hidden="true">♢</span><div><strong>Transaksi Aman</strong><span>Data Anda terlindungi dengan enkripsi standar industri.</span></div></div>
        <div class="checkout-trust-item"><span class="checkout-trust-icon" aria-hidden="true">▤</span><div><strong>Verifikasi oleh Admin</strong><span>Setiap pesanan akan diverifikasi oleh tim kami.</span></div></div>
        <div class="checkout-trust-item"><span class="checkout-trust-icon" aria-hidden="true">◉</span><div><strong>Dukungan Member</strong><span>Tim kami siap membantu Anda kapan saja.</span></div></div>
    </div>
</div>
@endsection
