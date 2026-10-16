# YZA Auto Vehicle Fitment UI Refinement

## Root cause

Form fitment sebelumnya menerima merek, model, tahun, dan kode mesin sebagai teks bebas. Status awal juga mengarah ke `verified_fit`, sehingga admin dapat salah mengira data baru sebagai kompatibel.

## Implemented flow

### Auto-detect dari Work Order

`/admin/sparepart/{sparepart}?work_order={servis}` memuat `Servis` dan kendaraan melalui relasi database authoritative. Form menampilkan kendaraan, nomor polisi, Work Order, dan status pembacaan. Field kendaraan tidak diketik ulang dan server mengabaikan penggantian identitas kendaraan dari client ketika konteks Work Order digunakan.

Search result Work Order sekarang membawa tombol Kelola Fitment ke URL konteks tersebut. URL hanya menerima ID Work Order yang divalidasi oleh route/controller; tidak memakai referer atau redirect eksternal.

### Master fitment umum

Tanpa konteks Work Order, admin memilih kendaraan aktif melalui dropdown bertingkat merek → model → tahun → varian/kendaraan. Pilihan berasal dari `kendaraan_pelanggan` aktif yang ada, bukan data palsu. ID kendaraan tersembunyi tetap divalidasi server-side dan kombinasi invalid tidak dapat disimpan.

## Sumber data dan schema

Fitur memakai `KendaraanPelanggan`, `Servis`, `Sparepart`, dan tabel existing `sparepart_fitments`. Tidak ada tabel atau migration baru pada perubahan ini.

## Aturan verifikasi

- Default form: `unverified` / **Belum Terverifikasi**.
- `verified_fit` membutuhkan nomor part/referensi dan sumber bukti yang dapat diaudit.
- `verified_not_fit` tetap ditampilkan sebagai tidak kompatibel.
- Fitment tanpa bukti tidak otomatis menjadi kompatibel.
- Aktor dan waktu verifikasi tetap disimpan melalui kolom existing.

## UI dan Work Order

Detail sparepart memakai area konten penuh, panel kendaraan Work Order, panel verifikasi teknis terpisah, status badge, dan form yang lebih besar/responsif. Histori stok tetap read-only. Work Order tetap menggunakan service evaluasi fitment yang sama; stok dan harga tidak dicampur dengan proses fitment.

## Historical safety

Tidak ada perubahan terhadap approval lama, klarifikasi, snapshot harga, stok, ledger, transaksi, pembayaran, atau finalisasi. Work Order 13822 tidak diubah; konteksnya hanya dibaca saat URL dibuka.

## Tests

Focused `SparepartFitmentDependencyTest`: **8 passed, 35 assertions**. Cakupan mencakup akses admin/member, duplicate fitment, pencarian, auto-detect Work Order, status unverified, dan bukti wajib untuk verified fit.

Full regression setelah perubahan: **375 passed, 3 skipped, 0 failed, 1318 assertions**. Dua tambahan test berasal dari validasi auto-detect Work Order dan bukti wajib verified fit.

## Remaining risks

Vehicle master saat ini berasal dari kendaraan pelanggan aktif; belum ada katalog kendaraan universal terpisah untuk kombinasi model/varian. Kode mesin teknis tidak ditebak dari nomor mesin internal. Kendaraan legacy tanpa identitas mesin masih mengikuti compatibility exception lama agar histori dan regression tidak rusak; UI tetap menampilkan status unverified secara eksplisit.

