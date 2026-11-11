# YZA AUTOMOTIVE — P0 STAGE 4

Tanggal: 31 Oktober 2026, Asia/Jakarta. Project: `C:\xampp\htdocs\yza_automotive_laravel`.

## 1. Executive Summary

**B09 diperbaiki pada lima jalur yang terbukti terdampak:** `ServisService::finalizeWithPromo`, `BookingCuciService::finalizeWithPromo`, `PenjualanSparepartService::finalizeWithPromo`, `PenjualanMobilService::createWithPromo`, dan `TradeInService::createWithPromo`. Operasi domain dan penerapan promo sekarang mempunyai satu outer database transaction. Exception promo membatalkan invoice/link, konsumsi stok/history, perubahan status, reservasi, notifikasi DB, dan perubahan harga yang merupakan bagian operasi itu.

Total bersih domain kini mengikuti invoice yang diberi promo; snapshot harga bruto/detail tetap dipertahankan. Diskon ganda, scope yang berbeda dengan jenis transaksi, serta perubahan harga setelah pembayaran/attempt dimulai ditolak. Perhitungan promo memakai integer cents dan DECIMAL, dengan konvensi pembulatan diskon rupiah existing dipertahankan. Tidak membuat promo engine baru atau jenis diskon baru.

Branch awal/akhir: `feature/vehicle-purchase-flow`. HEAD: `0359b1e8fa28aeceac5aa8491bb7eb5d85672ea6`. Working tree sudah berisi pekerjaan lokal dan hasil tahap 1–3; tidak di-reset, checkout, commit atau ditimpa. Snapshot isi enam service target diambil sebelum perubahan tahap 4. Baseline terdahulu: **539 PASS / 2.455 assertions / 0 failure/error/skipped**, berdasarkan hasil tahap 3 yang sudah tersedia.

Dokumen audit utama, evidence, dan laporan tahap 1–3 menjadi dasar audit. Tidak ada rewrite Midtrans/payment implementation, RBAC, assignment, QC/PDI, handover atau ownership. Perubahan payment tambahan hanya hook pelepasan reservasi di transaksi pembatalan existing; credential dan `.env` tidak diubah. Tidak ada deployment atau mutasi database operasional.

**Keputusan bisnis lanjutan dari pengguna:** reservasi kuota saat invoice dibuat, pelepasan hanya ketika transaksi dibatalkan, tanpa kedaluwarsa otomatis. Keputusan ini sudah diterapkan menggunakan status `reserved/completed/released` existing, tanpa migration atau perubahan data operasional. Kuota total dan batas per pelanggan menghitung reserved + completed. Pelunasan mengubah reservasi yang sama menjadi completed; pembatalan melepaskan reserved secara atomik.

## 2. Root Cause B09

Sebelum fix, wrapper memanggil operasi domain yang menjalankan `DB::transaction`, mengembalikannya setelah commit, lalu memanggil promo engine dalam transaksi kedua. Rollback promo hanya membatalkan transaksi kedua. Controller menangkap exception dan menampilkan error meskipun invoice/reservasi/stok sudah berubah.

| Jalur | Efek domain sebelum promo gagal | Hasil audit/perbaikan |
|---|---|---|
| Servis | Konsumsi sparepart, history/notifikasi stok, invoice, link WO, event finalized | Satu outer transaction; QC/authorization tahap 1–3 tetap wajib |
| Car wash | Invoice dan link booking | Satu outer transaction |
| Penjualan sparepart | Draft existing difinalisasi, total/status berubah, stok/history dikonsumsi | Satu outer transaction; bila gagal, draft existing dipertahankan dalam keadaan semula |
| Penjualan mobil operasional | Invoice, sale dan reservasi unit | Satu outer transaction; total sale/invoice sama, validasi DP sesudah promo |
| Trade-in | Invoice, trade dan reservasi unit dealer | Satu outer transaction; selisih bayar bersih mengikuti invoice |
| Checkout member | `create` mengunci mobil/inventori dan menyimpan order/snapshot dalam satu transaction; `approve` membuat invoice/sale dalam transaction sendiri pada tahap bisnis berbeda | Tidak memanggil promo engine dan tidak terbukti mempunyai split-promo boundary; `MemberVehiclePurchaseService` tidak diubah |

