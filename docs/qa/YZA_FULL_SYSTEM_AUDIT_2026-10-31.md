# YZA AUTOMOTIVE — FULL SYSTEM AUDIT & DEVELOPMENT PROGRESS

Tanggal audit: **31 Oktober 2026 (Asia/Jakarta)**  
Proyek: `C:/xampp/htdocs/yza_automotive_laravel`  
Metode: read-only pada source dan database operasional; regression dan probe mutasi hanya pada database testing terisolasi dengan rollback.  
Artefak: laporan ini, [inventory bukti](YZA_FULL_SYSTEM_AUDIT_2026-10-31_EVIDENCE.md), dan [hasil PHPUnit JUnit](YZA_FULL_SYSTEM_AUDIT_2026-10-31_TEST_RESULTS.xml).

## 1. Executive Summary

Aplikasi sudah jauh melewati prototipe. Backend transaksi, stok, booking, membership, checkout kendaraan, PDI, handover, servis, approval pelanggan, QC, finance, sales, dan laporan Owner tersedia. Sebagian besar data operasional memakai Eloquent/SQL ke database sungguhan. Ada pengecualian penting: empat showcase kendaraan virtual mempunyai definisi model/harga hardcoded, dan sebagian KPI belum mempunyai definisi atau salah sumber hitung.

**Kesimpulan: lanjutkan stabilisasi sistem existing, jangan rewrite dan jangan menambah modul terlebih dahulu.** Suite saat ini lulus **434 tes / 1.650 assertions / 0 failure / 0 error / 0 skipped**, tetapi audit menemukan **14 bug** yang tidak ditolak oleh suite tersebut. Celah utama: otorisasi mutasi dengan fallback `dashboard.view`, mutasi WO mekanik tanpa guard assignment, sukses pembayaran `capture` yang menerima fraud `challenge`, finalisasi tanpa row QC, serta finalisasi dan promo yang tidak atomik.

Ringkasan 75 unit fitur dalam 25 modul, dihitung satu status utama per baris pada bagian 5:

| Status | Jumlah |
|---|---:|
| COMPLETE & VERIFIED | 40 |
| IMPLEMENTED — NOT VERIFIED | 3 |
| PARTIAL | 8 |
| UI ONLY | 1 |
| NOT IMPLEMENTED | 4 |
| BLOCKED | 3 |
| BUG FOUND | 14 |
| NOT ASSESSED | 2 |
| **Total** | **75** |

COMPLETE & VERIFIED berlaku **hanya pada subfitur dan skenario yang ditulis**, bukan seluruh modul, keamanan keseluruhan, semua kombinasi input, maupun integrasi provider nyata. Contoh: checkout/handover normal teruji, tetapi legacy PDI, role QC, dan points sesudah handover tetap mempunyai temuan terpisah.

Modul dengan bukti positif paling kuat: transaksi stok sparepart, penjualan sparepart, pembayaran offline, dan checkout member sampai ownership. Modul paling memerlukan stabilisasi: RBAC lintas role, batas finalisasi Work Order, lifecycle pembayaran membership, dan integrasi sesudah penjualan.

Staging: layak untuk reproduksi/QA **terisolasi**, tetapi belum lulus gerbang penerimaan UAT sampai P0 ditutup. Production: **belum siap**. Mode Midtrans bahkan secara eksplisit masih dibatasi sandbox. Tidak dilakukan deployment, commit, perubahan .env, migration, perbaikan source, atau perubahan data operasional.

## 2. Technology & Architecture

### Runtime dan struktur aktual

| Aspek | Bukti aktual |
|---|---|
| Laravel | Runtime `app()->version() = 12.69.2`; composer requirement `^12.0` |
| PHP | Runtime CLI XAMPP `8.2.4`; composer requirement `^8.2` |
| Database | Koneksi Laravel `mysql`; server aktual **MariaDB 10.4.28**, bukan mengasumsikan versi MySQL |
| Frontend | Blade, CSS/JS lokal, Vite 7, Tailwind 4, Axios, Three.js pada package.json |
| Dependency payment | HTTP client Laravel + `MidtransClient`; tidak ada Midtrans SDK dalam require composer |
| Struktur | 74 file controller termasuk base/compatibility controller; 73 model; 55 service; 54 migration; 200 Blade view |
| Routes | 427 route efektif termasuk health/framework route; 71 controller action target unik; **0 action route hilang** |
| Tests | 57 file Feature dan 1 file Unit; PHPUnit runtime 11.5.56 |
| Environment efektif | local; debug=true; cache=file; session=file; queue=sync; Midtrans is_production=false |
| Credentials | Keberadaan server/client key diperiksa sebagai SET saja; nilainya tidak dicantumkan |

Angka struktur adalah jumlah file pada snapshot, bukan jumlah fitur. Route daftar lengkap, middleware, controller, model, service, migration dan tests tersedia pada inventory bukti.

Aplikasi menggunakan controller → FormRequest → service → Eloquent/DB transaction. Tabel memakai nama Indonesia dan primary key khusus, seperti `id_user`, `id_transaksi`, `id_servis`. Tidak ditemukan direktori implementasi khusus Repository, Policy, Job, Event, Listener, atau Notification Laravel. Tidak berarti tidak ada otorisasi/event: otorisasi dilaksanakan middleware dan controller; event database dibuat langsung di service; `AppServiceProvider::boot` mendaftarkan callback `Servis::created` untuk assignment awal. Notifikasi bisnis memakai model `Notifikasi`, bukan kelas Laravel Notification terpisah.

`routes/web.php` memuat UI dan endpoint JSON; tidak ada routes/api.php pada snapshot. `bootstrap/app.php` mengecualikan CSRF hanya untuk `payment/midtrans/notification`; alias middleware mencakup role, permission, module.access, assigned.wo, member, verified.owner. `routes/console.php` tidak mempunyai schedule rekonsiliasi pembayaran atau dispatch reminder. Command demo/backfill/provisioning ada, tetapi tidak dijalankan terhadap database utama.

### Database schema dan data aktual

Pembacaan metadata/query operasional dilakukan setelah `SET SESSION TRANSACTION READ ONLY`, dalam transaksi yang diakhiri rollback. Tidak dilakukan schema repair.

- **76 tabel**, **142 foreign-key constraints**, **54 migration tercatat**, terakhir `2026_10_15_050000_create_financial_adjustments_tables`; jumlah migration source juga 54.
- Semua **73 model** dapat dipetakan ke tabel yang tersedia. Ini pemeriksaan keberadaan tabel, **bukan** pembuktian setiap column/cast/relation bebas drift.
- Kolom/FK/unique yang ditinjau mendalam: vehicle_inventories/history, PDI, handover, kendaraan_pelanggan, service QC, payment dan assignment. PDI dan handover memiliki unique `id_pesanan`; ownership memiliki unique `id_penjualan`; aggregate inventory memiliki unique `inventory_key`.
- Sumber hubungan utama: User → Role/Permission, Pelanggan → Membership/Kendaraan/Transaksi; Order → Penjualan/Transaksi/PDI/Handover; BookingServis → Servis → Details/Spareparts/Approval/QC/Transaksi; Transaksi → Pembayaran/Gateway/Adjustment.
- Snapshot operasional: 18 users, 10 pelanggan, 10 membership (6 active, 4 pending_payment), 26 mobil, 12 aggregate inventory, 2 pesanan member (1 dibatalkan, 1 diserahkan), 6 servis, 13 transaksi, 18 pembayaran, 8 gateway, 2 kendaraan pelanggan, 0 sales leads, 1 sales award, 0 financial adjustments.
- Aggregate inventory: total stock_total=59, sold_units=1. Ini bukan total stok seluruh dealer karena masih ada unit nonaggregate dan reservasi.
- Stok sparepart negatif: **0**; orphan pembayaran → transaksi: **0**; aggregate sold_units > stock_total: **0**.
- Dua WO aktif berstatus dikerjakan tanpa transaksi dan tanpa row QC ditemukan. Satu member order diserahkan belum mempunyai riwayat poin terkait.
- Tabel inventory yang benar `vehicle_inventories`, **bukan `vehicle_inventory`**. Query awal nama singular tidak tersedia, kemudian dikoreksi berdasarkan model/schema; ini bukan missing-table bug aplikasi.
- Angka operasional dapat memuat data demo/UAT terdahulu; keberadaan data tidak membuktikan asalnya transaksi pelanggan produksi.

### Sisa arsitektur dan duplikasi

Controller `app/Http/Controllers/Petugas/ProfileController.php` tidak menjadi target route langsung; isinya hanya subclass compatibility dari ProfileController. Base Controller tentu bukan halaman. Tidak ditemukan route yang merujuk method controller tidak ada. Halaman lama `resources/views/pelanggan/dashboard.blade.php` tersedia, tetapi `DashboardController::pelanggan` mengembalikan `pelanggan.member-center-empty`; nama “empty” tidak berarti halaman dummy.

Duplikasi penting: `savePdi` legacy vs `completePdi` rinci; decision payment membership vs payment transaksi; wrapper `createWithPromo/finalizeWithPromo` pada beberapa service; definisi empat showcase berulang; agregasi stok Owner terpisah dari VehicleAvailabilityService. Pencarian TODO/FIXME tidak dijadikan indikator selesai: gap penting justru berada dalam method yang tampak final.

## 3. Git & Development History

Branch aktif: **feature/vehicle-purchase-flow**.  
HEAD: **0359b1e8fa28aeceac5aa8491bb7eb5d85672ea6**, 17 Oktober 2026, “Perbaiki tampilan dropdown navigasi member”.

Snapshot sebelum audit memiliki **53 file tracked dimodifikasi** dan **15 entri untracked tingkat Git status**, beberapa di antaranya direktori berisi banyak file. Perubahan tersebut sudah ada sebelum audit. Diff tracked sekitar 2.081 tambahan/261 penghapusan baris; tidak mencakup isi file untracked. Audit memasukkan semuanya sebagai kondisi aktual working tree. Jangan menganggap checkout HEAD saja merepresentasikan sistem yang diuji.

