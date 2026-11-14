# YZA Automotive — Super Admin Role, Menu & Icon Audit

Tanggal: 8 November 2026. Ruang lingkup: navigasi dan presentasi; tidak mengubah kebijakan role, transaksi, pembayaran, promo, Work Order atau QC/PDI.

## Stage A — temuan sebelum koreksi

- Baseline aktual: **665 tests / 3446 assertions PASS**, `storage/logs/super-admin-audit-baseline.xml`.
- Inventaris read-only operasional: **36 menu sidebar Super Admin**, seluruh route ada. Bukti `super-admin-menu-before.json`, dikumpulkan melalui transaksi READ ONLY dan ROLLBACK, tanpa request HTTP operasional.
- Akun `admin506` / Administrator YZA mempunyai stored role `admin`, effective key `super_admin` melalui `Role::key()`. Akun `superadmin506` mempunyai stored/effective `super_admin`. Nama tampilan bukan dasar otorisasi. Sesi browser yang melampirkan screenshot tidak dibaca/diasumsikan; identitas akun dibuktikan dari relasi database, bukan klaim cookie sesi.
- Proposal `Proposal_Kerja_Sama_YZA_Automotive_Update_Terbaru_2026(2).docx` tidak ditemukan dalam workspace, termasuk pencarian hidden. Karena itu bagian 5/6 proposal tidak diklaim telah diverifikasi. Pemetaan proposal dalam laporan ini dibatasi cakupan yang diberikan pengguna dan dokumentasi implementasi terbaru. Tidak menghapus fitur berdasarkan proposal yang tidak tersedia.
- Pembacaan source mengonfirmasi 9 effective role staf yang diminta, alias legacy admin/petugas, dan pelanggan. Owner tetap dilarang mutasi bisnis oleh module.access; WO menggunakan service authorization/assignment; tidak ditemukan Gate/Policy Laravel terpisah sebagai sumber otorisasi.
- Ikon: sidebar mengabaikan deklarasi icon per item, memakai match label dengan default `document` untuk sebagian besar menu; seluruh staf memakai `document`. Ini akar ikon generik berulang, bukan library hilang.
- Active state membandingkan nama route secara persis sehingga halaman create/edit/detail tidak menandai menu induk.
- Tooltip link Super Admin tidak berada di anchor, sehingga label collapsed tidak cukup terbantu. Label topbar `Administrator` statis juga dipakai staf lain. Link Pengaturan dropdown tampil kepada staf tanpa staff.manage dan menghasilkan 403.
- Activity memakai centang hijau untuk `menunggu`; ikon dibaca pada notifikasi menyiratkan selesai. Statistik menghitung pesanan lunas dalam Diproses sekaligus Perlu Tindakan; PDI berjalan bisa dihitung dua kali.
- Activity link pembayaran terkait order menuju detail order, padahal role finance memiliki finance.view tanpa sales.view. Activity mengumpulkan task modul lain tanpa memeriksa kemampuan membaca tujuannya.
- Kandidat duplikasi: Dashboard Keuangan dan Pembayaran memakai controller/view yang sama; link Laporan Keuangan milik Finance menuju Pembayaran. Dipertahankan, bukan dihapus tanpa keputusan bisnis.
- Verifikasi isi yang lebih ketat kemudian menemukan Laporan Keuangan (`owner.payment-report`) mengembalikan HTTP 200 kosong. File view berukuran 8723 byte berisi **8723 byte NUL**, bukan HTML Blade. Status 200 saja tidak cukup untuk menyatakan halaman berfungsi. View dipulihkan dari versi repository yang valid dan diverifikasi kembali, tanpa perubahan controller/perhitungan pembayaran.
- Batas source: belum ditemukan bukti aturan four-eyes umum yang melarang Super Admin memegang seluruh tahapan. Permission bypass Super Admin adalah perilaku existing; bukan alasan melewati validasi domain. Perubahan pemisahan tugas membutuhkan keputusan bisnis.

## 1. Acuan dan batas kesimpulan

Source dan dokumen yang diperiksa: `Role`, `User`, `Permission`, `YzaSeedLocalStaffCommand`, `RoleMiddleware`, `PermissionMiddleware`, `RoleModuleAccessMiddleware`, `AssignedWorkOrderMiddleware`, `WorkOrderAuthorizationService`, routes, seluruh deklarasi sidebar termasuk cabang staf/Owner, controller activity dan auxiliary, controller sales/finance, service refund, serta tampilan terkait. Acuan perkembangan: Full System Feature Audit 8 Oktober, Full System Audit 31 Oktober, laporan P0 Stage 1 Authorization, Stage 2 Payment Security, Stage 3 QC/PDI, Stage 4 Promo Integrity, Order/Sales Workflow UI Refinement dan Preorder Modal Management.

