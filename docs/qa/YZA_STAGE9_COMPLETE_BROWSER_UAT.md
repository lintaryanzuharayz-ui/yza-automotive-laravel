# YZA Automotive — Stage 9 Browser UAT & Operational Verification

Tanggal: 15 Oktober 2026  
Lingkungan: Laravel lokal `http://127.0.0.1:8000`  
Status: **NOT READY FOR PRODUCTION — MANUAL/STAGING EVIDENCE REQUIRED**

## Ringkasan

Backend regression dan smoke browser inti berjalan baik. Seluruh 9 akun role staff dapat login setelah menggunakan username fixture yang benar, dashboard role tampil, dan pembatasan URL langsung menghasilkan 403. Jalur registrasi publik juga terverifikasi: konteks `membership` kembali ke `/#janji-temu`, sedangkan konteks default/login kembali ke `/login`.

Status belum dapat dinaikkan menjadi production-ready karena UAT CRUD lengkap seluruh menu, responsive zoom 67%, kredensial member aktif untuk browser E2E, callback Midtrans HTTPS nyata, dan refund provider nyata belum dijalankan. Tidak ada transaksi atau refund uang nyata yang dilakukan.

## Database dan backup

- Database aplikasi Laravel lokal yang dipakai adalah `yza_automotive_laravel`.
- Database testing terpisah: `yza_automotive_laravel_test`.
- Database native PHP `yza_automotive` tidak disentuh; pemeriksaan tabel finansial menunjukkan tidak ada tabel Stage 7 di sana.
- Migration status Laravel lokal: seluruh migration sampai `2026_10_15_050000_create_financial_adjustments_tables` berstatus `Ran`.
- Pada awal verifikasi, `migrate:fresh --env=testing` terkena config cache dan sempat berjalan terhadap database lokal sampai migration inventory. Database Laravel lokal kemudian dipulihkan dari backup:
  `storage/app/backups/yza_automotive_laravel_before_financial_adjustments_20261015.sql`.
- Setelah pemulihan, hanya migration financial adjustment yang diperlukan diterapkan. Tidak ada penghapusan transaksi existing secara selektif atau perubahan terhadap database native.
- Database testing dibangun ulang terpisah dan diberi fixture non-produksi untuk regression test. Fixture tersebut tidak masuk ke database lokal aplikasi.

## Automated regression

| Suite | Hasil aktual |
|---|---:|
| Full regression | **404 passed, 0 failed, 3 skipped, 1.482 assertions** |
| Owner/Sales/RBAC focused | **19 passed, 100 assertions** |
| Financial/Midtrans/RBAC focused | **20 passed, 68 assertions** |
| Service/fitment/member/RBAC focused (sebelum full run) | **65 passed, 238 assertions** |

Skipped test yang masih ada:

1. `WorkOrderWizardPhaseTest`: tidak tersedia fixture Work Order berstatus inspection.
2. `ServiceExecutionPhase4ATest`: fixture hanya memiliki satu sparepart aktif.
3. `YzaPointsStage10BTest`: tidak tersedia customer legacy tanpa `id_user`.

Kegagalan awal yang terkait role auto-increment pada database testing merupakan kontaminasi fixture lama. Setelah database test dibangun ulang dan auto-increment role diperbaiki, full regression menjadi hijau. Tidak ada assertion yang dihapus atau dinonaktifkan.

## Browser UAT yang dijalankan

### Role staff

| Akun | URL hasil login | Hasil |
|---|---|---|
| `superadmin506` | `/admin/dashboard` | PASS |
| `owner506` | `/owner/dashboard` | PASS |
| `sales506` | `/sales/dashboard` | PASS |
| `advisor506` | `/staff/dashboard` — Dashboard Service Advisor | PASS |
| `mekanik506` | `/staff/dashboard` — Dashboard Mechanic | PASS |
| `gudang506` | `/staff/dashboard` — Dashboard Warehouse Staff | PASS |
| `finance506` | `/staff/dashboard` — Dashboard Finance | PASS |
| `operasional506` | `/staff/dashboard` — Dashboard Operational Staff | PASS |
| `qc506` | `/staff/dashboard` — Dashboard Quality Control | PASS |

