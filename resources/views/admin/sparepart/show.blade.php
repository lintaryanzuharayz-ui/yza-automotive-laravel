@extends('layouts.app')

@section('title', 'Detail ' . $item->nama_sparepart . ' · Admin YZA Automotive')
@section('admin-page-title', 'Detail Spare Part')

@section('content')
@php($vehicleCatalogJson = $vehicleCatalog->map(fn ($vehicle) => ['id' => $vehicle->id_kendaraan_pelanggan, 'merk' => $vehicle->merk, 'model' => $vehicle->model, 'tahun' => $vehicle->tahun, 'varian' => $vehicle->tipe, 'plate' => $vehicle->nomor_polisi])->values()->toJson())
<style>
.sp-fitment-page{display:grid;gap:22px;width:100%}.sp-fitment-page .sp-card{background:#fff;border:1px solid #dbe4ee;border-radius:18px;padding:28px;box-shadow:0 10px 28px rgba(15,23,42,.05)}.sp-fitment-page .sp-card:hover{box-shadow:0 14px 34px rgba(15,23,42,.08)}.sp-fitment-page .sp-card h2{font-size:24px}.sp-fitment-page .sp-card small{font-size:12px}.sp-fitment-page .sp-form-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.sp-fitment-page .sp-field{display:grid;gap:7px}.sp-fitment-page .sp-field label{color:#475569;font-size:12px;font-weight:800}.sp-fitment-page .sp-field input,.sp-fitment-page .sp-field select,.sp-fitment-page .sp-field textarea{width:100%;min-height:48px;border:1px solid #cbd5e1;border-radius:10px;padding:0 13px;background:#fff;color:#0f172a;font:inherit}.sp-fitment-page .sp-field textarea{padding:12px;min-height:90px}.sp-fitment-page .sp-field input:focus,.sp-fitment-page .sp-field select:focus,.sp-fitment-page .sp-field textarea:focus{outline:0;border-color:#ed172b;box-shadow:0 0 0 3px rgba(237,23,43,.12)}.sp-fitment-page .sp-context{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;padding:18px;border:1px solid #bbf7d0;border-left:5px solid #16a34a;border-radius:13px;background:#f0fdf4}.sp-fitment-page .sp-context strong{display:block;color:#166534;font-size:15px}.sp-fitment-page .sp-context span{display:block;margin-top:4px;color:#64748b;font-size:12px}.sp-fitment-page .sp-badge{display:inline-flex;align-items:center;width:max-content;padding:7px 10px;border-radius:999px;background:#dcfce7;color:#15803d;font-size:11px;font-weight:900}.sp-fitment-page .sp-technical{margin-top:22px;padding-top:22px;border-top:1px solid #e2e8f0}.sp-fitment-page .sp-submit{margin-top:16px;min-height:46px;padding:0 20px;border:0;border-radius:10px;background:#ed172b;color:#fff;font-weight:900;cursor:pointer;transition:transform .18s ease,box-shadow .18s ease}.sp-fitment-page .sp-submit:hover{transform:translateY(-2px);box-shadow:0 9px 18px rgba(237,23,43,.22)}@media(max-width:1000px){.sp-fitment-page .sp-form-grid,.sp-fitment-page .sp-context{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:620px){.sp-fitment-page .sp-card{padding:18px}.sp-fitment-page .sp-form-grid,.sp-fitment-page .sp-context{grid-template-columns:1fr}}
</style>
<style>
/* Compact overview and clearer section navigation. */
.sp-fitment-page .sp-section-nav{display:flex;gap:8px;flex-wrap:wrap;margin-top:-6px}
.sp-fitment-page .sp-section-nav a{padding:9px 13px;border:1px solid #dbe4ee;border-radius:999px;background:#fff;color:#475569;font-size:12px;font-weight:800;text-decoration:none}
.sp-fitment-page .sp-section-nav a:hover{border-color:#ed172b;color:#c81024;background:#fff7f8}
.sp-fitment-page .sp-overview-card{display:grid!important;grid-template-columns:170px minmax(0,1fr);align-items:start;gap:24px!important;padding:22px!important}
.sp-fitment-page .sp-overview-card .sp-overview-photo{width:170px;max-width:none!important;margin:0!important}
.sp-fitment-page .sp-overview-card .sp-overview-photo img{aspect-ratio:1/1;object-fit:contain}
.sp-fitment-page .sp-overview-card .sp-overview-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:10px!important}
.sp-fitment-page .sp-overview-card .sp-overview-grid>div{padding:13px 14px!important;min-height:72px}
.sp-fitment-page .sp-section-card{scroll-margin-top:24px}
.sp-fitment-page .sp-section-title{display:flex;align-items:center;gap:10px}
.sp-fitment-page .sp-section-title:before{content:"";width:4px;height:24px;border-radius:99px;background:#ed172b}
.sp-fitment-page .sp-history-card{padding:22px!important}
@media(max-width:1100px){.sp-fitment-page .sp-overview-card{grid-template-columns:130px minmax(0,1fr)}.sp-fitment-page .sp-overview-card .sp-overview-photo{width:130px}.sp-fitment-page .sp-overview-card .sp-overview-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
@media(max-width:700px){.sp-fitment-page .sp-overview-card{grid-template-columns:1fr}.sp-fitment-page .sp-overview-card .sp-overview-photo{width:120px}.sp-fitment-page .sp-overview-card .sp-overview-grid{grid-template-columns:1fr!important}}
.sp-fitment-page > div[style*="flex-direction: column"]{display:grid!important;grid-template-columns:170px minmax(0,1fr);align-items:start;gap:24px!important;padding:22px!important}
.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="max-width: 280px"]{width:170px;max-width:none!important;margin:0!important}
.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="grid-template-columns: repeat(2, 1fr)"]{grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:10px!important}
.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="grid-template-columns: repeat(2, 1fr)"]>div{min-height:72px;padding:13px 14px!important}
.sp-fitment-page > div[style*="flex-direction: column"]:not(:has(img)){display:block!important}
.sp-fitment-page > div[style*="flex-direction: column"]:not(:has(img)) > div[style*="grid-template-columns: repeat(2, 1fr)"]{width:100%}
.sp-fitment-page h2{display:flex;align-items:center;gap:10px}
.sp-fitment-page h2:before{content:"";width:4px;height:24px;border-radius:99px;background:#ed172b;flex:0 0 auto}
@media(max-width:1100px){.sp-fitment-page > div[style*="flex-direction: column"]{grid-template-columns:130px minmax(0,1fr)}.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="max-width: 280px"]{width:130px}.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="grid-template-columns: repeat(2, 1fr)"]{grid-template-columns:repeat(2,minmax(0,1fr))!important}}
@media(max-width:700px){.sp-fitment-page > div[style*="flex-direction: column"]{grid-template-columns:1fr}.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="max-width: 280px"]{width:120px}.sp-fitment-page > div[style*="flex-direction: column"] > div[style*="grid-template-columns: repeat(2, 1fr)"]{grid-template-columns:1fr!important}}
</style>
<div class="admin-detail-container sp-fitment-page" style="width: 100%; max-width: none; margin: 0;">
    {{-- Header --}}
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div>
            <nav style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748B; margin-bottom: 8px;">
                <span style="font-weight: 700; color: #94A3B8;">&lsaquo;</span>
                <a href="{{ route('admin.sparepart.index') }}" style="color: #64748B; text-decoration: none;">Master Spare Part</a>
                <span style="color: #CBD5E1;">&gt;</span>
                <span style="color: #0F172A; font-weight: 700;">Detail #{{ $item->kode_sparepart }}</span>
            </nav>
            <h1 style="margin: 0; font-size: 26px; font-weight: 800; color: #0F172A;">{{ $item->nama_sparepart }}</h1>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.sparepart.edit', $item) }}" style="height: 40px; padding: 0 18px; border-radius: 10px; background: #0F172A; color: #FFFFFF; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                Edit Data
            </a>
            <a href="{{ route('admin.sparepart.index') }}" style="height: 40px; padding: 0 16px; border-radius: 10px; background: #F1F5F9; color: #334155; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; border: 1px solid #E2E8F0;">
                &larr; Kembali
            </a>
        </div>
    </div>

    {{-- Content Card --}}
    <div style="background: #FFFFFF; border-radius: 18px; border: 1px solid #E2E8F0; padding: 28px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); display: flex; flex-direction: column; gap: 24px;">
        @if($item->photoUrl())
            <div style="max-width: 280px; border-radius: 14px; overflow: hidden; border: 1px solid #E2E8F0; background: #F8FAFC;">
                <img src="{{ $item->photoUrl() }}" alt="{{ $item->nama_sparepart }}" style="width: 100%; height: auto; display: block;">
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px;">
            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Kode SKU</small>
                <strong style="display: block; margin-top: 4px; font-size: 15px; color: #0F172A; font-family: monospace;">{{ $item->kode_sparepart }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Kategori Suku Cadang</small>
                <strong style="display: block; margin-top: 4px; font-size: 15px; color: #4338CA;">{{ $item->kategori->nama_kategori }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Merek / Pabrikan</small>
                <strong style="display: block; margin-top: 4px; font-size: 15px; color: #0F172A;">{{ $item->merk ?: '—' }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Satuan Kemasan</small>
                <strong style="display: block; margin-top: 4px; font-size: 15px; color: #0F172A;">{{ $item->satuan }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Harga Beli / Pokok</small>
                <strong style="display: block; margin-top: 4px; font-size: 16px; color: #64748B;">Rp {{ number_format((float)$item->harga_beli, 0, ',', '.') }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Harga Jual / Servis</small>
                <strong style="display: block; margin-top: 4px; font-size: 18px; color: #ED172B; font-weight: 800;">Rp {{ number_format((float)$item->harga_jual, 0, ',', '.') }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Saldo Stok Fisik</small>
                <strong style="display: block; margin-top: 4px; font-size: 18px; color: #0F172A; font-weight: 800;">{{ $item->stok }} {{ $item->satuan }}</strong>
            </div>

            <div style="background: #F8FAFC; padding: 14px 16px; border-radius: 12px; border: 1px solid #F1F5F9;">
                <small style="color: #64748B; font-size: 11px; font-weight: 700; text-transform: uppercase;">Batas Minimum & Status</small>
                <div style="margin-top: 6px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 700; color: #334155;">Min: {{ $item->stok_minimum }} {{ $item->satuan }}</span>
                    <span style="padding: 2px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; {{ $item->status === 'aktif' ? 'background:#ECFDF5;color:#047857;' : 'background:#F1F5F9;color:#64748B;' }}">
                        {{ $item->status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.03);">
        <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap;">
            <div><h2 style="margin:0; font-size:20px; color:#0F172A;">Kompatibilitas Kendaraan</h2><p style="margin:6px 0 0; color:#64748B; font-size:13px;">Gunakan verifikasi ini agar sparepart yang dipilih sesuai kendaraan customer.</p></div>
            <span style="padding:6px 10px; border-radius:999px; background:#FFF7ED; color:#C2410C; font-size:11px; font-weight:800;">{{ $item->fitments->where('status_verifikasi','verified_fit')->count() }} FIT TERVERIFIKASI</span>
        </div>
        <div style="display:grid; gap:10px; margin-top:18px;">
            @forelse($item->fitments as $fitment)
                @php($fitmentStyle = match($fitment->status_verifikasi) { 'verified_fit' => 'background:#ECFDF5;color:#047857;', 'verified_not_fit' => 'background:#FEF2F2;color:#B91C1C;', default => 'background:#FFF7ED;color:#C2410C;' })
                <div style="display:flex; justify-content:space-between; gap:16px; align-items:center; padding:14px 16px; border:1px solid #E2E8F0; border-radius:12px; flex-wrap:wrap;">
                    <div><strong style="color:#0F172A;">{{ $fitment->merk_kendaraan ?: 'Semua merek' }} {{ $fitment->model_kendaraan ?: '• semua model' }}</strong><div style="margin-top:4px; color:#64748B; font-size:12px;">{{ $fitment->tahun_mulai ?: '—' }}–{{ $fitment->tahun_akhir ?: '—' }} · Mesin {{ $fitment->kode_mesin ?: 'umum' }} · Bukti: {{ $fitment->sumber_bukti ?: 'belum dicatat' }}</div></div>
                    <div style="display:flex; gap:8px; align-items:center;"><span style="padding:5px 9px; border-radius:999px; font-size:11px; font-weight:800; {{ $fitmentStyle }}">{{ match($fitment->status_verifikasi) { 'verified_fit' => 'KOMPATIBEL TERVERIFIKASI', 'verified_not_fit' => 'TIDAK KOMPATIBEL', default => 'KOMPATIBILITAS BELUM TERVERIFIKASI' } }}</span><form method="post" action="{{ route('admin.sparepart.fitment.destroy', [$item, $fitment]) }}">@csrf @method('DELETE')<button type="submit" style="border:1px solid #FCA5A5; color:#B91C1C; background:#FFF; border-radius:8px; padding:5px 9px; font-size:11px; font-weight:700;">Hapus</button></form></div>
                </div>
            @empty
                <div style="padding:14px 16px; border-radius:12px; background:#FFF7ED; color:#9A3412; font-size:13px;">Belum ada fitment terverifikasi. Sparepart akan dianggap belum aman untuk kendaraan yang identitasnya lengkap.</div>
            @endforelse
        </div>
        @if($vehicleContext)
            <div class="sp-context" style="margin-top:22px;"><div><small>KENDARAAN DARI WORK ORDER</small><strong>{{ $vehicleContext->merk }} {{ $vehicleContext->model }}{{ $vehicleContext->tahun ? ' · '.$vehicleContext->tahun : '' }}</strong><span>{{ $vehicleContext->nomor_polisi ?: 'Nomor polisi belum diketahui' }}</span></div><div><small>REFERENSI</small><strong>Work Order #{{ $workOrder->id_servis }}</strong><span>{{ $workOrder->kode_servis }}</span></div><div><small>IDENTITAS MESIN</small><strong>{{ $vehicleContext->nomor_mesin ? 'Tersedia di profil kendaraan' : 'Belum diketahui' }}</strong><span>Kode mesin teknis tidak ditebak dari nomor internal.</span></div><div><span class="sp-badge">✓ Data kendaraan berhasil dibaca</span><span style="margin-top:8px;">Field kendaraan terkunci dari database.</span></div></div>
        @endif
        <form method="post" action="{{ route('admin.sparepart.fitment.store', $item) }}{{ $workOrder ? '?work_order='.$workOrder->id_servis : '' }}" class="sp-technical">
            @csrf
            @if($vehicleContext)
                <input type="hidden" name="merk_kendaraan" value="{{ $vehicleContext->merk }}"><input type="hidden" name="model_kendaraan" value="{{ $vehicleContext->model }}"><input type="hidden" name="tahun_mulai" value="{{ $vehicleContext->tahun }}"><input type="hidden" name="tahun_akhir" value="{{ $vehicleContext->tahun }}"><input type="hidden" name="varian" value="{{ $vehicleContext->tipe }}">
                <div class="sp-field"><label>Kendaraan terpilih</label><div class="sp-context" style="grid-template-columns:1fr;padding:12px 14px;"><strong>{{ $vehicleContext->merk }} {{ $vehicleContext->model }} · {{ $vehicleContext->tahun ?: 'Tahun belum diketahui' }}</strong><span>{{ $vehicleContext->nomor_polisi ?: 'Nomor polisi belum diketahui' }} · Work Order #{{ $workOrder->id_servis }}</span></div></div>
            @else
                <input type="hidden" name="id_kendaraan_pelanggan" id="id_kendaraan_pelanggan"><div class="sp-form-grid" data-vehicle-catalog="{{ $vehicleCatalogJson }}"><div class="sp-field"><label for="vehicle_merk">Merek kendaraan</label><select id="vehicle_merk"><option value="">Pilih merek...</option></select></div><div class="sp-field"><label for="vehicle_model">Model kendaraan</label><select id="vehicle_model" disabled><option value="">Pilih model...</option></select></div><div class="sp-field"><label for="vehicle_year">Tahun kendaraan</label><select id="vehicle_year" disabled><option value="">Pilih tahun...</option></select></div><div class="sp-field"><label for="vehicle_variant">Varian / kendaraan</label><select id="vehicle_variant" disabled><option value="">Pilih varian...</option></select><small>Semua pilihan berasal dari kendaraan aktif yang tersimpan.</small></div></div>
            @endif
            <div class="sp-form-grid"><div class="sp-field"><label for="status_verifikasi">Status verifikasi</label><select id="status_verifikasi" name="status_verifikasi"><option value="unverified" selected>Belum Terverifikasi</option><option value="verified_fit">Kompatibel Terverifikasi</option><option value="verified_not_fit">Tidak Kompatibel Terverifikasi</option></select></div><div class="sp-field"><label for="nomor_part_referensi">Nomor part / referensi bukti</label><input id="nomor_part_referensi" name="nomor_part_referensi" placeholder="Wajib untuk status terverifikasi"></div><div class="sp-field"><label for="sumber_bukti">Sumber bukti</label><input id="sumber_bukti" name="sumber_bukti" placeholder="OEM, supplier, katalog resmi..."></div><div class="sp-field"><label for="kode_mesin">Kode mesin teknis</label><input id="kode_mesin" name="kode_mesin" placeholder="Kosongkan jika belum diketahui"><small>Jangan isi nomor mesin internal sebagai kode teknis.</small></div></div>
            <div class="sp-field" style="margin-top:14px;"><label for="catatan">Catatan teknis</label><textarea id="catatan" name="catatan" placeholder="Catatan verifikasi atau cakupan fitment..."></textarea></div>
            <button type="submit" class="sp-submit">Simpan Verifikasi Fitment</button>
        </form>
    </div>

    <div style="background:#FFFFFF; border:1px solid #E2E8F0; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,.03);">
        <h2 style="margin:0; font-size:20px; color:#0F172A;">Riwayat Mutasi Stok</h2>
        <div style="overflow:auto; margin-top:16px;"><table style="width:100%; border-collapse:collapse; font-size:13px;"><thead><tr style="background:#F8FAFC; text-align:left; color:#64748B; font-size:11px; text-transform:uppercase;"><th style="padding:10px;">Tanggal</th><th style="padding:10px;">Transaksi</th><th style="padding:10px;">Perubahan</th><th style="padding:10px;">Saldo</th><th style="padding:10px;">Referensi</th></tr></thead><tbody>@forelse($stockHistory as $row)<tr style="border-bottom:1px solid #F1F5F9;"><td style="padding:10px;">{{ optional($row->tanggal)->format('d M Y H:i') }}</td><td style="padding:10px;">{{ $row->jenis_transaksi }}</td><td style="padding:10px; font-weight:700; color:{{ $row->jumlah_perubahan < 0 ? '#B91C1C' : '#047857' }};">{{ $row->jumlah_perubahan > 0 ? '+' : '' }}{{ $row->jumlah_perubahan }}</td><td style="padding:10px;">{{ $row->stok_sesudah }}</td><td style="padding:10px;">{{ $row->referensi_tipe ?: '—' }} #{{ $row->referensi_id ?: '—' }}</td></tr>@empty<tr><td colspan="5" style="padding:16px; color:#64748B;">Belum ada histori mutasi stok.</td></tr>@endforelse</tbody></table></div>
    </div>
</div>
@if(!$vehicleContext)
<script>
(() => {
    const source = document.querySelector('[data-vehicle-catalog]');
    const hidden = document.querySelector('#id_kendaraan_pelanggan');
    const merk = document.querySelector('#vehicle_merk');
    const model = document.querySelector('#vehicle_model');
    const year = document.querySelector('#vehicle_year');
    const variant = document.querySelector('#vehicle_variant');
    if (!source || !hidden || !merk || !model || !year || !variant) return;
    const rows = JSON.parse(source.dataset.vehicleCatalog || '[]');
    const fill = (select, values, placeholder) => { select.replaceChildren(new Option(placeholder, '')); [...new Set(values.filter(value => value !== null && value !== ''))].forEach(value => select.add(new Option(String(value), String(value)))); select.disabled = values.length === 0; };
    fill(merk, rows.map(row => row.merk), 'Pilih merek...');
    const refresh = () => {
        const models = rows.filter(row => row.merk === merk.value);
        fill(model, models.map(row => row.model), 'Pilih model...');
        fill(year, [], 'Pilih tahun...'); fill(variant, [], 'Pilih varian...'); hidden.value = '';
    };
    merk.addEventListener('change', refresh);
    model.addEventListener('change', () => { const years = rows.filter(row => row.merk === merk.value && row.model === model.value); fill(year, years.map(row => row.tahun), 'Pilih tahun...'); fill(variant, [], 'Pilih varian...'); hidden.value = ''; });
    year.addEventListener('change', () => { const variants = rows.filter(row => row.merk === merk.value && row.model === model.value && String(row.tahun ?? '') === year.value); fill(variant, variants.map(row => row.varian || row.plate || 'Varian umum'), 'Pilih varian...'); hidden.value = ''; });
    variant.addEventListener('change', () => { const matches = rows.filter(row => row.merk === merk.value && row.model === model.value && String(row.tahun ?? '') === year.value && (row.varian || row.plate || 'Varian umum') === variant.value); hidden.value = matches.length === 1 ? String(matches[0].id) : ''; });
})();
</script>
@endif
@endsection
