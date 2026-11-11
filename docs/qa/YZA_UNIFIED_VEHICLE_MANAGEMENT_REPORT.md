# YZA AUTOMOTIVE — LAPORAN PENGGABUNGAN INVENTORI & KATALOG
## MANAJEMEN MOBIL & SHOWROOM TERPADU

**Dokumen:** `docs/qa/YZA_UNIFIED_VEHICLE_MANAGEMENT_REPORT.md`  
**Tanggal:** 08 November 2026  
**Status Implementasi:** COMPLETE & FULLY VERIFIED (PASS)  
**Hasil Pengujian:** **712 PASS · 4.132 Assertions · 0 Failures**  

---

## 1. EXECUTIVE SUMMARY & LATAR BELAKANG

Sebelumnya, Super Admin memiliki dua menu terpisah di navigasi sidebar:
1. **Inventori & Stok Mobil** (`/admin/mobil`)
2. **Katalog & Showroom** (`/admin/katalog-showroom`)

Keduanya mengelola entitas kendaraan yang sama namun dengan tampilan dan perspektif terpisah, sehingga berisiko menimbulkan kebingungan data atau pemeliharaan redundan. Sesuai instruksi keputusan bisnis resmi YZA Automotive, kedua menu tersebut kini telah berhasil disatukan menjadi **SATU MENU NAVIGASI TERPADU** bernama:

> **Manajemen Mobil & Showroom**  
> Route utama: `/admin/mobil`  
> Ikon: Kendaraan (`car` SVG profesional)

Seluruh fungsi existing pada kedua modul dipertahankan 100% tanpa ada fitur bisnis atau data inventori yang dihapus.

---

## 2. FILE YANG DIUBAH & FILE BARU

| No | File Path | Tipe Perubahan | Ringkasan Perubahan |
|:---|:---|:---:|:---|
| 1 | `resources/views/components/admin-sidebar.blade.php` | Modified | Menghapus dua item menu lama (`Inventori & Stok Mobil` dan `Katalog & Showroom`) dan menggantinya dengan satu item: `Manajemen Mobil & Showroom` (`admin.mobil.index`). Mengatur array slicing section agar partisi "Lainnya" tetap presisi. |
| 2 | `app/Support/AdminNavigation.php` | Modified | Memetakan ikon semantic resmi `admin.mobil.index => 'car'` dan mempertahankan mapping route legacy untuk backward compatibility. |
| 3 | `app/Http/Controllers/Admin/MobilController.php` | Modified | Menyatukan pembacaan relasi warna (`warnaPilihan.galeri`), menghitung 6 KPI kompak dari database authoritative, dan menyediakan parameter `$activeTab` (`inventori`, `katalog`, `foto`). |
| 4 | `app/Http/Controllers/Admin/AdminWorkspaceController.php` | Modified | Mengarahkan route legacy `/admin/katalog-showroom` secara aman (HTTP 302 Redirect) ke `/admin/mobil?tab=katalog` dengan mempertahankan seluruh parameter query string filter (`q`, `merk`, `status`, `kondisi`, dll). |
| 5 | `resources/views/admin/mobil/index.blade.php` | Modified (Unified) | Menghadirkan antarmuka tunggal dengan 6 KPI kompak, sistem 3 Tab (`Inventori & Stok`, `Katalog & Showroom`, `Foto & Warna`), filter toolbar seimbang, serta integrasi galeri dan master warna. |
| 6 | `resources/views/admin/dashboard.blade.php` | Modified | Menyelaraskan teks widget "Perlu Perhatian" dan shortcut agar konsisten dengan penamaan modul baru. |
| 7 | `tests/Feature/SuperAdminMenuAuditTest.php` | Modified | Menyesuaikan verifikasi jumlah link sidebar Super Admin dari 36 menjadi 35 link terpadu (semua link valid HTTP 200/302 dengan ikon semantic lengkap). |
| 8 | `tests/Feature/AdminWorkspaceNavigationTest.php` | Modified | Menyesuaikan uji navigasi workspace untuk memverifikasi redirect kompatibel dari `katalog-showroom.index` ke `admin.mobil.index?tab=katalog` dan memastikan seluruh tab ter-render. |
| 9 | `tests/Feature/UnifiedVehicleManagementTest.php` | **New Test Suite** | 9 targeted tests khusus untuk memvalidasi: satu menu sidebar, header & 6 KPI, 3 Tab, redirect route legacy, sinkronisasi harga/stok, pembeda warna, batas hak akses Owner read-only, serta blokir Guest & Member. |

---

## 3. STRUKTUR DAN FUNGSI TIGA TAB

