@extends('layouts.app')
@section('content')
<div class="page-header"><h1>Pembayaran Keluar</h1><p>{{ $transaksi->kode_transaksi }} · Total {{ money($transaksi->total) }}</p></div>
<form class="card master-form" method="post" enctype="multipart/form-data">@csrf
<label class="form-label">Metode</label><select class="form-control" name="metode"><option>tunai</option><option>transfer</option><option>kredit</option><option>lainnya</option></select>
<label class="form-label">Jumlah</label><input class="form-control" type="number" min="0.01" step="0.01" name="jumlah" required>
<label class="form-label">Bukti transfer</label><input class="form-control" type="file" name="bukti_transfer">
<label class="form-label">Catatan</label><textarea class="form-control" name="catatan"></textarea><button class="button button-primary">Simpan Pembayaran</button>
</form>
@endsection
