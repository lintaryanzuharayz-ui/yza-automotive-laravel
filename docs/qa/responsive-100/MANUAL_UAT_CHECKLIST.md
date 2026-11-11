# YZA Automotive — checklist UAT responsif 100%

Tanggal: 6 November 2026 (Asia/Jakarta). Status awal seluruh kotak: belum dijalankan. Checklist ini tidak menyatakan visual PASS.

## Lingkungan dan pencatatan

- [ ] Gunakan instance pengujian dengan data/akun fixture terisolasi. Jangan mengganti koneksi database instance operasional yang sedang digunakan.
- [ ] Verifikasi address bar menunjukkan `http://127.0.0.1:8000` dan halaman memang YZA Automotive. Jika URL/origin tidak dapat dipastikan, hentikan automation tersebut.
- [ ] Jangan melewati pemeriksaan keamanan browser. Jangan membuka akun eksternal atau halaman pembayaran pihak ketiga untuk checklist ini.
- [ ] Mulai dari browser zoom 100%. Catat browser/versi, OS/display scaling, ukuran viewport CSS, role, route, fixture, tanggal, dan hasil aktual.
- [ ] Gunakan [ui-page-coverage.csv](ui-page-coverage.csv) untuk seluruh 657 kombinasi role–halaman (175 route unik), bukan hanya dashboard.
- [ ] Gunakan [route-attempts.csv](route-attempts.csv) dan [routes-without-html-coverage.csv](routes-without-html-coverage.csv) untuk menyiapkan fixture yang belum tersedia, menelusuri 404, serta halaman yang dikecualikan.
- [ ] Setiap hasil harus ditandai `VERIFIED PASS`, `IMPLEMENTED — VISUAL NOT VERIFIED`, `NEEDS FIX`, `BLOCKED`, atau `NOT TESTED`, disertai nama bukti dan catatan.

## Matriks ukuran dan zoom

Ulangi halaman kritis publik, member, admin, Owner, serta satu list, form, detail, dan laporan per role pada semua ukuran berikut. Untuk halaman lainnya, minimum desktop, tablet, dan mobile; perluas jika ada perbedaan layout.

| Viewport CSS | Sasaran | Selesai |
|---|---|---|
| 320×568 | Ponsel kecil | [ ] |
| 375×720 | Ponsel | [ ] |
| 390×768 | Ponsel modern | [ ] |
| 430×900 | Ponsel besar | [ ] |
| 768×900 | Tablet portrait | [ ] |
| 1024×768 | Tablet landscape | [ ] |
| 1280×720 | Laptop kecil | [ ] |
| 1366×768 | Baseline utama | [ ] |
| 1440×900 | Desktop | [ ] |
| 1920×1080 | Full HD | [ ] |
| 2560×1080 | Layar lebar | [ ] |

- [ ] Uji zoom browser nyata 125%, 150%, 200% pada halaman kritis. Catat ukuran jendela sebelum zoom dan viewport setelah zoom. Mengubah viewport saja tidak dihitung sebagai bukti zoom nyata.
- [ ] Uji tambahan 67%, 80%, 90%, 110% bila tersedia. Jangan meminta pengguna mengubah zoom untuk memperbaiki layout.
- [ ] Uji resize melewati 640, 900, 1024, 1180, 1439/1440 px; buka/tutup drawer sebelum dan sesudah resize.

## Kriteria pada setiap halaman

