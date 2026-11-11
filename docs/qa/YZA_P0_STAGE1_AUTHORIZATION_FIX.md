# YZA Automotive — P0 Stage 1: RBAC & Work Order Authorization Hardening

Tanggal: 31 Oktober 2026 (Asia/Jakarta)  
Project: `C:/xampp/htdocs/yza_automotive_laravel`  
Branch aktual saat mulai dan selesai: **feature/vehicle-purchase-flow**  
HEAD aktual: **0359b1e8fa28aeceac5aa8491bb7eb5d85672ea6**  
Status: **COMPLETE untuk cakupan P0 tahap 1 yang diuji**. Tidak ada bypass otorisasi tahap ini yang masih berhasil direproduksi pada pengujian akhir. Status ini bukan persetujuan production atau penyelesaian P0 pembayaran/QC/promo.

## 1. Ruang lingkup dan kondisi awal

Dokumen audit sumber, inventory route, dan JUnit historis dibaca. Branch/HEAD dicek ulang; bukan diasumsikan dari laporan. Baseline audit adalah 434 pass / 1.650 assertions / 0 failure / 0 skipped.

Terdapat 53 file tracked termodifikasi sebelum tahap ini serta source/view/CSS/test untracked. Snapshot isi dan SHA-256 sepuluh file target yang sudah ada diambil sebelum edit. Perubahan diterapkan terhadap isi working tree tersebut, bukan mengembalikan file ke HEAD. Perubahan lokal Owner/Admin yang tidak terkait tetap dipertahankan. Tidak ada git reset, commit, push, deploy, migration, perubahan .env, seed ke database operasional, atau perbaikan fitur P0 lainnya.

Fokus temuan audit: **B01** (view permission menjadi izin mutasi/fallback) dan **B02** (mutasi WO tidak memeriksa assignment aktif). Pemeriksaan juga mencakup alias Petugas, child-resource IDOR, pengecualian otorisasi yang tertangkap sebagai redirect biasa, dan pemanggilan service langsung dengan principal yang sedang login.

## 2. Temuan, reproduksi dan akar masalah

| Temuan | Sebelum | Setelah | Bukti Pengujian | Status |
|---|---|---|---|---|
| B01 — Owner mengubah pengaturan | PUT /admin/pengaturan memakai fallback dashboard.view; PengaturanController::updateMany tidak menambahkan guard role | Mutasi bisnis Owner ditolak 403; pengaturan memerlukan staff.manage pada jalur non-Owner | test_owner_cannot_update_settings_without_changing_database; baseline expected 403 mendapat 302 | FIXED |
| B01 — kategori/supplier dan modul tak terpetakan | Kategori/supplier tidak masuk pemetaan inventory sehingga fallback dashboard.view juga berlaku untuk PATCH | Pemetaan modul eksplisit inventory.view/manage; unknown module deny by default | test_owner_cannot_toggle_category_without_changing_database; test_cross_role_direct_requests_and_unknown_modules_fail_closed | FIXED |
| B02 — mekanik menyasar WO lain | Banyak POST/PUT/DELETE admin.servis hanya lolos service.assigned tanpa memeriksa resource | Semua resource WO staf melalui assignment guard dan kemampuan work/manage/qc di backend | test_every_work_order_mutation_rejects_other_mechanics_and_body_id_spoofing | FIXED |
| B02 — assignment dicabut tetapi kolom utama tertinggal | AssignedWorkOrderMiddleware menerima id_petugas cocok walaupun row assignment sudah unassigned | Ledger aktif menjadi sumber kewenangan; kolom utama saja tidak cukup | test_revoked_primary_cannot_use_stale_primary_column_to_open_work_order; baseline GET mendapat 302 | FIXED |
| Missing/future/inactive assignment | Record yang tidak ada atau belum efektif tidak diperiksa secara memadai | Harus primary/assistant, assigned_at <= now, unassigned_at null, user dan petugas aktif | test_missing_revoked_future_and_inactive_assignments_reject_mutations | FIXED |
| QC/assignment/finalisasi tersirat dari service.assigned | Mechanic branch sebelumnya memperlakukan semua admin.servis mutations seragam | Technical work terpisah dari manage dan qc; penugasan tidak otomatis memberikan QC/finalisasi/assignment | test_primary_and_assistant_can_work_but_cannot_assign_finalize_or_qc | FIXED |
| Alias Petugas memperluas hak modern Advisor | Grup /petugas menerima service_advisor tetapi tidak memakai module.access | Advisor memakai pemetaan izin yang sama melalui alias; privilege legacy Petugas tetap hanya pada surface legacy | test_advisor_can_work_but_alias_does_not_grant_finance_or_inventory_actions | FIXED |
| Child ID dari WO berbeda | Service akhirnya menyaring child, tetapi error dapat berubah menjadi redirect melalui catch Throwable | Bound detail/sparepart/approval harus berasal dari resource WO URL; mismatch ditolak sebelum controller | test_mismatched_child_url_returns_404_without_deleting_another_order_detail | HARDENED |
| Controller catch dan pemanggilan service | Error otorisasi service dapat menjadi 302/422; service staf sendiri tidak memeriksa actor login | AuthorizationException diteruskan; service mutasi staf memeriksa principal sebenarnya | test_authenticated_direct_service_calls_cannot_bypass_controller_guards | FIXED |
| Palsukan identitas customer saat memanggil approval service | decide memeriksa customerId parameter terhadap WO, tanpa mencocokkan principal login | Principal login harus pelanggan pemilik customerId; mekanik tidak dapat memakai ID customer sebagai otorisasi | Direct-service negative test; existing customer approval tests tetap lulus | HARDENED |

