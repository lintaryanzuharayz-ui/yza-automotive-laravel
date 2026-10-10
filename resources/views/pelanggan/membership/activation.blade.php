@php
    $isActive = $membership->status === 'active';
    $providerStatus = strtolower((string) ($membership->gatewayPayment?->status_provider ?? ''));
    $isFailed = in_array($providerStatus, ['failed', 'deny', 'denied', 'cancel', 'cancelled', 'expired', 'expire'], true);
    $isProcessing = in_array($providerStatus, ['settlement', 'capture', 'processing', 'paid'], true);
    $statusKey = $isActive ? 'active' : ($isFailed ? 'failed' : ($isProcessing ? 'processing' : 'pending'));
    $statusLabel = match ($statusKey) {
        'active' => 'TERVERIFIKASI',
        'failed' => 'PEMBAYARAN GAGAL',
        'processing' => 'SEDANG DIPROSES',
        default => 'MENUNGGU PEMBAYARAN',
    };
    $statusTitle = match ($statusKey) {
        'active' => 'Membership Aktif',
        'failed' => 'Pembayaran Tidak Berhasil',
        'processing' => 'Pembayaran Diproses',
        default => 'Menunggu Pembayaran',
    };
    $statusDescription = match ($statusKey) {
        'active' => 'Aktivasi membership Anda sudah berhasil diverifikasi dan seluruh benefit sudah dapat digunakan.',
        'failed' => 'Pembayaran terakhir belum berhasil. Silakan ulangi proses melalui payment gateway resmi.',
        'processing' => 'Pembayaran sedang diproses oleh payment gateway. Periksa status kembali jika belum berubah.',
        default => 'Selesaikan pembayaran sekali aktivasi untuk mengakses layanan dan benefit eksklusif.',
    };
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $isActive ? 'YZA Member Aktif' : 'Pembayaran YZA Member' }} · YZA Automotive</title>
    <link rel="icon" href="{{ asset('assets/images/branding/logo-yza.png') }}">
    @foreach (['variables.css','base.css','components.css','pages/login.css','pages/pelanggan.css','pages/register.css'] as $style)
        <link rel="stylesheet" href="{{ asset('assets/css/'.$style) }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('assets/css/pages/membership-flow.css?v=membership-flow-20261004') }}">
</head>
<body>
<main class="register-page membership-payment-page">
    <section class="register-showcase membership-payment-showcase" aria-label="YZA Automotive membership"></section>
    <section class="card register-card membership-payment-card">
        <div class="register-card-heading"><span class="eyebrow">YZA AUTOMOTIVE MEMBERSHIP</span><span class="register-heading-line"></span></div>
        <h1>{{ $isActive && request()->boolean('finish') ? 'Membership Berhasil Diaktifkan' : 'Pembayaran Aktivasi' }} <em>YZA Member</em></h1>
        <p class="intro">Selesaikan pembayaran sekali aktivasi untuk mengakses layanan dan benefit eksklusif YZA Automotive.</p>
        @if($errors->any())<div class="alert alert-error" role="alert">{{ $errors->first() }}</div>@endif
        <div class="register-steps membership-payment-steps" aria-label="Progress membership">
            <span class="complete"><b>01</b>Data Diri</span><i></i>
            <span class="complete"><b>02</b>Alamat</span><i></i>
            <span class="complete"><b>03</b>Membership</span><i></i>
            <span class="{{ $isActive ? 'complete' : 'active' }}"><b>04</b>Pembayaran</span><i></i>
            <span class="{{ $isActive ? 'complete' : '' }}"><b>05</b>Selesai</span>
        </div>
        <article class="membership-payment-summary">
            <span class="eyebrow">RINGKASAN MEMBERSHIP</span>
            <h2>YZA Member</h2>
            <div class="membership-payment-price">Rp {{ number_format((float) $membership->activation_fee, 0, ',', '.') }} <small>sekali aktivasi</small></div>
            <p>Tanpa biaya bulanan. Membership aktif setelah pembayaran berhasil diverifikasi.</p>
            <ul class="membership-payment-benefits">
                <li>Promo eksklusif</li><li>YZA Points</li><li>Service reminder</li>
                <li>Test drive &amp; kendaraan</li><li>Trade-in terintegrasi</li><li>Akses layanan member</li>
            </ul>
        </article>
        <article class="membership-payment-status is-{{ $statusKey }}">
            <span class="eyebrow">STATUS PEMBAYARAN</span>
            <h2>{{ $statusTitle }}</h2>
            <div class="membership-status-badge {{ $statusKey === 'active' ? 'is-active' : 'is-'.$statusKey }}">{{ $statusLabel }}</div>
            <p>{{ $statusDescription }}</p>
        </article>
        <article class="membership-payment-member">
            <span class="eyebrow">DATA MEMBER</span>
            <div class="membership-payment-member-grid">
                <div><span>Nama</span><strong>{{ $membership->customer->nama_lengkap }}</strong></div>
                <div><span>Email</span><strong>{{ $membership->customer->user?->email }}</strong></div>
                <div><span>Member ID</span><strong>{{ $membership->member_id }}</strong></div>
            </div>
        </article>
        <div class="membership-payment-actions">
            @if($isActive)
                @if(request()->boolean('finish'))<p class="membership-success-copy"><strong>05 SELESAI</strong> · Selamat, akun Anda sekarang aktif sebagai YZA Member.</p>@endif
                <p class="membership-success-copy">Aktivasi terverifikasi pada {{ optional($membership->activated_at)->format('d M Y, H:i') }}.</p>
                <a class="button button-primary membership-payment-cta" href="{{ route('pelanggan.dashboard') }}">MASUK KE AKUN&nbsp; →</a>
            @elseif($membership->gatewayPayment?->status_provider === 'pending')
                <p class="membership-success-copy">Jika Anda sudah menyelesaikan pembayaran, periksa statusnya melalui tombol berikut.</p>
                <div class="form-actions">
                    <form method="post" action="{{ route('pelanggan.membership.sync') }}" data-membership-payment-form>@csrf<button class="button button-primary" type="submit">CEK STATUS PEMBAYARAN</button></form>
                    <form method="post" action="{{ route('pelanggan.membership.pay') }}" data-membership-payment-form>@csrf<button class="button button-secondary" type="submit">LANJUTKAN PEMBAYARAN</button></form>
                </div>
            @else
                <form method="post" action="{{ route('pelanggan.membership.pay') }}" data-membership-payment-form>@csrf<button class="button button-primary membership-payment-cta" type="submit">BAYAR &amp; AKTIFKAN MEMBER&nbsp; →</button></form>
            @endif
        </div>
    </section>
</main>
<script>
document.querySelectorAll('[data-membership-payment-form]').forEach((form) => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.textContent = 'MEMPROSES PEMBAYARAN...';
}));
</script>
</body>
</html>
