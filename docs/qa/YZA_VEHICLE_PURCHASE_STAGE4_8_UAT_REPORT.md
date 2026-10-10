# YZA Stage 4.8 Real Webhook & Authenticated Browser UAT

Tanggal: 2026-10-10
Branch: `feature/vehicle-purchase-flow`
Commit awal: `a44b3af`

## Status final

**PARTIAL / BLOCKED**

Pembayaran kendaraan Stage 4.7 tetap terverifikasi melalui Get Transaction Status API. Dua blocker Stage 4.8 belum selesai: real Midtrans Sandbox webhook belum memiliki bukti delivery HTTP ke endpoint staging, dan authenticated browser UAT member/admin belum dapat dijalankan oleh browser helper lokal.

Tidak ada implementasi ulang fitur pembelian mobil dan tidak ada perubahan database utama/native.

## Fase verifikasi

- Branch: `feature/vehicle-purchase-flow`.
- Working tree bersih sebelum laporan ini dibuat.
- Konfigurasi staging: `yza_automotive_laravel_staging`.
- Midtrans environment: `sandbox`; production flag: `false`.
- Callback terdaftar: `POST /payment/midtrans/notification`.
- Route member `pelanggan/pesanan-mobil` dan admin `admin/pesanan-mobil` terdaftar.
- Listener lokal ditemukan pada Apache/XAMPP, tetapi tidak ada proses `ngrok`, `cloudflared`, `frpc`, atau `localtunnel`.
- Notification URL Midtrans Sandbox tidak diubah.

## Browser automation

Skill browser lokal dicoba dua kali sesuai prosedur recovery. Keduanya gagal pada inisialisasi:

`trusted Node process exited unexpectedly; kernel reset`

Tidak ada aksi login, submit form, approval, PDI, atau handover yang diklaim berhasil.

### Hasil UAT member

| Skenario | Hasil | Bukti |
|---|---|---|
| Login member staging | NOT VERIFIED | Browser helper gagal sebelum login |
| Member Center | NOT VERIFIED | Belum ada session browser |
| Katalog dan detail mobil | NOT VERIFIED authenticated | Public/headless route bukan bukti session |
| Beli Sekarang dan checkout | NOT VERIFIED | Belum dijalankan melalui browser |
| Pesanan Mobil Saya | NOT VERIFIED | Belum dijalankan melalui browser |
| Invoice dan status pembayaran | NOT VERIFIED authenticated | Settlement API Stage 4.7 bukan browser UAT |
| Timeline PDI dan jadwal | NOT VERIFIED | Belum dijalankan melalui browser |
| My Garage sebelum handover | NOT VERIFIED via browser; backend PASS | Tidak ada ownership sebelum handover |

### Hasil UAT admin

| Skenario | Hasil | Bukti |
|---|---|---|
| Login admin staging | NOT VERIFIED | Browser helper gagal sebelum login |
| Daftar/detail pesanan | NOT VERIFIED | Belum dijalankan melalui browser |
| Approval/rejection | NOT VERIFIED | Belum dijalankan melalui browser |
| Invoice dan pembayaran | NOT VERIFIED | Belum dijalankan melalui browser |
| PDI 10 checklist | NOT VERIFIED via browser; automated PASS | Tidak dijalankan pada browser |
| Jadwal dan serah terima | NOT VERIFIED via browser; automated PASS | Tidak dijalankan pada order settlement nyata |
| My Garage setelah handover | NOT VERIFIED via browser | Handover tidak dipaksakan |

## Audit webhook existing

- Route callback dikecualikan dari CSRF karena dipanggil server-to-server.
- Signature diverifikasi dengan SHA-512 atas `order_id`, `status_code`, `gross_amount`, dan Server Key melalui `hash_equals`.
- `order_id` wajib menemukan gateway yang sudah ada.
- Gross amount mismatch menjadi `requires_review` dan tidak membuat payment lunas.
- Settlement/capture dipetakan ke status sukses sesuai service.
- Event key berbasis order/status/code/provider transaction ID mencegah event duplikat.
- Payment record dibuat hanya jika gateway belum memiliki `id_pembayaran`.
- Route member/admin tetap memakai auth dan role middleware.

