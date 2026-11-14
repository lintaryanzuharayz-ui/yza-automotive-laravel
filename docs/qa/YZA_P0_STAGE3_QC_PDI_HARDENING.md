# YZA AUTOMOTIVE — P0 STAGE 3: QC & PDI HARDENING

Tanggal: 31 Oktober 2026. Workspace: `C:\xampp\htdocs\yza_automotive_laravel`.

## 1. Executive summary

Guard backend kini mewajibkan QC yang lengkap, sah, dan masih sesuai pekerjaan sebelum finalisasi WO. Kontrak PDI legacy menggunakan validasi checklist rinci yang sama; sepuluh boolean tidak dapat meluluskan PDI. Jadwal pickup dan handover memeriksa ulang bukti PDI, keterkaitan transaksi/pelanggan/penjualan/unit, dan identitas kendaraan. Riwayat inspeksi dipertahankan dalam penyimpanan existing tanpa migration.

Branch sebelum/sesudah: `feature/vehicle-purchase-flow`. HEAD: `0359b1e8fa28aeceac5aa8491bb7eb5d85672ea6`. Working tree sudah berisi banyak perubahan lokal sebelum tahap ini, termasuk hasil tahap 1/2. Perubahan tersebut dipertahankan; daftar perubahan Git keseluruhan bukan daftar perubahan tahap 3. Tidak ada reset, checkout, commit, push, deployment, perubahan `.env`, credential, atau write database operasional.

Laporan ini melanjutkan empat dokumen audit/tahap terdahulu yang diwajibkan. Baseline sebelumnya: 510 tests, 2.318 assertions, nol failure/error/skipped. Kesimpulan tahap ini adalah lulus verifikasi kode otomatis; bukan persetujuan deployment atau pengganti UAT.

## 2. Audit Work Order dan QC

Fitur existing sudah mempunyai enam item QC wajib, status `not_started/in_progress/failed/passed`, pemeriksa/waktu, approval pelanggan, klarifikasi, serta event pelaksanaan. WO masuk `pemeriksaan`, lalu `dikerjakan`; finalisasi membuat invoice belum bayar dan mengonsumsi stok. Pelunasan menandai servis/booking selesai. Tidak dibuat tahap pengambilan fisik servis baru.

Entry point ditemukan di `routes/web.php`: POST `/admin/servis/{servis}/finalize` dan `/petugas/servis/{servis}/finalize`, controller `OperationsServisController::finalize`, wrapper `ServisService::finalizeWithPromo`, lalu `finalizeService`. QC menggunakan POST `.../qc/start` dan PUT `.../qc`. Wizard menggunakan `WorkOrderStageService`. Tidak ditemukan route API/AJAX finalisasi terpisah; direct HTTP dan pemanggilan service tunduk guard yang sama. Pembayaran offline/gateway akhirnya menggunakan `PaymentService::sync`, yang merupakan jalur lain penetapan status servis selesai dan kini memvalidasi bukti QC sebelum perubahan status.

Model/tabel existing: `Servis`, `DetailServis`, `ServisSparepart`, `ServiceQualityControl`, `ServiceQualityControlItem`, `ServiceExecutionEvent`, approval dan assignment. Migration existing menetapkan QC unik per WO, item unik per QC/key, dan invoice unik pada WO. Tidak ada migration baru.

## 3. Audit PDI kendaraan

Alur member existing: checkout/reservasi → approve/invoice/penjualan → lunas → PDI → jadwal → siap diambil → handover → ownership/My Garage. Ada 36 item pada sembilan kelompok. Semua item existing menerima `pass/issue/na`; tidak ditemukan kebijakan per-item yang melarang N/A. Kebijakan ini dipertahankan. ISSUE wajib catatan dan tetap memblokir pickup/handover.

Model/tabel existing: `PesananMobilMember`, `PdiPesananMobil`, `SerahTerimaPesananMobil`, `PenjualanMobil`, `Transaksi`, `Mobil`, inventori agregat, `KendaraanPelanggan`. PDI dan handover masing-masing unik per pesanan. Controller admin menangani start/complete/schedule/handover; ownership baru dibuat setelah handover sah melalui `VerifiedCarOwnershipService`.

