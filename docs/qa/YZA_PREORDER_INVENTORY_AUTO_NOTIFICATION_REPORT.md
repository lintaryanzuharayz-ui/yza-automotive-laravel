# Pre-order, inventori dan pemberitahuan member otomatis

Tanggal: 8 November 2026. Project: `C:\xampp\htdocs\yza_automotive_laravel`.

## Status dan batas penerapan

Implementasi dan pengujian dilakukan di workspace dan database terpisah `yza_automotive_laravel_test`. **Migrasi baru tidak diterapkan pada database operasional**, sesuai instruksi pemilik. Tidak membuat transaksi operasional, mengirim pemberitahuan operasional, commit, push atau deploy.

Fitur outbox/riwayat otomatis baru membutuhkan migrasi `2026_11_08_190000_add_preorder_inventory_delivery_outbox.php` pada tahap penerapan yang diotorisasi nanti. Aplikasi mendeteksi keberadaan tabel: jika belum tersedia, tidak mencoba menulis outbox atau kolom state baru. Popup menjelaskan pemberitahuan otomatis belum diaktifkan. Pemeriksaan stok dan tautan pengelolaan tetap berfungsi.

Pemeriksaan schema operasional read-only mengonfirmasi tabel outbox dan kolom state baru belum terpasang (`preorder-operational-schema-readonly.json`).

## Audit alur sebelumnya

- `MemberVehiclePreorder` / `pre_order_mobil_member` menyimpan pemilik, relasi mobil opsional, `product_key`, slug, snapshot nama/varian/tahun/warna/harga referensi, status, waktu notifikasi, pembatalan dan pesanan.
- Status existing: menunggu_stok, stok_tersedia, notifikasi_terkirim, dilanjutkan_ke_checkout, dibatalkan. Tidak menambah status bebas atau mengubah kontrak checkout.
- `VehicleInventoryService` memiliki pencatatan mutasi nyata dalam `vehicle_inventory_histories`; kapasitas dihitung dari stock_total dikurangi sold_units dan reservasi aktif oleh booking/order/penjualan yang relevan. Checkout mengunci kendaraan/inventori dan menolak stok habis.
- Hook restock lama ada di controller kendaraan, tetapi pencocokan menggunakan product_key saja, tanpa seluruh spesifikasi, membership atau retry persisten. Service stok langsung tidak memiliki durable delivery event.
- `StaffNotificationService` sudah memakai afterCommit untuk pemberitahuan staf, tetapi bukan outbox stok pre-order yang dapat dicoba ulang setelah kegagalan. Karena itu satu tabel outbox/activity ditambahkan; tidak membuat form stok kedua, transport notifikasi kedua, atau event bus baru.
- `Notifikasi`, `NotificationStateService`, `NotificationDestinationService` dan polling existing menjadi saluran lonceng/unread/read. Waktu baca terperinci tidak disimpan oleh sistem existing.
- `ActiveMemberCustomerService` memvalidasi akun/pelanggan aktif dan membership dengan bukti aktivasi sah, termasuk kebijakan legacy yang sudah ada.
- Tidak ditemukan alokasi FIFO/antrean eksklusif untuk pre-order. Tidak menambah kebijakan alokasi baru.

## Popup dan tautan stok resmi

Empat tab dipertahankan: Detail Permintaan, Ketersediaan Stok, Tindak Lanjut, Riwayat. Tinggi mengikuti isi, dengan batas viewport, scroll isi, header/footer tetap rapi, tampilan ponsel dan status loading/error. Hasil stok mencakup spesifikasi permintaan, unit eligible, reservasi serta waktu pemeriksaan.

`Kelola Stok Mobil Ini` membuka `/admin/mobil` dengan kode mobil, `stock_mobil=<PK>` dan konteks `preorder=<PK>`. Halaman tersebut membuka `openVehicleActionModal(..., 'stock')` yang sudah ada. Tautan kembali memakai `review=<PK>`, memfilter pre-order terkait dan membuka lagi popup. Tidak ada form stok baru.

Jika belum ada unit authoritative yang sesuai, tautan membuka pencarian inventori tanpa memilih unit warna lain. Admin diberi penjelasan untuk menambah/menautkan unit asli, bukan menambah stok untuk kendaraan yang keliru. Akses tautan memerlukan inventory.view dan inventory.manage; endpoint stok memerlukan permission backend, akun aktif dan bukan Owner.

Tombol pemberitahuan dibatasi oleh hasil stok, membership, lifecycle dan izin. Kirim ulang perlu kode konfirmasi serta alasan minimal 10 karakter yang tercatat; dibatasi sekali per periode ketersediaan. Pengulangan request tidak membuat notifikasi baru. Tidak ada pilihan status lunas/selesai/ownership dari popup.

## Pencocokan authoritative dan eligibility

- Relasi ID mobil terverifikasi menjadi sumber group key inventori existing. Kandidat harus memiliki ID sama atau group key identik. Untuk pre-order katalog tanpa ID, wajib ada binding `showcase_slug` eksplisit dan product_key sesuai.
- Nama tampilan saja tidak pernah cukup untuk menentukan kandidat. Setelah relasi terbukti, snapshot model, varian, tahun dan warna harus sama; transmisi, bahan bakar, kondisi dan limited-edition yang diketahui juga harus sama.
- Warna galeri bukan bukti stok. Unit yang tidak memiliki VIN/nomor mesin, transmisi, bahan bakar, harga positif, atau berstatus bukan tersedia tidak boleh memicu pemberitahuan.
- Stok eligible menggunakan service availability dan reservasi existing. Stok agregat tetap mengikuti kebijakan kapasitas existing; tidak menyatakan semua angka agregat sebagai VIN tersendiri. Tidak membuat identitas fisik fiktif.
- Referensi legacy yang tidak terhubung secara authoritative tetap menunggu verifikasi, meskipun nama mobil mirip.

