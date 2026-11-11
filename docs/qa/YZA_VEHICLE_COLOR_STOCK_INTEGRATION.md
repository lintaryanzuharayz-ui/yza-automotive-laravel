# YZA Automotive — Integrasi Stok Kendaraan per Warna

Tanggal: 10 November 2026. Ruang lingkup: `/admin/mobil`, katalog/detail Member, detail publik, checkout/SPK, reservasi dan Pre-Order.

## Status pelaksanaan

Implementasi selesai di workspace. **Regresi penuh lulus: 775 tests / 4.660 assertions**. Tidak ada commit, push, deployment, penerapan migration operasional, atau pembuatan unit pada database operasional.

## Audit awal dan kompatibilitas data lama

Audit skema, model, relasi dan alur existing menemukan pencatatan **campuran**:

- `mobil` menyimpan identitas unit, warna fisik, spesifikasi dan status. Nomor rangka dan nomor mesin memiliki constraint unik.
- `vehicle_inventories` menyimpan angka agregat berdasarkan key spesifikasi yang **sudah memasukkan warna**. Angka tersebut sebelumnya dapat membuka beberapa pesanan pada satu `id_mobil`/VIN representatif.
- `mobil_warna` dan `mobil_galeri` adalah master pilihan warna/foto. Keduanya bukan bukti adanya unit fisik. Skema existing tidak mempunyai flag aktif/nonaktif warna; seluruh master yang masih terdaftar diperlakukan sebagai pilihan aktif.
- Booking, pesanan Member/SPK, penjualan/transaksi dan PDI telah mempunyai relasi ke unit. Existing PDI melindungi penggunaan VIN/mesin yang sudah diklaim inspeksi/penyerahan lain.
- Checkout Member sebelumnya menerima warna galeri yang berbeda dari warna fisik row yang direservasi. Pre-Order sebelumnya unik per member + produk sehingga belum mendukung beberapa warna pada produk yang sama.

Audit database operasional memakai `SET TRANSACTION READ ONLY`, kemudian rollback. Snapshot yang dibaca: **26 row mobil**, **26 row dengan kolom VIN/mesin terisi**, **44 master warna**, **12 row inventory agregat**, **69 stock_total agregat**, dan **2 pesanan aktif**. Bukti: [vehicle-color-stock-readonly.json](vehicle-color-stock-readonly.json).

Angka 69 **tidak membuktikan 69 VIN/unit fisik berbeda**. Jumlah row beridentitas terisi juga bukan sertifikasi keaslian dokumen/VIN. Tidak ada penggandaan VIN, pengisian unit dari selisih angka, atau penyalinan stok ke seluruh warna katalog. Contoh angka Lexus pada permintaan pengguna tidak dijadikan seed atau hardcode. Pemeriksaan baca saja sesudah pekerjaan menghasilkan angka snapshot yang sama: [vehicle-color-stock-readonly-after.json](vehicle-color-stock-readonly-after.json).

## Sumber stok authoritative

`VehicleColorStockService` menjadi sumber bersama; `VehicleAvailabilityService` mendelegasikan perhitungan stok kepadanya. Pembacaan Admin, Member, SPK dan pemeriksaan kecocokan Pre-Order menggunakan hasil yang sama.

1. Kelompok kendaraan mengikuti relasi katalog existing (`catalog_identity`/`showcase_slug`) dan spesifikasi yang cocok: merek, model, varian, tahun, transmisi, bahan bakar, kondisi dan limited edition. Unit dari identitas katalog eksplisit lain dikecualikan. Fallback kelompok legacy existing tetap menghitung row kendaraan nyata, bukan menggandakan angka inventory.
2. Warna stok berasal dari `mobil.warna`, dicocokkan tanpa membedakan kapital dan spasi tepi.
3. Unit harus mempunyai nomor rangka dan nomor mesin terisi serta tidak bertabrakan dengan row lain. Unit nonaktif, telah diserahkan, atau sudah mempunyai penjualan selesai/lunas tidak menjadi stok siap jual, termasuk jika flag status legacy masih keliru `tersedia`.
4. Stok fisik mencakup unit teridentifikasi yang masih ada, termasuk unit dalam pemeriksaan. Unit dalam pemeriksaan ditahan dari stok siap jual.
5. Ready dihitung dari unit berstatus `tersedia` yang eligible, dikurangi unit yang terikat reservasi aktif. Flag `dibooking` tanpa pelepasan resmi juga tidak dijadikan ready.
6. Reservasi aktif berasal dari booking `menunggu`/`dikonfirmasi`, pesanan selain `ditolak`/`dibatalkan`/`diserahkan`, dan penjualan `proses` dengan transaksi belum dibatalkan. Deduplication berdasarkan `id_mobil`: booking + SPK/penjualan yang mengikat satu unit tidak mengurangi stok beberapa kali.
7. Angka agregat lama dipertahankan sebagai catatan historis. Selisih yang belum didukung row unit ditampilkan sebagai **stok agregat lama belum terverifikasi**, dan tidak dijual.

