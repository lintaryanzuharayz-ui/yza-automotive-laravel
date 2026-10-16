# YZA Automotive — Staging UAT Execution & Defect Closure

Tanggal: 15 Oktober 2026

Dokumen ini hanya mencatat verifikasi tambahan setelah final acceptance sebelumnya. Audit dan regression suite yang sudah selesai tidak diulang.

## Status lingkungan

- Aplikasi yang tersedia adalah lokal (`APP_ENV=local`), bukan staging publik.
- PHPUnit memakai database terisolasi `yza_automotive_laravel_test` dan konfigurasi testing terpisah.
- Database Laravel lokal yang operasional tetap digunakan secara read-only untuk pemeriksaan; database PHP Native tidak disentuh.
- Backup Laravel tersedia di `storage/app/backups/`, termasuk backup sebelum penyesuaian finansial.
- Tidak ada staging publik yang dipublikasikan.
- Playwright lokal tidak terpasang; executable Chrome dan Edge tersedia untuk UAT manual. Browser automation sesi sebelumnya menggunakan browser automation bawaan Codex, bukan Playwright lokal.
- Panduan eksekusi manual tersedia di [MANUAL_UAT_GUIDE.md](MANUAL_UAT_GUIDE.md).
- Local UAT terisolasi tersedia di `http://127.0.0.1:8001` dengan database `yza_automotive_laravel_uat`; evidence tambahan ada di [uat-local/README.md](../uat-local/README.md).
- Smoke browser 9 role pada UAT benar-benar dijalankan dan masing-masing mencapai dashboard role yang benar; ini bukan bukti CRUD penuh.
- Konfigurasi Midtrans lokal berada pada mode non-production, tetapi callback HTTPS dan refund provider belum dapat dibuktikan dari lingkungan ini.

## Pengujian browser yang benar-benar dijalankan

| Area | Hasil | Bukti aktual |
|---|---|---|
| Super Admin login dan dashboard | PASS | `superadmin506` mencapai `/admin/dashboard` |
| Owner login dan dashboard | PASS | `owner506` mencapai `/owner/dashboard` |
| Sales login dan dashboard | PASS | `sales506` mencapai `/sales/dashboard` |
| Service Advisor login dan dashboard | PASS | `advisor506` mencapai `/staff/dashboard` |
| Mekanik login dan dashboard | PASS | `mekanik506` mencapai `/staff/dashboard` |
| Gudang login dan dashboard | PASS | `gudang506` mencapai `/staff/dashboard` |
| Finance login dan dashboard | PASS | `finance506` mencapai `/staff/dashboard` |
| Operasional login dan dashboard | PASS | `operasional506` mencapai `/staff/dashboard` |
| QC/PDI login dan dashboard | PASS | `qc506` mencapai `/staff/dashboard` |
| Akses lintas role Sales → Owner | PASS | Menghasilkan HTTP 403/Forbidden |
| Full CRUD, filter, pagination, modal, dan error-message browser UAT | MANUAL UAT REQUIRED | Belum dibuktikan melalui staging terisolasi pada sesi ini |

PASS pada tabel role berarti login dan halaman dashboard sesuai role, bukan bukti seluruh CRUD role tersebut.

Screenshot browser telah diambil selama UAT sebelumnya dan tersimpan sebagai bukti sesi; tidak ada screenshot baru yang dipalsukan untuk menutup gap yang belum diuji.

## Zoom dan export

- Native browser zoom 100% dan 67% pada Chrome serta Edge: **MANUAL UAT REQUIRED**. Browser automation yang tersedia hanya menyediakan kontrol viewport, bukan kontrol native zoom yang ekuivalen.
- Pengukuran responsive pada viewport 375, 390, 768, 1366, dan 1920 tidak menemukan horizontal overflow dokumen pada landing/register.
- Route, authorization, dan content-type export Owner telah lulus automated tests.
- Membuka file PDF/XLSX secara langsung untuk memeriksa isi, periode, nominal, konsistensi angka, dan keterbukaan file: **MANUAL UAT REQUIRED**. Percobaan batch download browser dihentikan karena browser menolak download paralel; ini bukan defect aplikasi yang terverifikasi.

## End-to-end

- Pembelian mobil sampai PDI, handover, dan My Garage: **BLOCKED untuk browser staging E2E**. Automated domain/integration coverage lulus, tetapi belum ada staging terisolasi dengan fixture browser dan operator UAT yang dapat membuktikan alur mutasi penuh.
- Booking servis sampai penugasan mekanik, QC, pembayaran, dan riwayat kendaraan: **BLOCKED untuk browser staging E2E** dengan alasan yang sama.
- Tidak ada pembayaran provider yang dipalsukan atau dinyatakan berhasil tanpa konfirmasi provider.
- Regression final yang telah dijalankan: **407 PASS, 0 FAIL, 0 SKIPPED, 1.488 assertions**.

## Midtrans dan refund

- Mode konfigurasi lokal non-production terdeteksi dan automated sandbox contract tests lulus.
- Callback HTTPS sandbox nyata: **BLOCKED** karena belum tersedia public HTTPS staging URL, webhook yang dapat diakses provider, dan kredensial sandbox yang siap dipakai untuk transaksi UAT.
- Refund/partial refund/reconciliation provider: **BLOCKED** karena capability dan otorisasi refund sandbox belum tersedia.
- Sales Awards tetap fail-closed/provisional sampai sumber refund dan kebijakan bisnis disahkan. Tidak ada refund production yang diaktifkan.

## Defect closure

Tidak ada defect aplikasi baru yang terbukti dari sesi gap UAT ini, sehingga tidak ada perubahan kode tambahan yang dibenarkan. Perbaikan responsive yang sudah ada tetap tercakup oleh regression final.

## Keputusan release

**READY FOR STAGING UAT / NOT READY FOR PRODUCTION.**

Kriteria yang masih harus ditutup sebelum production: staging terisolasi dengan backup/restore drill, browser CRUD/E2E lengkap, native zoom 67% Chrome/Edge, inspeksi langsung seluruh PDF/XLSX, HTTPS callback Midtrans sandbox, refund sandbox dan rekonsiliasi, serta pengesahan kebijakan finalisasi Sales Awards.
