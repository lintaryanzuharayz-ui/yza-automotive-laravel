# YZA Automotive - Final UAT Closure & Release Readiness Matrix

Tanggal pemeriksaan: 15 Oktober 2026 (Asia/Jakarta)

## Ringkasan

| Area | Status | Bukti utama | Owner / prasyarat |
|---|---|---|---|
| Kronologi pickup dan handover | PASS dengan temuan historis | `MemberVehiclePurchaseFlowTest`, `VehicleAggregateInventoryFlowTest` | Owner bisnis meninjau order historis |
| Guard handover terlalu awal | PASS | `test_handover_cannot_happen_before_scheduled_pickup_time` | Tidak ada |
| Ekspor Owner endpoint | PASS secara automated | `OwnerExecutiveDashboardStage4Test`, `SalesExcellenceAwardsStage5Test` | Inspeksi visual browser masih manual |
| Artefak PDF Owner terbaru | PASS parser/render; visual aplikasi PDF tetap manual | Enam PDF baru berhasil dibuat ulang, `pdfinfo` dan `pdftoppm` membaca/render masing-masing 1 halaman | Buka keenam file di PDF reader untuk sign-off visual penuh |
| Artefak PDF lama | FAIL historis / superseded | `owner-rekap-penjualan-uat.pdf` lama tetap malformed dan bukan bukti final | Jangan gunakan artefak lama |
| Artefak XLSX Owner terbaru | PASS struktural terbatas; Excel native manual | Enam workbook baru memiliki ZIP/XML valid, worksheet, baris data, dan numeric cells; belum dibuka di Microsoft Excel | Inspeksi Excel/LibreOffice manual |
| Zoom native Chrome/Edge 67% | MANUAL UAT REQUIRED | CUA tidak memberi bukti perubahan zoom native atau screenshot file | Operator dengan Chrome dan Edge |
| CRUD browser sembilan role | MANUAL UAT REQUIRED | Boundary RBAC automated; CRUD browser penuh belum dijalankan | UAT operator dengan fixture sintetis |
| Midtrans HTTPS webhook | BLOCKED | Route, signature, idempotency backend tersedia; endpoint publik HTTPS belum tersedia | Staging HTTPS, TLS, callback reachability, sandbox credentials |
| Refund provider | BLOCKED | Refund online sengaja fail-closed; adjustment offline diaudit | Provider capability dan kebijakan refund authoritative |
| Sales Awards finalization | BLOCKED by design | Finalisasi menolak tanpa sumber refund authoritative dan target lengkap | Owner menyahkan policy dan sumber refund |

## Detail skenario dan hasil aktual