Controller/route yang diperiksa: POST finalisasi servis admin/petugas, aksi finalize booking cuci, finalize penjualan sparepart, store penjualan mobil operasional, dan store trade-in pelanggan. Preview promo hanya menghitung eligibility; subtotal preview dari browser tidak pernah dipakai sebagai harga invoice authoritative.

Tabel/model: `promo_member`, `promo_redemptions`, `transaksi`, domain sale/WO/booking/trade, `sparepart`, `riwayat_stok`, `mobil`, `vehicle_inventories`, pembayaran dan notifikasi. Existing schema mempunyai status redemption `reserved/completed/released`. Pada audit awal aplikasi hanya memakai completed; setelah keputusan eksplisit pengguna, reserved dan released kini dipakai sesuai kontrak di bagian 6.

## 3. Bukti reproduksi sebelum fix

Lima negative tests dibuat sebelum mengubah service produksi. Setiap test menyiapkan domain sah, termasuk QC lulus untuk servis, lalu memakai ID promo yang tidak ada. `ModelNotFoundException` muncul setelah operasi utama selesai. Snapshot tabel domain/invoice/stok/history/approval/pembayaran/notifikasi sesudah exception berbeda dari sebelumnya pada kelima jalur.

Hasil kode lama: **5 tests / 10 assertions / 5 failures / 0 errors**. Bukti: [YZA_P0_STAGE4_BEFORE.xml](YZA_P0_STAGE4_BEFORE.xml). Setelah outer transaction ditambahkan, lima test yang sama lulus tanpa melemahkan assertion. Snapshot mismatch membuktikan efek parsial; bukan sekadar keberadaan exception.

## 4. Daftar file yang diubah

| File | Perubahan tahap 4 |
|---|---|
| `app/Services/ServisService.php` | Outer transaction pada wrapper promo saja; guard authorization/QC dan finalisasi dasar dipertahankan |
| `app/Services/BookingCuciService.php` | Outer transaction wrapper promo |
| `app/Services/PenjualanSparepartService.php` | Outer transaction wrapper promo; hook release pada pembatalan transaksi draft existing |
| `app/Services/PenjualanMobilService.php` | Outer transaction, lock aggregate inventory, cegah sale/reservasi unit fisik ganda, arithmetic harga DECIMAL |
| `app/Services/TradeInService.php` | Outer transaction, arithmetic DECIMAL, dan pelepasan reservasi dalam transaksi cancel existing |
| `app/Services/PromoEngineService.php` | Exact arithmetic, guard invoice/scope/stacking, sync payable domain, reservasi/complete/release atomik dan current-read quota |
| `app/Services/PaymentService.php` | Hook releaseReservation hanya ketika penolakan pembayaran existing membatalkan transaksi member |
| `resources/views/invoices/car-sale.blade.php` | Baris diskon manual bernilai nol bila diskon berasal dari promo, agar tidak terkesan ditampilkan dua kali |
| `tests/Support/PromoIntegrityFixtures.php` | Fixture melalui layanan WO/QC sah dan unit testing unik |
| `tests/Feature/PromoTransactionIntegrityStage4Test.php` | Reproduksi lima wrapper, harga/rollback/retry/eligibility/role/expiry/snapshot/exclusivity |
| `tests/Feature/PromoConcurrencyStage4Test.php` | Empat race dengan proses/koneksi independen |
| `tests/Support/promo_concurrent_worker.php` | Worker terbatas CLI/testing dan DB testing eksplisit |
| `docs/qa/YZA_P0_STAGE4_READONLY_AUDIT.php` dan `.json` | Audit aggregate operational dengan transaksi READ ONLY, selalu rollback |
| `docs/qa/YZA_P0_STAGE4_*.xml` dan laporan ini | Bukti pengujian dan hasil akhir |

