# YZA AUTOMOTIVE — FINAL REFINEMENT POPUP ACTION SYSTEM
## DOKUMEN QA & ARSITEKTUR: MANAJEMEN MOBIL & SHOWROOM (MODAL SYSTEM 100%)

**Tanggal Verifikasi:** 08 November 2026  
**Target URL:** `http://127.0.0.1:8000/admin/mobil`  
**Engineer:** Senior Laravel 12 Full-Stack Engineer, UI/UX Designer, Accessibility & QA Engineer  
**Status Implementasi:** **SELESAI (100% PASS - 24/24 Tests, 197 Assertions)**

---

### 1. RINGKASAN EKSEKUTIF & PRINSIP UTAMA

Sesuai instruksi dan mandat bisnis resmi YZA Automotive, seluruh aksi kendaraan pada modul **Manajemen Mobil & Showroom** telah berhasil distandardisasi menjadi **100% POPUP / MODAL DALAM SATU HALAMAN (IN-PLACE)**:
1. **DILARANG NAVIGASI HALAMAN:** Tidak ada lagi perpindahan URL browser, pembuatan halaman baru, atau pembukaan tab baru ketika tombol aksi diklik (`<a>` navigation link untuk tombol aksi telah dieliminasi dan digantikan dengan `<button type="button">` terhubung ke JavaScript Modal Engine).
2. **KONSISTENSI SEMUA STATUS KENDARAAN:** Berlaku untuk model dengan stok fisik tersedia, model dengan stok habis, model showcase katalog APM digital tanpa unit fisik, maupun model dengan antrean Pre-Order aktif.
3. **PENGGUNAAN SISTEM MODAL EXISTING:** Tidak membuat duplikasi markup; menggunakan arsitektur modular terpadu (`#vehicleActionModalBackdrop` & `#vehicleModalDialog`) dengan dynamic content renderer, tab container terisolasi, dirty form state protection, scroll-lock, dan focus trap accessibility.
4. **ROLE SECURITY PERMISSION:** Role Owner tetap dipertahankan murni *Read-Only* (seluruh form mutasi disembunyikan / dinonaktifkan dengan notice jelas, dan backend tetap memvalidasi dengan respon `403 Forbidden`).

---

### 2. MATRIKS FITUR & PERILAKU MODAL AKSI

| No | Tombol / Aksi | Tipe Elemen | Target Modal | Fitur Utama di Dalam Modal |
|---|---|---|---|---|
| 1 | **Header: + Tambah Mobil** | `<button type="button" class="btn-primary-action">` | `openVehicleActionModal('new', 'add_unit')` | Pendaftaran unit fisik baru dari awal (Merek, Model, VIN, Mesin, Harga Beli/Jual, dsb). |
| 2 | **Detail (Ikon Mata)** | `<button type="button" class="btn-row-action is-detail">` | `openVehicleActionModal(id, 'detail')` | Tab 1: Ringkasan & Hero Card; Tab 2: Spesifikasi Teknis & VIN; Tab 3: Ketersediaan Stok Fisik & Pesanan. |
| 3 | **Tambah Unit (Ikon Plus Hijau)** | `<button type="button" class="btn-row-action is-stock is-add-unit">` | `openVehicleActionModal(id, 'add_unit')` | Form registrasi unit fisik dengan Merek/Model/Showcase otomatis terisi, input wajib VIN asli, mesin, kondisi, harga, dsb. |
| 4 | **PO (Jumlah) / Badge Pre-Order** | `<button type="button" class="preorder-count-badge">` | `openVehicleActionModal(id, 'preorder')` | Daftar member pre-order, summary bar, dan tombol `Tinjau` untuk membuka detail di dalam popup yang sama. |
| 5 | **Tinjau (di Modal Pre-Order)** | `<button type="button" class="btn-po-review">` | `viewPreorderDetail(poId, carId)` | Transisi panel `#preorderListView` &rarr; `#preorderReviewView` dengan tombol `← Kembali ke Daftar Pre-Order` tanpa reload halaman. |
| 6 | **Stok (Ikon Database)** | `<button type="button" class="btn-row-action is-stock">` | `openVehicleActionModal(id, 'stock')` | Tab 1: Ikhtisar 4 KPI stok (Fisik, Orderable, Reservasi, Terjual); Tab 2: Form penyesuaian stok dengan perhitungan delta real-time. |
| 7 | **Edit (Ikon Pensil)** | `<button type="button" class="btn-row-action is-edit">` | `openVehicleActionModal(id, 'edit')` | Form komprehensif edit identitas, spesifikasi, harga jual, dan status showroom. |
| 8 | **Menu Titik Tiga (⋯)** | Floating Dropdown | `specs`, `add_unit`, `preorder`, `history`, `gallery`, `deactivate` | Membuka modal aksi masing-masing tanpa terkena clipping/overflow tabel. |

