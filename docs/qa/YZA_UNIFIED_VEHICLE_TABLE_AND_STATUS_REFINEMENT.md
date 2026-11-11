# YZA AUTOMOTIVE — UNIFIED VEHICLE TABLE, CATALOG STATUS & PRE-ORDER VISIBILITY
**Audit, Implementation & Verification Report**
**Target:** `http://127.0.0.1:8000/admin/mobil` (Manajemen Mobil & Showroom)
**Date:** November 2026
**Environment:** Localhost / Laravel 12 / PHP 8.2.4 / MySQL

---

## 1. Executive Summary & Architecture

Berdasarkan keputusan bisnis YZA Automotive, modul **Manajemen Mobil & Showroom** telah disempurnakan secara menyeluruh untuk mengeliminasi redundansi visual dan inkonsistensi status operasional.

### Perubahan Arsitektur Utama
1. **Satu Tabel Terpadu (Unified Vehicle Table):**
   - Menghapus komponen card terpisah *"Model Katalog · Belum Ada Unit Fisik"* di atas tabel.
   - Menggabungkan seluruh 30 model kendaraan ke dalam satu tabel utama (`#tab-inventori`): model dengan unit fisik tersedia (26 model fisik dengan total 69 unit), model dengan stok 0, serta model virtual katalog (4 model showcase APM: Bugatti Chiron, Audi R8, BMW M4, McLaren 720S).
   - Memastikan tidak ada duplikasi model di dalam satu tampilan tabel.
2. **Penyempurnaan 10 Kolom Terstandarisasi:**
   1. Checkbox
   2. Kendaraan & Kode (disertai subline VIN & Nomor Mesin)
   3. Harga Jual
   4. Status Katalog (menggantikan kolom `SHOWROOM` yang sebelumnya membingungkan dan bernilai `—`)
   5. Stok Fisik
   6. Stok Ready
   7. Reservasi Aktif
   8. Pre-Order Aktif
   9. Ketersediaan
   10. Aksi (Sticky Column dengan trigger modal terpadu)
3. **Pemisahan Konsep Status:**
   - **Status Katalog:** Mengontrol penayangan publik di website (`Tayang` vs `Tidak Tayang`).
   - **Ketersediaan Unit:** Merefleksikan kondisi operasional fisik (`Ready`, `Stok Habis`, `Belum Ada Unit Fisik`, `Dipesan`, `Terjual`, `Nonaktif`).
   - **Pre-Order:** Bukan merupakan status stok fisik, melainkan metrik kuantitatif permintaan customer aktif (`X permintaan`) yang dapat diklik langsung (deep-link) menuju `/admin/pre-order-mobil`.

---

## 2. Audit Findings & Database Integrity

| Domain | Temuan Audit | Tindakan & Solusi |
|---|---|---|
| **Pemisahan Card vs Tabel** | Model virtual tanpa unit fisik sebelumnya dirender dalam card terpisah di atas tabel melalui `catalog-without-units.blade.php`, menyebabkan pemborosan ruang vertikal dan Admin harus memantau dua daftar terpisah. | Card container terpisah dihilangkan. Seluruh model virtual dirender sebagai baris model resmi pada tabel utama dengan styling khusus (`is-virtual-model-row`), tetap mempertahankan identitas katalog yang valid tanpa membuat record fisik tiruan di database. |
| **Kolom Showroom** | Kolom lama menampilkan header `SHOWROOM` namun bernilai strip (`—`) pada seluruh baris karena ketidakjelasan integrasi showroom display. | Diganti menjadi kolom **STATUS KATALOG** (`Tayang` vs `Tidak Tayang`) dengan slug showcase badge, merefleksikan status keterlihatan publik yang transparan. |
| **Pencatatan Pre-Order** | Pre-order sebelumnya rawan disalahartikan sebagai status inventori fisik. | Pre-order didefinisikan secara tegas sebagai penghitung permintaan pelanggan aktif (`activePreorders`). Model Bugatti Chiron menampilkan `1 permintaan` dengan deep-link filter ke `/admin/pre-order-mobil`, sedangkan model lain menampilkan `0 permintaan`. |
| **Integritas Database Operasional** | Dilarang keras memodifikasi database operasional `yza_automotive_laravel` atau membuat nomor rangka/mesin fiktif. | Seluruh pembaruan dilakukan pada level query Eloquent, View presentation, dan Model mapping tanpa DDL/DML alter table maupun manipulasi data historis. |