| Tahap | Bukti | Penilaian |
|---|---|---|
| Foundation/auth/master | docs/FOUNDATION_STAGE_1.md, AUTH_STAGE_2.md; migration 28–30 September; service/test Stage3–Stage10 | Ada di source; regression subset relevan kini lulus |
| Booking, WO, inventory, payments | docs/SERVICE_*; migration September; service/Test Stage4–Stage9 | Implementasi substantial; bukan halaman statis |
| Paid membership/promo/SPK | docs/YZA_PAID_MEMBERSHIP_STAGE_13A.md, YZA_PROMO_* | Ada di kode; lifecycle provider membership belum mempunyai coverage memadai |
| Aggregate inventory dan checkout | Git `eccc8aa`, 11 Oktober; VehicleInventoryService | Jalur member teruji, integrasi legacy belum seluruhnya sama |
| Admin payment/invoice/recovery | Git `5e74399`, `a649243`, `31f9c01`, `dafccba`, `e8f417d` | Terdapat perbaikan nyata dan regression relevan |
| Post-payment PDI/pickup/handover | Git `160eb4b`, `7ca669f`; docs/implementation/YZA_PDI_PICKUP_HANDOVER_IMPLEMENTATION.md | Implementasi dan dokumen UAT; happy path kini lulus |
| My Garage dan navigasi | Git `37df801`, HEAD `0359b1e` | Tampilan/ownership color/navigation diteruskan |
| Scheduling/personnel/execution/fitment | migration 12–13 Oktober; docs/implementation dan tests Phase2–4 | Bukti working source/schema; jangan memaksakan tanggal commit HEAD sebagai tanggal semua kerja |
| Role/sales/awards/refund offline | migration 15 Oktober; YzaRoleManagementTest, SalesManagementStage3Test, FinancialAdjustmentStage7Test | Ada pada working tree dan DB; final awards tetap blocked |
| Pekerjaan lokal terbaru | AdminWorkspaceController, AdminAuxiliaryController, OwnerOverviewService, owner shell/CSS, 2 tests baru | Fokus terbaru adalah redesign Owner/Admin dan navigasi, belum di-commit |

Klaim lama perlu dipisahkan: FINAL_QA_RELEASE_READINESS mencatat 401 pass/3 skipped; REGRESSION_RECOVERY_REPORT mencatat 407 pass/0 skipped; laporan servis sebelumnya mencatat 367 pass/3 skipped. **Hasil yang benar-benar dieksekusi audit ini adalah 434 pass/0 skipped**, bukan pengulangan klaim lama.

Dokumen `docs/qa/uat-local/VEHICLE_PURCHASE_PDI_HANDOVER_UAT.md` mengklaim provider sandbox settlement, PDI, pickup, handover, Garage dan Owner UAT pada 15 Oktober. Ini **bukti historis yang dilaporkan**, tidak direplay saat audit. Tanggal pickup 16 Oktober dan pencatatan tanggal laporan tidak cukup untuk mengaudit kondisi clock sesi historis; audit tidak menganggapnya bukti eksekusi baru.

## 4. Role & Permission Audit

Terdapat **12 nama role DB**: admin, petugas, pelanggan, super_admin, owner, sales, service_advisor, mechanic, warehouse_staff, finance, operational_staff, quality_control. `Role::key` memetakan admin→super_admin dan petugas→operational_staff; tidak ada role “member” tersendiri.

Seluruh role internal di bawah dapat masuk grup `/admin/*` sesuai allow-list route dan kemudian module.access. Karena itu label admin pada URL **bukan** bukti admin-only. Sidebar menyembunyikan sebagian menu, tetapi keputusan backend harus ditinjau per route.

| Role aktual | Dashboard/menu intended | Permission DB dan aksi backend | Hubungan dan status |
|---|---|---|---|
| admin (legacy) | /admin/dashboard, seluruh master/operasional | roleKey super_admin bypass semua permission | Akses luas existing; baseline suite lulus |
| super_admin | /admin/dashboard; seluruh modul; assignment/target/finalize awards | User::hasPermission selalu true; beberapa FormRequest masih hanya menerima nama admin | Penuh secara intended, tetapi check-in WO B13 dan legacy request perlu konsistensi |
| owner | /owner/dashboard, penjualan/servis/performa/finance/inventory/awards/export | dashboard.view, sales.view, customers.view, reports.view, inventory.view, finance.view, service.view; Owner awards finalize tambahan role super_admin | Laporan tersedia, tetapi read-only global **gagal B01** |
| sales | /sales/dashboard, prospek, aktivitas, test drive, pesanan, target, awards | dashboard.view, sales.view/manage, customers.view; authorizeLead dan scope id_sales | B12 client assignment; akses admin sales modul lebih luas dari scope /sales perlu negative test menyeluruh |
| service_advisor | /staff/dashboard, booking servis, jadwal, personel, WO | dashboard.view, service.view/manage, customers.view | Dapat mengelola WO; B13 check-in salah prefix; mutation fallback B01 |
| mechanic | /staff/dashboard, daftar/halaman WO assigned, baca inventory | dashboard.view, service.view, service.assigned, inventory.view | GET assigned.wo bekerja; **mutasi B02**; assistant belum masuk daftar utama |
| warehouse_staff | /staff/dashboard, inventory/sparepart/stok | dashboard.view, inventory.view/manage | Mutasi inventory sesuai intent; default modul kategori/supplier terlalu luas B01 |
| finance | /staff/dashboard, payments, verify, adjustments, reports | dashboard.view, finance.view/verify, reports.view | Payment/adjustment suite lulus; B01 tetap lintas role |
| operational_staff | /staff/dashboard, tampilan operasional | dashboard.view, service.view | Tidak service.manage/sales.manage; aksi carwash/test drive intended belum konsisten |
| quality_control | /staff/dashboard, WO QC | dashboard.view, service.view, qc.manage; special-case .servis.qc. | QC servis bisa, tetapi PDI pembelian ditolak B07 |
| petugas (legacy) | /petugas/dashboard, mobil/sparepart baca, booking/WO/transaksi | Grup /petugas memakai role:petugas dan permission berbeda dari grup admin modern | Tetap dapat operasional legacy meski pivot permission kosong; bukan setara akses role operational_staff modern |
| pelanggan | /pelanggan/member, katalog/pesanan, Garage, booking, points/promo/riwayat/notifikasi | role:pelanggan + member; layanan tertentu verified.owner; per-customer scoping | Member aktif ≠ ownership; recovery membership tetap dapat dipanggil sebelum active |

Bukti otorisasi: middleware dan route inventory; YzaRoleManagementTest, FinancialAdjustmentStage7Test, VerifiedCarOwnershipTest, MemberFoundationStage10ATest dan SalesManagementStage3Test. Suite ini tidak membuktikan seluruh 427 route×12 role. Probe read-only module middleware menunjukkan Owner, mechanic, operational_staff dan QC lolos PUT admin.pengaturan.update-many serta PATCH kategori.toggle. Controller updateMany/toggle tidak menambahkan role guard, sehingga ini bukan hanya masalah tombol frontend.

Owner report GET tidak melakukan mutasi transaksi, tetapi halaman awards menggunakan `SalesAwardsService::award` firstOrCreate untuk metadata provisional; audit tidak memanggil GET operasional tersebut agar tidak menulis DB. Belum ada jaminan semua GET aman tanpa side effect.

## 5. Feature Completion Matrix

Semua path bukti relatif ke root proyek di atas. Nama controller singkat di tabel berada dalam `app/Http/Controllers/`, nama service dalam `app/Services/`, dan nama test tanpa prefix berada dalam `tests/Feature/`. Nama method dan route lengkap dapat ditelusuri pada inventory bukti. Status BUG FOUND diprioritaskan bila ada defect terbukti pada unit fitur tersebut; tidak dihitung lagi sebagai complete/partial.

