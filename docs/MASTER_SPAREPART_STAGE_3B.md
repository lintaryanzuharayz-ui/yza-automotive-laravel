# Master Spare Part — Tahap 3B

Status: selesai untuk review. Scope terbatas pada `kategori_sparepart`, `supplier`, dan `sparepart`. Project serta database native tetap diperlakukan read-only.

## 1. Audit schema native

- `kategori_sparepart`: `id_kategori` bigint unsigned auto increment, `nama_kategori` varchar(80) unique, `deskripsi` varchar(255) nullable, `status` enum `aktif/nonaktif`, timestamps.
- `supplier`: `id_supplier` bigint unsigned auto increment, `kode_supplier` varchar(30) unique, `nama_supplier` varchar(120), `no_hp` varchar(25) nullable, `email` varchar(120) nullable, `alamat` text nullable, `kontak_person` varchar(120) nullable, `status` enum `aktif/nonaktif`, timestamps, index nama.
- `sparepart`: `id_sparepart` bigint unsigned auto increment, `kode_sparepart` varchar(30) unique, `id_kategori` bigint unsigned not null, `nama_sparepart` varchar(150), `merk` varchar(100) nullable, harga decimal(15,2), `stok` unsigned int, `stok_minimum` unsigned int, `satuan` varchar(30), `foto` varchar(255) nullable, status enum, timestamps, indexes stok/nama/kategori, FK `fk_sparepart_kategori` ke kategori dengan cascade update.
- Tidak ada kolom `id_supplier` pada `sparepart`, sehingga tidak dibuat relasi supplier langsung.

## 2. Audit behavior native

Native menyediakan list, tambah, edit, detail, dan aktif/nonaktif untuk ketiga master. Penghapusan hard delete tidak digunakan. Supplier dipakai oleh `stok_masuk`; kategori dan spare part juga memiliki dependency inventory/penjualan/service.

Native memiliki foto spare part di `uploads/sparepart`, memakai MIME JPEG/PNG/WEBP, batas 5 MB, nama acak, dan tidak menerima executable.

## 3. Master vs inventory

`sparepart.stok` adalah saldo baseline yang dibaca dari master, tetapi mutasinya dilakukan oleh inventory, penjualan, dan service. Native membuat spare part dengan stok awal 0 dan tidak menyediakan input stok pada edit. Laravel mempertahankan nilai existing 1:1, memasukkan `stok` ke guarded, tidak menerima `stok` pada Form Request, dan menampilkan status stok sebagai derived value: aman, menipis, atau habis.

`stok_minimum` tetap dapat dikelola sebagai atribut master. Tidak ada stok masuk/keluar, adjustment, riwayat, atau transaksi baru pada tahap ini.

## 4. Migration dan copy data

Migration: `database/migrations/2026_09_28_020000_create_sparepart_master_tables.php`.

Data target setelah copy:

| Tabel | Source | Target |
|---|---:|---:|
| kategori_sparepart | 9 | 9 |
| supplier | 1 | 1 |
| sparepart | 1 | 1 |

ID, kode, nama, kategori, harga beli/jual, stok, stok minimum, satuan, foto, dan status cocok. Satu file foto disalin ke `storage/app/public/uploads/sparepart`; hash source-target cocok. Native tidak dihapus, dipindah, atau diubah.

## 5. Model dan relationship

- `KategoriSparepart` hasMany `Sparepart` melalui `id_kategori`.
- `Sparepart` belongsTo `KategoriSparepart`.
- `Supplier` tidak memiliki relationship langsung ke `Sparepart` karena schema tidak mendukungnya.
- Model: `app/Models/KategoriSparepart.php`, `Supplier.php`, `Sparepart.php`.

## 6. Controller, Request, route, authorization

Admin controller: `KategoriSparepartController`, `SupplierController`, `SparepartController`. Petugas controller: `Petugas\SparepartController` read-only.

Form Request:

- `StoreKategoriSparepartRequest`, `UpdateKategoriSparepartRequest`
- `StoreSupplierRequest`, `UpdateSupplierRequest`
- `StoreSparepartRequest`, `UpdateSparepartRequest`

Route admin berada di `/admin/kategori-sparepart`, `/admin/supplier`, dan `/admin/sparepart`, dilindungi `auth` serta `role:admin`. Toggle status memakai PATCH dan CSRF framework. Petugas hanya `/petugas/sparepart` dan detail GET. Pelanggan serta guest tidak memperoleh akses admin.

Tidak ada katalog public spare part karena native tidak menyediakan public catalog spare part.

## 7. Security, foto, dan audit

- Validasi unique update mengecualikan record aktif.
- Harga dan stok minimum tidak boleh negatif.
- Kategori wajib ada melalui `exists` validation.
- Upload dibatasi image JPEG/PNG/WEBP, maksimum 5 MB, nama acak, basename-safe storage, dan public disk Laravel.
- Tidak ada route delete master; status nonaktif diprioritaskan karena dependency belum dimigrasikan.
- `MasterAuditService` menjadi abstraction sementara yang konsisten dan menulis ke Laravel log. Dependency `audit_log` native ditunda sampai tahap audit log khusus.

## 8. Test result

- PHPUnit target database: 9 test, 27 assertion, pass.
- Admin dapat membuka seluruh halaman master.
- Duplicate kategori ditolak.
- Harga negatif ditolak.
- Relationship kategori berjalan.
- Stok guarded dan tidak ada pada request update.
- Petugas dan pelanggan diblokir dari admin.
- Petugas read-only berhasil.
- Guest diarahkan ke login.
- Smoke regression `/mobil` dan detail mobil berhasil.
- Native homepage HTTP 200; native admin tanpa sesi 302.

## 9. Deferred dependencies dan file utama

Ditunda: `stok_masuk`, `detail_stok_masuk`, `riwayat_stok`, penjualan spare part, service spare part, inventory transaction, audit_log, pembayaran, transaksi, membership, points, promo, Midtrans, dan laporan final.

File utama yang berubah/ditambahkan:

- migration `2026_09_28_020000_create_sparepart_master_tables.php`
- model `KategoriSparepart.php`, `Supplier.php`, `Sparepart.php`
- controller admin kategori, supplier, spare part; controller petugas spare part
- enam Form Request master
- `MasterAuditService.php`
- route `routes/web.php`
- Blade views master admin dan petugas
- CSS master spare part pada `public/css/app.css`
- `tests/Feature/SparepartMasterTest.php`
- `phpunit.xml` diarahkan ke development database target, bukan database native

Tahap 3B berhenti di sini dan menunggu persetujuan sebelum modul inventory atau transaksi dikerjakan.
