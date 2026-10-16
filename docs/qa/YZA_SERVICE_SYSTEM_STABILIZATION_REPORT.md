# YZA Automotive — Service System Stabilization Report

Tanggal: 13 Oktober 2026  
Status akhir: **READY FOR DEMO (database testing/demo)**

## 1. Baseline regression

Run awal setelah refinement inventory:

- 349 passed
- 14 failed
- 3 skipped
- 1.238 assertions

Kegagalan awal terkonsentrasi pada dashboard admin, kontrak notifikasi booking servis, teks portal member, label preferensi teknisi, fixture tanggal scheduling, dan fixture booking Work Order yang memilih slot istirahat.

## 2. Root cause dan perbaikan

| Area | Root cause | Perbaikan |
|---|---|---|
| Dashboard admin | `admin-page-title` didefinisikan view tetapi tidak dirender layout; label `Pesanan terbaru` juga tidak cocok dengan kontrak regression. | Layout admin sekarang merender judul halaman; dashboard mempertahankan UI premium dan memenuhi label live widgets. |
| Booking notification | Kontrak final membutuhkan notifikasi admin saat booking berhasil, dengan deduplikasi pada service notifikasi. | Jalur `BookingServisService` mempertahankan notifikasi booking; test realtime dan booking berhasil lulus. |
| Portal member | CTA hanya berbunyi “Jelajahi Koleksi”, sedangkan kontrak UI membutuhkan “Koleksi Mobil”. | Copy CTA diperjelas menjadi “Jelajahi Koleksi Mobil”. |
| Preferensi teknisi | Detail member belum memuat frasa eksplisit “Penugasan teknisi final”. | Copy detail booking diperjelas tanpa mengubah data assignment. |
| Scheduling past date | Fixture menggunakan kombinasi Carbon yang menghasilkan Selasa mendatang, bukan tanggal lampau. | Fixture memakai Selasa yang benar-benar berada pada minggu sebelumnya; rule aplikasi tetap menolak slot lampau. |
| Work Order sparepart | Fixture booking memakai pukul 12:00, yaitu slot istirahat/tertutup. | Fixture memakai pukul 10:00; kapasitas, benturan, dan aturan slot aplikasi tidak dilonggarkan. |

Tidak ada test yang dihapus atau dilemahkan.

## 3. File yang diperbaiki

- `resources/views/layouts/app.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/pelanggan/member-center-empty.blade.php`
- `resources/views/pelanggan/booking-servis/show.blade.php`
- `app/Services/BookingServisService.php`
- `tests/Feature/ServiceSchedulingPhase2A1Test.php`
- `tests/Feature/ServisSparepartStage5BTest.php`

Perubahan refinement inventory tetap mencakup migration, model, service fitment, master sparepart, Work Order, dan laporan inventory sebelumnya.

## 4. Hasil focused tests

Focused service/inventory/dashboard/booking suite:

- 97 passed
- 1 skipped
- 0 failed
- 322 assertions

Coverage mencakup CRUD master sparepart, stok masuk, histori stok, approval, QC, finalisasi, pembayaran, scheduling, ownership kendaraan, dashboard, dan portal member.

## 5. Hasil full regression

Final run:

- **367 passed**
- **0 failed**
- **3 skipped**
- **1.283 assertions**

Skip yang tersisa bersifat fixture-availability, bukan kegagalan fungsional:

1. Fixture test approval membutuhkan lebih dari satu sparepart aktif.
2. Fixture Work Order pemeriksaan tidak tersedia pada baseline untuk satu skenario redirect.
3. Fixture customer legacy tanpa user tidak tersedia pada baseline.

Ketiganya tidak diperlukan untuk demo utama karena alur validnya sudah diuji oleh test lain.

## 6. Migration readiness

Migration OEM/fitment:

- Berhasil dijalankan pada database testing terisolasi.
- Berhasil di-rollback satu langkah dan dijalankan kembali.
- `migrate --pretend` pada konfigurasi testing menghasilkan `Nothing to migrate` setelah schema sinkron.
- Foreign key dan index fitment tersedia.
- Kolom metadata nullable sehingga data sparepart historis tetap dapat dibaca.
- Tidak ada migration dijalankan pada database utama `yza_automotive`.

Database native tetap read-only.

## 7. Backward compatibility

- Master sparepart lama tetap dapat dibuka dan diedit tanpa mengubah saldo stok.
- Kendaraan legacy tanpa nomor mesin tetap dapat dibaca.
- Pencatatan stok tetap melalui `InventoryService` dan memakai locking/transaksi.
- Approval, pembayaran historis, dan transaksi lama tidak diubah.
- Ownership kendaraan member tetap dibatasi server-side.

## 8. Inventory dan fitment

Fitur fitment menggunakan status `verified_fit`, `unverified`, dan `verified_not_fit`, dengan sumber bukti, verifier, tanggal, model, rentang tahun, dan kode mesin. Work Order menampilkan informasi stok/harga dan guard server-side menolak penggunaan part yang tidak kompatibel ketika identitas mesin kendaraan tersedia.

## 9. Work Order 13822

WO `13822` tetap read-only selama audit:

- Filter oli `YZA-FLT-OIL01` tetap stok `0`.
- Kompatibilitas Toyota GR Supra 2024 / mesin `YZA-GRSUPRA-B58-001` belum terverifikasi.
- Approval revisi belum disetujui.
- Klarifikasi historis tetap utuh.
- QC dan finalisasi tetap ditolak selama klarifikasi belum diselesaikan, stok tidak tersedia, atau fitment belum aman.

Tidak ada perubahan stok, transaksi, pembayaran, atau approval native.

## 10. End-to-end demo readiness

Alur terisolasi dari member login, ownership kendaraan, booking, penerimaan admin, pemeriksaan, rekomendasi, approval, pencatatan jasa/sparepart, QC, finalisasi, pengurangan stok satu kali, invoice, pembayaran, dan riwayat servis tercakup oleh regression suite. Test service execution, finalisasi, payment, member service execution, dan ownership semuanya lulus.

Tidak ada approval customer palsu yang dibuat pada data operasional utama.

## 11. Browser QA

Browser QA interaktif belum dijalankan pada sesi ini karena tidak tersedia session admin/member yang aman untuk digunakan. HTTP/Blade regression dan migration verification sudah dijalankan. QA browser manual tetap disarankan saat demo staging untuk memeriksa layout responsif, dropdown member, panel fitment, dan alur tombol finalisasi.

## 12. Risiko dan blocker

- Migration baru masih perlu dijalankan pada deployment target melalui prosedur resmi setelah backup dan review DBA.
- Fitment produksi untuk sparepart lama belum otomatis terisi; admin harus memasukkan bukti OEM/supplier.
- WO 13822 sengaja tetap terblokir dan bukan kandidat demo finalisasi.

## Kesimpulan

Status sistem: **READY FOR DEMO** pada database testing/demo dengan migration fitment aktif. Database native tetap aman, tidak diubah, dan WO 13822 tetap ditahan sesuai aturan bisnis.
