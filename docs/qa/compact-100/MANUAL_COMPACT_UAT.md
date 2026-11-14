# UAT compact premium — YZA Automotive

6 November 2026, Asia/Jakarta. Kotak kosong berarti belum diuji pada tahap compact ini, bukan hasil gagal atau PASS.

## Lingkungan

- [ ] Pakai instance/akun/data test terisolasi. Jangan mengalihkan database instance operasional yang sedang dipakai.
- [ ] Pastikan address bar tetap `http://127.0.0.1:8000` dan aplikasi YZA yang benar. Hentikan automation bila origin tidak dapat dipastikan; jangan melewati pemeriksaan keamanan.
- [ ] Mulai pada zoom browser 100%; catat browser/versi, ukuran viewport CSS, display scaling, role, route, fixture, state dan bukti screenshot.
- [ ] Jangan mengirim transaksi, pembayaran, WhatsApp, atau pendaftaran akun operasional dalam QA visual ini.

## Matriks wajib

- [ ] 320×568
- [ ] 375×667
- [ ] 390×844
- [ ] 430×900
- [ ] 768×900
- [ ] 1024×768
- [ ] 1280×720
- [ ] 1366×768 — baseline utama
- [ ] 1440×900
- [ ] 1920×1080
- [ ] Zoom browser nyata 125%, 150%, 200% pada landing, detail, login, member, admin, Owner, checkout dan WO. Sembilan simulasi viewport efektif yang tersedia tidak menggantikan pengujian ini.

## Pemeriksaan setiap halaman

- [ ] Densitas nyaman: heading tidak mendominasi, kartu/panel proporsional, teks 14–16 px untuk isi utama dan label tetap terbaca.
- [ ] Tidak ada horizontal document overflow. Carousel/tabel boleh scroll lokal; kolom terakhir dan aksi tetap terjangkau.
- [ ] Nama panjang, member ID, nomor order/invoice, harga, total, status serta peringatan keselamatan tidak terpotong.
- [ ] Navbar/sidebar tidak bertabrakan; drawer bisa ditutup, overlay/Escape bekerja, fokus kembali, collapse desktop menambah ruang konten.
- [ ] Semua menu bisnis tetap tersedia. Uji transisi breakpoint 640, 900, 1024/1025, 1100/1200, 1439/1440 px.
- [ ] Galeri mempertahankan bentuk kendaraan; warna, thumbnail, previous/next berfungsi.
- [ ] Form, error, date/select/upload, label panjang dan textarea bisa dipakai; input penting tidak diperkecil berlebihan.
- [ ] Modal panjang dapat digulir, tombol tutup/aksi terjangkau, fokus Tab aman dan kembali ke pemicu.
- [ ] Grafik, legend, tooltip dan kondisi kosong mengikuti lebar kontainer setelah resize/collapse.
- [ ] Footer tidak menutup tombol, pagination atau data terakhir. Periksa sampai bawah, bukan hanya viewport awal.
- [ ] Target sentuh mobile sekitar 44 px, focus-visible jelas, keyboard dan reduced motion berfungsi.
- [ ] Tidak ada console error yang mengganggu. Catat status `IMPLEMENTED & VISUALLY VERIFIED`, `IMPLEMENTED — VISUAL NOT VERIFIED`, `NEEDS FIX`, `BLOCKED` atau `NOT TESTED` beserta bukti.

## Cakupan halaman dan role

Gunakan [page-coverage.csv](page-coverage.csv) untuk 657 kombinasi role–halaman / 175 route HTML unik. Gunakan [route-attempts.csv](route-attempts.csv) untuk fixture yang belum tersedia, redirect, denial dan 404. Jangan menambah permission agar sebuah halaman dapat diperiksa.

