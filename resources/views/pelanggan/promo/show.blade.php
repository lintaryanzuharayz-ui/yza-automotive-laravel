@extends('layouts.app')
@section('title',$item->nama.' · YZA Automotive')
@section('content')
<section class="member-section member-section-wide"><span class="yza-eyebrow">Promo Tersedia</span><h1>{{ $item->nama }}</h1><p>{{ $item->deskripsi }}</p><p><strong>Benefit:</strong> {{ ucfirst($item->jenis_benefit) }} {{ $item->nilai_benefit ?? 'informasi' }}</p><p><strong>Berlaku untuk:</strong> {{ ucfirst(str_replace('_',' ',$item->berlaku_untuk)) }}</p><p><strong>Periode:</strong> {{ $item->mulai_at->format('d M Y H:i') }} - {{ $item->selesai_at->format('d M Y H:i') }}</p><p><strong>Syarat & Ketentuan:</strong><br>{{ $item->syarat_ketentuan ?: 'Lihat ketentuan pada saat layanan.' }}</p><div class="yza-alert">Promo ditampilkan sebagai benefit YZA Member. Belum ada redemption atau penerapan otomatis ke transaksi.</div></section>
@endsection
