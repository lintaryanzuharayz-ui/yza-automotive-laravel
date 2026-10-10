@extends('layouts.app') @section('content')
<div class="page-header"><div><span class="eyebrow">Inventory</span><h1>Stok Spare Part</h1></div><a class="button button-primary" href="{{route('admin.inventory.adjustment')}}">Penyesuaian Stok</a></div>
@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
<div class="card content-card"><div class="table-wrapper"><table class="data-table"><thead><tr><th>Kode</th><th>Spare Part</th><th>Stok</th><th>Minimum</th><th>Status</th></tr></thead><tbody>@foreach($parts as $p)<tr><td>{{$p->kode_sparepart}}</td><td>{{$p->nama_sparepart}}</td><td>{{$p->stok}} {{$p->satuan}}</td><td>{{$p->stok_minimum}}</td><td>{{$p->stockLevel()}}</td></tr>@endforeach</tbody></table></div>{{$parts->links()}}</div>@endsection