- [ ] Public: seluruh section landing termasuk membership/benefit, carousel 4/3/2/1 sesuai ruang, katalog/filter/pagination, detail/galeri/spesifikasi, login/register/aktivasi. Lima halaman publik sudah memiliki pengukuran DOM di sepuluh viewport, tetapi tidak semua state telah diuji.
- [ ] Member: kedua varian dashboard `.yza-home` dan `.member-center-dashboard`, greeting, kartu member dengan ID/nama panjang, empat KPI dan layanan, drawer/profil/notifikasi, My Garage/create/edit/detail, katalog, pesanan/preorder/checkout, booking servis/cuci/test drive, trade-in, promo/poin dan invoice.
- [ ] Super Admin dan Admin: dashboard/lima KPI, sidebar/collapse/drawer, search/notifikasi/profil, seluruh index/create/edit/detail, filter, tabel, data master, aktivitas, audit dan pengaturan.
- [ ] Owner: dashboard, periode/filter, laporan penjualan/pembayaran, chart, tabel dan sidebar; akses tetap read-only.
- [ ] Sales: leads/detail, transaksi/penjualan, order, invoice dan handover sesuai permission.
- [ ] Service Advisor: booking, assignment, jadwal, WO, pemeriksaan/persetujuan sesuai permission.
- [ ] Mechanic: WO assigned, pengerjaan, jasa/sparepart/foto/catatan; WO lain tetap ditolak.
- [ ] Warehouse Staff: sparepart/fitment, inventori, stok masuk, penjualan dan histori yang diizinkan.
- [ ] Finance: pembayaran, rekonsiliasi, laporan, status dan nominal invoice utuh.
- [ ] Operational Staff: booking, test drive, car wash, pengajuan, trade-in dan penjualan sesuai permission.
- [ ] Quality Control: checklist, mandatory/optional, PASS/FAIL/N/A, alasan FAIL, status dan tombol authorized.
- [ ] Petugas legacy: shell/menu fallback dan semua halaman yang memang diizinkan.

## WO, PDI, QC dan transaksi

- [ ] WO show dan seluruh wizard pemeriksaan, persetujuan, pengerjaan, QC, finalisasi: stepper, biaya, jasa, sparepart, approval, bukti dan catatan.
- [ ] PDI: header order, informasi kendaraan, checklist PASS/ISSUE/N/A, catatan dan status; tidak mengurangi keterbacaan peringatan.
- [ ] QC: input/catatan tetap luas, teks wajib/opsional dan peringatan rework jelas. Tidak mengubah state agar UI terlihat berhasil.
- [ ] Checkout/promo/pembayaran: ringkasan, input nominal, total, status, metode, reservasi promo, validasi dan tombol tersedia tanpa clipping. Lakukan perubahan data hanya dalam fixture test yang diotorisasi.
- [ ] Invoice/print: nominal/status dan footer utuh; print/export membutuhkan pemeriksaan tersendiri.
- [ ] Triage 25 respons 404 dan 244 kombinasi fixture-unavailable sebelum menyatakan cakupan lengkap. Invoice/WO/detail yang tidak ter-render bukan bukti UI lulus.

## Bukti yang masih dibutuhkan

- [ ] Before/after compact untuk Member Center, Admin, Owner, halaman operasional, checkout, WO/PDI/QC dan invoice. Screenshot dari tahap responsif sebelumnya bukan after compact.
- [ ] Screenshot desktop/tablet/mobile pada setiap role, form error, modal panjang, dropdown terbuka, grafik, tabel kolom terakhir dan footer akhir.
- [ ] Capture yang sudah stabil setelah resize. Batch screenshot cepat bisa menangkap frame ukuran sebelumnya; jangan menganggap adanya file sebagai bukti visual benar.
- [ ] Gunakan nama `uat-{role}-{route}-{width}x{height}-z{zoom}-{state}.jpg`, tanpa data operasional sensitif.
- [ ] Catat reviewer/tanggal, langkah reproduksi, status, bukti dan retest. Checklist lintas fitur yang lebih rinci tersedia di [UAT responsif terdahulu](../responsive-100/MANUAL_UAT_CHECKLIST.md); gunakan ukuran baru di atas untuk tahap compact.

## Sign-off

- [ ] Semua halaman kritis dan role memiliki bukti; tutup NEEDS FIX dan ulangi build/render/regression bila source berubah.
- [ ] Jangan memberi persetujuan visual menyeluruh hanya dari render HTTP, stylesheet, atau angka overflow.
- [ ] Tidak ada commit, push atau deployment dalam pekerjaan ini.
