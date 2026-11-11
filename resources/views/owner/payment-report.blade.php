@extends('layouts.app')
@section('title','Pendapatan & Pembayaran · YZA Automotive')
@section('admin-page-title','Pendapatan & Pembayaran')
@push('styles')
<style>
.owner-payment-report{display:grid;gap:20px;min-width:0}
.owner-payment-report .admin-panel{margin:0;min-width:0}
.owner-payment-report .admin-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:10px}
.owner-payment-report .admin-filter-input{min-height:40px;max-width:100%;box-sizing:border-box;padding:8px 12px;border:1px solid #dbe2ec;border-radius:9px;background:#fff;color:#25344c;font:inherit}
.owner-payment-report .admin-button{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:8px 14px;box-sizing:border-box;border:1px solid #dbe2ec;border-radius:9px;background:#fff;color:#25344c;font:600 12px Arial,sans-serif;text-decoration:none;cursor:pointer}
.owner-payment-report .admin-button-primary{border-color:#ed172b;background:#ed172b;color:#fff}
.owner-payment-report .admin-button:hover{border-color:#ed172b}
.owner-payment-report .admin-button:focus-visible{outline:2px solid #ed172b;outline-offset:3px}
.owner-payment-report .admin-detail-list{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:20px}
.owner-payment-report .admin-detail-list>div{padding:14px;border:1px solid #e6ebf2;border-radius:11px;background:#f8fafc;min-width:0}
.owner-payment-report .admin-detail-list span{display:block;margin-bottom:8px;color:#65748b;font-size:12px;line-height:1.4}
.owner-payment-report .admin-detail-list strong{display:block;color:#132139;font-size:18px;line-height:1.3;overflow-wrap:anywhere}
.owner-payment-report .admin-muted{margin:16px 0 0;color:#65748b;font-size:12px;line-height:1.6}
.owner-payment-report .admin-table-wrap{max-width:100%;overflow-x:auto}
.owner-payment-report .admin-table{width:100%;min-width:600px;border-collapse:collapse}
.owner-payment-report .admin-table :is(th,td){padding:12px 14px;text-align:left;border-bottom:1px solid #e9edf3;font-size:12px;line-height:1.5}
.owner-payment-report .admin-table th{background:#f3f6fa;color:#64748b;font-weight:700}
@media(max-width:1100px){.owner-payment-report .admin-detail-list{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.owner-payment-report .admin-detail-list{grid-template-columns:1fr}.owner-payment-report .admin-toolbar>*{flex:1 1 120px}}
</style>
@endpush
@section('content')
<div class="owner-payment-report">
<section class="admin-panel"><form class="admin-toolbar" method="get"><input class="admin-filter-input" type="date" name="from" value="{{ $period['from']->toDateString() }}"><input class="admin-filter-input" type="date" name="to" value="{{ $period['to']->toDateString() }}"><button class="admin-button admin-button-secondary">Filter</button><a class="admin-button admin-button-primary" href="{{ route('owner.payment-export', request()->query()+['format'=>'excel']) }}">Export Excel</a><a class="admin-button admin-button-secondary" href="{{ route('owner.payment-export', request()->query()+['format'=>'pdf']) }}">Export PDF</a></form><div class="admin-detail-list"><div><span>Terverifikasi bruto</span><strong>Rp {{ number_format($summary['verified'],0,',','.') }}</strong></div><div><span>Refund confirmed</span><strong>Rp {{ number_format($gateway['confirmed_refunds'],0,',','.') }}</strong></div><div><span>Penerimaan bersih terverifikasi</span><strong>Rp {{ number_format($gateway['net_verified'],0,',','.') }}</strong></div><div><span>Refund pending</span><strong>Rp {{ number_format($gateway['pending_refunds'],0,',','.') }}</strong></div><div><span>Online terverifikasi</span><strong>Rp {{ number_format($summary['online'],0,',','.') }}</strong></div><div><span>Offline terverifikasi</span><strong>Rp {{ number_format($summary['offline'],0,',','.') }}</strong></div><div><span>Pending pembayaran</span><strong>Rp {{ number_format($summary['pending'],0,',','.') }}</strong></div><div><span>Midtrans settlement/capture</span><strong>Rp {{ number_format($gateway['midtrans_settled'],0,',','.') }}</strong></div></div><p class="admin-muted">Settlement Midtrans ditampilkan sebagai status gateway, bukan klaim dana sudah masuk rekening. Laba tidak dihitung karena biaya perolehan tidak menjadi bagian laporan kas ini.</p></section>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Kode</th><th>Tanggal</th><th>Metode</th><th>Status</th><th>Jumlah</th></tr></thead><tbody>@forelse($summary['rows'] as $row)<tr><td>{{ $row->kode_pembayaran }}</td><td>{{ $row->tanggal?->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td><td>{{ $row->metode }}</td><td>{{ ucfirst($row->status) }}</td><td>Rp {{ number_format((float)$row->jumlah,0,',','.') }}</td></tr>@empty<tr><td colspan="5" class="admin-empty">Belum ada pembayaran pada periode ini.</td></tr>@endforelse</tbody></table></div></section>
</div>
@endsection
