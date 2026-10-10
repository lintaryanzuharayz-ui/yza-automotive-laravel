# Promo YZA Member + Service Reminder — Tahap 10C

## Scope

Tahap ini menambahkan dua fitur Laravel baru:

1. katalog benefit Promo YZA Member;
2. pengingat servis berbasis riwayat servis selesai.

Database Native tetap read-only. Tidak dibuat schema parity Native.

## Promo Catalog

Promo disimpan pada tabel promo_member. Field utamanya adalah id_promo, kode_promo, nama, deskripsi, jenis_benefit, nilai_benefit, minimum_transaksi, maksimum_benefit, berlaku_untuk, mulai_at, selesai_at, syarat_ketentuan, status, dan created_by.

Jenis benefit:

- persentase;
- nominal;
- informasi.

Scope layanan:

- semua;
- servis;
- cuci_mobil;
- penjualan_sparepart;
- penjualan_mobil.

Trade-In dan pembelian mobil bekas outgoing tidak menjadi scope promo.

Promo member terlihat hanya jika status aktif dan waktu aplikasi berada di antara mulai_at dan selesai_at. Waktu diproses dengan timezone Asia/Jakarta.

Nilai benefit, minimum transaksi, dan maksimum benefit hanya bersifat informasional. Tahap 10C tidak menerapkan promo ke transaksi, invoice, PaymentService, atau YZA Points.

## Admin Management

Admin memiliki CRUD:

- daftar;
- tambah;
- detail;
- edit;
- aktifkan/nonaktifkan.

created_by selalu berasal dari authenticated admin, bukan dari request. Tidak ada hard delete default.

FormRequest memvalidasi kode unik, enum, nominal non-negatif, persentase 0 sampai 100, scope valid, dan periode selesai tidak lebih awal dari mulai.

## Member Catalog

Member aktif dapat membuka:

- /pelanggan/promo
- /pelanggan/promo/{promo_member}

Promo future, expired, atau nonaktif tidak tampil dan detail direct access ditolak.

UI secara eksplisit menyebut promo sebagai benefit katalog dan tidak menampilkan tombol redeem, klaim, checkout, atau penggunaan promo.

## Demo Data

Command idempotent:

- yza:promo-demo

Command membuat tiga promo demo moderat:

- Promo Servis Member;
- Promo Car Wash Member;
- Promo Spare Part Member.

Command hanya menulis database Laravel dan tidak membuat transaksi atau Points.

## Service Reminder

ServiceReminderService bersifat read-only dan tidak membutuhkan tabel service_reminders.

Sumber tanggal servis terakhir adalah servis.tanggal_selesai dengan status servis selesai. created_at, booking pending, servis dikerjakan, dan transaksi belum lunas tidak digunakan.

Konfigurasi terpusat:

- interval_months = 6;
- due_soon_days = 30.

Next service date adalah tanggal selesai terakhir ditambah enam bulan.

Status:

- upcoming: lebih dari 30 hari sebelum due date;
- due_soon: 0 sampai 30 hari, termasuk tepat pada due date;
- overdue: melewati due date;
- no_history: kendaraan belum memiliki servis selesai.

Hanya kendaraan pelanggan berstatus aktif yang diproses. Kendaraan dijual, ditukar, dan nonaktif dikecualikan.

## Member Center

Member Center menampilkan:

- jumlah promo aktif dan preview maksimal tiga promo;
- jumlah reminder overdue dan due soon;
- preview reminder prioritas;
- link ke katalog promo dan halaman pengingat servis.

Halaman pengingat tersedia pada /pelanggan/pengingat-servis dan menampilkan kendaraan milik member sendiri, servis terakhir, tanggal selesai, kilometer, jenis servis bila tersedia, estimasi berikutnya, status, dan CTA booking servis existing.

Tidak ada auto-booking, cron, queue, email, WhatsApp, SMS, atau push notification.

## Authorization and Security

- Admin mengelola promo.
- Pelanggan aktif melihat promo dan reminder miliknya.
- Guest diarahkan ke login.
- Admin/Petugas ditolak pada route pelanggan.
- Reminder selalu resolve dari authenticated user ke pelanggan dan kendaraan miliknya.
- id_pelanggan tidak diterima dari request.
- Blade menggunakan escaped output.
- Tidak ada endpoint redeem atau endpoint mutation Points.

## Isolation and Performance

Membuka promo, detail promo, Member Center, atau reminder tidak mengubah transaksi, pembayaran, invoice, poin, maupun ledger.

Reminder mengambil seluruh kendaraan aktif dalam satu query dan seluruh servis selesai terkait dalam satu query terkelompok, sehingga tidak melakukan query per kendaraan.

Promo Member Center dibatasi maksimal tiga item. Katalog memakai pagination.

## Deferred

Deferred:

- promo redemption/application;
- coupon dan voucher;
- penggunaan Points sebagai diskon atau pembayaran;
- tier membership;
- expiry Points;
- Midtrans, Snap, webhook, QRIS, dan VA;
- otomasi WhatsApp/email/push;
- reminder scheduler/background job.

## Verification

Test khusus Tahap 10C: 9 test passed, 41 assertions passed.

Regression seluruh project dijalankan setelah perubahan. Native database tidak dimigrasikan dan tidak ditulis.
