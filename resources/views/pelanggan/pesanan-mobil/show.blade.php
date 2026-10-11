@extends('layouts.app')
@section('title', 'Detail Pesanan '.$order->nomor_pesanan.' - YZA Automotive')
@section('body-class', 'member-order-detail-page')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/member-order-detail.css') }}?v={{ filemtime(public_path('assets/css/member-order-detail.css')) }}">
@endpush
@php
    $statusLabel = ucwords(str_replace('_', ' ', $order->status));
    $paymentStatus = $order->transaksi?->status ? ucwords(str_replace('_', ' ', $order->transaksi->status)) : 'Menunggu persetujuan';
    $pdiStatus = $order->pdi?->status ? ucwords(str_replace('_', ' ', $order->pdi->status)) : 'Belum diperiksa';
    $pickupStatus = $order->jadwal_pengambilan?->format('d M Y H:i').' WIB' ?: 'Belum dijadwalkan';
    $handoverStatus = $order->diserahkan_at?->format('d M Y H:i').' WIB' ?: 'Belum selesai';
    $vehicleName = trim($order->snapshot_merk.' '.$order->snapshot_model);
    $vehicleMeta = implode(' | ', array_filter([$order->snapshot_tipe ?: 'Varian standar', $order->snapshot_tahun ?: '-', $order->snapshot_warna ?: '-']));
    $price = 'Rp '.number_format((float) $order->total_tagihan, 0, ',', '.');
    $timeline = [
        ['label' => 'Checkout', 'value' => 'Selesai', 'done' => true],
        ['label' => 'Verifikasi Admin', 'value' => $order->diputuskan_at?->format('d M Y') ?: 'Menunggu', 'done' => (bool) $order->diputuskan_at],
        ['label' => 'Pembayaran', 'value' => $paymentStatus, 'done' => in_array($order->transaksi?->status, ['dp', 'lunas'], true)],
        ['label' => 'PDI', 'value' => $pdiStatus, 'done' => $order->pdi?->status === 'lulus'],
        ['label' => 'Pengambilan', 'value' => $pickupStatus, 'done' => (bool) $order->jadwal_pengambilan],
        ['label' => 'Selesai', 'value' => $handoverStatus, 'done' => (bool) $order->diserahkan_at],
    ];
