# YZA Member Foundation — Tahap 10A

## Status

Tahap 10A selesai diimplementasikan dan diverifikasi. Fitur ini merupakan lapisan Member Foundation dan Member Center di atas model serta lifecycle pelanggan yang sudah ada.

Tidak dibuat role baru, tabel members, tier membership, points, promo, payment gateway, atau perubahan schema database.

## Identitas Member

Semua pelanggan aktif yang memiliki relasi user-pelanggan yang valid ditampilkan sebagai YZA Member.

- Member ID deterministik: YZA-MBR- diikuti enam digit id_pelanggan.
- Contoh: pelanggan 1 menjadi YZA-MBR-000001.
- Member Since menggunakan pelanggan.created_at.
- Akun tidak dibuat otomatis untuk pelanggan legacy yang belum memiliki user.
- Admin dan Petugas tidak dapat membuka Member Center pelanggan.

## Member Center

Member Center tersedia melalui:

- /pelanggan/member
- /pelanggan/dashboard sebagai alias dashboard pelanggan existing

Agregasi read-only dilakukan oleh MemberDashboardService. Service ini mengambil data dari domain existing tanpa membuat engine baru dan tanpa melakukan mutation.

Informasi yang ditampilkan:

- identitas member;
- kendaraan milik pelanggan;
- booking mobil, test drive, servis, dan car wash;
- riwayat servis;
- transaksi dan ringkasan pembayaran;
- pengajuan jual mobil bekas;
- trade-in;
- link invoice atau dokumen existing;
- aktivitas terbaru.

Riwayat pada halaman dashboard dibatasi untuk tampilan ringkas. Halaman domain existing tetap menjadi sumber untuk daftar lengkap.

## Otorisasi dan Ownership

Controller mengambil pelanggan dari user yang sedang login. ID pelanggan tidak diterima dari request sebagai sumber ownership.

- Pelanggan hanya melihat data miliknya.
- Guest, Admin, dan Petugas ditolak oleh middleware atau controller.
- User tanpa relasi pelanggan tidak dapat menggunakan Member Center.
- Invoice menggunakan route dan authorization existing.
- Transaksi incoming dan outgoing disajikan dengan arah pembayaran yang sesuai.

## Performa dan Empty State

Query Member Center menggunakan eager loading pada relasi pembayaran transaksi dan batas jumlah untuk card serta aktivitas dashboard. Data kosong ditampilkan sebagai empty state yang aman; pelanggan tanpa aktivitas tidak menyebabkan error.

## Integrasi Lifecycle Existing

Tahap ini tidak mengganti lifecycle booking, servis, car wash, penjualan, pembelian mobil bekas, atau trade-in. Member Center hanya membaca status dan relasi yang sudah dikelola modul-modul tersebut.

Route pelanggan existing untuk profil, kendaraan, trade-in, transaksi, dan invoice tetap dipertahankan.

## Scope yang Ditunda

Fitur berikut tidak termasuk Tahap 10A:

- level atau tier membership;
- YZA Points;
- promo atau reward;
- Payment Center baru;
- Midtrans, Snap, webhook, QRIS, atau virtual account;
- tabel dan workflow membership terpisah.

## Verifikasi

Test khusus:

- 7 test passed;
- 28 assertions passed.

Regression suite dijalankan setelah test khusus. Tahap ini tidak menjalankan migration dan tidak menulis database Native yza_automotive.

## File Utama

- app/Services/MemberIdentityService.php
- app/Services/MemberDashboardService.php
- app/Http/Controllers/DashboardController.php
- routes/web.php
- resources/views/pelanggan/dashboard.blade.php
- public/css/app.css
- tests/Feature/MemberFoundationStage10ATest.php
