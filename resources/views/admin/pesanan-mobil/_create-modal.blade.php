@if(auth()->user()->hasPermission('sales.manage') && auth()->user()->roleKey() !== 'owner')
<dialog id="order-create-dialog" class="order-create-dialog" data-reopen="{{ $errors->createOrder->any() ? '1' : '0' }}" aria-labelledby="order-create-title">
    <form method="post" action="{{ route('admin.pesanan-mobil.store') }}" class="order-create-form" data-spk-form="1" data-quote-url="{{ route('admin.pesanan-mobil.quote') }}" data-storage-ready="{{ $spkStorageReady ? '1' : '0' }}">
        @csrf
        <header><div><small>YZA AUTOMOTIVE</small><h2 id="order-create-title">Tambah Pesanan Mobil — SPK</h2></div><button type="button" data-order-create-close aria-label="Tutup dialog">×</button></header>
        <ol class="spk-stepper" aria-label="Tahapan SPK"><li data-spk-step-label aria-current="step"><b>1</b><span>Pelanggan &amp; Kendaraan</span></li><li data-spk-step-label><b>2</b><span>Harga &amp; Diskon</span></li><li data-spk-step-label><b>3</b><span>Konfirmasi SPK</span></li></ol>
        <script type="application/json" data-spk-catalog>{!! \Illuminate\Support\Js::encode($spkVehicles) !!}</script>
        <script type="application/json" data-spk-color-catalog>{!! \Illuminate\Support\Js::encode($spkCatalog) !!}</script>
        <div class="order-create-body">
            <p>Catat pesanan awal. Kendaraan akan dicadangkan sesuai ketersediaan; transaksi dibuat setelah verifikasi.</p>
            @if($errors->createOrder->any())<div class="order-create-errors" role="alert"><ul>@foreach($errors->createOrder->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <fieldset data-spk-step="0"><legend>Pelanggan &amp; kendaraan</legend>
            @php($selectedMember = $orderCustomers->firstWhere('id_pelanggan', old('id_pelanggan')))
            <div class="order-member-picker" data-member-picker>
                <label for="order-member-search">Pilih Member Aktif</label>
                <input type="hidden" name="id_pelanggan" value="{{ $selectedMember?->id_pelanggan }}">
                <input id="order-member-search" type="search" data-member-search role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="order-member-options" autocomplete="off" placeholder="Cari nama atau ID member…" value="{{ $selectedMember ? $selectedMember->nama_lengkap.' — '.$selectedMember->membership->member_id : '' }}" required autofocus>
                <div id="order-member-options" class="order-member-options" role="listbox" aria-label="Member aktif" hidden>
                    @foreach($orderCustomers as $customer)
                        <button type="button" role="option" data-member-option data-id="{{ $customer->id_pelanggan }}" data-name="{{ $customer->nama_lengkap }}" data-member="{{ $customer->membership->member_id }}" data-code="{{ $customer->kode_pelanggan }}" data-email="{{ $customer->user?->email }}" data-phone="{{ $customer->no_hp }}" data-address="{{ $customer->alamat }}" aria-selected="{{ $selectedMember?->id_pelanggan === $customer->id_pelanggan ? 'true' : 'false' }}"><strong>{{ $customer->nama_lengkap }}</strong><span>{{ $customer->membership->member_id }} <b>Member Aktif</b></span></button>
                    @endforeach
                    <p data-member-empty role="status" @if($orderCustomers->isNotEmpty()) hidden @endif>Tidak ada member aktif yang sesuai.</p>
                </div>
                <small>Hanya akun member aktif dengan bukti aktivasi yang sesuai. Penjualan non-member tetap melalui Penjualan Mobil.</small>
            </div>
            <div class="order-create-fields"><label>Model kendaraan <select data-spk-model required><option value="">Pilih model kendaraan</option>@foreach(collect($spkCatalog)->unique('model_key') as $vehicle)<option value="{{ $vehicle['model_key'] }}">{{ $vehicle['name'] }}</option>@endforeach</select></label><label>Varian <select data-spk-variant required><option value="">Pilih varian</option>@foreach($spkCatalog as $vehicle)<option value="{{ $vehicle['group'] }}">{{ $vehicle['variant'] }}</option>@endforeach</select></label></div>
            <div><span class="spk-field-label">Warna katalog &amp; ketersediaan inventori</span><div class="spk-swatches" data-spk-swatches role="group" aria-label="Warna katalog dan stok"></div><p data-spk-no-color>Pilih model dan varian untuk melihat warna katalog serta stoknya.</p></div>
            <p data-spk-color-status role="status"></p>
            <label>Unit eligible / sumber stok <select name="id_mobil" data-spk-unit required><option value="">Pilih unit yang tersedia</option>@foreach($spkVehicles as $vehicle)<option value="{{ $vehicle['id'] }}" @selected(old('id_mobil') == $vehicle['id'])>{{ $vehicle['code'] }} · {{ $vehicle['color'] }} · {{ $vehicle['aggregate'] ? 'Stok agregat' : 'Unit fisik' }}</option>@endforeach</select></label>
            <input type="hidden" name="warna_pilihan" value="{{ old('warna_pilihan') }}">
            <div class="spk-vehicle-preview" data-spk-preview hidden><img data-spk-image alt="Foto unit terpilih"><div><strong data-spk-name></strong><p data-spk-unit-info></p><small data-spk-vin></small><p data-spk-stock></p></div></div>
            <small>Warna katalog bukan bukti stok. <a href="{{ route('admin.preorder-mobil.index') }}">Buka pusat Pre-Order</a>. Pengajuan warna tertentu belum didukung langsung melalui akun staf; pilihan tanpa stok tidak membuat reservasi atau pre-order otomatis.</small>
            <div class="order-create-fields"><label>Nama pembeli <input name="nama_pembeli" maxlength="120" value="{{ old('nama_pembeli') }}" required></label><label>Telepon <input name="telepon_pembeli" maxlength="25" value="{{ old('telepon_pembeli') }}" required></label></div>
            <label>Email (opsional) <input name="email_pembeli" type="email" maxlength="120" value="{{ old('email_pembeli') }}"></label>
            <label>Alamat pembeli <textarea name="alamat_pembeli" rows="2" maxlength="2000" required>{{ old('alamat_pembeli') }}</textarea></label>
            </fieldset>
            <fieldset data-spk-step="1" hidden><legend>Harga &amp; diskon</legend><div class="spk-price-grid"><div class="spk-discount-inputs">
                <label>Jenis diskon <select name="manual_discount_type"><option value="none" @selected(old('manual_discount_type','none') === 'none')>Tanpa diskon</option><option value="percentage" @selected(old('manual_discount_type') === 'percentage')>Persentase (%)</option><option value="nominal" @selected(old('manual_discount_type') === 'nominal')>Nominal Rupiah (Rp)</option></select></label>
                <label>Nilai diskon <input name="manual_discount_value" type="number" min="0" step="0.01" value="{{ old('manual_discount_value',0) }}" required></label>
                <label>Alasan pengajuan diskon <textarea name="manual_discount_reason" rows="3" maxlength="1000">{{ old('manual_discount_reason') }}</textarea></label>
                <p>Setiap diskon di atas nol wajib ditinjau Super Admin independen. Batas harga minimum/margin belum ditetapkan; pengajuan tetap menunggu dan belum menjadi harga yang disahkan.</p>
                @unless($spkStorageReady)<p role="status">Penyimpanan draft diskon belum tersedia sampai pembaruan schema SPK diterapkan. Pesanan tanpa diskon tetap tersedia.</p>@endunless
            </div><dl class="spk-price-summary"><div><dt>Harga dasar dari server</dt><dd data-spk-base>—</dd></div><div><dt>Potongan manual <span data-spk-discount-label></span></dt><dd data-spk-discount>—</dd></div><div><dt>Estimasi setelah diskon</dt><dd data-spk-total>—</dd></div><div><dt>Persetujuan</dt><dd data-spk-approval>Belum dihitung</dd></div><p>Estimasi ini belum merupakan invoice. Promo Member tidak digabungkan dengan diskon manual.</p></dl></div></fieldset>
            <fieldset data-spk-step="2" hidden><legend>Konfirmasi SPK</legend><dl class="spk-confirm-summary"><div><dt>Member</dt><dd data-spk-confirm-member>—</dd></div><div><dt>Pembeli / kontak</dt><dd data-spk-confirm-buyer>—</dd></div><div><dt>Kendaraan / warna / unit</dt><dd data-spk-confirm-vehicle>—</dd></div><div><dt>Identitas fisik</dt><dd data-spk-confirm-vin>—</dd></div><div><dt>Harga dasar</dt><dd data-spk-confirm-base>—</dd></div><div><dt>Potongan manual</dt><dd data-spk-confirm-discount>—</dd></div><div><dt>Estimasi yang diajukan</dt><dd data-spk-confirm-total>—</dd></div><div><dt>Status</dt><dd data-spk-confirm-approval>—</dd></div></dl>
            <p>Nomor pesanan dibuat server setelah penyimpanan berhasil. SPK ini mencatat permintaan dan reservasi; pembayaran, invoice, PDI, serta serah terima mengikuti proses resmi berikutnya.</p>
            <label class="order-create-consent"><input type="checkbox" name="syarat_disetujui" value="1" @checked(old('syarat_disetujui')) required><span>Saya telah memastikan data pembeli dan persetujuan syarat pemesanan dengan pelanggan.</span></label>
            </fieldset>
            <input type="hidden" name="expected_base_price" value="">
            <p class="spk-quote-status" data-spk-status role="status" aria-live="polite"></p>
        </div>
        <footer><button type="button" data-order-create-close>Batal</button><button type="button" data-spk-back hidden>Kembali</button><button type="button" data-spk-next>Lanjut</button><button type="submit" data-spk-save hidden>Simpan Pesanan</button></footer>
    </form>
</dialog>
@endif
