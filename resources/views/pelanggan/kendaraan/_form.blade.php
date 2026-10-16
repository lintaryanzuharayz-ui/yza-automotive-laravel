@csrf
@php($isEdit = isset($vehicleImage))
<section class="vehicle-edit-preview">
    <div class="vehicle-edit-preview-image"><img src="{{ $vehicleImage ?? asset('assets/images/hero/yza-hero-reference-v3.png') }}" alt="{{ $item->merk }} {{ $item->model }}"></div>
    <div class="vehicle-edit-preview-copy"><small>KENDARAAN SAYA</small><h2>{{ $item->merk }} <strong>{{ $item->model }}</strong></h2><span>{{ strtoupper($item->tipe ?: 'KENDARAAN') }}</span></div>
    <div class="vehicle-edit-preview-stats"><div><b>◉</b><strong>{{ $item->tahun ?: '—' }}</strong><small>Tahun</small></div><div><b>◌</b><strong>{{ $item->transmisi ?: '—' }}</strong><small>Transmisi</small></div><div><b>●</b><strong>{{ $item->warna ?: '—' }}</strong><small>Warna</small></div></div>
</section>
<section class="vehicle-edit-form-card">
    <div class="vehicle-edit-form-heading"><span aria-hidden="true">▣</span><div><h2>{{ $isEdit ? 'Data Kendaraan' : 'Informasi Kendaraan' }}</h2><p>{{ $isEdit ? 'Data identitas resmi kendaraan dari mitra' : 'Lengkapi data kendaraan Anda' }}</p></div></div>
    <div class="vehicle-edit-fields">
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Merk</span><input name="merk" value="{{ old('merk', $item->merk) }}" required @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Model</span><input name="model" value="{{ old('model', $item->model) }}" required @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Tipe</span><input name="tipe" value="{{ old('tipe', $item->tipe) }}" @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Tahun</span><input type="number" name="tahun" value="{{ old('tahun', $item->tahun) }}" @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Warna</span><input name="warna" value="{{ old('warna', $item->warna) }}" @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Transmisi</span><select name="transmisi" @disabled($isEdit)><option value="">Pilih transmisi</option>@foreach(['manual','automatic','cvt','dct','lainnya'] as $v)<option value="{{ $v }}" @selected(old('transmisi', $item->transmisi) === $v)>{{ ucfirst($v) }}</option>@endforeach</select></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Bahan bakar</span><select name="bahan_bakar" @disabled($isEdit)><option value="">Pilih bahan bakar</option>@foreach(['bensin','diesel','hybrid','listrik','lainnya'] as $v)<option value="{{ $v }}" @selected(old('bahan_bakar', $item->bahan_bakar) === $v)>{{ ucfirst($v) }}</option>@endforeach</select></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Nomor Polisi</span><input name="nomor_polisi" value="{{ old('nomor_polisi', $item->nomor_polisi) }}" @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Nomor Rangka</span><input name="nomor_rangka" value="{{ old('nomor_rangka', $item->nomor_rangka) }}" @readonly($isEdit)></label>
        <label class="{{ $isEdit ? 'is-locked' : '' }}"><span>Nomor Mesin</span><input name="nomor_mesin" value="{{ old('nomor_mesin', $item->nomor_mesin) }}" @readonly($isEdit)></label>
        <label><span>Kilometer terakhir</span><div class="vehicle-edit-input-unit"><input type="number" min="0" name="kilometer" value="{{ old('kilometer', $item->kilometer ?? 0) }}" required><em>km</em></div></label>
    </div>
    @if($isEdit)
        <div class="vehicle-edit-editable-note"><strong>Yang dapat diperbarui</strong><span>Catat kilometer terakhir kendaraan. Perubahan data resmi dilakukan melalui verifikasi admin/mitra.</span></div>
    @endif
    <div class="vehicle-edit-actions"><a href="{{ !empty($returnToGarage) ? route('pelanggan.kendaraan.index') : route('pelanggan.kendaraan.show', $item) }}">Batal</a><button type="submit"><span aria-hidden="true">▣</span> {{ $isEdit ? 'Simpan Kilometer' : 'Simpan Kendaraan' }}</button></div>
</section>