| ID | Skenario | Ekspektasi | Aktual | Status | Evidence | Owner / prasyarat |
|---|---|---|---|---|---|---|
| A-01 | Audit order historis Toyota GR Supra | Timestamp dan timezone konsisten serta anomaly teridentifikasi | Order `ORD-202610-B8358FB0` memiliki pickup 16 Okt 2026 10:00 WIB dan handover 15 Okt 2026. Aplikasi menggunakan Asia/Jakarta. Ini adalah data historis yang terjadi sebelum guard. | PASS audit / anomaly historis | `FINAL_BUSINESS_RULE_UAT.md`; test flow | Owner bisnis menentukan perlakuan data historis |
| A-02 | Handover sebelum jadwal pada fixture baru | Server menolak sebelum waktu pickup | Ditolak dengan pesan `Serah-terima belum dapat dilakukan sebelum jadwal pengambilan tiba.` | PASS | `MemberVehiclePurchaseFlowTest.php` | Tidak ada |
| A-03 | Handover pada waktu yang sah | Handover berhasil sekali dan ownership dibuat sekali | Lulus dengan frozen time; pengulangan handover tidak membuat ownership kedua | PASS | `MemberVehiclePurchaseFlowTest.php`, `VehicleAggregateInventoryFlowTest.php` | Tidak ada |
| A-04 | Bypass route / authorization | Hidden button bukan kontrol keamanan; backend tetap menolak | Route memakai middleware/authorization yang ada; focused dan full regression lulus. | PASS automated | `routes/web.php`, focused tests | Browser negative test tetap manual untuk role matrix |
| B-01 | Owner exports: sales, service, performance, payment, inventory, awards | Owner dapat mengunduh PDF/XLSX dari scope yang sama | Semua endpoint dan content type lulus automated test; Sales Awards juga lulus. | PASS automated | `OwnerExecutiveDashboardStage4Test`, `SalesExcellenceAwardsStage5Test` | Download dan inspeksi visual manual |
| B-02 | PDF file inspection | Parser dapat membuka PDF dan data periode/nominal benar | Keenam PDF terbaru dibuat ulang melalui endpoint Owner; seluruhnya dibaca `pdfinfo` dan dirender `pdftoppm` menjadi 1 halaman. Periode dan baris data terlihat pada render; artefak lama tetap gagal dan disisihkan. | PASS teknis / MANUAL visual | PDF: `owner-sales.pdf`, `owner-service.pdf`, `owner-performance.pdf`, `owner-payment.pdf`, `owner-inventory.pdf`, `owner-sales-awards.pdf`; render PNG: `uat_owner-sales-1.png`, `uat_owner-service-1.png`, `uat_owner-performance-1.png`, `uat_owner-payment-1.png`, `uat_owner-inventory-1.png`, `uat_owner-sales-awards-1.png` | Buka keenam file di PDF reader untuk sign-off visual penuh |
| B-03 | XLSX file inspection | Workbook dapat dibuka di Excel dan angka konsisten | Keenam workbook terbaru lolos pemeriksaan ZIP/XML: 5 entries, worksheet valid, baris data, dan numeric cells; Microsoft Excel belum tersedia untuk dikendalikan sehingga repair-warning/layout belum terbukti. | PASS struktural / MANUAL Excel | `owner-sales.xlsx`, `owner-service.xlsx`, `owner-performance.xlsx`, `owner-payment.xlsx`, `owner-inventory.xlsx`, `owner-sales-awards.xlsx` | Operator membuka keenam file di Microsoft Excel |
| C-01 | Chrome native zoom 100% dan 67% | Layout sidebar, navbar, card, form, tabel, modal, grafik tetap usable | Belum ada bukti perubahan native zoom atau screenshot lokal yang tersimpan. | MANUAL UAT REQUIRED | Tidak ada screenshot file yang dapat diklaim | Operator Chrome |
| C-02 | Edge native zoom 100% dan 67% | Layout tetap usable | Belum dijalankan oleh automation. | MANUAL UAT REQUIRED | Tidak ada screenshot file yang dapat diklaim | Operator Edge |
| D-01 | Sembilan role: menu dan auth boundary | Menu sesuai permission, akses langsung ditolak jika tidak berwenang | Login seluruh akun staff dan boundary server-side lulus automated; CRUD browser lengkap, pagination, modal, pencarian belum dibuktikan di browser. | PASS automated / MANUAL UAT REQUIRED | `YzaRoleManagementTest`, `OwnerExecutiveDashboardStage4Test` | UAT operator dengan fixture sintetis |
| D-02 | CRUD Super Admin, Owner, Sales, Service Advisor, Mekanik, Gudang, Finance, Operasional, QC/PDI | Create/read/update/delete hanya pada menu yang diizinkan | Tidak menyatakan PASS browser. Matrix CRUD per role masih membutuhkan eksekusi manual. | MANUAL UAT REQUIRED | Checklist manual di bawah | UAT operator |
| E-01 | Notification signature | Signature salah ditolak | Test signature invalid, unknown order, dan amount mismatch lulus. | PASS automated | `MidtransSandboxStage11ATest`, `MidtransClient.php` | Tidak membuktikan HTTPS internet |
| E-02 | Notification idempotency dan settlement | Event/payment tidak tercatat dua kali | Event key dan gateway/payment guard diuji automated; settlement sebelumnya telah diverifikasi melalui status inquiry UAT. | PASS automated / webhook BLOCKED | `MidtransPaymentService.php` | HTTPS callback nyata |
| E-03 | HTTPS callback staging | Provider dapat mencapai URL TLS dan callback nyata teruji | Belum tersedia domain/endpoint staging HTTPS yang dapat diuji. | BLOCKED | Tidak ada bukti webhook nyata | Domain, TLS valid, callback URL publik yang disetujui, sandbox credentials |
| F-01 | Refund separation, evidence, amount cap, idempotency | Refund tidak melebihi payment, perlu SoD dan bukti | Offline adjustment menolak payment gateway online, membatasi total, mencatat event, dan menolak self-approval. | PASS control / provider BLOCKED | `FinancialAdjustmentService.php`, `FinancialAdjustmentStage7Test.php` | Kebijakan bisnis dan provider |
| F-02 | Provider refund dan ownership reversal | Tidak ada refund production; ownership tidak otomatis dibalik | Tidak ada refund provider online yang diaktifkan; Sales Awards tetap fail-closed; tidak ada ownership reversal otomatis. | BLOCKED / SAFE | `SalesAwardsService.php`, `SalesExcellenceAwardsStage5Test.php` | Owner mengesahkan policy dan sumber authoritative |

## Checklist manual yang masih terbuka

