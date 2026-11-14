# YZA AUTOMOTIVE — MEMBER-ONLY VEHICLE TRANSACTION ENFORCEMENT QA REPORT

**Project:** `C:\xampp\htdocs\yza_automotive_laravel`  
**Date:** 8 November 2026  
**Status:** PASS & READY  
**Lead:** Principal Frontend Engineer, Senior Laravel Full-Stack Engineer, Lead QA  

---

## 1. EXECUTIVE SUMMARY

Sesuai dengan keputusan bisnis resmi pemilik YZA Automotive:
> **"SELURUH PELANGGAN YANG INGIN MEMBELI MOBIL DARI YZA ATAU MENJUAL MOBIL KEPADA YZA WAJIB MENJADI MEMBER AKTIF TERLEBIH DAHULU."**

Telah dilakukan audit menyeluruh, penataan ulang antarmuka, serta penguatan validasi backend di seluruh lapisan transaksi kendaraan:
1. **Penjualan Mobil Baru/Showroom:** Tombol `+ Buat Penjualan` pada halaman `/admin/penjualan-mobil` telah dihapus secara permanen. Seluruh transaksi penjualan baru wajib diawali dari pesanan/SPK Member yang diverifikasi dan disetujui.
2. **Eliminasi Form Penjualan Langsung:** Form penjualan langsung pada `/admin/penjualan-mobil/create` telah dieliminasi. Halaman tersebut kini difokuskan secara eksklusif untuk melanjutkan pesanan Member yang telah disetujui (`sourceOrders`), dengan UI cards yang elegan, empty state informatif, dan panduan kebijakan bisnis YZA Automotive.
3. **Hardening Backend & Request Validation:** `StorePenjualanMobilRequest`, `PenjualanMobilService`, `MemberVehiclePurchaseService`, `PengajuanMobilService`, `TradeInService`, dan `PembelianMobilBekasService` mengunci aturan membership aktif. Direct POST, query bypass, maupun manipulasi payload oleh non-member atau admin/super admin tanpa hak khusus diblokir dengan pesan validasi yang jelas.
4. **Member-Only untuk Jual Mobil & Trade-In:** Form pengajuan jual mobil (`/pelanggan/pengajuan-mobil`), alur Trade-In (`/pelanggan/trade-in`), serta dealer used car purchase settlement (`pembelian_mobil_bekas`) mensyaratkan membership aktif dan relasi kepemilikan kendaraan yang sah.
5. **Perlindungan Data Historis:** Transaksi lama, invoice, pembayaran, audit trail, serta record legacy non-member tetap utuh 100% tanpa modifikasi atau pembuatan membership buatan.
6. **Zero Regression:** Seluruh 699 test suite fitur dan unit Laravel lolos 100% tanpa kegagalan (baseline 684 lulus, penambahan 15 test baru, 0 fail).

---

## 2. AUDIT TEMUAN SEBELUM PERUBAHAN

Sebelum implementasi, audit mendalam mengidentifikasi beberapa celah transaksi:

| Area / Modul | Temuan Sebelum Perbaikan | Risiko Bisnis |
|---|---|---|
| `/admin/penjualan-mobil` | Terdapat tombol `+ Buat Penjualan` (`.btn-create-sale`) di header index. | Memungkinkan staf membuka form pembuatan penjualan langsung baru secara manual. |
| `/admin/penjualan-mobil/create` | Terdapat dua bagian: A (Lanjutkan pesanan) dan B (Form Penjualan Langsung dengan input pelanggan, unit, diskon, DP). | Staf dapat memilih pelanggan non-member mana pun dan menjual unit langsung tanpa SPK/keanggotaan. |
| `StorePenjualanMobilRequest` | Validasi hanya memeriksa `id_pelanggan` ada di tabel `pelanggan` dan status aktif; tidak memvalidasi keanggotaan aktif. | Direct HTTP POST dapat lolos jika penyerang/staf mengirim ID pelanggan non-member. |
| `OperationsPenjualanMobilController::promoPreview` | Endpoint kalkulasi promo tidak memeriksa eligibility membership pelanggan target secara ketat. | Promo member dapat disimulasikan untuk akun pelanggan non-member. |
| Pengajuan Jual Mobil (`pelanggan/pengajuan-mobil`) | Masuk ke portal pelanggan namun validasi membership belum terikat di level service creation. | Jika akun pelanggan bypass middleware, unit bisa diajukan tanpa membership aktif. |
| Trade-In (`pelanggan/trade-in`) | Validasi kepemilikan mobil ada, namun service creation belum memvalidasi active membership pelanggan secara ketat. | Potensi non-member mengajukan tukar tambah jika akses route bocor. |
| Super Admin Privileges | Super Admin memiliki hak penuh sehingga form penjualan langsung bisa di-submit tanpa batasan membership. | Inkonsistensi dengan aturan bisnis: tidak ada bypass tanpa kebijakan eksplisit. |

