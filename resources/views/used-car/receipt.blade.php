@extends('layouts.app')
@section('content')
<div class="page-header"><h1>Bukti Pembelian Mobil Bekas</h1><p>{{ $purchase->kode_pembelian }}</p></div>
<article class="card"><p>Transaksi: {{ $purchase->transaksi?->kode_transaksi }}</p><p>Pelanggan: {{ $purchase->pelanggan?->nama_lengkap ?? '—' }}</p><p>Kendaraan: {{ $purchase->pengajuan?->merk }} {{ $purchase->pengajuan?->model }}</p><p>Nilai pembelian: {{ money($purchase->harga_pembelian) }}</p><p>Status transaksi: {{ $purchase->transaksi?->status }}</p><p>Status inventory: {{ $purchase->status }}</p><h3>Riwayat pembayaran keluar</h3>@foreach($purchase->transaksi?->pembayarans ?? [] as $payment)<div>{{ $payment->metode }} · {{ money($payment->jumlah) }} · {{ $payment->arah }} · {{ $payment->status }}</div>@endforeach</article>
@endsection
