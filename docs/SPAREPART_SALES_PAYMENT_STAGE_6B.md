# Tahap 6B — Pembayaran Manual + Invoice Penjualan Spare Part

## Audit Native

Native memakai payment engine bersama untuk transaksi `penjualan_sparepart`. Tunai langsung berstatus `terverifikasi`; transfer, kredit, dan lainnya berstatus `menunggu`. Hanya pembayaran `arah = masuk` dan `status = terverifikasi` yang dihitung sebagai pembayaran sah. Pembayaran ditolak tetap disimpan.

Pembayaran dapat dilakukan oleh Admin dan Petugas. Portal pelanggan tidak memiliki mutation pembayaran. Pembayaran Native menggunakan transaction lock, mendukung multiple payment, dan menolak overpayment untuk pembayaran yang langsung terverifikasi. Bukti pembayaran memakai upload validation yang sama dengan flow existing.

## Historical relation

Target memiliki 2 historical payment yang terkait dengan 2 transaksi `penjualan_sparepart`. Relationship transaksi ke pembayaran tetap valid dan baseline tidak diproses ulang.

## Status dan completion

Status transaksi dihitung dari database:

- verified 0: `belum_bayar`
- verified lebih dari 0 dan kurang dari total: `dp`
- verified sama dengan total: `lunas`

Audit Native menemukan bahwa `penjualan_sparepart` sudah berstatus `selesai` ketika finalisasi Tahap 6A selesai. Karena itu pembayaran tidak mengubah status penjualan lagi. PaymentService tetap mengunci relationship penjualan saat transaksi lunas sebagai completion boundary, tanpa menciptakan transisi baru yang berbeda dari Native.

## PaymentService reuse

`PaymentService` yang sama sekarang menerima `servis`, `cuci_mobil`, dan `penjualan_sparepart`. Tidak ada payment engine khusus spare part. Lock transaksi, perhitungan verified amount, overpayment validation, verify, reject, dan status calculation tetap shared.

## Walk-in dan ownership

Transaksi dengan `id_pelanggan = null` dapat dibayar oleh staff dan invoice staff menampilkan label `Walk-in`. Invoice Walk-in tidak dapat diakses akun pelanggan. Invoice transaksi pelanggan hanya dapat diakses pelanggan pemilik, sedangkan Admin dan Petugas dapat mengakses invoice operasional.

## Invoice

Invoice dirender dinamis tanpa tabel baru dari:

- `penjualan_sparepart`
- `detail_penjualan_sparepart`
- `sparepart`
- `transaksi`
- `pembayaran`
- `pelanggan` nullable
- `petugas`

Harga invoice berasal dari snapshot `detail_penjualan_sparepart.harga_satuan`, bukan harga master terbaru.

## Locking, rollback, concurrency

Create payment mengunci transaksi dengan `lockForUpdate()`. Verify mengunci payment dan transaksi. Payment kedua tidak dapat menyebabkan verified amount melebihi total. Update transaction dan completion boundary berada dalam database transaction. Pembayaran tidak menyentuh `sparepart.stok` atau `riwayat_stok`.

## Security

Mutation pembayaran berada di area role Admin/Petugas dengan CSRF middleware. Nilai total, status, pelanggan, petugas, verifier, dan paid amount tidak dipercaya dari request. Invoice customer menggunakan ownership check untuk mencegah IDOR. Upload bukti memakai validation existing.

## Test dan regression

Test Tahap 6B mencakup historical relation, cash, transfer, kredit, lainnya, pending, verify, reject, multiple payment, overpayment, Walk-in invoice, ownership, stock isolation, dan no-gateway boundary.

Hasil test khusus: **10 passed, 39 assertions**.

Hasil seluruh regression: **187 passed, 475 assertions**.

Regression penuh dan flow Servis, Car Wash, serta Spare Part diverifikasi setelah perubahan.

Payment gateway, Midtrans, membership, points, promo, penjualan mobil, trade-in, used car, dan laporan baru tetap ditunda.