Proposal tidak ditemukan; ringkasan cakupannya di sini bersumber **hanya dari permintaan pengguna**: dashboard, master kendaraan, penjualan/used car/trade-in, booking, servis/sparepart/car wash, pembayaran/laporan, membership/notifikasi. Admin/Petugas/Member pada cakupan tersebut diuraikan oleh role Laravel yang lebih rinci. Kolom Dasar di bawah bukan kutipan atau pengesahan bagian 5/6 dokumen yang tidak tersedia. Semua fitur existing dipertahankan.

Snapshot operasional dibaca dengan koneksi `yza_automotive_laravel` melalui `SET TRANSACTION READ ONLY`, kemudian ROLLBACK. Tidak login lewat browser operasional, tidak membuka handler bisnis operasional, tidak menjalankan seed/migration/update role. `super-admin-menu-before.json` dan `super-admin-menu-after.json` memperlihatkan role, jumlah user dan permission yang sama. Tidak menyimpan password/token/kunci provider dalam laporan.

## 2. Identitas dan otorisasi aktual

- `admin506` → relasi `roles.nama_role=admin` → `Role::key()=super_admin`; display name Administrator YZA. `superadmin506` → `super_admin` langsung. Keduanya aktif pada snapshot. Ini membuktikan role akun, **tidak membuktikan cookie sesi screenshot**; sesi tersebut tidak diinspeksi.
- `User::hasPermission()` mengembalikan true untuk effective Super Admin. Role legacy admin maupun literal super_admin tidak memerlukan baris grant per permission. Ini kontrak existing, bukan permission baru dari audit ini.
- `auth`, `role:*`, `module.access`, permission route tambahan dan guard domain tetap merupakan otorisasi server. Role non-Super Admin ditolak untuk modul/aksi yang tidak terpetakan. Tidak ada Gate/Policy Laravel terpisah yang menggantikan guard ini.
- Owner memiliki permission view/report, tetapi module.access menolak mutasi bisnis. Pengecualian existing: menandai notifikasi milik sendiri dibaca. Perubahan password sendiri adalah fungsi akun, bukan mutasi transaksi.
- WO: principal/status staf dan assignment ledger aktif diperiksa. Mechanic hanya dapat melihat/mengerjakan WO berpenugasan aktif; tidak memperoleh manage/finalize/QC hanya karena assignment. Controller dan service P0 tetap berlaku.
- PDI: checklist, status pembayaran/order, bukti inspector, waktu dan snapshot kendaraan tetap diperiksa oleh alur existing. Super Admin tidak melewati integritas PDI/handover.
- Refund: `FinancialAdjustmentService::transition` melarang requested_by menyetujui/mengonfirmasi pengajuannya sendiri, termasuk Super Admin. Bukti dana keluar tetap wajib. Tidak mengubah aturan four-eyes ini.

Label topbar sekarang berasal dari effective role: Super Admin, Sales, Service Advisor, Mekanik, Staf Gudang, Finance, Staf Operasional, Quality Control. Nama tampilan tidak diubah; role user tidak diganti.

Saat Super Admin membuka laporan ber-prefix `/owner`, shell laporan eksekutif existing tetap dipakai, tetapi label profil juga menampilkan Super Admin. Pergantian shell bukan pergantian role; label Owner asli tetap Executive Management. Ini koreksi teks terakhir yang diverifikasi ulang lewat HTTP menu tests.

## 3. Matriks permission aktual

Permission efektif terdaftar (nama disingkat untuk tabel): D=dashboard.view; SV/SM=sales.view/manage; CV=customers.view; SeV/SeM=service.view/manage; SA=service.assigned; IV/IM=inventory.view/manage; FV/FVf=finance.view/verify; R=reports.view; StM=staff.manage; A=audit.view; QC=qc.manage.

| Role | Grant aktual | Menu yang berhak diakses (bukan janji seluruhnya tampil) | Read | Create | Update | Approve | Finalize |
|---|---|---|---|---|---|---|---|
| super_admin (termasuk alias admin) | Bypass permission existing; guard domain tetap | 36 menu + Dashboard melalui logo; sales/owner reports yang diizinkan route | Semua modul terdaftar | Ya sesuai modul | Ya sesuai modul | Ya; refund tidak boleh sendiri | Ya, hanya jika semua prasyarat terpenuhi |
| owner | D SV CV SeV IV FV R | Executive dashboard, laporan, monitoring sales/service/inventory/finance/customer | Ya sesuai view/report | Tidak untuk bisnis | Tidak untuk bisnis | Tidak | Tidak |
| sales | D SV SM CV | Dashboard Sales, awards, prospek, test drive, pesanan/penjualan/target; admin sales/customer surface sesuai middleware | Sales/customer | Sales sesuai route | Sales sesuai route | Sales.manage; bukan finance/QC | Tahap sales yang disediakan; bukan QC/keuangan |
| service_advisor | D CV SeV SeM | Booking servis, Work Order; sumber lain sesuai service.view/manage | Service/customer | Service.manage | Service.manage | Customer approval tetap milik customer; kontrol manager existing tetap | Servis melalui manage + QC/approval sah |
| mechanic | D SeV SA IV | Pekerjaan servis, sparepart | WO assigned aktif; inventory view | Catatan/temuan/jasa/part pada WO assigned sesuai guard | Technical work assigned | Bukan approval pelanggan/finance/QC | Tidak |
| warehouse_staff | D IV IM | Sparepart, Stok masuk; inventori kendaraan dapat dibaca/dikelola via inventory permission | Inventory | Inventory.manage | Inventory.manage | Bukan pembayaran/QC | Operasi stok yang disediakan; bukan invoice bisnis |
| finance | D FV FVf R | Pembayaran, Refund/Reversal, laporan/monitoring keuangan, aktivitas pembayaran | Finance/report | Pembayaran/adjustment sesuai handler | Finance sesuai handler | Finance.verify + four-eyes refund | Konfirmasi finansial sesuai status; bukan WO/PDI |
| operational_staff | D SeV | Dashboard/profil; service view melalui URL yang diizinkan | Service.view | Tidak dari grant saat ini | Tidak dari grant saat ini | Tidak | Tidak |
| quality_control | D SeV QC | QC Work Order; PDI/order read dan PDI action melalui pengecualian route QC | Service/QC; order view khusus | Mulai inspeksi QC/PDI yang disediakan | Checklist sesuai guard | Hasil QC/PDI, bukan pembayaran | Hasil inspeksi; bukan finalisasi invoice WO |

