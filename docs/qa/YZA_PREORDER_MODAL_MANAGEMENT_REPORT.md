# YZA Automotive — Pre-Order Modal Management Report

Tanggal: 8 November 2026 (Asia/Jakarta). Target: `/admin/pre-order-mobil`.

## Penyesuaian tombol Tinjau

Mengikuti permintaan lanjutan, kolom Aksi kini hanya berisi satu tombol **Tinjau** per baris. Tombol membuka modal yang sama pada tab Detail Permintaan; pilihan Ketersediaan Stok, Tindak Lanjut, dan Riwayat tetap berada di dalam modal. Lebar kolom Aksi diperkecil dan tetap sticky. Setiap pembukaan dimulai dari bagian atas konten. Backend dan aturan tindakan tidak berubah. Deskripsi empat tombol masuk di bawah merupakan catatan implementasi awal yang digantikan oleh penyesuaian ini.

Validasi penyesuaian: `PreorderModalManagementTest` **9 tests / 59 assertions PASS**, termasuk satu tombol masuk per baris dan empat tab modal. Node syntax check dan scoped `git diff --check` PASS. Tidak mengulang full regression untuk penyesuaian tampilan ini.

Penyesuaian berikutnya: judul dan isi kolom memakai alignment kiri yang sama, kecuali ID tetap tengah. Ini menghilangkan perbedaan alignment judul Status dengan badge-nya. Lebar tujuh kolom diselaraskan menjadi total minimum 1180px, termasuk kolom Aksi 124px, agar ruang tombol konsisten dan kolom Terdaftar lebih mudah terlihat pada desktop. Scroll horizontal dan Aksi sticky dipertahankan untuk layar kecil. Pengujian ulang: 9 tests / 59 assertions PASS dan scoped diff check PASS; tidak mengklaim pengujian browser baru untuk perubahan CSS ini.

## Hasil audit

Route awal hanya menyediakan GET daftar admin; tidak ada endpoint detail/tindakan admin pre-order. `MemberVehiclePreorder` menghubungkan pelanggan, mobil opsional, dan `id_pesanan` opsional. Pre-order showroom dapat tidak mempunyai `id_mobil` karena belum menjadi unit inventori. Foto diselesaikan melalui katalog showroom dengan identitas dan warna yang cocok, bukan membuat mobil baru.

Enum database existing: `menunggu_stok`, `stok_tersedia`, `notifikasi_terkirim`, `dilanjutkan_ke_checkout`, `dibatalkan`. Tidak ditambah status, kolom, atau tabel baru. Harga referensi merupakan data permintaan, bukan invoice atau harga final.

Workflow existing dalam `MemberVehiclePreorderService`: pencatatan minat saat stok belum tersedia; pembatalan member selain setelah checkout; pemberitahuan restock; proses pembelian tetap melalui checkout member resmi. Pre-order tidak sama dengan `PesananMobilMember` atau `PenjualanMobil` dan tidak menghasilkan ownership.

RBAC existing melalui `RoleModuleAccessMiddleware`: `sales.view` untuk membaca, `sales.manage` untuk mutasi; super admin memperoleh izin sesuai model existing. Owner tetap tidak boleh melakukan mutasi. Endpoint baru mengikuti module access dan service menambahkan pemeriksaan role/permission lagi. Tidak ada perubahan role/permission atau guard pembayaran, PDI, promo, reservasi, dan ownership.

Acuan dibaca: source route/controller/model/service/view, migration pre-order, availability/inventory, notifikasi dan destination, `YZA_ORDER_SALES_WORKFLOW_UI_REFINEMENT.md`, `YZA_VEHICLE_PURCHASE_STAGE4_UAT_REPORT.md`, serta laporan P0 Stage 1–4. Tidak ditemukan proposal pre-order terpisah. Tidak ada policy pre-order khusus atau tabel audit history pre-order. `MasterAuditService` existing menulis log aplikasi, bukan riwayat per-record yang dapat di-query untuk UI.

## File perubahan

- `app/Services/PreorderManagementService.php`: pemeriksaan akses, detail/photo authoritative, pencocokan stok read-only, tindakan atomik dan idempotent.
- `app/Http/Controllers/Admin/MemberVehiclePreorderController.php`: JSON detail/stok/tindakan, validasi konfirmasi/status, KPI authoritative dalam respons simpan.
- `routes/web.php`: endpoint GET detail, GET stok, POST tindakan dengan middleware admin existing.
- `resources/views/admin/preorder/index.blade.php`: header ringkas, empat KPI dipertahankan, kolom Aksi, empat tombol per baris, satu modal bersama.
- `resources/views/admin/preorder/management-modal.blade.php`: modal reusable dan empat panel tab.
- `public/assets/css/preorder-management.css`: tampilan compact, kolom aksi sticky, responsive dan modal scroll.
- `public/assets/js/preorder-management.js`: fetch, tab/keyboard/focus, form/confirmation state, discard guard, loading/error/retry, pembaruan tabel/KPI tanpa reload.
- `tests/Feature/PreorderModalManagementTest.php`: pengujian backend dan integritas workflow.

