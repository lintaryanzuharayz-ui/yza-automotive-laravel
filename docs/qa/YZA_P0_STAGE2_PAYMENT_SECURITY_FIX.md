# YZA Automotive — P0 Stage 2: Midtrans Payment Security & Membership Lifecycle

Tanggal: **31 Oktober 2026 (Asia/Jakarta)**  
Project: `C:/xampp/htdocs/yza_automotive_laravel`  
Branch/HEAD aktual awal dan akhir: **feature/vehicle-purchase-flow / 0359b1e8fa28aeceac5aa8491bb7eb5d85672ea6**  
Status: **B03/B04 FIXED & VERIFIED pada pengujian terisolasi; implementasi keamanan tahap 2 selesai dalam cakupan ini.** Production, rekonsiliasi historis, dan kebijakan bisnis refund/reversal tetap mempunyai blocker terpisah.

## 1. Hasil audit pembayaran existing

Dokumen audit utama, inventory bukti route, laporan P0 tahap 1 dan implementasi saat ini diperiksa. Working tree masih memuat perubahan lokal Owner/Admin dan P0 tahap 1; tidak dilakukan reset/checkout/commit. Isi enam file payment/test target disimpan sebagai snapshot sebelum perubahan; file lain dibaca sesuai dependensi. Perubahan dilakukan pada working tree aktual.

Integrasi existing dipertahankan:

- `MidtransController`: notification publik, create attempt, return kendaraan dan inquiry pelanggan.
- `MembershipController`: activation page, pay, finish dan sync membership.
- `Admin/PaymentManagementController::sync`: inquiry finance melalui izin existing.
- `MidtransClient`: Snap create dan Get Status memakai HTTP Basic Auth server key, timeout dan endpoint sandbox dari config.
- `MidtransPaymentService` dan `MembershipPaymentService`: dua tujuan pembayaran yang berbeda; membership tidak dijadikan penjualan kendaraan.
- `PaymentService`: penerimaan transfer baru kemudian verify receipt gateway; pembayaran offline existing tetap manual.
- Invoice kendaraan/servis/cuci/sparepart/trade-in membaca transaksi/pembayaran existing. Tidak ada invoice dihapus atau diganti.
- Notifikasi dan points adalah record DB dari service existing, bukan outbound email/job baru. Tidak ditemukan job/listener payment terpisah yang perlu dimigrasi pada snapshot.
- Route `POST /payment/midtrans/notification` tetap satu callback publik dengan signature; CSRF exception existing tidak diperluas.
- Route create/sync/finish pelanggan tetap menggunakan auth, role/membership/ownership guard existing. Route finance tetap dilindungi otorisasi tahap 1.

Schema ditinjau melalui migration/model dan pembacaan DB read-only: pembayaran_gateway, event_pembayaran_gateway, memberships, pembayaran, transaksi, hubungan order dan invoice. Unique `order_id`, unique `event_key`, unique gateway receipt pointer, unique membership per customer, FK dan row lock existing cukup untuk perubahan ini; **tidak diperlukan migration atau status enum baru**. `status_provider` sudah berupa varchar dan `requires_review` sudah dipakai sebelumnya.

## 2. Root cause B03/B04 sesuai audit asli

| ID | Deskripsi audit asli | Akar masalah terkonfirmasi |
|---|---|---|
| **B03** | Keputusan sukses capture/fraud pada membership dan transaksi | Kedua service memakai settlement OR (capture AND fraud_status != deny). Challenge, nilai tak dikenal atau field yang hilang bisa lolos; status_code tidak diwajibkan 200 |
| **B04** | Konsistensi gateway membership saat webhook terlambat | MembershipPaymentService menulis ulang gateway tanpa membedakan state belum dibayar, sudah diproses, pending terlambat, dan transisi pascapembayaran |

Temuan tambahan dalam jalur yang sama: inquiry menimpa order_id respons dengan order lokal dan mengisi gross_amount/status_code sukses jika provider tidak mengirimkannya; event identity tidak membedakan perubahan fraud; createAttempt kendaraan memakai lockForUpdate tanpa outer transaction; status pascapembayaran diabaikan seluruhnya; error response provider dicatat sebagai raw body pada log.