Tidak ada migration baru. Test baseline tahap 1–3 tidak diubah atau dikurangi assertion-nya. Test Stage 4 diperbarui dari expected tanpa ledger menjadi expected satu ledger reserved sesuai keputusan baru, dengan assertion status tambahan. Perubahan Git lain yang sudah ada sebelum tahap ini tidak merupakan perubahan Stage 4.

## 5. Transaction boundary baru

```text
outer DB::transaction (wrapper bisnis)
  lock domain dan validasi existing (authorization, QC, status, approval, stok)
  nested transaction domain: finalisasi/create, stok/history, invoice/link/reservasi
  nested transaction promo: lock invoice → lock promo → validasi → snapshot diskon
  sinkronisasi total bersih domain dan validasi DP
  simpan redemption reserved (menahan kuota)
commit outer transaction
```

Urutan internal finalisasi existing dipertahankan: beberapa write domain/stok dilakukan sebelum validasi promo, tetapi kini belum commit dan seluruhnya di-rollback bila validasi gagal. Tidak mengklaim write dilakukan sesudah promo dalam semua service; jaminannya adalah **tidak ada efek wajib yang tersisa setelah kegagalan wrapper**.

Perilaku nested transaction diverifikasi dari source framework yang terpasang, `vendor/laravel/framework/src/Illuminate/Database/Concerns/ManagesTransactions.php`: PDO commit hanya ketika level transaksi sama dengan satu; nested begin memakai savepoint bila grammar mendukung; rollback nested kembali ke level sebelumnya. Exception tidak ditelan di dalam wrapper sehingga mencapai outer rollback. Test fault injection setelah promo menyimpan harga juga membuktikan rollback mencakup seluruh operasi domain, bukan hanya savepoint promo.

Notifikasi stok/service existing berupa record DB pada koneksi/transaksi yang sama. Tidak ditemukan request provider/email eksternal pada lima wrapper yang diperbaiki. Tidak ada Snap/inquiry/payment request eksternal yang dipindahkan ke dalam transaksi ini.

## 6. Validasi promo

Aturan existing yang dipertahankan: aktif, periode mulai/selesai inklusif, scope produk/jenis transaksi, minimum subtotal, member aktif untuk member_only, quota reserved + completed, batas reserved + completed per pelanggan, nominal/persentase, maximum benefit, dan satu promo per transaksi. Eligibility/expiry diperiksa menggunakan record promo yang telah dikunci, sesudah memperoleh lock.

Penguatan terkait integrity:

- Scope apply harus sama dengan `transaksi.jenis_transaksi`; caller tidak dapat memilih scope lain agar promo lolos.
- Hanya invoice `belum_bayar` tanpa record pembayaran atau gateway attempt yang boleh diberi promo. Pending payment juga membekukan harga; tidak membuat intent berharga lama lalu mengubah tagihan.
- Diskon existing nonzero tidak dapat ditumpuk dengan promo. Promo member_only dan publik juga merupakan alternatif: promo kedua ditolak, bukan digabung.
- `redeem` tidak dapat menghitung ulang harga atau mengklaim invoice belum lunas, promo berbeda, pelanggan berbeda atau scope berbeda. Jalur itu kini menggunakan snapshot invoice dan mekanisme finalize redemption yang sama.
- Finalize redemption berulang mengembalikan ledger completed existing, serta memeriksa hubungan promo/pelanggan. Perubahan campaign setelah invoice tidak menghitung ulang nominal invoice/ledger.

Tidak menambahkan field harga member/publik di katalog, auto-selection promo, jenis benefit baru atau aturan item baru. Reservasi kuota mengikuti keputusan pengguna, bukan asumsi dari enum. Kontrak existing `member_only` dan satu promo per invoice digunakan untuk pengujian exclusivity. Promo servis meliputi invoice WO beserta sparepart existing; tidak menciptakan diskon terpisah per baris/produk. Penjualan sparepart legacy tetap didukung karena jalur tersebut memang sudah ada dan terbukti mengalami B09.

