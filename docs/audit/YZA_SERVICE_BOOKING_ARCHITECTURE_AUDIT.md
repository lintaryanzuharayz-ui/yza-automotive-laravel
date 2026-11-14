# YZA Service Booking Architecture Audit

Tanggal audit: 12 Oktober 2026  
Ruang lingkup: audit read-only arsitektur booking servis, integrasi My Garage, work order, pembayaran, dan kesiapan pengembangan kalender/slot.  
Status perubahan: **tidak ada perubahan database, schema, migration, data, auth, route, controller, atau UI yang dilakukan oleh audit ini.**

## 1. Ringkasan eksekutif

Modul booking servis dasar sudah tersedia dan terhubung dengan alur operasional sampai work order, detail jasa/spare part, transaksi, pembayaran, dan riwayat servis pelanggan.

Kesiapan saat ini:

| Area | Status | Temuan utama |
|---|---|---|
| Booking dasar pelanggan | SIAP DENGAN CATATAN | Customer dapat memilih kendaraan, jenis servis, tanggal, jam, keluhan, dan catatan. |
| Otorisasi dan ownership | SIAP | Semua booking dibatasi ke customer yang sedang login dan kendaraan aktif miliknya. |
| My Garage | SIAP SEBAGIAN | Detail kendaraan sudah terhubung ke booking; link dari daftar kendaraan belum meneruskan kendaraan terpilih. |
| Preselection kendaraan | BELUM LENGKAP | Controller sudah membaca `?kendaraan=`, tetapi view create belum menandai option terpilih. |
| Validasi tanggal/jam | SIAP DASAR | Masa lalu ditolak, tetapi jam operasional, hari libur, konflik, dan kapasitas belum diperiksa. |
| Kalender dan slot | BELUM TERSEDIA | Belum ada operating hours, interval slot, blackout/holiday, kapasitas, atau endpoint availability. |
| Teknisi | SIAP UNTUK ASSIGNMENT MANUAL | Work order memiliki `id_petugas`; belum ada preferensi, spesialisasi, jadwal, atau availability teknisi. |
| Service advisor | BELUM TERDEFINISI | Belum ada entitas/field khusus service advisor atau aturan assignment-nya. |
| Kilometer | SIAP DI WORK ORDER | Kilometer dicatat saat work order dibuat dan current kilometer kendaraan diperbarui bila nilainya lebih tinggi. Belum diambil saat booking. |
| Pembayaran dan riwayat | SIAP SETELAH WORK ORDER | Transaksi servis dibuat saat finalisasi work order; pembayaran dan invoice sudah memiliki route/member view. |

Kesimpulan: fondasi domain sudah cukup untuk implementasi bertahap. Perubahan paling penting sebelum kalender produksi adalah definisi kapasitas dan aturan slot; tanpa itu aplikasi hanya menyimpan permintaan tanggal/jam dan belum menjamin jadwal dapat dipenuhi bengkel.

## 2. Rute dan akses

Semua route member berada di bawah autentikasi, role pelanggan, dan middleware member. Route create booking juga menggunakan `verified.owner`.

### Member/customer

| Method | URI | Route name | Handler | Fungsi |
|---|---|---|---|---|
| GET | `/pelanggan/booking-servis` | `pelanggan.booking-servis.index` | `BookingServisController@index` | Daftar booking milik customer |
| GET | `/pelanggan/booking-servis/create` | `pelanggan.booking-servis.create` | `BookingServisController@create` | Form booking |
| POST | `/pelanggan/booking-servis` | `pelanggan.booking-servis.store` | `BookingServisController@store` | Membuat booking |
| GET | `/pelanggan/booking-servis/{booking_servis}` | `pelanggan.booking-servis.show` | `BookingServisController@show` | Detail booking milik customer |
| PATCH | `/pelanggan/booking-servis/{booking_servis}/cancel` | `pelanggan.booking-servis.cancel` | `BookingServisController@cancel` | Membatalkan booking menunggu/diterima |
| GET | `/pelanggan/servis` | `pelanggan.servis.index` | `Pelanggan\ServisController@index` | Riwayat work order customer |
| GET | `/pelanggan/servis/{servis}` | `pelanggan.servis.show` | `Pelanggan\ServisController@show` | Detail servis, biaya, pembayaran |
| GET/POST/... | `/pelanggan/kendaraan/*` | `pelanggan.kendaraan.*` | `Pelanggan\KendaraanController` | My Garage dan kendaraan pelanggan |