Penjualan operasional nonmember mempunyai penyelesaian melalui pembayaran dan tidak memiliki kontrak PDI member. Trade-in dan pembelian mobil bekas juga memiliki inspeksi/settlement berbeda. Jalur tersebut diaudit dari source tetapi tidak dipaksa mengikuti aturan member tanpa keputusan bisnis. Bukti PDI mengikat identitas `mobil` yang tersedia dalam schema existing; inventori agregat belum merupakan registri VIN fisik tersendiri per pesanan.

## 4. Temuan dan root cause

| Temuan | Akar Masalah | Perbaikan | Bukti Test | Status |
|---|---|---|---|---|
| B05 — finalisasi tanpa QC | Guard hanya menolak bila QC ada dan belum passed; wizard juga mengizinkan QC null | QC wajib, semua item wajib lengkap, pemeriksa sah, snapshot pekerjaan sesuai, diagnosis dan readiness diverifikasi | Reproduksi sebelum fix; invalid QC; direct POST; finalisasi sah; race | FIXED |
| B06 — PDI legacy bypass | `savePdi` menerima 10 boolean tanpa 36 item rinci | `savePdi` mendelegasikan ke `completePdi`; item hilang/result invalid ditolak | Legacy boolean, incomplete, ISSUE, PASS/N/A, purchase regression | FIXED |
| B07 — role QC ditolak PDI | Route PDI dipetakan ke `sales.manage` | Aksi PDI memakai `qc.manage`; akses baca workflow untuk QC; sales/handover tetap terpisah | Quality Control dapat PDI, Owner/mechanic/advisor ditolak PDI, QC ditolak handover | FIXED |
| QC usang/identitas pemeriksa tidak sah | Status passed saja tidak membuktikan pekerjaan yang diperiksa | Snapshot event, validasi role aktif, invalidasi jasa/part/temuan/approval | Stale diagnosis, item diubah/dihapus, invalidasi dan riwayat | FIXED |
| PDI usang atau unit/pesanan berbeda | Status lulus saja tidak membuktikan keterkaitan dan isi inspeksi | Proof terikat order/sale/tx/customer/car/checklist/inspector/time; validasi ulang pickup/handover | Vehicle identity/replacement, order mismatch, forged checklist, reinspection | FIXED |
| Pembayaran dapat menyelesaikan WO yang invoice-nya ditautkan langsung tanpa QC | Sinkronisasi status pembayaran tidak memeriksa QC | Tambah validasi QC tepat sebelum servis selesai, tetap dalam transaksi pembayaran | Payment bypass rollback; webhook duplicate points/notification regression | FIXED |
| B09 — wrapper promo tidak atomik | Finalisasi domain commit sebelum penerapan promo | Tidak diubah sesuai batasan tahap 3 | Temuan source/audit terdahulu tetap berlaku | OPEN, di luar tahap ini |

## 5. Reproduksi sebelum perbaikan

`QcPdiHardeningStage3Test` dibuat dan dijalankan sebelum guard diperbaiki, pada database testing terpisah. Dua test mengharapkan penolakan: finalisasi WO tanpa QC, dan PDI dengan sepuluh boolean true tanpa checklist rinci. Hasil: **2 tests / 2 assertions / 2 failures / 0 errors**, karena exception yang diharapkan tidak terjadi. Bukti tersimpan di [YZA_P0_STAGE3_BEFORE.xml](YZA_P0_STAGE3_BEFORE.xml). Kedua test sekarang lulus.

## 6. Daftar file yang diubah dalam tahap 3

