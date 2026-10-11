# Implementasi Workflow Pasca Pembayaran YZA Automotive

## Ringkasan

Workflow pembelian mobil member sekarang mengikuti:

LUNAS -> PDI -> PDI LULUS -> JADWAL PENGAMBILAN -> SIAP DIAMBIL -> SERAH TERIMA -> SELESAI -> MY GARAGE

Implementasi memakai struktur existing pesanan_mobil_member, pdi_pesanan_mobil, serah_terima_pesanan_mobil, penjualan_mobil, transaksi, dan inventory kendaraan.

## Perubahan Schema

Migration additive:

2026_10_11_220000_add_pdi_pickup_ownership_workflow.php

Tambahan PDI:

- started_at, completed_at, result
- checklist JSON
- snapshot nomor rangka dan nomor mesin
- index status/completed

Tambahan pickup:

- lokasi_pengambilan
- status_pengambilan
- jadwal_dikonfirmasi_at
- catatan_pengambilan
- index status/jadwal

Tambahan sumber ownership:

- kendaraan_pelanggan.id_penjualan
- kendaraan_pelanggan.id_pesanan
- kendaraan_pelanggan.id_serah_terima
- unique source id_penjualan
- foreign key dan index terkait

Tidak ada kolom yang dihapus, tabel yang di-reset, atau data Native DB yang disentuh.

## State Machine

### Order

- menunggu_verifikasi
- menunggu_pembayaran
- lunas
- pdi_lulus
- pdi_gagal
- siap_diambil
- diserahkan

### PDI

- belum_diperiksa
- sedang_diperiksa
- perlu_perbaikan
- lulus

### Pickup

- belum_dijadwalkan
- dijadwalkan
- siap_diambil
- diserahkan

## Backend Rules

- PDI hanya dapat dimulai setelah order diverifikasi dan transaksi lunas.
- Checklist divalidasi di service, bukan hanya di browser.
- Hasil ISSUE wajib memiliki catatan.
- Pickup hanya dapat dibuat setelah PDI lulus.
- Handover hanya dapat diproses jika pembayaran lunas, PDI lulus, pickup valid, order belum selesai, dan unit masih sesuai order.
- Handover memakai DB::transaction() dan lockForUpdate().
- Ownership tidak dibuat pada checkout, approval, payment, PDI, atau scheduling.
- Ownership dibuat/link hanya setelah handover sukses.
- Handover berulang mengembalikan handover yang sama dan tidak menjual unit atau membuat ownership kedua.

## Checklist PDI

Checklist JSON menyimpan section dan item berikut:

IDENTITAS, MESIN & FLUID, KAKI-KAKI, ELECTRICAL, CABIN, EXTERIOR, FEATURE, COMPLETENESS, dan FINAL.

Setiap item memiliki result (pass, issue, na) dan notes.

## UI Admin/Petugas

Detail order admin sekarang menyediakan:

- tombol mulai PDI yang idempotent
- checklist berbasis section cards
- status PASS / ISSUE / N/A
- catatan issue
- form jadwal tanggal, jam, lokasi, dan catatan
- form handover hanya setelah order siap diambil

Route mutation tetap berada di area admin/petugas yang sudah diproteksi middleware aplikasi.

## UI Member

Member hanya mendapatkan tampilan read-only pada detail pesanan:

- timeline PDI / Pemeriksaan
- status PDI dan pesan yang aman untuk pelanggan
- tanggal, jam, lokasi, dan status pickup
- success state setelah handover
- tautan My Garage, invoice modal, dan riwayat pembelian

## My Garage

VerifiedCarOwnershipService hanya menganggap kendaraan dengan sumber id_penjualan sebagai ownership pembelian YZA. Kendaraan yang dimasukkan manual tidak otomatis menjadi ownership pembelian terverifikasi.

Ownership menghubungkan member, unit, penjualan, order, dan serah-terima.

## Notification dan Audit

Notifikasi idempotent memakai tipe notifikasi transaksi existing:

- Pembayaran terverifikasi
- PDI dimulai
- PDI memerlukan tindak lanjut
- PDI lulus
- Jadwal pengambilan tersedia
- Kendaraan siap diserahkan
- Serah terima selesai

Audit action dicatat melalui log aplikasi dengan actor, order, action, dan timestamp.

## Compatibility

savePdi() legacy tetap tersedia untuk kompatibilitas data lama, tetapi hasil lulus tetap harus melewati scheduling sebelum handover. Data historis tidak dipaksa membuat PDI baru.

## Verifikasi

Baseline sebelum perubahan:

- 320 passed, 1 skipped, 1048 assertions

Verifikasi setelah implementasi:

- focused purchase/ownership/inventory tests: 15 passed, 68 assertions
- Blade cache berhasil
- route PDI start/submit, schedule, dan handover terdaftar

Backup sebelum migration:

storage/app/backups/yza_automotive_laravel_before_pdi_20261011_204305.sql