Reproduksi awal dijalankan **sebelum perubahan production source** melalui empat regression test pada DB testing terisolasi. Keempatnya gagal: expected 403 tetapi mendapat 302. Kasusnya adalah settings Owner, toggle kategori Owner, finding mekanik unassigned, dan akses primary yang dicabut. Hasil disimpan pada [YZA_P0_STAGE1_BEFORE.xml](YZA_P0_STAGE1_BEFORE.xml). Ini bukti respons endpoint tidak menolak akses; assertion snapshot pada kasus awal tidak dilanjutkan setelah assertion HTTP gagal. Tidak mengklaim checksum before/after kasus awal sebagai hasil yang sudah diukur.

Lokasi root cause sebelum perubahan:

- `app/Http/Middleware/RoleModuleAccessMiddleware.php::handle`: default dashboard.view; eskalasi mutation hanya dilakukan untuk beberapa permission view; mechanic blanket exception.
- `routes/web.php`: assigned.wo hanya pada sebagian GET WO; mutation dan AJAX search belum lengkap; grup petugas tidak memakai module.access.
- `app/Http/Middleware/AssignedWorkOrderMiddleware.php::handle`: id_petugas OR active assignment.
- `app/Http/Controllers/ServiceAssignmentController.php::update`: daftar role masih menyebut Owner sebagai pelaku assignment.
- `OperationsServisController`, `ServiceExecutionController`: catch Throwable mengonversi exception menjadi feedback biasa.
- `ServisService`, `ServisSparepartService`, `ServiceExecutionService`, `ServiceAssignmentService`: parameter actor/id bukan bukti bahwa principal login berwenang.

## 3. Desain perbaikan

### RBAC existing tetap dipakai

Tidak membuat tabel, role, permission, atau sistem RBAC baru. `User::hasPermission`, relasi Role/Permission dan alias admin→super_admin tetap dipakai.

RoleModuleAccessMiddleware sekarang mempunyai mapping **view dan write terpisah**. Kategori/supplier masuk inventory; settings/staff/roles memerlukan staff.manage; audit log memerlukan audit.view; customer/promo mutation tidak menggunakan permission melihat data. Route/resource yang tidak dikenal ditolak untuk user non-super-admin. Super Admin tetap memiliki wewenang luas existing.