| File | Perubahan tahap ini |
|---|---|
| `app/Services/InspectionIntegrityService.php` (baru) | Identitas/otorisasi pemeriksa, snapshot QC, guard, invalidasi |
| `app/Services/VehiclePdiIntegrityService.php` (baru) | Binding, proof, riwayat dan guard PDI |
| `app/Services/ServiceExecutionService.php` | Gate wajib QC, validasi inspector/readiness/item ownership, snapshot event, invalidasi |
| `app/Services/ServisService.php` | Invalidasi saat diagnosis/detail berubah; guard finalisasi existing memakai gate baru |
| `app/Services/ServisSparepartService.php` | Invalidasi saat draft part ditambah/diubah/dihapus |
| `app/Services/WorkOrderStageService.php` | Akses finalisasi wizard mengikuti gate backend |
| `app/Services/PaymentService.php` | Validasi bukti QC sebelum status servis selesai |
| `app/Services/MemberVehiclePurchaseService.php` | Kontrak PDI tunggal, inspector, riwayat/proof, validasi pickup/handover |
| `app/Http/Middleware/RoleModuleAccessMiddleware.php` | Perbaikan B07 khusus PDI dan akses baca QC |
| `app/Http/Controllers/Admin/MemberVehiclePurchaseController.php` | AuthorizationException tetap menjadi penolakan akses |
| `resources/views/admin/pesanan-mobil/index.blade.php` | Badge mengikuti status PDI, pilihan checklist awal kosong, validasi ulang PDI usang |
| `tests/Feature/QcPdiHardeningStage3Test.php` (baru) | Reproduksi, negative/positive QC/PDI, role, rollback, stale/reinspection |
| `tests/Feature/InspectionConcurrencyStage3Test.php` (baru) | Empat race menggunakan proses/koneksi independen |
| `tests/Support/inspection_concurrent_worker.php` (baru) | Worker terbatas CLI/testing, guard nama DB |
| `tests/Support/CompletesQualityChecks.php` (baru) | Fixture QC/PDI melalui layanan sah |
| `tests/Feature/ServisFinalizationStage5CTest.php` | Fixture menyiapkan QC sebelum finalisasi positif/stock failure |
| `tests/Feature/ServicePaymentStage5DTest.php` | Fixture QC sebelum invoice payment |
| `tests/Feature/MemberVehiclePurchaseFlowTest.php` | Fixture menggunakan checklist rinci, termasuk ISSUE |
| `tests/Feature/VehicleAggregateInventoryFlowTest.php` | Fixture PDI rinci |
| `tests/Feature/PaymentSecurityStage2Test.php` | Fixture callback servis mempunyai QC sah sebelum invoice ditautkan |
| `docs/qa/YZA_P0_STAGE3_*.xml` dan laporan ini | Bukti sebelum fix, focused, concurrency, full regression |

Assertion bisnis existing tidak dihapus atau dilemahkan. Fixture QC memakai pemeriksa legacy yang sah dan layanan start/update sebenarnya; tidak menonaktifkan gate produksi. Perubahan working tree lain di luar tabel di atas berasal dari pekerjaan existing.

## 7. Aturan validasi QC

Finalisasi memerlukan status WO `dikerjakan`, belum ada invoice, diagnosis terisi, approval yang diperlukan disetujui/diterapkan, tidak ada klarifikasi unresolved, serta draft part memenuhi readiness/stok existing. Tidak dibuat kewajiban baru bahwa semua servis harus mempunyai jasa/part berbayar.

QC harus ada dan `passed`, mempunyai checked_by/checked_at, serta enam key wajib dengan hasil `PASS` atau `N/A`. FAIL atau null menolak finalisasi. User dan petugas pemeriksa harus aktif serta memenuhi `WorkOrderAuthorizationService`. Actor payload harus sama dengan petugas principal yang login. Mekanik tetap membutuhkan assignment aktif untuk kerja teknis dan tidak memperoleh hak QC; Owner tetap read-only. Hak QC Service Advisor/manager yang sudah diizinkan tahap 1 tetap mengikuti service authorization existing.

Event keputusan QC terakhir harus `qc_passed`, dengan metadata yang sama dengan record/checklist dan hash pekerjaan saat ini. Snapshot memuat diagnosis, relasi pelanggan/kendaraan, identitas kendaraan, jasa/harga, part/quantity/harga, temuan, approval dan applied links. Stok aktual, invoice, dan status selesai tidak masuk hash sehingga konsumsi stok/pelunasan yang sah tidak membatalkan QC.

