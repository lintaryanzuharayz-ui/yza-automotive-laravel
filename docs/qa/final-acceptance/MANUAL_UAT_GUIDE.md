# YZA Automotive — Panduan Manual UAT Staging

Panduan ini untuk Chrome atau Edge pada staging terisolasi. Jangan gunakan URL production, database operasional, credential production, atau kartu pembayaran nyata.

## Akun UAT

Gunakan akun fixture UAT yang sudah tersedia, dengan password UAT kuat di lingkungan staging:

`superadmin506`, `owner506`, `sales506`, `advisor506`, `mekanik506`, `gudang506`, `finance506`, `operasional506`, `qc506`.

Password jangan ditulis di laporan atau screenshot. Ganti dengan password unik sebelum staging dibuka melalui jaringan.

## Zoom 67%

1. Buka URL staging, lalu login sesuai role.
2. Chrome: menu `⋮` → `Zoom` → `-` sampai indikator `67%`.
3. Edge: menu `…` → `Zoom` → `-` sampai indikator `67%`.
4. Ulangi pemeriksaan pada 100%.
5. Pada setiap ukuran, periksa sidebar, navbar, hero, card, form, modal, tabel, dan grafik.
6. Hasil PASS hanya bila tidak ada konten terpotong, overlap, tombol tidak terjangkau, atau horizontal scroll yang tidak disengaja. Simpan screenshot setiap temuan.

## Pemeriksaan menu dan CRUD per role

Untuk setiap akun: buka `/login`, masuk, lalu ikuti semua menu yang terlihat pada sidebar/navbar.

Pada setiap menu yang memiliki data:

1. Buka halaman index dan pastikan judul, tabel, pagination, pencarian, filter, dan tombol aksi tampil.
2. Buat satu fixture sintetis berlabel `UAT-<tanggal>-<role>`.
3. Uji validasi dengan mengosongkan field wajib dan memasukkan format yang salah; pastikan pesan error tampil dan data tidak tersimpan.
4. Simpan data valid, buka detail/edit, lalu verifikasi perubahan.
5. Uji pencarian dan pagination.
6. Gunakan modal buka/tutup dan tombol batal; pastikan tidak ada submit tidak sengaja.
7. Hapus hanya fixture UAT tersebut bila fitur dan kebijakan staging mengizinkan.
8. Simpan screenshot index, error validasi, hasil simpan, dan hasil aksi.

Role yang harus diperiksa: Super Admin, Owner, Sales, Service Advisor, Mekanik, Gudang, Finance, Operasional, dan QC/PDI. Menu yang tidak terlihat atau menghasilkan 403 di luar permission yang ditetapkan dicatat sebagai expected, bukan dipaksa lulus.

## Pembelian kendaraan sampai My Garage

1. Login sebagai member UAT.
2. Buka katalog kendaraan dan pilih fixture kendaraan UAT.
3. Ikuti pengajuan/pembelian sampai status yang tersedia pada staging.
4. Gunakan sandbox payment bila provider benar-benar terhubung; jangan mengklaim settlement tanpa status provider.
5. Login sebagai role operasional/Sales yang berwenang dan proses PDI/handover sesuai menu.
6. Kembali sebagai member, buka My Garage.
7. PASS hanya bila kendaraan menjadi milik member yang sama, status dan dokumen konsisten, serta kendaraan member lain tidak terlihat.

## Booking servis sampai riwayat mekanik

1. Login sebagai member UAT dan pilih kendaraan milik akun tersebut.
2. Buka Booking Servis, pilih layanan, jadwal yang tersedia, dan preferensi mekanik/rekomendasi.
3. Kirim booking dan catat nomor booking.
4. Login sebagai Service Advisor; terima/konfirmasi sesuai jadwal, kapasitas, dan kompetensi. Pastikan tidak terjadi benturan jadwal.
5. Login sebagai Mekanik; pastikan Work Order hanya terlihat dan dapat diubah oleh mekanik yang ditugaskan.
6. Catat pemeriksaan, pekerjaan, QC, dan pembayaran sesuai aturan staging.
7. Kembali sebagai member; buka kendaraan dan riwayat servis.
8. PASS hanya bila tanggal, nomor Work Order, pekerjaan, mekanik utama/pendamping, status, hasil yang boleh tampil, dan tanggal selesai berasal dari transaksi yang sama.

## Export Owner PDF/XLSX

1. Login sebagai Owner dan buka setiap menu laporan.
2. Pilih periode fixture yang jelas, misalnya satu bulan UAT.
3. Unduh satu file PDF, tunggu selesai, lalu buka file sebelum mengunduh file berikutnya.
4. Ulangi untuk XLSX.
5. Periksa nama laporan, periode, total baris, nominal, subtotal/total, format tanggal, dan apakah file dapat dibuka tanpa warning.
6. Cocokkan total dengan tampilan dashboard/filter yang sama. Simpan file dan screenshot halaman laporan.

## Bukti dan keputusan

Untuk setiap skenario tulis: tanggal, browser, zoom, role, URL, fixture ID, langkah, hasil `PASS/FAIL/BLOCKED`, dan nama file screenshot. `PASS` hanya untuk langkah yang benar-benar dijalankan. Jika callback provider, credential sandbox, atau staging URL tidak tersedia, gunakan `BLOCKED` dan tulis prasyaratnya; jangan menggantinya dengan mock.