| Modul | Fitur | Status | Bukti | Masalah / batas verifikasi | Langkah Selanjutnya |
|---|---|---|---|---|---|
| Autentikasi | F01 — Registrasi profil, alamat wilayah, akun pending | COMPLETE & VERIFIED | app/Http/Controllers/RegistrationController.php::store; POST /register; tests/Feature/RegistrationNavigationTest.php, IdentityStage3CTest.php | Validasi form/profil teruji; email ownership belum diverifikasi | Pertahankan validasi; uji abuse registrasi |
| Autentikasi | F02 — Login/logout, hashing, pembatasan percobaan | COMPLETE & VERIFIED | app/Http/Controllers/AuthController.php::store/destroy; POST /login,/logout; AuthMemberLoginRegressionTest, AuthNavigationHardeningTest | CSRF negatif tidak tercakup harness umum | UAT browser dan status akun nonaktif |
| Autentikasi | F03 — Verifikasi email / pemulihan password via email | NOT IMPLEMENTED | routes/web.php; app/Models/User.php; ProfileController::password/updatePassword | Yang ada hanya ganti password saat login; tidak ditemukan route reset/verify email | Tentukan kebutuhan bisnis terlebih dahulu |
| Role & permission | F04 — Role, permission DB, dashboard, legacy alias | COMPLETE & VERIFIED | app/Models/Role.php::key; User::hasPermission; YzaRoleManagementTest | 12 nama role fisik; admin/petugas alias semantik | Pertahankan kompatibilitas |
| Role & permission | F05 — Default guard mutasi dan Owner read-only | BUG FOUND | app/Http/Middleware/RoleModuleAccessMiddleware.php::handle; Admin/PengaturanController::updateMany; B01 | dashboard.view dapat lolos mutasi pengaturan/kategori | P0 explicit permission seluruh route |
| Role & permission | F06 — Scope mutasi mekanik ke WO assigned | BUG FOUND | routes/web.php admin.servis.*; AssignedWorkOrderMiddleware; OperationsServisController; B02 | assigned.wo tersedia pada GET tetapi tidak pada banyak mutasi | P0 scope semua aksi dan endpoint |
| Showroom & katalog | F07 — Katalog DB, detail, galeri, availability | COMPLETE & VERIFIED | Public/MobilController::index/memberShow; VehicleAvailabilityService::for; VehicleAvailabilityStockTest, VehicleConfiguratorTest | Pola aggregate dan unit fisik bercampur | Pertahankan kalkulasi availability |
| Showroom & katalog | F08 — Showcase virtual tanpa inventory | UI ONLY | app/Http/Controllers/Public/MobilController.php::catalogShowcases/memberShowcase; VehicleAvailabilityService::syntheticAvailability | 4 definisi harga/model hardcoded; unit virtual tidak dapat checkout | Pisahkan label showcase dari stok nyata |
| Showroom & katalog | F09 — Harga kendaraan khusus member eksklusif promo publik | NOT IMPLEMENTED | MemberVehiclePurchaseService::create/approve; member-show.blade.php; snapshot_harga=harga_jual,diskon=0 | Tidak ditemukan jalur dual-price atau aturan eksklusif publik/member | Tetapkan aturan, gunakan sumber harga tunggal |
| Membership | F10 — Create/reuse attempt, finish, inquiry, route recovery | IMPLEMENTED — NOT VERIFIED | app/Http/Controllers/MembershipController.php::pay/finish/sync; app/Services/MembershipPaymentService.php; routes/web.php pelanggan.membership.* | Coverage suite umum tidak membuktikan semua cabang membership provider; tidak ditemukan tes lifecycle khusus yang memadai | Tambahkan kasus adversarial membership |
| Membership | F11 — Keputusan sukses capture/fraud | BUG FOUND | app/Services/MembershipPaymentService.php::processNotification:53; MidtransPaymentService::process; B03 | capture+challenge mengaktifkan membership pada probe testing | P0 status sukses eksplisit dan regression |
| Membership | F12 — Konsistensi gateway saat webhook terlambat | BUG FOUND | MembershipPaymentService::processNotification:54; B04 | capture kemudian pending menghasilkan member active/gateway pending | State terminal monotonic dan audit rekonsiliasi |
| Membership | F13 — Masa berlaku/renewal/refund membership | NOT IMPLEMENTED | app/Services/MemberAccessService.php; MembershipPaymentService; memberships schema | Active berbasis status; tidak ada tanggal kedaluwarsa/renewal | Putuskan lifetime vs renewal |
| Member Center | F14 — Card, kendaraan YZA, saldo poin, promo, notifikasi | COMPLETE & VERIFIED | DashboardController::pelanggan; resources/views/pelanggan/member-center-empty.blade.php; MemberFoundationStage10ATest, YzaMemberLandingTest | Kartu kendaraan menggunakan verifiedVehicles; bukan semua kendaraan manual | Pertahankan sumber ownership |
| Member Center | F15 — Booking Aktif dan aktivitas mendatang | BUG FOUND | app/Services/MemberDashboardService.php::for:13-23; B10 | count dilakukan setelah limit 5 per jenis lalu take(8) | Aggregate DB terpisah dari daftar ringkas |
| Member Center | F16 — Histori transaksi, invoice, aktivitas | COMPLETE & VERIFIED | MemberDashboardService::for/transaction; MemberFoundationStage10ATest, MemberVehiclePurchaseFlowTest | Daftar ringkas sengaja dibatasi 12 transaksi/8 aktivitas | UAT navigasi ke histori penuh |
| My Garage & ownership | F17 — Ownership hanya setelah member handover lunas | COMPLETE & VERIFIED | VerifiedCarOwnershipService::completedSalesFor/syncVehicleFromCompletedSale; VerifiedCarOwnershipTest, MemberVehiclePurchaseFlowTest | Manual vehicle tidak membuka layanan eksklusif | Pertahankan guard backend |
| My Garage & ownership | F18 — Daftar, detail, warna pembelian, kilometer | COMPLETE & VERIFIED | Pelanggan/KendaraanController::index/show/update; UpdateKendaraanPelangganRequest; MemberVehiclePurchaseFlowTest | Identitas resmi dibatasi; update hanya kilometer | UAT kendaraan terjual/nonaktif |
| My Garage & ownership | F19 — Ownership dari penjualan petugas/trade-in | PARTIAL | PaymentService::sync; TradeInService::finalize; VerifiedCarOwnershipService::completedSalesFor | Ownership mensyaratkan memberOrder diserahkan; jalur legacy tidak membuatnya | Satukan proses handover existing lintas sumber |
| Booking mobil | F20 — Member booking, konfirmasi, cancel/expiry | COMPLETE & VERIFIED | BookingMobilService; OperationsBookingController; BookingTestDriveStage4BTest, CarSalesStage7ATest | Booking dan checkout member merupakan dua jalur | UAT konversi booking ke penjualan aggregate |
| Booking mobil | F21 — Konversi reservasi terakhir aggregate | IMPLEMENTED — NOT VERIFIED | PenjualanMobilService::create; VehicleInventoryService::reserveCount | Kapasitas dihitung sebelum booking milik sendiri dikonversi; potensi hitung ganda | Uji stok 1, booking sendiri, konversi |
| Test drive | F22 — Pengajuan, persetujuan, penolakan, selesai | COMPLETE & VERIFIED | TestDriveService; OperationsTestDriveController::action; BookingTestDriveStage4BTest | Tidak ada bukti provider eksternal diperlukan | UAT jadwal bentrok |
| Test drive | F23 — Hasil dan follow-up sales terassign | COMPLETE & VERIFIED | SalesManagementController::testDrives/updateTestDrive; SalesManagementStage3Test | Scope sales diperiksa backend | UAT perubahan lintas role |
| Pembelian kendaraan | F24 — Checkout snapshot harga/warna, reservasi, approve/reject | COMPLETE & VERIFIED | MemberVehiclePurchaseService::create/approve/reject; MemberVehiclePurchaseFlowTest | Harga berasal server dan transaksi dibuat saat approve | Tambah concurrency multi-proses |
| Pembelian kendaraan | F25 — Offline payment, reject, recovery, invoice | COMPLETE & VERIFIED | Pelanggan/MemberVehiclePurchaseController::offlinePayment/reopenPayment; PaymentService; MemberVehiclePurchaseFlowTest | Manual rejection tidak dianggap lunas | Pertahankan sumber pembayaran terverifikasi |
| Pembelian kendaraan | F26 — PDI lengkap, jadwal, handover idempotent | COMPLETE & VERIFIED | MemberVehiclePurchaseService::startPdi/completePdi/schedule/handover; MemberVehiclePurchaseFlowTest | Happy path dan beberapa guard lulus; celah legacy terpisah | UAT baru setelah perbaikan guard |
| Pembelian kendaraan | F27 — Legacy PDI bypass checklist rinci | BUG FOUND | Admin/MemberVehiclePurchaseController::pdi; MemberVehiclePurchaseService::savePdi; B06 | Payload tanpa checklist dapat lulus dengan 10 flag boolean | P1 satukan kontrak checklist |
| Pembelian kendaraan | F28 — QC/PDI oleh role quality_control | BUG FOUND | RoleModuleAccessMiddleware::handle; admin.pesanan-mobil.pdi; B07 | Route dipetakan sales.manage; QC hanya qc.manage | Permission PDI khusus |
| Pembelian kendaraan | F29 — Menu PDI dan pickup terpisah | BUG FOUND | Admin/AdminAuxiliaryController::ordersWorkflow; Admin/MemberVehiclePurchaseController::index; B14 | Filter proses tidak memuat pdi_gagal; pickup juga bukan filter khusus | Tampilkan antrean sesuai tahap |
| Pembelian kendaraan | F30 — Pre-order, pembatalan, notifikasi restock | IMPLEMENTED — NOT VERIFIED | app/Services/MemberVehiclePreorderService.php::create/cancel/notifyForRestock; Pelanggan/MemberVehiclePreorderController; routes/web.php | Pre-order adalah daftar minat; bukan transaksi berbayar; tidak ditemukan tes lifecycle khusus yang memadai | UAT notifikasi gudang/member |
| Gateway Midtrans | F31 — Snap transaksi, signature, nominal, dedup, inquiry | COMPLETE & VERIFIED | MidtransClient::verifySignature; MidtransPaymentService; MidtransSandboxStage11ATest | Dibuktikan dengan HTTP fake, bukan provider nyata saat audit | Retain signature/amount tests; perbaiki fraud branch |
| Gateway Midtrans | F32 — Webhook HTTPS dan settlement provider nyata terkini | BLOCKED | POST /payment/midtrans/notification; docs/qa/uat-local/VEHICLE_PURCHASE_PDI_HANDOVER_UAT.md | Audit tidak mengirim transaksi/callback nyata; localhost bukan bukti publik | UAT provider pada staging HTTPS |
| Gateway Midtrans | F33 — Production mode, refund/chargeback provider | BLOCKED | MidtransClient::key menolak is_production; FinancialAdjustmentService::request; MidtransPaymentService::process | Mode sandbox disengaja; refund online belum diaktifkan; status refund setelah success diabaikan | Keputusan scope produksi dan ledger provider |
| Booking servis | F34 — Eligibility ownership, kendaraan, jenis servis, booking | COMPLETE & VERIFIED | EnsureVerifiedCarOwnership; BookingServisService; BookingServisStage4CTest, VerifiedCarOwnershipTest | Membership aktif saja tidak mencukupi layanan owner | Pertahankan dua guard terpisah |
| Booking servis | F35 — Slot kapasitas, durasi, jam operasional, blackout | COMPLETE & VERIFIED | ServiceSchedulingService::availability/assertAvailable; ServiceSchedulingPhase2A1Test | Ada lock jam operasional dan booking; belum stress multi-worker | Load test terisolasi |
| Personel bengkel | F36 — Profil, spesialisasi, jam kerja, cuti, preferensi | COMPLETE & VERIFIED | ServicePersonnelAvailabilityService; Admin/ServicePersonnelController; ServicePersonnelPhase3ATest, ServicePersonnelPhase3CIntegrationTest | Preferensi pelanggan bukan assignment final | UAT ketersediaan lintas hari |
| Personel bengkel | F37 — Assignment utama, assistant dan audit | COMPLETE & VERIFIED | ServiceAssignmentService::assignPrimary/setAssistants; ServiceAssignmentStage2Test | Daftar/dashboard mekanik hanya filter id_petugas utama | Tampilkan tugas assistant dan perketat scope |
| Personel bengkel | F38 — Check-in oleh super_admin / advisor | BUG FOUND | OperationsServisController::fromBooking:40; B13 | isAdmin hanya roleName admin; role baru diarahkan ke petugas.servis.show | Normalisasi roleKey dan route prefix |
| Work Order | F39 — Check-in, diagnosis, jasa dan sparepart draft | COMPLETE & VERIFIED | ServisService::createFromBooking/updateInspection/addDetail; ServisStage5ATest, ServisSparepartStage5BTest | Draft belum mengurangi stok | Pertahankan boundary transaksi |
| Work Order | F40 — Approval customer snapshot dan klarifikasi revisi | COMPLETE & VERIFIED | ServiceExecutionService::createApprovalRequest/decide/requestApprovalClarification; ServiceExecutionPhase4ATest, ServiceWorkflowRefinementTest | Pending/revisi unresolved menahan finalisasi | Uji harga/quantity setelah approval |
| Work Order | F41 — Fitment sparepart dan pencarian compatible | COMPLETE & VERIFIED | SparepartFitmentService; ServisSparepartService::add; SparepartFitmentDependencyTest | Validasi backend tersedia | Perluas data fitment terverifikasi |
| Work Order | F42 — QC wajib sebelum finalisasi | BUG FOUND | ServiceExecutionService::assertCanFinalize; WorkOrderStageService::canAccess; B05 | Jika row QC tidak ada, guard dan wizard memperbolehkan finalisasi | P0 wajib QC passed untuk semua jalur |
| Work Order | F43 — Finalisasi satu tagihan, konsumsi stok, rollback | COMPLETE & VERIFIED | ServisService::finalizeService; InventoryService::consumeServicePartsWithinTransaction; ServisFinalizationStage5CTest | Tes lulus pada skenario yang sudah disiapkan; promo atomic terpisah | Tambah uji lintas guard |
| Work Order | F44 — Pembayaran, invoice, riwayat dan tracking selesai | PARTIAL | PaymentService::sync; ServiceExecutionService::tracking; ServicePaymentStage5DTest, MemberServiceExecutionPhase4BTest | Pembayaran menutup servis, tetapi stage completed selalu false | Definisikan penyerahan servis dan terminal tracking |
| Inventori | F45 — Master sparepart/kategori/supplier, fitment | COMPLETE & VERIFIED | Admin/SparepartController, KategoriSparepartController, SupplierController; SparepartMasterTest, ServiceCarwashSettingsTest | Akses kategori default memiliki B01; CRUD data sendiri teruji | Pertahankan validasi dan perbaiki otorisasi |
| Inventori | F46 — Stok masuk, finalize, adjustment dan mutasi | COMPLETE & VERIFIED | InventoryService::finalize/adjust; InventoryStage4ATest | Penerimaan idempotent status draft; ledger stok tersedia | UAT opname dan audit actor |
| Inventori | F47 — Aggregate kendaraan, reservasi, sold_units/history | COMPLETE & VERIFIED | VehicleInventoryService; VehicleAggregateInventoryFlowTest, VehicleAvailabilityStockTest | Normal path member teruji; trade-in belum menyatu | Uji lintas jalur penjualan |
| Inventori | F48 — Model unit fisik unik dan stok lintas trade-in | PARTIAL | TradeInService::create/finalize; PembelianMobilBekasService::finalize; VehicleInventoryService | Trade-in ubah mobil.status tanpa recordSale; ownership aggregate menyalin VIN template | Tetapkan identitas unit per handover dan ledger bersama |
| Penjualan sparepart | F49 — Draft, harga snapshot, finalize, stok, invoice/payment | COMPLETE & VERIFIED | PenjualanSparepartService; SparepartSalesStage6ATest, SparepartSalesPaymentStage6BTest | Bukan storefront ecommerce sparepart member | Lanjutkan implementasi yang ada |
| Car wash | F50 — Booking, confirm/start, finalisasi, pembayaran | COMPLETE & VERIFIED | BookingCuciService; BookingCuciStage4DTest, CarWashFinalizationStage5ETest | Alur nominal dan status tercakup suite | UAT petugas operasional |
| Car wash | F51 — Kapasitas bay/jam bentrok dan assignment operasional | PARTIAL | BookingCuciService::create/transition; RoleModuleAccessMiddleware | Tidak ditemukan kapasitas slot seperti servis; operational_staff tidak service.manage | Definisikan kapasitas dan wewenang operator |
| Mobil bekas & trade-in | F52 — Pengajuan, inspeksi, offer, penerimaan pelanggan | COMPLETE & VERIFIED | PengajuanMobilService; UsedCarSubmissionStage8ATest | Tidak langsung mengubah stok saat pengajuan | UAT kondisi penolakan |
| Mobil bekas & trade-in | F53 — Pembelian dealer, pembayaran keluar, stok masuk | COMPLETE & VERIFIED | PembelianMobilBekasService; OutgoingPaymentService; UsedCarPurchaseSettlementStage8BTest | Lunas dan finalisasi stok dipisah | Verifikasi unit identity aggregate |
| Mobil bekas & trade-in | F54 — Trade-in, selisih bayar, finalisasi/cancel | COMPLETE & VERIFIED | TradeInService; TradeInStage9ATest | Suite happy path lulus; integration ownership/aggregate masih partial | Satukan ledger dan handover |
| Sales management | F55 — Prospek, aktivitas, target, scope sales | COMPLETE & VERIFIED | SalesManagementController; SalesManagementService; SalesManagementStage3Test | Celah storeLead payload dicatat terpisah | Pertahankan authorizeLead |
| Sales management | F56 — Assignment lead dari input sales | BUG FOUND | SalesManagementController::storeLead:49; B12 | Array union mempertahankan id_sales dari client | P1 override field di server |
| Sales management | F57 — Attribution order/sale dan KPI target terpadu | PARTIAL | SalesManagementService::assign/dashboard; OwnerAnalyticsService::salesPerformance | Assign order dan sale independen; dashboard sales menghitung order handover | Sinkronkan attribution melalui invariant |
| Sales management | F58 — Ranking dan Sales Excellence provisional | COMPLETE & VERIFIED | SalesAwardsService::leaderboard/eligibleSales; SalesExcellenceAwardsStage5Test | Tidak ada dummy ranking; eligible scope pembayaran/handover | UAT target dan tie case |
| Sales management | F59 — Finalisasi penghargaan resmi | BLOCKED | SalesAwardsService::finalize/refundAuthoritativeAvailable | Method authoritative selalu false walau ledger adjustment offline sudah ada | Selesaikan kontrak ledger/reversal |
| Promo & SPK | F60 — CRUD promo, kalkulasi, kuota, preview, simulasi | COMPLETE & VERIFIED | PromoEngineService; PromoSimulationService; Stage14FinalQaTest, PromoServiceReminderStage10CTest | Satu promo per transaksi; belum dual-price member/publik | Uji kuota concurrent dan expired saat settlement |
| Promo & SPK | F61 — Atomic finalize/create + apply promo | BUG FOUND | ServisService::finalizeWithPromo; BookingCuciService::finalizeWithPromo; PenjualanMobilService::createWithPromo; B09 | Transaksi domain commit sebelum apply promo; kegagalan meninggalkan hasil parsial | P0 satu transaksi atomik |
| YZA Points | F62 — Earn idempotent servis/cuci/sparepart | COMPLETE & VERIFIED | YzaPointService::awardIfEligible; YzaPointsStage10BTest | Lunas + domain completed + membership; idempotency ledger | Pertahankan dan uji reversal |
| YZA Points | F63 — Earn pembelian member sesudah handover | BUG FOUND | PaymentService::sync; MemberVehiclePurchaseService::handover; YzaPointService::domainCompleted; B08 | Return dini saat lunas, tidak award saat handover; aggregate status tetap tersedia | Hubungkan final handover ke points |
| YZA Points | F64 — Penukaran poin/voucher dan reversal poin refund | NOT IMPLEMENTED | YzaPointService; routes/web.php; FinalSystemAuditTest:: schema boundaries | Tidak ditemukan redemption ledger/flow; saat ini earn/history | Putuskan aturan penukaran dan reversal |
| Notifikasi & reminder | F65 — In-app DB, unread, open, polling, reminder | COMPLETE & VERIFIED | NotifikasiController; ServiceReminderService; AdminRealtimeNotificationsTest, PromoServiceReminderStage10CTest | Reminder query on-demand; bukan scheduler pengiriman | UAT deduplikasi berulang |
| Notifikasi & reminder | F66 — Fan-out ke role baru, email/WA/push dan retry | PARTIAL | ServiceNotificationService::forAdminBooking; InventoryService::notify; routes/console.php | Penerima admin lama saja pada beberapa event; tidak ada queued dispatcher | Definisikan penerima advisor/gudang/super_admin |
| Finance & ledger | F67 — Incoming/outgoing, verifikasi, invoice, audit field | COMPLETE & VERIFIED | PaymentService; OutgoingPaymentService; AdminPaymentManagementTest, CarSalesPaymentStage7BTest | Nominal terverifikasi dipakai; capture fraud B03 lintas gateway | Uji cancellation + pending payment lama |
| Finance & ledger | F68 — Refund offline, limit, separation of duties, event | COMPLETE & VERIFIED | FinancialAdjustmentService::request/transition; FinancialAdjustmentStage7Test | Confirmed terpisah dari payment asli; bukti dana keluar wajib | Rekonsiliasi settlement bank nyata |
| Finance & ledger | F69 — Reversal authoritative, settlement bank/provider | PARTIAL | FinancialAdjustmentService; OwnerAnalyticsService::paymentSummary; MidtransPaymentService | Tidak ada adapter settlement/refund provider; Awards sengaja blocked | Definisikan ledger authoritative bersama |
| Owner dashboard | F70 — Laporan penjualan/servis/finance/performa dan export | COMPLETE & VERIFIED | OwnerDashboardController; OwnerAnalyticsService; OwnerExecutiveDashboardStage4Test, OwnerDashboardRedesignTest | Query DB nyata; Owner GET report tidak berarti semua admin aman | UAT export PDF/XLSX dan volume besar |
| Owner dashboard | F71 — KPI inventori unit aggregate | BUG FOUND | OwnerAnalyticsService::kpis/inventorySummary; OwnerOverviewService::inventory; B11 | COUNT mobil dipresentasikan stok unit meski aggregate terpisah | Gunakan availability/ledger unit |
| Admin workspace | F72 — Menu activity, transaksi, garage, roles, audit-log | COMPLETE & VERIFIED | Admin/AdminWorkspaceController; Admin/AdminAuxiliaryController; AdminWorkspaceNavigationTest | Dibuktikan render/query/scoping tertentu; bukan semua interaksi browser | UAT filter/export dan permission khusus audit |
| Admin workspace | F73 — KPI siap servis/sport Garage dan audit trail lengkap | PARTIAL | Admin/AdminAuxiliaryController::garage/auditEntries; resources/views/admin/garage/index.blade.php | Definisi siap servis/sport belum tersedia; audit membaca file log terbatas | Definisikan KPI dan retensi audit terstruktur |
| Deployment & operasi | F74 — Deploy, restore backup, HTTPS, monitoring, UAT terbaru | NOT ASSESSED | docs/deployment/LARAVEL_RELEASE_CHECKLIST.md; docs/operations/YZA_OPERATIONAL_RUNBOOK.md | Dokumen ada; deployment/restore/monitoring tidak dieksekusi audit | Staging terisolasi setelah P0 |
| Deployment & operasi | F75 — Browser Member Center dan seluruh menu lintas role | NOT ASSESSED | http://127.0.0.1:8000/pelanggan/member; dokumen UAT historis; PHPUnit render tests | Tidak ada login/mutasi browser baru pada audit read-only | Jalankan UAT terkontrol setelah perbaikan |