---

### 3. DETAIL SPESIFIKASI MODAL POPUP

#### A. POPUP DETAIL KENDARAAN (`detail`)
- **Fungsi:** Menampilkan potret menyeluruh kendaraan, spesifikasi teknis, harga, dan ketersediaan stok fisik operasional.
- **Dukungan Model:** Berfungsi identik baik untuk unit fisik yang sudah ada di inventori maupun model katalog showcase virtual yang belum memiliki unit fisik.
- **Karakteristik Tab:**
  - **Ringkasan:** Hero visual kendaraan, harga jual showroom, pill status & kondisi, deskripsi ringkas.
  - **Spesifikasi Lengkap:** Parameter teknis (transmisi, bahan bakar, odometer), identitas tracking dealer (VIN, Nomor Mesin, tanggal masuk).
  - **Ketersediaan Stok:** 4 KPI stok fisik, status alokasi pesanan/booking member. Pada model katalog virtual, terdapat callout informatif dan tombol direct CTA: `+ Tambah Unit ke Inventori`.

#### B. POPUP TAMBAH UNIT FISIK (`add_unit`)
- **Fungsi:** Mendaftarkan unit fisik kendaraan ke inventori operasional showroom dealer.
- **Prefill Otomatis:** Ketika dibuka dari baris mobil tertentu (baik fisik maupun virtual), merek, model, varian, tahun pembuatan, dan showcase slug otomatis terkunci/terisi.
- **Field Wajib (Backend Validation Enforced):**
  - Merek & Model kendaraan.
  - Warna fisik unit yang hadir di dealer.
  - Kode mobil internal dealer (`YZA-UNIT-XXXX`).
  - **Nomor Rangka (VIN Fisik):** Wajib diisi input teks nyata (real input), bukan dummy/placeholder statis. Diverifikasi keunikan (`unique`) oleh `StoreMobilRequest`.
  - **Nomor Mesin Fisik:** Wajib diisi valid dan unik.
  - **Kondisi:** Pilihan Baru (`baru`) atau Bekas (`bekas`).
  - **Kilometer:** Angka odometer fisik.
  - **Harga Beli & Harga Jual:** Input nominal angka.
  - **Transmisi & Bahan Bakar:** Select option sesuai drivetrain.
  - **Catatan Dokumen / BASTK:** Input textarea kelengkapan surat kendaraan dan faktur.
- **AJAX Dynamic Response:**
  - Mengirim payload ke `admin.mobil.store` dengan header JSON.
  - Respon sukses (`200 OK`, `success: true`) menutup modal, menampilkan *Toast Notification* sukses, serta secara reaktif memperbarui baris tabel dan counter KPI tanpa refresh halaman.
  - Respon gagal validasi (`422 Unprocessable Entity`) merender pesan kesalahan inline langsung di bawah input bersangkutan (`.modal-field-error`) dengan outline merah.
  - Role Owner diblokir dengan tampilan notice read-only dan proteksi backend `403 Forbidden`.

