# Notifikasi Real-Time Admin untuk Booking Servis

## Ringkasan

Setiap booking servis yang berhasil disimpan membuat satu notifikasi untuk setiap admin aktif. Notifikasi menggunakan tabel dan model `notifikasi` yang sudah ada, sehingga tidak membutuhkan migration, tabel baru, atau perubahan autentikasi.

## Alur server

`BookingServisService::create()` menyimpan booking dan memanggil `ServiceNotificationService::forAdminBooking()` dalam transaksi yang sama. Penerima dipilih dari user aktif dengan role `admin`. Isi notifikasi memuat pelanggan, kendaraan, nomor polisi, jenis servis, tanggal, dan jam booking.

Pencegahan duplikasi menggunakan kombinasi user admin, tipe `booking`, referensi `booking_servis`, ID booking, dan judul notifikasi. Jika penyimpanan notifikasi gagal, kegagalan dilaporkan tanpa membatalkan booking yang sudah diproses oleh alur aplikasi.

## Sinkronisasi browser

Layout admin memanggil endpoint `admin.notifikasi.sync` secara berkala dengan `after_id`. Polling berjalan sekitar setiap 5 detik, berhenti saat tab tidak terlihat, lalu melakukan sinkronisasi ketika tab aktif kembali. Jika terjadi kegagalan, interval meningkat sementara sampai koneksi kembali normal.

Respons endpoint dipakai untuk:

- memperbarui angka badge notifikasi;
- menampilkan toast untuk notifikasi baru;
- menambahkan item baru ke halaman pusat notifikasi tanpa reload penuh;
- membuka detail booking melalui `admin.booking-servis.show`.

Endpoint hanya tersedia untuk admin yang sudah login. Controller memeriksa `id_user` sebelum menandai notifikasi terbaca. Tautan pembukaan juga menandai notifikasi sebagai terbaca melalui endpoint server.

## Pengujian

Pengujian feature berada di `tests/Feature/AdminRealtimeNotificationsTest.php` dan mencakup deduplikasi, deep link booking, pembatasan akses member, mark-read, serta branding pusat notifikasi. Uji manual dua browser dapat dilakukan dengan langkah berikut:

1. Buka halaman admin notifikasi pada satu browser.
2. Login sebagai member pada browser lain dan buat booking servis.
3. Pastikan badge admin berubah tanpa reload penuh.
4. Pastikan toast dan item booking baru muncul.
5. Klik item tersebut dan pastikan halaman detail booking terbuka.
6. Pastikan item berubah menjadi terbaca dan jumlah unread berkurang.

Status UAT manual: belum dijalankan pada tahap implementasi ini.