### Admin/petugas

| Method | URI | Route name | Fungsi |
|---|---|---|---|
| GET | `/admin/booking-servis` | `admin.booking-servis.index` | Monitoring dan filter booking |
| GET | `/admin/booking-servis/{booking_servis}` | `admin.booking-servis.show` | Detail booking |
| POST | `/admin/booking-servis/{booking_servis}/{action}` | `admin.booking-servis.action` | Confirm/reject |
| GET | `/admin/servis` | `admin.servis.index` | Daftar work order |
| POST | `/admin/servis/from-booking/{booking_servis}` | `admin.servis.from-booking` | Membuat work order dari booking diterima |
| POST/DELETE | `/admin/servis/{servis}/*` | `admin.servis.*` | Inspection, detail jasa, spare part, finalisasi |

Petugas memiliki rangkaian route operasional yang ekuivalen dengan prefix `petugas`. Admin dan petugas tidak berbagi akses member karena route dipisahkan oleh middleware role.

## 3. Alur booking yang berjalan

1. Customer membuka form booking.
2. Controller mengambil hanya kendaraan customer berstatus `aktif` dan jenis servis berstatus `aktif`.
3. Service memverifikasi ulang customer, ownership kendaraan, status aktif kendaraan, jenis servis aktif, dan tanggal/jam tidak berada di masa lalu.
4. Booking dibuat dengan kode `BSV-YYYYMM-RANDOM` dan status `menunggu`.
5. Admin/petugas mengubah `menunggu` menjadi `diterima` atau `ditolak`.
6. Setelah diterima, petugas membuat work order secara manual melalui `fromBooking`.
7. Work order mengubah booking menjadi `diproses`, menyimpan kilometer masuk, lalu melewati status `pemeriksaan` dan `dikerjakan`.
8. Finalisasi membuat transaksi servis berstatus `belum_bayar`; pembayaran diverifikasi melalui modul pembayaran.
9. Member dapat melihat status, diagnosis, detail jasa/spare part, total biaya, invoice, dan riwayat servis.

Catatan penting: pembuatan work order belum otomatis terjadi ketika booking diterima. Ini merupakan langkah operasional terpisah dan memang perlu dipertahankan atau ditegaskan dalam desain UX agar status tidak membingungkan.

## 4. Ownership dan otorisasi

Implementasi saat ini cukup aman untuk alur dasar:

- `BookingServisController` mengambil customer dari user login, bukan dari input form.
- Form request menerima `authorize(): true`, tetapi ownership tidak dipercayakan kepada form request; service tetap mencari kendaraan melalui relasi customer aktif.
- Parameter kendaraan dari URL divalidasi terhadap kendaraan aktif milik customer; kendaraan orang lain menghasilkan 404.
- `show` dan `cancel` memeriksa `id_pelanggan` booking terhadap customer login.
- `StoreBookingServisRequest` memvalidasi tipe servis melalui `exists`, dan service mengulangi pemeriksaan bahwa tipe tersebut aktif.
- Data tambahan seperti `id_pelanggan` atau `status` yang dikirim client diabaikan karena service membangun nilai tersebut dari konteks server.
- Tidak terlihat Policy khusus; proteksi saat ini berada pada middleware, controller scoping, dan service query.

Rekomendasi: pertahankan defense-in-depth ini. Jika alur diperluas, tambahkan authorization test untuk availability, teknisi pilihan, dan endpoint pembatalan/reschedule.

## 5. Integrasi My Garage

Integrasi yang sudah ada:

- `KendaraanPelanggan` memiliki relasi ke `BookingServis`, `Servis`, booking cuci, penjualan, dan aktivitas kendaraan lain.
- Halaman detail kendaraan sudah mengarah ke `pelanggan.booking-servis.create` dengan query `kendaraan=<id>`.
- Controller create sudah membaca dan memvalidasi query tersebut.
- Form booking menampilkan kendaraan aktif milik customer.

