# LAPORAN QA & IMPLEMENTASI: SINGLE THREE-DOT ACTION MENU
**Sistem:** YZA Automotive Admin Portal  
**Modul Target:** Manajemen Mobil & Showroom (`/admin/mobil?tab=inventori`)  
**Dokumen:** `docs/qa/YZA_SINGLE_THREE_DOT_ACTION_MENU_REFINEMENT.md`  
**Status:** **RESOLVED & VERIFIED PASS** (100% Zero-Redundancy & In-Place Modal System)

---

## 1. LATAR BELAKANG & KEPUTUSAN DESAIN FINAL

Sebelumnya, pada tabel Manajemen Mobil & Showroom terdapat tombol aksi ganda di luar menu:
- Baris unit fisik memiliki tombol luar `Detail`, `Stok`, `Edit`, dan `PO (n)` di samping tombol titik tiga (`...`).
- Baris virtual katalog memiliki tombol luar `Detail`, `Tambah Unit`, dan `PO (n)` di samping tombol titik tiga (`...`).
- Di dalam menu titik tiga, terdapat tindakan yang menduplikasi tombol luar serta tab internal modal (seperti `Lihat Spesifikasi` dan `Riwayat Stok`).

**Keputusan Desain Final yang Diterapkan:**
> **SETIAP BARIS KENDARAAN HANYA MEMILIKI SATU TOMBOL AKSI, YAITU IKON TITIK TIGA (`⋯`).**  
> Tidak ada tombol aksi lain di luar ikon tersebut.  
> Seluruh tindakan terpusat di dalam satu dropdown elegan yang relevan dengan tipe kendaraan dan hak akses pengguna.

---

## 2. DAFTAR TOMBOL DUPLIKAT YANG DIHILANGKAN

| Lokasi Sebelumnya | Tombol yang Dihilangkan | Status Baru | Alasan & Integrasi |
|---|---|---|---|
| Kolom AKSI Tabel | Tombol luar `Detail` | **Dihapus dari tabel** | Dipusatkan ke menu `Detail Kendaraan` / `Detail Katalog` di dropdown. |
| Kolom AKSI Tabel | Tombol luar `Stok` | **Dihapus dari tabel** | Dipusatkan ke menu `Kelola Stok` di dropdown. |
| Kolom AKSI Tabel | Tombol luar `Edit` | **Dihapus dari tabel** | Dipusatkan ke menu `Edit Kendaraan` / `Edit Katalog` di dropdown. |
| Kolom AKSI Tabel | Tombol luar `Tambah Unit` | **Dihapus dari tabel** | Dipusatkan ke menu `Tambah Unit Fisik` di dropdown. |
| Kolom AKSI Tabel | Tombol luar `PO (n)` | **Dihapus dari tabel** | Jumlah pre-order tetap berada di kolom **PRE-ORDER AKTIF** sebagai badge, dan tindakan pre-order masuk ke dropdown saat aktif. |
| Dropdown Action | Menu `Lihat Spesifikasi` | **Dihapus dari dropdown** | Spesifikasi Lengkap sudah tersedia sebagai tab di dalam popup **Detail Kendaraan**. |
| Dropdown Action | Menu `Riwayat Stok` | **Dihapus dari dropdown** | Riwayat Mutasi Stok sudah tersedia sebagai tab di dalam popup **Kelola Stok**. |

---

## 3. SUSUNAN DROPDOWN FINAL