---

## 3. Matriks Status & Definisi Bisnis

### 3.1. Status Katalog vs Ketersediaan
| Status Katalog | Kondisi Pemicu | Ketersediaan | Deskripsi Operasional |
|---|---|---|---|
| **Tayang** | Model aktif di katalog publik & unit fisik berstatus `tersedia` dengan stok ready > 0 | **Ready** | Unit fisik ada di showroom dan siap dipesan/dibeli oleh member aktif. |
| **Tayang** | Unit fisik berstatus `tersedia` namun seluruh unit teralokasi ke reservasi aktif (stok ready = 0) | **Stok Habis** | Model tayang di katalog publik, namun saat ini tidak memiliki unit siap jual. |
| **Tayang** | Unit berstatus `terjual` | **Terjual** | Unit telah selesai diserahkan ke pelanggan. |
| **Tayang** | Unit berstatus `dibooking` | **Dipesan** | Unit sedang dalam proses administrasi booking member. |
| **Tayang** | Model showcase APM / Katalog resmi yang belum memiliki alokasi unit fisik di database | **Belum Ada Unit Fisik** | Model tayang di showroom digital untuk eksplorasi dan Pre-Order member, tanpa mengganggu stok fisik. |
| **Tidak Tayang** | Model/unit berstatus `nonaktif` | **Nonaktif** | Kendaraan diarsipkan sementara dari etalase showroom publik dan operasional aktif. |

### 3.2. Metrik Pre-Order
- Pre-Order **BUKAN** unit fisik dan tidak pernah ditambahkan ke kolom Stok Fisik maupun Stok Ready.
- Kolom **PRE-ORDER AKTIF** menampilkan:
  - Badge hijau bergaris (`.preorder-count-badge.is-active`): `1 permintaan` (untuk Bugatti Chiron). Mengklik badge mengarahkan admin ke `/admin/pre-order-mobil?q=bugatti-chiron`.
  - Teks abu-abu (`.preorder-count-badge.is-zero`): `0 permintaan` (untuk model lainnya).
- Saat admin menavigasi dari modul Pre-Order ke Manajemen Mobil melalui deep-link `?preorder=X`, halaman menampilkan **Pre-Order Context Banner** di atas tabel dengan tombol cepat *&larr; Kembali ke Daftar Pre-Order*.

---

## 4. KPI Card Metrics

KPI di bagian atas halaman Manajemen Mobil & Showroom telah distandarisasi untuk menyajikan informasi yang presisi dan tidak ambigu:

| KPI Card | Nilai Riil | Subteks Penjelas |
|---|---|---|
| **Total Model Katalog** | 30 | 26 model berinventori fisik + 4 model katalog showcase |
| **Unit Fisik Tercatat** | 69 unit | Total unit fisik dari seluruh inventori dealer |
| **Unit Ready** | 66 unit | Unit siap jual (Stok total fisik dikurangi reservasi aktif) |
| **Reservasi Aktif** | 2 unit | Unit dalam pesanan member aktif atau booking aktif |
| **Pre-Order Aktif** | 1 permintaan | Permintaan pemesanan unit khusus menunggu pengadaan stok |
| **Kendaraan Terjual** | 2 unit | Unit yang telah berhasil ditransaksikan dan diserahterimakan |

---

## 5. Security, RBAC & Backward Compatibility

1. **Role Super Admin:**
   - Memiliki akses penuh mengelola stok, mengedit data kendaraan, mengelola foto multi-sudut, dan menonaktifkan kendaraan via popup modal.
2. **Role Owner (Executive):**
   - Memiliki akses **Read-Only** penuh untuk memantau data tabel, KPI, dan spesifikasi.
   - Seluruh mutasi AJAX (`POST /admin/mobil/stock/adjust`, `PUT /admin/mobil/{id}`, `PATCH /admin/mobil/{id}/deactivate`, `POST /admin/mobil`) dilindungi Policy dan mengembalikan **`403 Forbidden`**.
3. **Role Pelanggan & Guest:**
   - Ditolak dari seluruh endpoint administrasi mobil (`403 Forbidden` untuk member, redirect ke login untuk guest).