### Kebijakan reservasi yang disetujui

- Penerapan promo menyimpan satu ledger reserved sebelum outer commit. Jika reservasi gagal dibuat, invoice/stok/reservasi kendaraan dan snapshot promo juga rollback.
- Kuota total dan usage_per_member menghitung reserved + completed. Reservasi baru ditolak sebelum invoice diterbitkan bila slot habis.
- Pelunasan memakai slot reserved sendiri, memverifikasi snapshot, mengubah row yang sama menjadi completed dan memperbarui used_at. Tidak mengevaluasi ulang harga/expiry atau mengambil slot baru, termasuk bila campaign kemudian dinonaktifkan atau kuotanya diturunkan.
- Tidak ada TTL, scheduler, auto-release karena tanggal promo habis, expiry attempt provider, atau penolakan bukti bayar saja. Transaksi yang masih belum_bayar/DP tetap memegang reservasi.
- releaseReservation membaca ulang dan mengunci transaksi lalu promo lalu ledger; hanya menerima transaksi berstatus dibatalkan. Reserved menjadi released secara idempotent; completed tetap merupakan receipt dan tidak dibalik otomatis.
- Hook dipasang pada pembatalan trade-in, pembatalan transaksi draft penjualan sparepart, dan pembatalan transaksi member oleh penolakan pembayaran existing. Semuanya berada di transaction boundary pemanggil. Tidak menambahkan endpoint cancel invoice baru atau mengubah kewenangan pembatalan.
- Invoice historis yang sudah memakai promo tetapi tidak memiliki ledger reservation tidak di-backfill otomatis. Fallback completion existing masih membutuhkan kuota tersedia; bila tidak tersedia, perlu review terkontrol, bukan mengambil slot invoice baru atau mengubah invoice operasional.

## 7. Konsistensi invoice dan harga

`subtotal` dan detail harga tetap snapshot bruto authoritative dari server/database. Browser dapat mengirim harga palsu, tetapi field tersebut tidak menjadi sumber harga final. Diskon berasal dari promo terkunci. Total dihitung dengan representasi exact dan tidak negatif; diskon tidak boleh melebihi subtotal.

Konvensi existing tetap: pembulatan subtotal ke rupiah untuk basis persentase, hasil persentase dibulatkan ke bawah melalui integer division, nominal/max benefit ke rupiah. Untuk subtotal pecahan, sisa cents dipertahankan pada total sehingga `subtotal - diskon = total` secara DECIMAL; diskon rupiah dikap pada bagian rupiah yang tidak melebihi gross. Contoh yang diuji: **999999.49 − 169999.00 = 830000.49**. Perhitungan `discount_value` float hanya metadata kompatibilitas respons; arithmetic diskon menggunakan cents/integer, bukan float.

| Domain | Nilai sesudah promo |
|---|---|
| WO | `total_biaya = transaksi.total`; biaya_jasa/biaya_sparepart tetap gross |
| Penjualan mobil | `diskon = transaksi.diskon`, `total_akhir = transaksi.total`; DP tidak boleh melebihi net |
| Penjualan sparepart | `diskon/total` sama dengan invoice; subtotal/detail gross dipertahankan |
| Trade-in | `selisih_bayar = transaksi.total`; nilai unit lama dan harga unit dealer tidak diganti |
| Car wash | `harga` booking tetap gross snapshot; total pembayaran membaca transaksi |

Invoice, payment summary dan laporan Owner mengambil snapshot authoritative masing-masing; sinkronisasi sale.total_akhir menghindari selisih laporan penjualan terhadap invoice baru. Data historis tidak dihitung ulang. Payment implementation tahap 2 dipertahankan, dengan satu hook pembatalan untuk release reservasi. Pengujian pelunasan servis setelah promo diubah/nonaktif membuktikan invoice/ledger tetap menggunakan diskon saat finalisasi.

## 8. Atomicity dan rollback

