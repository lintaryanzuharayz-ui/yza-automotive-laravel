<section class="pom-overlay" data-preorder-modal hidden>
    <div class="pom-backdrop" data-pom-backdrop></div>
    <div class="pom-dialog" role="dialog" aria-modal="true" aria-labelledby="pom-title" aria-describedby="pom-code">
        <header class="pom-header"><div><small id="pom-code" data-pom-field="code">PRE-ORDER</small><h2 id="pom-title">Detail &amp; Tindak Lanjut Pre-Order</h2></div><button type="button" data-pom-close aria-label="Tutup pengelolaan pre-order">×</button></header>
        <div class="pom-tabs" role="tablist" aria-label="Pengelolaan pre-order">
            @foreach(['detail' => 'Detail Permintaan', 'stock' => 'Ketersediaan Stok', 'followup' => 'Tindak Lanjut', 'history' => 'Riwayat'] as $tab => $label)
                <button type="button" role="tab" id="pom-tab-{{ $tab }}" aria-controls="pom-panel-{{ $tab }}" aria-selected="false" data-pom-tab="{{ $tab }}">{{ $label }}</button>
            @endforeach
        </div>
        <div class="pom-scroll">
            <p class="pom-loading" data-pom-loading role="status" hidden>Memuat data pre-order…</p>
            <div class="pom-error" data-pom-error role="alert" hidden><p data-pom-error-text></p><button type="button" data-pom-retry>Coba lagi</button></div>
            <p class="pom-success" data-pom-success role="status" hidden></p>
            <section id="pom-panel-detail" role="tabpanel" aria-labelledby="pom-tab-detail" data-pom-panel="detail" hidden>
                <div class="pom-vehicle"><img data-pom-image alt=""><div><span class="pom-status" data-pom-field="status"></span><h3 data-pom-field="vehicle"></h3><p>Spesifikasi yang tercatat pada permintaan member</p></div></div>
                <dl class="pom-data">
                    @foreach(['variant' => 'Varian', 'model' => 'Model', 'year' => 'Tahun', 'color' => 'Warna', 'transmission' => 'Transmisi katalog', 'fuel' => 'Bahan bakar katalog', 'reference_price' => 'Harga referensi saat permintaan', 'created_at' => 'Tanggal permintaan', 'customer.name' => 'Nama member', 'customer.code' => 'Kode member', 'customer.phone' => 'Telepon member', 'customer.email' => 'Email member'] as $field => $label)
                        <div><dt>{{ $label }}</dt><dd data-pom-field="{{ $field }}">Belum tercatat</dd></div>
                    @endforeach
                </dl>
                <p class="pom-note">Harga referensi bukan tagihan atau jaminan harga transaksi. Harga akhir mengikuti checkout resmi.</p>
                <h3>Catatan pelanggan</h3><p>Workflow pre-order saat ini belum menyimpan catatan pelanggan khusus.</p>
            </section>
            <section id="pom-panel-stock" role="tabpanel" aria-labelledby="pom-tab-stock" data-pom-panel="stock" hidden>
                <div class="pom-section-head"><div><h3>Kecocokan inventori</h3><p>Model, varian, tahun, warna, dan atribut katalog yang tercatat harus sesuai.</p></div><button type="button" class="pom-primary" data-pom-check>Cek ulang stok</button></div>
                <p data-pom-stock-status role="status">Belum diperiksa.</p><div class="pom-stock-list" data-pom-stock-list></div>
                <a class="pom-primary pom-manage" data-pom-manage hidden>Kelola Stok Mobil Ini ↗</a><p data-pom-field="stock_management_note"></p>
                <p class="pom-note">Cek stok tidak melakukan reservasi, mengurangi stok, membuat pesanan, atau mengubah penjualan. Stok agregat belum menentukan nomor rangka unit fisik.</p>
            </section>
            <section id="pom-panel-followup" role="tabpanel" aria-labelledby="pom-tab-followup" data-pom-panel="followup" hidden>
                <h3>Tindakan yang tersedia</h3><p>Gunakan alur resmi pre-order. Checkout tetap dilakukan member melalui katalog setelah stok terverifikasi.</p>
                <a class="pom-primary pom-manage" data-pom-manage hidden>Kelola Stok Mobil Ini ↗</a>
                <p class="pom-note" data-pom-field="notification_status"></p>
                <p class="pom-note" data-pom-automation-status hidden>Pemberitahuan otomatis belum diaktifkan. Pemeriksaan dan pengelolaan stok tetap dapat digunakan.</p>
                <p class="pom-note" data-pom-readonly hidden>Akun ini hanya dapat memeriksa detail dan stok, atau permintaan sudah tidak dapat ditindaklanjuti.</p>
                <form data-pom-form>
                    <label for="pom-action">Pilih tindakan</label><select id="pom-action" name="action" required><option value="">Pilih tindakan…</option><option value="notify">Periksa stok dan beri tahu member</option><option value="resend">Kirim ulang pemberitahuan (satu kali per periode ketersediaan)</option><option value="cancel">Batalkan pre-order</option></select>
                    <p data-pom-eligibility-note role="status"></p>
                    <div data-pom-reason-group hidden><label for="pom-reason">Alasan kirim ulang</label><textarea id="pom-reason" data-pom-reason maxlength="1000" placeholder="Wajib minimal 10 karakter untuk kirim ulang"></textarea></div>
                    <p class="pom-note">Pemberitahuan hanya dikirim jika pemeriksaan backend menemukan stok yang cocok. Unit belum direservasi dan member tetap mengikuti checkout resmi.</p>
                    <button type="submit" class="pom-primary" data-pom-review>Tinjau tindakan</button>
                </form>
                <div class="pom-confirm" data-pom-confirm hidden>
                    <h3>Konfirmasi tindakan</h3><p data-pom-confirm-text></p><p>Ketik kode <strong data-pom-confirm-code></strong> untuk mengonfirmasi.</p>
                    <label for="pom-confirmation">Kode pre-order</label><input id="pom-confirmation" type="text" autocomplete="off" spellcheck="false" data-pom-confirmation>
                    <div class="pom-confirm-actions"><button type="button" data-pom-back>Kembali</button><button type="button" class="pom-primary" data-pom-save disabled>Konfirmasi &amp; simpan</button></div>
                </div>
                <p class="pom-note">Tidak ada perubahan status bebas. Setiap tindakan diperiksa kembali oleh server dan dicatat jika penyimpanan riwayat sudah diaktifkan.</p>
            </section>
            <section id="pom-panel-history" role="tabpanel" aria-labelledby="pom-tab-history" data-pom-panel="history" hidden>
                <h3>Riwayat perubahan</h3><div data-pom-history-list class="pom-history"></div><p class="pom-note">Hanya aktivitas yang benar-benar tersimpan ditampilkan. Waktu baca rinci tidak disimpan oleh sistem notifikasi.</p>
                <h3>Waktu yang tercatat</h3><dl class="pom-data"><div><dt>Permintaan dibuat</dt><dd data-pom-field="created_at"></dd></div><div><dt>Notifikasi stok dikirim</dt><dd data-pom-field="notified_at"></dd></div><div><dt>Dibatalkan</dt><dd data-pom-field="cancelled_at"></dd></div></dl>
            </section>
        </div>
        <footer class="pom-footer"><div class="pom-discard" data-pom-discard hidden role="alert"><span>Buang pilihan tindakan yang belum disimpan?</span><button type="button" data-pom-keep>Tetap di modal</button><button type="button" data-pom-discard-confirm>Buang &amp; tutup</button></div><span data-pom-footer-status>Data pre-order</span><button type="button" data-pom-close>Tutup</button></footer>
    </div>
</section>