#### C. POPUP PRE-ORDER & REVIEW SISTEM TUNGGAL (`preorder`)
- **Fungsi:** Mengelola antrean permintaan kendaraan dari member YZA.
- **Header Summary Bar:** Menampilkan total pre-order aktif, model kendaraan, dan evaluasi stok fisik dealer secara seketika (hijau jika stok ready &gt; 0, oranye jika stok habis/belum ada unit fisik).
- **Tampilan Daftar Antrean (`#preorderListView`):**
  - Tabel berisi kode pre-order, tanggal pengajuan, nama member, nomor telepon, varian, warna diminta, status, dan tombol aksi `Tinjau`.
  - State kosong yang rapi dilengkapi tombol `+ Daftarkan Unit Baru ke Inventori` jika belum ada antrean.
- **Tampilan Tinjau Pre-Order (`#preorderReviewView`):**
  - **SISTEM MODAL TUNGGAL (NO STACKING / NO RELOAD):** Dikliknya tombol `Tinjau` langsung melakukan transisi tampilan internal di dalam modal yang sama (`display: none` pada daftar, `display: block` pada review) tanpa membuka popup bertumpuk atau navigasi halaman.
  - Terdapat tombol `← Kembali ke Daftar Pre-Order` di bagian atas untuk kembali ke tabel antrean dengan mulus.
  - Mengambil data mutakhir melalui AJAX endpoint `admin.preorder-mobil.detail`.
  - Menampilkan 2 kartu terstruktur: **Profil Member Pemesan** (kontak, email, status keanggotaan aktif) dan **Spesifikasi Kendaraan Diminta** (varian, tahun, warna).
  - Menampilkan box **Evaluasi Stok Fisik Dealer**: Jika stok fisik telah tersedia, disediakan tombol `Lanjutkan ke SPK`; jika belum tersedia, disediakan tombol `+ Tambah Unit Fisik` yang langsung berpindah ke modal registrasi unit.

#### D. POPUP MANAJEMEN STOK (`stock`)
- **Fungsi:** Mengatur kuantitas stok fisik kendaraan secara terkendali.
- **Pemisahan Tab Nyata:**
  - **Tab Ikhtisar Stok:** Monitoring murni (read-only) dengan 4 metrik KPI, peringatan reservasi pesanan, dan tabel 4 mutasi riwayat stok terbaru.
  - **Tab Penyesuaian Stok:** Formulir penyesuaian kuantitas dengan kalkulasi delta dinamis (+/-) dan validasi proteksi reservasi SPK aktif.
  - Respon AJAX memperbarui sel tabel `.td-stock-physical`, `.td-stock-ready`, serta badge `.td-availability` seketika.

#### E. POPUP EDIT KENDARAAN (`edit`)
- **Fungsi:** Memperbarui data umum, harga jual, spesifikasi, atau status publikasi kendaraan.
- Dilengkapi penanganan error 422 inline, dirty form tracking, dan pembaruan sel tabel langsung saat disimpan.

#### F. POPUP GALERI FOTO & WARNA (`gallery`)
- **Fungsi:** Mengelola visual multi-sudut dan membedakan secara tegas warna katalog showroom vs warna fisik unit di inventori.
- **Tab A:** Foto Kendaraan & 4-sudut (Depan, Samping, Belakang, Kabin) dengan preview interaktif.
- **Tab B:** Warna Katalog Showroom (pilihan konfigurasi member di website publik).
- **Tab C:** Warna Unit Inventori Aktual (alokasi stok fisik nyata dealer).

#### G. POPUP KONFIRMASI NONAKTIFKAN (`deactivate`)
- **Fungsi:** Menonaktifkan tayangan kendaraan dari showroom publik dengan proteksi ganda (tidak dapat menonaktifkan unit terjual, menampilkan peringatan jika ada reservasi member yang masih mengikat).

---

### 4. AKSESIBILITAS & PERILAKU INTERAKTIF UX

