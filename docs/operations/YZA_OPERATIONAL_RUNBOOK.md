# YZA Automotive — Operational Runbook

## Role utama

- Super Admin: administrasi, permission, audit, dan pengawasan.
- Owner: dashboard, laporan, export, dan Awards read-only.
- Sales: prospek, follow-up, test drive, pesanan, penjualan, target, dan leaderboard pribadi.
- Service Advisor: booking, jadwal, assignment, dan Work Order.
- Mekanik: hanya Work Order yang ditugaskan.
- Gudang: stok sparepart dan adjustment sesuai permission.
- Finance: verifikasi pembayaran dan rekonsiliasi.
- Operasional: test drive dan car wash.
- QC/PDI: pemeriksaan mutu dan kelayakan.

## Alur operasional

Penjualan: pemesanan → assignment Sales → pembayaran → verifikasi → PDI → jadwal pengambilan → serah-terima → ownership → My Garage.

Servis: booking member → preferensi mekanik → konfirmasi advisor → assignment → pemeriksaan → approval → sparepart → pekerjaan → QC → finalisasi → pembayaran → riwayat.

## Awards

Leaderboard memakai unit final terserah-terima, pencapaian target resmi, lalu nilai final. Penjualan tanpa Sales tetap berada pada total perusahaan tetapi tidak diatribusikan. Tahun berjalan provisional. Finalisasi membutuhkan Super Admin, target lengkap, periode berakhir, dan sumber refund/reversal authoritative.

## Backup dan rollback

Sebelum migration, buat dump database Laravel dengan `mysqldump --single-transaction`. Simpan dump di lokasi aman. Rollback migration hanya dilakukan setelah verifikasi backup dan persetujuan operasional. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau operasi terhadap database native `yza_automotive`.

## Known limitations

Refund/reversal authoritative belum tersedia; finalisasi Awards sengaja diblokir. Browser UAT, benchmark runtime dashboard, dan pengujian webhook sandbox masih memerlukan environment UAT/staging yang sesuai.
