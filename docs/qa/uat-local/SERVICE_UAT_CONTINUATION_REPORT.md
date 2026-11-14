# Service UAT Continuation Report

Tanggal: 15 Oktober 2026  
URL UAT: `http://127.0.0.1:8001`  
Database UAT: `yza_automotive_laravel_uat`

## Work Order existing

Booking `BSV-202610-96C3C181` dan Work Order `SRV-202610-ECD72CB1` dilanjutkan tanpa reset atau pembuatan booking baru.

- QC: **PASS** — enam checklist wajib terlihat `LULUS`, catatan QC tersimpan, dan approval menunggu `0`.
- Validasi approval: **PASS** — finalisasi UI menampilkan tidak ada approval tertunda.
- Sparepart/fitment: **PASS / tidak terpakai** — Work Order ini memiliki biaya sparepart Rp0 dan tidak memiliki item sparepart yang perlu dikonsumsi.
- Finalisasi: **PASS melalui UI resmi** — transaksi `TRX-202610-B1BF99A3` dibuat.
- Pembayaran sintetis: **PASS melalui UI resmi** — tunai Rp150.000 tersimpan dan transaksi menjadi `lunas`/`terverifikasi`.
- Status servis: **selesai**; `tanggal_selesai` tercatat `2026-10-15 18:55:21`.
- Assignment: histori lama tetap `closed`; assignment aktif tetap primary Rian Hidayat (Teknisi Demo). Tidak ada assistant pada fixture ini.

## Member history

Request terautentikasi dengan akun member fixture `memberdemo` membuka halaman riwayat servis dan menemukan Work Order `SRV-202610-ECD72CB1` serta nama mekanik utama `Rian Hidayat`. Halaman juga menampilkan servis selesai dan nominal yang berasal dari data Work Order/transaksi.

Ini dicatat sebagai **authenticated HTTP evidence**, bukan browser PASS penuh, karena sesi browser automation yang tersedia masih terikat pada akun mekanik dan tidak menyediakan sesi browser terpisah pada turn ini. Tidak ada data pelanggan lain yang diminta atau ditampilkan dalam request member tersebut; ownership tetap dilindungi oleh route/controller dan regression test.

## Negative CSRF

Pada UAT, request sintetis ke `/login` menghasilkan:

| Request | HTTP | Efek bisnis |
|---|---:|---|
| POST tanpa token | 419 | Tidak ada login/data bisnis |
| POST token invalid | 419 | Tidak ada login/data bisnis |
| POST token dari session lain | 419 | Tidak ada login/data bisnis |
| POST token sah + kredensial invalid | 302 | Hanya error autentikasi; tidak ada data bisnis |

Validasi aplikasi tetap aktif; hanya callback Midtrans yang dikecualikan dari CSRF session melalui `bootstrap/app.php`. Mekanik biasa tidak diuji ulang lewat browser pada turn ini; boundary assignment tetap tercakup oleh focused/full regression dan tidak ada perubahan authorization.

## Isolasi database

- `php artisan test` menghasilkan **407 PASS / 0 FAIL / 0 SKIPPED / 1.488 assertions**.
- PHPUnit efektif memakai `yza_automotive_laravel_test` melalui `phpunit.xml`.
- Guard `tests/TestCase.php` menolak database selain database testing.
- Database operasional dan UAT tidak dipakai oleh PHPUnit.
- Pemeriksaan data operasional bersifat read-only; tidak ada restore atau koreksi otomatis yang dilakukan.

## Evidence screenshot

Browser menampilkan Work Order sebelum finalisasi, halaman QC, hasil finalisasi, dan status pembayaran lunas selama sesi. Backend browser automation tidak menyediakan penyimpanan screenshot ke file lokal pada sesi ini, sehingga **tidak ada file screenshot yang diklaim**. Bukti visual tersebut hanya tersedia inline pada sesi browser.

## Blocker tersisa

1. Browser member session terpisah belum tersedia pada sesi ini; member history baru dikonfirmasi melalui authenticated HTTP evidence.
2. Negative CSRF tercatat melalui request HTTP sintetis, belum melalui browser UI.
3. Tidak ada screenshot file lokal yang dapat dilampirkan.
4. Callback HTTPS Midtrans/refund provider tetap mengikuti blocker staging sebelumnya.

Tidak ada bug aplikasi baru yang terbukti, sehingga tidak ada perubahan kode dan tidak ada regression rerun tambahan selain verifikasi `php artisan test` yang sudah lulus.
