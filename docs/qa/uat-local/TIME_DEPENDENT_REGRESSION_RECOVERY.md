# Time-Dependent Regression Recovery

Tanggal: 16 Oktober 2026 (Asia/Jakarta)

## Hasil

Regression yang sebelumnya berakhir pada **336 PASS, 75 FAIL, 0 SKIPPED, 1349 assertions** telah pulih menjadi:

- **411 PASS**
- **0 FAIL**
- **0 SKIPPED**
- **1499 assertions**

Database test tetap dijaga oleh Tests\TestCase: PHPUnit menolak koneksi selain yza_automotive_laravel_test.

## Akar masalah

Failure sebelumnya berasal dari fixture yang menghitung jadwal relatif dengan now()->addDay(). Saat test dijalankan pada Jumat 16 Oktober 2026, tanggal booking menjadi Sabtu dan aturan kalender bengkel yang benar menolaknya sebagai hari tutup. Failure lanjutan berupa assertion status/notification/Work Order adalah efek berantai dari booking fixture yang gagal dibuat.

Audit juga menemukan tiga service membandingkan tanggal dengan time() PHP, sementara test memakai Carbon/Laravel clock. Hal ini membuat negative test tanggal lampau tidak konsisten setelah test clock dibuat deterministik.

## Perbaikan

1. tests/TestCase.php menetapkan Carbon test clock deterministik pada Senin 19 Oktober 2026 untuk setiap test dan membersihkannya pada tearDown(). Fixture addDay() menjadi Selasa, hari operasional.
2. BookingCuciService, BookingServisService, dan TestDriveService membandingkan input terhadap now()->timestamp, sehingga timezone dan test clock Laravel konsisten.
3. Tidak ada perubahan pada jam buka/tutup, bentrok jadwal, validasi masa depan, middleware, CSRF, atau authorization.
4. Perbaikan RBAC sebelumnya tetap dipertahankan: mekanik memakai service.assigned, QC memakai qc.manage hanya untuk route QC, dan operational_staff tetap menggunakan transisi Test Drive yang diizinkan.

## Evidence pengujian

- Representative booking/service suite: **136 PASS, 0 FAIL, 286 assertions**.
- Full regression terbaru: **411 PASS, 0 FAIL, 0 SKIPPED, 1499 assertions**.
- Positive calendar coverage: jadwal hari operasional diterima oleh booking servis dan car wash.
- Negative calendar coverage: weekend tetap ditolak pada ServiceSchedulingPhase2A1Test; tanggal lampau tetap ditolak pada booking servis, car wash, dan test drive.
- RBAC mechanic/QC/operational tetap tercakup oleh full regression; browser UAT sebelumnya juga membuktikan pemeriksaan mekanik, QC, dan persetujuan Test Drive setelah perbaikan permission.

## File diubah

- tests/TestCase.php
- app/Services/BookingCuciService.php
- app/Services/BookingServisService.php
- app/Services/TestDriveService.php
- docs/qa/uat-local/FINAL_RELEASE_READINESS_MATRIX.md

Perubahan operasional browser dan fixture UAT sebelumnya tetap ada pada file terkait; tidak ada migration, reset, atau mutasi database produksi.

## Status

Regression recovery: **PASS**.

Warehouse finalisasi dan Finance browser mutation UAT tetap belum boleh dilanjutkan sampai tahap berikutnya sesuai instruksi sebelumnya. Blocker production yang tidak terkait recovery ini—HTTPS callback Midtrans, refund provider, dan native desktop inspection—tetap terbuka.