## 6. End-to-End Business Workflow

### Pengunjung → member

GET / → /register → validasi RegisterRequest dan hierarchy wilayah → transaksi users/pelanggan/membership pending_payment → login + regenerasi session → /pelanggan/membership/aktivasi → POST membership/pay → Snap redirect → webhook publik atau server inquiry → active → /pelanggan/member.

Backend guard mengizinkan route pelanggan.membership.* untuk pending/cancelled agar jalur pembayaran tidak terkunci oleh middleware member. Return browser bukan satu-satunya jalur aktivasi; webhook mempunyai controller terpisah. Jika browser tidak kembali dan webhook publik tidak terkirim, dibutuhkan inquiry manual. Tidak ditemukan scheduled reconciliation/retry otomatis. Aktivasi hanya update status pending_payment sehingga tidak membuat membership berulang, tetapi status gateway belum monotonic dan fraud challenge masih salah dianggap sukses.

### Pembelian member → Garage

Katalog/detail → pilih unit/warna → create order menunggu_verifikasi dan reservasi → admin approve menciptakan transaksi + penjualan → menunggu_pembayaran → pembayaran online/offline → verified lunas → order lunas (belum ownership) → PDI lengkap → pdi_lulus atau pdi_gagal → jadwal pickup masa depan → siap_diambil → waktu pickup tiba → handover → penjualan selesai, stok sold_units bertambah/physical terjual → order diserahkan → ownership → Garage.

