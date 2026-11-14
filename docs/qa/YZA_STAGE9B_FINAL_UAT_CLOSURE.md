# YZA Automotive — Stage 9B Final UAT Closure & System Stabilization

Tanggal: 15 Oktober 2026  
Lingkungan: Laravel lokal `http://127.0.0.1:8000`  
Status: **READY FOR STAGING UAT; NOT READY FOR PRODUCTION**

## 1. Integritas database setelah restore

- Effective Laravel database: `yza_automotive_laravel`, dari `.env`: MySQL `127.0.0.1:3306`.
- Database testing: `yza_automotive_laravel_test`.
- Native PHP database `yza_automotive`: tetap read-only dan tidak disentuh.
- Migration Laravel: seluruh migration sampai `2026_10_15_050000_create_financial_adjustments_tables` berstatus **Ran**.
- Backup yang menjadi bukti pemulihan: `storage/app/backups/yza_automotive_laravel_before_financial_adjustments_20261015.sql`.
- Tidak dilakukan restore ulang pada Stage 9B.
- Kondisi saat ini:
  - roles: 12
  - users: 18
  - transaksi: 12
  - Work Order/servis: 6
  - service assignments: 6
  - sales leads: 0
  - sales awards: 0
  - financial adjustments/events: 0/0
- `CHECK TABLE` untuk roles, users, servis, assignments, transaksi, pembayaran: **OK**.
- Orphan assignment → Work Order: 0.
- Orphan assignment → petugas: 0.
- Orphan payment → transaksi: 0.
- Duplicate transaction code: 0.
- Tidak ada bukti kehilangan atau overwrite data operasional pada kondisi sekarang. Backup sebelum financial adjustment tidak memuat data Stage 7 baru, sehingga perbandingan langsung hanya valid untuk tabel baseline; tabel Stage 7 saat ini sesuai migration dan kosong secara konsisten.

## 2. Sembilan akun staff

| Role | Username | Dashboard | Browser smoke |
|---|---|---|---|
| Super Admin | `superadmin506` | `/admin/dashboard` | PASS |
| Owner | `owner506` | `/owner/dashboard` | PASS |
| Sales | `sales506` | `/sales/dashboard` | PASS |
| Service Advisor | `advisor506` | `/staff/dashboard` | PASS |
| Mechanic | `mekanik506` | `/staff/dashboard` | PASS |
| Warehouse | `gudang506` | `/staff/dashboard` | PASS |
| Finance | `finance506` | `/staff/dashboard` | PASS |
| Operational | `operasional506` | `/staff/dashboard` | PASS |
| Quality Control | `qc506` | `/staff/dashboard` | PASS |

Browser smoke membuktikan login, redirect, dashboard, sidebar role untuk role utama, logout, dan direct URL terlarang. Pengujian setiap menu CRUD, filter, pagination, dan validasi form belum diklaim sebagai PASS penuh; bagian itu tetap manual UAT/staging.

## 3. Perbaikan horizontal overflow

### Root cause

Pengukuran DOM pada viewport 390px menemukan:

- Registrasi: `.register-steps` tetap satu baris dengan label `white-space: nowrap`; step 05 melebar hingga 481px.
- Landing: override desktop `.show-hero-inner` masih membawa grid minimum `500px / 760px` pada breakpoint mobile; `.hero-copy-premium` mencapai lebar 441px pada viewport 375px.

### Perbaikan

- Mobile registration stepper sekarang wrap menjadi dua kolom dan separator horizontal disembunyikan pada mobile.
- Mobile landing hero sekarang memakai satu kolom dengan `minmax(0,1fr)`, lebar container responsif, dan `hero-copy-premium` dibatasi oleh viewport.
- Tidak menggunakan `overflow-x:hidden` global.
- Layout desktop dan sticky/sidebar existing tidak diubah.

### Retest scroll width

| Viewport | Register | Landing | Status |
|---|---:|---:|---|
| 375×812 | document scrollWidth 375 | document scrollWidth 375 | PASS |
| 390×844 | document scrollWidth 390 | document scrollWidth 390 | PASS |
| 768×1024 | document scrollWidth 768 | document scrollWidth 768 | PASS |
| 1366×768 | document scrollWidth 1366 | document scrollWidth 1366 | PASS |
| 1920×1080 | document scrollWidth 1920 | document scrollWidth 1920 | PASS |

Screenshot browser sebelum dan sesudah diperoleh selama UAT. Bukti visual ditampilkan pada sesi browser; tidak dibuat screenshot fiktif atau dipalsukan. Hasil pengukuran sesudah perbaikan dicatat di laporan ini.

