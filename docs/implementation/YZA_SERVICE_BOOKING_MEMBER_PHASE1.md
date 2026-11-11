# YZA Service Booking Member Phase 1

Tanggal implementasi: 12 Oktober 2026  
Scope: member/pelanggan — My Garage → Jadwalkan Servis → Booking Servis.

## Hasil implementasi

- CTA **Jadwalkan Servis** pada My Garage index dan detail kendaraan memakai named route `pelanggan.booking-servis.create` dengan query `kendaraan=<id_kendaraan_pelanggan>`.
- Halaman booking hanya memuat kendaraan aktif milik member yang sedang terautentikasi.
- Parameter kendaraan dari My Garage divalidasi terhadap ownership dan status aktif; kendaraan milik member lain atau nonaktif ditolak dengan 404.
- Kendaraan terpilih otomatis menggunakan prioritas `old('id_kendaraan_pelanggan', $selectedVehicleId)`.
- Ringkasan kendaraan menampilkan data aktual: gambar, nama, nomor polisi, tahun, warna, dan kilometer terakhir.
- Badge **Verified YZA Ownership** hanya tampil ketika `VerifiedCarOwnershipService` menyatakan ownership terverifikasi.
- Dropdown tetap tersedia untuk member dengan beberapa kendaraan. Perubahan pilihan memperbarui summary dari payload server-side yang dirender, tanpa API baru.
- Field existing tetap dipertahankan: jenis servis, tanggal, jam, keluhan, dan catatan. Kilometer hanya ditampilkan read-only.
- UI booking memakai progress visual tiga tahap, layout form sekitar 65% dan summary sticky sekitar 35%, serta responsive untuk layar kecil.

## Keamanan dan flow booking

Customer selalu diambil dari user terautentikasi. Request tidak dipercaya untuk menentukan `id_pelanggan`, `status`, atau ownership. Store flow existing tetap digunakan dan booking baru tetap berstatus `menunggu`; confirm/reject serta work order tetap berada pada alur operasional existing.

Tidak ada perubahan pada `BookingServisService`, `ServisService`, auth, payment, work order backend, atau halaman admin/operations.

## Validasi

- Test fokus booking member mencakup preselection dan summary, penolakan kendaraan member lain/nonaktif, serta daftar kendaraan aktif milik akun sendiri.
- Tidak ada migration, perubahan schema, pembuatan database baru, atau perubahan data operasional.

Perintah validasi yang digunakan:

```text
C:\xampp\php\php.exe artisan test tests/Feature/BookingServisStage4CTest.php
C:\xampp\php\php.exe artisan optimize:clear
C:\xampp\php\php.exe artisan view:cache
```
