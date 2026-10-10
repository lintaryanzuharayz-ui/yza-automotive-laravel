@extends('layouts.app')
@section('title','Midtrans Sandbox')
@section('content')
<section class="member-section member-section-wide"><span class="yza-eyebrow">MIDTRANS SANDBOX</span><h1>Status pembayaran sedang diperiksa</h1><p>Kembali dari halaman Midtrans belum menjadi bukti pembayaran. Gunakan pemeriksaan status dari server atau tunggu notifikasi resmi Midtrans.</p><a href="{{ route('pelanggan.member') }}">Kembali ke Member Center</a></section>
@endsection