Mutasi teknis menyimpan event `qc_invalidated` dengan snapshot sebelumnya lalu mereset hasil checklist untuk diperiksa ulang. Event failed/passed tetap tersimpan, termasuk actor pada event. Perubahan langsung terhadap diagnosis/hasil/identitas yang tidak melewati mutator tetap terdeteksi melalui pembandingan snapshot sebelum finalisasi/pelunasan. Item QC milik WO lain ditolak, bukan diabaikan diam-diam.

## 8. Aturan validasi PDI

Start/complete hanya sesudah lunas dan sebelum status terminal. Inspector aktif dengan `qc.manage` atau super admin/legacy petugas yang memang diizinkan. Principal login tidak dapat mengatasnamakan inspector lain. Tidak ada grant sales.manage baru untuk QC. Schedule/handover juga memeriksa actor workflow yang berwenang; caller konsol tanpa principal tetap merupakan jalur trusted existing.

Semua 36 item harus hadir dengan hasil valid. ISSUE memerlukan catatan; ada ISSUE menghasilkan `perlu_perbaikan/pdi_gagal`, bukan lulus. PASS dan N/A sah sesuai kontrak existing. Payload sepuluh boolean tanpa checklist tidak dapat menyelesaikan inspeksi.

Proof internal di JSON checklist `_integrity` mengikat order, pelanggan, transaksi, penjualan, mobil, snapshot pilihan kendaraan, VIN/nomor mesin, seluruh hasil checklist, status/result, pemeriksa, dan waktu. Pickup/handover menguji ulang checklist, inspector, proof, dan konsistensi foreign key. Data mobil untuk snapshot menggunakan row yang telah dikunci. Perubahan signifikan memerlukan pemeriksaan ulang oleh petugas berwenang.

`_history` menyimpan kondisi PDI sebelumnya, hasil rinci, proof terdahulu, legacy flags, pemeriksa, waktu dan catatan sebelum penggantian hasil. History tidak menerima data history/proof dari payload. Ini jejak aplikasi dalam JSON existing, bukan ledger anti-tamper terhadap administrator database. PDI lama yang belum mempunyai proof ditolak pada gate baru sampai diperiksa ulang; tidak ada backfill yang mengarang hasil inspeksi.

## 9. Perbaikan finalisasi

Finalisasi mengambil lock WO, memeriksa authorization/status/QC, memvalidasi dan mengonsumsi stok melalui layanan inventory existing, membuat satu invoice dan menautkan WO, dalam transaksi database existing. Pengulangan finalisasi ditolak tanpa efek baru. Perhitungan decimal dan snapshot harga existing dipertahankan.

UI wizard kini memanggil gate yang sama sebelum membuka finalisasi. Penolakan backend tetap berlaku pada POST langsung. Pembayaran offline dan Midtrans tetap memakai alur tahap 2; satu tambahan terkait bukti bypass adalah validasi QC sebelum servis ditandai selesai. Kegagalan validasi itu me-rollback perubahan pembayaran yang berada dalam transaksi yang sama.

## 10. Concurrency dan idempotency

Mutasi teknis, QC, dan finalisasi mengunci WO yang sama. Snapshot QC juga mengunci kendaraan. Start/complete PDI, schedule dan handover mengunci pesanan yang sama, lalu record inspeksi dan mobil relevan. Validasi dilakukan setelah lock. Unique constraints existing dipertahankan; tidak ditambahkan mekanisme distributed lock.

| Race nyata | Bukti koneksi independen | Invariant yang diuji |
|---|---|---|
| Dua finalisasi WO | Dua worker PHP, parent menahan lock lalu melepaskan keduanya | Satu sukses; satu penolakan; satu invoice/link dan satu konsumsi stok/history |
| Perubahan pekerjaan vs finalisasi | Dua worker pada WO sama | Bila mutasi menang, QC invalid dan tidak ada invoice; bila finalisasi menang, mutasi ditolak |
| Dua handover | Dua worker pada pesanan sama | Keduanya menerima hasil operasi yang sama; satu ownership dan satu handover |
| PDI ISSUE vs handover | Dua worker pada pesanan sama | ISSUE menang: handover ditolak; handover menang: perubahan PDI ditolak karena terminal |