Tabel ini merangkum **kemampuan route/domain**, bukan CRUD blanket untuk semua record. Delete/cancel memerlukan write permission dan state/ownership guard terkait; Owner dan role view-only tidak mendapatkannya. Tidak ditemukan permission delete terpisah yang boleh disimpulkan dari role label. Akses data sensitif berupa identitas pelanggan, nilai transaksi, kontak staf dan audit dibatasi modul; super_admin luas, finance finansial, advisor/sales pelanggan sesuai surface, mechanic hanya WO authorized, warehouse inventory.

Role aktual tambahan: `petugas` legacy dipetakan ke operational_staff, tetapi mempunyai pengecualian legacy pada prefix `/petugas`; ini **tidak sama** dengan memberi modern operational_staff hak legacy. Role pelanggan/member berada di portal pelanggan. Terdapat 12 stored roles / 10 effective keys termasuk pelanggan. Seeder command dibaca sebagai bukti, tidak dijalankan.

**Batas penting Sales:** controller `/sales` melakukan scope penugasan, sedangkan sejumlah endpoint `/admin` memakai permission sales.view/manage tingkat modul. Tidak menyatakan semua akses Sales di seluruh aplikasi sudah assigned-only. Menutup surface generic secara blanket akan mengubah kebijakan existing dan memerlukan keputusan bisnis.

## 4. Inventaris lengkap menu Super Admin

36 item di 7 kelompok; tidak ada submenu tersembunyi dalam cabang Super Admin. Dashboard adalah link logo, sehingga 37 entry navigasi bila logo dihitung. Cabang staf/Owner juga diperiksa. `R1`=auth + role group admin/modern staff + module.access; `R2`=auth + role owner/super_admin + reports.view; `R3`=auth akun sendiri. Detail URI, HTTP methods, controller lengkap dan seluruh middleware per route tersimpan di JSON before/after. Semua handler ada: **435 route efektif, 0 controller action hilang**.

Status utama saling eksklusif: A=sesuai; B=sesuai, halaman monitoring/read-only; D=tujuan duplikat yang perlu keputusan. Tidak berarti Super Admin kehilangan hak pada modul lain bila item diberi B.

