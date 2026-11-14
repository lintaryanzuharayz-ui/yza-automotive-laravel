# YZA Automotive — Duplicate Super Admin Retirement

Tanggal: 8 November 2026 (Asia/Jakarta). Database operasional: `yza_automotive_laravel`.

## Hasil akhir

**`superadmin506` dinonaktifkan, bukan dihapus permanen.** Username/primary key dan semua referensi tetap ada untuk menjaga histori. Akun dan profil stafnya berstatus `nonaktif`; login baru ditolak, satu session terenkripsi target dicabut, dan sesi lama ditolak oleh pemeriksaan status akun pada seluruh web routes.

**`admin506` tetap aktif dan effective Super Admin.** Role, permission dan password tidak diubah. Akun utama tersebut tidak digabung atau ditukar ID dengan akun target.

## Identitas yang diverifikasi

| Akun | User PK | Stored role | Effective role | Staff PK | Status sesudah |
|---|---:|---|---|---:|---|
| admin506 / Administrator YZA | 1 | admin | super_admin | 1 | User aktif; Petugas aktif |
| superadmin506 / Super Admin YZA | 992 | super_admin | super_admin | 242 | User nonaktif; Petugas nonaktif |

`Role::key()` tetap memetakan admin→super_admin. `User::hasPermission()` tetap memberi effective Super Admin kemampuan existing; tidak ada grant baru, perubahan role, migration role atau reset password. Otorisasi didasarkan username dan PK terverifikasi, bukan display name.

## Audit dependency dan alasan tidak hard-delete

User tidak menggunakan SoftDeletes; tabel users tidak mempunyai deleted_at. FK diinspeksi melalui information_schema, termasuk seluruh referensi ke users dan petugas.

Target memiliki:

- 1 profil `petugas.id_user → users.id_user`, **ON DELETE RESTRICT**.
- 3 record `notifikasi.id_user → users.id_user`, **ON DELETE CASCADE**.
- Tidak ditemukan row FK bisnis pada target user/staff untuk order/sales, pembayaran/invoice/transaksi, keputusan pesanan/SPK, refund/reversal, assignment WO, QC/PDI, stok dan serah-terima.
- Kolom actor `_by` yang tidak mempunyai FK juga diperiksa terhadap kedua kandidat PK; tidak ditemukan kandidat target. ID User 992 dan Petugas 242 tidak disamakan atau dialihkan ke ID 1.
- Prefix log aplikasi sebelum operasi dipertahankan; audit actor ID 992 tidak ditemukan pada snapshot awal log, tetapi tidak ada baris log yang dihapus atau diganti identitasnya.

Hard delete akan memerlukan mengatasi RESTRICT dan berisiko menghapus notifikasi via cascade. Sesuai keputusan pemilik, dipilih penonaktifan existing, bukan memaksa penghapusan permanen. Profil staf target juga dibuat nonaktif agar tidak muncul dalam filter staf aktif; tetap terlihat jika seluruh status diminta untuk kebutuhan histori.

## Pelaksanaan operasional

Command tetap-terbatas: `yza:retire-duplicate-super-admin`. Default hanya dry-run. Apply wajib menyebut database dan kedua PK:

```text
php artisan yza:retire-duplicate-super-admin --apply --target-id=992 --retained-id=1 --database=yza_automotive_laravel
```

Operasi berhasil, operation ID **b790486f-0729-42fd-89d7-f885bc4c0423**. Di dalam transaksi:

1. Kunci kedua akun dan profil target; verifikasi username, PK berbeda, status/role akun utama.
2. Fingerprint 76 tabel, dengan hanya row User/Petugas target dikecualikan.
3. Tulis backup terenkripsi, lalu uji decrypt round-trip sebelum mengubah data.
4. Ubah hanya status dan updated_at User 992 serta Petugas 242. Tidak mengganti password atau actor historis.
5. Cocokkan fingerprint tabel lainnya, seluruh atribut akun utama, dan password target; perbedaan membatalkan transaksi.
6. Commit, cabut session target, kemudian append audit event `master.audit/deactivate`. Actor console tetap null/system, tidak mengklaim operasi dilakukan oleh admin506.

Hasil pemeriksaan **pada saat commit**: seluruh data di luar dua row target tetap sama, termasuk role/permission, notifikasi, pelanggan, invoice/pembayaran, stok dan seluruh histori bisnis. Tidak melakukan cascade/delete data bisnis.

## Backup dan pemulihan

Backup terenkripsi yang berhasil diverifikasi:

`storage/app/private/account-retirement/superadmin506-992-b790486f-0729-42fd-89d7-f885bc4c0423.json.enc`

Receipt:

`storage/app/private/account-retirement/receipt-b790486f-0729-42fd-89d7-f885bc4c0423.json`

Backup berada di luar public web root dan menggunakan Laravel Crypt/app key existing; jangan mempublikasikan file atau app key. Berisi row asli User/Petugas target dan fingerprint tabel lainnya. Tidak menyimpan password plaintext. Jika kelak pemilik mengotorisasi pemulihan, maintenance terpercaya dapat mendekripsi dengan app key yang sama, memverifikasi username/PK, lalu memulihkan field status target dari backup dalam transaksi. Jangan otomatis memulihkan sesi yang sudah dicabut, menimpa histori, atau mengganti akun utama. Tidak menjalankan pemulihan dalam pekerjaan ini.

## Session dan token