## Outbox, commit, retry dan duplikasi

`PreorderInventoryNotificationService` mengamati perubahan kendaraan dan kapasitas reservasi/penjualan. Mutasi resmi memasukkan event dengan kunci `inventory-history:<PK>` dalam transaksi yang sama dengan riwayat stok. Perubahan model/status kendaraan memiliki event tersendiri.

Pemrosesan dimulai melalui afterCommit. Jika proses mati setelah commit atau pemrosesan gagal, event pending/failed tetap ada dan bisa dicoba ulang. Command `preorders:deliver-inventory-events` dijadwalkan setiap menit dengan withoutOverlapping. Command ini juga melanjutkan penerima yang sebelumnya tertunda karena akun/membership, setelah eligible kembali. Scheduler Laravel harus berjalan pada tahap penerapan; tidak diaktifkan pada OS operasional dalam pekerjaan ini.

Pemroses mengunci event dan pre-order, menghitung ulang stok dan mencatat hasil. State `stock_available`, `stock_generation`, `matched_mobil_id`, `stock_checked_at` membedakan periode tidak tersedia → tersedia. Pemberitahuan pertama per periode memiliki unique key `preorder:<PK>:availability:<generation>`. Notification row, state dan delivery ledger disimpan atomik; rollback/retry tidak menggandakan pesan. Notifikasi legacy pertama dapat diadopsi tanpa mengubah status bacanya. Periode ketersediaan yang benar-benar baru boleh menghasilkan pemberitahuan baru.

Hanya akun pemilik pre-order yang eligible menerima pesan, berisi nama/varian/tahun/warna/kode pre-order dan penjelasan bahwa stok dapat berubah serta tidak direservasi. Link dari lonceng diperiksa berdasarkan pemilik dan stock match saat dibuka: menuju detail kendaraan member yang benar, atau daftar pre-order jika stok sudah habis/permintaan ditutup/membership tidak eligible.

## Stok terbatas dan transaksi

Beberapa member yang minatnya cocok boleh diberi tahu tentang satu unit. Tidak ada alokasi FIFO atau reservasi otomatis. Notifikasi tidak membuat SPK, invoice, pembayaran, penjualan, pengurangan stok atau ownership. Checkout existing tetap memvalidasi membership, harga final, promo, reservasi, stock dan authorization di server. Harga pre-order tetap referensi.

## Riwayat

Stream menyimpan kejadian nyata sejak fitur aktif: pembuatan pre-order, pemeriksaan/cocok-tidaknya stok, kaitan event mutasi inventori, perubahan status/ID pesanan, pemberitahuan, alasan kirim ulang, aktor dan waktu. Status baca ditampilkan dari row notifikasi yang benar; tidak mengarang waktu baca. Catatan lama tanpa event tidak diberi timeline buatan.

Referensi pada stream sengaja tidak memakai cascade delete agar penghapusan sumber tidak menghapus histori. Jika akun aktor sudah tidak tersedia, ID petugas tetap ditampilkan; tidak diganti menjadi Admin lain atau Sistem. Pemeriksaan GET tetap read-only; tombol cek ulang menggunakan POST berizin untuk mencatat aktivitas.

## Validasi

- Focused awal: 20 tests / 124 assertions PASS.
- Setelah pelengkapan riwayat mutasi: 22 tests / 133 assertions PASS (`storage/logs/preorder-auto-posthistory.txt`).
- Domain, authorization, notifikasi, member-only dan inventori: 82 tests / 601 assertions PASS (`storage/logs/preorder-auto-domain.txt`).
- Tambahan terbaru: 13 tests / 73 assertions PASS (`storage/logs/preorder-auto-final-focused.txt`), termasuk rollback sebelum commit, retry, banyak member/satu stok, siklus restock baru, stok reserved, tujuan notifikasi, read/unread dan larangan membaca notifikasi orang lain.
- Regresi awal: 732 tests / 4286 assertions PASS (`storage/logs/preorder-auto-regression.txt`). Regresi final: **734 tests / 4296 assertions PASS, 0 errors/failures/skipped**, lihat `storage/logs/preorder-auto-regression-final.txt` dan XML terkait.
- Seluruh JavaScript existing: 29 PASS. Syntax JS/PHP dan scoped diff check PASS. Frontend build PASS (`storage/logs/preorder-auto-build.txt`). Kompilasi seluruh Blade PASS.
- Browser menggunakan halaman asli yang dirender dari data testing dalam transaksi rollback; pengiriman operasional diblokir. Desktop dan 393×852: stok kosong, tombol notify disabled, empat tab, riwayat nyata, buka modal stok terkait dan kembali ke popup berhasil. File HTML QA sementara dihapus setelah pemeriksaan.

Bukti: `preorder-auto-stock-desktop.png`, `preorder-auto-stock-mobile.png`, `preorder-auto-inventory-link.png` dalam direktori ini. Gambar menggunakan data QA, bukan perubahan operasional.

## Sisa aktivasi / keputusan

1. Terapkan migrasi baru hanya melalui penerapan yang diotorisasi, lalu pastikan scheduler berjalan. Belum dilakukan karena instruksi melarang perubahan DB operasional/deployment.
2. Data legacy tanpa ID/binding katalog authoritative perlu ditautkan melalui inventori resmi oleh petugas. Sistem sengaja tidak menebak dari nama. Tidak ada backfill otomatis yang mengubah permintaan member.
3. Tidak ada keputusan bisnis tambahan yang dipaksakan: prioritas/antrean eksklusif dan jaminan harga tetap di luar kebijakan existing.