Test snapshot mencakup invoice, WO/booking/sale/trade, stok/history, approval, event, kendaraan/inventori, redemption, pembayaran dan notifikasi. Promo tidak ada/expired/nonaktif/belum mulai/salah scope/minimum/kuota/batas penggunaan/member tidak eligible ditolak tanpa efek parsial. Fault sesudah update promo juga rollback domain dan harga. Retry sesudah rollback membuat satu invoice dan satu mutasi stok.

Pada penjualan sparepart, draft dan invoice draft memang sudah ada sebelum finalisasi; kegagalan mengembalikannya ke keadaan draft semula, bukan menghapus draft milik pengguna. Tidak ada penghapusan invoice operasional atau koreksi transaksi historis.

Finalisasi gagal tidak membuat record pembayaran/gateway intent; wrapper tidak memanggil payment provider. Authorization, QC gagal, dan approval existing tetap dipertahankan. Repeated finalization ditolak menurut state machine existing; bukan membuat invoice/diskon/stok baru.

Audit database operasional dijalankan terpisah dengan `SET TRANSACTION READ ONLY`, begin, SELECT aggregate, dan rollback. Hasil `local / yza_automotive_laravel`: **0 invoice arithmetic mismatch**, **0 WO promo payable mismatch**, **0 penjualan mobil promo payable mismatch**, **0 penjualan sparepart promo payable mismatch**, **0 trade-in promo payable mismatch**. Bukti [YZA_P0_STAGE4_READONLY_AUDIT.json](YZA_P0_STAGE4_READONLY_AUDIT.json). Ini hasil lima pemeriksaan aggregate saat audit, bukan klaim seluruh data historis bebas anomali.

## 9. Concurrency dan idempotency

Urutan lock yang relevan:

| Jalur | Urutan |
|---|---|
| WO | WO → QC/kendaraan yang diverifikasi → sparepart berurutan ID → invoice baru → promo |
| Counter parts | Sale → detail berurutan → transaksi → sparepart berurutan → promo |
| Car sale | Mobil → aggregate inventory bila ada → booking relevan → invoice baru → promo |
| Trade-in | Pengajuan → inspeksi → mobil dealer → invoice baru → promo |
| Apply/redemption/release | Invoice → promo → ledger usage/current locking read; sinkronisasi domain dilakukan dalam boundary wrapper |
| Cancel trade/member | Domain/receipt → invoice → kendaraan relevan → promo → ledger reserved; seluruh cancel/release atomik |

Physical car yang sudah mempunyai active sale/order tidak dapat dipakai lagi, termasuk reuse booking confirmed yang sama. Aggregate inventory juga dikunci pada create penjualan agar stock/reservation tidak hanya dilindungi masing-masing alias mobil. Checkout member tetap memakai locking existing dan tidak diubah.

Quota check memakai locking read setelah lock promo sehingga melihat ledger reserved/completed terbaru meskipun transaksi caller sudah mempunyai snapshot REPEATABLE READ sebelum menunggu. Jumlah reserved + completed tidak dapat melewati kuota/per-customer limit pada penerbitan invoice karena mutator menggunakan mutex row promo yang sama. Pelunasan reservasi tidak mengambil slot kedua.

| Test independen | Hasil/invariant |
|---|---|
| Dua pelanggan, sisa kuota satu | Hanya satu invoice promo dan satu reserved commit; request lain rollback invoice/link, sebelum pembayaran |
| Dua pelunasan invoice reserved yang sama | Satu pembayaran dan satu ledger completed, memakai ID reservasi yang sama |
| Dua WO, sparepart terakhir | Satu invoice dan satu konsumsi/history; WO yang kalah tidak menyisakan invoice/promo parsial |
| Dua finalisasi WO dengan promo | Satu finalisasi, diskon satu kali dan satu konsumsi stok |
| Dua checkout direct sale untuk unit sama | Satu sale/invoice/reservasi; request lain ditolak |