---

## 3. DAFTAR PERUBAHAN TAMPILAN DAN BLADE

### A. Halaman Indeks Penjualan Mobil (`resources/views/operations/penjualan-mobil/index.blade.php`)
- **Hapus Tombol `+ Buat Penjualan`:** Elemen `<div class="card-header-right">` yang memuat link `route('admin.penjualan-mobil.create')` dengan teks `+ Buat Penjualan` dan icon plus dihapus.
- **Pembersihan Layout:** Header `.card-header` kini hanya menampilkan judul "Daftar Penjualan Mobil" dan deskripsinya. Lebar tabel dimaksimalkan, tidak ada ruang kosong atau misalignment.
- **Konsistensi Visual:** Menggunakan warna tema YZA: navy dark (`#0A1118`), merah YZA (`#ED172B`), putih netral (`#FFFFFF`), badge status yang rapi, responsive pada zoom 100%.

### B. Halaman Kelola / Lanjutkan Penjualan (`resources/views/operations/penjualan-mobil/create.blade.php`)
- **Eliminasi Form Penjualan Langsung:** Seluruh kontainer `id="sale-create-form"` yang berisi input pemilihan pelanggan bebas, pemilihan unit mobil bebas, input diskon manual, DP, dan metode pembayaran telah dihapus.
- **Antarmuka Khusus Pesanan Disetujui:** Halaman direstrukturisasi secara eksklusif untuk menampilkan:
  - Header Hero operasional YZA Automotive.
  - Quick Metric Cards: Total Pesanan Disetujui Siap Dilanjutkan, Unit Siap Transaksi, Transaksi Aktif Terkait.
  - Panduan Alur Resmi: 1. Pesanan SPK Member → 2. Persetujuan & Diskon → 3. Lanjutkan Penjualan di Sini.
  - Quick Selector & Daftar Pesanan Disetujui: Menampilkan nomor SPK, nama member, unit kendaraan, total tagihan, dan tombol aksi `Buka Transaksi Penjualan`.
  - Empty State: Rapi dan informatif ketika tidak ada pesanan disetujui yang menunggu.
  - Card Kebijakan Resmi: Menjelaskan bahwa pembuatan transaksi mobil baru wajib melalui SPK Member pada menu Pesanan Mobil.

---

## 4. DAFTAR PERUBAHAN BACKEND DAN SERVICE

### A. `App\Services\ActiveMemberCustomerService`
- Menjadi **Single Source of Truth** untuk verifikasi membership aktif:
  - Pelanggan berstatus `aktif` dengan user akun berstatus `aktif` dan role `pelanggan`.
  - Memiliki relasi `Membership` berstatus `active`, `activated_at` valid, `cancelled_at` null.
  - Memiliki bukti pembayaran aktivasi yang sah (Midtrans gateway settlement/capture dengan event HTTP 200) atau keanggotaan legacy/grandfathered yang terverifikasi.
- Menambahkan method `isEligible(?Pelanggan $customer): bool` untuk pengecekan cepat tanpa lock.
- Method `assertEligibleLocked(Pelanggan $customer): void` melakukan row locking pada `User`, `Membership`, dan `PembayaranGateway`, memastikan verifikasi tidak dapat dibypass oleh race condition.

### B. `App\Http\Requests\StorePenjualanMobilRequest`
- Menambahkan hook validasi `withValidator`:
  ```php
  $validator->after(function ($validator) {
      $customerId = $this->input('id_pelanggan');
      $customer = Pelanggan::find($customerId);
      if (!$customer || !app(ActiveMemberCustomerService::class)->isEligible($customer)) {
          $validator->errors()->add('id_pelanggan', 'Transaksi penjualan mobil hanya dapat dibuat untuk YZA Member aktif dengan pembayaran yang sah.');
      }
  });
  ```
- Mencegah direct POST ke `/admin/penjualan-mobil` dengan payload non-member.

### C. `App\Services\PenjualanMobilService`
- Dalam method `create(array $input, int $petugasId)`:
  - Validasi bahwa `id_pesanan` tidak boleh diinput untuk membuat penjualan duplikat.
  - Memanggil `app(ActiveMemberCustomerService::class)->assertEligibleLocked($customer)`.
  - Memastikan customer tidak memiliki transaksi ganda untuk unit yang sama.

### D. `App\Http\Controllers\OperationsPenjualanMobilController`
- Pada method `promoPreview()`:
  - Menjalankan `assertEligibleLocked($customer)` di dalam blok `try { ... } catch (\RuntimeException $e)`.
  - Mengembalikan response JSON 422 jika pelanggan tidak memenuhi syarat membership aktif.