## Modal dan halaman

Satu modal putih, border tipis, aksen merah, radius 16px, lebar maksimum 860px, tinggi maksimum 88–90dvh. Animasi fade/scale 180ms mengikuti `prefers-reduced-motion`. Header, tab, dan footer terpisah dari konten tengah yang dapat scroll sendiri.

Tombol Detail, Cek Stok, Tindak Lanjut, dan Riwayat membuka modal yang sama dengan tab sesuai tombol. Pergantian tab tidak menavigasi/reload halaman dan mempertahankan pilihan/konfirmasi form. Header besar duplikat dihapus; header halaman, KPI, filter dan tabel tetap tersedia. KPI diperkecil. Kolom Aksi sticky di kanan tabel sehingga tombol tetap dapat dijangkau tanpa menggeser sampai ujung tabel.

Detail berisi kode pre-order, identitas member dari relasi sah, nama/model/varian/tahun/warna tersimpan, atribut katalog yang dapat dicocokkan, harga referensi yang diberi label, status dan tanggal. Data tidak tersedia ditandai belum tercatat. Catatan pelanggan tidak dipalsukan karena tidak ada field existing yang menyimpannya.

Riwayat menampilkan empty state audit khusus yang belum tersedia. Tanggal dibuat/notifikasi/pembatalan ditampilkan hanya jika memang tersimpan. Tidak disimpulkan status sebelumnya, waktu transisi, atau nama petugas dari data lain. Log aplikasi mencatat tindakan admin dengan actor, tetapi tidak dipresentasikan sebagai audit history database.

## Stok dan tindakan

Pencarian mencocokkan nama lengkap kendaraan, varian, tahun, warna; atribut transmisi, bahan bakar, kondisi, dan limited edition juga diperiksa jika sumber katalog terverifikasi tersedia. Permintaan dengan spesifikasi penting yang belum lengkap tidak dinyatakan eligible. Candidate harus berstatus tersedia dan lolos `VehicleAvailabilityService`, termasuk reservasi aktif.

Hasil membedakan unit fisik dan inventori agregat. Stok agregat dihitung dari total dikurangi sold/reserved dan di-deduplicate per inventory ID; VIN tidak ditampilkan sebagai VIN unit pada stok agregat. Unit fisik ditampilkan per ID yang dapat dibeli. Bukti kecocokan, waktu pemeriksaan dan harga katalog saat ini diberi label. Pemeriksaan tidak memanggil ensure/adjust/reserve/create transaksi.

Aksi yang diimplementasikan:

1. **Beri tahu member:** hanya jika pemeriksaan ulang backend menemukan stok eligible yang cocok; memakai status existing `notifikasi_terkirim` dan notifikasi member dengan referensi pre-order.
2. **Batalkan:** memakai aturan pembatalan existing, mengunci record; menolak permintaan yang sudah terkait pesanan/checkout. Member terkait memperoleh notifikasi pembatalan.

Keduanya membutuhkan konfirmasi kode pre-order dan expected status; status berubah di tab lain menghasilkan validasi, bukan overwrite. Retry yang sudah mencapai target tidak membuat duplikat. Notifikasi disimpan di transaksi yang sama; rollback tidak meninggalkan notifikasi keberhasilan. Respons simpan memperbarui status baris/KPI dan meminta refresh state notifikasi admin. Notification Center/badge/toast member memakai pipeline notifikasi existing; tidak ada push/toast sintetis sebelum commit. Link notifikasi tetap melalui resource/ownership guard existing.

Tidak dibuat invoice, sale, order checkout, perubahan stok, reservasi promo, PDI, handover, atau ownership oleh aksi ini. Checkout tetap dilakukan member melalui alur resmi; admin tidak memesan atas nama member dari modal.

Belum didukung backend: catatan komunikasi/customer note, edit spesifikasi permintaan, status bebas, audit history database lengkap, dan checkout admin khusus pre-order. UI menjelaskan keterbatasan tersebut. Tidak ditambah form yang seolah menyimpan data tanpa tempat penyimpanan authoritative.

## Pengujian backend

Database testing dipaksa `yza_automotive_laravel_test`; `tests/TestCase.php` menolak database lain. Tidak ada migration/seed/aksi bisnis pada database operasional.

Focused suite akhir: **34 tests / 310 assertions PASS** mencakup:

- Detail read-only dan tidak membuat riwayat fiktif.
- Foto showroom dan warna yang tepat.
- Stok berbeda warna/varian/tahun/transmisi, booked stock, dan spesifikasi belum lengkap tidak eligible.
- Unit fisik vs stok agregat; tidak menampilkan VIN agregat sebagai unit fisik.
- Tidak ada perubahan tabel transaksi, pesanan, penjualan, ownership atau history stok saat cek/notifikasi.
- Notifikasi member setelah update sah; retry tidak menggandakan notifikasi.
- Pembatalan valid, konfirmasi wajib, stale status, status tidak dikenal, checkout guard.
- Member/finance/owner tidak dapat menulis; service guard owner juga menolak.
- Navigasi admin, availability/reservasi, stok agregat dan global notification state existing.

