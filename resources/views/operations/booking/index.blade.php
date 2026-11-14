@extends('layouts.app')

@section('title', 'Booking Mobil · YZA Automotive')
@section('admin-page-title', 'Booking Mobil')

@section('content')
@php
    $detailRoute = in_array($role, ['admin', 'super_admin'], true) ? 'admin.booking.show' : 'petugas.booking.show';
    $actionRoute = $role === 'admin' ? 'admin.booking.action' : 'petugas.booking.action';
    $statusLabels = ['menunggu' => 'Menunggu', 'dikonfirmasi' => 'Dikonfirmasi', 'kadaluarsa' => 'Kadaluarsa', 'dibatalkan' => 'Dibatalkan', 'selesai' => 'Selesai'];
    $statusTone = ['menunggu' => 'warning', 'dikonfirmasi' => 'info', 'kadaluarsa' => 'danger', 'dibatalkan' => 'danger', 'selesai' => 'success'];
    $hasFilters = $search !== '' || $status !== '' || $from !== '' || $to !== '';
    $vehicleImage = function ($car, $photo) {
        if ($photo && $photo->existsOnDisk()) return $photo->url();
        if (!$car) return asset('assets/images/vehicles/avanza/gray/front.png');
        $slugs = array_filter([
            $car->showcase_slug ?? null,
            \Illuminate\Support\Str::slug(trim(($car->merk ?? '').' '.($car->model ?? ''))),
            \Illuminate\Support\Str::slug((string) ($car->model ?? '')),
        ]);
        foreach ($slugs as $slug) {
            $matches = glob(public_path('assets/images/vehicles/'.$slug.'/*/front.png')) ?: [];
            if ($matches) return asset(str_replace('\\', '/', substr($matches[0], strlen(public_path()) + 1)));
        }
        return asset('assets/images/vehicles/avanza/gray/front.png');
    };
