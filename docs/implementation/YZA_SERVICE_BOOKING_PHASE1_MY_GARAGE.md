# YZA Service Booking Phase 1 — My Garage Integration

Tanggal implementasi: 12 Oktober 2026  
Scope: My Garage → Jadwalkan Servis → vehicle preselection → vehicle summary → ownership safety.

## Hasil

Member yang menekan **Jadwalkan Servis** pada kendaraan tertentu di My Garage sekarang diarahkan ke:

`/pelanggan/booking-servis/create?kendaraan=<vehicle_id>`

Kendaraan tersebut langsung terpilih pada form booking. Jika member memiliki beberapa kendaraan aktif, dropdown tetap dapat digunakan untuk mengganti kendaraan dan ringkasan di sisi kanan ikut diperbarui.

## Perubahan yang diterapkan

- Link **Jadwalkan Servis** pada `resources/views/pelanggan/kendaraan/index.blade.php` sekarang menggunakan route Laravel dengan parameter `kendaraan`.
- Link pada detail kendaraan tetap menggunakan route dan parameter kendaraan yang sama.
- `BookingServisController@create` tetap mempertahankan validasi ownership dan status kendaraan aktif.
- Controller menyiapkan data presentasi kendaraan: nama, nomor polisi, tahun, warna, gambar, kilometer, dan status verified ownership.
- `resources/views/pelanggan/booking-servis/create.blade.php` memakai prioritas `old('id_kendaraan_pelanggan', $selectedVehicleId)` untuk preselection.
- Form menampilkan summary card **Ringkasan Booking** dengan kendaraan, nomor polisi, kilometer terakhir, jenis servis, tanggal, dan jam.
- Badge **Verified YZA Ownership** hanya tampil berdasarkan `VerifiedCarOwnershipService`.
- Dropdown hanya berisi kendaraan aktif milik customer yang sedang login.
- Perubahan dropdown menggunakan data server yang sudah dirender; tidak ada fetch/API baru.
- Layout booking dibuat desktop-first dengan form sekitar 65% dan summary sticky sekitar 35%, serta responsive untuk layar kecil.
- Field booking existing tetap dipertahankan: jenis servis, tanggal, jam, keluhan, dan catatan.

## Ownership dan keamanan

Tidak ada input client yang dipercaya untuk menentukan `id_pelanggan`, `status`, atau ownership. Backend tetap mengambil customer dari user terautentikasi dan menolak parameter `kendaraan` jika kendaraan tersebut bukan milik customer atau sudah nonaktif.

Pembuatan booking tetap menggunakan status awal `menunggu`. Confirm/reject admin/petugas dan pembuatan work order tetap berjalan melalui flow existing.

## Test coverage

Test focused yang ditambahkan pada `tests/Feature/BookingServisStage4CTest.php`:

- kendaraan langsung terpilih dan summary menampilkan data kendaraan;
- kendaraan milik member lain ditolak;
- kendaraan nonaktif ditolak;
- daftar kendaraan pada form hanya berisi kendaraan aktif milik member yang login.

Validasi yang dijalankan:

- PHP lint controller dan test: lulus;
- `php artisan optimize:clear`: lulus;
- `php artisan view:cache`: lulus;
- focused booking create tests: **3 passed, 10 assertions**.

## Belum diimplementasikan

Fitur berikut sengaja tetap menjadi fase berikutnya:

- Calendar availability;
- Operating Hours;
- Slots;
- Capacity;
- Technician Preference;
- Service Advisor.

Phase 1 juga belum meminta kilometer baru dari customer dan belum membuat work order otomatis.

## Dampak data

- Database schema changed: **NO**
- Migration executed: **NO**
- Database created: **NO**
- Data operasional diubah: **NO**