- [ ] Tidak ada scroll horizontal dokumen; carousel dan tabel boleh memiliki scroll lokal yang jelas.
- [ ] Teks panjang, nama kendaraan, harga/rupiah, member ID, nomor invoice, pesan error, dan label tidak terpotong atau bertabrakan.
- [ ] Heading, kartu, CTA, ikon, dan gambar memiliki proporsi yang terbaca pada zoom 100%.
- [ ] Footer tidak menutup pagination, baris terakhir, tombol simpan, atau pesan validasi; scroll sampai akhir halaman.
- [ ] Tombol utama nyaman ditekan; fokus keyboard terlihat, urutan Tab logis, Enter/Space berfungsi.
- [ ] Dropdown terbuka melalui klik/keyboard, tetap di dalam viewport, dan dapat ditutup dengan Escape/klik luar sesuai komponen.
- [ ] Tabel dapat digeser lokal dengan sentuhan dan keyboard; kolom terakhir dan seluruh aksi tetap terjangkau.
- [ ] Form berubah menjadi satu kolom saat diperlukan; date/time/select, placeholder, upload, dan error tampil utuh.
- [ ] Modal pendek dan panjang memiliki tinggi aman, scroll internal, tombol tutup/aksi terlihat, fokus tidak keluar, dan fokus kembali ke pemicu.
- [ ] Grafik berubah ukuran saat sidebar/viewport berubah; label, legend, tooltip, dan kondisi tanpa data tidak terpotong.
- [ ] Gambar mempertahankan rasio; crop kendaraan dan overlay teks tetap tepat. Uji setelah gambar/font selesai dimuat.
- [ ] Periksa console dan permintaan asset yang gagal; pisahkan kegagalan asset dari endpoint yang sengaja tidak tersedia pada snapshot.
- [ ] Uji reduced motion dan keyboard tanpa mouse; catat masalah aksesibilitas secara terpisah dari hasil render.

## Public

- [ ] Landing: intro dapat ditutup, semua section navigasi tersedia, hero kendaraan merah tetap sama, heading/CTA tidak overlap.
- [ ] Navbar: hamburger, semua menu bisnis, CTA Masuk/Buat Member, Escape dan klik luar; CTA akun tersedia di menu ponsel.
- [ ] Katalog: filter, pencarian, card panjang, harga, badge, pagination, kondisi kosong, favorit dan detail.
- [ ] Detail kendaraan/showcase: galeri, thumbnail, spesifikasi, warna, CTA pembelian, rekomendasi; siapkan fixture showcase yang belum ter-render.
- [ ] Login/register/aktivasi: input panjang, kesalahan validasi, password toggle, syarat yang ada, CTA dan footer. Jangan membuat akun operasional.
- [ ] Section membership, jual mobil/trade-in, servis, janji temu, informasi showroom, tentang/kontak, video, dan footer. Jangan mengirim WhatsApp/form eksternal.

## Member

- [ ] Dashboard: greeting, ID/nama/status kartu member, empat KPI, empat layanan, transaksi, rekomendasi, footer.
- [ ] Drawer: buka/tutup/overlay/Escape, semua tautan bisnis, fokus Tab dan kembali ke pemicu; uji 1366 dan transisi 1439→1440.
- [ ] Dropdown desktop, profil, notifikasi: klik/keyboard; menu tidak keluar viewport.
- [ ] My Garage: list, tambah, detail, edit kilometer, data panjang, tombol Batal pada tambah kembali ke list.
- [ ] Pesanan/preorder: list/detail/status berbeda; checkout ringkasan, metode pembayaran, promo, error, total, CTA. Siapkan fixture eligible dan hindari transaksi operasional.
- [ ] Booking mobil, servis, Car Wash, test drive: index/create/detail, kalender/waktu, error, kondisi member eligible/tidak eligible.
- [ ] Trade-in/pengajuan: stepper membungkus, form dan foto, estimasi/detail/penawaran, kondisi kosong.
- [ ] Promo/poin/profile: list/detail/riwayat/pagination, nama panjang; jumlah dan aturan promo tetap sama.
- [ ] Riwayat servis, persetujuan, tracking, invoice: gunakan fixture yang sesuai ownership dan lifecycle.

## Super Admin dan Admin

- [ ] Dashboard: lima KPI tetap tersedia, kicker tidak bertabrakan dengan ikon, tabel pesanan dan panel perhatian terbaca.
- [ ] Sidebar: label, active state, scroll internal; collapse/expand tidak meninggalkan ruang kosong; tooltip dan keyboard tersedia.
- [ ] Drawer: tombol tutup, overlay, Escape, fokus; uji 1024→1025 dan status collapse tersimpan.
- [ ] Topbar: hanya satu pencarian, notifikasi, menu profil, hasil pencarian panjang, dropdown pada viewport kecil.
- [ ] Buka seluruh link sidebar dengan akun masing-masing; jangan mengubah permission untuk meloloskan UI.
- [ ] Seluruh index/create/edit/detail/approval: mobil, inventori, preorder, pesanan, pengambilan, PDI, handover, pelanggan/member, promo/poin, karyawan, role, pengaturan, audit/aktivitas.
- [ ] Pembayaran, dashboard keuangan, transaksi, rekonsiliasi, invoice: banner dan filter 320 px; tabel lokal; nominal panjang dan tombol aksi.
- [ ] Servis/WO: booking, jadwal, personel, pemeriksaan, persetujuan, pengerjaan, QC, finalisasi, sparepart; periksa tampilan setiap tahap tanpa mengubah lifecycle agar UI terlihat.
- [ ] Sparepart detail/fitment: foto, data teknis, kompatibilitas, histori, field panjang dan tombol; tidak melebar pada 320 px.
- [ ] Stok masuk: header/action group membungkus; create/detail dan tabel aman.
- [ ] Laporan servis: nominal KPI pendapatan/average membungkus pada 320 px; tabel scroll lokal; filter/date; panel distribusi.
- [ ] Invoice/print: halaman layar dan print preview; jangan menganggap snapshot invoice yang 404 telah diuji.