Keempat kasus lulus: **4 tests / 47 assertions**. Worker menggunakan proses dan koneksi MySQL terpisah, bukan dua pemanggilan berurutan dalam satu transaksi PHPUnit. Fixture yang perlu terlihat lintas koneksi dikomit hanya pada database testing, mempunyai identitas acak, dan dibersihkan di finally berdasarkan ID fixture. Ini membuktikan invariant skenario tersebut, bukan semua kombinasi interleaving/load produksi.

Handover berulang mengembalikan record existing sebelum efek inventory/ownership/notifikasi diulang. Test menguji ownership dan jumlah notifikasi. Fault injection saat finalisasi menguji rollback stock/history/invoice/link; fault injection saat pembuatan ownership menguji rollback sale/order/handover/inventory/notifikasi.

## 11. Hasil focused tests

Database: hanya `yza_automotive_laravel_test` di localhost, `APP_ENV=testing`, array cache/session dan queue sync. TestCase menolak nama DB selain database testing. Worker juga menolak non-CLI/non-testing dan memeriksa nama DB setelah bootstrap. Tidak menjalankan migrate:fresh/seed pada database operasional. Staff seeding hanya fixture dalam transaksi testing.

Focused suite akhir: **179 tests / 1.044 assertions / 0 failures / 0 errors / 0 skipped**. Mencakup QC/PDI baru (25 tests / 90 assertions), concurrency (4 / 47), ServiceExecutionPhase4A (11 / 55), ServisFinalizationStage5C (21 / 49), member purchase (23 / 123), ServicePaymentStage5D (21 / 45), aggregate inventory (2 / 15), AuthorizationHardeningStage1 (18 / 355), dan PaymentSecurityStage2 (54 / 265). Bukti: [YZA_P0_STAGE3_FOCUSED.xml](YZA_P0_STAGE3_FOCUSED.xml).

Coverage utama: absent/incomplete/failed/stale/forged QC; mandatory item dihapus; cross-WO item; valid N/A; direct POST tanpa QC; role tidak sah; invoice/payment bypass; invalidasi dan re-QC; repeated finalization; stok/rollback; PDI absent/incomplete/ISSUE/PASS/N/A; vehicle identity/replacement/order mismatch; forged checklist; stale reinspection/history; unpaid gate; duplicate handover; rollback ownership; race independen. Pengujian tahap 1/2 juga mempertahankan assignment/Owner serta webhook signature, inquiry, replay, membership, points dan notification assertions existing.

## 12. Hasil full regression

Perintah akhir menggunakan `C:\xampp\php\php.exe vendor/bin/phpunit --no-progress --do-not-cache-result --log-junit docs/qa/YZA_P0_STAGE3_FULL.xml`, dengan environment testing dan database testing eksplisit. Tidak mengubah phpunit config/.env. Bukti: [YZA_P0_STAGE3_FULL.xml](YZA_P0_STAGE3_FULL.xml).

Hasil akhir: **539 tests PASS / 2.455 assertions / 0 failures / 0 errors / 0 skipped**, durasi 77,798 detik. Dibanding baseline, terdapat 29 tests dan 137 assertions tambahan; jumlah assertions baseline dipertahankan.

PHP lint dijalankan pada 19 file PHP produksi/test yang diubah/ditambahkan pada tahap ini. `git diff --check` lulus; Git hanya memberi pemberitahuan CRLF/LF pada file Owner existing yang bukan perubahan tahap 3. Rendering Blade tercakup pada feature test HTTP, tanpa klaim inspeksi visual browser.

## 13. Dampak terhadap modul lain

Tidak mengubah client/prosesor Midtrans, signature/inquiry, aturan membership atau promo, perhitungan invoice, stock service, maupun penciptaan ownership. Guard completion pembayaran adalah perubahan sempit yang terkait langsung bypass QC yang dibuktikan. Fixture webhook servis ditingkatkan agar mempunyai bukti QC sah dan seluruh assertion points/notifikasi tetap dipertahankan.

RBAC hanya disesuaikan untuk B07 yang terverifikasi: aksi PDI menerima permission QC dan akses baca workflow tersedia untuk role tersebut. Hak approve, schedule, handover, finance atau inventory tidak diberikan kepada QC. Penjualan nonmember/trade-in tidak mendapat persyaratan PDI baru. UI terbatas pada informasi status, pilihan hasil eksplisit dan jalur validasi ulang; tidak ada redesign.