## 3. Bukti reproduksi sebelum perbaikan

Dua tes pertama dijalankan pada kode payment lama, DB testing terpisah:

1. Capture + fraud challenge: expected membership pending_payment, actual **active**.
2. Settlement kemudian pending: expected gateway settlement, actual **pending**.

Hasil: **2 tests, 2 assertions, 2 failures, 0 errors, 0 skipped**. Ini reproduksi yang diharapkan, bukan hasil regresi akhir. Bukti: [YZA_P0_STAGE2_BEFORE.xml](YZA_P0_STAGE2_BEFORE.xml).

Kedua kasus sekarang tercakup ulang pada suite akhir bersama jalur transaksi kendaraan/layanan yang berbagi keputusan pembayaran.

## 4. Dasar dokumentasi resmi dan perubahan source

Dokumentasi resmi diperiksa pada 31 Oktober 2026:

- [HTTP(S) Notification / Webhooks](https://docs.midtrans.com/docs/https-notification-webhooks): success status_code 200; capture/settlement; fraud harus accept bila tersedia. Metode nonkartu tertentu memang tidak mengirim fraud_status. Signature dan idempotency tetap diperlukan.
- [Get Transaction Status](https://docs.midtrans.com/reference/get-transaction-status): inquiry server-to-server mengembalikan identitas order, nominal, status dan metode. Hasilnya harus divalidasi, bukan dilengkapi menjadi sukses dari nilai lokal.
- [Transaction Status Cycle](https://docs.midtrans.com/docs/transaction-status-cycle): capture dapat dilanjutkan settlement/cancel; settlement dapat berlanjut refund/chargeback dan pada kasus provider tertentu reversal ke deny. Karena itu semua terminal tidak dibekukan secara mutlak.

Implementasi memakai persyaratan lebih konservatif untuk kartu: capture memerlukan credit_card + fraud accept. Settlement kartu tanpa fraud dianggap bukti belum lengkap. Settlement nonkartu dapat sah tanpa field fraud; bila fraud dikirim tetapi kosong/null/challenge/deny/tak dikenal, tidak diakui sukses. Ini bukan aturan “semua metode wajib fraud_status”.

| File | Perubahan |
|---|---|
| app/Services/MidtransGatewayProcessor.php | Baru; verifikasi canonical inquiry, purpose/owner/order/amount binding, state mapping, audit events, locking dan atomic application bersama |
| app/Services/MidtransPaymentService.php | Delegasi notification/inquiry ke processor bersama; createAttempt dibungkus DB transaction |
| app/Services/MembershipPaymentService.php | Delegasi proses bersama; ownership sync, batas attempt, stable attempt reuse, log exception class |
| app/Services/MidtransClient.php | Validasi field signature dan bentuk JSON inquiry; raw provider response tidak dicetak ke log |
| app/Exceptions/MidtransInquiryException.php | Baru; membedakan kegagalan inquiry sementara dari callback invalid |
| app/Services/GatewayPaymentGuard.php | Konflik/review/refund menahan attempt baru dan pembayaran manual sampai rekonsiliasi |
| app/Http/Controllers/MidtransController.php | Inquiry unavailable/connection failure menghasilkan 503 yang bisa di-retry; callback invalid tetap 400 |
| app/Http/Controllers/MembershipController.php | Finish order ID asing ditolak; tidak fallback diam-diam; log tidak memakai raw exception message provider |
| tests/Feature/PaymentSecurityStage2Test.php | Baru; 54 cases termasuk 40 kombinasi status/fraud dan integration replay |
| tests/Feature/PaymentConcurrencyStage2Test.php | Baru; empat kasus dua worker/koneksi MySQL independen |
| tests/Support/midtrans_concurrent_worker.php | Baru; CLI-only worker khusus test DB dan synthetic HTTP provider |
| tests/Feature/MidtransSandboxStage11ATest.php | Fake inquiry dilengkapi order_id; callback tests diberi fake Get Status sesuai verifikasi baru; assertion lama dipertahankan |

Tidak ada perubahan role/permission DB, .env, server/client key, config Midtrans, URL callback production, UI, QC, promo, inventory, state machine PDI/handover, atau ownership.

## 5. Payment state machine setelah perbaikan

Data yang mengubah pembayaran adalah **hasil Get Status yang diautentikasi ke provider**, bukan klaim browser atau field status callback semata. Notification wajib lolos signature lebih dahulu, lalu canonical inquiry diperiksa ulang.

| Kondisi canonical provider / internal | Hasil gateway dan domain |
|---|---|
| Pending / authorize | Belum memberi akses atau receipt; authorize tidak diperlakukan captured dalam konfigurasi existing |
| Capture + credit_card + accept + code 200 + identitas lengkap | Sukses jika target, nominal, kepemilikan dan invoice/member state eligible |
| Capture challenge/deny/absent/unknown fraud | Tidak aktif/lunas; challenge tetap pending verifikasi; deny tidak diakui receipt |
| Settlement code 200, noncard tanpa fraud | Dapat sukses bila payment_type, transaction_id, order, target dan nominal sah |
| Settlement kartu tanpa fraud; fraud tidak accept; code bukan 200 | Tidak sukses; bukti provider disimpan, tanpa aktivasi/receipt |
| Unknown/incomplete payload, identity/amount/currency mismatch | requires_review; tidak mengisi sendiri order_id/gross/status_code menjadi sukses |
| Invoice cancelled/lunas oleh attempt lain atau pending transfer offline | requires_review; tidak membuat penerimaan tambahan atau verify transfer offline |
| Membership cancelled atau sudah aktif dari attempt lain | requires_review; tidak reaktivasi/overwrite linkage/tanggal aktif |
| Callback duplikat pada paid attempt | Receipt dan activation tidak diulang; event identik tidak digandakan |
| Paid lalu pending/authorize | State paid dipertahankan; tidak turun ke pending |
| Capture sah → settlement sah | Gateway maju ke settlement tanpa receipt, poin, notifikasi atau activation kedua |
| Failure/expire sebelumnya kemudian bukti inquiry sukses sah | Dapat diproses setelah seluruh invariant diverifikasi; failed bukan larangan permanen untuk inquiry yang membuktikan pembayaran |
| Paid → refund/partial_refund/chargeback/partial_chargeback atau reversal/cancel canonical | Status provider dicatat; local review event + warning dibuat; tidak otomatis menghapus receipt, membalik stok/poin atau mencabut membership |
| Post-payment refund/reversal lalu pending/success lama | Tidak menghidupkan ulang state sebelum refund/reversal |

`requires_review` karena konflik binding/nominal/receipt/manual dipertahankan sebagai hold untuk investigasi, bukan otomatis dilepas oleh browser atau replay webhook. Jalur pembayaran baru/manual juga ditahan. Belum menambahkan fitur approval/release hold: penyelesaiannya mengikuti rencana rekonsiliasi terpisah di bawah.

Status provider pascapembayaran **tidak disamakan dengan belum dibayar**. Internal invoice/receipt historis tetap tersimpan untuk audit. Kebijakan refund/reversal dan suspensi akses member tidak ditetapkan sepihak; ini blocker operasional yang eksplisit.

## 6. Perbaikan fraud challenge

- Predicate permisif “fraud != deny” dihapus dari kedua jalur.
- Shared processor memakai aturan identik untuk membership dan transaksi existing.
- Card capture harus fraud accept, code 200, metode credit_card dan provider transaction ID.
- Null/empty/unknown fraud tidak otomatis diterima.
- Metode nonkartu tanpa field fraud tetap diuji sebagai success yang sah saat settlement; menghindari false rejection menyeluruh.
- Callback dengan status/fraud dimodifikasi tetapi signature order/code/amount masih cocok tidak dapat memalsukan sukses: processor memakai hasil inquiry aktual.
- Tidak ada aktivasi dari query finish `transaction_status=settlement` atau status frontend.

## 7. Perbaikan webhook dan inquiry

1. Signature SHA-512 tetap memakai order_id + status_code + gross_amount + server key dan hash_equals; input nonscalar atau signature invalid ditolak sebelum inquiry.
2. Order harus dikenal dan purpose harus cocok: membership XOR transaksi, provider midtrans, prefix order sesuai target, initiated_by cocok dengan customer target.
3. Inquiry harus mengembalikan order_id yang sama, gross_amount dan status_code yang lengkap; respons tidak lagi ditimpa dengan nilai lokal.
4. Nominal dibandingkan sebagai integer sen dari string decimal yang tervalidasi; bukan perbandingan float permisif.
5. Membership memeriksa snapshot activation_fee. Invoice memeriksa tagihan authoritative dan verified incoming balance. Jenis transaksi wajib berada dalam TYPES existing; pembayaran outgoing tidak dicampurkan.
6. Payment type/transaction ID yang sudah diproses terikat pada receipt; perubahan identitas setelah paid ditandai.
7. Metode Snap tidak dipilih bebas oleh callback: canonical payment_type dari inquiry yang dipakai. Target/invoice purpose berbeda dari jenis kanal pembayaran.
8. Unknown order/signature invalid mendapatkan 400 tanpa mutasi. Provider unavailable/connection failure mendapatkan 503 agar dapat dikirim ulang. Browser inquiry gagal tidak mengaktifkan member.
9. Log tidak menyimpan server key, signature, Snap token, card response atau raw body provider. Warning konflik hanya memuat gateway internal ID dan kode alasan.
10. Return kendaraan tetap melakukan ownership scoping dan inquiry server; membership finish dengan order ID asing ditolak. Tidak ada perubahan URL callback atau merchant configuration.

Get Status menambah satu request provider per callback valid. Ini tradeoff keamanan yang sengaja dipilih karena signature tidak mencakup semua field status/fraud. Latensi, throughput, timeout dan real provider retry perlu UAT staging; tidak diklaim sudah dibuktikan dengan provider nyata.

## 8. Lifecycle membership setelah perbaikan

Registrasi masih membuat membership pending_payment dengan activation_fee existing. createAttempt hanya untuk customer pemilik yang aktif dan status pending_payment; active/cancelled/review tidak membuat pembayaran baru diam-diam.

Alur: pending registration → Snap attempt/reuse → signature + canonical inquiry → target/amount/fraud/state check → DB transaction mengaktifkan membership dan menyimpan gateway/processed_at → Member Center melalui middleware existing.

- Webhook dapat mengaktifkan tanpa browser kembali.
- Finish hanya melakukan inquiry atas order milik customer; query browser bukan bukti pembayaran.
- Status active, activated_at dan gateway_payment_id ditulis sekali; replay tidak memperpanjang tanggal.
- Tidak menambah expiry/renewal/lifetime promise baru; schema existing hanya mempunyai activation date/status.
- Fault injection saat activation membuktikan gateway status, event dan membership rollback bersama. Retry sukses kemudian mengaktifkan sekali.
- Attempt berbeda untuk membership yang sudah aktif menghasilkan review; tidak menimpa paid attempt pertama.
- Membership receipt tetap berupa record gateway/linkage existing, **bukan** transaksi penjualan kendaraan. Belum membuat ledger pendapatan membership baru.
- Aturan kendaraan YZA tetap terpisah: membership aktif tidak membuat ownership; PDI/handover/My Garage tidak diubah.

## 9. Pembayaran kendaraan dan pembayaran offline

Regression mencakup checkout snapshot, invoice, manual transfer pending/reject/recovery, online inquiry finish, pelunasan, PDI, pickup, handover dan verified ownership. Tidak ada logic PDI/handover yang diganti.

Shared processor hanya membuat **receipt gateway baru** dari dana provider yang valid. Record transfer offline yang sudah ada tidak di-verify oleh callback. Jika pending offline atau saldo invoice tidak cocok, gateway masuk review. Duplicate/late callback tidak membuat invoice atau receipt tambahan.

Pembayaran offline sah tetap melalui otorisasi/verifikasi existing. Review/refund hold mencegah masuknya dana tambahan sampai konflik diselesaikan, bukan mengesahkan transfer offline otomatis.

Regresi P0 tahap 1 ikut lulus dalam full suite; tidak mengubah middleware RBAC, assignment service atau authorization test tahap 1.

## 10. Idempotency, transaction dan concurrency

- Lock order: **membership/transaksi target → gateway → event/receipt**. Dua gateway berbeda untuk invoice/member yang sama juga terserialisasi.
- Payment status, events, receipt, activation, invoice/order updates, points dan DB notifications berada di transaksi DB yang sama. Tidak ada queue/outbound notification sebelum commit yang baru diperkenalkan.
- Event memakai existing unique event_key dengan fingerprint sumber + field aman, termasuk fraud dan ringkasan refund. Dua partial refund dengan nominal/referensi berbeda tidak dianggap replay identik.
- Callback dan inquiry dicatat sebagai dua sumber berbeda. LOCAL requires_review event adalah keputusan aplikasi, bukan klaim status provider.
- Event existence menggunakan locking read di bawah target lock; tidak hanya `if exists` tanpa sinkronisasi.
- Ref target dibaca sebelum transaksi; pengecekan ulang binding dilakukan setelah lock. Ini menghindari stale consistent-read snapshot InnoDB sebelum menunggu lock callback lain.
- DB transaction retry tersedia untuk konflik/deadlock lokal. Inquiry HTTP dilakukan sebelum transaksi application; retry transaksi lokal tidak mengirim dana/provider mutation.
- Receipt pointer dan state paid dipakai untuk menghindari pemanggilan ulang PaymentService/points/notifications.

**Uji bersamaan nyata:** dua proses PHP dan dua koneksi MySQL independen; parent menahan row target sampai kedua worker READY dan terblokir, lalu melepas lock. Empat kasus: duplicate membership, duplicate invoice, dua attempt membership dan dua attempt invoice. Hasil: tepat satu paid attempt/receipt atau satu linkage aktivasi; attempt kedua konflik masuk review; tidak ada event key duplikat.

Provider pada worker tetap HTTP fake. Ini bukti concurrency database lokal, bukan dua callback dari server Midtrans nyata.

Selama pengembangan, uji concurrency menemukan unique-event race pada consistent snapshot; diperbaiki lewat locking event read dan target-first transaction boundary. Cleanup fixture awal sempat tertahan FK pembayaran; diperbaiki sesuai urutan FK dan dua fixture testing buatan tahap ini dibersihkan secara spesifik. Tidak ada truncate/wipe/reset atau cleanup data operasional. Verifikasi akhir: nol user fixture concurrency tersisa.

## 11. Focused testing

[Hasil focused final](YZA_P0_STAGE2_FOCUSED.xml): **105 tests / 501 assertions / 0 failures / 0 errors / 0 skipped**, console 14,830 detik.

Cakupan focused:

- 40 kombinasi purpose × status/fraud, termasuk card accept/challenge/deny/missing, noncard settlement tanpa fraud, unknown/empty, code salah, pending/failure/cancel/expire/authorize.
- Signature invalid/valid, malformed input dan unknown order.
- Canonical inquiry berbeda dari klaim status/fraud callback.
- Gross/order/currency/initiator/dual-target/invoice-balance mismatch.
- Missing inquiry fields, HTTP provider error dan retryable notification response.
- Duplicate, capture→settlement, late/stale pending/deny dan post-payment lifecycle.
- Refund revision event identity, tanpa receipt ganda.
- Browser finish forgery dan account lain.
- Rollback activation dan retry.
- Pending transfer offline tidak disahkan.
- Online servis benar-benar menghasilkan satu perolehan **10 points** dan notifikasi yang tidak bertambah pada replay.
- Empat concurrency cases.
- Regression Midtrans existing, pembelian member/PDI/handover dan pembayaran penjualan kendaraan.

Fake lama diperbaiki agar menyertakan **order_id resmi dalam inquiry**, serta fake Get Status pada signed-webhook cases. Tidak menghapus atau mengurangi assertion lama. Provider responses pada pengujian tetap sintetis.

## 12. Full regression dan final QA

[Hasil full regression final](YZA_P0_STAGE2_REGRESSION.xml):

| Metrik | Hasil |
|---|---:|
| Baseline tahap 1 | 452 tests / 2.005 assertions |
| Final tahap 2 | **510 tests / 2.318 assertions** |
| Tambahan | **58 cases / 313 assertions** |
| Failure / error / skipped | **0 / 0 / 0** |
| Exit code | **0** |
| Console duration | **65,399 detik** |
| JUnit aggregate duration | **64,430980 detik** |
| Memory | **176 MB** |
| PHP / PHPUnit | **8.2.4 / 11.5.56** |

DB proses dipaksa ke `yza_automotive_laravel_test`, APP_ENV testing, DB_URL kosong, cache/session array, queue sync dan LOG_CHANNEL null. TestCase guard existing tetap aktif. SQL read-only mengonfirmasi SELECT DATABASE() adalah DB test. Kebanyakan cases memakai DatabaseTransactions; concurrency cases memakai fixture committed yang unik karena koneksi worker harus melihat data tersebut, lalu cleanup hanya fixture miliknya.

PHP lint 12 file source/test/support: **PASS**. git diff --check: **PASS**; warning CRLF pada owner dashboard yang sudah ada tidak menghasilkan whitespace error dan file tersebut tidak diubah tahap ini.

Run intermediate selama pengembangan tidak dijadikan hasil final. Ada fixture error/stub reuse yang dikoreksi serta concurrency race yang diperbaiki; seluruh focused/full final di atas dijalankan ulang setelah perubahan terakhir. Dokumen/JUnit historis audit dan tahap 1 tetap dipertahankan.

Tidak menjalankan browser UAT, negative-CSRF real browser, live provider settlement, deployment atau merchant-dashboard operation. Harness existing membekukan tanggal fixture dan membypass CSRF umum; tidak mengklaim coverage persentase.

## 13. Audit data historis operasional — read-only

Semua query operasional menggunakan SET SESSION TRANSACTION READ ONLY, transaksi read-only lalu rollback. Tidak memanggil provider untuk mengubah/merekonsiliasi data operasional.

| Pemeriksaan | Hasil |
|---|---:|
| Membership | 10: 6 active, 4 pending_payment |
| Active tanpa gateway_payment_id | **4 — legacy/provenance perlu ditinjau** |
| Active dengan gateway | 2; pemeriksaan linkage/status/nominal lokal tidak menemukan mismatch |
| Event fraud challenge tersimpan | 0 |
| Paid gateway dengan event challenge/deny atau success code bukan 200 | 0 pada event yang tersedia |
| Duplicate event_key | 0 |
| Duplicate gateway receipt pointer | 0 |
| Duplicate provider_transaction_id | 0 |
| Order prefix vs target mismatch | 0 |
| Membership XOR transaksi target mismatch | 0 |
| Linked gateway/payment transaction atau nominal mismatch | 0 |

Empat membership active tanpa paid gateway disamarkan sebagai **e2453888a41c**, **68fa644660ca**, **6573d76e9c52**, **6b94153b218e** (hash referensi internal, tanpa nama/email/order pelanggan).

Migration `2026_10_04_020000_create_memberships_table::up` memang membuat membership aktif bagi customer existing. Karena itu empat kasus tersebut **bukan bukti fraud atau bug aktivasi baru yang sudah terkonfirmasi**. Namun asal entitlement/penerimaan dan keputusan grandfathering tidak dapat dibuktikan hanya dari gateway. Jangan otomatis menagih ulang atau mencabut akses.

Jumlah event lama hanya 5; ketiadaan event challenge tidak membuktikan tidak pernah ada challenge pada provider. Tidak menyamakan pembayaran DP/manual yang nominalnya serupa sebagai duplikasi tanpa bukti provider.

Snapshot sebelum dan setelah tahap ini sama: users 18, memberships 10, transaksi 13, pembayaran 18, gateway 8, gateway events 5, riwayat poin 8, notifikasi 23. Ini pengecekan jumlah row terpilih, bukan checksum seluruh DB. Seluruh mutasi test/cleanup diarahkan secara eksplisit ke database testing.

### Rencana rekonsiliasi — perlu persetujuan terpisah

1. Cocokkan empat pseudonym ke ID internal melalui petugas berwenang, tanpa mempublikasikan identitas.
2. Tinjau ketentuan grandfathering migration/entitlement dan bukti penerimaan non-gateway.
3. Untuk gateway requires_review/refund/reversal atau second settled attempt, lakukan inquiry provider dan cocokkan bank/merchant ledger, receipt, customer dan nominal.
4. Tetapkan tindakan bisnis per kasus: entitlement legacy tetap, pencatatan penerimaan, refund dana ganda, suspensi/penyesuaian benefit, atau koreksi linkage.
5. Buat proposal perubahan per-record yang bisa ditinjau, backup, approval dan jejak audit. Jangan memakai bulk SQL/reset atau mengubah status selama audit ini.
6. Jalankan rekonsiliasi hanya setelah persetujuan; dokumentasikan before/after dan bukti dana.

## 14. Risiko dan blocker yang belum diselesaikan

- **Provider nyata/staging HTTPS:** belum ada transaksi/callback/inquiry Midtrans nyata baru; mode production masih sengaja ditolak oleh MidtransClient. Latensi inquiry per callback, retry delivery, API quota dan monitoring perlu UAT.
- **Refund/reversal policy:** provider follow-up sekarang tercatat dan flagged, tetapi auto-refund, authoritative bank ledger reversal, pembatalan membership, pembalikan points/inventory dan pendapatan net belum diputuskan. Tidak mengambil keputusan dana/akses secara sepihak.
- **Membership legacy:** empat entitlement aktif tanpa gateway perlu keputusan provenance/grandfathering, bukan auto-deactivation.
- **Review hold resolution:** konflik ditahan secara konservatif; belum ada fitur baru untuk approve/release hold. Koreksi historis/financial memerlukan rencana dan persetujuan di atas.
- **Membership revenue ledger:** schema existing tidak mempunyai penerimaan membership dalam tabel transaksi umum. Tidak membuat ledger finansial baru pada tahap security ini.
- **External methods:** integration tetap Snap sandbox existing, bukan BI SNAP/DANA product baru. Dukungan metode yang membutuhkan kontrak inquiry lain harus diverifikasi sebelum diaktifkan.
- **Atomicity limits:** concurrency diuji untuk callback same/different attempts pada MySQL lokal. Belum stress test kombinasi offline operator/provider failure pada skala produksi.
- **P0 lain:** QC wajib, atomic promo, points setelah handover kendaraan, physical-unit identity, Awards dan KPI tetap pekerjaan terpisah; tidak diubah.

## 15. Status akhir

**YZA AUTOMOTIVE — P0 STAGE 2 RESULT**

- Fraud challenge: **FIXED**, kedua purpose, matrix dan reproduksi teruji.
- Webhook signature validation: **VERIFIED**, ditambah authenticated Get Status.
- Order ID validation: **VERIFIED**, response identity tidak lagi ditimpa.
- Gross amount validation: **VERIFIED**, exact decimal + target/balance authoritative.
- Duplicate webhook handling: **VERIFIED**, replay dan concurrency lokal.
- Late callback handling: **VERIFIED**, paid tidak turun karena pending/stale claim.
- Membership activation: **VERIFIED**, sekali, transactional, retry aman.
- Payment status consistency: **VERIFIED untuk alur normal/konflik**, post-payment follow-up ditandai untuk rekonsiliasi, bukan auto-reversal.
- Vehicle purchase regression: **PASS**.
- Offline payment regression: **PASS**, tidak auto-verify transfer existing.
- Focused tests: **105 PASS / 501 assertions**.
- Full regression: **510 PASS / 2.318 assertions / 0 fail / 0 error / 0 skipped**.
- Production DB modified: **NO**.
- Remaining blockers: **provider nyata/UAT, kebijakan refund-reversal, provenance membership legacy dan review reconciliation**.
- Final status: **Perbaikan teknis tahap 2 selesai dan teruji; bukan production-ready atau izin rekonsiliasi historis otomatis.**

Tidak melakukan commit/push/deploy, mengubah credential/callback production, mengaktifkan live payment, atau melanjutkan QC/promo. **Berhenti dan menunggu instruksi berikutnya.**

