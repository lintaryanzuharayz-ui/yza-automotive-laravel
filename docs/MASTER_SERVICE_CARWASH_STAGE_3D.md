# Master Jenis Servis, Paket Cuci, Pengaturan — Tahap 3D

Status: selesai untuk review. Scope hanya tiga master data; workflow booking, service, car wash, transaksi, pembayaran, dan inventory tidak dikerjakan.

## Audit native

`jenis_servis` memiliki `id_jenis_servis`, `nama_servis` unique varchar(120), deskripsi text nullable, `estimasi_harga` decimal(15,2), status enum `aktif/nonaktif`, dan timestamps current/current-on-update.

`paket_cuci` memiliki `id_paket`, `nama_paket` unique varchar(100), deskripsi text nullable, `harga` decimal(15,2), `estimasi_menit` unsigned smallint nullable, status enum, dan timestamps.

`pengaturan` memiliki `id_pengaturan`, `kunci_pengaturan` unique varchar(100), `nilai` text nullable, `deskripsi` varchar(255) nullable, dan hanya `updated_at` current/current-on-update.

Native menyediakan Admin CRUD/status untuk jenis servis dan paket cuci. Tidak ditemukan halaman public atau CRUD petugas untuk master tersebut. Isi pengaturan native adalah konfigurasi bisnis, bukan password/database credential/secret payment.

## Migration dan data copy

Migration: `database/migrations/2026_09_28_040000_create_service_carwash_settings_tables.php`.

| Tabel | Source | Target |
|---|---:|---:|
| jenis_servis | 6 | 6 |
| paket_cuci | 3 | 3 |
| pengaturan | 9 | 9 |

ID dan field baseline cocok. Target mempertahankan precision decimal, enum, unique constraint, dan timestamp behavior. Auto-increment target dapat lebih tinggi karena automated test transaction memakai insert lalu rollback; row baseline tetap sama.

## Models dan service

- `JenisServis`
- `PaketCuci`
- `Pengaturan`
- `PengaturanService` dengan whitelist sembilan key existing.

Tidak ada relationship antar ketiga tabel pada schema native. Dependency booking/service/car-wash sengaja deferred.

## Controller, Request, dan route

Admin controllers: `JenisServisController`, `PaketCuciController`, `PengaturanController`.

Form Requests:

- `StoreJenisServisRequest`, `UpdateJenisServisRequest`
- `StorePaketCuciRequest`, `UpdatePaketCuciRequest`
- `UpdatePengaturanRequest`

Routes Admin:

- `/admin/jenis-servis`
- `/admin/paket-cuci`
- `/admin/pengaturan`

Semua mutation dilindungi auth, role admin, CSRF, Form Request, unique validation, status whitelist, dan price/duration validation. Tidak ada hard delete; status aktif/nonaktif digunakan.

Pengaturan hanya menerima key yang terdaftar pada `PengaturanService::KEYS`. Route unknown key diblokir, dan tipe khusus timezone, currency, serta batas stok divalidasi.

## Petugas dan public

Tidak ada route petugas/public master yang ditambahkan karena audit native tidak menemukan akses langsung untuk master jenis servis, paket cuci, atau pengaturan. Tidak ada booking yang dibuat.

## Audit dan security

`MasterAuditService` existing digunakan untuk create/update/status master dan update pengaturan. `audit_log` migration tetap deferred.

Tidak ada sensitive credential ditemukan pada sembilan setting native. Credential teknis tetap tidak dipindahkan ke database target atau output/log.

Harga menggunakan database decimal dan validasi non-negative. Tampilan menggunakan format Rupiah tanpa mengubah nilai storage.

## Test dan regression

- Automated test: 26 test, 104 assertion, seluruhnya pass.
- Admin CRUD/status jenis servis: pass.
- Admin CRUD/status paket cuci: pass.
- Pengaturan whitelist dan invalid value: pass.
- Petugas/pelanggan blocked dari admin: pass.
- Guest redirect: pass.
- Regression authentication, mobil, sparepart, identity/kendaraan, foundation: pass.
- Native homepage: HTTP 200.
- Native database tidak menerima operasi write.

## Deferred dependencies

Booking servis, servis, detail servis, servis sparepart, booking cuci, transaksi, pembayaran, stok masuk, riwayat stok, penjualan, booking mobil, test drive, trade-in, used car, membership, YZA Points, promo, Midtrans, laporan final, dan audit_log tidak dikerjakan.

## File utama

- `database/migrations/2026_09_28_040000_create_service_carwash_settings_tables.php`
- `app/Models/JenisServis.php`
- `app/Models/PaketCuci.php`
- `app/Models/Pengaturan.php`
- `app/Services/PengaturanService.php`
- `app/Http/Controllers/Admin/JenisServisController.php`
- `app/Http/Controllers/Admin/PaketCuciController.php`
- `app/Http/Controllers/Admin/PengaturanController.php`
- Form Requests Stage 3D
- Blade views Stage 3D
- `routes/web.php`, `public/css/app.css`
- `tests/Feature/ServiceCarwashSettingsTest.php`

Tahap 3D berhenti di sini dan menunggu persetujuan sebelum workflow layanan atau transaksi dikerjakan.
