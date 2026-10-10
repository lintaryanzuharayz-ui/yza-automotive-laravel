# Tahap 5D — Pembayaran Manual dan Invoice Servis

## Audit Native

Native memiliki tabel pembayaran dengan metode `tunai`, `transfer`, `kredit`, dan `lainnya`; status `menunggu`, `terverifikasi`, dan `ditolak`. Tunai langsung terverifikasi. Metode lainnya menunggu verifikasi. Hanya pembayaran terverifikasi yang dihitung. Pembayaran ditolak tetap menjadi histori. Verifikasi lunas menyinkronkan servis dan booking menjadi selesai. Invoice dirender dinamis dari transaksi dan data servis.

## Implementasi

Migration dan model `Pembayaran` mengikuti schema Native. Baseline 11 pembayaran disalin langsung tanpa recalculation. `PaymentService` mengunci transaksi sebelum menghitung pembayaran sah dan sisa tagihan, membuat pembayaran, memverifikasi, menolak, dan menghitung status transaksi.

Status transaksi dihitung server-side: tanpa pembayaran sah `belum_bayar`, sebagian `dp`, dan mencapai total `lunas`. Overpayment ditolak. Tunai langsung menjalankan recalculation; transfer/kredit/lainnya menunggu verifikasi. Verifikasi dan sinkronisasi servis/booking berada dalam transaction yang sama.

## Invoice

Invoice tidak memakai tabel baru. `InvoiceController` merender transaksi servis, pelanggan, kendaraan, jasa, spare part, subtotal, diskon, total, pembayaran, sisa, dan status. Pelanggan dibatasi ke transaksi miliknya; Admin/Petugas dapat melihat invoice servis sesuai authorization route.

Upload bukti memakai validasi MIME/ukuran Laravel dan penyimpanan disk public dengan nama yang dikelola framework. Tidak ada gateway, Midtrans, Snap, webhook, QRIS, atau VA.

## Test

Test Tahap 5D mencakup baseline, tunai, transfer, verifikasi, reject, multiple payment, DP/lunas, overpayment, forged status/total, replay, ownership invoice, upload, non-service scope, dan isolasi stok. Regression seluruh tahap sebelumnya dijalankan.