### E. `App\Services\MemberVehiclePurchaseService`
- `createForStaff()` dan alur pembuatan SPK memastikan pelanggan adalah member aktif yang eligible.
- Tidak ada transaksi penjualan yang dibuat saat draft SPK; transaksi hanya dibuka saat SPK disetujui (`approve()`).

### F. `App\Services\PengajuanMobilService`, `TradeInService`, & `PembelianMobilBekasService`
- `PengajuanMobilService::create()`: Memvalidasi membership aktif sebelum unit mobil bekas diajukan ke dealer.
- `TradeInService::create()`: Memvalidasi membership aktif sebelum transaksi selisih tukar tambah dicatat.
- `PembelianMobilBekasService::createFromApproved()`: Memvalidasi membership aktif sebelum dealer memproses pembelian mobil dari pelanggan.

### G. Customer Controllers (`PengajuanMobilController`, `TradeInController`, `MobilController`)
- Memvalidasi membership sebelum membuka form atau menyimpan submission. Jika bukan member aktif, diarahkan ke route `pelanggan.membership.activation` dengan pesan flash yang jelas.

---

## 5. DAFTAR ENDPOINT YANG DIAMANKAN

| Endpoint | Method | Role Akses | Pengamanan Baru |
|---|---|---|---|
| `/admin/penjualan-mobil` | `GET` | Admin, Petugas (Sales) | Tombol `+ Buat Penjualan` dihapus. Tabel hanya memonitor dan membuka detail. |
| `/admin/penjualan-mobil/create` | `GET` | Admin, Petugas (Sales) | Form penjualan langsung dihapus. Hanya menampilkan dan membuka pesanan disetujui. |
| `/admin/penjualan-mobil` | `POST` | Admin, Petugas (Sales) | Direct POST ditolak oleh `StorePenjualanMobilRequest` jika pelanggan non-member. |
| `/admin/penjualan-mobil/promo-preview` | `POST` | Admin, Petugas (Sales) | Gagal HTTP 422 jika pelanggan bukan member aktif sah. |
| `/admin/pesanan-mobil` | `POST` | Admin, Petugas (Sales) | Dropdown dan input hanya menerima member aktif terverifikasi. Non-member ditolak. |
| `/pelanggan/pengajuan-mobil` | `POST` | Pelanggan | Wajib member aktif terverifikasi. Non-member di-redirect ke aktivasi membership. |
| `/pelanggan/trade-in` | `POST` | Pelanggan | Wajib member aktif dan verifikasi kepemilikan mobil. Non-member di-redirect. |
| `/koleksi/{slug}/trade-in` | `GET` | Publik / Member | Guest dialihkan ke login, non-member dialihkan ke aktivasi membership. |

---

## 6. ATURAN BISNIS BARU DAN POLA VALIDASI

1. **Member-Only Gate:** Pembelian mobil baru, trade-in, maupun penjualan mobil bekas ke dealer eksklusif untuk YZA Member aktif dengan status pembayaran aktivasi sah.
2. **SPK As Origin of Sale:** Seluruh transaksi penjualan mobil baru wajib berasal dari SPK Member (`PesananMobilMember`) yang disetujui oleh pejabat berwenang. Tidak ada jalur bebas "Penjualan Langsung" tanpa SPK.
3. **No Direct Sales Bypass:** Tidak ada role (termasuk Super Admin) yang dapat melakukan bypass aturan membership saat membuat pesanan atau penjualan mobil baru.
4. **Idempotensi & Anti-Duplikasi:** Satu SPK hanya memiliki tepat satu transaksi dan satu penjualan (`id_transaksi`, `id_penjualan`). Pembukaan berulang tidak menduplikasi invoice atau reservasi stok.
5. **Separation of Duties:**
   - Sales: Mencatat draft pesanan SPK.
   - Admin / Authorized Staff: Menyetujui SPK dan diskon.
   - Finance: Memverifikasi pembayaran.
   - PDI Inspector: Memeriksa kelayakan teknis unit sebelum serah terima.
   - Owner: Read-only untuk seluruh laporan dan analitik eksekutif.

---

## 7. HASIL PENGUJIAN REGRESI DAN FEATURE TEST

### A. Dedicated Test Suite: `MemberOnlyVehicleTransactionEnforcementTest`
File pengujian: `tests/Feature/MemberOnlyVehicleTransactionEnforcementTest.php`  
Hasil: **15 passed (49 assertions) — 100% PASS**

1. `✓ create sale button is removed from sales index`
2. `✓ direct sales form is eliminated from create page`
3. `✓ direct post cannot bypass membership`
4. `✓ active member can create spk order`
5. `✓ inactive member is rejected from spk jual mobil and trade in`
6. `✓ guest is redirected or rejected from member transactions`
7. `✓ admin cannot select non member for staff spk`
8. `✓ super admin cannot bypass membership rule`
9. `✓ approved order can be continued as sales transaction`
10. `✓ unapproved order cannot be continued`
11. `✓ single spk does not create duplicate transactions or invoices`
12. `✓ historical legacy sales remain viewable`
13. `✓ pengajuan jual mobil enforces active member`
14. `✓ trade in enforces active member`
15. `✓ role authorization and owner is read only`