Owner ditolak untuk mutation bisnis pada surface tersebut walaupun dapat melihat laporan. Satu pengecualian eksplisit adalah menandai notifikasi sendiri dibaca: ini bukan perubahan transaksi/operasional, dan controller tetap memeriksa pemilik notifikasi. Tidak menambahkan atau mencabut permission DB Owner. GET laporan/export existing tetap mengikuti route/permission sebelumnya.

Grup /petugas sekarang juga memakai module.access. Role **petugas literal** tetap mempertahankan surface operasional legacy yang sebelumnya diizinkan; ini tidak memberi semua role modern privilege Petugas. Advisor melalui alias tetap harus mempunyai permission modul sesuai aksi. Owner tidak masuk grup alias tersebut.

### Otorisasi WO terpusat

Service baru `app/Services/WorkOrderAuthorizationService.php` mengimplementasikan:

| Kemampuan | Kebijakan |
|---|---|
| view | super_admin/legacy admin, legacy petugas, atau service.view; mechanic tetap harus assignment aktif |
| work | manager servis atau mechanic dengan service.assigned dan assignment aktif |
| manage | super_admin/legacy admin, legacy petugas, atau service.manage untuk role non-Owner/non-mechanic |
| qc | manager existing atau qc.manage untuk role non-Owner; mechanic tidak memperoleh hak ini hanya dari assignment |

Manager existing mencakup Advisor dengan service.manage. Mempertahankan Advisor/Petugas yang sebelumnya sah menjalankan kontrol QC; tidak mengubah hasil checklist atau syarat finalisasi bisnis. Mechanic tanpa qc.manage ditolak meskipun menjadi primary/assistant. Finalisasi dan perubahan assignment merupakan kemampuan manage, bukan efek otomatis service.assigned.

Principal dibaca dari autentikasi server dan di-refresh dari DB untuk status/role/petugas; nilai id_petugas, id_servis, id_pelanggan dalam body tidak dijadikan bukti wewenang. Ledger `service_work_order_assignments` harus memuat row primary/assistant yang sudah berlaku dan belum dicabut. Tidak ada fallback dari `servis.id_petugas` untuk menghidupkan akses yang sudah dicabut.

Semua **37 route resource WO staf**, termasuk **23 route mutasi**, menggunakan shared assignment guard. Mutation action juga dipetakan eksplisit; endpoint WO baru yang tidak dikenal tidak otomatis menjadi technical work. AJAX pencarian sparepart mengikuti resource WO yang sama.

### Service dan resource scope

- Mutation service staf memeriksa actor login sebelum business write. ServisService dan ServisSparepartService memeriksa setelah row WO dikunci.
- Finding, request approval, klarifikasi dan QC memeriksa otorisasi di dalam transaksi dengan row WO terkunci. Dengan demikian revokasi assignment melalui service assignment yang juga mengunci WO tidak hanya bergantung pemeriksaan middleware sebelum transaksi.
- ServiceAssignmentService memeriksa kemampuan manage, sehingga pemanggilan langsung tidak dapat dipakai mekanik/Owner untuk self-assignment.
- Approval decision customer memeriksa kecocokan principal pelanggan dan customerId; request tetap memakai customer scope existing.
- Child-resource URL dari WO lain menghasilkan 404, sebelum controller mengubah error menjadi redirect.
- AuthorizationException diteruskan sehingga request staf yang tidak berwenang menghasilkan 403, bukan success-like 302 atau error bisnis 422.

Service domain masih mendukung **trusted console tanpa principal login** untuk importer/maintenance dan fixture setup existing. Ini bukan API anonim: route HTTP tetap memerlukan auth; pemanggilan service pada runtime HTTP tanpa user ditolak. Jika ada user login, termasuk pada test/console, user tersebut tetap diperiksa. Akses eksekusi PHP/SQL langsung oleh operator mesin berada di luar batas aplikasi ini.

## 4. Daftar file yang diubah pada tahap ini

Path relatif terhadap root proyek:

| File | Perubahan tahap 1 |
|---|---|
| app/Services/WorkOrderAuthorizationService.php | Baru; pusat kemampuan dan assignment aktif |
| app/Http/Middleware/RoleModuleAccessMiddleware.php | Mapping eksplisit view/write, Owner guard, per-action WO, fail closed |
| app/Http/Middleware/AssignedWorkOrderMiddleware.php | Ledger assignment aktif, status principal, child-resource scope |
| routes/web.php | Guard pada seluruh resource/mutasi/search WO staf; module.access pada alias Petugas |
| app/Http/Controllers/OperationsServisController.php | Daftar mekanik memakai primary/assistant aktif; AuthorizationException diteruskan; alias super_admin dikenali dan redirect memakai prefix request |
| app/Http/Controllers/ServiceExecutionController.php | Exception otorisasi tetap 403, termasuk AJAX/customer decision |
| app/Http/Controllers/ServiceAssignmentController.php | Hapus Owner dari pelaku assignment; authorize manage; exception diteruskan |
| app/Services/ServisService.php | Otorisasi check-in/manage, technical changes, dan finalisasi/manage |
| app/Services/ServisSparepartService.php | Guard mutation sparepart draft |
| app/Services/ServiceExecutionService.php | Guard finding/approval/clarification/QC, lock saat authorization, principal customer untuk decision |
| app/Services/ServiceAssignmentService.php | Guard assignment service langsung |
| tests/Feature/AuthorizationHardeningStage1Test.php | Baru; 18 tes regression tahap ini |

Perubahan check-in role alias/redirect dan daftar assistant hanya menyelaraskan jalur akses yang dikeraskan. Tidak mengganti status/transisi WO, harga, kalkulasi invoice, kebijakan stok, payment, membership, PDI/handover, ownership, atau algoritme promo. Tidak ada perubahan UI baru atau schema.

## 5. Bukti Owner read-only

- GET Owner dashboard, sales report, service report, payment report, inventory report dan performance report berhasil pada HTTP tests.
- HTTP mutation Owner untuk settings, kategori, WO inspection/QC/finalization/assignment, stock adjustment, mobil stock, penjualan mobil/sparepart, refund request dan payment confirmation ditolak 403.
- Sweep middleware meliputi **108 kombinasi route/metode bisnis admin yang terdaftar**, di luar self-notification read, semuanya ditolak bagi Owner sebelum handler sentinel dipanggil.
- Snapshot hash isi **28 tabel** dibandingkan sebelum/sesudah request yang ditolak, termasuk users/petugas/permission pivot, pengaturan, kategori/supplier, booking/kendaraan, WO/assignment/jasa/parts/approval/QC/events, stok/history, transaksi/payment/notifikasi, penjualan/mobil/inventory dan adjustment. Tidak ada perubahan isi pada tabel yang diperiksa.
- Sweep middleware melengkapi HTTP tests; tidak diklaim sebagai 108 browser test atau 108 controller mutation yang benar-benar dieksekusi.
- Endpoint Owner Awards finalization tetap memerlukan super_admin dari kebijakan existing; tidak diubah pada tahap ini.

## 6. Bukti assignment mekanik, Advisor dan QC

- Primary dan assistant aktif dapat membuka WO, menyimpan diagnosis, dan membuat finding pada WO sendiri.
- Primary/assistant tersebut tidak otomatis boleh menjalankan QC, finalisasi atau assignment.
- Seluruh **12 jenis mutation WO admin** diuji terhadap mekanik lain: inspection, add/remove jasa, add/remove part, finding, request approval, clarification, QC start/update, finalisasi dan assignment.
- Body id_servis/id_petugas palsu tidak membuat mechanic dapat menyasar WO URL lain. Saat URL sah dan body menunjuk WO lain, hanya WO URL sah yang berubah dan actor berasal dari sesi.
- Row assignment hilang, dicabut, belum berlaku, atau petugas nonaktif ditolak. Kolom id_petugas yang tertinggal tidak lagi memberi akses.
- Child detail dari WO lain menghasilkan 404 dan tidak terhapus.
- Advisor dapat menambah finding melalui /admin dan /petugas, serta melakukan assignment ke teknisi eligible dengan riwayat tetap tersimpan.
- Advisor tidak dapat menggunakan alias Petugas untuk verifikasi pembayaran tanpa finance.verify; inventory mutation juga ditolak.
- QC dapat memulai/mengisi QC hingga passed pada skenario legal existing, tetapi tidak dapat mengubah diagnosis/jasa/assignment atau memfinalisasi WO tanpa service.manage.
- Guest web mendapat redirect login; guest JSON mendapat 401; staf login tanpa hak mendapat 403. Child mismatch mendapat 404 agar resource yang tidak berada pada WO itu tidak diperlakukan sebagai target valid.
- Penolakan pemanggilan service langsung diuji untuk inspection, jasa, sparepart removal, finding, QC, forged customer decision, assignment dan finalization.