1. **Scroll-Lock Latar Belakang:** Ketika modal terbuka, class `.modal-open` disuntikkan ke `<body>`, memblokir scrolling halaman latar secara penuh (`overflow: hidden !important;`).
2. **Backdrop & Escape Key:** Menekan tombol Escape atau mengklik latar belakang semi-transparan akan menutup modal secara aman.
3. **Pencegahan Kehilangan Data (Dirty Form Protection):** Jika pengguna telah mengubah isian formulir pada form Tambah Unit atau Edit Kendaraan, penutupan modal akan memunculkan dialog konfirmasi pencegahan: *"Terdapat perubahan formulir yang belum disimpan. Yakin ingin menutup popup?"*.
4. **Focus Trap & Restorasi:** Fokus keyboard otomatis diarahkan ke `#vehicleModalDialog` saat terbuka, dan dikembalikan ke tombol pemicu awal saat modal ditutup.
5. **Loading States:** Semua tombol submit AJAX (`btnSubmitAddUnit`, `btnSubmitStockAdjust`, `btnSubmitEditVehicle`, dsb) otomatis berubah teks (*"Menyimpan..."*) dan dinonaktifkan (`disabled = true`) selama proses jaringan berlangsung.

---

### 5. HASIL VERIFIKASI & BUKTI TESTING AUTOMATION

Seluruh rangkaian pengujian unit dan integrasi dijalankan menggunakan database test terisolasi `yza_automotive_laravel_test` (MySQL), menjaga database operasional tetap **100% aman dan tidak tersentuh**:

```bash
# 1. Test Suite: Unified Vehicle Management (24 Tests)
C:\xampp\php\php.exe vendor/bin/phpunit tests/Feature/UnifiedVehicleManagementTest.php
........................                                          24 / 24 (100%)
Time: 00:12.783, Memory: 50.00 MB
OK (24 tests, 197 assertions)

# 2. Test Suite: Catalog & Inventory Preorder Consistency (9 Tests)
C:\xampp\php\php.exe vendor/bin/phpunit tests/Feature/CatalogInventoryPreorderConsistencyTest.php
.........                                                           9 / 9 (100%)
Time: 00:08.088, Memory: 48.00 MB
OK (9 tests, 115 assertions)

# 3. Production Build Frontend Assets
npm run build
✓ 58 modules transformed.
✓ built in 4.63s
```

#### Ringkasan Assertion Kunci:
1. `test_all_table_vehicle_actions_and_header_button_open_modal_without_navigation_links`:
   - Terverifikasi tombol `+ Tambah Mobil` membuka modal via `onclick="openVehicleActionModal('new', 'add_unit')"`.
   - Terverifikasi tidak ada link navigasi ke `/admin/mobil/create`.
   - Terverifikasi seluruh tombol baris (Detail, Tambah Unit, Stok, Edit, PO) memiliki modal trigger.
   - Terverifikasi keberadaan struktur modal terpadu `#vehicleModalDialog`, `#addUnitVehicleForm`, `#preorderListView`, dan `#preorderReviewView`.
2. `test_ajax_store_vehicle_registers_physical_unit_and_returns_json_response`:
   - Terverifikasi pendaftaran unit fisik dengan VIN nyata melalui modal berhasil mengembalikan respon JSON `success: true`.
   - Terverifikasi tercatat pada tabel `mobil` dan inisialisasi inventori pada `vehicle_inventories`.
3. `test_owner_role_forbidden_from_submitting_vehicle_registration`:
   - Terverifikasi pengiriman mutasi unit baru oleh role Owner ditolak dengan kode status `403 Forbidden`.
4. `test_preorder_detail_endpoint_returns_json_for_review_modal`:
   - Terverifikasi endpoint `admin.preorder-mobil.detail` mengembalikan struktur data lengkap untuk review modal tanpa reload.

---

### 6. KESIMPULAN

Penyempurnaan arsitektur modal aksi pada halaman **Manajemen Mobil & Showroom** telah selesai secara menyeluruh sesuai seluruh ketentuan Codex:
- **100% aksi kendaraan kini beroperasi sebagai Modal Popup di halaman yang sama.**
- Tidak ada reload halaman atau tab baru ketika melakukan aksi.
- Model katalog showcase tanpa unit fisik dapat langsung ditambahkan unit inventori fisiknya melalui tombol aksi di baris tabel maupun di dalam modal detail.
- Antrean Pre-Order dapat ditinjau dan dievaluasi stoknya secara instan di dalam popup yang sama.
- Seluruh pengujian otomatis telah lulus dengan hasil **33 PASS (312 assertions)** dan build aset Vite berjalan tanpa error.