Tidak ditambahkan tabel unit fisik kedua karena `mobil` existing telah menyediakan identitas unik dan seluruh relasi transaksi/PDI. Ini menghindari migrasi/alokasi unit buatan dari angka agregat.

## Admin

- Popup **Galeri Foto & Master Warna** dan **Manajemen Stok Kendaraan** dipertahankan. Keduanya menampilkan kartu per warna: nama/swatch, fisik, reservasi, ready, status tersedia/habis, serta informasi selisih legacy.
- Pembukaan popup stok/galeri/riwayat unit memuat ulang data authoritative dari server. Riwayat mencakup inventory warna terkait dan referensi unit, termasuk setelah perubahan warna.
- Penambahan dilakukan lewat popup **Tambah Unit Fisik**, dengan kode unit, VIN dan nomor mesin unik. Default awal menjadi **Dalam Pemeriksaan**; Admin dapat menyatakan tersedia setelah verifikasi.
- Penambahan, edit dan penonaktifan mewajibkan **alasan** dan **bukti berupa nomor/referensi dokumen verifikasi**. Bukti ini bukan fitur upload dokumen baru. Audit disimpan pada `vehicle_inventory_histories`: aktor, waktu, referensi unit, alasan, bukti dan snapshot sebelum/sesudah.
- Perubahan warna, identitas, spesifikasi dan status unit yang terjual/terikat transaksi ditolak. Status `dibooking`/`terjual` tidak dapat dibuat melalui edit stok biasa; status tersebut mengikuti transaksi resmi.
- Penyesuaian angka hanya untuk rekonsiliasi dengan unit yang memang tercatat. Form menampilkan jumlah unit nyata; penambahan dilakukan dengan mendaftarkan unit, pengurangan melalui status unit yang tidak terikat transaksi. Backend menolak angka bebas yang akan menciptakan stok tanpa identitas serta angka di bawah penjualan/reservasi terlindungi.
- Registrasi unit dan pencatatan penjualan memperoleh pengaman replay melalui referensi histori. Satu pencatatan penjualan hanya boleh mewakili satu unit fisik.
- Pengisian counter ready secara optimistis `+1` di browser dihapus: sesudah registrasi, halaman mengambil kembali data database. Unit baru yang masih diperiksa tidak ditampilkan seolah langsung ready.

## Member, checkout dan Pre-Order

- Katalog Member memperlihatkan pilihan warna dan jumlah ready/label habis. Detail memperlihatkan semua master warna meskipun foto belum lengkap dan stok nol; swatch kosong diberi tanda ×.
- Seleksi warna memperbarui label stok, badge status, pilihan warna dalam formulir dan CTA. Ready membuka **Lanjutkan Pembelian** untuk ID unit fisik yang cocok. Warna kosong membuka **Ajukan Pre-Order**.
- Checkout hanya menampilkan warna unit yang benar-benar dipilih. Pilihan warna palsu ditolak; warna katalog lain tidak boleh disimpan sebagai snapshot unit yang berbeda warna. Alias warna basic legacy hanya diterima jika pemetaannya tidak ambigu dan tetap cocok dengan warna fisik unit.
- Stok/warna diperiksa ulang server-side di dalam transaksi dengan lock row unit. Lock membership/akun dan validasi member aktif existing dipertahankan. Permintaan bersamaan tidak bisa memesan satu unit dua kali.
- Penolakan/pembatalan menghilangkan ikatan aktif yang tepat. Pelepasan status oleh service pesanan memeriksa ikatan lain. Penjualan langsung dan konfirmasi booking mengikuti pengaman unit fisik juga; pembayaran dan handover melindungi penggunaan identitas yang sudah terjual.
- Pre-Order menyimpan produk/model, varian, tahun, warna canonical terpilih dan pelanggan pemiliknya. Pre-Order warna kosong tetap diperbolehkan ketika warna lain ready. Pengajuan tersebut tidak membuat unit, SPK, invoice, pembayaran, atau reservasi.
- Notifikasi memakai outbox existing. Kecocokan produk, varian, tahun, warna, status siap jual, reservasi dan eligibility member diperiksa ulang setelah commit. Pengiriman deduplicated per siklus ketersediaan; replay event tidak membuat notifikasi tambahan. Tidak ada transaksi/alokasi unit otomatis.
- Foto hanya berganti jika tersedia aset model/warna yang cocok. Aset konfigurasi yang merupakan fallback warna lain tidak dianggap foto warna terverifikasi. Jika foto warna terpilih tidak tersedia, foto sebelumnya dipertahankan; jika sejak awal tidak ada foto yang cocok, digunakan placeholder.
- Kartu warna dan popup Pre-Order memakai putih/navy dengan aksen merah. Layout halaman Member lain dan alur transaksi tetap mengikuti struktur existing.