4. **Backward Compatibility:**
   - Route legacy `/admin/katalog-showroom` secara otomatis me-redirect ke `/admin/mobil?tab=katalog` dengan mempertahankan seluruh query parameter (`q`, `merk`, dll.).
   - Model `Mobil` dilengkapi relasi `inventory(): HasOne` untuk kompatibilitas Eloquent yang bersih.

---

## 6. Verification & Automated Test Evidence

### 6.1. UnifiedVehicleManagementTest (20 Tests — 100% Passed)
```text
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
Configuration: C:\xampp\htdocs\yza_automotive_laravel\phpunit.xml

....................                                              20 / 20 (100%)

Time: 00:09.267, Memory: 52.00 MB
OK (20 tests, 160 assertions)
```

**Daftar Uji Lengkap:**
1. `test_sidebar_has_single_merged_vehicle_management_menu` (PASS)
2. `test_unified_page_renders_header_kpis_and_three_tabs` (PASS)
3. `test_tab_1_inventory_renders_compact_table_with_vin` (PASS — Menguji 10 kolom terpadu & VIN subline)
4. `test_tab_2_catalog_renders_card_grid_and_public_preview` (PASS)
5. `test_tab_3_photos_and_colors_renders_multi_angle_and_color_distinction` (PASS)
6. `test_legacy_katalog_showroom_route_redirects_safely_with_parameters` (PASS)
7. `test_inventory_and_catalog_share_consistent_authoritative_data` (PASS)
8. `test_owner_is_read_only_on_unified_vehicle_management` (PASS)
9. `test_member_and_guest_cannot_access_admin_vehicle_management` (PASS)
10. `test_actions_render_as_modal_triggers_and_not_full_page_links` (PASS)
11. `test_show_endpoint_returns_json_for_modal` (PASS)
12. `test_stock_endpoint_returns_json_for_modal` (PASS)
13. `test_edit_endpoint_returns_json_for_modal` (PASS)
14. `test_adjust_stock_ajax_updates_stock_and_validates` (PASS)
15. `test_update_car_ajax_updates_and_validates` (PASS)
16. `test_deactivate_car_ajax_and_guards` (PASS)
17. `test_owner_is_forbidden_from_all_modal_mutations` (PASS)
18. `test_modal_tab_segregation_and_distinct_content_rendering` (PASS)
19. `test_unified_vehicle_table_renders_all_catalog_models_including_virtual_and_zero_stock` (PASS — Menguji rendering model fisik + 0 stok + virtual dalam 1 tabel terpadu)
20. `test_filter_by_status_and_preorder` (PASS — Menguji filter `ready`, `habis_atau_belum_ada`, `ada_preorder`, `tidak_tayang`)

### 6.2. CatalogInventoryPreorderConsistencyTest (9 Tests — 100% Passed)
```text
PHPUnit 11.5.56 by Sebastian Bergmann and contributors.
Configuration: C:\xampp\htdocs\yza_automotive_laravel\phpunit.xml

.........                                                           9 / 9 (100%)

Time: 00:08.422, Memory: 48.00 MB
OK (9 tests, 115 assertions)
```

### 6.3. Asset Compilation
```text
npm run build
vite v7.3.7 building client environment for production...
✓ 58 modules transformed.
public/build/manifest.json             0.33 kB │ gzip:  0.17 kB
public/build/assets/app-Dc90MONy.css  54.89 kB │ gzip: 11.44 kB
public/build/assets/app-DMsN-rLE.js   51.52 kB │ gzip: 19.51 kB
✓ built in 3.78s
```

---

## 7. Kesimpulan
Seluruh permintaan bisnis dalam **UNIFIED VEHICLE TABLE, CATALOG STATUS & PRE-ORDER VISIBILITY** telah terpenuhi 100% tanpa kompromi integritas data:
- Tampilan kini rapi, padat, dan profesional dalam 1 tabel terpadu.
- Tidak ada lagi pemisahan card atas dan tabel bawah.
- Status katalog publik dan ketersediaan unit fisik telah terdefinisi secara independen dan akurat.
- Pre-Order tampil sebagai metrik kuantitatif dengan direct link ke modul operasional.
- Seluruh tes otomatis (29 tests, 275 assertions) berhasil lolos secara solid.