### A. Mobil Ready / Memiliki Unit Fisik (`!car.is_virtual`)
1. **Detail Kendaraan** (`detail`) — Membuka popup Detail Kendaraan (dilengkapi tab Ringkasan, Spesifikasi Lengkap, dan Ketersediaan Stok).
2. **Kelola Stok** (`stock`) — Membuka popup Kelola Stok (dilengkapi tab Ikhtisar Stok, Penyesuaian Stok, dan Riwayat Stok). *Hanya untuk pengguna berwenang (bukan Owner).*
3. **Edit Kendaraan** (`edit`) — Membuka popup Edit Identitas & Harga Kendaraan. *Hanya untuk pengguna berwenang.*
4. **Galeri & Warna** (`gallery`) — Membuka popup Kelola Galeri Foto Multi-Sudut dan Master Warna.
5. **Permintaan Pre-Order (N)** (`preorder`) — *Hanya muncul jika terdapat permintaan aktif (`poCount > 0`).*
6. **Nonaktifkan Mobil** (`deactivate`) — Garis pemisah (*divider*) + tombol merah (*danger*). *Hanya muncul jika diizinkan dan unit belum terjual.*

### B. Mobil Tanpa Unit Fisik / Katalog (`car.is_virtual === true`)
1. **Detail Katalog** (`detail`) — Membuka popup Detail Katalog dan Spesifikasi Showcase APM.
2. **Tambah Unit Fisik** (`add_unit`) — Membuka popup Registrasi Unit Fisik Baru yang langsung terikat dengan model katalog terpilih. *Hanya untuk pengguna berwenang.*
3. **Permintaan Pre-Order (N)** (`preorder`) — Menampilkan jumlah permintaan aktif aktual (contoh: **Permintaan Pre-Order (1)** untuk Bugatti Chiron). *Disembunyikan jika tidak ada permintaan (`poCount === 0`).*
4. **Edit Katalog** (`edit`) — Membuka popup Edit Model Katalog. *Hanya untuk pengguna berwenang.*
5. **Galeri & Warna** (`gallery`) — Membuka popup Galeri & Master Warna Katalog.
6. **Nonaktifkan Model** (`deactivate`) — Garis pemisah + tombol merah (*danger*). *Hanya untuk pengguna berwenang.*

---

## 4. DESAIN PREMIUM, POSISIONING & RESPONSIF

1. **Lebar Kolom AKSI Kompak:**
   - Lebar kolom sticky `th.th-action` dan `td.td-action` dipangkas menjadi **`72px`** (sebelumnya `200px`).
   - Menghemat lebih dari `128px` lebar horizontal tabel, menjadikan tabel lebih rapi, tidak sesak, dan responsif pada layar desktop maupun tablet pada zoom 100%.

2. **Gaya Dropdown Premium (`.floating-action-dropdown-menu`):**
   - **Background:** Putih bersih (`#ffffff`).
   - **Border:** Abu-abu sangat lembut (`1px solid #e2e8f0`).
   - **Border Radius:** `12px`.
   - **Shadow:** `0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08)`.
   - **Lebar Menu:** `230px`.
   - **Aksen Teks:** Navy `#1e293b`, hover `#0f172a` dengan background lembut `#f8fafc`.
   - **Aksen Berbahaya:** Teks `#dc2626`, hover background merah lembut `#fef2f2`.
   - **Transisi:** Animasi halus fade/slide `150ms`.

3. **Smart Viewport & Clamping Positioning:**
   - Menggunakan portal fixed positioning (`z-index: 9999`) di luar container scroll tabel, sehingga menu **tidak pernah terpotong** oleh overflow tabel, sticky header, sidebar, atau footer.
   - **Auto-Flip Vertikal:** Jika tombol berada di baris bawah tabel yang mendekati batas bawah layar, menu otomatis terbuka ke atas (*flipped upward*).
   - **Horizontal Clamping:** Posisi koordinat horizontal menu otomatis terkunci di dalam batas viewport (margin minimal 10px dari tepi layar).

4. **Interaksi & Aksesibilitas:**
   - **Buka:** 1-klik pada tombol titik tiga (`btn-more-options`).
   - **Tutup:** Klik di luar menu, klik item aksi, tekan tombol `Escape`, resize jendela, atau scroll halaman.
   - **Keyboard Navigation:** Mendukung navigasi tombol panah atas/bawah (`ArrowUp` / `ArrowDown`) antar item menu.
   - **Fokus Kembali:** Menutup dengan `Escape` mengembalikan fokus ke tombol titik tiga pemanggil.

