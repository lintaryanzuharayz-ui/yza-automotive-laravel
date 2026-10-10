# YZA Stage 4.6 Midtrans Settlement & Manual UAT

Tanggal: 2026-10-10
Branch: `feature/vehicle-purchase-flow`
Commit dasar: `7117f01`

## Status sebenarnya

**PARTIAL**

Settlement Sandbox untuk order sintetis berhasil diverifikasi melalui API resmi dan sinkronisasi backend. Namun transaksi ini adalah `cuci_mobil`, bukan `penjualan_mobil`, sehingga tidak membuktikan keberhasilan payment flow pembelian kendaraan. Authenticated browser UI tetap **NOT VERIFIED** karena browser automation interaktif tidak tersedia.

## Bukti Midtrans API

Order: `YZA-19-H4QRHUVFEZ`  
Database: `yza_automotive_laravel_staging`  
Environment: `sandbox`  
Production flag: `false`  
Gross amount: IDR 10.000  
Provider status dari Get Transaction Status API: `settlement`  
Payment type: `bank_transfer`  
Application payment record: `id_pembayaran=17`  
Application transaction status: `lunas`

Alur yang diverifikasi:

1. Screenshot Sandbox menunjukkan `Payment successful`; screenshot diperlakukan sebagai bukti pendukung, bukan sumber status final.
2. Existing backend `sync` memanggil status API resmi Midtrans.
3. Hasil API `settlement` tersimpan pada gateway record.
4. Satu payment record dibuat dengan nominal yang sama.
5. Sinkronisasi kedua tetap menghasilkan satu payment record; tidak terjadi duplikasi.
6. Invoice `/pelanggan/invoice/car-wash/{transaction}` merespons HTTP 200 dan memuat kode transaksi.

Rujukan API: [Get Transaction Status Midtrans](https://docs.midtrans.com/reference/get-transaction-status).

## Webhook versus API sync

Hasil di atas adalah **API status sync**, bukan bukti webhook HTTP nyata. Event gateway tidak bertambah pada sinkronisasi kedua dan payment tetap satu, sehingga idempotency pada jalur sync terverifikasi. Webhook publik dengan signature Midtrans belum dikirim dan belum diverifikasi end-to-end pada staging.

Mock test sebelumnya tetap mencakup signature invalid, amount mismatch, duplicate notification, settlement, pending, dan expire. Mock tersebut tidak diklaim sebagai webhook nyata.

## Rekonsiliasi dan isolasi

- Gateway status: `settlement`.
- Transaksi aplikasi: `lunas`.
- Payment count untuk transaksi: 1 sebelum dan sesudah sync kedua.
- Invoice: HTTP 200.
- Membership gateway pada flow ini: tidak ada.
- Member vehicle order untuk transaksi ini: 0.
- Verified ownership kendaraan member tidak diaktifkan oleh payment `cuci_mobil`.
- Database utama `yza_automotive_laravel` dan Native `yza_automotive`: tidak disentuh.

## Batasan penting

Order ini memiliki `jenis_transaksi=cuci_mobil`. Settlement ini tidak membuktikan:

- settlement pembayaran `penjualan_mobil`;
- approval pembelian mobil via UI;
- invoice pembelian kendaraan;
- PDI/handover pembelian kendaraan setelah settlement;
- verified ownership kendaraan baru.

Flow pembelian kendaraan tetap divalidasi oleh `MemberVehiclePurchaseFlowTest` dengan data terisolasi. Payment Sandbox nyata untuk kendaraan belum dijalankan.

## Panduan manual browser UAT

Gunakan browser Chrome/Edge dengan akun sintetis staging. Jangan menonaktifkan authentication/authorization dan jangan menampilkan password atau credential.

| Skenario | Hasil | Bukti yang perlu diambil |
|---|---|---|
| Login member | NOT VERIFIED | Halaman member tanpa password terlihat |
| Katalog dan detail mobil | PASS via headless DOM | URL katalog/detail, spesifikasi, harga |
| Beli Sekarang dan checkout | NOT VERIFIED | Checkout dan snapshot harga |
| Pesanan Mobil Saya | NOT VERIFIED | Nomor/status pesanan |
| Invoice mobil | NOT VERIFIED | Invoice tanpa token/credential |
| Status pembayaran mobil | NOT VERIFIED | Status gateway dan nominal |
| Login admin | NOT VERIFIED | Daftar pesanan admin |
| Approval/rejection | NOT VERIFIED | Status dan alasan rejection |
| PDI | NOT VERIFIED via UI; PASS via automated flow | Checklist 10 item |
| Jadwal pengambilan | NOT VERIFIED via UI; PASS via automated flow | Jadwal dan prasyarat |
| Serah terima | NOT VERIFIED via UI; PASS via automated flow | Dokumen, kendaraan, penerima |
| My Garage | NOT VERIFIED via UI; PASS via automated flow | Satu verified ownership |

Untuk screenshot, hindari URL Snap lengkap, token, Server Key, Client Key, APP_KEY, password, dan data pelanggan.

## Regression terbaru

- Full Feature: **297 passed, 1 skipped, 913 assertions**.
- Focused purchase/Midtrans/audit: **21 passed, 65 assertions**.
- Unit: **1 passed, 1 assertion**.
- PHP lint dan Blade cache: lulus.
- Skipped: `YzaPointsStage10BTest::test_legacy_customer_without_user_is_not_a_member`; fixture sintetis tidak memiliki customer legacy tanpa `id_user`.

## Blocker tersisa

1. Payment Sandbox nyata untuk `penjualan_mobil` belum dilakukan.
2. Webhook nyata dan signature callback belum diverifikasi; yang terbukti adalah API sync.
3. Authenticated browser UI member/admin belum VERIFIED karena runner interaktif gagal.

Stage 4.6 tidak dinyatakan COMPLETE. Tidak ada perubahan source aplikasi, database utama, credential, harga katalog, atau branch `main`.
