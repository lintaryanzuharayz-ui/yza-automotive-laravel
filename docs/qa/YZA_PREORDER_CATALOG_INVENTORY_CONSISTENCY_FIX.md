# Katalog–Inventori–Pre-order: konsistensi identitas kendaraan

Tanggal: 8 November 2026. Project: C:\xampp\htdocs\yza_automotive_laravel.

## Hasil dan batas penerapan

Semua 30 identitas dalam audit dapat diresolusi dan dibuka Admin, termasuk empat model showroom virtual dengan stok 0. Tidak membuat master Mobil, VIN, nomor mesin atau stok untuk sekadar menampilkan model katalog. Kode, pengujian dan pemeriksaan browser selesai; database operasional hanya dibaca.

Migrasi nullable `2026_11_08_200000_add_explicit_catalog_identity_to_mobil.php` dijalankan **hanya pada database testing**. Tidak menjalankan migrasi/backfill operasional, membuat transaksi operasional, reset/clean, commit, push atau deploy. Outbox notifikasi dari tahap sebelumnya juga **belum terpasang pada database operasional**. Karena itu jangan menyebut notifikasi otomatis sudah aktif di lingkungan operasional.

## Root cause berbasis kode dan database

1. Katalog publik/member memiliki empat model sintetis dalam array controller: Audi R8, BMW M4, Bugatti Chiron dan McLaren 720S. Konstruktor `new Mobil(...)` tidak menyimpan row. Bugatti dapat tampil dan membuat pre-order katalog meskipun tidak punya ID mobil fisik.
2. Snapshot operasional membuktikan `PRE-00001` tersimpan dengan `product_key=showcase:bugatti-chiron`, slug bugatti-chiron, id_mobil NULL dan status menunggu_stok. Jumlah record fisik Bugatti: 0. Ini bukan bukti bahwa katalog hilang atau bahwa stok 0 harus dibuat sebagai row kendaraan.
3. Halaman Admin sebelumnya hanya memakai query tabel Mobil untuk ketiga tab. Model virtual tidak pernah masuk daftar. Tautan fallback memakai q nama lengkap sehingga tidak memiliki referensi model terpilih. Pencarian lama juga membandingkan merk/model secara terpisah, sehingga frasa merek + model tidak selalu cocok walaupun unit ada.
4. KPI memakai `Schema::hasTable('member_vehicle_preorders')`, padahal tabel model sebenarnya `pre_order_mobil_member`. Pemeriksaan tabel salah menghasilkan 0. Snapshot operasional: tabel salah tidak ada, tabel benar ada, pre-order aktif global 1.
5. Tiga salinan definisi showcase menyebabkan metadata mudah berbeda. Ditemukan default Audi Ibis White tidak mempunyai galeri warna persis; konfigurasi galeri dan aset aslinya memakai Nardo Silver.

Bukti read-only: `docs/qa/catalog-consistency-operational-audit.json`. Tidak menyimpulkan ada stok fisik hanya karena foto/warna katalog tersedia.

## Sumber authoritative dan pemetaan

Definisi showcase existing dikonsolidasikan dalam `config/vehicle_showcases.php`; harga empat model disalin tanpa perubahan. `CatalogVehicleIdentityService` menjadi resolver yang digunakan Admin, pre-order dan lookup showcase publik/member:

- `showcase:<slug>` untuk model showroom yang terdaftar atau explicit binding showcase_slug pada row nyata.
- `mobil:<primary-key>` untuk kendaraan database biasa.
- `mobil.catalog_identity` opsional untuk menghubungkan beberapa unit/warna fisik dengan satu model canonical, tanpa menduplikasi master katalog.
- Binding fisik existing `showcase_slug` dipakai kembali. Inventori group existing tetap dipertahankan; tidak ada pencocokan identitas hanya dari nama.
- Harga referensi konfigurasi berlaku untuk model virtual; harga row kendaraan yang terkait tetap menjadi harga inventori/checkout existing. Snapshot harga pre-order dan transaksi lama tidak ditulis ulang.