Gap konkret:

1. `resources/views/pelanggan/kendaraan/index.blade.php` mengarah ke form booking tanpa parameter kendaraan. Akibatnya tombol “Jadwalkan Servis” dari daftar My Garage tidak membawa konteks unit.
2. `resources/views/pelanggan/booking-servis/create.blade.php` tidak memakai `$selectedVehicleId` untuk `selected`. Jadi walaupun link detail kendaraan mengirim parameter yang benar, user tetap harus memilih ulang kendaraan.
3. Kilometer saat ini baru disimpan ketika work order dibuat (`ServisService::createFromBooking`). Booking belum menangkap odometer yang dilaporkan customer.

Perbaikan fungsional yang disarankan sebelum fitur kalender: teruskan ID kendaraan dari semua entry point My Garage, tandai option terpilih di form, dan tampilkan ringkasan kendaraan terpilih sebelum submit.

## 6. Data, model, dan schema

### Booking servis

Tabel `booking_servis` memiliki:

- `kode_booking` unique;
- `id_pelanggan`;
- `id_kendaraan_pelanggan`;
- `id_jenis_servis` nullable;
- `tanggal`, `jam`;
- `keluhan`, `catatan`;
- status enum `menunggu`, `diterima`, `ditolak`, `dibatalkan`, `diproses`, `selesai`;
- index `[tanggal, jam, status]` dan index customer.

Index waktu tersebut membantu query, tetapi bukan constraint kapasitas atau unique slot. Dua booking dengan kendaraan, tanggal, dan jam yang sama saat ini dapat dibuat.

### Jenis servis

Tabel `jenis_servis` menyimpan nama, deskripsi, estimasi harga, dan status aktif/nonaktif. Data read-only saat audit:

| ID | Jenis | Estimasi | Status |
|---:|---|---:|---|
| 1 | Servis Berkala | Rp350.000 | aktif |
| 2 | Ganti Oli | Rp150.000 | aktif |
| 3 | Tune Up | Rp400.000 | aktif |
| 4 | Servis Rem | Rp300.000 | aktif |
| 5 | Servis AC | Rp300.000 | aktif |
| 6 | Kelistrikan | Rp250.000 | aktif |

Belum ada durasi standar, kategori, kebutuhan bay, kapasitas per slot, atau aturan teknisi untuk setiap jenis servis. Karena itu sistem belum dapat menghitung slot berdasarkan beban kerja nyata.

### Kendaraan dan kilometer

`kendaraan_pelanggan.kilometer` memiliki default 0 dan diperbarui oleh `ServisService` hanya jika kilometer work order lebih tinggi daripada nilai sebelumnya. Nilai negatif dan penurunan odometer ditolak. Belum ada audit trail khusus perubahan kilometer dan belum ada field kilometer pada booking.

### Work order, teknisi, advisor

`servis` menyimpan `id_booking_servis`, `id_pelanggan`, `id_kendaraan_pelanggan`, `id_petugas`, kilometer, diagnosis, biaya, dan status work order. `petugas` menyimpan nama, kontak, jabatan, foto, dan status aktif/nonaktif.

Yang belum tersedia:

- tabel jadwal/availability teknisi;
- spesialisasi teknisi;
- preferensi teknisi pada booking;
- pemisahan peran service advisor dan teknisi;
- assignment advisor/technician dengan riwayat perubahan;
- batas jumlah work order per teknisi atau per slot.

Field `jabatan` pada `petugas` dapat menjadi data display, tetapi belum cukup untuk menjadi aturan bisnis service advisor.

### Konfigurasi

Tabel `pengaturan` hanya berisi konfigurasi umum seperti nama perusahaan, mata uang, timezone, dan batas notifikasi stok. Tidak ditemukan konfigurasi jam operasional, hari libur, interval slot, kapasitas, atau service bay.

## 7. Status dan handoff

Booking dan work order memiliki status yang berbeda.