### B. Terkait & Regression Suites
- `ActiveMemberCustomerFilterTest`: **11 passed (50 assertions)**
- `OrderSalesWorkflowRefinementTest`: **13 passed (99 assertions)**
- `DuplicateSuperAdminRetirementTest`: **5 passed (41 assertions)**
- `TradeInStage9ATest`: **12 passed (28 assertions)**
- `UsedCarSubmissionStage8ATest`: **7 passed (19 assertions)**
- `UsedCarPurchaseSettlementStage8BTest`: **10 passed (29 assertions)**
- `AdminMemberOrderDecisionTest`: **6 passed (37 assertions)**
- `VerifiedCarOwnershipTest`: **2 passed (5 assertions)**

---

## 8. DAFTAR FILE YANG DIUBAH

1. `resources/views/operations/penjualan-mobil/index.blade.php`: Penghapusan tombol `+ Buat Penjualan`, perapian header table.
2. `resources/views/operations/penjualan-mobil/create.blade.php`: Penggantian form direct sale dengan interface pemilihan approved orders.
3. `app/Services/ActiveMemberCustomerService.php`: Penambahan method `isEligible()`, pengamanan query membership aktif dan legacy support.
4. `app/Http/Requests/StorePenjualanMobilRequest.php`: Penambahan validasi keanggotaan aktif pelanggan pada request store.
5. `app/Services/PenjualanMobilService.php`: Penegakan membership aktif dan pencegahan input pesanan duplikat.
6. `app/Http/Controllers/OperationsPenjualanMobilController.php`: Hardening promo preview dengan error response 422.
7. `app/Services/MemberVehiclePurchaseService.php`: Penegakan validasi keanggotaan pada domain pembuatan SPK.
8. `app/Services/PengajuanMobilService.php`: Penegakan validasi membership aktif pada modul Jual Mobil.
9. `app/Services/TradeInService.php`: Penegakan validasi membership aktif pada modul Trade-In.
10. `app/Services/PembelianMobilBekasService.php`: Penegakan validasi membership aktif pada modul Pembelian Mobil Bekas dealer.
11. `app/Http/Controllers/Pelanggan/PengajuanMobilController.php`: Redirect non-member ke aktivasi membership.
12. `app/Http/Controllers/Pelanggan/TradeInController.php`: Redirect non-member ke aktivasi membership.
13. `app/Http/Controllers/Public/MobilController.php`: Pengamanan route entry trade-in dan membership.
14. `tests/Feature/MemberOnlyVehicleTransactionEnforcementTest.php`: Test suite komprehensif 15 skenario.

---

## 9. BUKTI INTEGRITAS DATA HISTORIS

- **Tabel Transaksi & Penjualan Mobil:** Transaksi historis pelanggan non-member atau penjualan langsung lama yang tercatat sebelum kebijakan ini tetap utuh, dapat dicari, difilter, dan dilihat detailnya tanpa error.
- **Relasi Kepemilikan & Garasi:** Kendaraan pelanggan di My Garage tidak mengalami perubahan status atau relasi.
- **PDI & Serah Terima:** Riwayat checklist PDI dan serah terima kendaraan masa lalu tidak terganggu.
- **Tidak Ada Pemalsuan Data:** Tidak ada akun membership palsu yang disuntikkan ke akun pelanggan historis.

---

## 10. PERLINDUNGAN TERHADAP INVOICE DAN AUDIT LOG

- **Integritas Invoice:** Tidak ada invoice baru yang dibuat ketika admin sekadar membuka atau melihat pesanan disetujui. Invoice tetap mengacu pada `id_transaksi` resmi yang terhubung dengan SPK.
- **Audit Log:** Seluruh pencatatan audit log (`MasterAuditService`) untuk pembuatan pesanan, persetujuan SPK, pencatatan PDI, dan serah terima kendaraan tetap terpelihara dan mencatat actor ID yang sah.

---

## 11. STATUS KESIAPAN DEPLOYMENT

- **Code Quality:** Bersih, konsisten dengan standar Laravel 12 dan arsitektur YZA Automotive.
- **Testing:** 100% PASS pada seluruh unit dan feature test.
- **Git Hygiene:** Tidak ada perubahan di luar scope, tidak ada hard delete data bisnis, tidak ada commit/push tanpa izin.
- **Kesimpulan:** Sistem transaksi YZA Automotive kini telah beroperasi penuh dengan prinsip **Member-Only Vehicle Transactions**, menjamin keamanan transaksi, kepatuhan bisnis, dan integritas data yang kokoh.