## 14. Risiko dan blocker tersisa

1. **B09 masih OPEN**: wrapper finalize/create + promo mempunyai transaction boundary terpisah. Rollback yang dibuktikan pada tahap ini berlaku pada finalisasi dasar/handover, bukan jaminan atomicity wrapper promo. Perbaikan promo dilarang pada tahap ini dan tidak dikerjakan.
2. **UAT browser operasional belum dilakukan**. Perlu validasi nyata alur mechanic → QC → advisor/finalisasi → pembayaran, serta QC/PDI → sales pickup/handover, dengan akun dan unit UAT.
3. **Inspeksi historis tanpa proof** harus diperiksa ulang oleh role sah sebelum gate baru dilalui. Tidak ada rewrite massal, migrasi data atau pelulusan otomatis. Untuk WO belum difinalisasi dan PDI belum diserahkan, pemeriksaan ulang memakai alur existing. **WO legacy yang sudah memiliki invoice tetapi belum selesai tanpa proof**, atau kehilangan validitas QC sesudah invoice, akan tertahan pada completion pembayaran; alur existing tidak mengizinkan re-QC setelah invoice. Penyelesaian kasus tersebut membutuhkan keputusan koreksi operasional yang terkontrol, tidak dibuat bypass atau pembatalan invoice otomatis pada tahap ini. WO historis yang sudah selesai tetap dibaca sebagai histori; tidak ada pembukaan ulang otomatis.
4. **Inventori agregat** masih memakai identitas mobil existing. Penentuan VIN fisik per unit pesanan serta bukti fisik dokumen/penerima memerlukan proses bisnis/schema tersendiri bila dibutuhkan; tahap ini tidak mengarang alokasi unit baru. Handover existing masih mengisi konfirmasi dokumen/kendaraan sesuai kontrak existing.
5. **Nonmember/legacy/trade-in** belum mempunyai definisi PDI member yang sama. Kewajiban PDI untuk jalur tersebut memerlukan keputusan bisnis; tidak dimasukkan secara spekulatif.
6. Proof/history berbasis database melindungi kontrak aplikasi dan mendeteksi ketidaksesuaian snapshot, bukan administrator database yang dapat menulis ulang seluruh record dan jejak secara langsung. Data operational tidak dimodifikasi untuk mencoba skenario tersebut.

## 15. Status akhir

**YZA AUTOMOTIVE — P0 STAGE 3 RESULT**

- WO tanpa QC: DITOLAK.
- WO dengan QC gagal: DITOLAK.
- WO dengan QC lulus: BERHASIL jika seluruh syarat lain sah.
- QC authorization: LULUS; assignment dan batasan tahap 1 dipertahankan.
- Finalisasi idempotent: efek bisnis tepat satu; replay ditolak.
- PDI incomplete: DITOLAK.
- PDI ISSUE: disimpan perlu_perbaikan; pickup/handover DITOLAK.
- PDI PASS: BERHASIL; N/A sah tetap didukung.
- PDI authorization: LULUS; QC dapat menjalankan tugas tanpa hak sales/handover.
- Handover tanpa PDI: DITOLAK.
- Handover idempotency: LULUS; satu handover/ownership, notifikasi tidak diulang.
- Database transaction safety: LULUS pada finalisasi dasar/handover dan empat race; pengecualian B09 tetap OPEN.
- Focused tests: **179 PASS / 1.044 assertions**, termasuk 4 race independen.
- Full regression: **539 PASS / 0 errors / 0 skipped**.
- Total assertions: **2.455**.
- Failures: **0**.
- Production DB modified: **NO**.
- Remaining blockers: B09; UAT browser; pemeriksaan ulang record historis tanpa proof dan koreksi WO legacy yang sudah berinvoice; keputusan fisik-unit agregat dan PDI nonmember bila diperlukan.
- Final status: **STAGE 3 IMPLEMENTED — AUTOMATED VERIFICATION PASS; OPERATIONAL/PRODUCTION ACCEPTANCE MASIH BERSYARAT**. Tidak ada deployment. Berhenti setelah tahap 3.
