# YZA Automotive — Smart SPK Order Modal

Tanggal: 8 November 2026. Target: `/admin/pesanan-mobil`.

## 1. Audit dan keputusan bisnis

Acuan: modul dan layanan Pesanan Mobil/Penjualan Mobil, inventory/availability/gallery, membership eligibility yang sudah diperbaiki, `CAR_SALES_STAGE_7A/7B`, dokumen promo Stage 13C/13E, dan laporan P0 Stage 1–4. Tidak ditemukan proposal Laravel 12 tersendiri yang lebih baru dalam project.

Istilah SPK pada `PromoSimulationService`/dokumen promo berarti sistem pendukung keputusan campaign; bukan Surat Pemesanan Kendaraan. Tidak memakai record simulasi promo sebagai pesanan baru. Surat pemesanan memakai **PesananMobilMember** existing dengan nomor `nomor_pesanan`/prefix ORD yang dibuat server setelah save berhasil, relasi customer/car/sale/transaction existing, dan status pemesanan existing.

Kebijakan yang dikonfirmasi pengguna pada tugas ini:

- Admin/Sales berwenang boleh mengajukan diskon persen atau nominal.
- Setiap nilai diskon di atas nol wajib alasan dan persetujuan Super Admin independen; pengaju tidak boleh meninjau pengajuannya sendiri.
- Owner read-only. Tidak menambah permission/role baru. Alias Admin legacy tetap mengikuti `roleKey()` P0 yang memetakannya ke Super Admin; aturan independensi tetap berlaku.
- Belum ada kebijakan harga minimum/margin perusahaan. Karena itu **seluruh pengajuan nonzero tetap pending dan tidak dapat disahkan**. Tidak mengarang batas 5/10/30% sebagai kewenangan persetujuan.
- Boleh menyimpan draft SPK, tetapi diskon draft tidak menjadi payable/final invoice. Manual discount tidak ditumpuk dengan promo Member.

## 2. Integrasi pesanan, harga, invoice dan reservasi

`MemberVehiclePurchaseService::createForStaff` tetap menjadi entry point authoritative, lalu mendelegasikan penciptaan pesanan kepada `create` existing. Validasi member aktif, customer/user binding, authorization, duplicate order, car locking, inventori dan reservasi dipertahankan. Tidak membuat master customer atau sistem transaksi paralel.

Draft diskon memakai metadata tambahan pada row pesanan yang sama: jenis/nilai/nominal diskon, status proposal, alasan, pengaju, reviewer/waktu dan history JSON. **`snapshot_harga` dan `total_tagihan` tetap harga dasar asli**, bukan harga diskon pending. Estimasi sesudah diskon dihitung terpisah. Nomor invoice/sale/payment tidak dibuat saat modal disimpan.

`approve` pesanan kini menolak proposal nonzero sebelum membuat transaksi/sale. Dengan demikian endpoint lama tidak bisa melewati diskon pending/rejected. Setelah pengaju menarik/mengubah diskon menjadi nol, verifikasi pesanan penuh tetap mengikuti konfirmasi dan workflow existing. Penarikan bukan persetujuan diskon.

Stok tetap dicadangkan menurut mekanisme pembuatan order existing. Tidak menambah reservasi kedua, TTL, stok terjual, payment otomatis, PDI PASS, pickup, handover atau ownership. Penolakan proposal diskon sendiri tidak membatalkan reservasi/order secara diam-diam; pembatalan pesanan tetap memakai alur existing.

## 3. Modal tiga langkah

Nama modal: **Tambah Pesanan Mobil — SPK**. Desktop maksimum 940px, maksimum tinggi 90dvh, header/stepper/footer tetap terjangkau, scroll internal, satu kolom di layar kecil, tanpa zoom/scale.

1. Pelanggan & kendaraan: searchable member picker existing, data pembeli/kontak, pilihan model/varian, actual inventory row, swatch warna, foto, kode, stok dan identitas fisik.
2. Harga & diskon: tipe none/persentase/nominal, nilai sampai dua desimal, alasan wajib untuk nonzero, ringkasan harga dasar/potongan/estimasi dan status approval.
3. Konfirmasi: member, pembeli/kontak, unit/warna/VIN, harga dan proposal, consent pelanggan, lalu simpan pesanan atau simpan draft SPK.

Back/Next tidak mengganti halaman atau menghapus data. Validasi langkah, penanganan invalid field, Enter, member picker keyboard dan native dialog focus trap dipertahankan. Quote lama dibatalkan/diabaikan setelah input berubah, dan kegagalan quote memblokir save. Harga/stock/nominal tetap diperiksa backend saat save.

## 4. Warna, foto dan VIN