---

## 5. KEAMANAN & AUTHORIZATION

- **Owner Read-Only Protection:** Akun Owner tidak ditampilkan opsi mutasi (`Kelola Stok`, `Edit`, `Tambah Unit`, `Nonaktifkan`). Owner hanya melihat tindakan inspeksi (`Detail Kendaraan`, `Galeri & Warna`, `Permintaan Pre-Order`).
- **CSRF & Backend Guard:** Seluruh aksi modal tetap diproteksi token CSRF dan otorisasi permission Laravel (`inventory.manage`, `inventory.delete`).
- **Zero URL Navigation:** Seluruh aksi membuka popup/modal in-place di halaman yang sama tanpa reload, tanpa redirect, dan tanpa pembukaan tab baru.

---

## 6. HASIL PENGUJIAN OTOMATIS (AUTOMATED TEST SUITE)

### A. Pengujian Fitur Manajemen Kendaraan Terpadu
File: `tests/Feature/UnifiedVehicleManagementTest.php`  
- **Test 10:** Modal action triggers data attributes di dropdown (*detail, stock, edit, add_unit, preorder, gallery, deactivate*).
- **Test 20:** Single page action flow tanpa navigasi full-page.
- **Test 26:** Pre-order count & modal JSON payload sinkronisasi mutlak.
- **Test 27 (Baru):** `test_single_three_dot_action_menu_architecture`:
  - Memverifikasi setiap baris tabel hanya memiliki 1 tombol (`.btn-more-options`).
  - Memverifikasi nol tombol luar `.btn-row-action`.
  - Memverifikasi lebar header AKSI kompak `72px`.
  - Memverifikasi Bugatti Chiron memiliki handler dropdown mandiri.
- **Hasil:** **27 tests, 287 assertions PASSED (100%)**

### B. Pengujian Konsistensi Katalog & Pre-Order
File: `tests/Feature/CatalogInventoryPreorderConsistencyTest.php`  
- **Hasil:** **9 tests, 115 assertions PASSED (100%)**

### C. Kompilasi Frontend
- `npm run build` sukses (Vite v7.3.7, 5.34s).

---

## 7. RINGKASAN VERIFIKASI AKHIR

| Kriteria Persyaratan | Status | Bukti Verifikasi |
|---|---|---|
| Satu Ikon Titik Tiga per Baris | **TERPENUHI** | Tepat 1 tombol `.btn-more-options` per `td.td-action`. |
| Tanpa Tombol Luar Duplikat | **TERPENUHI** | Nol tombol `Detail`, `Stok`, `Edit`, `Tambah Unit`, atau `PO` di luar menu. |
| Badge Pre-Order Tetap di Kolomnya | **TERPENUHI** | Badge `1 permintaan` tetap tampil di kolom **PRE-ORDER AKTIF**. |
| Lebar Kolom AKSI Kompak | **TERPENUHI** | `width: 72px; min-width: 72px;` |
| Menu Mobil Fisik Lengkap & Relevan | **TERPENUHI** | Detail, Kelola Stok, Edit, Galeri, Pre-Order (jika > 0), Nonaktifkan. |
| Menu Mobil Virtual Katalog Lengkap | **TERPENUHI** | Detail, Tambah Unit, Pre-Order (1 untuk Chiron), Edit, Galeri, Nonaktifkan. |
| Seluruh Aksi Membuka Modal | **TERPENUHI** | 100% modal in-place, tanpa reload halaman atau tab baru. |
| Auto-Flip & Anti-Clipping | **TERPENUHI** | Portal fixed dengan deteksi batas layar bawah otomatis flip ke atas. |
| Role Security Terjaga | **TERPENUHI** | Owner tetap read-only (`403 Forbidden` pada form backend). |

