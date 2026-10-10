# Tahap 4D — Booking Car Wash

## 1. Audit Native

Native memiliki workflow `booking_cuci` pelanggan dan operasional Petugas/Admin. Audit source dan schema dilakukan read-only.

## 2. Schema booking_cuci

Migration `2026_09_29_080000_create_booking_cuci_table.php` mempertahankan field, enum, index, unique key, FK, timestamp, dan decimal precision Native.

`id_transaksi` dipertahankan sebagai nullable unsigned field dengan unique index, tetapi foreign key ditunda karena tabel transaksi belum dimigrasikan.

## 3. Data copy

Baseline source-target: 1 row. ID, kode `BCW-202609-067D478B`, pelanggan, kendaraan, paket, petugas, tanggal, jam, harga `75000.00`, status `selesai`, dan timestamp dipertahankan.

## 4. Model/relationship

Model baru `BookingCuci` memiliki relasi ke `Pelanggan`, `KendaraanPelanggan`, `PaketCuci`, dan `Petugas`. Relasi inverse ditambahkan pada model terkait.

## 5. Booking flow

Pelanggan memilih kendaraan aktif miliknya dan paket cuci aktif. Booking dibuat `menunggu` dengan catatan, tanggal, jam, dan harga server-side.

## 6. Paket/harga behavior

Paket harus aktif. Harga tidak pernah diambil dari request pelanggan; service mengambil harga paket dari database.

## 7. Price snapshot behavior

Native menyimpan harga pada `booking_cuci.harga`. Laravel mempertahankan snapshot tersebut. Perubahan harga master setelah booking tidak mengubah booking lama.

## 8. Kode booking

Kode dibuat server-side dengan format Native `BCW-YYYYMM-XXXXXXXX`, random hexadecimal component, dan pengecekan unique.

## 9. Date/time rule

Tanggal dan jam harus valid serta berada di masa depan. Native tidak memiliki jam operasional, lead time khusus, kuota, bay, atau conflict schedule.

## 10. Status transition matrix

| Status awal | Action | Status akhir |
|---|---|---|
| menunggu | confirm | diterima |
| menunggu | cancel pelanggan | dibatalkan |
| diterima | start | proses |
| diterima | cancel pelanggan | dibatalkan |
| proses | complete | deferred ke financial stage |

Native tidak memiliki reject action pada workflow cuci.

## 11. Payment boundary

Native membuat transaksi ketika booking berstatus `proses` difinalisasi. Tahap 4D berhenti di status `proses` dan tidak membuat transaksi, pembayaran, invoice, atau Midtrans.

## 12. Ownership

Kendaraan di-resolve melalui relationship pelanggan authenticated dan status `aktif`. Detail/cancel pelanggan dibatasi ke booking miliknya.

## 13. Admin/Petugas authority

Admin dan Petugas sama-sama melihat daftar/detail, menerima booking, dan memulai proses sesuai Native. Petugas ditetapkan saat action operasional; tidak ada assignment saat create pelanggan.

## 14. Service layer

`BookingCuciService` menangani create, kode, confirm, start, cancel, assignment petugas, snapshot harga, transition validation, transaction, dan locking.

## 15. Controllers

`BookingCuciController` menangani flow pelanggan. `OperationsBookingCuciController` menangani daftar/detail/action Admin dan Petugas.

## 16. Requests

`StoreBookingCuciRequest` memvalidasi kendaraan, paket, tanggal, jam, dan catatan. Harga, status, pelanggan, petugas, transaksi, dan kode tidak dapat dikontrol user.

## 17. Routes

Pelanggan: `/pelanggan/booking-cuci`. Admin: `/admin/booking-cuci`. Petugas: `/petugas/booking-cuci`. Semua dilindungi auth dan role middleware.

## 18. Notifications

Audit Native tidak menemukan notifikasi create/confirm/start/complete/cancel. Tidak ada notifikasi baru yang dibuat.

## 19. Transaction/locking

Create dan transition memakai `DB::transaction`. Row booking dikunci saat transition untuk mencegah complete/cancel atau start/cancel bersamaan. Tidak ada lock slot karena Native tidak memiliki slot conflict.

## 20. Tests

`BookingCuciStage4DTest`: 17 test, 31 assertions, seluruhnya lulus. Mencakup baseline, ownership, price snapshot, role, tampering, transition, cancellation, dan concurrency-like scenario.

## 21. Security

IDOR, CSRF middleware, mass assignment, role bypass, status/price/customer tampering, inactive vehicle/package, date manipulation, dan server-side code generation diuji.

## 22. Deferred payment dependency

`id_transaksi` dan transition finansial setelah `proses` ditunda sampai tahap transaksi/pembayaran. Tidak ada payment state palsu.

## 23. Files changed

- `database/migrations/2026_09_29_080000_create_booking_cuci_table.php`
- `app/Models/BookingCuci.php`
- Relasi pada `Pelanggan`, `KendaraanPelanggan`, `PaketCuci`, `Petugas`
- `app/Services/BookingCuciService.php`
- `app/Http/Requests/StoreBookingCuciRequest.php`
- `app/Http/Controllers/Pelanggan/BookingCuciController.php`
- `app/Http/Controllers/OperationsBookingCuciController.php`
- `routes/web.php`
- View booking cuci pelanggan dan operasional
- `tests/Feature/BookingCuciStage4DTest.php`
