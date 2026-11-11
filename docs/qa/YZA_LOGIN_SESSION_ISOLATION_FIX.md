# YZA Login Member & Session Isolation Fix

Tanggal: 2026-10-10
Branch: `feature/vehicle-purchase-flow`

## Root cause terbukti

Controller login member sudah mengarahkan role `pelanggan` ke route `pelanggan.member`. Bug terjadi karena server utama port 8000 dan server staging port 8001 menggunakan nama cookie session default yang sama pada host `127.0.0.1`:

`yza-automotive-laravel-session`

Keduanya juga memakai project storage session yang sama. Cookie dari staging dapat terkirim ke port 8000, tetapi session tersebut tidak ditemukan pada runtime utama. Laravel lalu menganggap request sebagai guest dan mengembalikan pengguna ke landing page `/`.

## Perbaikan

- Staging launcher menetapkan `SESSION_COOKIE=yza-staging-session`.
- Staging launcher menetapkan database secara eksplisit ke `yza_automotive_laravel_staging`.
- Launcher memeriksa database efektif sebelum menjalankan server.
- URL staging ditetapkan ke `http://127.0.0.1:8001`.
- `.env` utama tidak diubah.
- Database utama dan Native tidak disentuh.
- Middleware authentication, role, membership, dan CSRF tidak dilemahkan.

File yang ditambahkan:

- `scripts/start-yza-staging.ps1`
- `tests/Feature/AuthMemberLoginRegressionTest.php`

## Hasil test

- Focused auth/member/audit: **28 passed, 123 assertions**.
- Full Feature: **299 passed, 1 skipped, 924 assertions**.
- Unit: **1 passed, 1 assertion**.
- PHP lint `AuthController`: lulus.
- Blade view cache: lulus.
- Route compilation/listing: lulus.
- PowerShell launcher syntax: lulus.
- Skipped: `YzaPointsStage10BTest::test_legacy_customer_without_user_is_not_a_member`; fixture sintetis tidak memiliki customer legacy tanpa `id_user`.

## Cara mencoba

1. Tutup tab atau hapus cookie lama untuk `127.0.0.1`, terutama cookie session default.
2. Buka website utama: `http://127.0.0.1:8000/login`.
3. Login menggunakan akun member yang valid pada database utama.
4. Hasil yang diharapkan: `http://127.0.0.1:8000/pelanggan/member`.
5. Untuk staging, jalankan `scripts/start-yza-staging.ps1`, lalu gunakan `http://127.0.0.1:8001/login`. Cookie staging sekarang terisolasi dari port 8000.

Password, hash, token, dan data pribadi tidak dicatat dalam laporan.
