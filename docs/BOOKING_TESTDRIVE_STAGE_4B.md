# Tahap 4B — Booking Mobil dan Test Drive

## 1. Audit Native

Schema dan source Native diaudit read-only. Native menyediakan `booking_mobil` dan `test_drive`; seluruh halaman operasional menggunakan role Admin/Petugas dan pelanggan hanya dapat mengakses data miliknya.

## 2. Schema

Laravel mempertahankan nama tabel, kolom, tipe, enum, index, unique key, timestamp, primary key, dan foreign key Native pada migration `2026_09_29_060000_create_booking_test_drive_tables.php`.

## 3. Data copy

Baseline disalin ke database target dengan primary key dipertahankan: `booking_mobil` 2 baris dan `test_drive` 1 baris.

## 4. Booking workflow

Pelanggan membuat booking untuk mobil berstatus `tersedia`. Booking dibuat `menunggu` dengan expiry tiga hari. Native mengizinkan lebih dari satu booking pending pada unit; benturan diputuskan saat konfirmasi.

## 5. Test drive workflow

Pelanggan membuat test drive untuk mobil `tersedia` atau `dibooking`, dengan tanggal dan jam di masa depan. Test drive tidak mengubah status mobil.

## 6. Status transition

- Booking: `menunggu -> dikonfirmasi`, `menunggu/dikonfirmasi -> dibatalkan`, `dikonfirmasi -> selesai`, `menunggu/dikonfirmasi -> kadaluarsa`.
- Test drive: `menunggu -> disetujui/ditolak/dibatalkan`, `disetujui -> selesai/dibatalkan`.
- Transition diterapkan melalui method service, bukan status bebas dari request.

## 7. Mobil lock strategy

Konfirmasi booking mengunci row `mobil` dengan `lockForUpdate`, memeriksa status `tersedia`, lalu mengubahnya menjadi `dibooking` dalam transaksi yang sama.

## 8. Double booking protection

Dua booking pending dapat ada sesuai Native, tetapi hanya satu yang dapat dikonfirmasi untuk unit yang sama. Konfirmasi kedua membaca status mobil terbaru setelah lock dan ditolak.

## 9. Ownership

ID pelanggan selalu diambil dari user terautentikasi. Detail dan pembatalan pelanggan menggunakan pemeriksaan `id_pelanggan`, sehingga IDOR ditolak.

## 10. Authorization

- Pelanggan: membuat, melihat milik sendiri, dan membatalkan booking/test drive milik sendiri.
- Admin: melihat dan memproses konfirmasi booking; menyetujui, menolak, dan menyelesaikan test drive.
- Petugas: melihat dan memproses booking/test drive, termasuk pembatalan operasional sesuai Native.
- Guest: diarahkan ke login.

## 11. Notifications

Audit Native tidak menemukan notifikasi booking atau test drive. Tidak ada notifikasi baru yang ditambahkan.

## 12. Controllers

`BookingMobilController`, `TestDriveController`, `OperationsBookingController`, dan `OperationsTestDriveController` menjaga controller tetap tipis.

## 13. Services

`BookingMobilService` menangani create, confirm, cancel, complete, expire, dan unlock unit. `TestDriveService` menangani create dan transition test drive.

## 14. Form Requests

`StoreBookingMobilRequest` dan `StoreTestDriveRequest` memvalidasi input publik. `id_pelanggan`, `id_petugas`, dan status internal tidak menerima mass assignment bebas.

## 15. Routes

Route pelanggan berada di `/pelanggan/booking-mobil` dan `/pelanggan/test-drive`. Route operasional berada di `/admin/...` dan `/petugas/...` dengan middleware role.

## 16. Tests

`BookingTestDriveStage4BTest` menambahkan 22 test case yang mencakup import, ownership, role, transition, status mobil, expiry, invalid date, dan regression.

## 17. Concurrency test

Test konfirmasi kedua memastikan unit yang telah dikunci dan berubah menjadi `dibooking` tidak dapat dikonfirmasi ulang. Service menggunakan `DB::transaction` dan `lockForUpdate`.

## 18. Rollback test

Konfirmasi terhadap unit yang sudah berubah menjadi `terjual` gagal tanpa mengubah booking dari `menunggu` atau status mobil.

## 19. Security review

CSRF berasal dari middleware Laravel, ownership diperiksa server-side, status memakai explicit action, query menggunakan Eloquent/query binding, dan output Blade di-escape.

## 20. Deferred dependencies

`penjualan_mobil` tidak dimigrasikan sesuai scope. Saat unlock unit, service hanya memeriksa tabel tersebut bila tersedia; pada target Tahap 4B tabel belum ada. Tidak ada foreign key palsu yang dibuat.

## 21. File changed

- Migration: `database/migrations/2026_09_29_060000_create_booking_test_drive_tables.php`
- Models: `BookingMobil.php`, `TestDrive.php`, relasi pada `Mobil`, `Pelanggan`, `Petugas`
- Services: `BookingMobilService.php`, `TestDriveService.php`
- Controllers: pelanggan dan operasional booking/test drive
- Requests: `StoreBookingMobilRequest.php`, `StoreTestDriveRequest.php`
- Routes dan Blade views pelanggan/operasional
- Test: `tests/Feature/BookingTestDriveStage4BTest.php`