1. Jalankan Chrome dan Edge pada 100%, lalu 67% melalui menu browser native. Untuk setiap role, buka dashboard dan menu yang diizinkan; uji satu create, read, update, delete yang memang tersedia, pencarian, filter, pagination, modal, validasi kosong/invalid, dan akses URL langsung yang tidak diizinkan.
2. Unduh semua enam kelompok export Owner dalam PDF dan XLSX. Buka file pada aplikasi pembaca sebenarnya, periksa periode, total nominal, jumlah baris, status, header/footer, clipping, dan tidak adanya data pelanggan/kendaraan di luar scope.
3. Keenam export terbaru sudah dibuat ulang dan diperiksa secara teknis; artefak lama jangan dipakai. Buka keenam PDF serta keenam XLSX pada aplikasi desktop untuk sign-off visual/native.
4. Siapkan staging HTTPS terisolasi dengan TLS valid, callback notification Midtrans Sandbox, credential Sandbox baru, akses jaringan yang disetujui, log sanitasi, dan prosedur rollback. Uji satu notification settlement dan satu duplicate notification tanpa production payment.
5. Untuk refund, Owner harus menetapkan sumber refund authoritative, kebijakan pasca-serah-terima, approval role, cap, bukti dana keluar, dan aturan ownership. Sebelum itu, finalisasi Sales Awards tetap tidak boleh dijalankan.

## Regression evidence

- Focused final-release tests: **25 passed, 0 failed, 132 assertions**. Log: `focused-final-release-readiness.log`.
- Full regression after PDF fix: **411 passed, 0 failed, 0 skipped, 1499 assertions**. Log: `regression-final-release-readiness.log`.
- Export evidence capture: **1 passed, 0 failed, 37 assertions**; keenam PDF dan keenam XLSX terbaru tersimpan di folder ini. Harness capture bersifat sementara dan tidak dipertahankan sebagai test produk.
- Tidak ada migration, reset database, perubahan database operasional, atau perubahan database PHP Native pada pemeriksaan ini.

## Keputusan release

Status: **NOT READY FOR PRODUCTION**.

Source regression dan kontrol bisnis yang diuji automated lulus. Closure UAT belum lengkap karena browser CRUD sembilan role, zoom native Chrome/Edge, inspeksi visual semua export, dan HTTPS webhook/refund provider belum memiliki bukti operasional yang diperlukan.

## Final 9-role browser CRUD UAT — 15 Oktober 2026

Scope: UAT port 8001 dengan satu tab CUA bersama. Setiap role diuji melalui login UI, kemudian logout melalui tombol aplikasi sebelum role berikutnya. Hanya aksi yang benar-benar dijalankan yang dicatat. Bukti CUA tersedia inline; tidak ada file screenshot lokal yang dibuat.

| Role | Bukti yang dijalankan | Hasil aktual |
|---|---|---|
| Super Admin | `/admin/dashboard`; `/admin/petugas`; `/admin/petugas/create`; daftar, page 2, search/filter, validasi form kosong | PASS untuk navigasi/read, pagination, search/filter, dan validasi; create/edit/delete NOT TESTED |
| Owner | `/owner/dashboard?from=2026-09-01&to=2026-10-15`; filter periode diterapkan dan periode tampil | PASS untuk dashboard dan filter periode; mutasi operasional N/A |
| Sales | `/sales/dashboard`; `/sales/prospek`; navigasi, read state, kontrol search/filter | PASS untuk navigasi/read dan kontrol; fixture prospek tidak tersedia, create/update/delete NOT TESTED |
| Service Advisor | `/staff/dashboard`; link Booking Servis dan Work Order, metrik booking/WO | PASS untuk navigasi/read; pencarian booking, assignment, dan histori NOT TESTED |
| Mechanic | `/staff/dashboard`; `/admin/servis`; menu pekerjaan dan tabel status/filter | PASS untuk navigasi/read; edit pekerjaan dan assignment NOT TESTED |
| Warehouse | `/staff/dashboard`; link Sparepart dan Stok Masuk, read dashboard | PASS untuk navigasi/read; mutasi stok NOT TESTED |
| Finance | `/staff/dashboard`; link Pembayaran, Refund & Reversal, dan Laporan Keuangan, metrik transaksi | PASS untuk navigasi/read; detail pembayaran dan adjustment NOT TESTED |
| Operational | `/staff/dashboard`; `/admin/booking-mobil`; `/admin/test-drive`; tabel read | PASS untuk navigasi/read; perubahan status NOT TESTED |
| QC/PDI | `/staff/dashboard`; `/admin/servis`; statistik WO, dua record selesai, opsi status filter | PASS untuk navigasi/read; checklist QC/PDI NOT TESTED dan record selesai tidak diubah |

### Status penutupan browser

