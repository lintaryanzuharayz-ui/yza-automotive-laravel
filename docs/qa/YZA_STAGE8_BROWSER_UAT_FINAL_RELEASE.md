# YZA Automotive — Tahap 8 Browser UAT & Final Release Verification

Tanggal verifikasi: 15 Oktober 2026  
Environment aplikasi: `local`, `http://127.0.0.1:8000`  
Database aplikasi: `yza_automotive_laravel`  
Database testing: `yza_automotive_laravel_test`

## Status akhir

Automated regression: **PASS — 404 passed, 3 skipped, 1.482 assertions**.  
Focused owner/sales/RBAC: **PASS — 19 passed, 100 assertions**.  
Focused financial/Midtrans: **PASS — 20 passed, 68 assertions**.  
Browser UAT: **PARTIAL PASS**; smoke login, dashboard, logout, guest Membership CTA, registration context, and cross-role 403 telah diverifikasi. UAT lengkap untuk semua akun, semua breakpoint, zoom 67%, transaksi Midtrans Sandbox nyata, dan refund provider nyata tetap memerlukan sesi manual/staging terkontrol.

## Browser evidence yang benar-benar dijalankan

| Skenario | Hasil | Bukti |
|---|---|---|
| `owner506` login | PASS | Redirect dan halaman Executive Dashboard terlihat di browser |
| Owner dashboard dan sidebar | PASS | KPI live, Sales Awards, laporan, inventori, dan pembayaran terlihat |
| `operasional506` login | PASS | `/staff/dashboard`, dashboard role-aware dan menu server-side terlihat |
| `qc506` login | PASS | Dashboard Quality Control terlihat |
| QC membuka `/owner/dashboard` | PASS | Halaman `403 Forbidden` terlihat |
| Logout | PASS | Kembali ke `/login` |
| Guest membuka `/#janji-temu` | PASS | Landing/showroom dan CTA `BUAT MEMBER` terlihat |
| Membership → registrasi | PASS | `/register?from=membership`, wizard 5 tahap terlihat, tombol `KEMBALI KE MEMBERSHIP` menuju `/#janji-temu` |
| Form login/registrasi | PASS | Field dan tombol utama terlihat tanpa redesign |

Sembilan akun staff juga lulus login pada automated test: `superadmin506`, `owner506`, `sales506`, `advisor506`, `mekanik506`, `gudang506`, `finance506`, `operasional506`, dan `qc506`. Browser smoke langsung tidak diklaim sebagai pengganti UAT manual untuk sembilan akun tersebut.

## Regression dan fixture

Focused suite service assignment, personnel/scheduling, sparepart fitment, booking member, RBAC, financial adjustment, awards, dan Midtrans: PASS.

Full suite:

- 404 passed
- 3 skipped
- 0 failed
- 1.482 assertions

Skipped yang teramati:

1. `WorkOrderWizardPhaseTest`: fixture tidak memiliki Work Order pemeriksaan yang sesuai.
2. `ServiceExecutionPhase4ATest`: fixture hanya memiliki satu sparepart aktif.
3. `YzaPointsStage10BTest`: tidak ada pelanggan legacy tanpa `id_user`.

Skip tersebut berasal dari prasyarat data sintetis yang tidak tersedia, bukan assertion yang dinonaktifkan.

## Database dan migration audit

- Backup lokal digunakan untuk recovery: `storage/app/backups/yza_automotive_laravel_before_financial_adjustments_20261015.sql`.
- Migration `2026_10_15_050000_create_financial_adjustments_tables` diterapkan kembali pada database Laravel lokal setelah recovery karena backup dibuat sebelum migration tersebut.
- `financial_adjustments` dan `financial_adjustment_events` ada pada database Laravel lokal.
- Database native `yza_automotive` tidak disentuh dan tidak memiliki tabel financial adjustment baru.
- Database testing dibangun ulang pada database terpisah, kemudian diisi `Stage2TestingSeeder`, `TestingVehicleStockFixtureSeeder`, dan sembilan akun lokal.
- Tidak ada perubahan struktur database native PHP.

Catatan recovery: percobaan awal `migrate:fresh --env=testing` terpengaruh konfigurasi Laravel yang ter-cache dan sempat menjalankan migration pada database lokal. Proses berhenti pada migration inventory sebelum migrasi lanjutan; database lokal kemudian dipulihkan dari backup yang sesuai dan diverifikasi ulang. Insiden ini dicatat agar tidak disalahartikan sebagai hasil uji aplikasi.

## Keamanan, finansial, dan Midtrans

- Akses lintas role diuji otomatis dan browser; QC ke owner dashboard menghasilkan 403.
- Owner hanya read-only untuk area finansial/inventori sesuai test.
- Refund/reversal tidak mengubah payment original; adjustment memiliki audit event dan separation of duties.
- Tidak ada refund Midtrans nyata yang dikirim.
- Midtrans tetap Sandbox-only pada automated test; signature, amount mismatch, duplicate notification, pending/expire, status sync, dan idempotency lulus.
- Awards finalization tetap konservatif dan diblokir bila sumber refund authoritative lintas channel belum tersedia.
- Rekonsiliasi bank, settlement provider nyata, refund provider nyata, dan webhook HTTPS publik belum dapat dinyatakan terverifikasi dari localhost.

## UAT manual yang masih diperlukan

- Login browser langsung untuk seluruh sembilan akun dengan pemeriksaan sidebar per-role dan direct URL 403.
- Member aktif melakukan booking servis sampai preferensi mekanik, konfirmasi Service Advisor, assignment mekanik, eksekusi, QC, pembayaran, finalisasi, dan riwayat kendaraan.
- Vehicle sale end-to-end melalui UI dengan fixture terisolasi.
- Midtrans Sandbox Simulator dan webhook HTTPS staging nyata; jangan memakai credential production.
- Financial Adjustment UAT melalui UI memakai payment dummy terisolasi; jangan melakukan refund ke pelanggan nyata.
- Browser Edge/Chrome pada desktop, tablet, mobile, zoom 100% dan 67%.

## Kesimpulan release

Status: **READY FOR STAGING UAT, NOT READY FOR PRODUCTION RELEASE**.

Alasannya bukan failure automated regression—suite sudah hijau—melainkan bukti browser UAT dan provider/financial reconciliation nyata belum lengkap. Tidak ada perubahan UI besar atau perubahan aturan bisnis pada verifikasi Tahap 8.
