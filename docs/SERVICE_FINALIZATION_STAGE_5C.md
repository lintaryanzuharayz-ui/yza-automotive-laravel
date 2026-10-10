# Tahap 5C — Finalisasi Servis dan Transaksi Servis

## Audit Native

Native `finalize_service()` hanya membuat transaksi servis, mengonsumsi spare part, membuat riwayat stok, dan menghubungkan transaksi ke Work Order. Pembayaran tidak dibuat pada fungsi ini. Status transaksi awal `belum_bayar`; status servis tetap `dikerjakan` dan booking tetap `diproses`. Status selesai dipicu workflow pembayaran berikutnya ketika transaksi menjadi `lunas`.

## Schema dan baseline

Migration `transaksi` mempertahankan kolom, enum, index, foreign key, decimal, dan timestamp Native. Baseline transaksi disalin langsung dari Native tanpa menjalankan finalisasi, mutasi stok, riwayat, atau update servis. Baseline target berisi 7 transaksi, termasuk transaksi servis Native id 4.

## Finalization flow

`ServisService::finalizeService()` menjalankan satu `DB::transaction()`: lock Work Order, validasi status `dikerjakan` dan `id_transaksi` null, mengambil draft spare part dalam urutan `id_sparepart` ascending, menghitung jasa dan spare part dari data server, memanggil InventoryService untuk lock dan konsumsi stok, membuat transaksi `TRX-YYYYMM-8HEX` status `belum_bayar`, lalu mengisi biaya dan `id_transaksi` pada servis. Tidak ada update booking menjadi selesai.

## Inventory dan atomicity

`InventoryService::consumeServicePartsWithinTransaction()` adalah jalur mutasi stok. Method ini tidak membuka transaction baru sehingga tetap menjadi bagian dari transaction finalisasi. Setiap spare part dikunci, stok aktual divalidasi ulang, stok before/after dihitung dari row terkunci, lalu riwayat `jenis_transaksi='servis'` dibuat dengan reference `servis`. Jika item berikutnya gagal atau transaksi gagal, seluruh perubahan rollback.

## Idempotency dan security

Lock Work Order dan validasi `id_transaksi` mencegah double finalize dan replay. Detail jasa dan draft spare part tetap terkunci setelah finalisasi. Controller hanya mengotorisasi Admin/Petugas dan memanggil domain service; pelanggan tidak memiliki route finalisasi. Harga draft dan total dihitung server-side.

## Payment boundary

Tabel/model pembayaran tidak dimigrasikan pada tahap ini karena Native tidak membuat pembayaran di `finalize_service()`. Invoice, Payment Center, dan Midtrans ditunda.

## Test

Test mencakup baseline, finalisasi tanpa/satu/multi spare part, stok aktual, insufficient stock, rollback, riwayat, total jasa, total spare part, snapshot harga, kode transaksi, status, booking sync, double finalize, authorization, relationship, dan payment boundary. Regression suite dijalankan setelahnya.