Sidebar role untuk Owner dan Sales tampil dan memuat menu role masing-masing. Sidebar admin Super Admin juga tampil. Password fixture yang digunakan adalah username masing-masing, sesuai command `yza:seed-local-staff`.

### Authorization

- Sales yang membuka langsung `/owner/dashboard` menerima **403 Forbidden**. PASS.
- Logout melalui menu administrator kembali ke `/login`. PASS.
- UAT ini hanya membaca halaman dan tidak men-submit operasi bisnis yang mengubah data.

### Registrasi dan Membership publik

- Landing page menampilkan link `BUAT MEMBER` dengan href `/register?from=membership`. PASS.
- `/register?from=membership` menampilkan wizard Data Diri, Alamat, Membership, Pembayaran, Selesai dan tombol `KEMBALI KE MEMBERSHIP`. PASS.
- Tombol tersebut menuju `http://127.0.0.1:8000/#janji-temu` dan Membership tampil. PASS.
- Link login `Buat Member` menuju `/register`; konteks tanpa parameter tervalidasi sebagai fallback login. PASS.
- `/register` menampilkan `KEMBALI KE LOGIN`; tombol menuju `/login`. PASS.
- Akses register sebagai guest setelah logout juga PASS. Akses register sebagai staff tetap mengikuti guard yang ada.

### Responsive

- Viewport `390x844`: halaman registrasi, field Data Diri, dan tombol konteks tampil; screenshot browser berhasil dikumpulkan. PASS fungsional.
- Viewport `768x1024`: halaman registrasi dan tombol konteks tampil; screenshot browser berhasil dikumpulkan. PASS fungsional.
- Viewport default desktop: role dashboard dan halaman publik ter-render. PASS.
- Zoom 100%/67% belum dapat diverifikasi melalui kontrol browser yang tersedia. **Manual UAT required.**
- Pada screenshot 390px terlihat scrollbar horizontal browser. Ini dicatat sebagai temuan responsive untuk pemeriksaan visual lanjutan; belum diubah karena belum ada bukti elemen aplikasi mana yang menjadi sumber overflow dan perubahan CSS besar berada di luar scope tanpa reproduksi tambahan.

## Alur bisnis dan provider

- Service assignment, booking, ownership isolation, mechanic authorization, fitment, approval, QC, finalisasi, financial adjustment, awards, dan Midtrans sandbox sudah tercakup oleh automated tests yang lulus.
- Browser E2E penuh member → booking → advisor → mechanic → QC → finalisasi belum dijalankan dengan akun member aktif karena kredensial member aktif tidak diberikan dan submit alur tersebut mengubah data bisnis.
- Pembayaran Midtrans yang dijalankan adalah sandbox/mock coverage pada test suite. Callback HTTPS provider nyata dan refund provider nyata belum dilakukan.
- Tidak ada pembayaran, refund, atau transaksi produksi yang dilakukan selama UAT ini.

## File dan perubahan Stage 9

Perubahan kode aplikasi baru tidak diperlukan dari browser audit ini. Laporan ini adalah artefak QA baru:

- `docs/qa/YZA_STAGE9_COMPLETE_BROWSER_UAT.md`

Perubahan database lokal yang dilakukan terbatas pada pemulihan backup Laravel lokal dan penerapan migration financial adjustment yang sebelumnya sudah menjadi bagian implementasi Stage 7. Database native PHP tidak dimodifikasi.

## Keputusan rilis

**NOT READY FOR PRODUCTION.** Regression automated PASS, smoke browser role/auth PASS, dan jalur navigasi registrasi PASS. Release masih menunggu UAT staging/manual untuk CRUD setiap menu dan form, browser E2E member dengan akun uji aktif, zoom 67%, callback Midtrans HTTPS, refund sandbox/provider, serta penyelesaian atau justifikasi tiga skipped fixture tests.