Reject order melepas stok; reject transfer mempunyai recovery pembayaran. Handover berulang mengembalikan record existing sehingga tidak semestinya mengurangi stok dua kali. Alur normal otomatis teruji. Celah: PDI legacy, role QC, antrean pdi_gagal, poin pascahandover, dan identitas fisik unit aggregate.

### Booking servis → finance → histori

Verified owner memilih kendaraan/jenis/tanggal/slot/preferensi teknisi → backend ownership + slot/durasi/capacity validation → booking menunggu → advisor/admin confirm diterima → check-in create/reuse WO dan booking diproses → diagnosis → jasa permintaan awal + rekomendasi → approval snapshot pelanggan → approved/rejected, klarifikasi melalui revisi → draft sparepart fitment → pengerjaan → QC checklist → finalisasi transaksi + konsumsi stok → pembayaran terverifikasi → servis/booking selesai → invoice/riwayat/member notification.

Approval pending dan klarifikasi unresolved memblokir finalisasi. QC gagal juga memblokir **jika record QC ada**, tetapi ketidakhadiran QC masih mengizinkan finalisasi (B05). Tracking menampilkan ready_for_pickup sesudah lunas, tetapi stage completed selalu false; tidak ditemukan event penyerahan servis final tersendiri. Jangan menyamakan lunas dengan bukti pengambilan fisik.

### Car wash, sparepart, mobil bekas, trade-in

- Car wash: member booking → diterima → proses → finalize satu tagihan → pembayaran → selesai. Backend belum mempunyai kapasitas bay seperti slot bengkel.
- Sparepart: petugas buat draft → item/harga snapshot → finalize mengurangi stok → transaksi belum_bayar → bayar/verify → invoice/points. Tidak ditemukan storefront checkout sparepart mandiri untuk member.
- Mobil bekas: pengajuan → pemeriksaan/inspeksi/offer → pelanggan setuju → pembelian dealer → outgoing payment verified/lunas → finalize membuat mobil stok tersedia dan menandai kendaraan lama dijual.
- Trade-in: penawaran → pilih dealer car → reservasi + satu transaksi selisih → bayar → finalize mobil dealer terjual, mobil lama masuk inventory. Selisih menggunakan max(0,...); skenario nilai mobil lama lebih besar dari mobil dealer tidak memodelkan pembayaran balik selisih. Jalur ini belum masuk PDI/handover/ownership member yang sama.

## 7. Member Center Audit

Halaman aktif adalah **resources/views/pelanggan/member-center-empty.blade.php**, dikirim DashboardController::pelanggan untuk /pelanggan/member dan alias /pelanggan/dashboard.

| Elemen | Sumber aktual | Hasil |
|---|---|---|
| Membership Card | customer, membership, MemberIdentityService | Identitas DB, bukan ID acak runtime; guard active berlaku |
| Kendaraan YZA | VerifiedCarOwnershipService::verifiedVehiclesFor | Hanya completed sale lunas + order diserahkan + kendaraan aktif terkait penjualan; bukan semua kendaraan manual |
| Booking Aktif | MemberDashboardService::for | Salah definisi count setelah limit/take; maksimum list 8, bukan total DB B10 |
| YZA Points | poin_member + riwayat_poin, rate config | Earn/history nyata; pembelian member handover belum mengisi B08; redemption belum dibuat |
| Promo Member | PromoMember::visible count/list | Count periode/status DB; tidak menjamin semua promo eligible/kuota masih ada untuk customer ini |
| Notifikasi | Notifikasi id_user, unread count, latest 5 | Data DB ter-scope akun; bukan push/WhatsApp |
| Riwayat aktivitas | gabungan transaksi latest 12 + booking terbatas | Ringkasan, bukan audit histori lengkap |
| Navigasi layanan | Blade sidebar/nav + routes/middleware | Link dan render teruji; seluruh click/modal/responsive browser terbaru NOT ASSESSED |

Pembatasan kepemilikan memang backend: `EnsureVerifiedCarOwnership`, `VerifiedCarOwnershipService`, serta pemeriksaan kendaraan milik pelanggan dalam booking service. Manual register vehicle tidak membuktikan pembelian YZA. `KendaraanController::index` menampilkan verifiedVehicles, sementara route create/store manual masih ada; kendaraan manual dapat tersimpan namun tidak otomatis muncul pada Garage verified. Perlu copy UX jelas dan kebutuhan bisnis kendaraan non-YZA.

Status membership pending/active/cancelled ada dalam kode/UI. Expiry/renewal bukan fitur tersedia. Akun aktif diverifikasi melalui status, validasi data dan password; **verifikasi kepemilikan email** belum tersedia.

History servis dan transaksi memeriksa customer; test lintas customer/IDOR tersedia. Tetapi invoice/riwayat bukan bukti seluruh alur pelanggan telah diuji pada browser dan provider asli.

## 8. Purchase & Payment Audit

### Stok dan identitas

Order create melock Mobil dan aggregate inventory, memeriksa available stock/reservasi, menolak unit physical yang tidak tersedia. Approval membuat satu transaksi dan satu penjualan existing; handover melock order/car lalu recordSale. VehicleAggregateInventoryFlowTest membuktikan reservasi → settlement → handover dan adjustment tidak menimpa sold_units.

Namun, aggregate inventory adalah kumpulan unit dengan satu record katalog/VIN template. Handover menyalin nomor rangka/mesin dari Mobil yang sama, bukan memilih physical unit baru per penjualan. Tidak ada alokasi VIN unik per order pada workflow tersebut. Risiko: dua pelanggan berbeda dapat memperoleh identitas fisik yang sama meski sold_units numeriknya benar. Ini perlu keputusan model unit fisik dan uji dua handover berbeda; belum dilakukan transaksi kedua pada database utama.

Jalur trade-in memakai mobil.status dan tidak memanggil VehicleInventoryService::recordSale. Potensi ketidaksinkronan aggregate vs physical harus diperbaiki melalui layanan existing, bukan dengan koreksi manual stok.

### Harga, promo dan invoice

Checkout member mengambil harga_jual dari server, snapshot warna dan harga. Approval menetapkan diskon=0. Tidak ditemukan mesin harga member terpisah atau promo publik otomatis pada checkout ini. Karena itu **tidak dapat mengonfirmasi aturan harga publik/member eksklusif yang belum diimplementasikan**. UI benefit pemasaran tidak sama dengan kalkulasi tagihan.

PromoEngineService menahan satu promo per transaksi, menggunakan integer rupiah, snapshot, minimum, quota dan usage/member. Penjualan petugas mempunyai diskon input dan createWithPromo; promo diterapkan ke subtotal transaksi, bukan stacking eksplisit dua model harga member/publik. Wrapper promo tidak atomik dengan domain creation/finalization (B09), dan angka domain seperti penjualan.total_akhir/servis.total_biaya berpotensi tidak sama dengan transaksi.total sesudah promo. Laporan Owner memakai total_akhir untuk nilai penjualan, sehingga perlu invariant nominal lintas invoice/payment/report.

