# YZA Automotive — Panduan Presentasi Final Layanan Servis

Status persiapan: **READY FOR MANUAL DEMO**

Panduan ini disiapkan untuk presentasi dosen menggunakan database Laravel demo yang terisolasi. Browser belum dijalankan oleh Codex pada tahap ini, sehingga seluruh alur klik di bawah tetap harus diuji manual sebelum presentasi.

## Batasan keselamatan

- Database demo: `yza_automotive_laravel_demo`.
- Database Laravel utama tidak dimigrasikan atau di-seed dalam persiapan ini.
- Database PHP Native `yza_automotive` tidak disentuh.
- Work Order `13822` tidak digunakan dan tidak diubah.
- Data fitment dan stok yang disiapkan adalah data sintetis untuk demo. Keterangan fitment diberi label `DEMO SYNTHETIC`, bukan klaim kompatibilitas OEM nyata.
- Jangan memakai gateway pembayaran nyata. Gunakan metode pembayaran offline/manual pada tahap demonstrasi.

## Akun demo

Password ketiga akun adalah `DemoYZA@2026!`.

| Peran | Email |
|---|---|
| Admin | `admin.demo@yza.local` |
| Petugas | `petugas.demo@yza.local` |
| Member | `member.demo@yza.local` |

## Data awal yang sudah disiapkan

- Kendaraan member: `B 1984 YZA` — Toyota GR Supra, tahun 2024, warna Supra Red.
- Booking sintetis: booking `#2`, jadwal **14 Oktober 2026 pukul 10:00**, keluhan `DEMO PRESENTASI — alur servis lengkap`.
- Sparepart demo: `YZA-FLT-OIL01`, stok awal **3**.
- Fitment sintetis untuk GR Supra sudah tersedia dengan sumber bukti yang menyatakan hanya untuk training/demo.
- Jadwal bengkel: Senin–Jumat 08:00–17:00, istirahat 12:00–13:00. Sabtu–Minggu tutup.

## Sebelum mulai

1. Tutup tab aplikasi yang masih menggunakan database utama.
2. Dari folder project, jalankan `switch-to-demo.bat`.
3. Buka aplikasi Laravel pada alamat lokal yang biasa digunakan.
4. Login menggunakan akun demo di atas.
5. Pastikan halaman inventory menunjukkan stok filter demo = 3 sebelum transaksi servis dimulai.

## Urutan klik presentasi

### 1. Member membuat booking

1. Login sebagai member.
2. Buka **Kendaraan Saya** dan pastikan `B 1984 YZA` terlihat sebagai kendaraan milik akun demo.
3. Buka **Layanan Otomotif → Booking Servis**.
4. Pilih kendaraan tersebut, jenis servis **Ganti Oli**, tanggal 14 Oktober 2026, dan slot 10:00.
5. Isi keluhan demo. Untuk booking yang sudah disiapkan, cukup tampilkan detail booking `#2`; jangan membuat booking duplikat jika booking tersebut masih berstatus menunggu.

### 2. Admin menerima booking

1. Logout member lalu login sebagai admin atau petugas.
2. Buka **Booking Servis**.
3. Buka booking `#2`, periksa identitas member, kendaraan, jadwal, dan keluhan.
4. Klik tindakan menerima booking. Jangan memilih booking lain yang bukan data demo.

### 3. Membuat Work Order dari booking

1. Dari detail booking yang diterima, klik tindakan **Buat Work Order**.
2. Pastikan kendaraan yang muncul tetap `B 1984 YZA`.
3. Lanjutkan ke tahap **Pemeriksaan**.
4. Masukkan hasil pemeriksaan sintetis, misalnya kondisi oli perlu diganti dan filter oli perlu diperiksa.

### 4. Rekomendasi dan persetujuan member

