# Tahap 5A — Work Order / Proses Servis

Implementasi ini memigrasikan fondasi Work Order Native ke Laravel.

## Cakupan

- Tabel `servis` dan `detail_servis`, termasuk baseline Native.
- Work Order dibuat dari `booking_servis` berstatus `diterima`.
- Pembuatan memakai transaksi database dan `lockForUpdate()`.
- Kilometer tidak boleh turun dan kilometer kendaraan diperbarui bila lebih tinggi.
- Status booking disinkronkan menjadi `diproses`.
- Alur pemeriksaan `pemeriksaan` menjadi `dikerjakan`.
- Admin/Petugas dapat menambah dan menghapus detail pekerjaan saat `dikerjakan`.
- Pelanggan hanya dapat melihat Work Order miliknya.

## Batas yang sengaja belum dikerjakan

Tidak ada transaksi, invoice, pembayaran, Midtrans, sparepart, pengurangan stok, membership, points, promo, penjualan, atau penyelesaian Car Wash.
Kolom `id_transaksi` tetap dipertahankan sebagai nullable untuk kompatibilitas baseline Native, tanpa relasi atau proses transaksi.

Native memiliki data walk-in dengan `id_booking_servis` nullable, tetapi tidak ditemukan endpoint pembuatan walk-in yang aktif. Data baseline dipertahankan; endpoint baru hanya membuat Work Order dari booking.

## Verifikasi

- Baseline target: 2 `servis`, 1 `detail_servis`.
- Test Tahap 5A: 10 test, 23 assertion, lulus.
- Route Admin, Petugas, dan Pelanggan terdaftar.
- Database Native tidak ditulis.
