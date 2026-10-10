# Laravel Release Checklist — RC1

## Runtime

- PHP dan Composer sesuai requirement project.
- MySQL tersedia dan target database terkonfigurasi.
- APP_KEY tersedia.
- APP_ENV sesuai environment.
- APP_DEBUG=false untuk deployment.

## Environment

- DB variables diisi melalui secret store.
- MIDTRANS_ENV=sandbox.
- MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY diisi hanya pada environment.
- MIDTRANS_IS_PRODUCTION=false.
- .env tidak dimasukkan ke repository.

## Application

- php artisan migrate --force
- php artisan config:clear
- php artisan cache:clear
- php artisan route:list
- storage dan bootstrap/cache writable.
- Route cache diuji sebelum diaktifkan.

## Midtrans Sandbox

- Gunakan Snap Redirect.
- Konfigurasikan public HTTPS webhook URL pada dashboard Midtrans Sandbox.
- localhost tidak menerima webhook publik.
- Jangan aktifkan production flag.
- Gunakan Get Status untuk reconciliation bila webhook lokal tidak tersedia.

## Data and Backup

- Backup database sebelum migration.
- Verifikasi backup dapat dibaca.
- Jalankan integrity query orphan, duplicate, payment direction, dan negative stock.
- Native database tidak boleh menjadi target migration.

## Optional Demo

- php artisan yza:promo-demo
- php artisan yza:points-backfill --dry-run
- Jalankan backfill aktual hanya setelah dry-run ditinjau.
- Jangan menjalankan command demo pada Native.