- **9/9 role** berhasil login, membuka area yang diizinkan, dan kembali ke guest melalui logout aplikasi.
- **0/9 role** dapat dinyatakan full CRUD closed pada run ini. Pengujian mutasi dibatasi karena fixture sintetis yang aman tidak tersedia untuk setiap menu dan user melarang perubahan pada order, ownership, serta Work Order yang sudah selesai.
- Search/filter/pagination yang benar-benar dieksekusi: Super Admin search/filter dan page 2 PASS; Owner filter periode PASS. Kontrol Sales terlihat tetapi tidak diterapkan pada fixture; opsi status QC terlihat tetapi filter tidak dipilih. Kontrol lain belum cukup dibuktikan untuk diberi PASS.
- Tidak ada bug aplikasi yang berhasil direproduksi. Satu salah indeks elemen saat membuka menu Finance merupakan keterbatasan indexing CUA dan pulih setelah AX state dibaca ulang, bukan bug aplikasi.
- Tidak ada perubahan source, route, middleware, database, migration, atau payment workflow pada run ini. Hanya dokumen matriks ini yang diperbarui.
- Full regression tidak dijalankan ulang karena source tidak berubah; baseline tetap **411 PASS, 0 FAIL, 0 SKIPPED, 1499 assertions**.
- Evidence browser hanya berupa state/screenshot inline dari CUA selama sesi; tidak ada screenshot file lokal yang boleh diklaim.

## Final 9-role browser UAT — execution closure update — 15 Oktober 2026

Run ini menggunakan UAT port 8001 dan database `yza_automotive_laravel_uat`. Seeder fixture UAT dijalankan satu kali untuk menyediakan data sintetis; tidak ada migration, reset, wipe, perubahan database operasional, atau perubahan database PHP Native. Setiap role menggunakan login UI dan logout melalui tombol aplikasi sebelum role berikutnya. Setelah QC logout, browser kembali ke halaman guest `/login`.

| Role | Skenario yang benar-benar dijalankan | Actual / status |
|---|---|---|
| Super Admin | `/admin/petugas`; tambah staff sintetis; buka detail; edit jabatan; validasi form; search/filter/page 2 | Create, read, edit, validation, search/filter, pagination **PASS**. Delete **NOT TESTED**. |
| Owner | `/owner/dashboard`; terapkan filter periode; baca rekap, grafik, laporan, dan menu export | Dashboard dan filter periode **PASS**. Mutasi operasional **N/A**. Export native/visual tetap **MANUAL** sesuai bagian B-02/B-03. |
| Sales | `/sales/prospek`; tambah prospek sintetis; buka detail; ubah status dan catatan follow-up | Create, read, update/activity **PASS**. Delete dan pagination **NOT TESTED**. |
| Service Advisor | `/admin/booking-servis`; baca daftar, status, pencarian/filter, dan detail booking | Read/list/filter **PASS**. Assignment mutation **NOT TESTED** agar fixture aktif tidak berubah. |
| Mechanic | `/staff/dashboard`; `/admin/servis`; baca menu dan daftar Work Order | Login, dashboard, authorization area, dan read **PASS**. Tidak ada Work Order aktif yang ditugaskan ke fixture mekanik; edit pekerjaan dan QC **NOT TESTED**. |
| Warehouse | `/admin/sparepart`; baca inventori, filter, search, pagination dan stok sintetis | Read/filter/pagination **PASS**. Transaksi stok dan validasi input **NOT TESTED**. |
| Finance | `/staff/dashboard`; baca menu pembayaran, refund/reversal, laporan, dan metrik transaksi | Login, authorization area, dan read **PASS**. Adjustment/refund **NOT TESTED**; tidak ada refund provider dijalankan. |
| Operational | `/admin/booking-mobil`; `/admin/test-drive`; baca tabel dan status | Login, navigation, read **PASS**. Perubahan status **NOT TESTED**. |
| QC/PDI | `/staff/dashboard`; `/admin/servis`; baca statistik, daftar WO selesai, dan status filter | Login, navigation, authorization area, dan read **PASS**. Checklist/QC mutation **NOT TESTED**; WO selesai tidak diubah. |

### Kesimpulan run

- **9/9 role** berhasil diuji untuk login, navigasi area berizin, pembacaan halaman, dan logout aplikasi.
- **2/9 role** memiliki mutasi UI yang benar-benar selesai pada run ini: Super Admin (create/edit staff sintetis) dan Sales (create/update prospek sintetis). Ini bukan penutupan full CRUD sembilan role.
- Tidak ada bug aplikasi yang berhasil direproduksi dan tidak ada source, route, middleware, migration, atau payment workflow yang diubah. Error Cloudflare/Statsig yang terlihat berasal dari telemetry browser Codex, bukan respons aplikasi lokal.
- Full regression tidak dijalankan ulang karena source tidak berubah. Baseline terakhir tetap **411 PASS, 0 FAIL, 0 SKIPPED, 1499 assertions**; angka ini bukan hasil rerun pada sesi ini.
- Evidence browser tersedia sebagai state/screenshot inline CUA untuk halaman yang disebutkan di atas. Tidak ada screenshot file lokal baru yang boleh diklaim.
- Status keseluruhan tetap **NOT READY FOR PRODUCTION**. Browser CRUD mutation yang belum memiliki fixture aman, native zoom 67% Chrome/Edge, inspeksi native PDF/XLSX, HTTPS callback Midtrans, dan refund provider masih terbuka.

