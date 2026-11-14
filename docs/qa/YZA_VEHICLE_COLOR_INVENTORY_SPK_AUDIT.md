# YZA Automotive — Vehicle Color, Inventory & SPK Audit

Tanggal: 8 November 2026. Target: `/admin/pesanan-mobil`.

## 1. Hasil audit operasional READ ONLY

Audit memakai transaksi MySQL `SET TRANSACTION READ ONLY`, lalu rollback. Tabel aktual: `mobil`, `mobil_warna`, `mobil_galeri`, `vehicle_inventories`, booking, pesanan, penjualan/transaksi, PDI dan ownership. Tidak ada perubahan master, VIN, stok, reservasi atau database operasional. Bukti: `vehicle-color-inventory-readonly.json` dan `vehicle-color-catalog-after.json`.

Snapshot: **26 row mobil**, **44 row warna katalog**, **12 row stok agregat**; tidak ditemukan grup nomor rangka nonempty duplikat pada tabel mobil. Tidak ditemukan row Mobil sekunder yang berbagi aggregate key dengan master representatif pada snapshot operasional.

### PREM-03 / Porsche 911 GT3 RS

| Data | Nilai aktual |
|---|---|
| Mobil | ID 252, PREM-03 |
| Varian/tahun | 4.0 Performance / 2024 |
| Warna row inventori | Guards Red |
| Status row | tersedia |
| Inventory | ID 5, agregat |
| stock_total | 2 |
| sold_units | 0 |
| Reservasi aktif | 0 |
| Sisa eligible | **2 − 0 − 0 = 2** |
| Row Mobil untuk model ini | 1 |
| VIN dan nomor mesin pada master | Terisi, tetapi hanya satu pasang identitas master |
| Jumlah warna katalog | **5** |
| Warna dengan stok eligible | **1** |

| Warna katalog aktual | Kode | Swatch | Foto tersedia | Stok eligible |
|---|---|---|---:|---:|
| Guards Red | guards-red | #c91c26 | 4 | 2 agregat |
| Racing Yellow | racing-yellow | #e4b51b | 4 | 0 |
| GT Silver Metallic | gt-silver-metallic | #aeb3b8 | 4 | 0 |
| Jet Black Metallic | jet-black-metallic | #151618 | 4 | 0 |
| Arctic Grey | arctic-grey | #777d84 | 4 | 0 |

Angka **2 bukan bukti dua unit dengan dua VIN berbeda**. Itu kapasitas stok agregat Guards Red. Tidak membuat dua objek fisik dari satu identitas master. Identitas VIN/engine mentah tidak disalin ke laporan publik ini.

## 2. Penyebab satu warna muncul

| Hipotesis | Hasil |
|---|---|
| A. Hanya satu warna tersedia dalam stok | Benar untuk inventori PREM-03: Guards Red saja |
| B. Query membentuk swatch hanya dari eligible inventory rows | Benar; modal sebelumnya mengambil daftar `vehicles()` yang sudah difilter stock > 0 dan mengelompokkan warna row tersebut |
| C. Relasi katalog warna belum ada | Salah; MobilWarna/MobilGaleri sudah berisi lima warna dan 20 foto untuk PREM-03 |
| D. Semua warna digabung menjadi satu stok | Tidak terbukti; inventory key existing memasukkan warna, jadi angka 2 terikat Guards Red, bukan lima warna |
| E. Bug frontend membuang stok warna lain yang sebenarnya ada | Tidak ditemukan stok warna lain untuk model ini. Kekurangannya adalah **warna katalog tanpa stok tidak ditampilkan**, bukan hilangnya stok yang tercatat |

Jadi hanya menambah swatch dari galeri lalu menganggap semuanya tersedia akan salah. Perbaikan memisahkan metadata katalog dan eligibility inventori.

## 3. Perubahan katalog, pilihan dan foto

`SmartSpkOrderService::catalog()` membentuk daftar model/varian dari row database dan relasi `mobil_warna`/`mobil_galeri`; tidak mengarang warna dari konfigurasi gambar. Warna inventori yang belum mempunyai metadata katalog tetap dapat ditampilkan berdasarkan row Mobil aktual, tanpa membuat ID/kode warna katalog fiktif.

Setiap warna menyertakan nama, kode/slug jika ada, hex/swatch, foto yang benar-benar ada, jumlah stok eligible, jumlah stok agregat, jumlah row unit fisik, jumlah identitas lengkap, dan ID unit eligible. EXISTS/availability existing tetap menjadi sumber status. Batch agregat dihitung satu kali per inventory ID agar alias/row representatif tidak menggandakan stok. Foto katalog bisa ditampilkan ketika stok kosong, tetapi bukan bukti ada kendaraan yang dapat dipesan.

Urutan modal sekarang:

