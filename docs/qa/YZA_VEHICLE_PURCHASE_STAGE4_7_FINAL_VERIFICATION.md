# YZA Stage 4.7 Vehicle Payment & Member UAT

Tanggal: 2026-10-10
Branch: `feature/vehicle-purchase-flow`
Commit dasar: `689e0ba`

## Status sebenarnya

**PARTIAL — pembayaran kendaraan berhasil diverifikasi melalui API resmi, tetapi webhook nyata dan authenticated browser UAT belum VERIFIED.**

Transaksi ini adalah transaksi staging sintetis untuk `penjualan_mobil`. Status pembayaran yang berhasil tidak otomatis menyelesaikan serah terima atau mengaktifkan kepemilikan kendaraan.

## Bukti Midtrans Sandbox

- Database: `yza_automotive_laravel_staging`
- Environment: `sandbox`
- Production flag: `false`
- Gateway ID: `7`
- Order ID: `YZA-20-6Z84VEB12L`
- Order member: `ORD-202610-5A9E9783`
- Jenis transaksi: `penjualan_mobil`
- Gross amount: IDR 10.000
- Payment type dari status API: `bank_transfer`
- Status resmi Get Transaction Status API: `settlement`
- Payment record aplikasi: `id_pembayaran=18`

Screenshot Sandbox yang diberikan pengguna adalah bukti pendukung. Bukti otoritatif untuk sinkronisasi ini adalah respons Get Transaction Status API yang dipanggil melalui `MidtransPaymentService` existing dengan environment Sandbox.

Rujukan: [Midtrans Get Transaction Status API](https://docs.midtrans.com/reference/get-transaction-status).

## Rekonsiliasi backend staging

| Pemeriksaan | Hasil | Keterangan |
|---|---|---|
| Gateway mencapai status sukses | PASS | Provider status `settlement` |
| Status transaksi aplikasi | PASS | `transaksi.status=lunas` |
| Status pesanan member | PASS | `pesanan_mobil_member.status=lunas` |
| Status penjualan kendaraan | PASS | Tetap `proses`; belum serah terima |
| Nominal | PASS | Status API dan transaksi sama-sama IDR 10.000 |
| Payment record tepat satu | PASS | Count tetap 1, payment `id_pembayaran=18` |
| Sinkronisasi kedua idempotent | PASS | Payment count 1 sebelum dan sesudah sync kedua |
| Event gateway | PASS | Count tetap 1 setelah sync kedua |
| Invoice kendaraan | PASS | `/pelanggan/invoice/penjualan-mobil/20` merespons HTTP 200 HTML |
| My Garage sebelum serah terima | PASS | Tidak ada ownership sintetis yang dibuat; count 0 |
| Membership terdampak | PASS | Tidak ada gateway membership pada transaksi ini |
| Harga katalog asli | PASS | Tidak diubah; transaksi memakai unit sintetis staging |

`penjualan_mobil.status=proses` adalah hasil yang diharapkan pada tahap ini. Alur existing menahan penyelesaian penjualan sampai PDI dan serah terima yang sah. Karena serah terima belum dilakukan, My Garage tidak diaktifkan.

## Webhook versus API sync

Yang VERIFIED adalah polling/status sync API resmi Midtrans melalui backend existing. Ini **bukan** bukti webhook HTTP nyata. Tidak ada endpoint HTTPS publik yang digunakan, tidak ada callback signature nyata yang diterima, dan tidak ada webhook palsu yang dikirim.

Test otomatis sebelumnya mencakup validasi signature, amount mismatch, duplicate notification, settlement, pending, dan expire menggunakan mock/fake HTTP. Test tersebut tidak diklaim sebagai verifikasi webhook produksi atau callback Sandbox end-to-end.

## Browser UAT

Authenticated browser automation tetap **NOT VERIFIED** karena runner interaktif sebelumnya gagal. Pemeriksaan headless/public hanya dapat menjadi bukti route publik, bukan bukti session member/admin.

| Skenario | Hasil | Bukti manual yang diperlukan |
|---|---|---|
| Login member | NOT VERIFIED | Screenshot halaman member tanpa password |
| Detail mobil | NOT VERIFIED authenticated | URL/detail dan harga tanpa data pribadi |
| Checkout | NOT VERIFIED | Snapshot harga dan order member |
| Pesanan Mobil Saya | NOT VERIFIED | Status order dan nominal |
| Invoice kendaraan | NOT VERIFIED authenticated | Invoice tanpa credential/token |
| Status pembayaran | NOT VERIFIED authenticated | Status `lunas` dan nominal |
| Login admin | NOT VERIFIED | Dashboard admin tanpa credential |
| Approval | NOT VERIFIED via UI | Riwayat approval |
| PDI | NOT VERIFIED via UI; automated PASS | Checklist PDI |
| Jadwal pengambilan | NOT VERIFIED via UI; automated PASS | Jadwal dan prasyarat |
| Serah terima | NOT VERIFIED via UI; automated PASS | Dokumen dan penerima |
| My Garage | NOT VERIFIED via UI; automated PASS only after handover flow | Ownership setelah serah terima |

## Regression

- Focused purchase/Midtrans/audit: **21 passed, 65 assertions**.
- Full Feature: **297 passed, 1 skipped, 913 assertions**.
- Unit: **1 passed, 1 assertion**.
- Skipped: `YzaPointsStage10BTest::test_legacy_customer_without_user_is_not_a_member`; fixture sintetis tidak memiliki customer legacy tanpa `id_user`.
- Test database yang digunakan: `yza_automotive_laravel_test`.
- Database staging settlement tetap terpisah dari database test.

## Keamanan dan batasan

- `yza_automotive_laravel` tidak disentuh.
- Database Native `yza_automotive` tidak disentuh.
- Tidak ada credential, Server Key, Client Secret, APP_KEY, password, atau token yang ditampilkan atau dicatat.
- Tidak ada perubahan database utama/native.
- Tidak ada perubahan visibility repository atau branch `main`.
- Tidak ada pembayaran sukses palsu.

## Blocker tersisa

1. Webhook Sandbox nyata dan signature callback belum VERIFIED end-to-end.
2. Authenticated browser UAT member/admin belum VERIFIED; perlu langkah manual Chrome/Edge dengan akun sintetis staging.
3. PDI, jadwal, dan serah terima belum dijalankan pada order settlement ini, sehingga ownership My Garage memang belum boleh aktif.

Stage 4.7 tidak dinyatakan COMPLETE selama tiga hal tersebut belum memiliki bukti yang sesuai.
