# YZA Automotive — Sparepart & Inventory Fitment Refinement

Tanggal audit: 13 Oktober 2026  
Ruang lingkup: master sparepart, stok masuk, histori mutasi, fitment kendaraan, dan integrasi Work Order.

## Keputusan

Implementasi aman untuk dipasang setelah migration metadata dijalankan pada environment aplikasi. Data native tidak diubah selama audit ini.

Untuk Work Order native `13822`, keputusan bisnis tetap **BELUM AMAN dikirim/diteruskan** untuk Filter Oli `YZA-FLT-OIL01`: stok master saat audit adalah `0` dengan minimum `5`, dan belum ada bukti fitment Toyota GR Supra 2024 / mesin `YZA-GRSUPRA-B58-001`.

## Perubahan yang dibuat

- Menambah metadata master sparepart: nomor OEM, nomor aftermarket, spesifikasi, supplier, dan waktu pembaruan harga.
- Menambah tabel `sparepart_fitments` dengan status `verified_fit`, `unverified`, dan `verified_not_fit`, termasuk sumber bukti, verifier, tanggal verifikasi, rentang tahun, model, varian, serta kode mesin.
- Menambah panel admin untuk mengelola fitment dan membaca histori mutasi stok per sparepart.
- Menambah indikator dashboard “Perlu Review Fitment”.
- Mempertahankan saldo stok read-only pada form master; stok tetap hanya berubah melalui penerimaan atau penyesuaian resmi `InventoryService`.
- Menahan pencatatan sparepart dan pembuatan approval baru bila kendaraan memiliki identitas mesin tetapi fitment belum terverifikasi atau dinyatakan tidak cocok.
- Menahan penerapan approval saat customer menyetujui ulang bila part sudah tidak cocok.
- Menjaga kendaraan legacy tanpa nomor mesin tetap dapat dibaca dan tidak dipaksa memiliki data fitment baru.

## Audit data native

- Sparepart `YZA-FLT-OIL01`: aktif, stok `0`, minimum `5`, harga beli `Rp45.000`, harga jual `Rp65.000`.
- Kendaraan pada WO 13822: Toyota GR Supra 2024, nomor mesin `YZA-GRSUPRA-B58-001`.
- WO 13822 tetap dikerjakan dan tidak diubah.
- Approval lama, transaksi, pembayaran, dan histori stok native tidak diubah.
- Klarifikasi approval yang sudah ada tetap menjadi blocker sampai approval revisi yang sah disetujui.

## Verifikasi

- PHP lint untuk controller dan service terkait: lulus.
- Blade cache: lulus.
- Migration baru berhasil dijalankan pada database testing, bukan database native.
- Regression terfokus: `40 passed, 2 skipped, 145 assertions`.
- Test mencakup approval, QC/finalisasi, konsumsi stok, proteksi master sparepart, dan wizard Work Order.

## Catatan deployment

Jalankan migration baru pada database target melalui prosedur deployment resmi sebelum fitur fitment diaktifkan. Setelah itu, isi fitment `verified_fit` berdasarkan sumber OEM/supplier yang dapat diaudit; jangan menandai Filter Oli untuk WO 13822 sebagai cocok sebelum stok dan bukti kompatibilitas tersedia.