## 4. Zoom browser 67%

Zoom browser native 67% tidak tersedia melalui kontrol browser automation yang digunakan. Tidak diterapkan CSS `zoom` atau transform global.

Status: **MANUAL UAT REQUIRED** untuk Chrome dan Edge pada:

- Super Admin, Owner, Sales, Mechanic, Service Advisor, Member Center, Booking Servis, Work Order, Finance.
- Verifikasi typography, sidebar, hero, modal, form, tabel, dan grafik.

## 5. Browser UAT matrix

| Role | Login/redirect | Dashboard/sidebar | Direct unauthorized URL | Full menu CRUD/forms |
|---|---|---|---|---|
| Super Admin | PASS | PASS | Automated PASS | Manual UAT required |
| Owner | PASS | PASS | Automated PASS | Manual UAT required |
| Sales | PASS | PASS | Browser 403 PASS | Manual UAT required |
| Service Advisor | PASS | PASS | Automated PASS | Manual UAT required |
| Mechanic | PASS | PASS | Automated PASS | Manual UAT required |
| Warehouse | PASS | PASS | Automated PASS | Manual UAT required |
| Finance | PASS | PASS | Automated PASS | Manual UAT required |
| Operational | PASS | PASS | Automated PASS | Manual UAT required |
| QC | PASS | PASS | Automated PASS | Manual UAT required |

## 6. Member E2E

Automated isolated coverage PASS untuk:

- Pembelian kendaraan, reserve, payment state, approval, PDI, handover, ownership/My Garage.
- Booking servis, preferensi mekanik, availability, assignment, approval, sparepart fitment, QC, finalisasi, pembayaran, dan riwayat.
- Ownership isolation dan mechanic assignment authorization.

Browser E2E penuh belum dijalankan karena membutuhkan akun pelanggan testing aktif dan submit workflow yang mengubah data bisnis. Provider payment/refund tidak dipalsukan.

## 7. Tiga skipped test

Semua skipped yang dapat dihindari sudah diselesaikan:

- Work Order pemeriksaan dibuat deterministik di dalam test transaction.
- Sparepart aktif kedua dibuat deterministik di dalam test transaction.
- Pelanggan legacy tanpa `id_user` dibuat deterministik di dalam test transaction.

Tidak ada fixture Stage 9B yang ditulis ke database operasional. Seeder sementara yang sempat dipakai untuk diagnosis di database testing sudah dihapus dan fixture persisten testing dibersihkan.

Focused fixture suite: **30 passed, 127 assertions**.

## 8. Automated final validation

- Full regression: **407 passed, 0 failed, 0 skipped, 1.488 assertions, 61.09s**.
- PHP lint: **386 file PASS**.
- Route list owner: PASS.
- Blade cache: PASS.
- Database integrity: PASS.
- Registration navigation, RBAC, service assignment, fitment, member booking, financial adjustment, awards, dan Midtrans sandbox tests: PASS.
- PDF/XLSX: automated owner export authorization/type coverage PASS; file content/layout visual review tetap manual UAT.
- Midtrans sandbox/mock: PASS.
- Signature, duplicate settlement, amount mismatch, status sync, dan finish return: PASS.
- Callback HTTPS provider nyata, refund provider, partial refund provider, dan reconciliation eksternal: **BLOCKED/MANUAL UAT REQUIRED**.
- Tidak ada pembayaran atau refund nyata.

## 9. File yang diubah

- `public/assets/css/pages/register.css`
- `public/assets/css/showroom.css`
- `tests/Feature/WorkOrderWizardPhaseTest.php`
- `tests/Feature/ServiceExecutionPhase4ATest.php`
- `tests/Feature/YzaPointsStage10BTest.php`
- `docs/qa/YZA_STAGE9B_FINAL_UAT_CLOSURE.md`

Tidak ada migration baru dan tidak ada perubahan schema/database operasional pada Stage 9B.

## 10. Blocker dan keputusan rilis

Tidak ada bug critical/high yang ditemukan pada scope teknis lokal ini. Blocker yang tersisa:

1. Native browser zoom 67% Chrome/Edge belum dapat diverifikasi otomatis.
2. Full menu CRUD/forms/pagination per role masih memerlukan staging UAT aman.
3. Browser E2E member dengan akun testing aktif belum dijalankan.
4. Callback HTTPS Midtrans dan refund provider belum diverifikasi terhadap sandbox/provider.
5. PDF/XLSX membutuhkan pemeriksaan visual/manual.

Kesimpulan: **internal stabilization PASS dan READY FOR STAGING UAT; NOT READY FOR PRODUCTION**. Tidak dilakukan deployment production.

