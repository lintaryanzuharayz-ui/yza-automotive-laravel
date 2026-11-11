@extends('layouts.app')
@section('title', 'Test Drive · YZA Automotive')
@section('admin-page-title', 'Test Drive')
@section('content')
@php
    $detailRoute = in_array($role, ['admin', 'super_admin'], true) ? 'admin.test-drive.show' : 'petugas.test-drive.show';
    $actionRoute = in_array($role, ['admin', 'super_admin'], true) ? 'admin.test-drive.action' : 'petugas.test-drive.action';
    $statusLabels = ['menunggu' => 'Menunggu', 'disetujui' => 'Dijadwalkan', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'];
    $statusTone = ['menunggu' => 'warning', 'disetujui' => 'info', 'selesai' => 'success', 'ditolak' => 'danger', 'dibatalkan' => 'danger'];
    $hasFilters = $search !== '' || $status !== '' || $date !== '';
    $vehicleImage = function ($car, $photo) {
        if ($photo && $photo->existsOnDisk()) return $photo->url();
        if (!$car) return asset('assets/images/vehicles/avanza/gray/front.png');
        foreach (array_filter([$car->showcase_slug ?? null, \Illuminate\Support\Str::slug(trim(($car->merk ?? '').' '.($car->model ?? ''))), \Illuminate\Support\Str::slug((string) ($car->model ?? ''))]) as $slug) {
            $matches = glob(public_path('assets/images/vehicles/'.$slug.'/*/front.png')) ?: [];
            if ($matches) return asset(str_replace('\\', '/', substr($matches[0], strlen(public_path()) + 1)));
        }
        return asset('assets/images/vehicles/avanza/gray/front.png');
    };
@endphp
<div class="admin-testdrive-page">
    <div class="testdrive-breadcrumb"><span>⌂</span><b>›</b><span>Layanan &amp; Operasional</span><b>›</b><strong>Test Drive</strong></div>
    <div class="testdrive-page-header"><span class="testdrive-eyebrow">LAYANAN &amp; OPERASIONAL</span><h1>Test Drive</h1><p>Kelola jadwal test drive pelanggan, pantau status, dan atur proses pengalaman berkendara dengan mudah.</p></div>
    <section class="testdrive-hero"><div class="testdrive-hero-copy"><span>TEST DRIVE MANAGEMENT</span><h2>Kelola <em>Test Drive</em> dengan Mudah</h2><p>Pantau seluruh pengajuan test drive, jadwal kunjungan, konfirmasi admin, hingga penyelesaian dalam satu tampilan yang terintegrasi.</p></div><div class="testdrive-hero-points"><div><b>▣</b><span>Proses penjadwalan<br>lebih rapi</span></div><div><b>ϟ</b><span>Pantau status<br>lebih cepat</span></div><div><b>♣</b><span>Pengalaman pelanggan<br>lebih premium</span></div></div></section>
    <section class="testdrive-stats" aria-label="Ringkasan test drive">
        <div class="testdrive-stat tone-red"><span>▣</span><div><strong>{{ $testDriveStats['total'] }}</strong><small>Total Pengajuan</small></div><i>↗</i></div>
        <div class="testdrive-stat tone-amber"><span>◷</span><div><strong>{{ $testDriveStats['waiting'] }}</strong><small>Menunggu Konfirmasi</small></div><i>↗</i></div>
        <div class="testdrive-stat tone-blue"><span>▣</span><div><strong>{{ $testDriveStats['scheduled'] }}</strong><small>Dijadwalkan</small></div><i>↗</i></div>
        <div class="testdrive-stat tone-green"><span>✓</span><div><strong>{{ $testDriveStats['completed'] }}</strong><small>Selesai</small></div><i>↗</i></div>
        <div class="testdrive-stat tone-rose"><span>×</span><div><strong>{{ $testDriveStats['cancelled'] }}</strong><small>Dibatalkan / Ditolak</small></div><i>↗</i></div>
    </section>
    <section class="testdrive-panel">
        <div class="testdrive-panel-heading"><div class="testdrive-section-icon">🚘</div><div><h2>Daftar Test Drive</h2><p>Daftar seluruh jadwal test drive pelanggan beserta statusnya.</p></div><span class="testdrive-total">{{ $rows->total() }} pengajuan</span></div>
        <form class="testdrive-filter-bar" method="get" action="{{ route('admin.test-drive.index') }}"><label class="testdrive-search"><span>⌕</span><input name="q" value="{{ $search }}" placeholder="Cari kode test drive, nama pelanggan, atau mobil..." aria-label="Cari test drive"></label><select name="status" aria-label="Filter status"><option value="">Semua Status</option>@foreach($statuses as $option)<option value="{{ $option }}" @selected($status === $option)>{{ $statusLabels[$option] }}</option>@endforeach</select><label class="testdrive-date"><span>▣</span><input type="date" name="date" value="{{ $date }}" aria-label="Pilih tanggal"></label><button class="testdrive-apply" type="submit">⌯ &nbsp; Terapkan</button><a class="testdrive-reset" href="{{ route('admin.test-drive.index') }}">↻ &nbsp; Reset</a></form>
        <div class="testdrive-table-wrap"><table class="testdrive-table"><thead><tr><th>Kode Test Drive</th><th>Pelanggan</th><th>Mobil</th><th>Jadwal</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @forelse($rows as $testDrive)
            @php($photo = $testDrive->mobil?->foto?->first())
            <tr><td><strong>{{ $testDrive->kode_test_drive }}</strong><small>#{{ $testDrive->id_test_drive }}</small></td><td><strong>{{ $testDrive->pelanggan?->nama_lengkap ?? 'Pelanggan' }}</strong><small>Member #{{ $testDrive->id_pelanggan }}</small></td><td><div class="testdrive-car"><span class="testdrive-car-thumb"><img src="{{ $vehicleImage($testDrive->mobil, $photo) }}" alt="{{ $testDrive->mobil?->merk }} {{ $testDrive->mobil?->model }}" loading="lazy"></span><span><strong>{{ trim(($testDrive->mobil?->merk ?? '').' '.($testDrive->mobil?->model ?? 'Mobil')) }}</strong><small>{{ $testDrive->mobil?->kode_mobil ?? 'Kendaraan' }}</small></span></div></td><td><div class="testdrive-schedule"><strong>▣ &nbsp;{{ optional($testDrive->tanggal)->format('d M Y') }}</strong><small>◷ &nbsp;{{ substr((string) $testDrive->jam, 0, 5) }} – {{ date('H:i', strtotime($testDrive->jam.' +1 hour')) }}</small></div></td><td><span class="testdrive-status status-{{ $statusTone[$testDrive->status] ?? 'info' }}"><i></i>{{ $statusLabels[$testDrive->status] ?? ucfirst($testDrive->status) }}</span></td><td><div class="testdrive-actions"><a class="testdrive-detail" href="{{ route($detailRoute, $testDrive) }}">◉ &nbsp; Detail</a>
                @if(in_array($role, ['admin','petugas','operational_staff'], true) && in_array($testDrive->status, ['menunggu','disetujui'], true))
                    <details class="testdrive-more"><summary>•••</summary><div>
                        @if($testDrive->status === 'menunggu')
                            <form method="post" action="{{ route($actionRoute, [$testDrive, 'approve']) }}">@csrf<button type="submit">Setujui jadwal</button></form>
                            <form method="post" action="{{ route($actionRoute, [$testDrive, 'reject']) }}">@csrf<button type="submit">Tolak pengajuan</button></form>
                        @elseif($testDrive->status === 'disetujui')
                            <form method="post" action="{{ route($actionRoute, [$testDrive, 'complete']) }}">@csrf<button type="submit">Tandai selesai</button></form>
                            @if(in_array($role, ['petugas','operational_staff'], true))
                                <form method="post" action="{{ route($actionRoute, [$testDrive, 'cancel']) }}">@csrf<button type="submit">Batalkan jadwal</button></form>
                            @endif
                        @endif
                    </div></details>
                @endif
            </div></td></tr>
        @empty
            <tr><td class="testdrive-empty" colspan="6"><span>⌕</span><strong>{{ $hasFilters ? 'Tidak ada test drive yang cocok' : 'Belum ada jadwal test drive' }}</strong><small>{{ $hasFilters ? 'Coba ubah kata kunci atau filter yang dipilih.' : 'Pengajuan test drive pelanggan akan tampil di sini.' }}</small></td></tr>
        @endforelse
        </tbody></table></div>
        <div class="testdrive-pagination"><span>Menampilkan <b>{{ $rows->firstItem() ?? 0 }}–{{ $rows->lastItem() ?? 0 }}</b> dari <b>{{ $rows->total() }}</b> data</span><div><label>Per halaman <select onchange="window.location=this.value"><option value="{{ request()->fullUrlWithQuery(['perPage'=>10,'page'=>1]) }}" @selected($perPage===10)>10</option><option value="{{ request()->fullUrlWithQuery(['perPage'=>20,'page'=>1]) }}" @selected($perPage===20)>20</option><option value="{{ request()->fullUrlWithQuery(['perPage'=>50,'page'=>1]) }}" @selected($perPage===50)>50</option></select></label>{{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}</div></div>
    </section>
</div>
@endsection
@push('scripts')<script>document.querySelectorAll('.testdrive-table form').forEach(function(form){form.addEventListener('submit',function(){var button=form.querySelector('button');if(button){button.disabled=true;button.textContent='Memproses…';}});});</script>@endpush
