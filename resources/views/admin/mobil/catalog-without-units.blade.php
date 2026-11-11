@if(($virtualCatalogCars ?? collect())->isNotEmpty())
<section class="catalog-models-without-units" aria-label="Model katalog tanpa unit fisik">
    <h2>Model Katalog · Belum Ada Unit Fisik</h2>
    <p>Model tetap tayang di katalog. Tidak dihitung sebagai unit stok dan belum dapat dibeli.</p>
    <div class="showroom-card-grid">
    @foreach($virtualCatalogCars as $model)
        @php($identity = app(\App\Services\CatalogVehicleIdentityService::class))
        <article class="showroom-car-card" data-catalog-identity="{{ $identity->key($model) }}">
            <div class="showroom-media"><img src="{{ $model->image }}" alt="{{ $model->merk }} {{ $model->model }}" loading="lazy"><div class="showroom-media-badges"><span class="showroom-badge is-inactive">Belum Ada Unit Fisik</span></div></div>
            <div class="showroom-body"><h3>{{ $model->merk }} {{ $model->model }}</h3><p>{{ $model->tipe }} · {{ $model->tahun }} · Warna katalog: {{ $model->warna }}</p>
                <p><strong>{{ money($model->harga_jual) }}</strong> · Stok dapat dibeli: <strong>0</strong></p>
                <p>Pre-Order aktif: {{ $identity->activePreorders($model) }}</p><small>{{ $identity->key($model) }}</small>
                <div class="showroom-card-actions">
                    @if(auth()->user()?->roleKey() !== 'owner' && auth()->user()?->hasPermission('inventory.manage'))
                    <a class="btn-modal-primary" href="{{ route('admin.mobil.create', array_filter(['catalog'=>$identity->key($model),'preorder'=>request()->integer('preorder') ?: null])) }}">Tambahkan Unit ke Inventori</a>
                    @else<p>Hanya dapat ditinjau. Penambahan unit memerlukan izin inventori.</p>@endif
                </div>
            </div>
        </article>
    @endforeach
    </div>
</section>
@endif
