@extends('layouts.app')
@section('title', 'Jadwal Test Drive · YZA Automotive')
@section('admin-page-title', 'Jadwal Test Drive')
@section('content')
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Jadwal</th><th>Pelanggan</th><th>Kendaraan</th><th>Sales pendamping</th><th>Status</th><th>Hasil / follow-up</th></tr></thead><tbody>
@forelse($testDrives as $drive)
<tr><td>{{ $drive->tanggal?->format('d M Y') }} · {{ $drive->jam }}</td><td>{{ $drive->pelanggan?->nama_lengkap }}</td><td>{{ $drive->mobil?->merk }} {{ $drive->mobil?->model }}</td><td>{{ $drive->sales?->nama_lengkap ?: 'Belum ditugaskan' }}@if(auth()->user()->roleKey() === 'super_admin')<form method="post" action="{{ route('sales.test-drives.assignment', $drive) }}" class="admin-inline-form">@csrf @method('PUT')<select name="id_sales"><option value="">Belum ditugaskan</option>@foreach(\App\Models\Petugas::whereHas('user.role', fn($q) => $q->where('nama_role','sales'))->where('status','aktif')->get() as $sales)<option value="{{ $sales->id_petugas }}" @selected($drive->id_sales === $sales->id_petugas)>{{ $sales->nama_lengkap }}</option>@endforeach</select><button class="admin-button admin-button-secondary">Tetapkan</button></form>@endif</td><td>{{ ucfirst($drive->status) }}</td><td>{{ $drive->hasil ?: 'Belum dicatat' }}@if($drive->id_sales === auth()->user()->petugas?->id_petugas || auth()->user()->roleKey() === 'super_admin')<form method="post" action="{{ route('sales.test-drives.update', $drive) }}" class="admin-inline-form">@csrf @method('PUT')<input name="hasil" placeholder="Hasil"><input name="catatan_sales" placeholder="Follow-up"><button class="admin-button admin-button-secondary">Simpan</button></form>@endif</td></tr>
@empty<tr><td colspan="6" class="admin-empty">Belum ada jadwal test drive.</td></tr>@endforelse
</tbody></table></div>{{ $testDrives->links() }}</section>
@endsection
