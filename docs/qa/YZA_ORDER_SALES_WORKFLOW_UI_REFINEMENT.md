# YZA Automotive — Order & Sales Workflow / UI Refinement

Tanggal: 8 November 2026. Workspace: `C:/xampp/htdocs/yza_automotive_laravel`.

## 1. Acuan dan batas perubahan

Tidak ditemukan dokumen proposal Laravel 12 tersendiri dalam file project yang tersedia. Acuan yang diperiksa: `docs/FOUNDATION_STAGE_1.md`, `docs/CAR_SALES_STAGE_7A.md`, `docs/CAR_SALES_PAYMENT_STAGE_7B.md`, audit pascapembayaran, implementasi PDI/pickup/handover, dan laporan P0 Stage 1–4. Implementasi aktual lebih baru daripada beberapa dokumen tahap awal; guard P0 dan source aktual menjadi acuan ketika berbeda.

Working tree sudah berisi banyak perubahan lokal. Tidak dilakukan reset, migration, seed ke database operasional, perubahan data historis, commit, push, atau deployment. Tidak diubah konfigurasi/kredensial, tabel hak akses, aturan pembayaran, perhitungan atau ketentuan promo, Midtrans, invoice, PDI, pickup, handover, maupun ownership.

## 2. Audit hubungan pesanan dan penjualan

| Jalur | Kontrak authoritative |
|---|---|
| Pesanan awal | `MemberVehiclePurchaseService::create`: snapshot pembeli/unit/harga/warna, `menunggu_verifikasi`, reservasi sesuai jenis inventori; belum ada sale/invoice/payment/ownership |
| Persetujuan | `approve`: mengunci order, memeriksa status, membuat satu `Transaksi` belum bayar dan satu `PenjualanMobil` proses, lalu menautkan kedua ID pada order |
| Penjualan langsung | `PenjualanMobilService::createWithPromo`: transaksi atomik, harga katalog server, validasi diskon/DP/booking/stok, sale proses dan reservasi; pembayaran terpisah |
| Invoice | Invoice existing membaca `Transaksi` dan relasi sale. Tidak dibuat mesin invoice kedua |
| Pembayaran member | Lunas dilanjutkan dengan PDI, pickup, handover, baru My Garage/ownership |
| Penjualan operasional | Tetap mengikuti completion pembayaran existing; tidak dipaksa memakai state machine PDI member |
| Stok agregat | `VehicleInventoryService::reserveCount` mengecualikan sale yang sudah direpresentasikan oleh order aktif; melanjutkan order tidak membuat reservasi baru |

Temuan: “Tambah Pesanan” memang merupakan link ke `/admin/penjualan-mobil/create`. Ini salah entry point untuk pencatatan awal. Pesanan yang sudah disetujui justru sudah mempunyai sale/invoice authoritative, sehingga checkout ulang bukan solusi.

## 3. Modal Tambah Pesanan

Tombol kini membuka native `<dialog>` pada halaman pesanan. Dialog putih, radius 16px, aksen merah, animasi fade/slide 200ms, header/footer tetap, body scroll, batas tinggi viewport. Native dialog menyediakan modal focus containment, Escape, dan background inert; JavaScript mengembalikan fokus ke pembuka saat ditutup.

Form: pelanggan aktif, unit, nama pembeli, telepon, alamat, email opsional, serta konfirmasi persetujuan pelanggan. Nama/telepon/alamat diisi awal dari pelanggan dan bisa diperiksa/diperbaiki. Snapshot mengikuti warna unit yang dipilih. Data kontak dan persetujuan dipertahankan karena merupakan kontrak checkout existing, bukan dilemahkan agar modal lebih pendek.

Tidak ditambahkan booking, sumber pesanan, atau catatan awal: model order existing tidak mempunyai relasi/field terpisah untuk hal itu. Tidak memasukkan diskon, DP, pembayaran atau promo ke modal.

POST `admin.pesanan-mobil.store` memakai middleware `sales.manage` existing. Owner tetap ditolak, tombol mutasi disembunyikan untuk pembaca. Error bag `createOrder` membuka kembali dialog beserta input/error. Simpan memakai redirect server existing agar daftar, detail dan indikator dibaca ulang; tidak dibuat renderer AJAX kedua.

`createForStaff` mengunci pelanggan dan unit, memvalidasi pelanggan aktif/unit tersedia, mencegah order aktif ganda pelanggan–unit, kemudian mendelegasikan ke `create`. Pemeriksaan kapasitas/reservasi tetap authoritative. Audit menggunakan `MasterAuditService`; event model/notifikasi existing dipertahankan. Tidak ada auto-paid, unit terjual, PDI lulus, handover atau ownership.

## 4. Form penjualan dan pencegahan duplikasi

“Buat Penjualan” tetap membuka full page. Bagian “Lanjutkan penjualan dari pesanan” hanya menawarkan pesanan yang sudah disetujui dan memiliki sale proses serta transaksi aktif. Pilihan membuka sale existing setelah memeriksa identitas katalog dan kecocokan order/sale/transaksi/pelanggan/unit. GET ini tidak membuat invoice/reservasi atau menerapkan ulang promo. Pesanan awal tetap diverifikasi melalui menu Pesanan Mobil dan konfirmasi keputusan existing.