@endphp
@section('content')
<div class="order-detail-wrap">
    <header class="order-detail-heading">
        <a class="order-back-link" href="{{ route('pelanggan.pesanan-mobil.index') }}">&larr; Kembali ke Daftar Pesanan</a>
        <div class="order-heading-row"><div><div class="order-number-row"><h1>{{ $order->nomor_pesanan }}</h1><span class="order-status-badge">{{ $statusLabel }}</span></div><p>Dibuat pada {{ $order->created_at?->format('d F Y') ?: '-' }} &middot; {{ $order->created_at?->format('H:i') ?: '--:--' }} WIB</p></div><section class="order-next-card order-next-card-top"><div class="order-next-icon">!</div><div><h2>Langkah Selanjutnya</h2><p>@if($order->status === 'menunggu_verifikasi')Pesanan menunggu verifikasi admin.@elseif($order->status === 'menunggu_pembayaran')Silakan lanjutkan pembayaran.@elseif($order->status === 'lunas')Tim kami melanjutkan proses PDI.@else>Status akan diperbarui oleh tim YZA Automotive.@endif</p></div></section></div>
    </header>
    <section class="order-timeline" aria-label="Tahapan pesanan">
        @foreach($timeline as $index => $step)<div class="order-timeline-step {{ $step['done'] ? 'is-done' : ($index === 1 ? 'is-current' : '') }}"><span class="order-timeline-dot">{{ $index + 1 }}</span><strong>{{ $step['label'] }}</strong><small>{{ $step['value'] }}</small></div>@endforeach
    </section>
    <main class="order-detail-grid">
        <section class="order-vehicle-card order-vehicle-panel">
        <section class="order-vehicle-media order-gallery-card">
            <button class="order-gallery-arrow order-gallery-arrow-prev" type="button" aria-label="Foto sebelumnya">&#8249;</button>
            <img id="order-main-vehicle-image" src="{{ $vehicleImage }}" alt="{{ $vehicleName }}">
            <button class="order-gallery-arrow order-gallery-arrow-next" type="button" aria-label="Foto berikutnya">&#8250;</button>
            <div class="order-vehicle-thumbs" role="list" aria-label="Galeri {{ $order->snapshot_warna }}">@foreach($selectedGallery['images'] ?? [] as $index => $image)<button class="order-vehicle-thumb {{ $index === 0 ? 'is-active' : '' }}" type="button" data-order-image="{{ $image['src'] }}" data-order-label="{{ $image['label'] }}" aria-label="Lihat {{ $image['label'] }}"><img src="{{ $image['src'] }}" alt="{{ $image['label'] }} {{ $vehicleName }}"></button>@endforeach</div>
        </section>
        <section class="order-vehicle-copy">
            <div class="order-brand-row"><span class="order-eyebrow">KENDARAAN PESANAN</span><span class="order-condition">UNIT BARU</span></div><h2>{{ $vehicleName }}</h2><p>{{ $vehicleMeta }}</p><strong class="order-vehicle-price">{{ $price }}</strong><div class="order-spec-grid"><div><span>Mesin</span><strong>{{ $order->mobil?->kapasitas_mesin ?: '3.0L Turbo 6-Cylinder' }}</strong></div><div><span>Transmisi</span><strong>{{ strtoupper($order->mobil?->transmisi ?: 'Automatic') }}</strong></div><div><span>Tipe &amp; Warna</span><strong>{{ $order->snapshot_tipe ?: '-' }} · {{ $order->snapshot_warna ?: '-' }}</strong></div></div>
        </section>
        </section>
        <div class="order-right-column">
        <section class="order-info-card order-buyer-card"><div class="order-card-title"><span class="order-card-icon">USER</span><div><h2>Informasi Pembeli</h2><p>Data yang digunakan untuk proses pesanan.</p></div></div><dl class="order-info-list"><div><dt>Nama Lengkap</dt><dd>{{ $order->nama_pembeli }}</dd></div><div><dt>Email</dt><dd>{{ $order->email_pembeli ?: '-' }}</dd></div><div><dt>Nomor HP</dt><dd>{{ $order->telepon_pembeli ?: '-' }}</dd></div><div><dt>Alamat</dt><dd>{{ $order->alamat_pembeli ?: '-' }}</dd></div></dl></section>
        <section class="order-payment-card order-payment-summary"><div class="order-card-title"><span class="order-card-icon">PAY</span><div><h2>Ringkasan Pembayaran</h2><p>Nilai transaksi berdasarkan harga server.</p></div></div><div class="order-payment-row"><span>Harga Kendaraan</span><strong>{{ $price }}</strong></div><div class="order-payment-total"><span>Total</span><strong>{{ $price }}</strong></div>
                @if($order->transaksi && in_array($order->status, ['menunggu_pembayaran', 'lunas']))
                    <form method="post" action="{{ route('pelanggan.midtrans.create', $order->transaksi) }}">@csrf<button class="order-primary-button" type="submit">Bayar melalui Midtrans <span>&rarr;</span></button></form>
                    @if($order->transaksi->status !== 'lunas')
                        <form method="post" action="{{ route('pelanggan.pesanan-mobil.payment.offline', $order) }}" enctype="multipart/form-data" class="order-offline-payment-form">
                            @csrf
                            <label for="offline-payment-method">Pembayaran offline</label>
                            <select id="offline-payment-method" name="metode" required><option value="transfer">Transfer langsung</option><option value="tunai">Tunai di dealer</option></select>
                            <div class="order-offline-proof" data-offline-proof>
                                <label for="offline-payment-proof">Bukti transfer</label>
                                <input id="offline-payment-proof" type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.webp,.pdf" aria-label="Bukti transfer">
                            </div>
                            <small data-offline-note>Transfer diverifikasi setelah dana masuk rekening resmi YZA. Bukti transfer wajib diunggah.</small>
                            <button class="order-secondary-button" type="submit">Ajukan Pembayaran Offline</button>
                        </form>
                    @endif
                @elseif($order->status === 'menunggu_verifikasi')
                    <a class="order-primary-button" href="{{ yza_whatsapp_url('Halo Admin YZA Automotive, saya ingin menanyakan pesanan '.$order->nomor_pesanan) }}" target="_blank" rel="noopener">Hubungi Admin <span>&rarr;</span></a>
                @endif
                @if($order->transaksi)
                    <a class="order-secondary-button" href="{{ route('pelanggan.invoice.car-sale', $order->transaksi) }}">Lihat Invoice</a>
                @endif
        </section>
        </div>
        <section class="order-info-card order-order-info"><div class="order-card-title"><span class="order-card-icon">DOC</span><div><h2>Informasi Pesanan</h2><p>Detail snapshot pesanan yang tersimpan saat checkout.</p></div></div><dl class="order-info-list"><div><dt>Nomor Pesanan</dt><dd>{{ $order->nomor_pesanan }}</dd></div><div><dt>Tanggal Pesanan</dt><dd>{{ $order->created_at?->format('d F Y, H:i') ?: '-' }} WIB</dd></div><div><dt>Status</dt><dd><span class="order-inline-status">{{ $statusLabel }}</span></dd></div><div><dt>Metode Pembayaran</dt><dd>{{ $order->transaksi?->metode_pembayaran ?: 'Menunggu verifikasi admin' }}</dd></div></dl></section>
        <section class="order-info-card order-vehicle-summary-card"><div class="order-card-title"><span class="order-card-icon">CAR</span><div><h2>Kendaraan Pesanan</h2><p>Detail spesifikasi kendaraan yang dipesan.</p></div></div><dl class="order-info-list"><div><dt>Nama Kendaraan</dt><dd>{{ $vehicleName }}</dd></div><div><dt>Mesin</dt><dd>{{ $order->mobil?->kapasitas_mesin ?: '3.0L Turbo 6-Cylinder' }}</dd></div><div><dt>Transmisi</dt><dd>{{ strtoupper($order->mobil?->transmisi ?: 'Automatic') }}</dd></div><div><dt>Tipe</dt><dd>{{ $order->snapshot_tipe ?: '-' }}</dd></div><div><dt>Warna</dt><dd>{{ $order->snapshot_warna ?: '-' }}</dd></div></dl></section>
    </main>
