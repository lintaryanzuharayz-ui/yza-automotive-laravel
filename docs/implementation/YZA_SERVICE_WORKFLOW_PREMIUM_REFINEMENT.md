# YZA Service Workflow & Premium UI Refinement

Tanggal: 8 Oktober 2026  
Project: YZA Automotive / YZA Dealer Management System

## 1. Previous Workflow

Flow native sebelum refinement:

`Member → kendaraan aktif → booking servis → petugas confirm/reject → work order → pemeriksaan → pengerjaan → detail jasa/sparepart → finalisasi → transaksi → payment → servis selesai → reminder calculated`.

Core workflow sudah menggunakan service layer, ownership check, database transaction, row locking, server-side total, dan inventory rollback. Refinement tidak mengganti state machine tersebut.

## 2. New Workflow

Flow yang sekarang divisualkan lebih jelas:

`Booking Menunggu → Booking Diterima → Pemeriksaan Kendaraan → Sedang Dikerjakan → Menunggu Pembayaran → Servis Selesai`.

Status database tetap memakai enum existing. Perubahan hanya pada presentation label, dashboard query, detail work order, customer timeline, notification internal, dan report read-only.

## 3. Status Mapping

| Native status | Label UI |
|---|---|
| `menunggu` | Menunggu Konfirmasi |
| `diterima` | Booking Diterima |
| `diproses` | Work Order Dibuka |
| `pemeriksaan` | Pemeriksaan Kendaraan |
| `dikerjakan` | Sedang Dikerjakan |
| `belum_bayar` / `menunggu_bayar` | Menunggu Pembayaran |
| `dp` | Pembayaran Sebagian |
| `selesai` | Servis Selesai |
| `ditolak` | Ditolak |
| `dibatalkan` | Dibatalkan |

Presenter status tersedia di `app/Services/ServiceStatusPresenter.php`, termasuk tone badge dan timeline progress.

## 4. Petugas Dashboard

Dashboard petugas berubah dari identity card menjadi **YZA Service Command Center**. Data aktual yang ditampilkan:

- Booking Hari Ini;
- Menunggu Pemeriksaan;
- Sedang Dikerjakan;
- Menunggu Pembayaran;
- Selesai Hari Ini;
- Stok Kritis;
- antrean servis hari ini dengan customer, kendaraan, jenis servis, keluhan, jam, status, dan aksi;
- prioritas booking pending, payment pending, dan stok kritis;
- quick action menuju route yang benar-benar tersedia.

Query berada di `app/Services/ServiceOperationsDashboardService.php`.

## 5. Member Timeline

Detail servis member sekarang menampilkan timeline horizontal pada desktop dan vertical pada mobile. Step berasal dari status servis/transaksi aktual; tidak ada fake completion.

Detail yang ditampilkan apabila tersedia:

- nomor booking dan work order;
- kendaraan, plat nomor, kilometer;
- jenis servis dan tanggal masuk;
- keluhan dan hasil pemeriksaan;
- detail jasa dan sparepart;
- total, jumlah terbayar, dan status pembayaran;
- link invoice dan reminder.

## 6. Work Order UI

Work order petugas/admin menggunakan section:

1. Customer & Vehicle
2. Complaint
3. Inspection & Diagnosis
4. Service Tasks
5. Spare Parts
6. Cost Summary
7. Service Status

Finalisasi hanya ditampilkan saat native status `dikerjakan`. Payment tetap diproses melalui route/service existing. Total pada tampilan hanya summary; authority tetap server-side.

## 7. Notification

`ServiceNotificationService` memakai tabel/model `notifikasi` yang sudah ada. Tidak ada tabel baru.

Event internal yang ditambahkan:

- booking servis diterima;
- booking servis ditolak;
- kendaraan mulai diperiksa;
- servis mulai dikerjakan;
- pembayaran servis diverifikasi;
- servis selesai setelah payment lunas.

Notification dibuat idempotent berdasarkan user, tipe referensi, ID referensi, dan judul. Tidak ada klaim bahwa WhatsApp otomatis terkirim.