Transaction type tidak diterima dari payload Midtrans sebagai sumber kebenaran. Callback mengikat order ke gateway dan transaksi lokal; jenis transaksi dibatasi saat `createAttempt`. Pengikatan bersifat server-side, tetapi tidak ada field `jenis_transaksi` terpisah dalam payload callback sebagai bukti independen.

## Bukti real webhook

**NOT VERIFIED.** Tidak ada bukti lengkap bahwa request dikirim Midtrans Sandbox, diterima endpoint staging melalui HTTPS publik, memiliki signature valid, diproses backend, dan tidak membuat payment duplikat.

Yang tersedia hanya:

- Get Transaction Status API nyata untuk order kendaraan Stage 4.7.
- Mock/fake HTTP tests untuk signature, settlement, duplicate notification, amount mismatch, pending, dan expire.
- Idempotency API sync nyata pada gateway kendaraan Stage 4.7.

Mock webhook dan API status sync tidak diklaim sebagai real webhook delivery.

## Prosedur manual aman

Gunakan akun sintetis staging pada Microsoft Edge atau Chrome:

1. Buka URL staging resmi tanpa credential atau token pada URL.
2. Login member sintetis; buka Member Center, katalog, detail mobil, checkout, Pesanan Mobil Saya, invoice, dan status pembayaran.
3. Pastikan order berstatus lunas tetapi ownership belum muncul sebelum handover.
4. Login admin sintetis; buka daftar/detail order, approval, PDI, jadwal pengambilan, dan prasyarat handover.
5. Jangan melakukan handover pada order ini kecuali pengguna memang menyelesaikan serah terima sintetis yang sah.
6. Untuk webhook, administrator Midtrans Sandbox harus mengarahkan Notification URL ke endpoint HTTPS staging yang hanya mengekspos path callback.
7. Jangan mengekspos phpMyAdmin, dashboard admin, atau seluruh XAMPP.
8. Simpan hanya timestamp, HTTP response, status callback, order ID, nominal, dan count payment. Jangan menyimpan credential, signature, token, atau payload sensitif.

Setiap langkah yang belum benar-benar dilakukan melalui browser wajib berstatus **NOT VERIFIED**.

## Security validation

- Route member memakai `auth`, `role:pelanggan`, dan `member` middleware.
- Route admin memakai `auth` dan `role:admin` middleware.
- Invoice membatasi pelanggan pada transaksi miliknya.
- Callback tidak membutuhkan login browser, tetapi memerlukan signature dan order gateway yang dikenal.
- Tidak ada bypass authorization yang dibuat untuk UAT.
- Ownership My Garage tidak diaktifkan melalui pembayaran saja.

## Regression

- Focused purchase/Midtrans/audit: **21 passed, 65 assertions**.
- Full Feature: **297 passed, 1 skipped, 913 assertions**.
- Unit: **1 passed, 1 assertion**.
- PHP lint service pembayaran dan purchase: lulus.
- Blade view cache dan route compilation/listing: lulus.
- Skipped: `YzaPointsStage10BTest::test_legacy_customer_without_user_is_not_a_member`; fixture sintetis tidak memiliki customer legacy tanpa `id_user`.

## Database dan keamanan

- `yza_automotive_laravel_staging` hanya digunakan untuk integrasi staging.
- `yza_automotive_laravel_test` digunakan untuk automated tests.
- `yza_automotive_laravel` tidak diubah.
- `yza_automotive` Native tidak diubah.
- Tidak ada `migrate:fresh`, perubahan harga katalog asli, perubahan credential, fake payment, atau force push.
- Tidak ada perubahan branch `main`.

## Blocker tersisa

1. Endpoint HTTPS staging aman dan Notification URL Midtrans Sandbox diperlukan untuk membuktikan real webhook delivery.
2. Pengguna perlu menjalankan checklist browser manual dengan akun sintetis staging atau menyediakan browser automation yang berfungsi.
3. PDI, handover, dan My Garage belum diverifikasi melalui browser; ownership tidak boleh dipaksakan.

Stage 4.8 belum COMPLETE.