Halaman utama `/admin/mobil` kini memiliki 3 tab yang dapat berpindah secara instan di sisi klien (tanpa full-page reload) sekaligus mendukung direct URL/bookmark melalui query parameter `?tab=...`:

### TAB 1: INVENTORI & STOK (`tab=inventori`)
Fokus pada kontrol fisik, logistik, nomor rangka, dan ketersediaan unit:
- **Tabel Profesional Kompak:**
  - Checkbox multi-select.
  - Kode Mobil (monospace link ke detail).
  - Mobil (Thumbnail foto, Merk, Model, Tipe, Tahun, Warna).
  - Identitas VIN: Nomor Rangka (`nomor_rangka`) & Nomor Mesin (`nomor_mesin`) ditampilkan rapi.
  - Showroom collection / showcase slug.
  - Harga Jual resmi (Rp).
  - Total Stok fisik (`stock_total` dari `VehicleInventory`).
  - Stok Tersedia yang Dapat Dibeli (`stockOrderable` setelah dikurangi reservasi aktif).
  - Reservasi Aktif (Pesanan member & booking).
  - Terjual (Sold units).
  - Status Badge (Tersedia, Dipesan, Terjual, Nonaktif).
  - Aksi: Tombol cepat `Detail`, `Stok` (riwayat & penyesuaian stok), `Edit`, serta menu opsi tambahan `Nonaktifkan Mobil` dan `Kelola Galeri & Warna`.
- **Toolbar Filter:** Pencarian teks bebas (`q`), dropdown Merek, Status ketersediaan, Kondisi (Baru/Bekas), Pagination, dan pengatur jumlah baris per halaman.

### TAB 2: KATALOG & SHOWROOM (`tab=katalog`)
Fokus pada presentasi pemasaran, etalase kendaraan, dan visibilitas publik:
- **Grid Kartu Showroom (3 Kolom pada Desktop):**
  - Tampilan visual mobil dengan rasio gambar proporsional (`object-fit: cover` tanpa distorsi).
  - Badge overlay: Status ketersediaan, Limited Edition / Unggulan, dan Kondisi (Baru/Bekas).
  - Metadata: Merk, Model, Tipe, Kode Mobil, Showcase Slug.
  - Harga Jual resmi terhubung langsung dengan master inventori (bukan angka terpisah).
  - Status ketersediaan stok fisik real-time (`X unit tersedia` atau `Stok Kosong`).
  - Aksi Showroom:
    - `Lihat Detail`: Membuka halaman detail internal admin.
    - `Edit`: Membuka form edit showroom dan spesifikasi.
    - `Preview`: Membuka link halaman publik showroom/koleksi pada tab baru (`/koleksi/{slug}` atau `/mobil/{id}`).

### TAB 3: FOTO & WARNA (`tab=foto`)
Fokus pada aset media multi-sudut dan pembeda warna katalog vs unit fisik:
- **Multi-Sudut Visual (4 Sudut):**
  - Depan (*Front 3/4*)
  - Samping (*Side Profile*)
  - Belakang (*Rear 3/4*)
  - Kabin (*Interior*)
- **Informatif Fallback:** Jika sudut foto belum diunggah atau tidak ada pada disk, sistem menampilkan placeholder netral yang bersih dan berlabel jelas (*"Sudut belum diunggah"*), bukan menampilkan foto mobil lain yang keliru.
- **Pembeda Warna Jelas:**
  - **Warna Unit Fisik:** Menampilkan warna riil unit kendaraan yang ada di inventori dealer saat ini.
  - **Warna Katalog Showroom:** Menampilkan daftar konfigurasi warna showroom (`warnaPilihan` / `MobilWarna`) lengkap dengan swatch warna hex dan tag default.
- **Manajemen Aset Foto Terunggah:**
  - Menampilkan thumbnail semua foto upload (`foto_mobil`).
  - Tombol aksi: "Jadikan Foto Utama" (`admin.mobil.foto.primary`) dan "Hapus Foto" (`admin.mobil.foto.destroy`).
  - Quick upload form: Memilih dan mengunggah foto baru langsung ke backend (`admin.mobil.foto.store`).

---

## 4. AUDIT KPI COMPACT (ANTI-MANIPULASI DATA)

Sesuai instruksi Tahap 4, KPI dihitung langsung dari sumber database authoritative tanpa estimasi fiktif:

| Indikator KPI | Sumber Data Nyata | Definisi & Makna Bisnis |
|:---|:---|:---|
| **Total Kendaraan** | `Mobil::count()` & `VehicleInventory::sum('stock_total')` | Jumlah model kendaraan terdaftar dan total unit fisik inventori |
| **Kendaraan Tersedia** | `Mobil::where('status', 'tersedia')->count()` | Unit dengan status siap dijual di showroom |
| **Kendaraan Dipesan** | Active `PesananMobilMember` + Active `BookingMobil` | Unit yang sedang diikat dalam transaksi pesanan atau reservasi booking |
| **Kendaraan Terjual** | `Mobil::where('status', 'terjual')->count()` | Unit yang sudah selesai diserahterimakan kepada pelanggan |
| **Pre-Order Aktif** | `MemberVehiclePreorder::whereIn('status', ['menunggu_stok', ...])->count()` | Permintaan pre-order member yang sedang menunggu alokasi unit |
| **Stok Menipis** | Unit dengan stok inventori tersedia $\le 3$ dan $> 0$ | Peringatan dini agar tim procurement segera melakukan restock |

---

## 5. KOMPATIBILITAS ROUTE LAMA & DEPENDENCY

- **Route Lama `/admin/katalog-showroom`:**  
  Tetap terdaftar di routing (`admin.katalog-showroom.index`). Ketika dikunjungi oleh pengguna, sistem memberikan respon **HTTP 302 Found** yang mengarahkan ke `/admin/mobil?tab=katalog` dengan mempertahankan semua query string filter.
- **Tautan Modul Lain:**  
  Tautan dari Pesanan Mobil, SPK, Pre-Order, Penjualan Mobil, dan Notifikasi yang mengarah ke `admin.mobil.show`, `admin.mobil.edit`, `admin.mobil.stock`, atau `admin.mobil.index` tetap berfungsi tanpa kendala.

---

## 6. SINKRONISASI DATA & KEAMANAN OTORISASI

1. **Satu Sumber Kebenaran (Single Source of Truth):**
   - Tidak ada master tabel stok atau harga kedua.
   - Perubahan harga atau stok pada kendaraan otomatis tersinkronisasi di Tab 1 (Inventori), Tab 2 (Katalog), maupun halaman publik pelanggan.
2. **Owner Tetap Read-Only:**
   - Role `owner` dapat membuka dan melihat halaman terpadu `/admin/mobil`.
   - Upaya melakukan mutasi data (nonaktifkan mobil, simpan mobil baru, unggah foto, penyesuaian stok) menghasilkan respon **HTTP 403 Forbidden**.
3. **Guest & Member Terisolasi:**
   - Guest diarahkan ke halaman login.
   - Member (`pelanggan`) ditolak dengan respon **HTTP 403 Forbidden**.

---

## 7. HASIL PENGUJIAN REGRESI

### A. Focused Test Suite: `UnifiedVehicleManagementTest`
```text
PASS  Tests\Feature\UnifiedVehicleManagementTest
✓ sidebar has single merged vehicle management menu (0.78s)
✓ unified page renders header kpis and three tabs (0.20s)
✓ tab 1 inventory renders compact table with vin (0.15s)
✓ tab 2 catalog renders card grid and public preview (0.16s)
✓ tab 3 photos and colors renders multi angle and color distinction (0.18s)
✓ legacy katalog showroom route redirects safely with parameters (0.06s)
✓ inventory and catalog share consistent authoritative data (0.16s)
✓ owner is read only on unified vehicle management (0.21s)
✓ member and guest cannot access admin vehicle management (0.07s)

Tests: 9 passed (51 assertions)
Duration: 2.14s
```

### B. Navigation & Role Audit Tests
- `SuperAdminMenuAuditTest`: **5 passed (407 assertions)**
- `AdminWorkspaceNavigationTest`: **5 passed (38 assertions)**
- `AdminDashboardRegressionTest`: **3 passed (116 assertions)**

### C. Frontend Build & Code Quality
- `npm run build` (Vite 7): **PASS** (58 modules transformed, CSS: 54.89 kB, JS: 51.52 kB)
- `git diff --check`: **PASS** (0 whitespace/formatting errors)

### D. Full Regression Test Suite
```text
Tests:    712 passed (4132 assertions)
Duration: 127.33s
Failures: 0
```

---

## 8. KESIMPULAN & BLOCKER STATUS

- **Blocker:** Tidak ada blocker yang tersisa.
- **Kondisi Database Operasional:** Skema dan data operasional tidak dimutasi secara ilegal; seluruh pengujian dijalankan di database pengujian terisolasi (`yza_automotive_laravel_test`).
- **Target Akhir Tercapai:** Sidebar Super Admin kini hanya memiliki satu menu **Manajemen Mobil & Showroom**, membuka satu halaman terpadu dengan 3 Tab, desain responsif, dan konsistensi data 100%.

