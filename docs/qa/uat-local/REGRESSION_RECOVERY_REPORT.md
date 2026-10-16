# Regression Recovery Report

Tanggal: 15 Oktober 2026

## Hasil akhir

- **407 PASS**
- **0 FAIL**
- **0 SKIPPED**
- **1.488 assertions**
- Durasi: 53,03 detik
- Log lengkap: [regression-recovery-full.log](regression-recovery-full.log)

Focused regression untuk assignment dan autentikasi juga lulus: **4 tests / 29 assertions**.

## Akar masalah 68 failure sebelumnya

Run sebelumnya dijalankan dengan `artisan test --env=testing`, tetapi konfigurasi proses pada Windows masih menunjuk ke database `yza_automotive_laravel` (operasional). Hal itu membuat hasil **339 PASS / 68 FAIL / 0 SKIPPED / 1.266 assertions** tidak valid sebagai hasil regression testing: POST tampak sebagai 419 dan fixture tidak cocok karena data yang dibaca bukan database testing.

Run recovery menggunakan environment proses yang eksplisit dan database terisolasi `yza_automotive_laravel_test`. Guard pada `tests/TestCase.php` sekarang menghentikan test bila koneksi bukan database tersebut, sehingga salah-target database tidak dapat terulang diam-diam.

Failure `SparepartFitmentDependencyTest` tidak terbukti sebagai bug aturan fitment atau placeholder Blade. Seluruh 8 test fitment lulus pada database testing yang benar, termasuk render approval, pencarian, kompatibilitas, dan traceability fitment.

## Perubahan

- `phpunit.xml`: memaksa `APP_ENV=testing`, database testing, URL lokal, dan session array untuk proses PHPUnit.
- `tests/TestCase.php`: menonaktifkan hanya middleware CSRF Laravel pada harness feature test agar test bisnis tidak perlu membuat token browser pada setiap request, serta menolak database non-testing.
- `docs/qa/uat-local/regression-recovery-full.log`: log hasil eksekusi penuh.

CSRF aplikasi produksi tidak diubah. `bootstrap/app.php` tetap mengaktifkan validasi CSRF dan hanya mengecualikan callback `payment/midtrans/notification`. Bypass pada `tests/TestCase.php` hanya berlaku di PHPUnit; browser dan HTTP production tetap melalui middleware aplikasi. Negative-CSRF boundary tetap perlu dibuktikan pada browser/staging atau test khusus middleware; hasil 407 test ini tidak diklaim sebagai negative-CSRF test.

## Database dan keselamatan pengujian

- Database yang dipakai PHPUnit: `yza_automotive_laravel_test`.
- Database operasional `yza_automotive_laravel` dan UAT `yza_automotive_laravel_uat` tidak dipakai untuk mutasi PHPUnit.
- Tidak ada reset destruktif, penghapusan transaksi, atau perubahan database PHP Native.
- Guard database diuji secara efektif ketika konfigurasi salah: run dihentikan dengan pesan `Refusing to run tests against non-test database`.

## Status

Regression suite aplikasi **lulus penuh** dan 68 failure sebelumnya sudah dijelaskan sebagai masalah environment test, bukan kegagalan fungsional yang masih terbukti. Status release keseluruhan tetap mengikuti batasan UAT sebelumnya: staging/browser E2E penuh dan callback HTTPS/refund Midtrans masih memerlukan pembuktian eksternal sebelum production.
