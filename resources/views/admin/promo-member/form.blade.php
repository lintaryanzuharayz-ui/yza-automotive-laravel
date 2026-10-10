@extends('layouts.app')
@section('title', $item->exists ? 'Edit Promo Member' : 'Tambah Promo Member')
@section('content')
<section class="member-section member-section-wide"><h1>{{ $item->exists ? 'Edit Promo Member' : 'Tambah Promo Member' }}</h1>
<form method="POST" action="{{ $item->exists ? route('admin.promo-member.update',$item) : route('admin.promo-member.store') }}">@csrf @if($item->exists) @method('PUT') @endif
<label>Kode Promo<input name="kode_promo" value="{{ old('kode_promo',$item->kode_promo) }}" required></label>
<label>Nama<input name="nama" value="{{ old('nama',$item->nama) }}" required></label>
<label>Deskripsi<textarea name="deskripsi">{{ old('deskripsi',$item->deskripsi) }}</textarea></label>
<label>Jenis Benefit<select name="jenis_benefit">@foreach(['persentase','nominal','informasi'] as $v)<option value="{{ $v }}" @selected(old('jenis_benefit',$item->jenis_benefit)===$v)>{{ ucfirst($v) }}</option>@endforeach</select></label>
<label>Nilai Benefit<input name="nilai_benefit" type="number" step="0.01" value="{{ old('nilai_benefit',$item->nilai_benefit) }}"></label>
<label>Minimum Transaksi<input name="minimum_transaksi" type="number" step="0.01" value="{{ old('minimum_transaksi',$item->minimum_transaksi ?? 0) }}" required></label>
<label>Maksimum Benefit<input name="maksimum_benefit" type="number" step="0.01" value="{{ old('maksimum_benefit',$item->maksimum_benefit) }}"></label>
<label>Berlaku Untuk<select name="berlaku_untuk">@foreach(['semua','servis','cuci_mobil','penjualan_sparepart','penjualan_mobil','trade_in'] as $v)<option value="{{ $v }}" @selected(old('berlaku_untuk',$item->berlaku_untuk)===$v)>{{ ucfirst(str_replace('_',' ',$v)) }}</option>@endforeach</select></label>
<label>Quota<input name="quota" type="number" min="1" value="{{ old('quota',$item->quota) }}"></label><label>Usage per Member<input name="usage_per_member" type="number" min="1" value="{{ old('usage_per_member',$item->usage_per_member) }}"></label><label><input name="member_only" type="checkbox" value="1" @checked(old('member_only',$item->member_only))> Khusus Member aktif</label>
<fieldset><legend>SPK Recommendation</legend><label>Simulation ID<input name="approved_spk_simulation_id" value="{{ old('approved_spk_simulation_id',$item->approved_spk_simulation_id) }}"></label><label>Score<input name="spk_score" type="number" min="0" max="100" value="{{ old('spk_score',$item->spk_score) }}"></label><label>Recommendation<input name="spk_recommendation" value="{{ old('spk_recommendation',$item->spk_recommendation) }}"></label><label>Projected Transactions<input name="projection_transactions" type="number" min="0" value="{{ old('projection_transactions',$item->projection_transactions) }}"></label><label>Projected Revenue<input name="projection_revenue" type="number" min="0" value="{{ old('projection_revenue',$item->projection_revenue) }}"></label><label>Projected Discount<input name="projection_discount" type="number" min="0" value="{{ old('projection_discount',$item->projection_discount) }}"></label><label>Projected Margin<input name="projection_margin" type="number" value="{{ old('projection_margin',$item->projection_margin) }}"></label><label>Estimated Cost Rate<input name="estimated_cost_rate" type="number" min="0" max="1" step="0.0001" value="{{ old('estimated_cost_rate',$item->estimated_cost_rate) }}"></label></fieldset>
<label>Mulai<input name="mulai_at" type="datetime-local" value="{{ old('mulai_at',$item->mulai_at?->format('Y-m-d\TH:i')) }}" required></label>
<label>Berakhir<input name="selesai_at" type="datetime-local" value="{{ old('selesai_at',$item->selesai_at?->format('Y-m-d\TH:i')) }}" required></label>
<label>Syarat & Ketentuan<textarea name="syarat_ketentuan">{{ old('syarat_ketentuan',$item->syarat_ketentuan) }}</textarea></label>
<label>Status<select name="status"><option value="aktif" @selected(old('status',$item->status)==='aktif')>Aktif</option><option value="nonaktif" @selected(old('status',$item->status)==='nonaktif')>Nonaktif</option></select></label>
<button type="submit">Simpan</button></form></section>
@endsection
