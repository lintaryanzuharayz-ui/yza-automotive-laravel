# YZA Automotive — Active Member Customer Filter Audit

Tanggal audit: 8 November 2026. Halaman: `/admin/pesanan-mobil`.

## Ringkasan hasil audit operasional READ ONLY

Audit memakai model/tabel aktual: `User/users`, `Pelanggan/pelanggan`, `Membership/memberships`, `PembayaranGateway/pembayaran_gateway`, `EventPembayaranGateway/event_pembayaran_gateway`, `pesanan_mobil_member`, `transaksi`, dan `kendaraan_pelanggan`. Seluruh query operasional berada dalam `SET TRANSACTION READ ONLY`, kemudian rollback. Tidak ada inquiry Midtrans langsung, aktivasi, backfill atau perubahan data operasional.

| Ukuran | Hasil |
|---|---:|
| Record master pelanggan | 11 |
| Akun dengan role pelanggan | 7 |
| Membership berstatus active | 7 |
| Membership pending_payment | 4 |
| Membership cancelled | 0 |
| Membership active dengan receipt gateway settlement terhubung | 3 |
| Receipt di atas yang juga terhubung akun pelanggan aktif yang benar | 2 |
| Dari dua akun tersebut, mempunyai processed_at modern | 1 |
| Dari dua akun tersebut, receipt historis tanpa processed_at | 1 |
| Satu ID user terhubung ke lebih dari satu row pelanggan | 0 |

Hasil filter aktual: **Lintar Yan Zuhara (customer 750, user 990, YZA-MBR-000750)** dan **Lucky Yan Zuhara (customer 752, user 1001, YZA-MBR-000752)**. Jumlah/nama ini hasil query, bukan whitelist dalam kode. Member baru yang memenuhi kontrak akan ikut tampil pada request halaman berikutnya.

Bukti lokal: `active-member-customer-readonly-audit.json` dan `active-member-customer-eligibility-audit.json`. Artefak hanya menyimpan identitas referensi dan metadata audit; tidak menyimpan nomor telepon/alamat mentah, email, token pembayaran atau kredensial.

## Temuan record legacy/testing dan nama yang sama

| Customer | Kondisi aktual | Keputusan filter modal |
|---|---|---|
| 1, 2 | Akun pelanggan testing, active hasil entitlement lama, tanpa gateway aktivasi | Tidak eligible pada modal pesanan Member berbasis bukti aktivasi |
| 3 | Walk-in tanpa akun, active tanpa gateway | Tidak eligible pada modal Member; tidak dihapus dari penjualan langsung |
| 439 | Demo Active, active tanpa gateway | Tidak eligible pada modal Member |
| 440 | Demo Pending, pelanggan.status aktif tetapi membership pending_payment | Tidak eligible |
| 528, 529 | Lintar, tanpa id_user, membership pending_payment | Tidak eligible |
| 530 | Lintar, tanpa id_user; membership active dan receipt settlement, tetapi initiated_by juga null | Tidak eligible: identitas akun tidak dapat dibuktikan |
| 750 | Lintar, user pelanggan aktif, membership active, receipt 1004 dan event paid sesuai | Eligible dengan catatan receipt historis di bawah |
| 751 | Nama Admin YZA tetapi **role akun sebenarnya pelanggan**, membership pending_payment | Tidak eligible karena status/bukti aktivasi, bukan karena nama mengandung Admin |
| 752 | Lucky, user pelanggan aktif, membership active, receipt 1018 diproses P0 | Eligible |

Ada empat row bernama Lintar: 528, 529, 530 dan 750. Tiga pertama sekarang tidak mempunyai user; hanya 750 terhubung ke akun 990. Log `master.audit` yang tersedia mencatat keempatnya sebagai `Registrasi YZA Member pending payment`, bukan penciptaan pelanggan dari checkout. Waktu registrasinya berbeda. Record 528/529/530 mempunyai telepon dan alamat yang sama; 750 mempunyai telepon berbeda dan alamat sama. Persamaan ini mendukung kebutuhan rekonsiliasi, tetapi tidak merupakan identifier unik yang cukup untuk merge otomatis.

Schema mempunyai unique `pelanggan.id_user` dan foreign key `ON DELETE SET NULL`. Jadi nama yang berulang bukan duplicate row dari query join atau dua pelanggan untuk satu user aktif. Mengapa user lama dihapus/tidak lagi terhubung tidak dapat dipastikan dari bukti create yang tersedia. Tidak merekonstruksi akun, memindahkan receipt 712, atau menggabungkan histori secara otomatis.