@endphp
<div class="admin-booking-page">
    <div class="booking-breadcrumb"><span>⌂</span><b>›</b><span>Layanan &amp; Operasional</span><b>›</b><strong>Booking Mobil</strong></div>
    <div class="booking-page-header"><div><span class="booking-eyebrow">LAYANAN &amp; OPERASIONAL</span><h1>Booking Mobil</h1><p>Kelola data booking mobil dari pelanggan. Pantau status, atur jadwal, dan kelola setiap pemesanan dengan mudah.</p></div></div>
    <section class="booking-hero"><div class="booking-hero-copy"><span>BOOKING MANAGEMENT</span><h2>Kelola <em>Booking Mobil</em> dengan Mudah</h2><p>Pantau semua pemesanan mobil dari pelanggan, mulai dari booking baru, konfirmasi, hingga penyelesaian dalam satu tempat yang terintegrasi.</p></div><div class="booking-hero-points"><div><b>▣</b><span>Proses booking lebih efisien<br>dan terorganisir</span></div><div><b>♢</b><span>Pantau status secara<br>terintegrasi</span></div><div><b>▥</b><span>Tingkatkan kepuasan<br>pelanggan</span></div></div></section>
    <section class="booking-stats" aria-label="Ringkasan booking">
        <div class="booking-stat tone-red"><span class="booking-stat-icon">▣</span><div><small>Total Booking</small><strong>{{ $bookingStats['total'] }}</strong><p>Seluruh data booking</p></div><i>↗</i></div>
        <div class="booking-stat tone-amber"><span class="booking-stat-icon">◷</span><div><small>Menunggu Konfirmasi</small><strong>{{ $bookingStats['waiting'] }}</strong><p>Perlu tindakan admin</p></div><i>↗</i></div>
        <div class="booking-stat tone-blue"><span class="booking-stat-icon">⚙</span><div><small>Dikonfirmasi</small><strong>{{ $bookingStats['confirmed'] }}</strong><p>Sedang dalam proses</p></div><i>↗</i></div>
        <div class="booking-stat tone-rose"><span class="booking-stat-icon">×</span><div><small>Kadaluarsa / Dibatalkan</small><strong>{{ $bookingStats['inactive'] }}</strong><p>Booking tidak aktif</p></div><i>↗</i></div>
        <div class="booking-stat tone-green"><span class="booking-stat-icon">✓</span><div><small>Selesai</small><strong>{{ $bookingStats['completed'] }}</strong><p>Booking berhasil</p></div><i>↗</i></div>
    </section>
    <section class="booking-panel">
        <div class="booking-panel-heading"><div class="booking-section-icon">▣</div><div><h2>Daftar Booking Mobil</h2><p>Daftar semua pemesanan mobil dari pelanggan beserta statusnya.</p></div><span class="booking-total">{{ $rows->total() }} booking</span></div>
        <form class="booking-filter-bar" method="get" action="{{ route('admin.booking.index') }}">
            <label class="booking-search"><span>⌕</span><input name="q" value="{{ $search }}" placeholder="Cari kode booking, nama pelanggan, atau mobil..." aria-label="Cari booking"></label>
            <select name="status" aria-label="Filter status"><option value="">Semua Status</option>@foreach($statuses as $option)<option value="{{ $option }}" @selected($status === $option)>{{ $statusLabels[$option] }}</option>@endforeach</select>
            <label class="booking-date"><span>▣</span><input type="date" name="from" value="{{ $from }}" aria-label="Tanggal mulai"></label><span class="booking-date-separator">—</span><label class="booking-date"><span>▣</span><input type="date" name="to" value="{{ $to }}" aria-label="Tanggal selesai"></label>
            <button class="booking-apply" type="submit">⌯ &nbsp; Terapkan</button><a class="booking-reset" href="{{ route('admin.booking.index') }}">↻ &nbsp; Reset</a>
        </form>
        <div class="booking-table-wrap"><table class="booking-table"><thead><tr><th>Kode Booking</th><th>Pelanggan</th><th>Mobil</th><th>Tanggal Booking</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse($rows as $booking)
            @php($photo = $booking->mobil?->foto?->first())
            <tr><td><strong class="booking-code">{{ $booking->kode_booking }}</strong><small>#{{ $booking->id_booking }}</small></td><td><strong>{{ $booking->pelanggan?->nama_lengkap ?? 'Pelanggan' }}</strong><small>Member #{{ $booking->id_pelanggan }}</small></td><td><div class="booking-car"><span class="booking-car-thumb"><img src="{{ $vehicleImage($booking->mobil, $photo) }}" alt="{{ $booking->mobil?->merk }} {{ $booking->mobil?->model }}" loading="lazy"></span><span><strong>{{ trim(($booking->mobil?->merk ?? '').' '.($booking->mobil?->model ?? 'Mobil')) }}</strong><small>{{ $booking->mobil?->kode_mobil ?? 'Kendaraan' }}</small></span></div></td><td><strong>{{ optional($booking->tanggal_booking)->format('d M Y') }}</strong><small>{{ optional($booking->tanggal_booking)->format('H:i') }} WIB</small></td><td><span class="booking-status status-{{ $statusTone[$booking->status] ?? 'info' }}"><i></i>{{ $statusLabels[$booking->status] ?? ucfirst($booking->status) }}</span></td><td><div class="booking-actions"><a class="booking-detail" href="{{ route($detailRoute, $booking) }}">◉ &nbsp; Detail</a>@if($booking->status === 'menunggu' && in_array($role, ['admin','petugas'], true))<details class="booking-more"><summary>•••</summary><div><form method="post" action="{{ route($actionRoute, [$booking, 'confirm']) }}">@csrf<button type="submit">Konfirmasi booking</button></form></div></details>@endif</div></td></tr>
        @empty
            <tr><td class="booking-empty" colspan="6"><span>⌕</span><strong>{{ $hasFilters ? 'Tidak ada booking yang cocok' : 'Belum ada booking mobil' }}</strong><small>{{ $hasFilters ? 'Coba ubah kata kunci atau filter yang dipilih.' : 'Data booking pelanggan akan tampil di sini.' }}</small></td></tr>
        @endforelse
        </tbody></table></div>
        <div class="booking-pagination"><span>Menampilkan <b>{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</b> dari <b>{{ $rows->total() }}</b> booking</span><div><label>Per halaman <select onchange="window.location=this.value"><option value="{{ request()->fullUrlWithQuery(['perPage'=>10,'page'=>1]) }}" @selected($perPage===10)>10</option><option value="{{ request()->fullUrlWithQuery(['perPage'=>20,'page'=>1]) }}" @selected($perPage===20)>20</option><option value="{{ request()->fullUrlWithQuery(['perPage'=>50,'page'=>1]) }}" @selected($perPage===50)>50</option></select></label>{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div></div>
    </section>
</div>
@endsection
@push('scripts')<script>document.querySelectorAll('.booking-table form').forEach(function(form){form.addEventListener('submit',function(){var button=form.querySelector('button');if(button){button.disabled=true;button.textContent='Memproses…';}});});</script>@endpush