## Seluruh role lainnya

Untuk setiap role berikut, jalankan semua halaman yang diizinkan dalam CSV, minimum dashboard, satu tabel, satu form yang memang diizinkan, detail/WO/transaksi dan notifikasi/profil. Jika role read-only tidak memiliki form, catat N/A beserta alasan; jangan menambah akses.

- [ ] Owner: dashboard, periode, laporan penjualan/pembayaran, grafik, data panjang, sidebar collapse/drawer dan read-only tetap berlaku.
- [ ] Sales: dashboard, leads/detail, penjualan/pengadaan dan form terkait, invoice/handover sesuai permission.
- [ ] Service Advisor: booking, WO, assignment, pemeriksaan/persetujuan, jadwal/personel sesuai permission.
- [ ] Mechanic: WO yang benar-benar diassign, detail/pengerjaan, sparepart, foto/catatan; akses WO lain tetap ditolak.
- [ ] Warehouse Staff: inventori, sparepart/fitment, stok masuk, penjualan sparepart dan histori sesuai permission.
- [ ] Finance: pembayaran, transaksi, laporan, rekonsiliasi dan invoice sesuai permission.
- [ ] Quality Control: daftar/detail WO yang relevan, checklist QC, penolakan/kelulusan dan data panjang sesuai permission.
- [ ] Operational Staff: booking, test drive, car wash, pengajuan, trade-in, penjualan dan jadwal sesuai permission.
- [ ] Petugas (role legacy yang ditemukan): dashboard/menu fallback serta booking/servis dan form/detail sesuai permission.

## Bukti yang harus dikumpulkan

- [ ] Screenshot viewport dan full-page untuk empat halaman kritis pada 1366×768, 768×900, 320×568, dan zoom nyata 200%.
- [ ] Screenshot setiap ukuran matriks lain untuk halaman kritis, terutama 375/390/430, 1024/1280, 1440/1920/2560.
- [ ] Screenshot drawer terbuka, dropdown, modal panjang, form ber-error, tabel kolom terakhir, footer akhir, serta grafik/legend.
- [ ] Screenshot khusus perbaikan terakhir: laporan servis KPI 320 px dan katalog member 1366 px. Belum ada pengukuran akhir tersimpan setelah dua perbaikan ini.
- [ ] Screenshot halaman detail/checkout/approval yang sebelumnya kekurangan fixture atau menghasilkan 404.
- [ ] Gunakan nama `uat-{role}-{route}-{width}x{height}-z{zoom}-{state}.png`; jangan sertakan data operasional/rahasia.
- [ ] Rekam hasil pada salinan CSV: status, reviewer, tanggal, browser, fixture, bukti, masalah/retest. Tanda centang tanpa bukti tidak cukup untuk VERIFIED PASS.

## Keputusan akhir UAT

- [ ] Seluruh halaman kritis dan seluruh role telah memiliki bukti visual/interaction; semua 404 yang relevan telah ditriase.
- [ ] Tidak ada NEEDS FIX pada navigasi, overflow, clipping, tombol utama, modal atau footer.
- [ ] Perbaikan tambahan tetap dibatasi pada tampilan; ulangi build/focused/full regression jika source berubah.
- [ ] Baru setelah persyaratan tersebut terpenuhi, tetapkan final responsive VERIFIED PASS. Tidak ada commit/push/deployment dalam pekerjaan finalisasi ini.
