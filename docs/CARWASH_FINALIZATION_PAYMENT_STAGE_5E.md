# Tahap 5E — Finalisasi Car Wash + Transaksi + Pembayaran Manual

## Scope

Tahap ini mengimplementasikan finalisasi booking Car Wash, transaksi `cuci_mobil`, pembayaran manual, sinkronisasi status ketika lunas, dan invoice dinamis. Tidak ada Midtrans, gateway, membership, points, promo, penjualan, trade-in, laporan baru, atau mutasi stok.

## Finalisasi

`BookingCuciService::finalize()` berjalan dalam satu database transaction. Booking dikunci dengan `lockForUpdate()`, hanya status `proses` tanpa `id_transaksi` yang diterima, dan `booking_cuci.harga` disalin ke `subtotal` serta `total` transaksi. Transaksi dibuat dengan status `belum_bayar`, lalu booking dihubungkan dengan transaksi dan petugas.

Finalisasi tidak mengubah booking menjadi `selesai`. Double finalize ditolak oleh validasi status dan `id_transaksi`, termasuk pada eksekusi berulang setelah transaksi pertama berhasil.

## Pembayaran

`PaymentService` yang sama digunakan untuk transaksi `servis` dan `cuci_mobil`.

- Tunai langsung `terverifikasi`.
- Transfer, kredit, dan lainnya `menunggu`.
- Hanya pembayaran masuk yang terverifikasi yang dihitung.
- Overpayment ditolak.
- Pembayaran dapat dilakukan berkali-kali.
- Pembayaran ditolak tetap tersimpan sebagai histori.
- Verifikasi mengunci pembayaran dan transaksi serta menghitung ulang status.

Ketika transaksi Car Wash menjadi `lunas`, booking yang masih `proses` diubah menjadi `selesai` dalam transaction yang sama.

## Invoice

Invoice Car Wash dirender dinamis dari relasi transaksi, booking, pelanggan, kendaraan, paket, dan pembayaran. Pelanggan hanya dapat melihat invoice miliknya sendiri. Admin dan Petugas dapat melihat invoice operasional.

## Test

Suite khusus Tahap 5E mencakup:

- finalisasi valid dan invalid;
- proteksi double finalize;
- harga snapshot;
- transaksi `cuci_mobil` dan status awal `belum_bayar`;
- seluruh metode pembayaran Native;
- pending, reject, verify, multiple payment, dan overpayment;
- sinkronisasi `proses` ke `selesai` hanya ketika lunas;
- ownership invoice dan IDOR;
- customer mutation boundary;
- isolasi stok;
- regression behavior pembayaran servis.

Hasil test khusus: **8 passed, 26 assertions**.

Hasil seluruh regression: **165 passed, 394 assertions**.