`SmartSpkOrderService::vehicles` membentuk pilihan dari row Mobil berstatus tersedia dengan availability authoritative. Group model/varian menggunakan merk/model/tipe/tahun/transmisi/bahan bakar/kondisi/limited edition, lalu swatch berasal dari **warna row inventori yang benar-benar tersedia**. Mengubah swatch memilih ID unit warna tersebut; tidak mengubah kolom warna/VIN unit.

Unit fisik menampilkan VIN sebenarnya. Inventori agregat menampilkan kode katalog dan jumlah tersedia, tetapi VIN dibiarkan belum dialokasikan—VIN master katalog tidak dianggap VIN fisik yang telah dipesan. Warna galeri saja tidak dijadikan stok. Tersedia link baca ke menu Pre-Order untuk warna/unit yang belum tersedia, tanpa membuat pre-order otomatis.

Preview memakai `VehicleGalleryService::forColor` untuk warna unit yang tepat. Jika tidak ada aset cocok, gunakan placeholder netral; foto kendaraan lain/generic hero tidak dipakai sebagai bukti unit. Gallery, availability dan pricing tidak mengubah database.

## 5. Kalkulasi server dan batas approval

GET `admin.pesanan-mobil.quote` mengambil harga dari Mobil dan memvalidasi actual unit/color/availability. Input harga/total browser tidak menjadi sumber finansial. Perhitungan memakai integer cents untuk DECIMAL(15,2), persentase sampai dua desimal, dan half-up ke satu cent. Rumus dibagi quotient/remainder agar perkalian harga besar dan basis points tidak overflow integer 64-bit. Frontend hanya menampilkan quote server.

Nilai negatif, notasi eksponen, lebih dua desimal, persen di atas 100, potongan melebihi harga dan tipe none dengan nilai nonzero ditolak. Perhitungan 5/10/30% merupakan simulasi yang sah secara aritmetika, **bukan persetujuan bisnis**. Semua nonzero pending, termasuk potongan kecil yang dibulatkan menjadi nol cent. Nominal Rupiah mengikuti format uang existing.

Save mengunci ulang unit dan menghitung ulang harga. `expected_base_price` menjadi pendeteksi quote kedaluwarsa; perubahan harga server meminta pengguna memeriksa ulang, bukan diam-diam mengikat nominal lama. Nomor pesanan, total tagihan, nominal potongan, status approval dan promo dari request tidak boleh ditentukan client.

POST `admin.pesanan-mobil.discount` mendukung:

- Revisi/penarikan oleh pengaju sendiri pada order menunggu verifikasi tanpa invoice; history mencatat nominal/status lama dan baru, alasan, aktor/role/waktu. Harga dasar snapshot tidak diganti.
- Penolakan beralasan oleh Super Admin independen; mencatat reviewer dan waktu.
- Permintaan approve diperiksa role dan independensi, lalu **tetap diblokir karena kebijakan harga minimum/margin belum tersedia**. Percobaan self-review/approval terblokir dicatat melalui audit existing. Tidak ada approval override dari UI/request.

History proposal pada row pesanan berada dalam transaksi yang sama. `MasterAuditService` juga mencatat submission/revision/rejection dan percobaan review/approval terblokir. Tidak mengubah payment callbacks atau memasukkan proposal ke `PromoEngineService`/quota ledger. P0 exclusivity manual discount vs campaign promo pada penjualan tetap dipertahankan.

## 6. Schema dan database operasional

Migration additive: `database/migrations/2026_11_08_120000_add_manual_discount_proposal_to_member_orders.php`. Hanya menambah metadata proposal ke tabel pesanan existing; tidak backfill/mengubah harga, customer, membership, payment, inventory atau histori lama.

**Migration hanya dijalankan pada `yza_automotive_laravel_test`**, setelah pemeriksaan nama database eksplisit dan konfigurasi phpunit. Database operasional tidak dimigrasikan sesuai batasan pengguna. Pada schema yang belum diperbarui, kalkulator tetap dapat melakukan simulasi, pesanan nol diskon tetap memakai kontrak existing, sedangkan save draft nonzero ditahan dengan penjelasan yang jelas dan rollback backend. Pengaktifan persistensi draft pada database operasional masih membutuhkan penerapan migration secara terpisah; itu tidak dilakukan pada tugas ini.

## 7. Daftar file perubahan tugas ini

