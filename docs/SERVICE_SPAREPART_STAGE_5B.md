# Tahap 5B — Draft Pemakaian Spare Part pada Work Order

## 1. Audit Native

Native menggunakan `servis_sparepart` sebagai draft pemakaian. `spare_add` mengambil spare part aktif, menggabungkan duplicate item pada servis yang sama, memakai `harga_jual` server-side, dan menghitung subtotal. Stok tidak berkurang saat draft ditambah, diedit, atau dihapus. Stok baru dikurangi oleh `finalize_service()` bersamaan dengan pembuatan transaksi.

## 2. Schema dan baseline

Migration mempertahankan primary key `id_servis_sparepart`, unique `(id_servis,id_sparepart)`, index `id_sparepart`, foreign key ke `servis` dan `sparepart`, `jumlah`, `harga_satuan`, dan `subtotal`. Baseline Native disalin langsung ke target tanpa memanggil service domain dan tanpa mutasi stok. Baseline target berisi 2 baris dan stok target tetap sama dengan Native: spare part id 1 memiliki stok 5.

## 3. Model dan relationship

`ServisSparepart` memiliki relasi `servis()` dan `sparepart()`. `Servis` memiliki `sparepartItems()`, dan `Sparepart` memiliki `servisItems()`. Detail Work Order menggunakan eager loading `sparepartItems.sparepart`.

## 4. Draft service behavior

`ServisSparepartService` menggunakan transaction dan lock pada Work Order serta spare part. Draft hanya dapat diubah pada status `dikerjakan` dan ketika `id_transaksi` masih null. Qty harus lebih dari nol dan total qty draft tidak boleh melewati stok sebagai pencegahan awal Native. Duplicate request digabung ke row existing. Harga selalu diambil dari `sparepart.harga_jual`; subtotal dihitung server-side dengan DECIMAL. Hapus draft tidak mengembalikan stok dan tidak membuat `riwayat_stok`, karena belum ada consumption final.

## 5. Authorization dan ownership

Route mutation hanya tersedia pada grup Admin dan Petugas. Pelanggan tidak memiliki route mutation dan hanya dapat membaca servis miliknya melalui ownership check. IDOR pada detail servis diblokir.

## 6. Inventory dan financial boundary

Tahap ini sengaja tidak memanggil mutasi inventory final, tidak membuat `riwayat_stok`, transaksi, pembayaran, invoice, atau order Midtrans. Deferred dependency: Tahap 5C harus mengunci Work Order, mengunci seluruh spare part, memvalidasi stok aktual, mengurangi stok, membuat `riwayat_stok` dengan `jenis_transaksi='servis'`, membuat transaksi, memperbarui status, dan commit dalam satu transaction. Kegagalan di salah satu langkah harus rollback semua.

## 7. Test dan regression

Test Tahap 5B membuktikan baseline, import tanpa perubahan stok, add/duplicate/qty validation, status aktif, harga dan subtotal server-side, edit/delete tanpa mutasi stok, final lock, customer mutation/IDOR, role access, relationship eager loading, dan financial isolation.
