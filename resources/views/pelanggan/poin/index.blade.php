@extends('layouts.app')
@section('title', 'YZA Points · YZA Automotive')
@section('content')
<section class="member-hero">
 <div><span class="yza-eyebrow">YZA Points</span><h1>Saldo Poin Anda</h1><p>Rp {{ number_format($points->rate(),0,',','.') }} transaksi = 1 YZA Point</p></div>
 <div class="member-id"><small>Saldo saat ini</small><strong>{{ number_format((int)($account?->saldo ?? 0),0,',','.') }} Poin</strong><small>Total diperoleh {{ number_format((int)($account?->total_diperoleh ?? 0),0,',','.') }} Poin</small></div>
</section>
@if(!$hasVerifiedOwnership)<section class="member-access-note"><strong>Benefit owner terkunci</strong><p>Riwayat points tetap tersimpan. Points dan promo owner akan aktif setelah Anda membeli mobil melalui YZA Automotive.</p><a class="yza-button" href="{{ route('mobil.index') }}">Lihat Koleksi Mobil</a></section>@endif
<section class="member-section member-section-wide">
 <div class="member-heading"><h2>Riwayat Poin</h2><a href="{{ route('pelanggan.member') }}">Kembali ke Member Center</a></div>
 @forelse($history as $row)
  <div class="member-row">
   <div><strong>{{ $row->keterangan ?: 'Perolehan YZA Points' }}</strong><small>{{ optional($row->created_at)->format('d M Y H:i') }}@if($row->transaksi) · {{ $points->label($row->transaksi->jenis_transaksi) }} · {{ $row->transaksi->kode_transaksi }}@endif</small></div>
   <div class="member-money"><strong>{{ $row->jenis === 'pembalikan' ? '-' : '+' }}{{ number_format((int)$row->perubahan_poin,0,',','.') }} Poin</strong><small>Saldo setelah {{ number_format((int)$row->saldo_setelah,0,',','.') }}</small></div>
  </div>
 @empty
  <p class="member-empty">Belum ada riwayat poin.</p>
 @endforelse
 {{ $history->links() }}
</section>
@endsection