1. Tambahkan temuan/rekomendasi untuk penggantian oli dan filter.
2. Buat permintaan persetujuan dengan item filter demo `YZA-FLT-OIL01` dan jasa yang diperlukan.
3. Logout admin/petugas.
4. Login member dan buka **Layanan Otomotif** atau notifikasi persetujuan servis.
5. Buka detail persetujuan, pastikan item dan harga tampil sebagai snapshot, lalu klik **Setujui**.
6. Jika layar meminta catatan, isi `Disetujui untuk kebutuhan demonstrasi`.

### 5. Pengerjaan

1. Login kembali sebagai petugas/admin.
2. Buka Work Order demo dari daftar servis.
3. Pastikan status persetujuan sudah diterima.
4. Masukkan jasa yang benar-benar didemonstrasikan dan tambahkan sparepart filter demo.
5. Lanjutkan status ke **Pengerjaan**. Jangan melakukan pengurangan stok manual dari halaman adjustment.

### 6. QC dan finalisasi

1. Buka tahap **Quality Control**.
2. Klik mulai QC, isi seluruh checklist wajib dengan hasil lulus, lalu simpan.
3. Periksa ringkasan total dan item sebelum finalisasi.
4. Klik **Finalisasi** satu kali saja setelah semua data benar.
5. Setelah finalisasi, buka inventory/riwayat stok dan pastikan stok filter berubah dari 3 menjadi 2.
6. Refresh halaman dan pastikan finalisasi kedua tidak mengurangi stok lagi. Target stok tetap 2.

### 7. Pembayaran dan invoice

1. Dari transaksi servis yang terbentuk, buka halaman pembayaran.
2. Pilih pembayaran offline/manual atau metode demo yang tidak terhubung gateway nyata.
3. Verifikasi pembayaran sebagai admin/petugas demo.
4. Buka invoice servis dan riwayat servis dari akun member.
5. Tunjukkan bahwa status akhir, rincian jasa, sparepart, total, dan invoice konsisten.

## Checklist pemeriksaan sebelum presentasi

- [ ] Browser manual berhasil login sebagai admin, petugas, dan member.
- [ ] Booking demo `#2` terlihat dan tidak berstatus dibatalkan.
- [ ] Identitas kendaraan tetap `B 1984 YZA` milik `member.demo@yza.local`.
- [ ] Fitment terlihat berlabel DEMO/SYNTHETIC.
- [ ] Stok awal filter = 3.
- [ ] Stok setelah finalisasi pertama = 2.
- [ ] Stok tetap 2 setelah refresh atau percobaan membuka ulang tahap finalisasi.
- [ ] QC tidak dapat dilewati tanpa checklist wajib lengkap.
- [ ] Invoice dapat dibuka dari sisi petugas/admin dan member.
- [ ] Tidak ada pembayaran gateway nyata.
- [ ] Tidak ada tindakan pada Work Order 13822.

## Setelah presentasi

1. Logout semua akun.
2. Jika perlu mengembalikan konfigurasi lokal ke database utama, jalankan `switch-to-main.bat` setelah seluruh demo selesai.
3. Jangan menjalankan migration atau seeder pada database utama tanpa backup dan prosedur deployment resmi.

## Bukti pemeriksaan yang sudah dilakukan

- Histori migration database demo berjumlah 49 dan seluruhnya berstatus `Ran`.
- Empat migration terakhir yang benar-benar dijalankan pada database demo adalah scheduling, personel, eksekusi/QC, dan fitment.
- Akun demo terdeteksi: 3.
- Kendaraan demo terdeteksi: 1.
- Fitment sintetis terdeteksi: 1.
- Stok awal filter demo terdeteksi: 3.
- WO 13822 tidak ditemukan pada database demo.
- `.env` utama tetap menunjuk ke `yza_automotive_laravel`; `.env.demo` menunjuk ke `yza_automotive_laravel_demo`.

Status akhir persiapan: **READY FOR MANUAL DEMO**. Status ini berarti data dan konfigurasi demo sudah disiapkan; keberhasilan klik end-to-end di browser tetap menunggu verifikasi manual.
