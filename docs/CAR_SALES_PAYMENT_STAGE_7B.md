# Tahap 7B — Pembayaran Manual + Penyelesaian Penjualan Mobil + Invoice

## Audit Native

- Historical payment penjualan mobil: 2 row, keduanya `arah=masuk`, metode `tunai`, dan `terverifikasi`.
- Pembayaran tunai langsung terverifikasi; transfer, kredit, dan lainnya menunggu verifikasi.
- Field `penjualan_mobil.dp` adalah nilai DP/rencana pada dokumen penjualan, bukan payment otomatis. Payment aktual tetap berasal dari tabel `pembayaran`.
- Kredit tidak memiliki leasing, tenor, bunga, atau cicilan tambahan.
- Hanya payment masuk dan terverifikasi yang dihitung.
- Status transaksi mengikuti verified total: `belum_bayar`, `dp`, `lunas`.
- Payment ditolak tetap menjadi histori dan tidak dihitung.
- Saat lunas: penjualan menjadi `selesai`, mobil menjadi `terjual`, dan booking terkait menjadi `selesai`.
- Direct sale tanpa booking dapat selesai tanpa mengakses booking null.
- Invoice Native dinamis, memuat kode, pelanggan, transaksi, unit, harga snapshot, diskon, total, pembayaran, sisa, dan status.

## Implementasi

- PaymentService existing diperluas untuk `penjualan_mobil`; tidak ada payment engine kedua.
- Semua create/verify/reject menggunakan transaction dan row locking.
- Completion memakai relationship `penjualan_mobil.id_mobil` dan `id_booking`, bukan nilai request.
- Completion dijalankan dalam transaction pembayaran yang sama dan aman terhadap replay.
- Invoice dinamis ditambahkan untuk Admin, Petugas, dan pelanggan pemilik transaksi.
- Upload bukti tetap memakai mekanisme existing dengan storage aman dan validasi request.
- Tidak ada Midtrans, gateway, leasing, cicilan otomatis, membership, points, promo, trade-in, atau pembelian mobil bekas.

## Verifikasi

- Test Tahap 7B: 11 test, 28 assertion — PASS.
- Regression sebelumnya tetap dijalankan setelah perubahan.
- Payment tidak mengubah `sparepart.stok` atau `riwayat_stok`.
- Native tetap read-only.

## Deferred

Tidak ada dependensi tambahan. Tahap berikutnya hanya diperlukan bila ingin mengembangkan fitur di luar scope ini.
