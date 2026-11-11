@props(['mobil', 'detailUrl'])
@php($colorOptions = app(\App\Services\VehicleColorStockService::class)->colors($mobil))
<div class="catalog-color-stock-options" aria-label="Stok per warna">
    @foreach($colorOptions as $key => $color)
        <a href="{{ $detailUrl.(str_contains($detailUrl, '?') ? '&' : '?').'warna='.rawurlencode($key) }}" class="catalog-color-stock-option {{ $color['stock'] > 0 ? 'is-ready' : 'is-empty' }}" title="{{ $color['label'] }}: {{ $color['stock'] > 0 ? $color['stock'].' unit siap jual' : 'Stok Habis' }}">
            <i style="--color-stock-swatch:{{ $color['swatch'] }}" aria-hidden="true">{{ $color['stock'] > 0 ? '' : '×' }}</i>
            <span>{{ $color['label'] }} <small>{{ $color['stock'] > 0 ? $color['stock'].' ready' : 'Stok Habis' }}</small></span>
        </a>
    @endforeach
</div>
@once
@push('styles')
<style>
.catalog-color-stock-options{display:flex;flex-wrap:wrap;gap:6px;margin:10px 0}
.catalog-color-stock-option{display:flex;align-items:center;gap:5px;max-width:100%;padding:5px 7px;border:1px solid #e2e8f0;border-radius:8px;color:#172033;background:#fff;text-decoration:none;font-size:10px;overflow-wrap:anywhere}
.catalog-color-stock-option i{display:grid;place-items:center;flex:none;width:18px;height:18px;border:1px solid #cbd5e1;border-radius:50%;background:var(--color-stock-swatch);color:#ed172b;font-size:18px;font-style:normal;font-weight:800;text-shadow:0 0 3px white}
.catalog-color-stock-option small{display:block;color:#64748b;font-size:9px}
.catalog-color-stock-option.is-empty{border-style:dashed}
</style>
@endpush
@endonce
