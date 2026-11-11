# YZA Vehicle Purchase Stage 4 UAT Report

Tanggal: 2026-10-10
Branch: `feature/vehicle-purchase-flow`
Commit awal: `98f2b19`
Database utama dan `yza_automotive_laravel_test` tidak digunakan untuk mutasi UAT.

## Status

**PARTIAL / BLOCKED**

Readiness, staging migration, synthetic UAT backend, public catalog rendering, dan Midtrans Snap Sandbox API create/status berhasil. Payment settlement/capture nyata belum terbukti karena transaksi Sandbox masih `pending` dan browser automation lokal gagal diinisialisasi. Tidak ada status pembayaran yang dipalsukan.

## Environment staging

- Database terisolasi: `yza_automotive_laravel_staging`.
- Database utama `yza_automotive_laravel` dan native `yza_automotive`: tidak dimutasi.
- Migrations: seluruhnya `Ran`, termasuk `2026_10_10_010000_create_member_vehicle_purchase_tables`.
- Aplikasi staging dijalankan pada `http://127.0.0.1:8001`.
- Fixture: data sintetis Stage 2/3, tanpa identitas atau transaksi pelanggan nyata.
- Akun sintetis tersedia untuk role admin, petugas, member aktif tanpa kendaraan terverifikasi, dan member aktif dengan kendaraan sintetis. Password tidak dicantumkan dalam laporan.

## Audit skipped test

Satu test yang skipped adalah `YzaPointsStage10BTest::test_legacy_customer_without_user_is_not_a_member`. Penyebabnya valid: tidak ada customer legacy tanpa `id_user` pada baseline sintetis. Ini bukan kegagalan aplikasi.

## UAT member dan admin

Automated integration UAT terhadap database staging:

- Checkout member mengambil harga dan snapshot spesifikasi dari database.
- Reservasi unit, approval admin, rejection, payment boundary, PDI, handover, dan My Garage lulus melalui `MemberVehiclePurchaseFlowTest`.
- Ownership terverifikasi hanya dibuat setelah handover sah.
- Pembayaran pending tidak menyelesaikan penjualan.
- PDI gagal menahan handover; PDI lengkap dan syarat pembayaran diperlukan untuk handover.
- Duplicate checkout, double finalization, ownership lintas member, dan endpoint admin oleh member diuji oleh regression suite.

Public headless-browser verification berhasil untuk `/mobil` dan `/mobil/9`: katalog menampilkan koleksi YZA, detail sintetis, spesifikasi, harga, dan link detail. Browser automation interaktif tidak tersedia karena helper lokal berhenti saat inisialisasi (`trusted Node process exited unexpectedly`), sehingga login/UI checkout/admin interaktif belum dapat diberi status VERIFIED.

## Midtrans Sandbox

Konfigurasi efektif staging diverifikasi tanpa menampilkan credential:

- Database efektif: `yza_automotive_laravel_staging`.
- Environment: `sandbox`.
- `is_production`: `false`.
- Server/client credential tersedia, tetapi nilainya tidak dicatat atau ditampilkan.

Satu Snap Sandbox API request nyata dibuat untuk transaksi sintetis staging:

- Nominal: IDR 10.000.
- Gateway record: ID 998.
- Redirect host: `app.sandbox.midtrans.com`.
- Initial provider status: `pending`.
- Official status sync: `pending`.
- `id_pembayaran`: belum ada; transaksi belum dianggap lunas.

Yang berhasil dibuktikan nyata: order creation, nominal server-side, redirect Sandbox, dan status query resmi. Settlement/capture, expired/cancelled, retry setelah status provider, serta webhook nyata belum dapat diverifikasi karena transaksi belum dibayar melalui Sandbox Simulator dan browser automation tidak tersedia.

Skenario signature invalid, unknown order, amount mismatch, duplicate notification, pending, expiry, dan idempotency diuji dengan mock terisolasi pada `MidtransSandboxStage11ATest`: **7 passed, 40 assertions** untuk suite Stage 2 + Midtrans bersama. Mock tidak diklaim sebagai transaksi Sandbox nyata.

Dokumentasi resmi yang digunakan: [Testing Payment on Sandbox](https://docs.midtrans.com/docs/testing-payment-on-sandbox) dan [Get Transaction Status](https://docs.midtrans.com/reference/get-transaction-status).

## PDI, handover, dan My Garage

Downstream flow tervalidasi melalui test terisolasi pada staging:

- 10 checklist PDI tersimpan.
- PDI gagal menolak handover.
- PDI berhasil, pembayaran terverifikasi, dokumen, kendaraan, dan penerima wajib valid sebelum handover.
- Handover menghasilkan satu verified ownership dan tidak dapat diulang.
- My Garage dan akses layanan khusus mengikuti ownership member.

Karena payment Sandbox nyata masih `pending`, hasil PDI/handover yang lulus adalah hasil automated isolated flow, bukan klaim transaksi Sandbox settled.

## Regression gate

- Full Feature: **297 passed, 1 skipped, 913 assertions**.
- Unit: **1 passed, 1 assertion**.
- `MemberVehiclePurchaseFlowTest`: **7 passed, 24 assertions**.
- Midtrans mock suite: **7 passed, 16 assertions**.
- Combined Stage 2 + Midtrans: **14 passed, 40 assertions**.
- PHP lint, Blade cache, route inspection, migration status, dan whitespace check: lulus.

## Security and change control

- `.env` tidak terlacak dan tidak diunggah.
- APP_KEY, Server Key, Client Key, password, token, dan payload sensitif tidak dicatat.
- Tidak ada database dump, backup pelanggan, atau upload pelanggan yang ditambahkan.
- Tidak ada force push dan `main` tidak diubah.
- Staging database berisi fixture sintetis dan dapat dipisahkan dari database utama.

## Keputusan / tindak lanjut

Stage 4 belum boleh disebut payment end-to-end VERIFIED. Untuk menutup blocker, jalankan Snap Sandbox Simulator pada order sintetis yang masih pending, verifikasi notification/status settlement melalui endpoint aplikasi staging, lalu ulangi skenario expired/cancelled dan retry. Setelah bukti settlement dan UI authenticated tersedia, laporan dapat dinaikkan menjadi READY.
