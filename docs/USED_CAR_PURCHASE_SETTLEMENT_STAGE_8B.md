# Tahap 8B — Settlement Pembelian Mobil Bekas

## Audit Native

Native memisahkan tiga langkah:

1. Setelah penawaran diterima, create_used_purchase membuat pembelian_mobil_bekas dan transaksi pembelian_mobil dengan status belum_bayar.
2. Pembayaran keluar menggunakan arah=keluar; tunai langsung terverifikasi, sedangkan transfer/kredit/lainnya menunggu. Hanya pembayaran keluar terverifikasi yang dihitung.
3. finalize_used_purchase adalah aksi terpisah setelah transaksi lunas. Aksi ini memasukkan unit ke inventory, mengubah pembelian dan pengajuan menjadi selesai, serta menandai kendaraan pelanggan sebagai dijual bila masih aktif.

Native baseline yang disalin: satu pembelian, transaksi id 6, dan dua pembayaran keluar tunai terverifikasi sebesar Rp40.000.000 dan Rp60.000.000. trade_in tidak dibuat.

## Implementasi Laravel

- pembelian_mobil_bekas mengikuti schema Native dan memiliki relasi ke pengajuan, inspeksi, pelanggan, petugas, mobil, dan transaksi.
- Harga transaksi selalu diambil dari inspeksi_mobil.harga_penawaran; harga client tidak dipercaya.
- OutgoingPaymentService dipisahkan dari PaymentService pembayaran masuk karena arah pembayaran dan completion behavior berbeda. PaymentService existing untuk Servis, Car Wash, Penjualan Spare Part, dan Penjualan Mobil tidak diubah.
- Semua payment dan finalisasi mengunci transaksi/pembelian pada database transaction. Overpayment diverifikasi ulang dari database.
- Pembayaran lunas tidak otomatis memasukkan unit ke inventory; finalisasi eksplisit tetap diperlukan sesuai Native.
- Inventory dibuat sebagai bekas, status tersedia, harga_beli snapshot pembelian, harga_jual nol, dan sumber pembelian_bekas.
- Bukti pembelian dirender dinamis dan pelanggan hanya dapat melihat pembelian miliknya. Admin/Petugas dapat melihatnya melalui route operasi.

## Batasan

Tidak ada trade_in, Midtrans, payment gateway, Membership, YZA Points, Promo, laporan baru, atau perubahan stok spare part.

## Verifikasi

Test khusus Tahap 8B mencakup baseline, server-side price, duplicate purchase/finalize, outgoing payment states, verified sum, overpayment, rollback transaction boundaries, explicit inventory finalization, authorization, stock isolation, dan no-gateway guard. Regression suite Tahap 8A dan seluruh suite project dijalankan setelah implementasi.
