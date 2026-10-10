# Pelanggan, Petugas, Kendaraan Pelanggan — Tahap 3C

Status: selesai untuk review. Scope hanya identity-management, registrasi pelanggan, profil terkait, dan kendaraan pelanggan. Database native `yza_automotive` tidak menerima operasi write.

## 1. Audit native dan schema

Tabel identity existing tidak dibuat ulang: `roles`, `users`, `pelanggan`, dan `petugas` tetap menggunakan migration/data Tahap 2.

Tabel baru native `kendaraan_pelanggan` memiliki `id_kendaraan_pelanggan` bigint unsigned auto increment, `id_pelanggan` FK ke `pelanggan`, merk/model/tipe, tahun, warna, enum transmisi dan bahan bakar, nomor polisi/rangka/mesin nullable, kilometer unsigned default 0, status enum `aktif/dijual/ditukar/nonaktif`, timestamps current/current-on-update, index pelanggan dan nomor polisi.

Baseline native saat audit: roles 3, users 5, petugas 3, pelanggan 3, kendaraan pelanggan 1.

## 2. Data copy

Migration: `database/migrations/2026_09_28_030000_create_kendaraan_pelanggan_table.php`.

Data kendaraan source disalin ke target dengan primary key dipertahankan. Source row: id 1, pelanggan 1, Toyota Avanza, tahun 2022, nomor polisi B 1234 YZA, kilometer 45000, status dijual.

## 3. Model dan relationship

- `User` hasOne `Pelanggan` dan hasOne `Petugas`.
- `Pelanggan` belongsTo `User` dan hasMany `KendaraanPelanggan`.
- `Petugas` belongsTo `User`.
- `KendaraanPelanggan` belongsTo `Pelanggan`.

Model baru: `app/Models/KendaraanPelanggan.php`. Model existing dipakai dan hanya `Pelanggan` ditambah relationship kendaraan.

## 4. Admin management

- `/admin/pelanggan`: daftar, pencarian/filter, detail, edit, status aktif/nonaktif, daftar kendaraan.
- `/admin/petugas`: daftar, tambah, detail, edit, status aktif/nonaktif.
- Pembuatan petugas memasukkan `users` dan `petugas` di dalam `DB::transaction()`.
- Role petugas dipilih server-side dari role `petugas`; request tidak dapat menyuntikkan role lain.
- Tidak ada hard delete pelanggan/petugas.

## 5. Registrasi publik

- `GET /register` dan `POST /register` tersedia untuk guest.
- Registrasi memvalidasi field native, username/email unique, password confirmation, CSRF, lalu membuat users + pelanggan atomic.
- Role selalu `pelanggan` dari server.
- Password memakai Laravel Hash dan tidak pernah dicatat.

## 6. Profil dan password

- `/pelanggan/profil` hanya memakai pelanggan dari authenticated user.
- `/petugas/profil` hanya memakai petugas dari authenticated user.
- `/akun/password` memakai verifikasi password lama melalui `PasswordCompatibilityService`, confirmation, dan hash baru Laravel.
- Tidak ada form yang menerima `id_user`, `id_pelanggan`, role, atau password hash sebagai authority.

## 7. Kendaraan saya

- `/pelanggan/kendaraan`: list kendaraan sendiri.
- `/pelanggan/kendaraan/create`: tambah kendaraan dengan owner dari session.
- Detail/edit/update selalu memeriksa `id_pelanggan` terhadap pelanggan authenticated.
- Kendaraan dinonaktifkan melalui PATCH; tidak ada hard delete.
- `id_pelanggan` tidak diterima sebagai sumber ownership dari form.

## 8. Validation, authorization, audit

Form Request tersedia untuk registrasi, kendaraan, profil, password, dan petugas. Tahun dibatasi 1886 sampai tahun berikutnya, kilometer non-negative, enum dipertahankan, dan string length mengikuti schema.

Route admin memakai `auth` + `role:admin`. Route petugas/pelanggan memakai role masing-masing. Ownership kendaraan menggunakan scoped customer check. Audit menggunakan `MasterAuditService` yang sama dengan Tahap 3B; migration `audit_log` tetap deferred.

## 9. Test dan security

Automated test: 20 test, 70 assertion, seluruhnya pass pada development database target.

Cakupan: register valid, hash password, login akun baru, duplicate username/email, role injection, admin authorization, atomic petugas, profile self-scope, vehicle validation, vehicle ownership/IDOR, password change, guest redirect, dan regression mobil/sparepart/foundation.

Native homepage tetap HTTP 200 dan native admin protected pages tetap redirect 302 tanpa sesi. Database native tidak diubah; verifikasi count identity dan kendaraan tetap cocok.

## 10. Deferred dependencies

Booking, test drive, booking servis, service, car wash, penjualan, spare part transaction, inventory, trade-in, used car, pembayaran, membership, YZA Points, promo, Midtrans, laporan final, dan audit_log tidak dikerjakan.

## 11. File utama

- `database/migrations/2026_09_28_030000_create_kendaraan_pelanggan_table.php`
- `app/Models/KendaraanPelanggan.php` dan perubahan relationship `Pelanggan.php`
- `app/Http/Controllers/RegistrationController.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Controllers/Pelanggan/KendaraanController.php`
- `app/Http/Controllers/Admin/PelangganController.php`
- `app/Http/Controllers/Admin/PetugasController.php`
- Form Requests Stage 3C
- route, Blade views, CSS foundation tambahan
- `tests/Feature/IdentityStage3CTest.php`

Tahap 3C berhenti di sini dan menunggu persetujuan sebelum modul layanan, inventory, transaksi, membership, points, atau payment dikerjakan.