## Operational mutation UAT — execution closure — 16 Oktober 2026

Fixture sintetis dibuat pada database `yza_automotive_laravel_uat` dengan marker `UAT_MUTATION_202610` melalui `database/seeders/UatMutationFixtureSeeder.php`. Seeder fail-closed pada nama database UAT, idempotent, tidak menjalankan migration/reset/delete, dan tidak menyentuh database operasional atau PHP Native. Browser UAT port 8001 sempat menjalankan proses lama yang tidak membaca database UAT; hanya proses port 8001 yang direstart. Port 8000 tidak disentuh.

| Role / fitur | Skenario UI aktual | Expected | Actual | Status | Evidence |
|---|---|---|---|---|---|
| Service Advisor | `/petugas/booking-servis/4`, klik `Terima Booking` pada `BSV-202610-71ECA063` | Booking sintetis diterima | Flash `Status booking servis diperbarui`, status berubah menjadi `BOOKING DITERIMA` | PASS | State/screenshot inline CUA |
| Mechanic | `/admin/servis/3/pemeriksaan`, isi hasil dan catatan; `/admin/servis/3/persetujuan`, simpan jasa | Mekanik dapat mengubah WO yang ditugaskan dan mencatat pekerjaan | Pemeriksaan tersimpan, status maju ke `Sedang Dikerjakan`, jasa Rp150.000 tersimpan | PASS setelah fix | State/screenshot inline CUA |
| QC/PDI | `/admin/servis/3/quality-control`, mulai QC; submit kosong; isi 6 checklist LULUS | QC start, validasi wajib, simpan hasil | Start PASS; submit kosong ditolak browser; keenam checklist tersimpan dan tombol lanjut muncul | PASS setelah fix | State/screenshot inline CUA |
| Operational / Test Drive | `/admin/test-drive/1`, klik `Setujui` | Status test drive sintetis berubah sah | Status berubah dari `menunggu` menjadi `disetujui`; tombol `Selesai` muncul | PASS setelah fix | State/screenshot inline CUA |
| Warehouse | Buat draft stok, tambah `UAT_MUTATION_202610-SP` qty 1 harga Rp10.000 | Draft dan item tersimpan; finalisasi menambah stok tepat sekali | Draft dan item tersimpan; dialog konfirmasi finalisasi tidak dapat diselesaikan oleh CUA; DB read-only tetap `draft`, stok tetap 2 | BLOCKED (browser dialog) | State CUA + query read-only; tidak ada screenshot file |
| Finance | Belum dijalankan karena sesi browser tersangkut pada dropdown logout Warehouse | Adjustment sintetis hanya melalui UI dan SoD | Tidak ada adjustment/refund dijalankan; tidak ada perubahan finansial | BLOCKED (session handoff) | Tidak ada klaim evidence |

### Bug yang ditemukan dan diperbaiki

1. `RoleModuleAccessMiddleware` memetakan seluruh POST `admin.servis` ke `service.manage`; mekanik yang hanya memiliki `service.assigned` mendapat 403 walaupun UI mengizinkan Work Order yang ditugaskan. Perbaikan memberi `service.assigned` hanya untuk mutasi `admin.servis` oleh mekanik, dengan `assigned.wo` dan guard controller tetap berlaku.
2. Route QC start/update ikut dipetakan ke `service.manage`; role QC mendapat 403 dari tombol yang terlihat. Perbaikan memberi `qc.manage` hanya untuk route `.servis.qc.`; mutasi Work Order lain tetap tidak dibuka untuk QC.
3. `OperationsTestDriveController` dan Blade detail masih mengenali role lama `admin/petugas`; `operational_staff` mendapat 403 atau form mengarah ke prefix yang salah. Controller dan view sekarang memakai role key serta route admin untuk aksi operasional yang diizinkan.
4. Seeder awal berpotensi menulis assignment primary kedua setelah `createFromBooking` membuat assignment. Seeder kini memeriksa assignment aktif sebelum `recordInitial`. Data UAT yang sudah terlanjur memiliki dua histori aktif tidak diubah secara manual; hal ini dicatat sebagai fixture setup issue, bukan disamarkan.