Customer 750 memiliki 3 pesanan, 5 transaksi/invoice dan 1 kendaraan pelanggan; customer 1 mempunyai 8 transaksi dan 1 kendaraan pelanggan. Record 528/529/530 tidak mempunyai pesanan, invoice atau ownership pada snapshot ini. Semua relasi tersebut dipertahankan.

## Kontrak aktivasi dan batas pembuktian historis

P0 Stage 2 menetapkan aktivasi melalui canonical server inquiry, validasi target/nominal/fraud, lalu atomic activation + `processed_at`. `MidtransGatewayProcessor` juga menganggap **membership active + pointer gateway yang sesuai** sebagai processed untuk menangani aktivasi historis. Ini diperiksa bersama `MembershipPaymentService`, `MemberAccessService`, `EnsureActiveMembership`, serta laporan P0 Stage 2.

Receipt 1004 milik Lintar berstatus settlement, mempunyai provider transaction identity, metode bank_transfer, initiated_by 990, target membership 360, prefix order dan nominal yang sesuai, serta event settlement/status_code 200/fraud accept dengan identity/metode sama. `processed_at` belum terisi. Filter mempertahankan fallback historis active+linked receipt yang sudah dikenali processor P0, dengan pemeriksaan bukti tambahan; **tidak mengisi processed_at dan tidak mengklaim receipt ini baru diverifikasi ulang ke Midtrans**. Lucky mempunyai bukti serupa dan processed_at terisi. Keduanya dibedakan dalam audit.

Migration `2026_10_04_020000_create_memberships_table` memberi status active kepada pelanggan lama tanpa mewajibkan receipt. Ini menjelaskan mengapa status active saja bukan bukti pembayaran. Entitlement legacy tersebut tidak dicabut: akses global member, status membership, payment dan kebijakan grandfathering tidak diubah oleh tugas ini. Hanya pilihan untuk pembuatan **pesanan Member oleh staf** yang diperketat. Penjualan operasional langsung tetap memakai eligibility existing.

Schema saat ini tidak mempunyai tanggal kedaluwarsa membership. Tidak dibuat aturan expiry baru. Status cancelled dan cancelled_at diperiksa sesuai data existing.

## Akar masalah dan perbaikan query

Query lama: `Pelanggan::where('status', 'aktif')->orderBy('nama_lengkap')->get()`. Seluruh 11 pelanggan mempunyai status master aktif, termasuk akun pending, walk-in, orphan dan testing. Label “Pelanggan aktif” menyamakan status master dengan membership.

Query baru dipusatkan di `ActiveMemberCustomerService::query()`:

- Pelanggan aktif dan akun user aktif dengan role pelanggan.
- Membership pada customer yang sama, active, activated_at terisi, cancelled_at kosong.
- Gateway ditunjuk oleh membership tersebut; target membership, initiated_by user dan nominal activation_fee cocok; provider Midtrans, tidak ditautkan ke invoice/payment kendaraan.
- Status settlement/capture, provider transaction ID/metode terisi, prefix order membership sesuai.
- Event paid/status_code 200 terikat gateway, transaction identity dan metode sama; fraud/method capture atau settlement mengikuti kondisi sukses P0.

Memakai correlated EXISTS, bukan join yang menggandakan hasil. Identitas berasal dari customer ID dengan unique user link dan member ID existing. Tidak memakai DISTINCT nama, hardcode nama/jumlah, substring testing/demo, atau whitelist ID.

## Validasi backend dan integritas

`createForStaff` mengunci pelanggan, akun, membership dan gateway yang dirujuk, lalu memeriksa eligibility yang sama sebelum reservasi/order dibuat. ID pelanggan ineligible ditolak walaupun dikirim manual. Input `id_user` dari browser dilarang; relasi user diselesaikan server dari pelanggan, bukan dari field manipulabel. Permission `sales.manage`, Owner read-only, pemeriksaan kendaraan, locking stok, duplicate-order guard dan service authoritative dipertahankan.

Pembuatan pesanan tidak membuat master pelanggan baru. Source registration membuat user/customer/pending membership dalam satu transaksi, dengan username/email unik dan unique user–customer link. Tidak ditemukan pembentukan master duplikat oleh checkout existing. Registrasi terpisah dengan nama sama tidak disatukan: nama/telepon bukan kunci identitas unik menurut kontrak existing. Karena itu tidak menambahkan larangan nama/telepon sama atau auto-merge yang dapat menolak identitas sah.

Penjualan langsung tidak memakai service filter ini. Pelanggan non-member tetap tersedia dan dapat membuat sale belum bayar melalui workflow operasional. Tidak mengubah pembayaran, promo, invoice, order state machine, stok terjual, ownership, PDI, pickup atau handover.