| Entitas | Status yang ditemukan | Handoff |
|---|---|---|
| Booking | `menunggu` | Menunggu review admin/petugas |
| Booking | `diterima` | Dapat dibuat menjadi work order |
| Booking | `ditolak` | Selesai di sisi booking |
| Booking | `dibatalkan` | Dibatalkan customer sebelum diproses |
| Booking | `diproses` | Work order sudah dibuat |
| Booking | `selesai` | Nilai tersedia di schema, tetapi tidak menjadi transisi utama pada service booking saat ini |
| Work order | `pemeriksaan` | Inspection awal |
| Work order | `dikerjakan` | Pekerjaan dan detail biaya |
| Work order | `selesai` | Finalisasi dan transaksi |
| Transaksi | `belum_bayar`, `dp`, `lunas`, `dibatalkan` | Status pembayaran dan invoice |

Risiko UX: status booking `diterima` berarti permintaan diterima, bukan pekerjaan selesai atau kendaraan sudah masuk bengkel. UI sebaiknya menggunakan label yang eksplisit seperti “Booking diterima” dan “Work order sedang diproses”.

## 8. Gap matrix

| Kebutuhan | Ada saat ini | Gap | Dampak |
|---|---|---|---|
| Pilih kendaraan My Garage | Ya | Preselection belum selesai | User mengulang pilihan |
| Jenis servis aktif | Ya | Belum ada durasi/kategori | Availability belum akurat |
| Tanggal dan jam | Ya | Belum ada jam buka/tutup dan hari libur | Booking di luar operasi dapat masuk |
| Slot | Tidak | Belum ada interval atau endpoint availability | Tidak ada pilihan slot yang benar-benar tersedia |
| Konflik booking | Tidak | Tidak ada overlap/lock kapasitas | Double booking mungkin terjadi |
| Kapasitas bengkel | Tidak | Belum ada service bay/limit harian | Overbooking mungkin terjadi |
| Preferensi teknisi | Tidak | Tidak ada field/relationship | Belum bisa dipenuhi |
| Assignment teknisi | Sebagian | `id_petugas` hanya saat work order manual | Tidak ada planning sebelum hari servis |
| Service advisor | Tidak | Belum ada role/assignment khusus | Tanggung jawab operasional ambigu |
| Kilometer booking | Tidak | Baru dicatat pada work order | Estimasi awal dan validasi customer belum tersedia |
| Konfirmasi pelanggan | Sebagian | Confirm/reject admin sudah ada | Belum ada flow reschedule/expiry yang lengkap |
| Pembayaran | Ya | Muncul setelah finalisasi work order | Tidak terkait langsung dengan booking awal |
| Riwayat servis | Ya | Customer-scoped | Sudah cukup untuk fase dasar |

## 9. Dampak schema yang mungkin diperlukan

Tidak ada migration yang dijalankan. Untuk implementasi penuh, perubahan additive yang kemungkinan diperlukan:

1. `service_operating_hours` atau konfigurasi terstruktur untuk hari buka, jam buka, jam tutup, dan jeda.
2. `service_holidays`/blackout dates untuk hari libur dan penutupan khusus.
3. `service_slots` atau availability calculator berbasis durasi dan kapasitas. Pilih salah satu pendekatan, jangan membuat dua sumber kebenaran jadwal.
4. Field durasi pada `jenis_servis` atau tabel konfigurasi durasi per layanan.
5. Tabel resource/bay atau field kapasitas harian/slot jika kapasitas tidak seragam.
6. Field preferensi teknisi pada booking, sebaiknya nullable dan dibedakan dari `id_petugas` final pada work order.
7. Entitas/assignment service advisor jika peran tersebut benar-benar dibutuhkan operasional.
8. Field kilometer pada booking hanya jika bisnis ingin meminta odometer saat customer mengajukan booking. Jika tidak, pertahankan satu sumber resmi pada work order.

Constraint dan concurrency harus dirancang bersamaan dengan migration. Validasi controller saja tidak cukup karena dua request bersamaan dapat lolos pada waktu yang sama.

## 10. Rencana implementasi yang disarankan

### Fase 1 — Rapikan integrasi My Garage