1. Member aktif.
2. Model kendaraan.
3. Varian/spesifikasi.
4. Warna katalog dengan status/jumlah stok.
5. Unit eligible atau sumber stok agregat untuk warna itu.
6. Foto/kode/status/identitas unit.
7. Harga & diskon, lalu konfirmasi SPK existing.

Swatch stok kosong bertanda **Tidak tersedia** dan memakai border dashed. Swatch tersebut tetap bisa dibuka untuk melihat foto katalog, tetapi unit ID dikosongkan, quote lama dibatalkan, nominal ringkasan dikosongkan dan save/next tidak dapat melanjutkan reservasi. Memilih model/varian/warna lain memperbarui opsi unit, foto dan stok secara konsisten. Nomor rangka/warna record fisik tidak diubah oleh frontend.

Setelah fix, audit read-only pada service aktual menghasilkan **5 warna katalog / 1 warna tersedia**, dengan stok 2/0/0/0/0 sesuai tabel di atas.

## 4. SPK, Pre-Order dan batas workflow existing

Pre-order existing adalah permintaan per customer + product/model; bukan per warna. `MemberVehiclePreorderService` menolak create ketika produk masih mempunyai stok, dan mengambil warna dari row/synthetic product. Mutation route existing tersedia bagi member pemilik akun, sedangkan pusat Pre-Order Admin hanya menyediakan tampilan/filter. Karena itu **tidak ada tindakan “Ajukan Pre-Order warna ini” yang sah dari modal staf saat Guards Red masih tersedia**.

UI menyediakan **Buka pusat Pre-Order** dan menjelaskan bahwa pengajuan warna tertentu belum didukung langsung pada akun staf. Tidak membuat route mutasi baru, berpindah identitas ke akun member, mengubah deduplikasi product_key, atau membuat checkout/pre-order otomatis dari klik swatch. Jika dibutuhkan pre-order spesifik warna oleh staf, perlu kontrak otorisasi/identity dan deduplication terpisah yang disetujui; tidak dipalsukan sebagai fitur yang sudah tersedia.

## 5. Identitas fisik, PDI dan handover

Row tanpa aggregate inventory diperlakukan sebagai unit individual; kode dan VIN aktual dipertahankan. Row yang terhubung stok agregat tetap diberi label **stok agregat**, tidak diekspansi menjadi beberapa VIN. UI menyebut identitas belum dialokasikan dan kebutuhan verifikasi pada PDI.

Pengaman tambahan `VehiclePdiIntegrityService::assertPhysicalIdentity`:

- Mengunci row Mobil authoritative.
- Menolak VIN/nomor mesin kosong sebelum PDI dinyatakan lulus.
- Menolak VIN **atau** nomor mesin yang sudah diklaim PDI lulus milik pesanan lain yang tidak ditolak/dibatalkan, termasuk pesanan yang sudah diserahkan.
- Memeriksa lagi saat validasi PDI/pickup/handover. Reinspection order yang sama tetap diperbolehkan, dan replay handover existing tetap idempotent.

Unique constraint nomor rangka/nomor mesin pada tabel Mobil serta car locking existing dipertahankan. Pengaman ini tidak membuat VIN baru atau otomatis menjadikan master katalog sebagai dua unit. Untuk batch dengan satu identitas master, identitas tersebut tidak boleh dipakai meluluskan dua pesanan. Verifikasi QC/PDI pertama harus memakai identitas fisik yang benar; pesanan berikutnya memerlukan identitas unit lain yang nyata dan proses alokasi yang benar.

Binding order/sale/transaction, hash proof PDI, checklist, inspector permission dan final ownership guard P0 tetap dipakai. Tidak mengubah state machine pembayaran/hand-over. Identitas belum lengkap atau sudah diklaim menghasilkan penolakan transaksi, bukan PDI PASS/ownership palsu.

## 6. Backend dan integritas stok

Quote dan save masih memvalidasi ID Mobil, warna yang sama dengan row inventori, status, harga server, membership/authorization dan stock availability. Gallery-only color tidak dapat dikirim sebagai warna baru pada unit Guards Red. Reservasi tetap melalui `MemberVehiclePurchaseService` dan `VehicleInventoryService`, dengan locking dan duplicate-order protections existing. Tidak mempercayai stock frontend atau mengganti harga/diskon.

Perubahan tidak menyentuh manual discount approval, promo/quota, invoice, payment callback, eligibility member atau ownership creation. Metadata proposal diskon dan batas minimum/margin yang belum ditetapkan dari tugas sebelumnya tetap berlaku. Tidak menerapkan migration SPK sebelumnya ke database operasional.

## 7. File yang diubah