Bukti: **5 tests independen**, [YZA_P0_STAGE4_CONCURRENCY.xml](YZA_P0_STAGE4_CONCURRENCY.xml). Dua proses PHP dan koneksi MySQL independen diblokir oleh lock parent, keduanya teramati READY/running tanpa DONE, lalu dilepas. Worker sengaja membentuk read snapshot sebelum mutasi untuk menguji current-read quota. Ini bukan dua pemanggilan serial. Fixture committed hanya pada database testing dan dibersihkan berdasarkan ID unik dalam finally.

Tidak menambahkan distributed lock/retry loop. Deadlock atau exception database tetap menolak operasi dan rollback; request dapat diulang setelah kegagalan. Tidak mengklaim semua kombinasi deadlock, beban tinggi atau seluruh interleaving produksi sudah dibuktikan.

## 10. Hasil focused tests

Semua PHPUnit memakai `APP_ENV=testing`, database **yza_automotive_laravel_test**, MySQL localhost, array cache/session dan queue sync. `Tests\TestCase` dan worker menolak database lain; worker memaksa konfigurasi testing dan menolak non-CLI/non-testing. Tidak ada migrate:fresh atau seed pada database operasional. Fixture membership/stock/receipt hanya dibuat/diubah di DB testing, dengan rollback atau cleanup fixture unik.

Focused filter mencakup B09 baru, concurrency, Stage14FinalQa, promo/reminder, finalisasi WO, inventory, member purchase, trade-in, car wash, payment security/concurrency tahap 2, QC/PDI/concurrency tahap 3, dan authorization tahap 1. Hasil akhir: **245 PASS / 1.363 assertions / 0 failures / 0 errors / 0 skipped**, durasi 42,243 detik. Test Stage 4 baru sendiri terdiri dari **39 tests / 148 assertions** ditambah **5 concurrency tests / 69 assertions**. Verifikasi tambahan setelah penyesuaian urutan lock: **44 tests / 217 assertions**, bukti [YZA_P0_STAGE4_RESERVATION_FOCUSED.xml](YZA_P0_STAGE4_RESERVATION_FOCUSED.xml). Bukti: [YZA_P0_STAGE4_FOCUSED.xml](YZA_P0_STAGE4_FOCUSED.xml).

Test baru menguji lima rollback sebelum/sesudah, lima positive wrappers, expired/boundary/nonaktif/future/scope/minimum/member/kuota/usage, exact money/cap, retry, fault after price write, duplicate promo/finalisasi, server price vs request palsu, DP vs net, confirmed-booking reuse, member/public exclusivity, frozen payment price, paid snapshot, invalid redeem, Owner denial, QC gagal, dan lima race. Tambahan reservasi menguji kuota/per-customer sebelum bayar, tidak ada expiry otomatis, penyelesaian slot sendiri setelah campaign berubah, penolakan release sebelum cancel, cancel/release idempotent, receipt ditolak tanpa cancel tetap reserved, pembatalan member melepaskan slot, completed tidak menjadi refund otomatis, dan rollback cancel bila release gagal. Assertion baseline tahap 1–3 tidak dimodifikasi.

## 11. Hasil full regression

Perintah: `C:\xampp\php\php.exe vendor/bin/phpunit --no-progress --do-not-cache-result --log-junit docs/qa/YZA_P0_STAGE4_FULL.xml`, dengan environment/database testing eksplisit. Bukti: [YZA_P0_STAGE4_FULL.xml](YZA_P0_STAGE4_FULL.xml). Hasil akhir: **583 tests PASS / 2.672 assertions / 0 failures / 0 errors / 0 skipped**, durasi 85,224 detik. Tambahan terhadap baseline tahap 3: 44 tests dan 217 assertions. Run akhir mencakup kebijakan reservasi dan penyesuaian urutan lock pembatalan.

PHP lint pada 12 file PHP produksi/test/audit yang diubah/ditambahkan: PASS. `git diff --check`: PASS; hanya pemberitahuan CRLF/LF pada file Owner existing. Feature tests invoice/HTTP menjadi pemeriksaan konten Blade; tidak ada klaim UAT browser atau deployment. Branch/HEAD tetap sama.