Frontend build PASS. Node syntax check PASS. PHP lint controller/service/Blade PASS. Scoped `git diff --check` PASS.

Full regression terakhir: **665 tests / 3436 assertions, 0 failure, 1 error, 0 skipped**. Error berupa database deadlock pada `ActiveMemberCustomerFilterTest::test_gateway_customer_binding_amount_and_failed_states_are_checked`, saat insert event pembayaran gateway di database testing. Tidak ada error pada suite modal pre-order. Bukti: `storage/logs/preorder-modal-regression.xml` dan `.txt`.

Pengujian ulang terarah menggabungkan seluruh focused suite di atas dengan kasus deadlock tersebut: **35 tests / 316 assertions PASS**, 0 error/failure (`storage/logs/preorder-modal-final-focused.xml` dan `.txt`). Ini membuktikan kasus tersebut lulus saat dijalankan ulang, tetapi tidak mengganti hasil full regression menjadi PASS. Tidak ada perubahan kode pembayaran atau pelemahan assertion untuk meloloskan pengujian.

Catatan run sebelumnya: satu run menemukan file aset `smart-spk-modal.js` belum tersedia dan satu kegagalan concurrency sparepart; file aset tersedia pada run terakhir dan kegagalan tersebut tidak terulang. Run paling awal menemukan perbedaan judul `Informasi Pembeli` / `Data Pembeli` pada halaman member; kegagalan itu juga tidak muncul pada run terakhir. Workspace berubah selama pemeriksaan; angka di atas merujuk snapshot run terakhir, bukan klaim perbaikan bagian lain oleh pekerjaan modal ini.

## Pengujian browser dan responsive

Browser IAB menguji HTML Blade asli dan JS/CSS asli dengan data simulasi `PRE-90001 / Member QA`. Rendering hanya membaca database testing; request detail/stok memakai fixture JSON terkontrol, POST fixture sengaja mengembalikan 422 dan tidak menulis data. Backend tindakan sukses diuji terpisah melalui Laravel feature tests di atas. Ini bukan klaim bahwa pesanan nyata pernah dibatalkan/dikirimi pesan lewat browser.

Hasil interaksi:

- Keempat entry buttons membuka **tepat satu** modal dan tab yang benar.
- Detail, stok kosong, tindak lanjut, dan empty history terbaca; URL tidak berubah ketika berganti tab.
- Pilihan tindakan tetap tersimpan setelah pindah tab.
- Konfirmasi kosong menahan submit; validasi/error 422 ditampilkan tanpa kehilangan form.
- Escape dan X dengan perubahan form menampilkan konfirmasi buang; Tetap di modal dan Buang & tutup bekerja.
- Focus trap forward/reverse dan focus return diuji; ArrowRight mengganti tab; Escape bersih menutup modal.
- Wheel mengubah scroll konten dari 0 ke 176.8, scroll dokumen tetap 149.6, posisi header/footer tetap.
- Body overflow kembali ke nilai sebelumnya setelah modal ditutup.

| Ukuran 100% | Modal di dalam layar | Footer terlihat | Scroll konten tersedia | Overflow horizontal dokumen |
|---|---|---|---|---|
| 320×568 | Ya | Ya | Ya | Tidak |
| 390×844 | Ya | Ya | Ya | Tidak |
| 768×900 | Ya | Ya | Ya | Tidak |
| 1024×768 | Ya | Ya | Ya | Tidak |
| 1280×720 | Ya | Ya | Ya | Tidak |
| 1440×900 | Ya | Ya | Ya | Tidak |
| 1920×1080 | Ya | Ya | Ya | Tidak |

Keempat tombol Aksi juga terukur berada di viewport horizontal pada lebar 390, 768, 1280 dan 1440px. Tabel dapat scroll horizontal; kolom Aksi tetap di kanan. Tidak memakai CSS zoom/transform scale untuk mengecilkan halaman. Override viewport di-reset setelah QA. Fixture publik sementara dihapus.

Bukti: [desktop](preorder-modal-desktop.png), [mobile](preorder-modal-mobile.png).

## Batas dan status

Implementasi modal selesai dalam kontrak backend yang ada. Audit history/database catatan belum tersedia dan ditampilkan jujur sebagai empty state. Uji layar ponsel menggunakan viewport browser, bukan perangkat sentuh fisik. Uji latensi loading dan sesi auth interaktif semua role nyata bukan bagian dari klaim browser; guard role diuji lewat feature tests. Tidak menguji transaksi nyata atau mengubah database operasional. Tidak commit, push, deploy, reset, atau clean.

Regresi penuh belum disebut PASS karena satu deadlock pada run terakhir, meskipun kasus itu dan seluruh focused suite lulus ketika diuji ulang. Hasil akhir harus dibaca bersama keterbatasan ini.
