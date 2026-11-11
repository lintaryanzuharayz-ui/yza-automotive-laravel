# YZA Vehicle Purchase Stage 4.5 Verification

Tanggal: 2026-10-10
Branch: `feature/vehicle-purchase-flow`
Commit dasar: `c5c04e7`

## Keputusan

**PARTIAL / BLOCKED**

Automated purchase flow, security boundaries, regression suite, staging isolation, dan pembuatan Snap Sandbox nyata berhasil. Settlement/capture Sandbox belum tercapai, dan authenticated browser UAT belum dapat diverifikasi karena browser automation interaktif tidak tersedia.

## Browser recovery

- Playwright dan Puppeteer tidak tersedia pada project atau Node global.
- Edge dan Chrome lokal tersedia.
- Edge headless berhasil melakukan DOM capture untuk `/mobil` dan `/mobil/9`; katalog, detail sintetis, spesifikasi, harga, dan link detail tampil.
- Computer-use/browser helper gagal saat inisialisasi dengan `trusted Node process exited unexpectedly`.
- Tidak ada autentikasi yang dinonaktifkan dan tidak ada authorization production yang diubah.

### Status UI

Member login, checkout interaktif, invoice interaktif, admin approval UI, PDI UI, dan handover UI: **NOT VERIFIED**. Alur yang sama tervalidasi melalui integration test terisolasi; hasil itu tidak dipromosikan sebagai bukti browser.

### Panduan UAT manual

1. Jalankan staging pada database `yza_automotive_laravel_staging` dan buka `http://127.0.0.1:8001`.
2. Login memakai akun sintetis staging; password tidak disimpan di repository atau laporan.
3. Member: buka `/mobil`, pilih unit tersedia, buka detail, pilih `Beli Sekarang`, cek snapshot harga/spesifikasi, konfirmasi, lalu buka daftar pesanan dan invoice.
4. Admin: buka daftar pesanan member, cek detail, approve/reject, buka pembayaran/PDI, dan lanjutkan hanya setelah prasyarat terpenuhi.
5. Jangan mengubah status database secara manual. Catat screenshot tanpa URL token, password, Server Key, Client Key, APP_KEY, atau data pribadi.

## Midtrans Sandbox nyata

Konfigurasi efektif diverifikasi tanpa mencetak credential:

- Database: `yza_automotive_laravel_staging`.
- Environment: `sandbox`.
- `is_production`: `false`.
- Endpoint Snap yang menerima order: `app.sandbox.midtrans.com`.

State order sintetis terbaru:

- Gateway record: `6`.
- Gross amount: IDR 10.000.
- Snap order creation: berhasil melalui API Sandbox.
- Official status sync: `pending`.
- Payment record: belum ada.
- Application transaction: tetap `belum_bayar`.

Order lama yang disebut pada Stage 4 tidak lagi ditemukan pada state staging saat audit ulang, sehingga dibuat order sintetis pengganti. Tidak ada settlement palsu, SQL mutation untuk pembayaran, atau klaim bahwa redirect berarti lunas.

### Settlement yang masih memerlukan tindakan manual

Midtrans Sandbox menggunakan simulator resmi, bukan dana nyata. Pengguna perlu membuka redirect Snap dari halaman pembayaran staging dan menyelesaikan skenario melalui Sandbox Simulator menggunakan credential uji resmi Midtrans. Setelah itu, jalankan tombol/status sync aplikasi dan verifikasi `transaction_status` melalui Get Transaction Status API. Jangan memakai credential production.

Rujukan: [Testing Payment on Sandbox](https://docs.midtrans.com/docs/testing-payment-on-sandbox), [Get Transaction Status](https://docs.midtrans.com/reference/get-transaction-status).

Settlement/capture nyata: **NOT VERIFIED**. Expired/cancelled nyata dan webhook nyata: **NOT VERIFIED**. Mock tests untuk pending, settlement, duplicate notification, invalid signature, amount mismatch, dan expire tetap lulus.

## Payment reconciliation

Integration/mock coverage membuktikan:

- Pending tidak membuat payment record atau status lunas.
- Settlement valid di mock membuat satu payment record dan status lunas.
- Duplicate callback idempotent.
- Signature invalid dan amount mismatch ditolak/masuk `requires_review`.
- Expire tidak membuat payment record.
- Pembayaran transaksi kendaraan dipisahkan dari membership.

Bukti settlement provider nyata belum tersedia, sehingga reconciliation nyata belum lengkap.

## PDI, handover, dan My Garage

`MemberVehiclePurchaseFlowTest` memvalidasi 7 skenario, 24 assertions:

- Checkout snapshot dan reservasi unit.
- Approval admin dan reuse transaksi/penjualan.
- Pembayaran pending tidak melewati syarat.
- PDI gagal menahan handover.
- PDI lengkap, pembayaran sah, dokumen, kendaraan, dan penerima diperlukan.
- Handover berhasil membuat verified ownership satu kali.
- My Garage dan layanan khusus mengikuti ownership yang benar.

Karena order Sandbox nyata masih pending, hasil downstream ini adalah automated isolated validation, bukan UAT settled payment.

## Regression terbaru

- Focused purchase/Midtrans/audit suite: **21 passed, 65 assertions**.
- Full Feature: **297 passed, 1 skipped, 913 assertions**.
- Unit: **1 passed, 1 assertion**.
- PHP lint untuk payment dan purchase service: lulus.
- Blade compilation/cache: lulus.
- `git diff --check`: lulus.

Skipped test: `YzaPointsStage10BTest::test_legacy_customer_without_user_is_not_a_member`. Fixture sintetis tidak memiliki customer legacy tanpa `id_user`; test melewati dirinya sendiri karena data prasyarat tidak ada. Tidak ada test yang diubah menjadi skipped.

## Keamanan dan database

- `yza_automotive` dan `yza_automotive_laravel` tidak dimutasi.
- `yza_automotive_laravel_test` hanya dipakai untuk automated regression.
- UAT menggunakan `yza_automotive_laravel_staging` dengan data sintetis.
- `.env`, APP_KEY, password, Midtrans Server Key, Client Key, token, signature, dan payload sensitif tidak ditampilkan atau dicatat.
- Tidak ada perubahan ke `main` dan tidak ada force push.

## Blocker tersisa

1. Authenticated browser runner tidak tersedia; UI member/admin tetap NOT VERIFIED.
2. Sandbox Simulator membutuhkan tindakan manual pengguna untuk mencapai settlement/capture.
3. Webhook publik staging belum dikonfigurasi; notification nyata belum dapat dibuktikan end-to-end.

Stage 4.5 tidak boleh dinaikkan menjadi COMPLETE sebelum tiga blocker tersebut memiliki bukti yang dapat diaudit.