| Menu | Role Pemilik Proses aktual | Akses Super Admin | Proposal/Dasar implementasi | Route | Permission read / write | Ikon sesudah | Status |
|---|---|---|---|---|---|---|---|
| Pusat Notifikasi | Akun penerima | Baca/tandai milik sendiri | Notifikasi; NotificationStateService | admin.notifikasi.index | R1 D / self-read D | bell | A |
| Aktivitas & Tugas Admin | Super Admin, monitoring finance/Owner | Ringkasan; buka resource authorized | Dashboard/operasional; AdminWorkspaceController | admin.aktivitas.index | R1 R / tidak ada mutasi | list-todo | B |
| Pesanan Mobil | Sales | Verifikasi/reject/lanjut sesuai tahap | Penjualan; MemberVehiclePurchaseController | admin.pesanan-mobil.index | R1 SV / SM; QC pengecualian khusus | clipboard-list | A |
| Penjualan Mobil | Sales | Kelola melalui workflow sales | Penjualan; OperationsPenjualanMobilController | admin.penjualan-mobil.index | R1 SV / SM | cart | A |
| Pre-Order | Sales | Tinjau, cek stok, notify/cancel sah | Penjualan/minat; MemberVehiclePreorderController | admin.preorder-mobil.index | R1 SV / SM | calendar-clock | A |
| Inventori & Stok Mobil | Warehouse/inventory | Kelola katalog unit sesuai status | Master kendaraan; MobilController | admin.mobil.index | R1 IV / IM | warehouse | A |
| Katalog & Showroom | Sales/inventory | Lihat katalog dan availability | Master/showroom; AdminWorkspaceController | admin.katalog-showroom.index | R1 IV; halaman GET | grid | B |
| PDI | QC | Kelola inspeksi via order resmi | Penjualan; ordersWorkflow + PDI integrity | admin.pdi.index | R1 SV view; QC route khusus; write sesuai tahap | clipboard-check | A |
| Jadwal Pengambilan | Sales menurut izin existing | Buka workflow pengambilan | Penjualan/handover; ordersWorkflow | admin.pengambilan.index | R1 SV / SM pada aksi order | calendar | D |
| Serah Terima | Sales menurut izin existing | Konfirmasi hanya setelah syarat sah | Penjualan/ownership; ordersWorkflow | admin.serah-terima.index | R1 SV / SM pada aksi order | key | A |
| Dashboard Keuangan | Finance | Memantau halaman pembayaran | Pembayaran; PaymentManagementController@index | admin.finance-dashboard.index | R1 FV / FVf pada aksi | chart | D |
| Pembayaran Online / Offline | Finance | Tinjau/verifikasi sesuai guard | Pembayaran; PaymentManagementController | admin.payments.index | R1 FV / FVf | card | A |
| Semua Transaksi | Finance | Cari/filter data transaksi | Transaksi; AdminAuxiliaryController | admin.transaksi.index | R1 FV; halaman GET | trade | B |
| Invoice & Kwitansi | Finance | Mode invoice/cetak resource sah | Invoice; transactions + invoice_mode | admin.invoices.index | R1 FV; halaman GET | receipt | B |
| Refund & Reversal | Finance | Request/transisi sah, four-eyes | FinancialAdjustmentController; kebutuhan finance terbaru | admin.financial-adjustments.index | R1 + FV; write FVf dan finance/super_admin | scale | A |
| Booking Mobil | Sales menurut izin existing | Kelola sesuai booking workflow | Booking; OperationsBookingController | admin.booking.index | R1 SV / SM | calendar-clock | A |
| Test Drive | Sales menurut izin existing | Kelola jadwal/hasil sesuai workflow | Booking/test drive; OperationsTestDriveController | admin.test-drive.index | R1 SV / SM | steering | A |
| Servis / Work Order | Service Advisor, mechanic assigned, QC | Pengawasan dan manage sah | Servis; OperationsServisController + WO authorization | admin.servis.index | R1 SeV / SeM; resource/assignment guard | wrench | A |
| Jadwal Bengkel | Service Advisor | Jadwal dan kapasitas bengkel | Servis; ServiceScheduleController | admin.servis.schedule | R1 SeV / SeM | calendar | A |
| Personel Servis | Service Advisor/admin | Profil kapabilitas/jam kerja servis | Servis; ServicePersonnelController | admin.servis.personnel | R1 SeV / SeM | users | A |
| Car Wash | Service/operasional sesuai grant | Confirm/start/finalize sah | Car wash; OperationsBookingCuciController | admin.booking-cuci.index | R1 SeV / SeM | droplets | A |
| Sparepart & Inventori | Warehouse | Stok/katalog sesuai inventory guard | Sparepart; SparepartController | admin.sparepart.index | R1 IV / IM | box | A |
| Trade-In | Sales | Proses trade/invoice sesuai alur | Trade-in; OperationsTradeInController | admin.trade-in.index | R1 SV / SM | repeat | A |
| Pembelian Mobil Bekas | Sales; Finance membayar | Kelola pengajuan/inspeksi yang tersedia | Used car; OperationsPengajuanMobilController | admin.pengajuan-mobil.index | R1 SV / SM; payment FV/FVf terpisah | car | A |
| Data Pelanggan | Admin; sales/advisor membaca | Kelola identitas sesuai handler | Pelanggan; PelangganController | admin.pelanggan.index | R1 CV / StM | users | A |
| Data Member | Admin/membership | Pantau status membership | Membership; MembershipController@index | admin.membership.index | R1 CV; halaman GET | id-card | B |
| My Garage | Sales/handover; admin monitoring | Data ownership terverifikasi | Membership/kendaraan; AdminAuxiliaryController | admin.garage.index | R1 CV; halaman GET | car | B |
| Promo Member | Admin kampanye | Kelola promo, engine tetap berlaku | Promo; PromoMemberController | admin.promo-member.index | R1 R / StM | ticket-percent | A |
| Manajemen Karyawan | Super Admin | Kelola data staf sesuai handler | Administrasi; PetugasController | admin.petugas.index | R1 StM | users | A |
| Role & Permission | Super Admin | Inspeksi matriks; bukan editor grant | RBAC terbaru; AdminAuxiliaryController@roles | admin.roles.index | R1 StM; halaman GET | shield | B |
| Laporan Penjualan | Owner; Super Admin memantau | Lihat/export | Laporan; OwnerDashboardController@salesReport | owner.sales-report | R2 R | chart | B |
| Laporan Keuangan | Owner; Super Admin memantau | Lihat/export | Laporan; OwnerDashboardController@paymentReport | owner.payment-report | R2 R | chart | B |
| Laporan Operasional | Owner/admin/Finance reporting | Lihat/filter/export tersedia | Laporan servis; LaporanServisController | admin.laporan-servis.index | R1 R | chart | B |
| Audit Log | Super Admin | Baca/export log terstruktur | Audit kebutuhan terbaru; AdminAuxiliaryController | admin.audit-log.index | R1 A; tidak ada mutasi | scroll | B |
| Pengaturan Website | Super Admin | Kelola whitelist pengaturan | Administrasi; PengaturanController | admin.pengaturan.index | R1 StM | gear | A |
| Profil Admin | Akun sendiri | Profil/password sendiri | Akun; ProfileController | account.password | R3 self | user | A |