### Regression setelah perubahan

- Focused kontrol terdampak sebelum recovery: **19 PASS, 0 FAIL, 94 assertions**. Representative booking/service suite setelah recovery: **136 PASS, 0 FAIL, 286 assertions**.
- `BookingTestDriveStage4BTest` tetap **PASS** pada full run.
- Full regression setelah recovery: **411 PASS, 0 FAIL, 0 SKIPPED, 1499 assertions**. Detail akar masalah dan perubahan waktu dicatat di TIME_DEPENDENT_REGRESSION_RECOVERY.md.
- Baseline 411/0/0/1499 berhasil dipulihkan tanpa mengubah aturan bisnis kalender bengkel.

Evidence browser hanya tersedia inline dari CUA; tidak ada screenshot file lokal yang diklaim. Finance adjustment, finalisasi stok Warehouse, native zoom, PDF/XLSX desktop, HTTPS webhook, dan refund provider tetap terbuka. Status keseluruhan: **NOT READY FOR PRODUCTION**.

## Final Warehouse & Finance Operational UAT — 16 Oktober 2026

Scope: UAT port 8001 dengan fixture yang sudah ada pada database `yza_automotive_laravel_uat`. Tidak ada migration, reset, SQL mutation, perubahan database operasional/PHP Native, provider refund, atau settlement baru.

| Area | Skenario browser/read-only | Expected | Actual | Status | Evidence |
|---|---|---|---|---|---|
| Warehouse | Buka draft `SM-20261016001916-404`, item `UAT_MUTATION_202610-SP` qty 1 | Draft existing tersedia dan belum final | Draft tampil melalui UI; database read-only menunjukkan `status=draft`, total Rp10.000; stok sparepart tetap 2 | PASS read / BLOCKED finalisasi | State CUA inline + query read-only |
| Warehouse | Klik `Finalisasi & Terima Stok Fisik` | Dialog dikonfirmasi dan draft menjadi final | Browser berpindah ke `419 Page Expired`; dialog native JavaScript tidak dapat diselesaikan oleh CUA pada sesi ini; tidak ada mutasi stok | BLOCKED — MANUAL UAT REQUIRED | State CUA inline |
| Warehouse | Histori dan idempotency | Satu mutasi `+1`, actor dan referensi tercatat; retry tidak menggandakan | Belum ada histori untuk draft 4; histori sebelumnya untuk item 17 tetap hanya penerimaan `stok_masuk` qty 2 dari referensi draft 3; stok tetap 2 | NOT TESTED (menunggu finalisasi) | Query read-only |
| Warehouse → Finance | Logout tombol resmi lalu buka login | Session Warehouse berakhir dan guest tampil | Klik `Keluar` menuju `/logout` menghasilkan `419 Page Expired`; membuka `/login` kemudian menampilkan guest; login Finance berhasil | PARTIAL / ENV SESSION BLOCKER | State CUA inline |
| Finance | Dashboard dan menu Finance | Area Finance dan permission tampil | Login `finance506` berhasil; dashboard menampilkan menu Pembayaran, Refund & Reversal, dan Laporan Keuangan | PASS navigation/auth | State CUA inline |
| Finance | Buka daftar pembayaran dan detail fixture `PAY-UAT-MUT-202610` | Detail transaksi sintetis terbaca tanpa provider refund | Detail tampil: Menunggu Konfirmasi, Rp250.000, Transfer offline, marker UAT; tidak ada provider gateway | PASS read | State CUA inline |
| Finance | Ajukan internal financial adjustment, validasi nominal/alasan/bukti, audit, SoD | Form pengajuan tersedia dan pengaju tidak dapat self-approve | Halaman `/admin/financial-adjustments` hanya menampilkan tabel kosong; tidak ada form pengajuan atau aksi dari detail pembayaran. Tidak ada adjustment dibuat dan tidak ada perubahan finansial | BLOCKED — UI capability missing | State CUA inline + source audit |
| Finance | Provider refund / settlement | Tidak ada provider mutation | Tidak dijalankan | PASS safe stop | No provider action |

### Catatan blocker operasional

- Finalisasi Warehouse memerlukan konfirmasi native `window.confirm`. CUA tidak dapat menyelesaikan dialog tersebut secara andal; operator perlu mengulang satu klik pada draft yang sama melalui Chrome/Edge dan menekan **OK**. Setelah itu verifikasi `DITERIMA`, stok menjadi 3 tepat satu kali, dan satu histori `stok_masuk` baru tercatat.
- `419 Page Expired` saat logout terjadi setelah proses UAT port 8001 direstart dan mengindikasikan token sesi/CSRF browser lama sudah stale. Tidak ada middleware atau authentication yang dilemahkan. Login Finance setelah kembali ke `/login` berhasil.
- Financial adjustment belum dapat diuji end-to-end melalui browser karena tidak ada UI untuk membuat permohonan, walaupun route `POST` dan service backend tersedia. Tidak ada source yang diubah pada run ini karena menambah form akan menjadi perubahan fitur di luar penutupan UAT.

