# Tahap 4C — Booking Servis

## 1. Audit Native

Native memiliki tabel `booking_servis` dan workflow pelanggan serta Admin/Petugas pada halaman booking servis. Audit dilakukan read-only.

## 2. Schema booking_servis

Migration `2026_09_29_070000_create_booking_servis_table.php` mempertahankan nama tabel, kolom, enum, FK, index, unique key, timestamp, dan tipe Native.

## 3. Data copy

Baseline source-target: 1 row. Primary key `1`, kode `BSV-202609-52D02542`, pelanggan `1`, kendaraan `1`, jenis servis `1`, tanggal `2026-09-26`, jam `09:00:00`, status `selesai` sama.

## 4. Models

Model baru: `BookingServis`.

## 5. Relationships

`BookingServis` belongsTo `Pelanggan`, `KendaraanPelanggan`, dan `JenisServis`. Relasi inverse ditambahkan pada ketiga model tersebut.

## 6. Booking flow

Pelanggan memilih kendaraan aktif miliknya, jenis servis aktif, tanggal masa depan, jam, keluhan, dan catatan. Booking dibuat berstatus `menunggu`.

## 7. Kode booking strategy

Kode dibuat server-side dengan format Native `BSV-YYYYMM-XXXXXXXX`, menggunakan random hexadecimal component dan pengecekan unique sebelum insert.

## 8. Date/time validation

Tanggal dan jam harus valid dan gabungannya harus lebih besar dari waktu sekarang. Native tidak memiliki aturan hari operasional, jam operasional, lead time khusus, atau batas maksimum tanggal.

## 9. Status transition matrix

| Status awal | Action | Status akhir |
|---|---|---|
| menunggu | confirm | diterima |
| menunggu | reject | ditolak |
| menunggu | cancel pelanggan | dibatalkan |
| diterima | cancel pelanggan | dibatalkan |
| diterima | mulai servis | diproses, ditunda ke Tahap Servis |

Status `diproses` Native dibuat ketika row `servis` dibuat. Tahap 4C tidak membuat row tersebut.

## 10. Pelanggan ownership

Kendaraan dicari melalui relationship pelanggan authenticated dengan status `aktif`. `id_pelanggan` tidak diambil dari request. Detail dan cancel dibatasi ke booking milik pelanggan.

## 11. Admin authorization

Admin dapat melihat daftar/detail serta menerima atau menolak booking, sesuai Native.

## 12. Petugas authorization

Petugas dapat melihat daftar/detail serta menerima atau menolak booking, sesuai Native. Assignment petugas tidak terjadi pada booking transition Native.

## 13. Service layer

`BookingServisService` menangani create, generate kode, confirm, reject, cancel, transition validation, dan transaction boundary. Controller hanya mengorkestrasi request/response.

## 14. Form Requests

`StoreBookingServisRequest` memvalidasi kendaraan, jenis servis, tanggal, jam, keluhan, dan catatan. Field status, pelanggan, petugas, dan kode tidak dapat dikontrol user.

## 15. Routes

Pelanggan: `/pelanggan/booking-servis`. Admin: `/admin/booking-servis`. Petugas: `/petugas/booking-servis`. Semua dilindungi middleware auth dan role.

## 16. Notification behavior

Audit source tidak menemukan pembuatan notifikasi untuk create, confirm, reject, atau cancel booking servis. Tidak ada notifikasi baru yang dibuat.

## 17. Deferred dependency servis

Konfirmasi tidak membuat row `servis`. Native membuat row servis saat action mulai pemeriksaan dari status `diterima`; bridge tersebut ditunda sampai tahap servis agar scope tidak melebar.

## 18. Test results

Test baru `BookingServisStage4CTest` berjumlah 19 test dan 29 assertions. Seluruhnya lulus.

## 19. Security review

Ownership kendaraan, IDOR, CSRF middleware, mass assignment, status tampering, role bypass, invalid date/time, inactive vehicle/service type, dan server-side code generation diuji.

## 20. Files changed

- `database/migrations/2026_09_29_070000_create_booking_servis_table.php`
- `app/Models/BookingServis.php`
- `app/Models/Pelanggan.php`
- `app/Models/KendaraanPelanggan.php`
- `app/Models/JenisServis.php`
- `app/Services/BookingServisService.php`
- `app/Http/Requests/StoreBookingServisRequest.php`
- `app/Http/Controllers/Pelanggan/BookingServisController.php`
- `app/Http/Controllers/OperationsBookingServisController.php`
- `routes/web.php`
- Booking servis Blade views
- `tests/Feature/BookingServisStage4CTest.php`