## Migration

Migration baru: `2026_11_10_100000_scope_member_preorders_to_color.php`.

- Mengganti unique key Pre-Order `(id_pelanggan, product_key)` menjadi `(id_pelanggan, product_key, warna)`.
- Tidak menambah atau mengubah row kendaraan, warna, stok, snapshot transaksi, VIN, invoice atau ownership.
- Sudah diterapkan **hanya** pada `yza_automotive_laravel_test` lewat runner yang memverifikasi nama database sebelum migrasi. `phpunit.xml` dan `Tests\TestCase` juga memaksa/memeriksa database testing.
- **Belum diterapkan pada database operasional.** Sebelum migration tersedia, percobaan mendaftarkan warna kedua pada produk yang sama menghasilkan pesan migration diperlukan, bukan mengarang unit atau menimpa permintaan sebelumnya.
- Risiko: DDL MySQL dapat mengunci tabel; gunakan prosedur deployment/backup normal. Rollback diblokir jika sudah ada beberapa warna untuk member + produk yang sama, karena mengembalikan unique key lama akan memerlukan rekonsiliasi manual. Tidak ada penghapusan otomatis untuk membuat rollback berhasil.

## Pengujian dan bukti

Seluruh pengujian backend menggunakan `yza_automotive_laravel_test`; tidak ada migrasi atau fixture pada database operasional.

| Skenario | Hasil | Bukti |
|---|---|---|
| Beberapa warna pada satu model, stok tidak tercampur | PASS | `VehicleColorStockIntegrationTest` |
| Warna kosong sementara warna lain ready → Pre-Order | PASS | Dua warna kosong tersimpan terpisah; jumlah unit/transaksi/invoice tidak bertambah |
| Warna ready → SPK/checkout yang cocok | PASS | ID unit dan snapshot warna fisik; warna salah ditolak |
| Reservasi mengurangi ready warna terkait | PASS | Stok warna lain tidak berubah |
| Penolakan/pembatalan melepaskan reservasi | PASS | Tes integrasi, alur pesanan, booking dan pembayaran |
| Pembelian bersamaan tidak overselling | PASS | `SmartSpkConcurrencyTest`: dua proses/koneksi MySQL dan dua member nyata dalam fixture testing, satu diterima/satu ditolak |
| Stok warna terverifikasi memicu notifikasi tanpa duplikasi | PASS | Unit pending tidak memicu notifikasi; warna cocok ready memicu satu delivery; replay tidak menambah delivery |
| VIN/mesin, unit terikat dan status legacy terlindungi | PASS | Constraint duplikasi, immutable unit terikat, unit sudah diserahkan tidak dapat dijual ulang, proteksi PDI existing |
| Sinkronisasi Admin–Member–SPK | PASS | Pembacaan stok bersama dan endpoint JSON Admin |
| RBAC dan bukti wajib | PASS | Owner 403 untuk mutasi; Admin tanpa bukti 422; histori menyimpan aktor |
| Build frontend | PASS | `vehicle-color-stock-build.txt`, Vite 58 modules |
| Tes antarmuka existing | PASS | `vehicle-color-stock-client.txt`, 29 tests |
| PHP syntax | PASS | 17 file implementasi/migration/tes diperiksa |
| Whitespace/diff check | PASS | `git diff --check` pada file implementasi dan tes terkait |
| Browser desktop/tablet/mobile, zoom CSS 100% | PASS | `vehicle-color-stock-visual.json`: 1366/768/390 px, CTA/status/warna formulir/foto/popup diperiksa |
| Regresi penuh final | PASS | `vehicle-color-stock-regression.txt`/`.xml`: 775 tests / 4.660 assertions, 300,121 detik |

Focused final: **16 tests / 68 assertions PASS**, termasuk 15 tes integrasi stok warna dan tes konkurensi dua proses. Bukti: `vehicle-color-stock-focused.txt`/`.xml`.

Perintah backend utama, dari root project:

```powershell
C:/xampp/php/php.exe vendor/bin/phpunit --filter 'VehicleColorStockIntegrationTest|SmartSpkConcurrencyTest' --no-progress
C:/xampp/php/php.exe vendor/bin/phpunit --no-progress
npm run build
node --test tests/Ui/*.test.cjs
```

Regresi penuh pertama mengungkap 36 error dan 22 failure pada 772 tes, termasuk asumsi fixture lama bahwa satu VIN dapat dipakai beberapa kali, pembelian warna galeri tanpa unit cocok, angka agregat sebagai stok, dan formulir tanpa bukti. Bug tampilan dan kompatibilitas yang ditemukan diperbaiki. Fixture untuk tes presentasi/warna kini menyediakan row unit terpisah dengan identitas unik **hanya dalam testing**; tes PDI/booking duplikasi legacy secara eksplisit membuat data korup di testing agar penolakannya tetap diuji. Proteksi backend tidak dilonggarkan. Bukti run awal dipertahankan pada `vehicle-color-stock-regression-first.txt`/`.xml`.

Run penuh berikutnya lulus **773 tests / 4.654 assertions**. Setelah dua tes perlindungan legacy tambahan dan penyelesaian pemeriksaan kode, regresi final lulus **775 tests / 4.660 assertions**. Penyempurnaan label counter galeri kosong diperiksa ulang melalui render, focused tests dan browser; counter tidak lagi menampilkan `01 / 00`.

Verifikasi browser menggunakan **HTML hasil render Laravel dari database testing** dan Chromium headless. Seluruh request browser diintersep lokal; ini bukan login atau transaksi pada browser/database operasional dan bukan pengujian pembayaran Midtrans live. Screenshot:

- [Popup stok desktop](vehicle-color-stock-admin-desktop.png).
- [Popup stok mobile](vehicle-color-stock-admin-mobile.png).
- [Detail Member mobile](vehicle-color-stock-member-mobile.png).

## File implementasi utama

- Baru: `app/Services/VehicleColorStockService.php`; migration unique warna Pre-Order; komponen `vehicle-color-stock-options.blade.php`; `VehicleColorStockIntegrationTest`; helper fixture `CreatesColorStockUnit`.
- Diperbarui: `VehicleAvailabilityService`, `VehicleInventoryService`, `MemberVehiclePurchaseService`, `MemberVehiclePreorderService`, `SmartSpkOrderService`, `PenjualanMobilService`, `BookingMobilService`, `PaymentService`.
- Controller/request: `Admin/MobilController`, `Pelanggan/MemberVehiclePurchaseController`, `Pelanggan/MemberVehiclePreorderController`, `StoreMobilRequest`, `AdjustVehicleInventoryRequest`.
- Tampilan: popup pada `admin/mobil/index`, form unit, katalog card, detail Member/publik, formulir Pre-Order serta style kartu stok.
- Tes existing yang menyentuh kontrak stok/warna diperbarui agar tetap menguji presentasi, pembayaran, PDI, notifikasi, RBAC dan workflow dengan identitas fisik yang konsisten.

## Risiko dan pekerjaan operasional yang belum dilakukan

1. **Migration operasional belum diterapkan**, sesuai larangan mutasi operasional tanpa izin. Dukungan beberapa warna Pre-Order pada satu produk memerlukan migration tersebut.
2. **Stok agregat lama belum direkonsiliasi dengan VIN/unit nyata.** UI dapat menampilkan ready lebih kecil daripada angka lama. Tambahan stok membutuhkan kendaraan dan identitas asli melalui popup; tidak boleh diisi dari perkiraan atau contoh Lexus.
3. Isi identitas legacy dan bukti dokumennya memerlukan pemeriksaan dealer. Sistem tidak dapat membuktikan keberadaan fisik/keaslian VIN hanya dari kolom terisi. Beberapa pesanan lama yang memakai satu identitas memerlukan alokasi unit nyata sebelum PDI/handover; pengaman duplikasi dipertahankan.
4. Counter historis `sold_units`/`stock_total` tidak dihapus atau diturunkan otomatis. Ketidaksesuaian historis yang menghalangi rekonsiliasi/finalisasi membutuhkan peninjauan, bukan pemaksaan angka atau pengubahan transaksi terikat.
5. Tidak ada uji pembayaran Midtrans live, pengiriman di luar sistem notifikasi aplikasi, atau deployment. Pengujian gateway/promo/PDI/QC dilakukan melalui suite regresi existing.

Tidak ada fitur di luar integrasi stok warna yang ditambahkan. Tidak ada unit operasional, invoice, pembayaran, reservasi, atau ownership buatan.