### Regression dan evidence

- Source tidak berubah pada run ini; full regression tidak diulang. Baseline terakhir tetap **411 PASS, 0 FAIL, 0 SKIPPED, 1499 assertions**.
- Evidence browser hanya berupa state/screenshot inline CUA; tidak ada screenshot file lokal yang diklaim.
- Status keseluruhan tetap **NOT READY FOR PRODUCTION** sampai finalisasi Warehouse dan financial adjustment UI/approval dapat diuji dengan fixture aman.

## Warehouse 419 & Finance Adjustment UI Closure — 16 Oktober 2026

Perbaikan ini hanya menyentuh jalur UAT Warehouse/Finance. Database operasional, database PHP Native, Midtrans, ownership, dan settlement Toyota GR Supra tidak diubah.

| Area | Actual setelah perbaikan | Status | Evidence |
|---|---|---|---|
| Akar masalah 419 | `.env.uat` sudah memakai `APP_URL=http://127.0.0.1:8001`, database UAT, `SESSION_COOKIE=yza_automotive_uat_session`, session file UAT terpisah, `SESSION_SECURE_COOKIE=false`, dan `SameSite=lax`. 419 terjadi pada sesi browser lama setelah restart, bukan collision cookie port atau CSRF yang dimatikan. Sesi baru menghasilkan token valid. | PASS diagnosis | Audit `.env.uat`/`config/session.php`; retest browser |
| Warehouse finalisasi | Draft `SM-20261016001916-404` difinalisasi melalui UI setelah sesi baru. Status menjadi `DITERIMA`. | PASS | State/screenshot inline CUA |
| Stok dan histori | Stok `UAT_MUTATION_202610-SP` berubah `2 → 3`; satu histori baru tercatat dengan perubahan `+1`, aktor Warehouse, referensi draft 4. | PASS | Query read-only UAT |
| Idempotency | Setelah diterima, item terkunci dan tombol finalisasi hilang; tidak ada histori kedua atau penambahan stok ulang. | PASS | UI + query read-only |
| Logout Warehouse | Tombol `Keluar` resmi mengakhiri session dan membawa ke halaman login guest pada sesi baru. | PASS | State CUA inline |
| Finance UI | Form `Ajukan Refund / Reversal Offline` tampil untuk Finance; hanya pembayaran terverifikasi offline tanpa gateway yang eligible. Pending `PAY-UAT-MUT-202610` tidak ditawarkan. | PASS | State/screenshot inline CUA |
| Validasi adjustment | Pengajuan Rp70.000 terhadap payment Rp65.000 ditolak dengan pesan nominal melebihi batas. Pengajuan valid Rp10.000 berhasil masuk status `requested`. | PASS | State CUA + audit UAT |
| Separation of duties | Finance berhasil memindahkan `requested → under_review`; self-approval ditolak server-side. Super Admin berbeda berhasil menyetujui; status akhir `approved`, 3 event audit tercatat. | PASS | State CUA + query read-only UAT |
| Provider refund | Tidak dijalankan. Adjustment berhenti di `approved`; tidak ada refund Midtrans atau transfer bank. | SAFE STOP | Query gateway tetap tidak terkait payment adjustment |

### File yang diubah

- `app/Http/Controllers/FinancialAdjustmentController.php` — mengirim daftar pembayaran eligible ke view.
- `resources/views/admin/financial-adjustments/index.blade.php` — form pengajuan, validasi HTML, CSRF, anti-double-submit, dan styling responsif.
- `tests/Feature/FinancialAdjustmentStage7Test.php` — regression untuk visibilitas form Finance.
- Dokumen ini.

### Pengujian setelah perubahan

- Focused Warehouse/Finance/Auth/RBAC: **13 passed, 0 failed, 59 assertions**.
- Full PHPUnit langsung dengan konfigurasi `phpunit.xml`: **412 passed, 0 failed, 0 skipped, 1502 assertions**.
- Percobaan wrapper Artisan yang pertama menolak seluruh test karena membaca database aplikasi `yza_automotive_laravel`; guard test menghentikan eksekusi sebelum test berjalan. Tidak dihitung sebagai test failure fungsional.

Status Warehouse dan Finance operational UAT: **CLOSED untuk scope yang diuji**. Status produksi keseluruhan tetap **NOT READY FOR PRODUCTION** karena blocker integrasi yang sudah ada sebelumnya seperti HTTPS webhook staging, refund provider, native zoom, dan inspeksi desktop PDF/XLSX.

