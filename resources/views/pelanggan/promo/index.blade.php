@extends('layouts.app')
@section('title','Promo Member · YZA Automotive')
@section('content')
<section class="member-hero"><div><span class="yza-eyebrow">YZA Member</span><h1>Promo Khusus Untuk Anda</h1><p>Lihat benefit yang bisa digunakan sesuai layanan dan status membership Anda.</p></div></section>
@if(!$customer->membership?->isActive())<div class="yza-alert">Promo khusus YZA Member aktif. <a href="{{ route('pelanggan.membership.activation') }}">Aktifkan membership</a></div>@endif
@if(!$hasVerifiedOwnership)<section class="member-access-note"><strong>Promo owner terkunci</strong><p>Promo member tetap dapat dilihat, tetapi benefit owner baru aktif setelah pembelian mobil YZA terverifikasi.</p><a class="yza-button" href="{{ route('mobil.index') }}">Lihat Koleksi Mobil</a></section>@endif
<section class="member-grid">@forelse($items as $item)<a class="member-section" href="{{ route('pelanggan.promo.show',$item) }}"><div class="member-heading"><h2>{{ $item->nama }}</h2><span class="member-status">{{ ucfirst($item->jenis_benefit) }}</span></div><p>{{ $item->kode_promo }} · {{ $item->deskripsi }}</p><small>Berlaku untuk {{ ucfirst(str_replace('_',' ',$item->berlaku_untuk)) }} · Min. Rp {{ number_format((float)$item->minimum_transaksi,0,',','.') }} · sampai {{ $item->selesai_at->format('d M Y') }}</small></a>@empty<p class="member-empty">Belum ada promo aktif untuk Anda.</p>@endforelse</section>{{ $items->links() }}
@endsection
