@extends('layouts.app')
@section('title','Promo Member')
@section('content')
<section class="member-section member-section-wide"><div class="member-heading"><h1>Promo Member</h1><a href="{{ route('admin.promo-member.create') }}">Tambah Promo</a></div>
@forelse($items as $item)<div class="member-row"><div><strong>{{ $item->nama }}</strong><small>{{ $item->kode_promo }} · {{ ucfirst($item->jenis_benefit) }} · {{ $item->mulai_at->format('d M Y') }} - {{ $item->selesai_at->format('d M Y') }}</small></div><span class="member-status">{{ ucfirst($item->status) }}</span><a href="{{ route('admin.promo-member.show',$item) }}">Detail</a></div>@empty<p class="member-empty">Belum ada promo member.</p>@endforelse
{{ $items->links() }}</section>
@endsection
