@extends('layouts.app')

@section('title', 'Edit ' . $item->nama_sparepart . ' · Admin YZA Automotive')
@section('admin-page-title', 'Edit Spare Part')

@section('content')
<div class="admin-crud-container" style="max-width: 980px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    {{-- Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <div>
            <nav style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748B; margin-bottom: 8px;">
                <span style="font-weight: 700; color: #94A3B8;">&lsaquo;</span>
                <a href="{{ route('admin.sparepart.index') }}" style="color: #64748B; text-decoration: none;">Master Spare Part</a>
                <span style="color: #CBD5E1;">&gt;</span>
                <span style="color: #0F172A; font-weight: 700;">Edit #{{ $item->kode_sparepart }}</span>
            </nav>
            <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #0F172A;">Edit Data Sparepart</h1>
        </div>
        <a href="{{ route('admin.sparepart.index') }}" style="height: 40px; padding: 0 16px; border-radius: 10px; background: #F1F5F9; color: #334155; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; border: 1px solid #E2E8F0;">
            &larr; Kembali
        </a>
    </div>

    @if($errors->any())
        <div style="padding: 14px 18px; border-radius: 12px; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; font-size: 13.5px; font-weight: 600;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form Card --}}
    <div style="background: #FFFFFF; border-radius: 18px; border: 1px solid #E2E8F0; padding: 28px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);">
        <form method="post" action="{{ route('admin.sparepart.update', $item) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.sparepart._form')
        </form>
    </div>
</div>
@endsection