### Duplikasi, kemiripan yang sah, dan fungsi yang belum ada

1. **D: Dashboard Keuangan / Pembayaran**: berbeda URI tetapi controller dan view index sama. Bukan dashboard keuangan khusus. Tidak dihapus/dipisah dalam audit UI ini.
2. **D: PDI / Jadwal Pengambilan**: `ordersWorkflow()` mengisi status `proses` pada keduanya; query index memasukkan lunas/pdi_lulus/siap_diambil. Kedua menu masih menampilkan himpunan yang sama. Serah Terima menggunakan siap_diambil sehingga bukan duplikasi identik. Memisahkan himpunan PDI/pickup membutuhkan definisi tahap/keputusan kebutuhan dan tidak disentuh di sini.
3. **Finance staf** mempunyai dua label Pembayaran/Laporan keuangan menuju admin.payments.index. Ini duplikasi tambahan di luar hitungan 36 Super Admin. Tidak diam-diam memberikan akses laporan Owner karena route Owner membatasi role.
4. **Semua Transaksi / Invoice** memakai method yang sama tetapi `invoice_mode` berbeda; tujuan baca/cetak berbeda, tidak diklasifikasikan D. Inventori/Showroom juga berbeda: kelola unit vs tampilan katalog/availability. Jadwal Bengkel berbeda dari Jadwal Pengambilan. Personel Servis berbeda dari Karyawan: kapasitas/jam kerja vs identitas staf.
5. **Belum didukung pada destination yang diaudit**: editor grant RBAC, pengelolaan points admin khusus, rekonsiliasi/pencairan provider otomatis. Modul role tetap menampilkan data read-only; Points service/member tetap ada; refund offline tersedia dengan validasi, refund online/provider payout tidak diklaim tersedia. Tidak dibuat tombol seolah menjalankan fitur tersebut.
6. Tidak ditemukan menu Super Admin yang salah role semata-mata karena nama operasionalnya; explicit grants existing membolehkan tindakan yang sah. Tidak menganggap seluruh A sebagai tanggung jawab harian utama Super Admin.

## 5. Audit Aktivitas & Tugas Admin

Ini **ringkasan turunan**, bukan task-management engine atau daftar seluruh pekerjaan perusahaan. Cakupan aktual: pesanan member dan pembayaran masuk berstatus menunggu, ditambah notifikasi akun sebagai stream terpisah. Booking servis, Work Order, sparepart, car wash, lead dan pre-order belum menjadi sumber task halaman ini. Tidak mengarang assignment/deadline/perubahan historis.

| Sumber | Resource ID / penugasan nyata | Klasifikasi sesudah koreksi | Tujuan |
|---|---|---|---|
| pesanan_mobil_member, menunggu_verifikasi | id_pesanan; id_sales bila ada | menunggu | admin.pesanan-mobil.show(ID), redirect existing memakai ?detail=ID |
| Pesanan lunas, PDI belum lulus | id_pesanan + relasi pdi | diproses jika sedang_diperiksa; selain itu tindakan | Detail order yang sama |
| pdi_gagal / pdi_lulus / siap_diambil | id_pesanan | tindakan; pdi_lulus berarti jadwal pengambilan perlu disiapkan | Detail order yang sama |
| menunggu_pembayaran / proses / lunas setelah PDI | id_pesanan | diproses | Detail order yang sama |
| diserahkan | id_pesanan | selesai, tercatat sebagai pesanan diserahkan | Detail order yang sama |
| pembayaran masuk menunggu | id_pembayaran; tidak ada assignee task khusus | menunggu | admin.payments.show(ID) selalu, bukan order yang Finance tak berhak buka |
| Notifikasi akun | id_notifikasi + id_user | stream peristiwa dibaca/belum dibaca | Pusat notifikasi; bukan completed task/audit history |

KPI sekarang menghitung satu kategori per source record dari pool dengan rentang tanggal sama; query/type/status menyaring list setelah ringkasan. Pesanan dan pembayaran adalah dua resource berbeda yang dapat sama-sama butuh penanganan; keduanya tidak dipaksakan menjadi satu task. Pesanan lunas/PDI berjalan tidak dihitung dua kali. Selesai berarti pesanan diserahkan, bukan jumlah klik atau jumlah notifikasi dibaca. Daftar mendukung filter Selesai.