## 12. Risiko dan blocker tersisa

1. **Reservasi kuota invoice telah disetujui dan diterapkan.** Blocker keputusan tersebut ditutup. Tidak ada kedaluwarsa otomatis; reservasi dapat bertahan lama sampai pelunasan atau pembatalan transaksi yang sah. Operator mengikuti alur cancel existing; tahap ini tidak membuka route cancel baru.
2. **Invoice legacy dan provider reconciliation tetap terpisah.** Invoice sebelum kebijakan ini mungkin belum mempunyai reservation dan tidak diubah otomatis. Perlu audit/migrasi data terkontrol bila ingin menjamin slotnya. Reservasi baru mencegah oversubscription invoice baru; dana provider di luar transaksi DB tetap membutuhkan reconciliation/refund policy untuk kondisi exceptional. Tidak melakukan refund/reversal otomatis.
3. **Dual katalog price/part-specific targeting belum didefinisikan dalam model existing.** Kontrak yang dibuktikan adalah satu promo, eligibility member_only vs publik, dan penolakan diskon existing + promo. Tidak menambahkan member_price/public_price, auto-best-price, atau diskon sparepart per-item baru tanpa definisi authoritative. Promo pada WO tetap memakai scope servis dan subtotal existing.
4. **UAT browser/provider sandbox end-to-end belum dilakukan.** Diperlukan validasi lintas role, receipt/invoice/report dan callback provider pada environment UAT. Automated tests tidak menggantikan ini.
5. **Blocker tahap sebelumnya tetap berlaku**, termasuk koreksi WO legacy berinvoice tanpa proof QC, identitas fisik unit agregat, dan keputusan ownership/points/refund lintas sumber. Tidak dilakukan rekonsiliasi historis atau perbaikan modul lain otomatis.

Tidak ada anomali ditemukan dalam lima query aggregate read-only yang dijalankan; jika ditemukan anomali lain di kemudian hari, koreksinya harus terpisah dan disetujui, bukan reprice otomatis.

## 13. Status akhir

**YZA AUTOMOTIVE — P0 STAGE 4 RESULT**

- B09 status: **FIXED** pada lima wrapper yang direproduksi.
- Finalisasi tanpa promo: PASS.
- Finalisasi dengan promo: PASS pada lima domain.
- Rollback ketika promo gagal: PASS, termasuk fault setelah price update.
- Invoice consistency: PASS pada snapshot baru; audit aggregate historis yang dijalankan menemukan nol mismatch.
- Stock consistency: PASS, satu konsumsi dan rollback utuh.
- Promo quota consistency: reservasi saat invoice; kuota total/per pelanggan menghitung reserved + completed; release hanya saat transaksi dibatalkan; tanpa expiry otomatis.
- Member/public promo exclusivity: PASS untuk kontrak satu promo/member_only existing dan diskon alternatif; dual-price katalog belum didefinisikan.
- Authorization regression: PASS, termasuk seluruh test tahap 1.
- Payment regression: PASS, termasuk security dan concurrency tahap 2.
- QC/PDI regression: PASS, termasuk seluruh test tahap 3.
- Independent concurrency tests: **5 PASS / 69 assertions**.
- Focused tests: **245 PASS / 1.363 assertions**; targeted reservation 44 PASS / 217 assertions.
- Full regression: **583 PASS**.
- Total assertions: **2.672**.
- Failures/errors/skipped: **0 / 0 / 0**.
- Database operasional dimodifikasi: **NO**, audit READ ONLY saja.
- Remaining blockers: invoice legacy tanpa reservation perlu review terkontrol; reconciliation provider/refund di luar cakupan; dual-price/product targeting bila dibutuhkan; UAT dan blocker tahap terdahulu.
- Final status: **B09 FIXED & AUTOMATED REGRESSION PASS; OPERATIONAL ACCEPTANCE MASIH BERSYARAT**, terutama review invoice legacy tanpa reservation dan UAT. Tidak ada commit/push/deployment. Berhenti setelah Stage 4.