</div>
@endsection
@push('scripts')
<script>
(() => {
    const main = document.getElementById('order-main-vehicle-image');
    const thumbs = [...document.querySelectorAll('.order-vehicle-thumb')];
    if (!main || !thumbs.length) return;
    let activeIndex = Math.max(0, thumbs.findIndex(thumb => thumb.classList.contains('is-active')));
    const showImage = (index) => {
        activeIndex = (index + thumbs.length) % thumbs.length;
        const thumb = thumbs[activeIndex];
        main.src = thumb.dataset.orderImage;
        main.alt = thumb.dataset.orderLabel + ' {{ $vehicleName }}';
        thumbs.forEach(item => item.classList.toggle('is-active', item === thumb));
    };
    thumbs.forEach((thumb, index) => thumb.addEventListener('click', () => showImage(index)));
    document.querySelector('.order-gallery-arrow-prev')?.addEventListener('click', () => showImage(activeIndex - 1));
    document.querySelector('.order-gallery-arrow-next')?.addEventListener('click', () => showImage(activeIndex + 1));
})();
(() => {
    const method = document.getElementById('offline-payment-method');
    const proof = document.querySelector('[data-offline-proof]');
    const proofInput = document.getElementById('offline-payment-proof');
    const note = document.querySelector('[data-offline-note]');
    if (!method || !proof || !proofInput || !note) return;
    const syncOfflineFields = () => {
        const isTransfer = method.value === 'transfer';
        proof.hidden = !isTransfer;
        proofInput.required = isTransfer;
        if (!isTransfer) proofInput.value = '';
        note.textContent = isTransfer
            ? 'Transfer diverifikasi setelah dana masuk rekening resmi YZA. Bukti transfer wajib diunggah.'
            : 'Pembayaran tunai akan dikonfirmasi petugas setelah uang diterima di dealer.';
    };
    method.addEventListener('change', syncOfflineFields);
    syncOfflineFields();
})();
</script>
@endpush