POST penjualan langsung melarang `id_pesanan`; service juga menolak pemakaian ulang order aktif pelanggan untuk direct checkout, termasuk inventori agregat. Pemeriksaan unit fisik ganda, locking aggregate inventory, transaksi atomik dan arithmetic P0 dipertahankan.

Pengiriman form langsung menggunakan UUID yang diterbitkan server dan terikat akun/session, maksimal 20 form terakhir. Setiap form mendapatkan kode sale unik dari generator existing sebelum disimpan. POST kedua dengan payload sama mengarah ke sale yang sama; payload yang diubah setelah completion ditolak. Route memakai session blocking untuk request paralel. Jika completion marker session hilang setelah commit, service mencari kode sale yang telah diterbitkan dan mengembalikan relasi existing tanpa menerapkan promo atau reservasi lagi. Unique constraint kode sale yang sudah ada menjadi pengaman DB terakhir; tidak ada schema/idempotency table baru. Form yang tidak mempunyai token sah harus dibuka ulang. Jalur internal service existing tanpa form tetap didukung.

Kontrak POST Blade bertambah `submission_key` wajib; caller HTTP khusus harus mengambilnya dari halaman create dan mempertahankan session. Tidak ditemukan caller UI lain yang perlu dimigrasikan. Session/token yang sudah kedaluwarsa ditolak, bukan membuat transaksi baru secara diam-diam.

FormRequest memakai izin `sales.manage` existing, termasuk Sales modern; ini menyelaraskan guard request dengan middleware, tanpa memberi permission baru atau mengubah role mapping. Alias Petugas pada route petugas tetap mengikuti kontrak legacy. Owner tetap read-only.

## 5. Perbaikan UI dan promo

- Banner daftar penjualan memakai layout grid dengan copy dalam normal flow, tinggi dasar 170px desktop. Slogan dekoratif, CTA banner duplikat, dan branding overlay yang bertabrakan dihapus dari presentasi.
- Background mobil/branding/warna YZA tetap dipakai. Tinggi boleh bertambah bila teks membutuhkan ruang pada viewport sempit.
- Banner create, stepper, heading, padding dan ringkasan dibuat compact. Dialog serta form mempunyai aturan responsif tanpa zoom/scale.
- Tanda plus duplikat pada “Buat Penjualan” dihapus.
- Topbar pada halaman terkait memakai putih opaque.
- Foto fallback Avanza untuk kendaraan lain diganti gallery unit yang sesuai atau placeholder netral.
- KPI pendapatan hanya memakai penerimaan masuk terverifikasi. Fallback nilai kontrak dihapus: kontrak belum dibayar bukan pendapatan kas. Tidak mengubah definisi laporan keuangan; KPI ini merupakan penerimaan bruto terverifikasi, bukan laba/net accrual.
- Angka pertumbuhan `+100%` hardcoded diganti data bulan aktual. Sparkline/bar dekoratif statis tidak lagi ditampilkan sebagai tren data.
- Enam kartu statistik Pesanan Mobil tetap satu baris; aturan sebelumnya dipertahankan.

Root cause promo: komponen member dipakai di form staf, sehingga preview menilai akun petugas, bukan pelanggan yang dipilih. CSS `.promo-summary { display:grid }` juga mengalahkan hidden bawaan browser; kondisi serupa terjadi pada pratinjau mobil.

Form staf kini mempunyai partial promo tersendiri dan endpoint preview di modul penjualan Admin/Petugas. Server mengambil pelanggan aktif dan harga katalog sendiri, kemudian memanggil `PromoEngineService::calculate` existing. Harga kiriman browser tidak dipakai. Endpoint read-only tidak membuat redemption/invoice/reservasi, memakai permission sales.view existing, dan respons sukses private/no-store.

Pilihan radio compact menampilkan persentase termasuk pecahan, minimum, masa berlaku, member-only dan batas benefit. Ringkasan subtotal/diskon/total hanya muncul setelah preview sah. Sidebar memakai hasil yang sama. Pergantian pelanggan/unit/diskon membatalkan preview lama; respons kedaluwarsa diabaikan. Diskon manual tidak dapat ditumpuk dengan promo, sesuai guard existing. Kegagalan/putus koneksi tidak ditandai berhasil dan memblokir submit promo sampai dihapus/diperbaiki. Backend menghitung ulang harga, eligibility dan kuota saat commit; guard DP setelah promo tetap milik engine existing.

## 6. File perubahan tugas ini