- Teruskan ID kendaraan dari index My Garage ke form booking.
- Gunakan `$selectedVehicleId` pada option kendaraan.
- Tampilkan ringkasan kendaraan, nomor polisi, dan kilometer terakhir.
- Tambahkan feature test untuk preselection dan ownership.

### Fase 2 — Definisikan aturan operasional

- Sepakati jam buka, hari libur, interval slot, durasi tiap jenis servis, kapasitas, dan timezone.
- Tentukan apakah kapasitas dihitung per slot, per bay, per teknisi, atau kombinasi.
- Tetapkan aturan reschedule, expiry booking, dan cutoff time.

### Fase 3 — Availability dan pencegahan double booking

- Tambahkan schema additive yang dipilih.
- Buat service availability yang menjadi sumber kebenaran untuk UI dan `store`.
- Tampilkan hanya slot yang valid.
- Ulangi validasi saat submit di dalam transaction/lock yang sesuai.
- Tambahkan test untuk overlap, kapasitas penuh, hari libur, cutoff, dan request bersamaan.

### Fase 4 — Teknisi dan service advisor

- Pisahkan “preferensi teknisi” dari “teknisi yang ditugaskan”.
- Tambahkan availability/spesialisasi hanya bila benar-benar dipakai untuk keputusan slot.
- Definisikan role dan ownership service advisor secara eksplisit.
- Tetap menyimpan assignment final pada work order agar perubahan operasional dapat dicatat.

### Fase 5 — Booking review dan work order

- Pertahankan confirm/reject yang sudah ada.
- Tambahkan informasi slot, estimasi durasi, dan kapasitas pada halaman admin.
- Putuskan apakah work order dibuat otomatis saat confirm atau tetap manual; jangan mencampur dua perilaku.
- Selaraskan label status booking dan work order pada UI member/admin.

### Fase 6 — Pembayaran, reminder, dan history

- Pertahankan transaksi dibuat ketika detail pekerjaan sudah final.
- Hubungkan reminder dengan tanggal servis selesai dan/atau kilometer.
- Pastikan invoice, pembayaran, dan riwayat tetap customer-scoped.

## 11. File yang kemungkinan disentuh pada implementasi berikutnya

Audit ini tidak mengubah file implementasi. Berdasarkan arsitektur sekarang, perubahan berikutnya kemungkinan mencakup:

- `routes/web.php` untuk endpoint availability/reschedule jika diperlukan;
- `app/Http/Controllers/Pelanggan/BookingServisController.php`;
- `app/Http/Requests/StoreBookingServisRequest.php` dan request availability;
- `app/Services/BookingServisService.php` serta service availability baru;
- `app/Services/ServisService.php` untuk handoff/assignment;
- model dan migration booking, jenis servis, resource/slot, teknisi/advisor;
- `resources/views/pelanggan/booking-servis/create.blade.php`;
- `resources/views/pelanggan/kendaraan/index.blade.php` dan `show.blade.php`;
- `resources/views/operations/booking-servis/*` dan `operations/servis/*`;
- `resources/views/pelanggan/servis/*` serta reminder/invoice bila label status berubah;
- CSS/JS member portal bila availability interaktif ditambahkan;
- test feature booking, ownership, availability, concurrency, status, dan payment handoff.

## 12. Bukti dan batasan audit

File utama yang ditinjau meliputi `routes/web.php`, controller Pelanggan/Operations, `BookingServisService`, `ServisService`, model booking/servis/kendaraan/petugas/jenis servis, migration booking/work order/settings, view booking dan My Garage, serta feature test booking dan servis.

Read-only query pada database `yza_automotive_laravel` berhasil membaca jumlah record dan konfigurasi. Query tidak melakukan insert/update/delete. Audit tidak menjalankan migration, seeder, reset database, atau perubahan schema.

Beberapa command inspeksi Laravel standar tidak dapat dipakai penuh karena environment PHP CLI tidak menyediakan extension `intl` dan `php artisan tinker` mencoba menulis history PsySH di luar workspace. Hal ini tidak mengubah kesimpulan karena verifikasi schema/source dan query read-only MySQL tetap berhasil.

**Database Changed: NO**  
**New Database Created: NO**