Invoice member mempunyai regression snapshot harga berubah, pending/rejected tidak dihitung sebagai paid, invoice owner-scoped, paid vs unpaid yang jelas. Pembayaran transfer tidak otomatis verified; tunai verified pada pencatatan resmi; overpayment diperiksa dalam lock transaksi. Gateway sukses menghasilkan pembayaran terverifikasi dan menyinkronkan state transaksi.

### Midtrans dan lifecycle

Signature SHA-512 memakai order_id+status_code+gross_amount+server key dan hash_equals. CSRF exception callback ada; tidak boleh menggunakan CSRF sebagai pengganti signature. Event key dan amount check tersedia; gateway transaksi sukses tidak diturunkan oleh pending terlambat, berbeda dengan membership.

B03: kedua service menerima capture selama fraud_status bukan deny, termasuk challenge/null. Probe membership membuktikan active pada challenge. Perlu acceptance yang eksplisit dan penanganan status_code konsisten pada webhook/inquiry, tanpa mempercayai query browser.

Mode production saat ini **sengaja ditolak** oleh MidtransClient::key. Refund online ditolak FinancialAdjustmentService, dan gateway transaksi sukses mengabaikan update non-success berikutnya termasuk refund/chargeback. Jangan menyebut fitur ini production-ready. Audit tidak menjalankan transaksi provider atau menampilkan kunci.

### PDI, pickup, handover

Checklist rinci, notes untuk issue, pelunasan, PDI lulus, jadwal tidak lampau, serta handover tidak sebelum jadwal tersedia dan diuji. Legacy savePdi masih dapat melewati checklist rinci (B06). Nomor identitas penerima opsional; dokumen_lengkap dan kendaraan_sesuai langsung diset true saat handover oleh service, sehingga perlu konfirmasi operasional nyata, bukan menganggap ada upload/cek dokumen independen.

## 9. Service & Work Order Audit

Implementasi mencakup scheduling, personnel profile, specialization, working hours/time-off, preferensi member, advisor, primary/assistant assignment history, diagnosis, request awal vs recommendation, approval snapshot, klarifikasi revisi, fitment, stock draft, QC dan invoice. Ini bukan modul yang perlu dibuat ulang.

- CreateFromBooking mengunci booking/kendaraan dan mengembalikan WO existing bila booking sudah mempunyai WO; kilometer tidak boleh mundur saat check-in.
- Mekanik primary/assistant tercatat, tetapi index dan dashboard mechanic menggunakan id_petugas utama. Assistant bisa membuka lewat assigned.wo, namun belum tentu menemukan tugas pada daftar/dashboard.
- AddDetail dan addSparepart membatasi status dikerjakan sebelum transaksi. Fitment ada pada service; approval memakai harga server snapshot, bukan nominal client.
- Approved sparepart dapat menggunakan draft matching; approval tidak langsung mengonsumsi stok. Stok dicek ulang dan dikurangi saat finalisasi dalam transaksi DB, memakai urutan id_sparepart untuk mengurangi risiko deadlock.
- Ada guard approval pending/klarifikasi dan QC gagal. **Tidak ada kewajiban keberadaan QC** di assertCanFinalize; wizard finalisasi juga mengizinkannya. Probe read-only pada WO operasional aktif tanpa QC mengembalikan ALLOWED.
- FinalizeWithPromo memanggil finalizeService yang sudah commit sebelum apply promo. Probe testing dengan promo tidak ada membuktikan exception namun id_transaksi tetap terbentuk sampai outer probe rollback.
- Pembayaran lunas menutup servis dan booking, mengirim notifikasi, menambahkan points bila eligible. Histori/invoice member ada.
- B02 memungkinkan mekanik melewati scope assignment melalui mutasi langsung. B13 membedakan admin literal dengan super_admin/advisor dan memilih prefix redirect salah.
- QC N/A diterima sebagai hasil checklist; kewajiban item/NA perlu dipastikan dengan kebijakan bisnis. Tidak dilakukan perubahan checklist selama audit.
- Notification tracking bukan bukti pelanggan telah mengambil kendaraan; stage completed selalu upcoming.

## 10. Cross-Module Integration

| Integrasi | Bukti hubungan | Penilaian |
|---|---|---|
| Checkout member → pesanan admin | Satu tabel pesanan_mobil_member, controller pelanggan/admin | Terhubung; suite normal path lulus |
| Payment sah → transaksi/order | PaymentService::verify/sync, MidtransPaymentService | Terhubung; fraud branch dan membership terminal belum aman |
| Payment → PDI | assertPdiEntry dan gate lunas | Terhubung; legacy PDI bypass rinci masih ada |
| Handover → stok → ownership → Garage | MemberVehiclePurchaseService::handover, VerifiedCarOwnershipService | Terhubung normal path; identitas unit aggregate perlu diperjelas |
| Handover → YZA Points | Tidak ada awardIfEligible pada handover; sync payment return dini | **Terputus B08** |
| Penjualan legacy/trade-in → Garage | Tidak membuat memberOrder handover yang disyaratkan ownership | **Partial**; bukan semua pembeli dealer menjadi verified owner |
| Booking servis → jadwal → WO | SchedulingService, ServisService::createFromBooking | Terhubung; alias role baru check-in B13 |
| WO → sparepart inventory | FinalizeService → consumeServicePartsWithinTransaction | Terhubung dan rollback teruji; QC gate belum lengkap |
| WO → customer approval/revisi → QC | ServiceExecutionService | Terhubung; beberapa bypass mutation/finalize perlu ditutup |
| Payment servis → finance/Owner/history | Sumber bersama transaksi/pembayaran/servis | Terhubung; definisi periode tanggal pembayaran vs verified_at berbeda |
| Penjualan final → Owner | OwnerAnalyticsService::finalVehicleSales | Terhubung; nilai promo dan stok unit perlu rekonsiliasi |
| Assignment order → sale → ranking | assign entity terpisah; awards memakai penjualan.id_sales | **Partial**; perubahan id_sales order tidak otomatis sale |
| Refund offline → net receipt Owner | FinancialAdjustmentService, paymentSummary | Terhubung; refund provider dan awards authoritative belum selesai |
| Booking/stock → semua role penerima | beberapa query hanya nama_role=admin | **Partial** untuk super_admin/advisor/gudang modern |
| Dashboard lintas role → status terkini | Query DB saat request; polling notifikasi | Ada, bukan websocket/event bus; count/tracking tertentu salah |

Perubahan satu role akan terlihat pada role lain bila keduanya membaca entity yang sama dan melakukan refresh/polling. Tidak ada bukti realtime push menyeluruh.

## 11. Bugs, Risks & Missing Features

### 14 bug teridentifikasi

Level bukti: **R** = dieksekusi read-only pada runtime/middleware/guard; **T** = probe terisolasi pada test DB, di-rollback; **S** = jalur defect dapat ditelusuri langsung di source, belum dieksploitasi HTTP/browser. Semua temuan ini adalah kondisi audit, bukan sudah diperbaiki.

| ID | Prioritas | Bukti dan trigger | Dampak dan kriteria perbaikan |
|---|---|---|---|
| B01 | P0 | **R+S** RoleModuleAccessMiddleware::handle default dashboard.view; PUT /admin/pengaturan → PengaturanController::updateMany; PATCH kategori/supplier toggle tanpa guard admin khusus. Owner/mechanic/operational/QC lolos probe module gate | Owner read-only dan least privilege gagal. Semua mutation harus punya permission aksi eksplisit; role tanpa wewenang 403 tanpa perubahan DB. Audit/roles/activity GET juga memakai dashboard.view, berpotensi membuka data lintas modul |
| B02 | P0 | **R+S** routes/web.php admin.servis.inspection/detail/sparepart/finalize/findings/approvals/qc tanpa assigned.wo; mechanic dipetakan service.assigned; FormRequest authorize true dan controller tidak scope penugasan | Mekanik dapat menyasar WO lain bila status memenuhi. Terapkan scope utama/assistant untuk setiap mutation/search; negative tests langsung URL |
| B03 | P0 | **T+S** MembershipPaymentService:53 dan MidtransPaymentService::process: capture && fraud_status != deny; probe challenge → membership active | Dana belum layak dianggap final dapat membuka layanan/menutup tagihan. Success predicate eksplisit untuk fraud/status yang sah; challenge/null/deny wajib tidak aktif/lunas |
| B04 | P1 | **T** MembershipPaymentService::processNotification menulis ulang status tanpa terminal guard: capture lalu pending → gateway pending, membership tetap active | Gateway dan membership tidak konsisten. Event terlambat/duplikat tidak boleh meregresikan terminal; uji replay dan urutan acak |
| B05 | P0 | **R+S** ServiceExecutionService::assertCanFinalize hanya if(qc && status != passed); WorkOrderStageService::canAccess menerima !qualityControl. WO aktif tanpa QC dan tanpa tagihan → kedua guard ALLOWED | Bisa membuat invoice/konsumsi stok tanpa QC. Wajib ada row QC passed, semua mandatory result valid, approval/clarification selesai |
| B06 | P1 | **S** Admin/MemberVehiclePurchaseController::pdi memilih savePdi jika payload tidak punya checklist; savePdi hanya 10 bool flags | Checklist rinci 36 item dapat dilewati melalui endpoint sama. Hilangkan perbedaan jaminan legacy/rinci; payload legacy tidak boleh meluluskan PDI tanpa standar rinci |
| B07 | P1 | **R+S** Route admin.pesanan-mobil.pdi/start diklasifikasi sales.manage; role quality_control hanya qc.manage | QC tidak dapat menjalankan PDI pembelian lewat backend meski tanggung jawabnya QC/PDI. Buat permission kontrak PDI dan negative role tests |
| B08 | P1 | **R+S** PaymentService::sync return setelah order lunas; handover tidak award points; domainCompleted penjualan juga menuntut mobil.status=terjual sementara aggregate tetap tersedia. Satu order diserahkan tidak mempunyai riwayat poin | Member kehilangan points pembelian sah. Award idempotent di final domain event dan gunakan status penjualan/order, bukan status template aggregate |
| B09 | P0 | **T+S** ServisService::finalizeWithPromo, BookingCuciService::finalizeWithPromo, PenjualanMobilService::createWithPromo, TradeInService::createWithPromo: dua transaksi terpisah. Probe invalid promo → ModelNotFoundException, WO id_transaksi tetap ada | Error UI dapat meninggalkan tagihan/stok/reservasi sudah berubah. Satu transaksi atomik mencakup promo dan domain; mismatch harga domain/report harus diuji |
| B10 | P2 | **S** MemberDashboardService::for: limit 5 tiap jenis, sort/take 8, baru hitung activeBookingCount | Statistik Booking Aktif undercount dan bisa kehilangan agenda. COUNT backend tanpa limit; limit hanya daftar |
| B11 | P2 | **R+S** OwnerAnalyticsService::kpis/inventorySummary dan OwnerOverviewService::inventory menghitung baris Mobil. DB 12 aggregate total 59/sold 1, katalog tersedia 23 baris | KPI jumlah unit tidak merepresentasikan stok fisik aggregate/reservasi. Gunakan satu inventory query; bedakan produk, unit, available/reserved/sold |
| B12 | P1 | **S** SalesManagementController::storeLead:49 memakai $data + ['id_sales'=>$salesId]; id_sales client yang sudah ada tidak ditimpa oleh union | Sales dapat menentukan assignment lain lewat payload, meski $salesId dihitung dari actor. Override field setelah validation; non-admin tidak bisa assign sales lain |
| B13 | P1 | **S** OperationsServisController::fromBooking:40 memakai roleName==='admin', selain itu redirect petugas.servis.show; role:petugas menolak super_admin/advisor | WO dapat terbuat tetapi redirect 403; assignment pilihan role baru diabaikan. Pilih privilege berdasarkan roleKey/permission dan prefix route request |
| B14 | P1 | **S** AdminAuxiliaryController::ordersWorkflow pdi/pengambilan memakai status proses; MemberVehiclePurchaseController::index proses hanya lunas,pdi_lulus,siap_diambil | Antrean PDI tidak menampilkan pdi_gagal untuk perbaikan; pickup bercampur tahap lain. Filter khusus tahap + tes order gagal/memperbaiki/siap pickup |

