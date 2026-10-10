# YZA Automotive — Laravel Foundation

## Scope

Tahap ini hanya menyiapkan fondasi Laravel terpisah. Authentication, migrasi bisnis, membership, YZA Points, Midtrans, dan payment gateway belum diimplementasikan.

## Project dan database

- Project: `C:\xampp\htdocs\yza_automotive_laravel`
- Database development: `yza_automotive_laravel`
- Database stable/reference: `yza_automotive` — tidak digunakan untuk operasi write.

## Foundation yang disiapkan

- Laravel 12 pada PHP 8.2.
- Blade layout dasar.
- Struktur view untuk `layouts`, `components`, `public`, `admin`, `petugas`, dan `pelanggan`.
- Struktur asset CSS, JavaScript, dan branding.
- Timezone `Asia/Jakarta` dan locale Indonesia.
- Session file driver dengan encryption, HTTP-only, dan SameSite development.
- CSRF Laravel tetap aktif.
- Logging Laravel menggunakan stack/single channel.
- Storage Laravel tersedia melalui `storage/app` dan `storage/app/public`.
- Placeholder environment Midtrans tanpa credential.
- Tidak ada migration bisnis yang dibuat atau dijalankan.

## Compatibility boundary

Project PHP Native existing dan database `yza_automotive` tetap terpisah serta tidak dimodifikasi.