| File | Perubahan |
|---|---|
| `routes/web.php` | POST pesanan; preview promo staf; session blocking sale POST |
| `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php` | Data form modal dan store tervalidasi |
| `app/Services/MemberVehiclePurchaseService.php` | Wrapper staff, lock/duplicate/active guards dan audit |
| `app/Http/Controllers/OperationsPenjualanMobilController.php` | Pilihan order existing, preview pelanggan, token form, replay redirect dan audit |
| `app/Http/Requests/StorePenjualanMobilRequest.php` | Selaras izin existing, larang double checkout order, validasi promo/token |
| `app/Services/PenjualanMobilService.php` | Guard order aktif/status unit; unique issued-code retry tanpa promo ulang |
| `resources/views/admin/pesanan-mobil/index.blade.php` | Pemicu modal dan asset |
| `resources/views/admin/pesanan-mobil/_create-modal.blade.php` | Dialog pesanan |
| `resources/views/operations/penjualan-mobil/index.blade.php` | Banner/KPI/button |
| `resources/views/operations/penjualan-mobil/create.blade.php` | Full form compact, order source, gallery, token, promo partial |
| `resources/views/operations/penjualan-mobil/_promo.blade.php` | Promo staf compact |
| `public/assets/css/order-sales-refinement.css` | Layout scoped/responsif/hidden/topbar |
| `public/assets/js/order-create-modal.js` | Open/close/focus/autofill/submit |
| `public/assets/js/sale-create.js` | Summary, preview, stale-response and input guards |
| `tests/Feature/OrderSalesWorkflowRefinementTest.php` | Pengujian workflow HTTP/domain terisolasi |
| `tests/Ui/order-sales.test.cjs` | Interaksi modal/promo melalui harness DOM |

Daftar ini bukan keseluruhan `git diff`: banyak perubahan lokal/P0 sudah ada sebelum tugas ini.

## 7. Pengujian dan keterbatasan

Seluruh pengujian PHP memakai `phpunit.xml`, `APP_ENV=testing`, database `yza_automotive_laravel_test`, dan transaksi rollback. Tidak menjalankan pengujian mutasi pada database operasional.

Hasil pemeriksaan:

- Focused workflow, car-sales/payment, keputusan order, promo P0 dan QC/PDI: **100 tests / 444 assertions, lulus** (`order-sales-focused.txt`).
- Harness JavaScript order/sales dan regresi panel/sinkronisasi notifikasi: **18 tests, lulus** (`order-sales-client.txt`). Enam kasus order/sales mencakup placeholder, validasi pelanggan/unit/promo, ringkasan, respons jaringan kedaluwarsa, DP/booking dan handler modal/fokus kembali.
- Frontend build: **lulus**, Vite 7.3.7, 58 modules (`order-sales-build.txt`). PHP/JavaScript syntax checks lulus.
- `git diff --check`: **exit 0**, hanya peringatan line-ending pada file lokal lain (`order-sales-diff-check.txt`).
- Full regression final: **630 lulus / 1 gagal, 631 tests / 3.208 assertions**, 108,974 detik (`order-sales-regression.txt`/`.xml`). Satu failure existing: `MemberVehiclePurchaseFlowTest::test_member_order_detail_renders_premium_dashboard_for_owned_order`, baris 51, mengharapkan label “Informasi Pembeli” sedangkan view member existing memakai “Data Pembeli”. File view/test tersebut tidak diubah pada tugas ini. Tidak ada failure baru pada workflow/refinement atau P0 yang diuji.

Focused backend menguji order valid/invalid/inaktif/stok habis, duplicate order pada stok fisik dan agregat, tidak ada invoice/payment/ownership/handover prematur, permission Sales dan penolakan Owner/role lain, reuse sale dan reservasi dari order, penolakan relasi order yang dimanipulasi, direct sale belum bayar, replay/mismatch/forged form key, replay setelah completion marker hilang, preview pelanggan/harga server, rollback promo dan DP pascapromo, serta KPI tanpa fallback kontrak. Suite P0 existing memeriksa pembayaran noncash pending, guard PDI/handover, quota/rollback/invoice integrity. Assertion existing tidak dikurangi.

Baseline awal aktual (`order-sales-baseline.txt`/`.xml`): 618 tests, 2 failures. Run tersebut beririsan dengan perubahan awal sehingga satu failure merupakan kondisi sementara asset CSS baru belum tersedia pada render QC; ini bukan baseline immutable yang bersih. Failure lainnya adalah assertion label “Informasi Pembeli” pada detail member yang sebelumnya sudah terdokumentasi. Kondisi asset sementara sudah diperbaiki dan focused QC/render lulus. Assertion existing tidak dikurangi atau diubah.

Uji responsif diminta pada 320, 375, 390, 768, 1024, 1280, 1366, 1440, dan 1920px, zoom 100%. Aturan grid, wrap, `min-width:0`, dialog viewport limit, scroll body dan media queries diaudit di source. **Belum ada verifikasi visual browser langsung untuk sembilan viewport tersebut**, termasuk Escape/focus containment native, scroll nyata dan overlap akhir. Harness DOM menguji handler dan state; tidak membuktikan geometri, animasi atau perilaku native browser.

Akses browser sebelumnya dihentikan karena alat tidak dapat memverifikasi URL Edge dengan cukup yakin. Pembatasan itu tidak dilewati. Tidak dibuat screenshot mockup/test-data sebagai bukti halaman operasional. UAT visual tetap outstanding, dan hasil ini tidak dinyatakan sebagai visual sign-off atau approval deployment.