| File | Perubahan tugas ini |
|---|---|
| `app/Services/SmartSpkOrderService.php` | Metadata model/color/identity, katalog database terpisah dari stock, batch deduplication |
| `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php` | Menyediakan katalog warna lengkap untuk modal |
| `resources/views/admin/pesanan-mobil/_create-modal.blade.php` | Model → varian → warna → unit, label ketersediaan/Pre-Order yang jujur |
| `public/assets/js/smart-spk-modal.js` | Seleksi katalog/varian/warna, preview tanpa stok, reset unit/quote, update stok/identitas |
| `public/assets/css/order-sales-refinement.css` | Swatch unavailable dan wrapping |
| `app/Services/VehiclePdiIntegrityService.php` | Guard identitas lengkap dan VIN/engine claimed |
| `app/Services/MemberVehiclePurchaseService.php` | Memanggil guard identitas sebelum PDI lulus |
| `tests/Feature/VehicleColorInventorySpkTest.php` | Sembilan skenario katalog/stock/identity/backend/PDI |
| `tests/Ui/smart-spk.test.cjs` | Warna kosong/foto, variant switch dan regresi wizard |

Tidak membuat migration baru pada tugas ini. Tidak ada data warna/stok/VIN master ditambahkan pada database operasional. Data sintetis baru hanya fixture transaksi testing dan di-rollback.

## 8. Pengujian

- Focused warna/inventory/SPK, race dua proses untuk unit fisik, PDI P0 dan member purchase: **68 tests / 352 assertions lulus** (`vehicle-color-focused.txt`).
- Client wizard/picker/sales/notifikasi: **26 tests lulus** (`vehicle-color-client.txt`).
- Frontend build **lulus**, Vite 58 modules (`vehicle-color-build.txt`). PHP/JavaScript syntax dan `git diff --check` lulus.
- Full regression final: **679 tests / 3.981 assertions, seluruhnya lulus**, 145,206 detik (`vehicle-color-regression.txt`/`.xml`).

Run penuh sebelumnya mencatat satu failure pada `SuperAdminMenuAuditTest::test_every_role_visible_navigation_has_an_authorized_destination_and_correct_role_label`: request update pengaturan oleh Owner mendapat 404, sementara assertion mengharapkan 403. Case dan class tersebut kemudian lulus tersendiri (5 tests / 500 assertions) tanpa perubahan assertion/authorization. Full regression terakhir di atas juga lulus. Mismatch awal tidak direproduksi pada run akhir; akar intermitensinya belum dipastikan. Tidak mengubah modul pengaturan, route/permission Owner, atau menyebut hasil awal sebagai kelulusan.

Focused cases mencakup satu/multi warna, warna katalog tanpa stok dan foto parsial, dua unit fisik warna sama, warna/varian berbeda, batch sama tidak dihitung dua kali, reserved/sold/exhausted, warna palsu pada quote, SPK tidak membuat Pre-Order, VIN/engine tidak lengkap, dua aggregate orders tidak dapat memakai satu identitas PDI lulus, serta rollback completePdi tanpa ownership. Concurrency memakai dua proses dan koneksi MySQL nyata dari test SPK existing. Database testing dipaksa ke `yza_automotive_laravel_test`.

Verifikasi visual live di browser belum dilakukan karena pembatasan akses browser sebelumnya. Harness DOM menguji handler/state, bukan geometri/pointer native. Tidak menyajikan screenshot mockup sebagai bukti halaman operasional.

## 9. Kekurangan data dan rencana aman

1. Empat warna Porsche lainnya mempunyai katalog/foto, tetapi tidak mempunyai stok eligible. Agar menjadi tersedia, dealer harus mencatat stok fisik yang benar melalui prosedur inventori resmi dengan bukti kendaraan; tidak menyalin angka 2 ke setiap warna.
2. Batch PREM-03 belum mempunyai registri dua unit/VIN terpisah. Jangan menggandakan master VIN atau mengubah master yang sudah terikat order/PDI untuk memuaskan pesanan kedua. Dibutuhkan data VIN/engine unik dan prosedur alokasi per order yang ditinjau sebelum finalisasi unit tambahan.
3. Jika kelak diperlukan registry/alokasi fisik eksplisit untuk seluruh stok agregat, siapkan migration additive dan mapping order→physical unit yang terverifikasi, uji pada database terisolasi, serta rekonsiliasi data lama tanpa overwriting snapshots/ownership. Tidak membuat atau menerapkan registry baru berdasarkan jumlah agregat saja. Test fixtures pada tugas ini sudah membuktikan kasus multi-VIN nyata, stok agregat dan guard alokasi ulang dengan struktur existing.
4. Pre-order staf per warna memerlukan kontrak fitur yang belum ada; tidak diberi tombol mutasi palsu.

Tidak ada commit, push, deployment, reset/clean Git, perubahan database operasional, fake VIN, atau perubahan harga/payment/promo.