Schema existing memakai **satu id_role pada users**, bukan multi-role assignment per user. Tidak menambahkan dukungan multi-role fiktif. Satu role dapat mempunyai beberapa permission melalui pivot; keputusan memeriksa permission existing beserta batas Owner dan assignment mekanik, bukan label/menu frontend.

## 7. Isolasi dan hasil pengujian

Preflight read-only mengonfirmasi database `yza_automotive_laravel_test`, MariaDB 10.4.28, 54 migration. Environment setiap proses PHPUnit ditetapkan eksplisit ke DB test, APP_ENV=testing, DB_URL kosong, cache/session array, queue sync dan LOG_CHANNEL=null. TestCase guard existing tetap menolak database selain nama test. Seluruh tes baru memakai DatabaseTransactions; seeding role/personel pada setup hanya terjadi pada DB test dan rollback.

| Run | Tes | Assertions | Failure | Error | Skipped | Hasil |
|---|---:|---:|---:|---:|---:|---|
| Audit historis, tidak diubah | 434 | 1.650 | 0 | 0 | 0 | Bukti historis |
| Empat reproduksi sebelum fix | 4 | 4 | 4 | 0 | 0 | Expected: celah dapat direproduksi |
| Regresi intermediate setelah empat fix awal | 438 | 1.658 | 0 | 0 | 0 | PASS; bukan hasil final |
| Focused tahap 1 akhir | **18** | **355** | **0** | **0** | **0** | **PASS** |
| Full regression akhir | **452** | **2.005** | **0** | **0** | **0** | **PASS** |

Full regression akhir console: **55,428 detik**, **172 MB**, exit code 0. JUnit aggregate time 54,513315 detik. Focused console: 4,368 detik. Full suite mencakup 434 tes sebelumnya ditambah 18 tes tahap ini; tidak ada test lama dihapus atau assertion dilemahkan.

Satu run focused selama pengembangan sempat error karena fixture servis_sparepart menggunakan create dengan field nominal yang guarded oleh model. Fixture diperbaiki menjadi forceCreate khusus di test untuk menyiapkan row valid. Tidak melonggarkan model, request atau production validation untuk mengatasi error fixture tersebut.

Bukti:

- [Reproduksi sebelum perbaikan](YZA_P0_STAGE1_BEFORE.xml)
- [Focused 18 tes](YZA_P0_STAGE1_FOCUSED.xml)
- [Regresi intermediate](YZA_P0_STAGE1_REGRESSION_INITIAL.xml)
- [Regresi akhir 452 tes](YZA_P0_STAGE1_REGRESSION.xml)

Perintah inti: `C:/xampp/php/php.exe vendor/bin/phpunit`, dengan `--no-progress --do-not-cache-result` dan output JUnit ke docs/qa. Focused menggunakan `tests/Feature/AuthorizationHardeningStage1Test.php`.

Lint PHP pada 12 file source/route/test yang terlibat: **PASS**. `git diff --check` pada file tahap ini: **PASS**.

Setelah pengujian, pembacaan read-only operasional mengonfirmasi jumlah row tetap: users 18, pelanggan 10, membership 10, mobil 26, aggregate inventory 12, order member 2, servis 6, transaksi 13, pembayaran 18, gateway 8, kendaraan pelanggan 2. Ini pengecekan jumlah row, bukan klaim checksum menyeluruh DB operasional. Tidak ada perintah mutasi diarahkan ke database operasional.

## 8. Klarifikasi 75 fitur vs 61 fitur audit