Portal notifikasi customer memakai route `notifications.index` dan tetap owner-scoped.

## 8. Reminder

Reminder existing tetap menggunakan `ServiceReminderService` dan menghitung status `upcoming`, `due_soon`, `overdue`, atau `no_history` dari servis terakhir yang selesai. Refinement menegaskan reminder tersebut pada member service detail dan dashboard.

Scheduler/queue persistent belum ditambahkan karena schema reminder belum ada dan scope refinement harus menjaga native DB untouched.

## 9. Report

Admin mendapat route `admin.laporan-servis.index` dengan report print-friendly. Filter yang tersedia:

- tanggal dari/sampai;
- status;
- jenis servis;
- pencarian WO, customer, plat nomor, atau model.

Summary report:

- total servis;
- selesai;
- pending;
- pendapatan;
- average transaction.

Report membaca data aktual dari `servis` dan `transaksi`; tidak membuat tabel metadata atau export dependency baru.

## 10. Security

Security boundary existing dipertahankan:

- role admin/petugas/member tidak dilonggarkan;
- active membership middleware tetap berlaku pada portal pelanggan;
- ownership customer tetap diverifikasi di controller;
- CSRF tetap digunakan pada form;
- status transition tetap diputuskan service layer;
- total, stok, dan payment tetap server-side;
- notifikasi hanya dapat dibaca user pemiliknya;
- public webhook/payment boundary tidak diubah.

## 11. Files Modified

File utama yang ditambahkan/diperbarui:

- `app/Services/ServiceStatusPresenter.php`
- `app/Services/ServiceNotificationService.php`
- `app/Services/ServiceOperationsDashboardService.php`
- `app/Services/BookingServisService.php`
- `app/Services/ServisService.php`
- `app/Services/PaymentService.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/NotifikasiController.php`
- `app/Http/Controllers/Admin/LaporanServisController.php`
- `app/Http/Controllers/OperationsServisController.php`
- `app/Http/Controllers/Pelanggan/ServisController.php`
- `routes/web.php`
- dashboard, work order, member service, notification, dan report Blade views.

## 12. Database Changes

**Tidak ada perubahan database.** Tidak ada migration, ALTER, DROP, DELETE, TRUNCATE, seed, atau perubahan enum.

Tabel notification existing dipakai kembali. Reminder tetap calculated/read-only seperti native implementation.

## 13. Tests

Baseline sebelum refinement: 286 passed / 870 assertions.  
Hasil setelah refinement: **290 passed / 879 assertions / 0 failures**.  
Durasi suite terakhir: 23.24 detik.

Selain suite test, dilakukan:

- PHP lint pada file PHP baru/perubahan;
- route list compilation;
- Blade view cache compilation.

## 14. Visual QA

View baru memakai breakpoint responsive:

- desktop: dashboard luas, KPI 6 kolom, queue table dengan data lengkap;
- tablet: KPI turun menjadi 2–3 kolom, panel menjadi satu kolom;
- mobile: KPI satu kolom, work order/detail table memakai horizontal scroll, timeline berubah vertical.

UI menggunakan YZA black/charcoal, white surface, dan red accent. Tidak ada primary blue/purple/random neon atau animasi berlebihan.

Validasi visual browser production-like tetap perlu dilakukan pada viewport 1920×1080, 1366×768, 768, 390, dan 430 dengan akun admin, petugas, dan member nyata. Smoke check browser otomatis tidak dapat dijalankan karena helper UI lokal berhenti sebelum membuka target window; compile Blade dan route validation tetap lulus.

## 15. Known Limitations

- Public appointment landing page masih berupa WhatsApp lead dan belum menjadi booking record.
- Reminder belum scheduler/queue persistent.
- Report belum export PDF/Excel; tersedia print-friendly report.
- Admin dashboard baru menampilkan service summary, bukan full BI dashboard.
- Petugas dashboard menampilkan antrean servis dan quick actions; queue module detail tetap memakai halaman operation existing.
- WhatsApp otomatis tidak diklaim terkirim.
- Production Midtrans, queue worker, email delivery, dan deployment HTTPS belum diuji di environment produksi.
