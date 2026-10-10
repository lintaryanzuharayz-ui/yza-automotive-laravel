# Tahap 7A — Penjualan Mobil + Transaksi

## Hasil audit Native

- `penjualan_mobil` memiliki satu relasi unik ke `transaksi`, satu unit `mobil`, pelanggan, petugas, dan booking opsional.
- Penjualan dibuat langsung sebagai row `status=proses` dan transaksi dibuat bersamaan dengan `status=belum_bayar`.
- Harga berasal dari `mobil.harga_jual` dan disimpan ke `harga_mobil`; diskon divalidasi server-side dan total adalah harga dikurangi diskon.
- Unit `tersedia` dapat dijual langsung. Unit `dibooking` hanya dapat dijual kepada pelanggan yang cocok dengan booking berstatus `dikonfirmasi`.
- Setelah penjualan dibuat, unit menjadi `dibooking`.
- Pembayaran dilakukan terpisah. Status penjualan, unit menjadi `terjual`, dan booking menjadi `selesai` baru disinkronkan ketika transaksi menjadi `lunas`; itu ditunda ke Tahap 7B.
- Native tidak membuat pembayaran pada pembuatan penjualan dan tidak memakai payment gateway pada flow ini.

## Implementasi Laravel

- Migration dan baseline `penjualan_mobil`.
- Model `PenjualanMobil` dan relasi dengan transaksi, mobil, pelanggan, petugas, serta booking.
- Service atomik dengan `lockForUpdate()` pada unit dan booking, validasi status, snapshot harga, diskon, DP, booking ownership, dan rollback.
- Operasi admin/petugas untuk membuat, melihat, serta mengelola penjualan.
- Pelanggan tidak memiliki mutation route.
- Tidak ada perubahan pada `pembayaran`, invoice, Midtrans, stok spare part, atau riwayat stok.

## Verifikasi

- Test Tahap 7A: 4 test, 15 assertion — PASS.
- Regression suite: 191 test, 490 assertion — PASS.
- Baseline Native: 1 row `penjualan_mobil` tersalin ke Laravel.
- Baseline tidak membuat pembayaran dan tidak mengubah status unit saat import.

Tahap 7A berhenti pada transaksi awal `belum_bayar`. Payment, completion sync, dan invoice tetap menjadi Tahap 7B.