B03 dan B09 terbukti dengan probe tambahan, tetapi tidak dimasukkan sebagai failure PHPUnit karena probe bukan test suite yang telah ada. **0 failing tests tidak berarti 0 bug.** B01/B02 dibuktikan pada gate serta source sampai aksi; tidak dilakukan unauthorized update pada database utama.

### Risiko dan gap lain, tidak ditambahkan ke hitungan 14 bug

1. **Identitas aggregate dan ownership lintas sumber**: satu template Mobil/VIN untuk beberapa penjualan, legacy direct sale/trade-in tidak mempunyai member handover. Perlu uji multi-customer/two-unit, bukan asumsi dari tes satu handover.
2. **Concurrency**: banyak DB transaction/row lock positif, tetapi `MidtransPaymentService::createAttempt` memakai lockForUpdate tanpa outer DB::transaction; lock bisa tidak melindungi rangkaian check/create di runtime autocommit. Service PenjualanMobil membaca aggregate tanpa lock khusus inventory, dan reopenRejectedPayment serupa; audit tidak menjalankan multi-process race.
3. **Gateway cancellation/reconciliation**: verify manual tidak memeriksa kembali status transaksi dibatalkan secara eksplisit; rejection satu transfer dapat membatalkan order walau payment lain telah terverifikasi. Perlu skenario dua pending/DP/reject, late settlement, double attempt, expired callback, refund/chargeback.
4. **Quota promo**: quota completed dihitung saat settlement. Dua tagihan diskon bisa tercipta sebelum quota habis; pelunasan terakhir dapat gagal karena quota sudah dipakai. Dibutuhkan keputusan reservasi quota dan graceful reconciliation dana provider.
5. **Approval vs execution**: matching by sparepart ID/line bukan selalu quantity/nominal versi approval; tambahan jasa tanpa approval hanya ditahan pada kondisi tertentu di assertReadyForQualityControl. QC passed lalu detail berubah belum menginvalidasi QC otomatis. Perlu immutable snapshot/reapproval dan finalization invariant.
6. **Membership receipt**: gateway membership tidak membuat Pembayaran/Transaksi incoming untuk activation fee; laporan umum finance berpusat pada Pembayaran. Rekonsiliasi pendapatan membership perlu definisi ledger, bukan asumsi tercakup omzet.
7. **Notifikasi**: admin literal saja untuk stock/booking event tertentu; tidak otomatis super_admin/advisor/gudang; tidak ada queue retry, delivery status email/WA, atau schedule inquiry otomatis.
8. **Audit trail**: AdminAuxiliaryController membaca seluruh file laravel*.log dan memfilter tiga channel; bukan ledger immutable lengkap. Pembacaan dua kali dan load seluruh baris berisiko memori/performa; identitas actor pada purchase audit dapat berupa petugas ID atau user ID yang ditafsir seragam.
9. **Dashboard/definisi**: Garage admin readyService/sportUnits bernilai null dan ditampilkan sebagai definisi belum tersedia. Tracking servis completed selalu false. Member promo count hanya visible, bukan eligibility personal.
10. **Keamanan operasional**: local debug=true adalah kondisi lokal wajar, bukan konfigurasi deployment aman. Akun demo/staff provisioning memakai pola password awal predictable; nilainya tidak ditampilkan laporan, dan command dibatasi local/testing/uat. Inventarisasi keberadaan akun demo pada staging/production perlu proses tersendiri.
11. **Kualitas kode**: service/controller sangat padat satu baris; cakupan FormRequest nama role legacy tidak konsisten; banyak catch Throwable mengubah exception menjadi error UI, termasuk exception otorisasi/validation tertentu. Logging audit tidak selalu atomik dengan DB.
12. **Scope tidak ada**: email verification/reset via email, renewal membership, points redemption, public/member dual pricing eksklusif, settlement/refund provider authoritative. Jangan membuatnya tanpa keputusan bisnis.

## 12. Test & Verification Results

### Regression terbaru

Perintah inti: `C:/xampp/php/php.exe vendor/bin/phpunit --no-progress --do-not-cache-result --log-junit docs/qa/YZA_FULL_SYSTEM_AUDIT_2026-10-31_TEST_RESULTS.xml`.

Environment proses ditetapkan eksplisit: APP_ENV=testing; DB_CONNECTION=mysql; DB_HOST=127.0.0.1; DB_DATABASE=yza_automotive_laravel_test; DB_URL kosong; cache/session array; queue sync; LOG_CHANNEL=null. Tidak menampilkan password/kunci di laporan. phpunit.xml memaksa APP_ENV dan nama DB testing; bootstrap/cache tidak memiliki config.php yang mengoverride setting.

Preflight read-only mengonfirmasi database testing berbeda dari operasional, mempunyai 76 tabel/54 migration, 13 users/26 mobil/2 servis fixture. TestCase guard menolak nama database selain test. Diperiksa tidak ada override koneksi lain/reset migration dalam suite yang dijalankan; sebagian tes memakai DatabaseTransactions, sebagian read-only render/unit. Command provision yang dipanggil tes hanya bekerja pada test DB dalam transaksi tes; tidak dipanggil pada operasional.

| Hasil | Nilai |
|---|---:|
| PHPUnit | 11.5.56 |
| PHP | 8.2.4 |
| Tes | **434** |
| Assertions | **1.650** |
| Failure / error / skipped | **0 / 0 / 0** |
| Exit code | **0** |
| Durasi console | **67,152 detik** |
| Memory | **170 MB** |
| Artefak | JUnit XML pada docs/qa; waktu agregat XML 65,858109 detik |

Harness umum membekukan Carbon ke **19 Oktober 2026 10:00 Asia/Jakarta** untuk fixture scheduling; audit dan tanggal laporan tetap 31 Oktober. Karena itu lulus scheduling tidak membuktikan seluruh edge tanggal kalender nyata 31 Oktober. Harness juga menonaktifkan ValidateCsrfToken; lulus feature test **tidak membuktikan negative-CSRF browser**. Tidak ada coverage instrumentation persentase, mutation testing, load test atau browser test baru.

Bidang tercakup: auth/navigation, identity/master, stock/availability, booking/test drive, customer scoping, purchase snapshot/invoice/manual payment, PDI/pickup/handover/ownership, service scheduling/personnel/assignment, approval/fitment/QC/finalization/payment, carwash, sparepart sales, used car/trade-in, promo/simulation/points, roles, sales/awards, offline adjustments, Owner analytics/redesign dan Admin workspace.

Gaps spesifik tes: membership pay/webhook lifecycle (pencarian source test tidak menemukan suite khusus MembershipPaymentService), preorder lifecycle, role default mutations, mekanik mutasi WO unassigned, mandatory-QC absent, fraud challenge, late status membership, invalid-promo rollback, points sesudah member handover aggregate, dua VIN/unit aggregate, client id_sales override, dan browser controls. Semua nama test tersedia di inventory/JUnit.

### Probe tambahan audit

| Probe | Lingkungan/metode | Hasil |
|---|---|---|
| Routes action existence | Bootstrap router, tanpa HTTP login | 427 routes; 0 method/controller action hilang |
| Model-table | Model getTable + schema metadata read-only | 73/73 tabel model tersedia |
| Role module gate | Request sintetis + user DB, closure sentinel tanpa controller mutation | Owner/mechanic/operational/QC lolos settings PUT dan kategori PATCH; QC ditolak PDI purchase |
| QC absent | Dua WO aktif operasional tanpa QC, guard read-only | assertCanFinalize ALLOWED; wizard finalisasi ALLOWED |
| Fraud challenge membership | Test DB, synthetic trusted inquiry-equivalent payload, processNotification(...,false) | membership menjadi active pada capture+challenge |
| Late pending membership | Test DB pada attempt probe yang sama | gateway pending sementara membership active |
| Promo rollback | Test DB, WO salinan, invalid promo | exception ModelNotFoundException; WO id_transaksi masih ada sebelum outer rollback |
| Integrity snapshot | SQL read-only | 0 negative sparepart; 0 orphan payment; 0 sold>stock_total |

