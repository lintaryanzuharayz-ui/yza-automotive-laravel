# Master Mobil & Foto Mobil — Tahap 3A

Status: implementasi Tahap 3A selesai untuk review; tidak ada perubahan pada project native atau database native.

## Batasan

Yang dipindahkan hanya tabel `mobil`, `foto_mobil`, dan salinan file pada `uploads/mobil`. Tahap ini tidak menghubungkan booking, test drive, penjualan, trade-in, transaksi, pembayaran, service, car wash, spare part, membership, points, promo, atau laporan final.

## Audit dan hasil migrasi

- Schema target mempertahankan primary key, unique key, index filter/harga, timestamps, dan foreign key foto ke mobil dengan cascade.
- Source saat audit: 14 mobil, 9 foto; target setelah migrasi: 14 mobil, 9 foto.
- Sembilan file foto disalin satu arah ke `storage/app/public/uploads/mobil`; hash SHA-256 source dan target cocok.
- Tidak ada operasi update/delete/rename/move pada native.

## Mapping native ke Laravel

| Native | Laravel |
|---|---|
| `includes/mobil.php` | `app/Models/Mobil.php`, `FotoMobil.php`, `MobilAuditService.php` |
| `admin/mobil/index.php` | `Admin\MobilController@index` |
| `admin/mobil/tambah.php` | `Admin\MobilController@create/store` |
| `admin/mobil/edit.php` | `Admin\MobilController@edit/update` |
| `admin/mobil/detail.php` | `Admin\MobilController@show` |
| `admin/mobil/foto.php` | `Admin\FotoMobilController` |
| `petugas/mobil/*` | `Petugas\MobilController` |
| `mobil.php`, `mobil-detail.php` | `Public\MobilController` |

Validasi dipisah ke `StoreMobilRequest`, `UpdateMobilRequest`, dan `StoreFotoMobilRequest`. Upload dibatasi image JPEG/PNG/WebP maksimal 5 MB dan nama file dibuat aman oleh server.

## Akses dan perilaku

- Publik hanya melihat mobil `tersedia`; detail status lain menghasilkan 404.
- Admin dapat list, tambah, edit, detail, upload foto, menjadikan foto utama, dan menghapus foto.
- Tidak ada hard delete mobil; route `PATCH admin/mobil/{mobil}/nonaktifkan` mempertahankan perilaku soft-state native dan menolak mobil berstatus `terjual`.
- Petugas hanya dapat list/detail read-only seluruh status.
- Foto utama mengikuti urutan native: `foto_utama`, `urutan`, `id_foto`. Saat foto utama dihapus, foto berikutnya otomatis menjadi utama.
- `audit_log` native belum dipindahkan karena tabel itu di luar scope; sementara aktivitas master mobil dicatat melalui Laravel log oleh `MobilAuditService`.

## Verifikasi

- Migrasi target berhasil.
- Route list berhasil memuat route publik, admin, dan petugas.
- Blade cache berhasil dikompilasi.
- HTTP: `/` 200, `/mobil` 200, `/mobil/9` 200, `/mobil/1` 404, akses admin/petugas tanpa login 302.
- Target memiliki 7 tabel: identity Stage 2 plus `mobil` dan `foto_mobil`.

## File utama

- `database/migrations/2026_09_28_010000_create_mobil_tables.php`
- `app/Models/Mobil.php`, `app/Models/FotoMobil.php`
- `app/Http/Controllers/Admin/MobilController.php`
- `app/Http/Controllers/Admin/FotoMobilController.php`
- `app/Http/Controllers/Petugas/MobilController.php`
- `app/Http/Controllers/Public/MobilController.php`
- `resources/views/admin/mobil/*`, `resources/views/petugas/mobil/*`, `resources/views/mobil/*`

Tahap berikutnya memerlukan persetujuan eksplisit sebelum menghubungkan modul bisnis apa pun.