- Driver efektif **file**, encryption **true**, lifetime 120 menit.
- Sebelum operasi: 1 file session milik User 992; sesudah: **0**. Satu file target dicabut; 0 file tidak terbaca.
- File hanya dipilih setelah decrypt/deserialize aman dan pemeriksaan `login_web_*` sama dengan PK target. File session akun lain/guest tidak dihapus.
- Tidak ada tabel personal access token/session database pada konfigurasi ini; satu-satunya auth guard yang terdaftar adalah web/session. users juga tidak mempunyai remember_token. Tidak mengklaim mencabut token provider eksternal yang tidak ada dalam implementasi.
- `EnsureActiveAccount` memeriksa status terkini pada web middleware, termasuk Dashboard/profil/Owner reports. Ini menutup penggunaan sesi yang tertinggal atau tercipta kembali akibat race. Logout current device tidak mengganti password/token akun lain.
- Request yang sudah selesai sebelum penonaktifan tidak dapat dibatalkan secara retroaktif; request berikutnya milik akun nonaktif ditolak.

## Login dan hak akses admin506

- Query autentikasi existing membutuhkan User aktif; admin506 tetap memenuhi, superadmin506 tidak.
- Login dengan kredensial fixture admin506 diuji pada DB testing terisolasi, sebelum/sesudah retirement fixture; tidak mereset password operasional untuk pengujian.
- Audit operasional sesudah perubahan menemukan session terenkripsi untuk **User 1** dan last_login admin506 yang lebih baru dari snapshot awal. Sesi target tidak tersisa. Tidak meminta/mencatat plaintext password pemilik atau melakukan brute-force login.
- Admin506 tetap stored admin / effective super_admin, profil staf aktif. Seluruh 36 menu diuji melalui HTTP di suite menu; Owner read-only dan batas Sales, Finance, Mekanik, Warehouse, Advisor, Operasional, QC tetap diuji.

## Perubahan setelah operasi yang dibedakan dari operasi retirement

User melanjutkan penggunaan aplikasi sebelum verifikasi akhir. Snapshot yang diambil kemudian berbeda dari receipt pada `users`, `vehicle_inventories` dan `vehicle_inventory_histories`; last_login admin506 berubah dan muncul sesi admin506. Karena itu laporan **tidak** menyatakan seluruh database tetap identik selamanya setelah retirement atau menganggap perubahan berikutnya berasal dari retirement.

Preservation check transaksi retirement sendiri telah lulus sebelum commit. Snapshot belakangan tetap cocok pada tabel lain, termasuk pembayaran/invoice/transaksi/role/notifikasi. Tidak mencoba membatalkan atau menimpa aktivitas aplikasi yang terjadi sesudahnya. Bukti perbedaan dicatat jujur dalam `duplicate-admin-verification.json`.

Verifikasi FK akhir: **142 constraints diperiksa, 0 orphan**. Ketiga notifikasi target tetap ada. Prefix log lama identik; audit baru hanya ditambahkan.

## Pencegahan pembuatan ulang

Sumber pembuatan akun ditemukan pada `YzaSeedLocalStaffCommand`. Akun duplikat sekarang hanya boleh dibuat bila **environment testing DAN nama DB tepat `yza_automotive_laravel_test`**. Local/uat/operasional melewatinya, sekalipun account hilang; tidak ada flag untuk memaksakan pembuatan ulang operational duplicate. Fixture Super Admin literal pada pengujian tetap tersedia. Seeder/command staf tidak dijalankan terhadap DB operasional; akun/grant staf lain tidak diubah.

## File perubahan

- `app/Services/RetireDuplicateSuperAdminService.php`: operasi terikat dua identitas, backup, transaksi dan preservation check.
- `app/Services/AccountSessionRevoker.php`: pembersihan session terarah, mendukung file terenkripsi dan driver database/array.
- `app/Console/Commands/RetireDuplicateSuperAdminCommand.php`: dry-run dan apply dengan PK/database guard; tanpa HTTP endpoint atau hard delete.
- `app/Http/Middleware/EnsureActiveAccount.php`, `bootstrap/app.php`: status guard sesi lama; tidak mengubah permission.
- `app/Console/Commands/YzaSeedLocalStaffCommand.php`: duplicate hanya untuk fixture testing terisolasi.
- `tests/Feature/DuplicateSuperAdminRetirementTest.php`: preservation, guard PK, login/session, encrypted file session dan provisioning.
- Collector/verification CLI dan bukti JSON di docs/qa; backup/receipt privat di storage.

## Pengujian

- Referensi awal: **679 tests / 3988 assertions PASS** (`storage/logs/duplicate-admin-baseline.xml`).
- Terarah: **34 tests / 947 assertions PASS**, termasuk retirement, seluruh menu, role management, login 9 role dan hardening authorization (`storage/logs/duplicate-admin-focused.xml`).
- Full regression: **684 tests / 4030 assertions PASS**, 0 error/failure/skipped (`storage/logs/duplicate-admin-regression.xml`).
- PHP lint dan scoped git diff check PASS. Tidak ada perubahan frontend yang memerlukan build.
- Bukti audit: `duplicate-admin-before.json`, `duplicate-admin-after.json`, `duplicate-admin-verification.json`. Tidak memuat password, cookie atau isi session.

## Blocker dan batas hasil

Penghapusan permanen **tidak dilakukan** karena dependency RESTRICT dan cascade notifikasi. Target sudah tidak dapat dipakai login dan session lama dicabut/diblok; admin506 menjadi satu-satunya akun aktif dari dua akun administratif yang diperiksa. Role definition super_admin dan record inactive target dipertahankan untuk histori. Tidak menghapus transaksi/audit, reset password, mengubah Role::key/permission, mengubah akun staf lain, commit/push/deploy atau reset/clean.