## Dropdown dan file yang diubah

Modal memakai label **Pilih Member Aktif** dan searchable combobox. Pilihan menampilkan nama, member ID sebenarnya, dan badge Member Aktif. Pencarian nama/member ID/kode pelanggan bekerja hanya pada data eligible dari server. List mempunyai scroll internal dan empty state. Arrow Up/Down, Enter, Escape dan Tab didukung; pemilihan mengisi snapshot kontak dan ID pelanggan, sementara teks pencarian bebas tidak dianggap identitas valid. Dialog native dan pemulihan fokus existing dipertahankan. Status yang berubah setelah halaman dibuka tetap diperiksa ulang saat save.

| File | Perubahan tugas ini |
|---|---|
| `app/Services/ActiveMemberCustomerService.php` | Query eligibility bersama dan recheck terkunci |
| `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php` | Query modal eligible/eager-load membership; tolak id_user input |
| `app/Services/MemberVehiclePurchaseService.php` | Panggil eligibility dalam wrapper staf sebelum reservasi |
| `resources/views/admin/pesanan-mobil/_create-modal.blade.php` | Combobox member ID/status/empty state |
| `public/assets/js/order-create-modal.js` | Search/selection/keyboard/validity |
| `public/assets/css/order-sales-refinement.css` | Tampilan picker dan scrollbar |
| `tests/Support/CreatesPaidMember.php` | Fixture aktivasi melalui inquiry P0 dengan provider response dimock, DB testing saja |
| `tests/Feature/ActiveMemberCustomerFilterTest.php` | 11 kasus eligibility, identitas, histori, backend dan non-member sale |
| `tests/Feature/OrderSalesWorkflowRefinementTest.php` | Fixture existing kini mempunyai bukti aktivasi sah; assertion dipertahankan, kasus stok memakai member sah kedua |
| `tests/Ui/order-sales.test.cjs` | Adaptasi handler picker; tambah search/empty/validity/member ID/keyboard checks |

Perubahan lokal lain yang sudah ada dipertahankan. Tidak ada schema/migration, commit, push atau deployment.

## Hasil pengujian

- Focused `ActiveMemberCustomerFilterTest`, workflow order/sales, dan security pembayaran Stage 2: **78 tests / 415 assertions, lulus** (`active-member-focused.txt`).
- Interaksi JavaScript picker/order/sales beserta regresi notifikasi: **18 tests, lulus** (`active-member-client.txt`).
- Frontend build: **lulus**, 58 modules (`active-member-build.txt`). Syntax JavaScript/PHP dan `git diff --check` lulus.
- Full regression pada database testing terisolasi: **644 lulus / 1 gagal, 645 tests / 3.274 assertions**, 120,252 detik (`active-member-regression.txt`/`.xml`). Satu failure lama tetap berada di `MemberVehiclePurchaseFlowTest::test_member_order_detail_renders_premium_dashboard_for_owned_order`, baris 51: assertion mengharapkan “Informasi Pembeli”, sementara view member existing memakai “Data Pembeli”. View/assertion tersebut tidak diubah dalam tugas ini. Tidak ada failure baru pada filter, order/sales maupun P0 yang diuji.

Fixture PHP menggunakan database `yza_automotive_laravel_test` dan rollback. Aktivasi fixture berjalan melalui inquiry processor P0 dengan mock respons provider, bukan mengubah membership operasional atau menghubungi provider nyata. Assertion lama tidak dikurangi. Logika payment/promo P0 tidak diubah.

Harness DOM membuktikan handler/state, bukan verifikasi visual native browser. Tampilan langsung dan perilaku pointer pada browser operasional belum diverifikasi; pembatasan akses browser sebelumnya tidak dilewati dan tidak dibuat screenshot mockup sebagai bukti live.

## Rekonsiliasi yang masih diperlukan

1. Tinjau asal pelepasan akun pada customer 528/529/530, bukti pemilik receipt 712, dan identifier akun original dari arsip yang sah. Jangan kaitkan ke user 990 hanya berdasarkan nama/alamat.
2. Tinjau receipt historis 1004 yang belum mempunyai processed_at dengan prosedur rekonsiliasi yang disetujui. Filter ini tidak melakukan backfill atau live re-verification.
3. Tentukan provenance/grandfathering untuk empat membership active tanpa gateway. Tidak dilakukan penagihan ulang atau pencabutan akses.
4. Data testing yang masih berada di master operasional tetap dipertahankan; tidak menyembunyikan akun testing yang suatu saat memenuhi seluruh eligibility secara sah hanya karena namanya.