Audit menemukan 26 record Mobil dan 4 model virtual. Resolusi/read Admin tidak mempersistenkan virtual model. Galeri dan foto tetap menggunakan VehicleGalleryService dan sumber existing.

Default katalog Audi sekarang Nardo Silver, sesuai konfigurasi galeri dan aset silver yang diperiksa secara visual. Ini perubahan metadata konfigurasi katalog, bukan perubahan warna pada record operasional atau snapshot pre-order lama. Harga Audi tetap Rp1.850.000.000.

## Model tanpa unit dan alur tambah unit

Tab Katalog & Showroom sekarang membaca gabungan model canonical, termasuk model stok 0. Tab Inventori menampilkan kartu model virtual: Belum Ada Unit Fisik, stok dapat dibeli 0, warna katalog, harga referensi, dan jumlah minat aktif. Row tersebut tidak ikut hitungan unit fisik.

Tombol Tambahkan Unit ke Inventori membuka form kendaraan existing dengan canonical ID dan konteks pre-order. Merk/model/varian/tahun/transmisi/bahan bakar/warna katalog diprefill. Kode unit, VIN dan nomor mesin tetap kosong dan wajib diberikan pengguna. Status awal dalam_pemeriksaan. Stok hanya dicatat melalui service resmi setelah form unit fisik valid.

Tidak menyediakan penyesuaian stok agregat untuk virtual model yang belum memiliki unit. Jika skema relasi baru belum terpasang, unit pertama dapat memakai showcase_slug existing. Tambahan unit dengan spesifikasi/warna berbeda memerlukan kolom relasi explicit agar tidak disambungkan dengan tebakan; jika belum tersedia, backend menolak dengan alasan dan tidak membuat stok.

Validasi menolak spesifikasi unit yang tidak cocok dengan model canonical dan perubahan spesifikasi yang merusak binding katalog existing. VIN/kode unit/nomor mesin tetap unik sesuai aturan existing.

## Deep link dan KPI

Popup memakai URL berbentuk:

`/admin/mobil?catalog=showcase%3Abugatti-chiron&preorder=1`

Tidak memakai nama sebagai identitas. Untuk model yang memiliki unit, stock_mobil PK dapat membuka modal stok existing; tanpa unit, halaman menampilkan model canonical yang benar dan alur penambahan unit. Konteks kembali menggunakan review PK dan membuka popup sebelumnya.

Scope `MemberVehiclePreorder::active()` dipakai untuk KPI global Admin, KPI halaman pre-order dan badge menu: status menunggu_stok/stok_tersedia/notifikasi_terkirim, tanpa id_pesanan. Status dibatalkan dan dilanjutkan_ke_checkout tidak dihitung aktif. Hitungan per-model diberi label per-model dan memakai relasi canonical/PK. Tidak memakai join yang menggandakan hitungan. Counter katalog dan counter unit dibedakan.

Pencarian teks mendukung gabungan merek/model, tetapi deep link memakai key stabil. KPI tersedia dan badge row tidak menyebut Tersedia untuk kendaraan dengan stok eligible 0.

## Stok, notifikasi dan transaksi

Pencocokan restock menggunakan resolver canonical dan unit fisik terkait, lalu memeriksa snapshot varian, tahun, warna serta atribut wajib. Pilihan warna/foto saja tidak menambah kapasitas. Unit dalam pemeriksaan, warna/model salah, reservasi aktif dan stok tak eligible tetap diblokir.

Outbox afterCommit dan idempotency dari tahap sebelumnya dipertahankan. Setelah unit benar-benar eligible, pemilik pre-order yang aktif menerima notifikasi yang cocok; retry tidak menggandakan pesan. Notifikasi tidak membuat SPK/invoice/pembayaran/ownership atau reservasi otomatis. Backend checkout, membership, harga final, promo, PDI dan handover tidak dilemahkan.

Operasional masih memerlukan penerapan outbox/migrasi dan scheduler yang diotorisasi. Tidak mengaktifkannya secara diam-diam.

## Audit seluruh kendaraan