Tanggal card berasal dari created_at order/tanggal pembayaran; bukan deadline. Priority Tinggi/Sedang/Normal merupakan aturan presentasi dari tahap, bukan field SLA tersimpan. id_sales adalah ID Petugas bila ada, bukan bukti user assignment pada semua proses; tidak menebak assigned user untuk pembayaran/QC. `resource_type`, `resource_id`, `assigned_staff_id` dan deadline null dicatat eksplisit dalam data presentasi.

Activity hanya mengambil data order bila sales.view atau qc.manage, dan pembayaran bila finance.view. Finance tidak lagi memperoleh task/detail order dari halaman ini. Owner tetap melihat monitoring dan link bertuliskan Lihat workflow; tidak memperoleh tindakan bisnis. Backend tujuan tetap mengotorisasi request asli.

Ikon status: menunggu clock/amber; diproses refresh/biru; tindakan alert/merah; selesai check-circle/hijau. `circle-x` tersedia untuk status batal, tetapi record batal tidak dipalsukan sebagai task aktif. Notifikasi dibaca menggunakan bell/netral, bukan centang hijau selesai. Riwayat aktivitas diberi penjelasan bahwa isinya notifikasi akun; audit resmi tetap menu Audit Log dengan sumber log aplikasi yang diparsing, bukan ledger audit DB universal.

## 6. Koreksi UI, ikon, dan navigasi

- Metadata ikon/label role/active parent ada di `App\Support\AdminNavigation`, tanpa grant atau mutation.
- **33 dari 36 ikon menu Super Admin diganti semantik**. Bell Notifikasi, Cart Penjualan dan Calendar Jadwal Pengambilan yang sudah sesuai dipertahankan. Staff route juga memakai mapping semantik, bukan document generik. Semantik sama sengaja memakai bentuk sama: laporan memakai chart, personel/pelanggan memakai users, jadwal memakai calendar.
- Component SVG lokal existing `order-icon` diperluas; tidak memasang library baru. Seluruhnya stroke 1.8, viewBox 24, sidebar terukur 19×20px. SVG dekoratif aria-hidden/focusable=false; link memiliki label/title. Ikon collapsed tetap ada, tidak ada emoji utama.
- Contoh sebelum→sesudah: Activity calendar→list-todo; Pesanan car→clipboard-list; Pre-order calendar→calendar-clock; Inventori box→warehouse; Showroom layers→grid; PDI document→clipboard-check; Serah Terima trade→key; Pembayaran document→card; Invoice document→receipt; Servis document→wrench; Car Wash document→droplets; Member document→id-card; Promo document→ticket-percent; Roles document→shield; Audit document→scroll; Pengaturan document→gear. Mapping semua item ada pada tabel dan JSON.
- Diff ikon/label lengkap untuk seluruh 36 item: `super-admin-icon-diff.json`. Ikon sebelum diambil dari match label renderer lama, bukan glyph deklaratif item yang sebelumnya diabaikan.
- Active state mengikuti route induk pada create/edit/detail/wizard. Jadwal Bengkel/Personel tidak sekaligus mengaktifkan Work Order. Super Admin link aktif memakai aria-current=page.
- Link collapsed mempunyai title/label; focus-visible merah. Logo dan tombol perluas yang tadinya tumpang tindih ditempatkan vertikal pada collapsed desktop. Internal scroll dipertahankan; semua menu tetap dapat dicapai.
- Label `Rekonsiliasi / Pencairan` dikoreksi menjadi **Refund & Reversal**, sesuai controller/validasi yang ada. `Promo & YZA Points` menjadi **Promo Member** karena destination tidak mengelola points. Label QC staf menjadi **QC Work Order**, karena destination adalah WO, bukan halaman PDI. Fitur/route tidak dihapus.
- Dua link operational_staff untuk Booking Mobil/Test Drive sekarang memerlukan sales.view, sesuai module.access. Role itu saat ini tidak memilikinya, sehingga link tidak lagi ditampilkan sebagai jalan menuju 403. Tidak menaikkan permission, tidak mengubah backend atau menghapus fitur; kepemilikan Booking untuk role ini perlu keputusan bisnis.
- Dropdown Pengaturan hanya tampil bila staff.manage; akun staf lain memperoleh link Profil Akun & Password yang sah.
- **Laporan Keuangan kosong dipulihkan:** `resources/views/owner/payment-report.blade.php` awalnya seluruhnya NUL. Pemeriksaan memastikan tidak ada konten non-NUL sebelum menulis versi Blade valid dari `git show HEAD:<path>` (bukan reset/checkout). Versi tersebut menggunakan variabel summary/gateway/period yang memang disediakan controller sekarang, menampilkan penerimaan/refund/status gateway dan tabel pembayaran serta export existing. Tidak mengubah nominal, query, engine, hak akses atau provider. Test kini memeriksa konten laporan, bukan hanya kode HTTP. Tidak mengklaim rekonstruksi desain premium yang datanya sudah hilang pada file rusak.

