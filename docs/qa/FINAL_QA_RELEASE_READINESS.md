# YZA Automotive — Final QA & Release Readiness

Tanggal audit: 15 Oktober 2026

## Status

**NOT READY — BLOCKED** untuk production. Sistem layak masuk staging/UAT terkontrol, tetapi belum boleh production karena sumber refund/reversal authoritative belum tersedia dan browser UAT belum dijalankan sebagai bukti interaksi nyata.

## Bukti otomatis

- Full regression: **401 PASS, 0 FAIL, 3 SKIPPED, 1.474 assertions**.
- Focused Tahap 5 + Final QA: **5 PASS, 30 assertions**.
- Semua 9 akun lokal berhasil login ke dashboard role yang sesuai.
- Akses Awards oleh member dan mekanik: HTTP 403.
- Finalisasi Awards diblokir service jika sumber refund authoritative tidak tersedia.
- PHP lint, route list, dan Blade view cache: PASS.

Skipped test tetap berasal dari fixture kondisional: tidak ada pelanggan tanpa aktivitas, tidak ada sparepart aktif yang memenuhi skenario, dan tidak ada Work Order pada status yang diminta.

## Data dan keamanan

- Database efektif Laravel: `yza_automotive_laravel`.
- Database native `yza_automotive`: read-only; tidak memiliki tabel `sales_awards`.
- Audit orphan penjualan: 0.
- Audit orphan pembayaran: 0.
- Audit kendaraan pada penjualan aktif ganda: 0.
- Backup sebelum migration: `storage/app/backups/yza_automotive_laravel_before_sales_awards_20261015.sql`.
- Migration Awards backward-compatible berhasil diterapkan pada lokal dan testing.

## Blocker kritis

Schema belum memiliki ledger refund/reversal authoritative yang membedakan refund penuh, sebagian, reversal offline, dan refund Midtrans secara audit-able. Karena itu `SalesAwardsService::finalize()` menolak seluruh finalisasi resmi, termasuk tahun lampau. Status tahun berjalan tetap `PROVISIONAL`.

PsySH query profiling tidak dapat dijalankan pada environment ini karena direktori konfigurasi PsySH tidak writable. N+1 target Sales sudah diuji secara focused test, tetapi benchmark runtime dashboard/export belum menjadi bukti release.

## Manual UAT wajib

Lakukan pada Chrome dan Edge desktop/tablet/mobile:

1. Login seluruh sembilan akun lokal dan pastikan sidebar serta redirect sesuai role.
2. Jalankan pembelian mobil dari pemesanan sampai PDI, pembayaran terverifikasi, serah-terima, ownership, dan My Garage.
3. Jalankan servis dari booking, preferensi mekanik, assignment advisor/mekanik, approval, sparepart, QC, finalisasi, pembayaran, dan riwayat member.
4. Uji direct URL lintas role, export Owner, CSRF, tombol back, empty state, pagination, serta responsive layout.
5. Verifikasi webhook Midtrans sandbox dua kali memakai payload yang sama dan pastikan tidak ada pembayaran ganda.

Browser UAT belum diklaim PASS berdasarkan feature test Laravel saja.