Snapshot operasional read-only; warna adalah metadata tersimpan, bukan bukti bahwa semua warna katalog mempunyai unit. Kolom record fisik tidak sama dengan jumlah stok agregat. Semua ID di bawah memiliki deep link canonical yang tersimpan dalam JSON audit.

| Canonical ID | Kendaraan / varian / tahun | Warna katalog | Record fisik | Stok eligible | Pre-order aktif | Galeri warna persis | Admin |
|---|---|---|---:|---:|---:|---|---|
| mobil:1 | Toyota Avanza · 1.5 G CVT · 2026 | Graphite Silver | 1 | 0 | 0 | Perlu verifikasi | Ditemukan |
| mobil:5 | Toyota Yaris · 1.5 S · 2022 | Putih | 1 | 0 | 0 | Ada | Ditemukan |
| mobil:6 | Honda Mobilio · 1.5 E CVT · 2022 | Deep Metallic Blue | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:7 | Honda Brio · Satya · 2024 | Putih | 1 | 0 | 0 | Perlu verifikasi | Ditemukan |
| mobil:8 | Daihatsu Xenia · 1.5 R CVT · 2022 | Silver Metallic | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:9 | Toyota Avanza · 1.5 G CVT · 2026 | White Pearl | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:10 | Toyota Innova Zenix · 2.0 G CVT · 2026 | Silver Metallic | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:11 | Honda Brio · RS CVT · 2025 | Phoenix Orange Pearl | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:12 | Honda HR-V · SE CVT · 2025 | Platinum White Pearl | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:13 | Mitsubishi Xpander · Ultimate CVT · 2026 | Graphite Gray | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:14 | Suzuki XL7 · Alpha AT · 2025 | Brave Khaki | 1 | 1 | 0 | Perlu verifikasi | Ditemukan |
| mobil:15 | Toyota Fortuner · 2.4 VRZ AT · 2023 | Attitude Black | 1 | 1 | 0 | Ada | Ditemukan |
| mobil:16 | Honda Civic · RS · 2022 | Ignite Red Metallic | 1 | 1 | 0 | Perlu verifikasi | Ditemukan |
| mobil:17 | Toyota Raize · 1.0 Turbo GR Sport · 2024 | Turquoise Blue | 1 | 1 | 0 | Perlu verifikasi | Ditemukan |
| mobil:195 | Toyota GR Supra · Performance · 2024 | Supra Red | 1 | 4 | 0 | Ada | Ditemukan |
| mobil:204 | Toyota Supra MK4 · 2JZ-GTE Turbo · 1998 | Supra Red | 1 | 3 | 0 | Ada | Ditemukan |
| mobil:250 | Lamborghini Revuelto · V12 Hybrid Performance · 2025 | Rosso Mars | 1 | 8 | 0 | Ada | Ditemukan |
| mobil:251 | Ferrari SF90 Stradale · Assetto Fiorano · 2024 | Rosso Corsa | 1 | 6 | 0 | Ada | Ditemukan |
| mobil:252 | Porsche 911 GT3 RS · 4.0 Performance · 2024 | Guards Red | 1 | 2 | 0 | Ada | Ditemukan |
| mobil:253 | Mercedes Amg GT Black Series · AMG Performance · 2024 | Magno Grey | 1 | 4 | 0 | Ada | Ditemukan |
| mobil:254 | Aston Martin DBS 770 Ultimate · V12 Ultimate · 2024 | Minotaur Green | 1 | 7 | 0 | Ada | Ditemukan |
| mobil:255 | Chevrolet Corvette Z06 · 6.2L V8 Performance · 2024 | Torch Red | 1 | 5 | 0 | Ada | Ditemukan |
| mobil:256 | Ford Mustang Shelby GT500 · 5.2L V8 Shelby · 2024 | Grabber Blue | 1 | 9 | 0 | Ada | Ditemukan |
| mobil:257 | Dodge Challenger SRT Hellcat Redeye · 6.2L Supercharged V8 · 2023 | TorRed | 1 | 7 | 0 | Ada | Ditemukan |
| mobil:258 | Nissan GT-R Nismo · Nismo Performance · 2024 | Nismo Stealth Gray | 1 | 6 | 0 | Ada | Ditemukan |
| mobil:259 | Lexus LFA · 4.8L V10 Performance · 2012 | Pearl White | 1 | 5 | 0 | Ada | Ditemukan |
| showcase:audi-r8 | Audi R8 · Performance · 2024 | Nardo Silver | 0 | 0 | 0 | Ada | Ditemukan |
| showcase:bmw-m4 | BMW M4 · Competition · 2024 | Toronto Red | 0 | 0 | 0 | Ada | Ditemukan |
| showcase:bugatti-chiron | Bugatti Chiron · Super Sport · 2024 | Atlantic Blue | 0 | 0 | 1 | Ada | Ditemukan |
| showcase:mclaren-720s | McLaren 720S · Performance · 2024 | Papaya Spark | 0 | 0 | 0 | Ada | Ditemukan |