**Navigasi yang diperbaiki**, dengan satuan dinyatakan: 2 link operational_staff yang sebelumnya 403; 1 pola dropdown Settings lintas staf; 1 pola link task pembayaran menjaga ID/context + 1 filter sumber task sesuai akses; active-state parent berlaku untuk semua item index. Ini bukan klaim seluruh perbedaan domain diselesaikan dengan navigasi.

## 7. Berkas berubah

- `app/Support/AdminNavigation.php` (baru): mapping ikon, label effective role, active parent.
- `resources/views/components/order-icon.blade.php`: SVG semantik tambahan, existing paths dipertahankan.
- `resources/views/components/admin-sidebar.blade.php`: pakai metadata, title/aria label, active state, label akurat dan visibilitas sesuai grant existing.
- `resources/views/layouts/app.blade.php`: label profil dan dropdown berdasar permission.
- `resources/views/components/owner-shell.blade.php`: label Super Admin tetap akurat ketika membaca laporan eksekutif.
- `resources/views/owner/payment-report.blade.php`: pemulihan view seluruhnya-NUL dari versi repository valid; hanya presentasi read-only, logika pembayaran tidak berubah.
- `app/Http/Controllers/Admin/AdminWorkspaceController.php`: proyeksi tugas/KPI read-only dan destination authorized; tidak mengubah transaksi/WO/QC.
- `resources/views/admin/activity/index.blade.php`: ikon/warna status, penjelasan sumber, filter selesai.
- `public/assets/css/admin-navigation.css`: fokus, status semantik dan spacing collapsed.
- `tests/Feature/SuperAdminMenuAuditTest.php` (baru): route/menu, ikon, matriks 9 role, activity classification/context.
- Laporan ini, collector read-only/JSON, renderer QA CLI, bukti responsive dan screenshots.

Tidak mengedit role/permission DB, middleware RBAC, payment/promo/WO/QC/PDI service, nilai harga/invoice, migration atau seeder. Perubahan working tree lain sudah ada/berlangsung di workspace dan tidak di-reset.

## 8. Pengujian dan bukti

- Baseline sebelum perubahan: **665 tests / 3446 assertions PASS**.
- Test baru `SuperAdminMenuAuditTest`: **5 tests / 507 assertions PASS**, termasuk kasus pdi_lulus yang sebelumnya tidak membentuk task pengambilan, identitas Super Admin pada laporan Owner, serta isi dan export laporan keuangan yang sebelumnya kosong. Semua 36 menu Super Admin menghasilkan 200; setiap menu punya SVG/path/title/aria-label. Bukti tujuan di `storage/logs/super-admin-menu-route-results.json`.
- Visible menu semua staf dan Owner diperiksa melalui HTTP pada DB testing, termasuk refresh request baru. Matriks URL langsung untuk 9 role × 4 modul memeriksa 200/403 sesuai grant, bukan CSS hiding. Pelanggan ditolak; Owner mutasi Pengaturan ditolak. P0 existing tetap menjadi bukti assignment mekanik, payment, QC dan promo.
- Frontend build PASS (`storage/logs/super-admin-audit-build.txt`); PHP lint helper/controller PASS; scoped diff check PASS. Tidak ada JS baru; JS sidebar existing tidak diubah.
- Regresi penuh sesudah koreksi inti: **679 tests / 3981 assertions PASS**, 0 error/failure/skipped, `storage/logs/super-admin-audit-regression.xml`.
- Run terarah P0 + UI sempat mencatat satu snapshot mismatch pada `PromoTransactionIntegrityStage4Test::test_dp_exceeding_discounted_car_total_rolls_back_reservation` (141 tests / 1361 assertions, 1 failure). Test tersebut bersama 5 test UI diuji ulang: **6 tests / 502 assertions PASS**. Tidak mengubah test/engine promo atau melemahkan assertion. Karena hasil ini, regresi penuh terakhir dijalankan kembali; hasil akhir ditambahkan di bawah.

**Regresi penuh terakhir: 679 tests / 3988 assertions PASS, 0 error, 0 failure, 0 skipped**, setelah pemulihan view laporan dan penambahan assertion isi/role laporan (`storage/logs/super-admin-audit-final.xml` dan `.txt`). CSS scoped terakhir pada laporan dipastikan lewat render browser dan pengujian terarah ulang. Tidak mengubah/melemahkan assertion test existing. Jumlah suite bertambah lebih dari 5 terhadap baseline karena workspace juga menerima test lain; angka adalah snapshot aktual, bukan seluruh penambahan diklaim berasal dari audit ini.

### Browser/keyboard/responsive

Browser memakai HTML Blade asli, CSS dan script sidebar asli pada preview statis yang dirender dari database **testing** READ ONLY. Empat status card memakai fixture UI sintetik, bukan pesanan operasional. Fetch, submit dan navigasi link live diblok pada fixture; tujuan route dan otorisasi diuji terpisah lewat Laravel HTTP tests. Preview publik sementara dihapus. Ini bukan login atau perubahan data operasional.

