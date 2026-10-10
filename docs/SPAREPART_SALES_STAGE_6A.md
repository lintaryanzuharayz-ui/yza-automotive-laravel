# Tahap 6A — Penjualan Spare Part + Finalisasi Inventory + Transaksi

## 1. Audit Native

Native memiliki dua tabel baru untuk domain ini: `penjualan_sparepart` dan `detail_penjualan_sparepart`. Baseline Native berisi 2 penjualan dan 2 detail.

Status penjualan adalah `draft`, `proses`, `selesai`, dan `dibatalkan`; status awal adalah `draft`. Native membuat transaksi bersamaan dengan pembuatan draft dengan jenis `penjualan_sparepart` dan status `draft`. Saat finalisasi, transaksi diperbarui menjadi `belum_bayar`. Pembayaran dilakukan terpisah melalui payment flow yang sudah ada. Tidak ada pembayaran atau invoice record yang dibuat oleh finalisasi.

Pelanggan bersifat opsional; Native mendukung transaksi Walk-in. Admin dan Petugas dapat membuat serta memproses penjualan. Portal pelanggan tidak memiliki mutation penjualan.

## 2. Schema dan Baseline

Migration Laravel mempertahankan nama tabel, primary key, foreign key, index, unique constraint, enum, decimal, nullable, default, dan timestamp Native.

Baseline disalin tanpa menjalankan finalization logic:

- Native `penjualan_sparepart`: 2 row
- Laravel `penjualan_sparepart`: 2 row
- Native `detail_penjualan_sparepart`: 2 row
- Laravel `detail_penjualan_sparepart`: 2 row

Tidak ada pengurangan stok, riwayat baru, transaksi baru, atau pembayaran baru saat copy baseline.

## 3. Draft dan Harga

Spare part yang sama digabung pada satu detail dengan menambah quantity, sesuai unique constraint Native. Quantity harus positif dan availability check dilakukan terhadap stok saat draft diubah.

Harga diambil dari `sparepart.harga_jual` oleh server dan disimpan pada detail. Subtotal detail dihitung server-side. Perubahan master harga setelah item masuk draft tidak mengubah snapshot detail tersebut. Jika detail diedit, Native mengambil harga master terbaru saat edit.

Draft tidak mengubah stok.

## 4. Finalisasi dan Inventory

`PenjualanSparepartService::finalize()` menjalankan satu database transaction:

1. lock penjualan;
2. validasi status `draft` dan transaksi terkait masih `draft`;
3. lock detail berurutan berdasarkan `id_sparepart`;
4. hitung ulang subtotal, diskon, dan total dari detail;
5. update penjualan dan transaksi menjadi `belum_bayar`;
6. lock seluruh spare part dalam urutan ID yang konsisten;
7. validasi stok aktual;
8. kurangi stok melalui `InventoryService`;
9. buat `riwayat_stok`;
10. ubah penjualan menjadi `selesai`;
11. commit.

Kegagalan pada salah satu langkah me-rollback seluruh perubahan. Riwayat memakai `jenis_transaksi = penjualan`, `referensi_tipe = penjualan_sparepart`, dan `referensi_id` penjualan. Stock warning memakai mekanisme notifikasi InventoryService yang sudah ada.

## 5. Transaction dan Payment Boundary

Transaksi menggunakan format generator `TRX-YYYYMM-XXXXXXXX`, jenis `penjualan_sparepart`, total server-side, diskon nol, dan status awal final `belum_bayar`.

Tahap 6A tidak membuat pembayaran, memverifikasi pembayaran, invoice workflow, Midtrans, atau gateway. Pembayaran dan invoice didefer ke Tahap 6B.

## 6. Authorization dan Security

Admin dan Petugas memiliki akses operasi penjualan. Pelanggan tidak memiliki route mutation. Detail selalu diproses melalui service yang memeriksa parent draft, sehingga detail dari penjualan lain tidak dapat diedit atau dihapus melalui IDOR. CSRF menggunakan middleware Laravel. Status, harga, subtotal, total, dan stok tidak dipercaya dari request.

## 7. Concurrency dan Rollback

Double finalize ditolak oleh row lock, status validation, dan transaksi draft. Dua draft yang bersaing atas stok yang sama tidak dapat sama-sama berhasil; finalisasi kedua melakukan revalidasi stok aktual setelah lock.

Kegagalan insufficient stock dan kegagalan multi-item tidak meninggalkan pengurangan stok atau riwayat parsial.

## 8. Test dan Regression

Test Tahap 6A mencakup baseline, draft, Walk-in, quantity, inactive spare part, duplicate merge, price snapshot, edit/delete draft, finalisasi, stok, riwayat, transaksi, payment boundary, double finalize, concurrent-like finalize, competing stock, rollback, authorization, dan no-gateway boundary.

Hasil test khusus: **12 passed, 42 assertions**.

Hasil seluruh regression: **177 passed, 436 assertions**.

## 9. Deferred Dependencies

Payment manual, verifikasi pembayaran, invoice dinamis, dan integrasi gateway tetap ditunda ke Tahap 6B setelah persetujuan terpisah.