### Temuan galeri legacy

Lima warna pada record existing belum mempunyai kecocokan label galeri yang persis: Toyota Avanza / Graphite Silver, Honda Brio / Putih, Suzuki XL7 / Brave Khaki, Honda Civic / Ignite Red Metallic, Toyota Raize / Turquoise Blue. Record tetap dapat ditelusuri Admin; ini bukan kegagalan identitas katalog. Tidak menyatakan galeri warna tersebut PASS, mengganti warna snapshot, atau memetakan otomatis ke warna lain. Foto unit/label perlu verifikasi dan perubahan data terpisah yang diotorisasi. Resolver foto pre-order tetap memakai placeholder apabila tidak ada foto warna persis.

## Pengujian dan browser

- Integrasi domain awal: 42 PASS / 300 assertions.
- Domain setelah perbaikan navigasi: 44 PASS / 317 assertions (`storage/logs/catalog-consistency-domain-final.txt`).
- Focused akhir katalog + navigasi: 14 PASS / 158 assertions (`storage/logs/catalog-consistency-final-focused.txt`).
- Regresi awal menemukan label Lihat Detail hilang akibat perpindahan action katalog ke deep link. Action tersebut dipulihkan menggunakan modal existing. Run lama dihentikan saat mencetak diff HTML kegagalan; tidak dilaporkan sebagai PASS.
- Regresi penuh final: **743 PASS / 4.410 assertions; 0 errors, failures dan skipped**, lihat `storage/logs/catalog-consistency-regression-final.txt` dan XML terkait.
- JavaScript: 29 PASS. Frontend build final PASS (5,27 detik), log `storage/logs/catalog-consistency-build-final.txt`. PHP lint, Blade compile dan scoped git diff --check PASS.
- Browser pada HTML hasil render controller nyata dari transaksi testing rollback, pengiriman operasional diblokir: Tinjau → stok → canonical model Bugatti stok 0 berhasil; tombol tambah unit membuka form terisi model dengan VIN/kode/mesin kosong; tab Katalog menampilkan model stok 0; desktop dan viewport 393×852 diperiksa. Ini bukan klaim visual berdasarkan HTTP saja.
- Bukti visual: `catalog-consistency-no-unit-desktop.png`, `catalog-consistency-no-unit-mobile.png`, `catalog-consistency-unit-form.png`.
- Halaman HTML QA sementara dihapus setelah pemeriksaan.

## Risiko, blocker dan perubahan yang memerlukan persetujuan

1. Tidak ada backfill operasional. Migrasi katalog nullable dan outbox sebelumnya hanya diuji di database testing; penerapan operasional dan scheduler masih memerlukan otorisasi terpisah.
2. Tidak membuat row fisik untuk empat model virtual. Unit baru hanya sah jika pengguna memasukkan data fisik asli melalui workflow existing.
3. Lima label galeri legacy memerlukan verifikasi aset/unit; tidak ditebak atau dinormalisasi pada database operasional.
4. Tidak menambah FIFO/alokasi eksklusif, mengubah membership, harga transaksi, atau histori. Seluruh pre-order pada snapshot audit berhasil diresolusi; record PRE-00001 tetap utuh.