Hasil: collapse tetap 36 link; 0 title hilang; ikon 19×20px. Setelah koreksi, brand bottom=68px dan tombol top=76px, tidak overlap. Enter pada tombol memperluas sidebar. Mobile drawer membuka dan memindahkan fokus, Shift+Tab dari link pertama kembali ke Profil Admin terakhir, scrollTop=1140 dengan last link bottom=811.8 pada tinggi 844px. Escape menutup dan mengembalikan fokus ke tombol navigasi. Menu paling bawah bisa dijangkau tanpa scroll halaman utama.

| Viewport 100% | Overflow horizontal dokumen | Link sidebar tetap ada | Empat ikon status valid |
|---|---|---|---|
| 320×568 | Tidak | 36 | Ya |
| 390×844 | Tidak | 36 | Ya |
| 768×900 | Tidak | 36 | Ya |
| 1024×768 | Tidak | 36 | Ya |
| 1440×900 | Tidak | 36 | Ya |
| 1920×1080 | Tidak | 36 | Ya |

Viewport di-reset setelah uji. Tidak mengklaim uji perangkat sentuh fisik. Bukti: `super-admin-audit-desktop.png`, `super-admin-audit-collapsed.png`, `super-admin-audit-mobile.png`, `super-admin-audit-mobile-menu.png`, `super-admin-audit-responsive.json`. Screenshot dibuat sebelum dua koreksi teks akhir Refund/Promo; layout/ikon/akses yang ditunjukkan tetap sama.

Pemeriksaan tambahan view Laporan Keuangan yang dipulihkan: preview menggunakan controller/data database testing READ ONLY, bukan angka operasional. Browser mengonfirmasi judul, label Super Admin, ringkasan dan tabel kembali tampil. CSS hanya diberi scope `.owner-payment-report` untuk jarak filter, kartu ringkasan, tabel dan responsive, tanpa perhitungan keuangan baru. Pada 390×844 dokumen tidak overflow horizontal; tabel mempunyai scroll sendiri. Bukti desktop `super-admin-audit-financial-restored.png`. Tidak menekan aksi pembayaran atau export di preview. Fixture publik dihapus setelah QA.

## 9. Kesimpulan dan keputusan yang tersisa

| Ukuran audit | Hasil |
|---|---:|
| Menu Super Admin diperiksa | 36 (37 jika link logo Dashboard dihitung) |
| A — sesuai dan punya aksi existing yang sah | 23 |
| B — monitoring/read-only pada destination ini | 11 |
| D — item redundan terhadap destination lain | 2 |
| C — salah role Super Admin yang terbukti | 0 |
| E — route/menu Super Admin tetap broken sesudah koreksi | 0 untuk 36 GET yang diuji |
| Halaman broken yang ditemukan lewat pemeriksaan isi dan dipulihkan | 1 (Laporan Keuangan: HTTP 200 kosong) |
| F — menu utama tanpa fungsi nyata | 0; ada 3 kemampuan belum didukung, terpisah di atas |
| Ikon Super Admin diperbaiki | 33; 3 yang valid dipertahankan |
| Controller action hilang pada route inventory | 0 / 435 |
| Grant permission bertambah | 0 |
| Area read-scope yang dikoreksi | 1 (Activity); 2 link staf dan dropdown juga disesuaikan |

Enam kelompok keputusan/risiko terbuka, **bukan enam menu yang otomatis harus dihapus**:

1. Sediakan/konfirmasi proposal terbaru untuk memvalidasi pemetaan bagian 5/6 secara literal.
2. Putuskan apakah modern operational_staff memang harus mengelola Booking Mobil/Test Drive/serah terima. Grant existing tidak memberi sales.view/manage atau service.manage; audit ini tidak menambahkannya.
3. Putuskan apakah generic admin sales surface perlu assigned-only seperti `/sales`; ini perubahan kebijakan/resource scope, bukan ikon.
4. Tentukan tujuan terpisah Dashboard Keuangan, PDI vs Jadwal Pengambilan, dan label laporan Finance yang redundan; tidak menghapus route bisnis yang ada.
5. Tentukan kebutuhan editor RBAC, admin points dan rekonsiliasi/pencairan provider. Tidak menganggap halaman laporan sebagai bukti backend tersebut tersedia.
6. Activity masih agregasi dua modul, tanpa task engine, deadline/SLA atau riwayat approval lengkap. Bila harus mencakup seluruh operasi perusahaan, perlu kontrak sumber/task ownership tersendiri.

Tidak ditemukan alasan untuk menghapus keseluruhan menu operasional dari Super Admin atau memberinya bypass prasyarat domain tambahan. Monitoring dan pelaksanaan dibedakan berdasarkan endpoint/guard aktual. Lulus pengujian lokal tidak merupakan jaminan seluruh konfigurasi/deployment bebas celah. Tidak commit, push, deploy, reset/clean atau mengubah data operasional.
