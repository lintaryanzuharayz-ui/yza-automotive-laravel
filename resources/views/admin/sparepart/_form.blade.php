@csrf
<style>
    .sparepart-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    @media (max-width: 768px) {
        .sparepart-form-grid {
            grid-template-columns: 1fr;
        }
    }
    .form-group-full {
        grid-column: 1 / -1;
    }
    .form-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-field-label {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .form-field-input, .form-field-select {
        width: 100%;
        height: 44px;
        padding: 0 14px;
        border: 1px solid #CBD5E1;
        border-radius: 10px;
        background: #FFFFFF;
        color: #0F172A;
        font-size: 13.5px;
        box-sizing: border-box;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }
    .form-field-input:focus, .form-field-select:focus {
        outline: none;
        border-color: #ED172B;
        box-shadow: 0 0 0 3px rgba(237, 23, 43, 0.12);
    }
    .form-stock-notice {
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13px;
        color: #64748B;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-stock-notice strong {
        color: #0F172A;
    }
    .form-actions-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 10px;
    }
    .btn-save-master {
        height: 44px;
        padding: 0 24px;
        border-radius: 10px;
        background: #ED172B;
        color: #FFFFFF;
        font-size: 13.5px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
        box-shadow: 0 4px 14px rgba(237, 23, 43, 0.28);
    }
    .btn-save-master:hover {
        background: #D91325;
        transform: translateY(-1px);
    }
    .btn-cancel-master {
        height: 44px;
        padding: 0 20px;
        border-radius: 10px;
        background: #F1F5F9;
        color: #475569;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        border: 1px solid #E2E8F0;
    }
    .btn-cancel-master:hover {
        background: #E2E8F0;
        color: #0F172A;
    }
</style>

<div class="sparepart-form-grid">
    @php($suppliers = $suppliers ?? collect())
    <div class="form-field">
        <label class="form-field-label">Kode SKU / Part Number <span style="color:#ED172B;">*</span></label>
        <input class="form-field-input" name="kode_sparepart" value="{{ old('kode_sparepart', $item->kode_sparepart) }}" required maxlength="30" placeholder="Contoh: YZA-OIL-SYN1L">
    </div>

    <div class="form-field">
        <label class="form-field-label">Kategori Suku Cadang <span style="color:#ED172B;">*</span></label>
        <select class="form-field-select" name="id_kategori" required>
            <option value="">Pilih Kategori</option>
            @foreach($categories as $c)
                <option value="{{ $c->id_kategori }}" @selected(old('id_kategori', $item->id_kategori) == $c->id_kategori)>{{ $c->nama_kategori }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-field form-group-full">
        <label class="form-field-label">Nama Barang / Deskripsi Produk <span style="color:#ED172B;">*</span></label>
        <input class="form-field-input" name="nama_sparepart" value="{{ old('nama_sparepart', $item->nama_sparepart) }}" required maxlength="150" placeholder="Contoh: Oli Mesin Fully Synthetic 0W-20 (1 Liter)">
    </div>

    <div class="form-field">
        <label class="form-field-label">Merek / Pabrikan</label>
        <input class="form-field-input" name="merk" value="{{ old('merk', $item->merk) }}" maxlength="100" placeholder="Contoh: YZA Genuine Oil, Bosch, Denso">
    </div>

    <div class="form-field">
        <label class="form-field-label">Supplier Utama</label>
        <select class="form-field-select" name="id_supplier">
            <option value="">Belum ditentukan</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id_supplier }}" @selected(old('id_supplier', $item->id_supplier) == $supplier->id_supplier)>{{ $supplier->nama_supplier }}</option>
            @endforeach
        </select>
    </div>

    <div class="form-field">
        <label class="form-field-label">Nomor Part OEM</label>
        <input class="form-field-input" name="nomor_part_oem" value="{{ old('nomor_part_oem', $item->nomor_part_oem) }}" maxlength="100" placeholder="Nomor referensi OEM">
    </div>

    <div class="form-field">
        <label class="form-field-label">Nomor Part Aftermarket</label>
        <input class="form-field-input" name="nomor_part_aftermarket" value="{{ old('nomor_part_aftermarket', $item->nomor_part_aftermarket) }}" maxlength="100" placeholder="Nomor alternatif aftermarket">
    </div>

    <div class="form-field">
        <label class="form-field-label">Satuan Kemasan & Pemakaian <span style="color:#ED172B;">*</span></label>
        <input class="form-field-input" name="satuan" value="{{ old('satuan', $item->satuan ?? 'pcs') }}" required maxlength="30" placeholder="Contoh: botol 1L, galon 4L, pcs, set">
    </div>

    <div class="form-field">
        <label class="form-field-label">Harga Beli / Pokok (Rp) <span style="color:#ED172B;">*</span></label>
        <input class="form-field-input" type="number" step="0.01" min="0" name="harga_beli" value="{{ old('harga_beli', $item->harga_beli ?? 0) }}" required placeholder="0">
    </div>

    <div class="form-field">
        <label class="form-field-label">Harga Jual / Servis (Rp) <span style="color:#ED172B;">*</span></label>
        <input class="form-field-input" type="number" step="0.01" min="0" name="harga_jual" value="{{ old('harga_jual', $item->harga_jual ?? 0) }}" required placeholder="0">
    </div>

    <div class="form-field">
        <label class="form-field-label">Batas Minimum Stok (Peringatan) <span style="color:#ED172B;">*</span></label>
        <input class="form-field-input" type="number" min="0" name="stok_minimum" value="{{ old('stok_minimum', $item->stok_minimum ?? 0) }}" required placeholder="Contoh: 5">
    </div>

    <div class="form-field">
        <label class="form-field-label">Foto Produk (Opsional)</label>
        <input class="form-field-input" style="padding-top:8px;" type="file" name="foto" accept="image/jpeg,image/png,image/webp">
    </div>

    <div class="form-field form-group-full">
        <label class="form-field-label">Spesifikasi & Catatan Teknis</label>
        <textarea class="form-field-input" name="deskripsi_spesifikasi" rows="3" style="height:auto; min-height:84px; padding-top:12px;">{{ old('deskripsi_spesifikasi', $item->deskripsi_spesifikasi) }}</textarea>
    </div>

    <div class="form-group-full">
        <div class="form-stock-notice">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            <div>
                Saldo Stok Fisik Saat Ini: <strong>{{ $item->exists ? $item->stok.' '.$item->satuan : '0' }}</strong>
                <span style="display:block; font-size:12px; color:#94A3B8; margin-top:2px;">(Read-only untuk menjaga integritas mutasi: penambahan stok wajib melalui modul Stok Masuk atau Penyesuaian Resmi).</span>
            </div>
        </div>
    </div>
</div>

<div class="form-actions-bar">
    <button type="submit" class="btn-save-master">Simpan Master Sparepart</button>
    <a href="{{ route('admin.sparepart.index') }}" class="btn-cancel-master">Batal & Kembali</a>
</div>