Probe membership menonaktifkan verifikasi signature **hanya lewat argumen internal probe**, bukan mengubah config atau source aplikasi. Tujuannya mengisolasi decision logic yang juga dipanggil sesudah signature/inquiry; bukan membuktikan webhook tanpa signature diterima. Semua mutasi probe diakhiri rollback. Probe pertama tidak menemukan pending membership fixture dan berhenti sesudah rollback; probe berikutnya membuat state pending sementara pada fixture dalam transaksi test dan berhasil. Tidak ada klaim run pertama lulus.

Tidak dilakukan tindakan browser autentikasi/pembayaran pada URL operasional; halaman member ditelusuri melalui route-controller-service-Blade dan HTTP tests terisolasi. Tidak ada klaim screenshot baru atau Midtrans merchant dashboard dibuka.

### Bukti historis dan batasannya

- REGRESSION_RECOVERY_REPORT: 407 pass/1.488 assertions; menjelaskan salah-target DB pada run lama sebelum guard. Ini konteks sejarah, bukan hasil hari ini.
- VEHICLE_PURCHASE_PDI_HANDOVER_UAT: klaim provider sandbox dan UI PDI→Garage→Owner, belum diulang.
- YZA_STAGE9B_FINAL_UAT_CLOSURE: sembilan role login smoke, belum full CRUD setiap menu; native browser zoom 67% belum terverifikasi.
- Dokumen release “final” lama bukan persetujuan release bagi perubahan lokal baru.

## 13. Release Readiness

| Target | Penilaian audit |
|---|---|
| Demo lokal terkontrol | Backend substantial dan regression lulus; jelaskan batasan serta hindari menyatakan P0 aman |
| Staging untuk reproduksi/QA | **Bisa disiapkan secara terisolasi**, tanpa data pelanggan nyata dan tanpa klaim UAT sign-off |
| Staging UAT acceptance | **Belum lulus gate** sampai B01/B02/B03/B05/B09 ditutup dan negative tests baru lulus |
| Provider sandbox E2E terbaru | **Blocked verifikasi**; perlu HTTPS callback, inquiry dan reconciliation nyata |
| Production | **NOT READY**: P0 terbuka, sandbox-only, authoritative refund/reversal dan browser/deployment proof belum cukup |

Release gate minimum: otorisasi 12 role yang eksplisit, assignment invariant, payment fraud/amount/idempotency/late event, mandatory QC, promo atomic, unit identity, points final event, UAT lintas role/browser, callback HTTPS dan provider proof, backup-restore drill, secrets/config deployment, log retention/monitoring/error handling, dan keputusan scope refund/awards. Checklist ini berasal dari temuan konkret dan dokumen deployment yang ada, bukan alasan untuk mengubah sistem selama audit.

Keberadaan file export PDF/XLSX lama tidak membuktikan output current valid/format benar. PHPUnit menguji route/konten tertentu; audit tidak merender ulang semua export atau menjalankan deployment tools.

## 14. Recommended Development Roadmap

Urutan berikut mengikuti ketergantungan, bukan membuat ulang fitur yang sudah ada. “Target penyelesaian” berupa hasil yang dapat diterima, bukan janji tanggal tanpa estimasi tim.

| Urutan | Prioritas / pekerjaan | Alasan teknis | Target penyelesaian | Kriteria pengujian |
|---|---|---|---|---|
| 1 | **P0 — tutup permission fallback dan scope assignment (B01/B02)** | Gate paling luar masih mengizinkan mutasi lintas role/WO; semua UAT berikutnya bergantung padanya | Daftar explicit view/create/update/verify/QC permission; Owner read-only; mekanik hanya assignment aktif; admin/super_admin kompatibel | Parameterized role×endpoint untuk mutation raw URL; deny 403 tanpa side effect; assistant aktif diizinkan sesuai wewenang; revoked assignment ditolak |
| 2 | **P0/P1 — state machine gateway membership/transaksi (B03/B04)** | Salah interpretasi status berisiko mengaktifkan layanan dan menutup tagihan | Success/fraud/status_code terpusat; terminal monotonic; trusted inquiry, duplicate events dan reconciliation konsisten | capture accept/challenge/deny/null; settlement nominal salah; invalid signature; duplicate/out-of-order; browser tidak kembali; retry server; satu ledger payment/activation |
| 3 | **P0/P1 — mandatory QC dan kontrak PDI tunggal (B05/B06/B07)** | Finalisasi/stok/handover harus bergantung quality gate backend | Tidak bisa finalisasi tanpa passed QC; legacy PDI memenuhi standar rinci; role QC dapat tugasnya tanpa sales.manage | POST langsung tanpa QC, failed/incomplete QC, pending/rejected approval; perubahan detail setelah QC; checklist legacy/rinci; role QC vs sales/mechanic |
| 4 | **P0 — atomik promo + finalisasi/pembuatan (B09)** | Error promo saat ini bisa meninggalkan stok/tagihan/order berubah | Satu DB transaction mencakup domain, promo, reservasi, invoice snapshot; source nominal konsisten | Invalid/expired/exhausted promo menolak tanpa tx/stok/history berubah; sum invoice=payment due=report snapshot; rollback fault injection |
| 5 | **P1 — satukan event penjualan final, unit identity dan Garage/points (B08 + gap lintas sumber)** | Handover normal ada, tetapi points dan pembeli legacy/trade-in tertinggal | Physical unit unik per handover; ledger stok shared; ownership dan points idempotent lintas jalur yang disetujui bisnis | Dua pembeli/two-unit distinct VIN; duplicate handover; direct sale/trade-in → ownership; aggregate sold/history; points tepat sekali dan refund/reversal rule |
| 6 | **P1 — perbaiki role baru/check-in/assignment sales/antrean PDI (B12/B13/B14)** | Portal modern masih tersangkut logika admin/petugas lama | Prefix berdasarkan request/permission; server override id_sales; queue PDI gagal terlihat; operator diberi aksi sesuai keputusan | Super_admin dan advisor check-in tidak 403; injected id_sales ditolak/diabaikan; assistant melihat tugas; pdi_gagal dapat diperbaiki sampai handover |
| 7 | **P1 — kontrak finance authoritative dan Awards** | Ledger offline sudah ada, tetapi refund provider/reversal belum authoritative dan Awards selalu blocked | Definisi sumber kebenaran net receipt/refund/reversal + pendapatan membership; aktifkan hanya capability yang sudah terbukti | Partial/full offline refund, SoD/evidence, pending vs confirmed, provider refund/chargeback, net report, awards finalized hanya tahun/target/ledger valid |
| 8 | **P2 — KPI, notification recipients dan terminal servis (B10/B11)** | Angka DB nyata belum tentu definisinya benar; role modern kehilangan event | COUNT penuh vs preview limit; stok unit aggregate; penerima advisor/gudang/super_admin; terminal pickup servis | >8 booking aktif; stok campuran aggregate/physical; rerun event tidak spam; approval/QC notif; servis diambil mencapai terminal |
| 9 | **P2 — UAT browser/provider dan deployment evidence** | Automated suite bukan browser/provider/operational proof | Staging HTTPS terisolasi; semua role dan critical workflows, restore drill, export, observability | Browser desktop/mobile/zoom, CSRF negatif, session isolation, provider duplicate callback/late inquiry, offline payments, restore + reconciliation dan monitoring alerts |
| 10 | **P3 — rapikan UX dan kode setelah invariant stabil** | Duplikasi/padatnya service memperbesar risiko perubahan; KPI placeholder perlu keputusan | Copy kendaraan manual vs verified, menu/filter role yang tepat, reusable view/style; refactor kecil dengan regression | Accessibility, responsive layout, state kosong, modal/keyboard, pagination, snapshot nominal tetap, seluruh suite + regression temuan lulus |

Jangan memulai points redemption, renewal atau dual pricing hanya karena belum ada: putuskan scope bisnis lebih dahulu. Jangan “mengatasi” bug dengan mengubah status SQL, menambah role baru, mengganti harga di frontend, atau menjalankan reset database.

## 15. Immediate Next Task

**Lanjutkan pertama dari hardening otorisasi backend: RoleModuleAccessMiddleware dan seluruh mutasi Work Order.**

Alasannya: modul utama sudah tersedia dan regression normal path lulus, tetapi Owner/role staf dapat melewati permission fallback dan mekanik dapat menyasar WO yang bukan penugasannya. Keduanya menempatkan perubahan pembayaran, stok, pekerjaan servis, dan konfigurasi bisnis di luar wewenang yang seharusnya. Perbaikan paling kecil yang bernilai besar adalah menggunakan mapping permission aksi eksplisit dan memasang scope assignment pada seluruh mutation, kemudian menambahkan regression untuk temuan B01/B02. Sesudah itu baru payment state machine, mandatory QC dan atomic promo.

Definisi tugas pertama selesai:

1. Owner tetap dapat GET laporan/export, tetapi seluruh mutasi bisnis/pengaturan ditolak.
2. Mekanik primary/assistant aktif hanya dapat aksi yang diizinkan pada WO assignment-nya; unassigned/revoked selalu ditolak.
3. Endpoint admin legacy dan role modern mempunyai aturan yang konsisten; UI mengikuti aturan backend.
4. Negative tests membuktikan **status HTTP dan tidak adanya perubahan DB**, bukan hanya menu tersembunyi.
5. Suite 434 baseline serta regression temuan baru lulus pada DB testing terisolasi; tidak ada perubahan data operasional.

**Implementasi tahap berikutnya belum dimulai. Audit berakhir dengan laporan dan bukti; menunggu instruksi pengguna.**


Pemeriksaan akhir audit: jumlah row operasional yang diperiksa tetap sama dengan preflight (users 18, pelanggan 10, memberships 10, mobil 26, vehicle_inventories 12, order 2, servis 6, transaksi 13, pembayaran 18, gateway 8, kendaraan pelanggan 2). Ini pemeriksaan jumlah row terpilih, bukan checksum seluruh isi database. Git status akhir mempertahankan 53 tracked changes yang sudah ada; tambahan audit hanya tiga artefak docs/qa di atas. Tidak dilakukan commit/push atau implementasi perbaikan.


