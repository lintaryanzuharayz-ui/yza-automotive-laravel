# Tahap 8A — Pengajuan Jual Mobil Bekas + Inspeksi + Penawaran

## Audit Native

`pengajuan_mobil` memiliki status: `diajukan`, `menunggu_inspeksi`, `dalam_inspeksi`, `penawaran_diberikan`, `disetujui`, `ditolak`, dan `selesai`. Native menggunakan kendaraan pelanggan yang sudah terdaftar; pelanggan tidak mengirim identitas pelanggan bebas.

Transition utama Native:

`diajukan → menunggu_inspeksi → dalam_inspeksi → penawaran_diberikan → disetujui/ditolak`.

Status `selesai` digunakan setelah settlement berikutnya, bukan pada Tahap 8A.

`inspeksi_mobil` bersifat one-to-many terhadap pengajuan. Field inspeksi meliputi mesin, body, interior, kelistrikan, ban, suspensi, AC, dokumen, kilometer, catatan, dan harga penawaran. Tidak ada tabel negosiasi terpisah; harga penawaran berada pada row inspeksi.

Native membuat `pembelian_mobil_bekas` setelah penawaran diterima dan langsung membuat transaksi pembelian serta pembayaran keluar pada flow tersebut. Karena itu seluruh pembelian, settlement, pembayaran keluar, dan masuk inventory dealer ditunda ke Tahap 8B.

Trade-In hanya diaudit sebagai dependency dan tidak diimplementasikan.

## Implementasi Laravel

- Migration `pengajuan_mobil` dan `inspeksi_mobil` sesuai schema Native.
- Baseline: 2 pengajuan dan 2 inspeksi.
- Model `PengajuanMobil` dan `InspeksiMobil` dengan relationship aktual.
- `PengajuanMobilService` menangani submission, process, inspection, offer, dan keputusan pelanggan.
- Admin/Petugas dapat memproses pengajuan; pelanggan hanya dapat melihat miliknya dan menerima/menolak penawaran.
- Semua action kritis mengunci aggregate pengajuan dengan `lockForUpdate()`.
- Tidak dibuat tabel `pembelian_mobil_bekas` atau `trade_in` pada target.
- Tidak dibuat transaksi, pembayaran, inventory mobil, gateway, atau invoice finansial.

## Security dan verifikasi

- Identitas customer diambil dari session.
- IDOR pengajuan diblokir.
- Status dan harga penawaran tidak dapat diubah dari request customer.
- CSRF menggunakan middleware Laravel.
- Request inspection dan offer dibatasi role Admin/Petugas serta divalidasi server-side.
- Test Tahap 8A: 7 test, 19 assertion — PASS.
- Regression tahap sebelumnya dijalankan setelah perubahan.

Tahap settlement pembelian mobil bekas tetap menjadi Tahap 8B.