## Midtrans HTTPS Staging Readiness Audit — 16 Oktober 2026

### Integrasi existing

- Webhook: `POST /payment/midtrans/notification`, tanpa login dan dikecualikan dari CSRF karena autentikasi webhook dilakukan melalui signature Midtrans.
- Signature: SHA-512 atas `order_id + status_code + gross_amount + Server Key`, dibandingkan dengan `hash_equals`.
- Validasi: order ID harus cocok dengan gateway internal; gross amount harus cocok; settlement juga dibandingkan dengan sisa tagihan server-side.
- Idempotency: event key provider disimpan pada `event_pembayaran_gateway`; payment dibuat hanya jika gateway belum memiliki `id_pembayaran`.
- Status yang ditangani: `pending`, `settlement`, `capture`, `deny`, `cancel`, `expire`, `failure`, `refund`, `partial_refund`, `chargeback`, dan `partial_chargeback`.
- Status inquiry: tersedia server-to-server melalui `GET /v2/{order_id}/status`; redirect browser bukan bukti pelunasan.
- Bukti otomatis berasal dari PHPUnit/HTTP fake dan status inquiry Sandbox sebelumnya. **Webhook HTTPS provider nyata belum pernah PASS.**

### Perbaikan yang dilakukan

`app/Services/MidtransPaymentService.php` sekarang menghentikan pemrosesan ulang ketika event settlement/capture yang sama sudah tercatat dan gateway sudah final. Sebelumnya duplicate settlement tidak menggandakan pembayaran, tetapi dapat menurunkan status gateway menjadi `requires_review` karena sisa tagihan sudah nol.

Test diperkuat pada `tests/Feature/MidtransSandboxStage11ATest.php` untuk memastikan duplicate settlement mempertahankan status `settlement`.

Focused Midtrans: **10 passed, 0 failed, 30 assertions**.
Full PHPUnit setelah perbaikan: **412 passed, 0 failed, 0 skipped, 1503 assertions**.

### Rancangan staging HTTPS

Prasyarat yang masih harus disediakan sebelum deployment:

1. Subdomain staging yang disetujui, misalnya `uat.example.tld`, dengan DNS ke reverse proxy/staging host.
2. Sertifikat TLS valid dan akses inbound HTTPS hanya ke reverse proxy; port 8000 lokal tidak dipublikasikan.
3. Callback URL `https://uat.example.tld/payment/midtrans/notification` yang dapat dijangkau Midtrans Sandbox.
4. Database staging terisolasi, user database terbatas, backup dan rollback tervalidasi; tidak ada akses MySQL publik.
5. Credential Midtrans Sandbox baru, disimpan sebagai secret environment, bukan source code/log/screenshot.
6. `APP_ENV=staging`, `APP_DEBUG=false`, `APP_KEY` unik, session cookie/domain khusus staging, `SESSION_SECURE_COOKIE=true`, `SameSite=Lax`, cache/queue/log storage terisolasi, serta worker yang tidak berbagi queue production.
7. Akun UAT kuat dan unik; tidak menggunakan password fixture sederhana pada endpoint publik.

Konfigurasi lokal UAT saat ini benar untuk HTTP localhost, tetapi belum layak dipublikasikan: `APP_DEBUG=true`, `APP_URL=http://127.0.0.1:8001`, dan `SESSION_SECURE_COOKIE=false`. Tidak ada perubahan deployment atau konfigurasi production dilakukan.

### Rencana pengujian provider

Setelah endpoint staging disetujui dan tersedia, jalankan satu transaksi Sandbox sintetis dan verifikasi: reachability TLS, notification asli, signature valid/invalid, order/gross mismatch, duplicate settlement, status inquiry, pending/expire/cancel, retry sementara, dan audit event. Simpan request/response yang sudah disanitasi—tanpa Server Key, Client Key, Snap token, cookie, atau Authorization header.

### Refund dan keputusan bisnis

Refund provider belum tersedia/diaktifkan. `FinancialAdjustmentService` hanya menerima pembayaran offline yang sudah terverifikasi dan secara eksplisit menolak payment yang terkait gateway online. Lifecycle offline, cap nominal, audit event, dan separation of duties tetap aktif. Tidak ada ownership reversal otomatis.

Masih diperlukan keputusan tertulis mengenai sumber refund authoritative, kebijakan refund setelah serah-terima, role approver/confirmer, bukti dana keluar, batas waktu, dan apakah refund parsial memengaruhi Sales Awards. Finalisasi Sales Awards tetap fail-closed.

Status staging: **NOT READY TO DEPLOY** sampai domain/TLS, callback reachability, sandbox credential ownership, isolated staging host/database, dan persetujuan refund policy tersedia.
