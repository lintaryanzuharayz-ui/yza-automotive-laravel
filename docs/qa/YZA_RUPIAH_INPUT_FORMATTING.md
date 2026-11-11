# Pemisah ribuan otomatis untuk kolom rupiah

Tanggal: 8 November 2026.

## Hasil

Kolom harga/nominal pada layout bersama otomatis menampilkan titik setiap tiga digit dari kanan. Contoh: `9000000000` menjadi `9.000.000.000`. Pecahan menggunakan koma: `1250000.50` menjadi `1.250.000,50`. Pengelompokan dilakukan pada string agar angka besar tidak dibulatkan JavaScript.

Implementasi ada di `public/assets/js/rupiah-input.js`, dimuat dari `resources/views/layouts/app.blade.php`. Mencakup input harga kendaraan, sparepart, biaya jasa, penawaran, pembayaran, DP, target nominal dan diskon nominal, termasuk form popup yang dibuat setelah halaman terbuka. Kolom pembayaran bernama `jumlah` diberi penanda eksplisit pada kedua form pembayaran agar tidak tertukar dengan jumlah barang.

Tahun, stok, kuantitas, telepon, ID dan persentase tidak diberi pemisah rupiah. Diskon manual/promo hanya diformat ketika jenisnya nominal. Input tersembunyi yang sudah ada tidak diubah.

## Integritas nilai

Input tampilan tidak memiliki `name`. Input asli mempertahankan nama, ID, referensi perhitungan, event dan nilai numerik tanpa titik ribuan untuk pengiriman formulir. Tidak ada perubahan harga dalam database operasional. Validasi wajib, min/max/step, pesan validasi dari perhitungan, reset dan pembaruan nilai melalui JavaScript dipertahankan. Teks tidak sah diblokir dan tidak mengirim nilai valid sebelumnya secara diam-diam.

## Pemeriksaan browser

Menggunakan hasil render halaman kendaraan asli dari database testing dalam transaksi read-only; semua pengiriman dan navigasi operasional diblokir pada halaman QA sementara. Halaman sementara sudah dihapus setelah pemeriksaan.

- Popup edit dinamis: mengetik `9000000000` menghasilkan `9.000.000.000`; backspace dan pengetikan lanjutan bekerja.
- FormData contoh: tampilan `1.250.000,50` mengirim `harga=1250000.50`; diskon `250.000` mengirim `manual_discount_value=250000`.
- Tahun tetap `2024`/`2026`, jumlah barang tetap `3`, bertipe number.
- Perubahan harga melalui JavaScript tersinkron ke `1.250.000,50`.
- Pergantian nominal → persentase → nominal mengembalikan tipe input yang sesuai.
- Reset mengembalikan `9.000.000.000` dengan nilai kirim `9000000000`.
- Validasi minimum dan pesan validasi perhitungan diteruskan; input `abc` ditolak.

Bukti tampilan (data simulasi): `docs/qa/rupiah-input-popup.png`.

## Pengujian

- Seluruh pengujian JavaScript: **29 PASS / 0 FAIL**, termasuk 3 pengujian formatter baru dan pengujian alur penjualan, SPK, serta notifikasi yang sudah ada. Log: `storage/logs/rupiah-input-client.txt`.
- Syntax JavaScript dan pemeriksaan whitespace file yang diubah: PASS.
- Regresi PHP penuh: **721 PASS / 4.220 assertions**, 0 failure, pada database testing terisolasi. Lihat `storage/logs/rupiah-input-regression.txt` dan `storage/logs/rupiah-input-regression.xml`. Jumlah ini mencakup pengujian yang kini tersedia dalam workspace, termasuk pengujian retirement akun.

Tampilan harga mentah pada detail penjualan sparepart, harga harapan pengajuan mobil, dan total form pembayaran juga memakai helper `money()` yang sudah tersedia.

Script berupa aset statis dengan versi berdasarkan waktu perubahan file; tidak memerlukan build Vite. Tidak ada commit, push atau deploy.
