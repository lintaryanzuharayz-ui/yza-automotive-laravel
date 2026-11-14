# YZA Work Order Workflow Refinement

## Status

**PARTIAL — hotfix implemented and automated HTML validation complete; browser visual QA remains blocked in this session.**

## Temuan dan akar masalah

1. Ringkasan Work Order aktif sebelumnya membaca `total_biaya`, sementara jasa dan sparepart draft belum direkonsiliasi ke nilai tersebut. Akibatnya panel dapat menampilkan `Rp0` walaupun item sudah ada.
2. Panel “Tindakan Berikutnya” hanya memeriksa status approval, sehingga pada halaman Pengerjaan masih menampilkan aksi kembali ke Pengerjaan.
3. Rincian approval menampilkan snapshot item tanpa memuat relasi sparepart canonical. Ini membuat judul pekerjaan tambahan dan nama sparepart terlihat seolah-olah selalu merupakan hal yang sama.
4. Panel kanan masih memiliki conditional Blade lama yang selalu mengarah ke Pengerjaan ketika approval selesai. JavaScript baru hanya menimpa DOM setelah halaman dimuat, sehingga HTML hasil render server masih dapat menampilkan aksi yang salah.

## Implementasi

- Menambahkan `WorkOrderEstimateService` sebagai sumber perhitungan server-side untuk jasa, suku cadang, biaya lain, dan total estimasi.
- Work Order yang belum difinalisasi menampilkan `Estimasi Total`.
- Work Order yang sudah memiliki transaksi menampilkan total final transaksi.
- Halaman Pengerjaan menampilkan hasil pemeriksaan, riwayat pekerjaan tambahan, status persetujuan, item jasa, dan item suku cadang.
- Item approval sparepart menggunakan relasi sparepart canonical bila tersedia, tanpa mengubah snapshot historis.
- Panel Tindakan Berikutnya mengikuti tahap aktual: Persetujuan, Pengerjaan, Pemeriksaan Mutu, atau Finalisasi.
- `WorkOrderStageService::nextAction()` sekarang menjadi sumber kebenaran server-side untuk tahap aktif, tujuan aksi berikutnya, deskripsi prasyarat, dan label CTA.
- Pada tahap Pengerjaan, render Blade langsung menampilkan deskripsi konteks pengerjaan dan CTA Pemeriksaan Mutu; tidak lagi mengandalkan penimpaan JavaScript.
- Ringkasan server-side langsung menampilkan Estimasi Jasa, Estimasi Suku Cadang, Biaya Lain, dan Estimasi Total sebelum finalisasi. JavaScript tidak lagi menggandakan atau mengubah panel tersebut.
- CTA duplikat langsung di bawah tabel Pengerjaan disembunyikan agar hanya ada satu aksi utama di panel kanan.
- Guard akses tahap dan service finalisasi existing tetap dipakai.
- Draft sparepart tetap tidak mengurangi stok; konsumsi stok tetap dilakukan oleh `InventoryService` saat finalisasi.

## Persetujuan dan item pekerjaan

Pekerjaan dasar, pekerjaan tambahan, dan sparepart approval ditampilkan sebagai kategori terpisah. Approval historis tidak diubah. Jika snapshot approval berbeda dari master sparepart, sistem menampilkan sumber canonical yang tersedia sekaligus mempertahankan snapshot persetujuan untuk audit.

## Navigasi dan state

Backend tetap menentukan tahap yang dapat diakses melalui `WorkOrderStageService`. Tombol UI hanya mengarah ke route yang telah diizinkan oleh backend; direct URL tetap melewati gate yang sama.

## Stok dan pembayaran

Tidak ada pengurangan stok pada perpindahan tahap, approval, atau pembukaan QC. Finalisasi tetap memakai transaksi database, locking, riwayat mutasi stok, dan pencegahan transaksi ganda yang sudah ada. Mekanisme invoice, pembayaran, dan status pembayaran tidak diubah.

## Validasi

- Blade cache berhasil dikompilasi.
- Blade cache berhasil dikompilasi setelah perubahan terakhir.
- Lint PHP untuk service dan controller terkait berhasil.
- Regression wizard, personnel, execution, finalisasi, dan payment: **73 lulus, 1 skipped, 216 assertions**.
- Rendering test HTTP/Blade untuk tahap Pengerjaan ditambahkan; pada fixture tanpa Work Order berstatus `dikerjakan`, test tersebut dilewati dengan alasan eksplisit. Test tetap memeriksa CTA Pemeriksaan Mutu, rincian estimasi, dan tidak adanya CTA lama.
- Endpoint `/admin/servis/13822/persetujuan` secara read-only merespons redirect ke `/login`, sehingga sesi HTTP yang tersedia belum terautentikasi sebagai admin.
- Otomasi browser tidak dapat diinisialisasi pada sesi ini karena layanan browser tepercaya tidak tersedia. Karena itu, QA visual desktop/tablet/mobile dan screenshot final tidak diklaim sebagai PASS.

## Database dan file member

Tidak ada migration, reset database, perubahan approval historis, perubahan pembayaran, atau perubahan file member pada refinement ini.

## Masalah tersisa

- Validasi visual desktop/tablet/mobile pada `/persetujuan`, `/pengerjaan`, dan route pemeriksaan mutu perlu dijalankan kembali menggunakan sesi admin yang valid dan layanan browser yang tersedia.
- Jika dealer membutuhkan aturan wajib catatan pengerjaan sebelum QC, aturan tersebut perlu dikonfirmasi terhadap kebijakan operasional karena model existing belum mendefinisikan syarat tersebut secara eksplisit.
