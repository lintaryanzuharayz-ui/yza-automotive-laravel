# UAT End-to-End Pembelian Mobil YZA Automotive

Tanggal pelaksanaan: 11 Oktober 2026  
Lingkungan: local Laravel (`/pelanggan/pesanan-mobil/{id}`)  
Status akhir: **READY WITH NOTES**

## Ruang lingkup

UAT ini memeriksa alur:

`CHECKOUT -> ADMIN VERIFY -> PAID -> PDI -> PDI PASS -> PICKUP SCHEDULE -> READY -> HANDOVER -> COMPLETE -> MY GARAGE`

Tidak ada fitur baru, reset database, atau perubahan native database yang dilakukan selama UAT.

## Data uji

- Akun: `MEMBER-TEST` (identifier disamarkan; kredensial tidak dicatat).
- Kendaraan: kendaraan uji yang tersedia pada fixture/test environment.
- Pesanan: identifier uji dianonimkan dan tidak menyalin data pelanggan nyata.
- Database: migrasi workflow additive diterapkan pada database aplikasi dan database test.
- Backup sebelum migrasi: `storage/app/backups/yza_automotive_laravel_before_pdi_20261011_204305.sql`.

## Hasil uji workflow

| Tahap | Expected result | Actual result | Status |
|---|---|---|---|
| Checkout | Pesanan dibuat dan kendaraan masuk status reserved | Dicakup oleh feature/integration test; reservation dibuat satu kali | PASS |
| Admin verify | Petugas dapat menyetujui pesanan; member biasa tidak dapat melakukan mutasi admin | Route, policy/middleware, dan test role isolation tersedia; full suite lulus | PASS |
| Paid | Pembayaran terverifikasi; status pesanan dan notifikasi pembayaran diperbarui | Payment service dan Midtrans notification/return flow lulus test | PASS |
| PDI start | PDI hanya dapat dimulai setelah pembayaran terverifikasi | Service guard dan route admin/PDI lulus test | PASS |
| PDI pass | Checklist dan hasil PDI tersimpan; kendaraan siap dijadwalkan | Checklist/result/snapshot tersimpan dan tervalidasi | PASS |
| Pickup schedule | Jadwal/lokasi pickup tersimpan; status menjadi `siap_diambil` | Schedule mensyaratkan paid + PDI lulus dan mengirim notifikasi terkait | PASS |
| Ready | Member melihat status siap diambil dan detail jadwal | Status/timeline pickup tersedia pada detail pesanan member | PASS |
| Handover | Serah terima mengunci proses dan membuat ownership kendaraan | Handover mensyaratkan paid + PDI + jadwal; ownership dibuat setelah handover | PASS |
| Complete | Pesanan selesai dan inventory menjadi sold tanpa duplikasi | Finalisasi inventory dan status complete dicakup test idempotency | PASS |
| My Garage | Kendaraan muncul hanya sebagai kendaraan milik member yang telah menyelesaikan pembelian | Ownership terhubung ke sumber penjualan/pesanan/serah-terima; query verified ownership menolak data manual tanpa sumber | PASS |

## Validasi integritas data dan regresi

- Migrasi `2026_10_11_220000_add_pdi_pickup_ownership_workflow` berstatus **Ran**.
- Field PDI, pickup, dan source ownership ditambahkan secara additive.
- Ownership hanya dibuat setelah handover; pengulangan handover tidak membuat ownership kedua.
- Kendaraan tidak berubah menjadi sold sebelum syarat workflow terpenuhi.
- Validasi invoice/member detail dan status pembayaran tetap tersedia.
- `php artisan optimize:clear`: PASS.
- `php artisan view:cache`: PASS.
- Route smoke/static check untuk admin PDI, schedule, handover, Midtrans notification, invoice, dan My Garage: PASS.
- Full regression: **320 passed, 1 skipped, 1050 assertions**.
- Focused purchase/PDI/ownership/Midtrans tests: PASS.

## Temuan dan perbaikan selama validasi

1. Notifikasi awal menggunakan nilai enum yang tidak tersedia. Diperbaiki agar memakai enum notifikasi transaksi yang sudah ada.
2. Fixture pembayaran legacy mengisi `verified_by` dengan ID petugas fiktif. Diperbaiki agar field hanya diisi jika actor memang ada pada tabel petugas, sehingga foreign key tetap valid.
3. Database test belum memiliki migrasi workflow terbaru. Disinkronkan dengan migrasi normal; tidak dilakukan reset atau penghapusan data.
4. Test handover yang sebelumnya langsung melompat dari PDI disesuaikan agar mengikuti aturan bisnis baru: pembayaran terverifikasi → PDI lulus → jadwal pickup → handover.

## Batasan UAT

Validasi klik browser/manual visual belum dapat dijalankan pada sesi ini. Tool computer-use gagal melakukan inisialisasi helper Windows dengan error `trusted Node process exited unexpectedly` dan `windows sandbox failed: helper_unknown_error`. Karena itu:

- hasil workflow backend, service, route, persistence, authorization, dan regression dinyatakan **PASS** berdasarkan test otomatis dan pemeriksaan route/migrasi;
- verifikasi visual UI, klik langsung tombol Midtrans, dan observasi redirect browser diberi status **NOT EXECUTED** pada sesi ini;
- tidak ada pesanan produksi/local baru yang dibuat hanya untuk menggantikan uji browser tersebut.

## Kesimpulan

Workflow pembelian mobil sampai My Garage siap untuk demo/UAT lanjutan dari sisi sistem dengan status **READY WITH NOTES**. Satu pekerjaan tersisa sebelum sign-off penuh adalah menjalankan ulang klik manual/browser pada akun uji yang valid, lalu mencocokkan status UI dengan hasil database dan notifikasi.
