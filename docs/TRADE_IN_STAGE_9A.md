# Tahap 9A — Trade-In / Tukar Tambah

## Audit Native dan schema

Native memiliki satu tabel trade_in dengan field: id_trade_in, kode_trade_in, id_transaksi, id_pelanggan, id_pengajuan, id_inspeksi, id_mobil_lama_masuk, id_mobil_dibeli, id_petugas, nilai_mobil_lama, harga_mobil_dibeli, diskon, selisih_bayar, tanggal, status, catatan, dan timestamps. Status persisnya proses, selesai, dibatalkan. Kode dan transaksi unik; foreign key mengarah ke transaksi, pelanggan, pengajuan, inspeksi, mobil lama, mobil dealer, dan petugas.

Baseline Native: 1 row (TRD-202609-032CEF4C), transaksi 7, pengajuan 2, inspeksi 2, mobil dealer 7, mobil lama 8, nilai lama Rp50.000.000, harga dealer Rp80.000.000, selisih Rp30.000.000, status selesai.

## Model bisnis Native

Pelanggan memulai Trade-In setelah pengajuan mobil bekas berada pada penawaran_diberikan. Inspeksi dan penawaran wajib ada. Pelanggan memilih mobil dealer yang tersedia dan memiliki harga jual positif. Native tidak membuat penjualan_mobil maupun pembelian_mobil_bekas untuk Trade-In; Trade-In berdiri sebagai domain summary dengan satu transaksi trade_in.

Formula persis:

selisih_bayar = max(0, harga_mobil_dibeli - diskon - nilai_mobil_lama)

Transaksi menggunakan total selisih. Tidak ada netting tambahan, transaksi ketiga, leasing, cicilan, atau kewajiban payout otomatis kepada pelanggan. Diskon dibuat oleh operation Native dan divalidasi tidak negatif serta tidak melebihi harga dealer.

## Lifecycle

| Kondisi | Aksi Native |
|---|---|
| penawaran_diberikan | Trade-In dibuat, transaksi trade_in dibuat belum_bayar, mobil dealer menjadi dibooking |
| proses + transaksi belum_bayar | Boleh dibatalkan; transaksi menjadi dibatalkan, mobil dealer kembali tersedia |
| proses + transaksi lunas | Finalisasi eksplisit |
| Finalisasi sukses | Mobil lama dibuat ke inventory trade_in, mobil dealer terjual, pengajuan selesai, kendaraan pelanggan aktif menjadi dijual, Trade-In selesai |
| selesai/dibatalkan | Tidak dapat diulang |

Completion predicate Native adalah transaksi Trade-In lunas, Trade-In masih proses, dan mobil dealer masih dibooking. Tidak ada predicate sale/purchase terpisah.

## Payment dan inventory

Pembayaran memakai PaymentService incoming existing dengan arah masuk. Tunai langsung terverifikasi; metode non-tunai menunggu verifikasi. Pembayaran keluar tidak dihitung. Overpayment tetap dilindungi oleh service existing. Trade-In tidak mengubah spare part atau riwayat_stok.

Finalisasi membuat mobil lama dengan field pengajuan, harga_beli nilai mobil lama, harga_jual nol, kondisi bekas, status tersedia, sumber trade_in. Tidak ada duplicate inventory. Mobil dealer mengikuti lifecycle Trade-In Native dan menjadi terjual.

## Locking, atomicity, dan security

Create lock order: pengajuan → inspeksi → mobil dealer → transaksi/trade-in creation. Finalize lock order: trade-in → transaksi → pengajuan → mobil dealer → duplicate inventory check. Cancel lock order: trade-in → transaksi → mobil dealer. Semua multi-write berada dalam database transaction.

Customer identity berasal dari session dan hanya dapat memilih pengajuan miliknya. Customer tidak dapat memalsukan pelanggan, nilai, diskon, selisih, status, petugas, transaksi, atau mobil dealer yang tidak tersedia. Admin/Petugas mengakses operasi finalisasi dan cancel; customer hanya membuat/melihat Trade-In miliknya. Semua mutation HTTP memakai auth dan CSRF.

## Dokumen dan integration boundary

Invoice Trade-In dirender dinamis dari Trade-In, transaksi, pembayaran, pengajuan, dan mobil. Tidak ada tabel invoice baru. Tidak ada integrasi sale/purchase domain karena Native memang tidak membuat relasi tersebut. Booking mobil tidak digunakan pada create Trade-In Native. Tidak ada reversal/refund di luar cancel sebelum pembayaran.

## Files changed

- database/migrations/2026_09_30_170000_create_trade_in_table.php
- app/Models/TradeIn.php, app/Models/Transaksi.php
- app/Services/TradeInService.php, app/Services/PaymentService.php
- app/Http/Requests/StoreTradeInRequest.php
- app/Http/Controllers/Pelanggan/TradeInController.php
- app/Http/Controllers/OperationsTradeInController.php
- app/Http/Controllers/TradeInInvoiceController.php
- routes/web.php
- Trade-In views dan tests/Feature/TradeInStage9ATest.php

## Deferred

Midtrans, Snap, webhook, Membership, YZA Points, Promo, leasing, cicilan, dan laporan baru tetap di luar scope.