Laporan sumber sebenarnya memuat **75 baris fitur (F01–F75)** dengan klasifikasi:

- COMPLETE & VERIFIED: 40
- IMPLEMENTED — NOT VERIFIED: 3
- PARTIAL: 8
- UI ONLY: 1
- NOT IMPLEMENTED: 4
- BLOCKED: 3
- NOT ASSESSED: 2
- BUG FOUND: 14

**40 + 3 + 8 + 1 + 4 + 3 + 2 = 61**. Selisih terhadap 75 adalah **14 baris berstatus BUG FOUND**. Jika semua delapan kategori dijumlahkan, totalnya **75**. Jadi bukan 14 fitur yang hilang dari inventory atau 14 temuan tambahan di luar inventory; kategori bug telah dipakai sebagai status utama, terpisah dari kategori lainnya.

Dokumen audit dan JUnit historis tidak diubah. Laporan tahap 1 ini tidak menghitung ulang atau mengklaim semua 14 bug sudah diperbaiki; yang ditangani adalah cakupan RBAC/WO authorization B01/B02 beserta bypass terkait.

## 9. Risiko dan pekerjaan yang sengaja belum diselesaikan

1. **QC fungsional B05 tetap terbuka**: guard finalisasi existing masih menerima kondisi tidak ada row QC. Tahap ini hanya membatasi siapa boleh memfinalisasi; tidak mengubah kewajiban QC, checklist, status, approval estimate atau state machine.
2. **Pembayaran B03/B04, atomic promo B09, PDI, points, stok aggregate/identitas fisik dan ledger finance** tidak diperbaiki di sini.
3. **UI** dapat tetap menampilkan tombol yang ditolak backend untuk sebagian role; ini tidak lagi menjadi sumber wewenang. Polishing sidebar/tombol/keterangan bukan cakupan tahap ini.
4. **Dashboard mechanic lama** masih mempunyai KPI berbasis id_petugas utama. Daftar WO operasional sudah memakai ledger primary/assistant aktif; perombakan definisi KPI tidak dilakukan.
5. **Trusted console/import** tetap dapat menjalankan service tanpa principal login; operator kode/SQL langsung bukan tenant aplikasi. Jika kelak dibuat job/API/import baru yang menerima input tidak tepercaya, entry point wajib memanggil authorization dengan principal yang sah.
6. **Browser UAT, negative CSRF, load/concurrency stress** belum dijalankan di tahap ini. Harness existing menonaktifkan CSRF umum dan membekukan waktu untuk fixture; tidak ada klaim browser/provider nyata.
7. Strict assignment tidak melakukan backfill ke data operasional. Mekanik dengan id_petugas lama tetapi tanpa ledger aktif sekarang ditolak secara sengaja. Admin/Advisor dapat memakai proses assignment resmi existing untuk memperbaiki penugasan yang memang sah.
8. Tidak mengaktifkan permission/role baru atau menyesuaikan seed operasional. Perubahan akses master/promo/audit memakai permission existing yang lebih tepat dan fail closed.
9. Jaminan hasil terbatas pada route/code snapshot dan skenario teruji. Tidak mengklaim audit seluruh keamanan aplikasi atau production readiness.

## 10. Penilaian penyelesaian tahap 1

**P0 Stage 1 selesai dalam cakupan yang diminta**: reproduksi awal terdokumentasi; akses Owner untuk mutasi bisnis ditutup; seluruh resource WO staf mendapat guard; assignment aktif primary/assistant menjadi sumber otoritatif; alias Petugas tidak menambahkan hak Advisor; QC/manage dipisah dari technical work; akses service dan child-resource juga dijaga; focused serta full regression final lulus.

Tidak ada bypass tahap 1 yang masih berhasil direproduksi atau dependency pengujian penting yang menghalangi hasil di atas. Masalah P0 QC fungsional, payment dan promo tetap dicatat sebagai pekerjaan tahap berikutnya, bukan diam-diam dianggap selesai.

**Berhenti setelah tahap ini. Tidak melakukan commit, push, deployment, atau melanjutkan implementasi pembayaran/QC/promo.**