| File | Tujuan |
|---|---|
| `app/Services/SmartSpkOrderService.php` | DTO inventory/color, exact quote, proposal/history/review |
| `app/Services/MemberVehiclePurchaseService.php` | Integrasi draft dan guard invoice, recheck warna/harga/promo |
| `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php` | DTO/quote endpoint, validasi, revise/review, feedback draft |
| `app/Models/PesananMobilMember.php` | Cast metadata diskon/history |
| `routes/web.php` | Quote dan discount actions di modul pesanan existing |
| Migration additive di atas | Metadata proposal pada tabel existing |
| `resources/views/admin/pesanan-mobil/_create-modal.blade.php` | Wizard tiga langkah |
| `resources/views/admin/pesanan-mobil/_discount-review.blade.php` | Tampilan pending/rejected dan revisi/reject |
| `resources/views/admin/pesanan-mobil/index.blade.php` | Integrasi review di body scroll dan asset wizard |
| `public/assets/js/smart-spk-modal.js` | State langkah, actual unit/color, quote server, confirmation |
| `public/assets/js/order-create-modal.js` | Email autofill dan delegasi submit ke wizard |
| `public/assets/css/order-sales-refinement.css` | Layout modal/stepper/swatch/summary/review responsif |
| `resources/views/pelanggan/pesanan-mobil/show.blade.php` | Menyelaraskan dua heading pembeli/pembayaran dengan fungsi section dan kontrak test existing |
| `tests/Feature/SmartSpkOrderTest.php` | Kalkulasi, draft, authorization, mismatch, approval dan rollback |
| `tests/Feature/SmartSpkConcurrencyTest.php` | Dua proses/koneksi bersaing untuk unit fisik yang sama |
| `tests/Support/spk_concurrent_worker.php` | Worker khusus CLI/testing DB, tanpa akses operasional |
| `tests/Ui/smart-spk.test.cjs` | Interaksi wizard/warna/quote/navigasi/save |

Tidak mengurangi assertion test existing. Sebelumnya detail member memakai heading “Data Pembeli” dan “Ringkasan Pesanan”; test mengharapkan “Informasi Pembeli” dan “Ringkasan Pembayaran”. Isi kedua section memang pembeli dan pembayaran, sehingga heading diperbaiki; tidak menambahkan teks tersembunyi untuk memuaskan assertion. Galeri dan tindakan invoice existing tetap ada.

## 8. Hasil pengujian

Pengujian seluruhnya memakai database testing terisolasi; concurrency memakai dua proses/koneksi nyata dan cleanup fixture khusus testing. Tidak ada callback provider nyata, notifikasi keluar atau perubahan database operasional.

- Smart SPK + member purchase focused awal: 33 tests / 213 assertions lulus.
- Interaksi wizard, picker, sales dan regresi notifikasi: **24 tests lulus** (`smart-spk-client.txt`).
- Frontend build: **lulus**, Vite/58 modules (`smart-spk-build.txt`). PHP/JavaScript syntax checks dan `git diff --check` lulus.
- Full regression final: **665 tests / 3.442 assertions, seluruhnya lulus**, 123,687 detik (`smart-spk-regression.txt` dan `smart-spk-regression.xml`). Mencakup Smart SPK, race unit fisik, workflow order/sales, member, authorization, payment, promo, inventory dan PDI. Tidak ada assertion existing dikurangi.
- Pengujian gabungan awal sempat mendapat deadlock SQL pada fixture aktivasi/penjualan lama. Bukti InnoDB menunjukkan transaksi bersaing pada lock mobil dan insert intention indeks penjualan. Kasus order/sales lulus tersendiri (13 tests / 101 assertions), tanpa perubahan assertion atau payment engine. Full regression terakhir di atas juga lulus, sehingga kegagalan sementara tidak direproduksi pada run akhir. Penyebab SQL contention teridentifikasi, tetapi asal setiap koneksi yang bersaing tidak dipastikan; tidak menuduh perubahan bisnis atau proses eksternal tanpa bukti.

Visual browser langsung pada zoom 100%/viewport mobile-desktop belum diverifikasi karena pembatasan browser sebelumnya. Harness DOM menguji handler/state, bukan geometri, animasi, scrolling atau focus containment native. Source memakai batas 90dvh, layout responsif, native dialog dan overflow internal; tidak ada screenshot mockup yang diklaim sebagai halaman operasional.

## 9. Keputusan yang masih diperlukan

1. Kebijakan harga minimum/margin perusahaan dan cara otoritatif memeriksanya belum tersedia. Persetujuan diskon nonzero sengaja belum dapat dieksekusi. Tidak menetapkan ambang persetujuan sendiri.
2. Penerapan migration pada database operasional tidak dilakukan. Draft nonzero pada website operasional tetap tertahan sampai pembaruan schema tersebut diterapkan dalam pekerjaan terpisah yang diizinkan.
3. UAT visual browser/keyboard native di viewport yang diminta masih outstanding. Tidak menyatakan visual sign-off/deployment siap.

Tidak ada reset/clean Git, penghapusan/merge customer, commit, push, deployment, perubahan harga master, permission matrix, Midtrans, promo rules, PDI/handover atau ownership.
