<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Stage2TestingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $syntheticHash = Hash::make(str_repeat('x', 32));
        DB::table('roles')->insertOrIgnore([
            ['id_role' => 1, 'nama_role' => 'admin', 'deskripsi' => 'Synthetic Stage 2 admin', 'created_at' => $now, 'updated_at' => $now],
            ['id_role' => 2, 'nama_role' => 'petugas', 'deskripsi' => 'Synthetic Stage 2 staff', 'created_at' => $now, 'updated_at' => $now],
            ['id_role' => 3, 'nama_role' => 'pelanggan', 'deskripsi' => 'Synthetic Stage 2 member', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('users')->insertOrIgnore([
            ['id_user' => 1, 'id_role' => 1, 'username' => 'stage2-admin', 'email' => 'stage2-admin@example.test', 'password' => $syntheticHash, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id_user' => 2, 'id_role' => 2, 'username' => 'stage2-petugas', 'email' => 'stage2-petugas@example.test', 'password' => $syntheticHash, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id_user' => 3, 'id_role' => 3, 'username' => 'stage2-member', 'email' => 'stage2-member@example.test', 'password' => $syntheticHash, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id_user' => 4, 'id_role' => 3, 'username' => 'stage2-member-two', 'email' => 'stage2-member-two@example.test', 'password' => $syntheticHash, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('petugas')->insertOrIgnore(['id_petugas' => 2, 'id_user' => 2, 'kode_petugas' => 'STAGE2-PETUGAS', 'nama_lengkap' => 'Stage 2 Petugas', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('petugas')->insertOrIgnore(['id_petugas' => 1, 'id_user' => 1, 'kode_petugas' => 'STAGE2-ADMIN', 'nama_lengkap' => 'Stage 2 Admin', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('pelanggan')->insertOrIgnore(['id_pelanggan' => 1, 'id_user' => 3, 'kode_pelanggan' => 'STAGE2-MEMBER', 'nama_lengkap' => 'Stage 2 Member', 'no_hp' => '080000000002', 'alamat' => 'Alamat sintetis untuk pengujian', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('pelanggan')->insertOrIgnore(['id_pelanggan' => 2, 'id_user' => 4, 'kode_pelanggan' => 'STAGE2-MEMBER-2', 'nama_lengkap' => 'Stage 2 Member Two', 'no_hp' => '080000000003', 'alamat' => 'Alamat sintetis kedua untuk pengujian', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('memberships')->insertOrIgnore(['id_membership' => 1, 'id_pelanggan' => 1, 'member_id' => 'YZA-STAGE2', 'status' => 'active', 'activation_fee' => 249000, 'registered_at' => $now, 'activated_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('memberships')->insertOrIgnore(['id_membership' => 2, 'id_pelanggan' => 2, 'member_id' => 'YZA-STAGE2-2', 'status' => 'active', 'activation_fee' => 249000, 'registered_at' => $now, 'activated_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('kendaraan_pelanggan')->insertOrIgnore(['id_kendaraan_pelanggan' => 2, 'id_pelanggan' => 2, 'merk' => 'YZA', 'model' => 'Synthetic Other', 'tipe' => 'Stage 3', 'tahun' => 2025, 'warna' => 'Putih', 'transmisi' => 'automatic', 'bahan_bakar' => 'bensin', 'nomor_polisi' => 'STAGE3-002', 'nomor_rangka' => 'STAGE3-VIN-002', 'nomor_mesin' => 'STAGE3-ENGINE-002', 'kilometer' => 100, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('jenis_servis')->insertOrIgnore(['id_jenis_servis' => 1, 'nama_servis' => 'Servis Berkala', 'deskripsi' => 'Synthetic service master', 'estimasi_harga' => 250000, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('paket_cuci')->insertOrIgnore(['id_paket' => 1, 'nama_paket' => 'Basic', 'deskripsi' => 'Synthetic wash package', 'harga' => 75000, 'estimasi_menit' => 45, 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('jenis_servis')->where('id_jenis_servis', 1)->update(['nama_servis' => 'Servis Berkala', 'status' => 'aktif', 'updated_at' => $now]);
        DB::table('paket_cuci')->where('id_paket', 1)->update(['nama_paket' => 'Basic', 'status' => 'aktif', 'updated_at' => $now]);
        DB::table('kategori_sparepart')->insertOrIgnore(['id_kategori' => 1, 'nama_kategori' => 'Consumable Stage 3', 'deskripsi' => 'Synthetic parts category', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('kategori_sparepart')->insertOrIgnore(['id_kategori' => 2, 'nama_kategori' => 'Oli', 'deskripsi' => 'Synthetic duplicate-validation category', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('supplier')->insertOrIgnore(['id_supplier' => 1, 'kode_supplier' => 'STAGE3-SUP', 'nama_supplier' => 'Synthetic Supplier', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('sparepart')->insertOrIgnore(['id_sparepart' => 1, 'kode_sparepart' => 'STAGE3-PART-001', 'id_kategori' => 1, 'nama_sparepart' => 'Synthetic Brake Pad', 'merk' => 'YZA', 'harga_beli' => 20000, 'harga_jual' => 35000, 'stok' => 5, 'stok_minimum' => 1, 'satuan' => 'pcs', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('sparepart')->where('id_sparepart', 1)->update(['stok' => 5, 'harga_jual' => 35000, 'status' => 'aktif', 'updated_at' => $now]);
        foreach ([6 => ['STAGE2-UNIT-006', 'Synthetic Used 6', 100000000, 'bekas'], 7 => ['STAGE2-UNIT-007', 'Synthetic Trade 7', 80000000, 'baru'], 8 => ['STAGE2-UNIT-008', 'Synthetic Trade 8', 50000000, 'bekas'], 9 => ['STAGE2-UNIT-009', 'Synthetic Public 9', 80000000, 'baru']] as $id => [$code, $model, $price, $condition]) {
            DB::table('mobil')->insertOrIgnore(['id_mobil' => $id, 'kode_mobil' => $code, 'merk' => 'YZA', 'model' => $model, 'tipe' => 'Stage 3', 'tahun' => 2025, 'warna' => 'Hitam', 'transmisi' => 'automatic', 'bahan_bakar' => 'bensin', 'nomor_rangka' => 'STAGE3-VIN-'.$id, 'nomor_mesin' => 'STAGE3-ENGINE-'.$id, 'kilometer' => 100, 'harga_beli' => $price * .8, 'harga_jual' => $price, 'kondisi' => $condition, 'status' => 'tersedia', 'sumber_mobil' => 'dealer', 'created_at' => $now, 'updated_at' => $now]);
        }
        DB::table('booking_mobil')->insertOrIgnore([
            ['id_booking' => 1, 'kode_booking' => 'BKG-202609-STAGE2-01', 'id_pelanggan' => 2, 'id_mobil' => 6, 'tanggal_booking' => $now->copy()->subDays(3), 'tanggal_expired' => $now->copy()->subDay(), 'status' => 'selesai', 'id_petugas_konfirmasi' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id_booking' => 2, 'kode_booking' => 'BKG-202609-STAGE2-02', 'id_pelanggan' => 2, 'id_mobil' => 7, 'tanggal_booking' => $now->copy()->subDays(2), 'tanggal_expired' => $now->copy()->subDay(), 'status' => 'selesai', 'id_petugas_konfirmasi' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('test_drive')->insertOrIgnore(['id_test_drive' => 1, 'kode_test_drive' => 'TDRV-202609-STAGE2-01', 'id_pelanggan' => 2, 'id_mobil' => 6, 'id_petugas' => 1, 'tanggal' => $now->copy()->subDays(2)->toDateString(), 'jam' => '10:00', 'lokasi' => 'Synthetic showroom', 'status' => 'selesai', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('booking_servis')->insertOrIgnore(['id_booking_servis' => 1, 'kode_booking' => 'BSV-202609-52D02542', 'id_pelanggan' => 2, 'id_kendaraan_pelanggan' => 2, 'id_jenis_servis' => 1, 'tanggal' => $now->copy()->subDays(2)->toDateString(), 'jam' => '10:00', 'keluhan' => 'Synthetic baseline', 'status' => 'selesai', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('booking_cuci')->insertOrIgnore(['id_booking_cuci' => 1, 'kode_booking' => 'BCW-202609-067D478B', 'id_pelanggan' => 2, 'id_kendaraan_pelanggan' => 2, 'id_paket' => 1, 'id_petugas' => 1, 'tanggal' => $now->copy()->subDays(2)->toDateString(), 'jam' => '11:00', 'harga' => 75000, 'status' => 'selesai', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('mobil')->insertOrIgnore(['id_mobil' => 1, 'kode_mobil' => 'STAGE2-UNIT-001', 'merk' => 'YZA', 'model' => 'Synthetic GT', 'tipe' => 'Stage 2', 'tahun' => 2026, 'warna' => 'Hitam', 'transmisi' => 'automatic', 'bahan_bakar' => 'bensin', 'nomor_rangka' => 'STAGE2-VIN-001', 'nomor_mesin' => 'STAGE2-ENGINE-001', 'kilometer' => 0, 'harga_beli' => 100000000, 'harga_jual' => 150000000, 'kondisi' => 'baru', 'status' => 'tersedia', 'sumber_mobil' => 'dealer', 'created_at' => $now, 'updated_at' => $now]);

        // Historical, synthetic baseline used by the Stage 3 regression contracts.
        DB::table('transaksi')->insertOrIgnore([
            ['id_transaksi'=>1,'kode_transaksi'=>'TRX-202609-BASE0001','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'penjualan_mobil','tanggal'=>$now->copy()->subDays(8),'subtotal'=>240000000,'diskon'=>0,'total'=>240000000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_transaksi'=>2,'kode_transaksi'=>'TRX-202609-BASE0002','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'penjualan_sparepart','tanggal'=>$now->copy()->subDays(7),'subtotal'=>105000,'diskon'=>0,'total'=>105000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_transaksi'=>3,'kode_transaksi'=>'TRX-202609-BASE0003','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'penjualan_sparepart','tanggal'=>$now->copy()->subDays(6),'subtotal'=>35000,'diskon'=>0,'total'=>35000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_transaksi'=>4,'kode_transaksi'=>'TRX-202609-BASE0004','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'servis','tanggal'=>$now->copy()->subDays(5),'subtotal'=>160000,'diskon'=>0,'total'=>160000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_transaksi'=>5,'kode_transaksi'=>'TRX-202609-BASE0005','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'cuci_mobil','tanggal'=>$now->copy()->subDays(4),'subtotal'=>75000,'diskon'=>0,'total'=>75000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_transaksi'=>6,'kode_transaksi'=>'TRX-202609-BASE0006','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'pembelian_mobil','tanggal'=>$now->copy()->subDays(3),'subtotal'=>100000000,'diskon'=>0,'total'=>100000000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_transaksi'=>7,'kode_transaksi'=>'TRX-202609-BASE0007','id_pelanggan'=>1,'id_petugas'=>2,'jenis_transaksi'=>'trade_in','tanggal'=>$now->copy()->subDays(2),'subtotal'=>30000000,'diskon'=>0,'total'=>30000000,'status'=>'lunas','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
        ]);
        DB::table('transaksi')->where('id_transaksi', 1)->update(['subtotal'=>240000000,'total'=>240000000,'status'=>'lunas','updated_at'=>$now]);
        DB::table('pembayaran')->insertOrIgnore([
            ['id_pembayaran'=>1,'kode_pembayaran'=>'PAY-BASE-0001','id_transaksi'=>1,'tanggal'=>$now,'arah'=>'masuk','metode'=>'tunai','jumlah'=>120000000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>2,'kode_pembayaran'=>'PAY-BASE-0002','id_transaksi'=>1,'tanggal'=>$now,'arah'=>'masuk','metode'=>'transfer','jumlah'=>120000000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>3,'kode_pembayaran'=>'PAY-BASE-0003','id_transaksi'=>2,'tanggal'=>$now,'arah'=>'masuk','metode'=>'tunai','jumlah'=>105000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>4,'kode_pembayaran'=>'PAY-BASE-0004','id_transaksi'=>3,'tanggal'=>$now,'arah'=>'masuk','metode'=>'tunai','jumlah'=>35000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>5,'kode_pembayaran'=>'PAY-BASE-0005','id_transaksi'=>4,'tanggal'=>$now,'arah'=>'masuk','metode'=>'tunai','jumlah'=>160000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>6,'kode_pembayaran'=>'PAY-BASE-0006','id_transaksi'=>6,'tanggal'=>$now,'arah'=>'keluar','metode'=>'tunai','jumlah'=>50000000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>7,'kode_pembayaran'=>'PAY-BASE-0007','id_transaksi'=>6,'tanggal'=>$now,'arah'=>'keluar','metode'=>'transfer','jumlah'=>50000000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>8,'kode_pembayaran'=>'PAY-BASE-0008','id_transaksi'=>5,'tanggal'=>$now,'arah'=>'masuk','metode'=>'tunai','jumlah'=>75000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>9,'kode_pembayaran'=>'PAY-BASE-0009','id_transaksi'=>7,'tanggal'=>$now,'arah'=>'masuk','metode'=>'tunai','jumlah'=>30000000,'status'=>'terverifikasi','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>10,'kode_pembayaran'=>'PAY-BASE-0010','id_transaksi'=>5,'tanggal'=>$now,'arah'=>'masuk','metode'=>'transfer','jumlah'=>1,'status'=>'ditolak','catatan'=>'Synthetic baseline','created_at'=>$now],
            ['id_pembayaran'=>11,'kode_pembayaran'=>'PAY-BASE-0011','id_transaksi'=>7,'tanggal'=>$now,'arah'=>'masuk','metode'=>'transfer','jumlah'=>1,'status'=>'ditolak','catatan'=>'Synthetic baseline','created_at'=>$now],
        ]);
        DB::table('pembayaran')->where('id_pembayaran', 10)->update(['id_transaksi'=>5]);
        DB::table('pembayaran')->where('id_pembayaran', 11)->update(['id_transaksi'=>7]);
        DB::table('penjualan_mobil')->insertOrIgnore(['id_penjualan'=>1,'kode_penjualan'=>'SALE-202609-8203F497','id_transaksi'=>1,'id_pelanggan'=>1,'id_mobil'=>9,'id_petugas'=>2,'id_booking'=>null,'tanggal_penjualan'=>$now,'harga_mobil'=>240000000,'diskon'=>0,'dp'=>240000000,'total_akhir'=>240000000,'metode_pembelian'=>'tunai','status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now]);
        DB::table('penjualan_mobil')->where('id_penjualan', 1)->update(['harga_mobil'=>240000000,'dp'=>240000000,'total_akhir'=>240000000,'status'=>'selesai','updated_at'=>$now]);
        DB::table('mobil')->where('id_mobil', 9)->update(['harga_jual'=>240000000,'status'=>'tersedia','updated_at'=>$now]);
        DB::table('servis')->insertOrIgnore([
            ['id_servis'=>1,'kode_servis'=>'SRV-202609-70A40DC2','id_transaksi'=>4,'id_booking_servis'=>1,'id_pelanggan'=>2,'id_kendaraan_pelanggan'=>2,'id_petugas'=>2,'tanggal_masuk'=>$now,'kilometer'=>100,'hasil_pemeriksaan'=>'Synthetic baseline','biaya_jasa'=>125000,'biaya_sparepart'=>35000,'total_biaya'=>160000,'status'=>'selesai','created_at'=>$now,'updated_at'=>$now],
            ['id_servis'=>2,'kode_servis'=>'SRV-202609-BASE0002','id_transaksi'=>null,'id_booking_servis'=>null,'id_pelanggan'=>2,'id_kendaraan_pelanggan'=>2,'id_petugas'=>2,'tanggal_masuk'=>$now,'kilometer'=>100,'hasil_pemeriksaan'=>null,'biaya_jasa'=>0,'biaya_sparepart'=>0,'total_biaya'=>0,'status'=>'dikerjakan','created_at'=>$now,'updated_at'=>$now],
        ]);
        DB::table('detail_servis')->insertOrIgnore(['id_detail_servis'=>1,'id_servis'=>1,'id_jenis_servis'=>1,'deskripsi_pekerjaan'=>'Synthetic baseline service','biaya'=>125000,'catatan'=>'Synthetic baseline']);
        DB::table('servis_sparepart')->insertOrIgnore([
            ['id_servis_sparepart'=>1,'id_servis'=>1,'id_sparepart'=>1,'jumlah'=>1,'harga_satuan'=>35000,'subtotal'=>35000],
            ['id_servis_sparepart'=>2,'id_servis'=>2,'id_sparepart'=>1,'jumlah'=>1,'harga_satuan'=>35000,'subtotal'=>35000],
        ]);
        DB::table('penjualan_sparepart')->insertOrIgnore([
            ['id_penjualan_sparepart'=>1,'kode_penjualan'=>'SPS-202609-29E646FA','id_transaksi'=>2,'id_pelanggan'=>1,'id_petugas'=>2,'tanggal'=>$now,'subtotal'=>105000,'diskon'=>0,'total'=>105000,'status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_penjualan_sparepart'=>2,'kode_penjualan'=>'SPS-202609-BASE0002','id_transaksi'=>3,'id_pelanggan'=>1,'id_petugas'=>2,'tanggal'=>$now,'subtotal'=>35000,'diskon'=>0,'total'=>35000,'status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
        ]);
        DB::table('detail_penjualan_sparepart')->insertOrIgnore([
            ['id_detail'=>1,'id_penjualan_sparepart'=>1,'id_sparepart'=>1,'jumlah'=>3,'harga_satuan'=>35000,'subtotal'=>105000],
            ['id_detail'=>2,'id_penjualan_sparepart'=>2,'id_sparepart'=>1,'jumlah'=>1,'harga_satuan'=>35000,'subtotal'=>35000],
        ]);
        for ($i = 1; $i <= 7; $i++) {
            DB::table('riwayat_stok')->insertOrIgnore(['id_riwayat'=>$i,'id_sparepart'=>1,'id_petugas'=>2,'tanggal'=>$now,'jenis_transaksi'=>$i <= 5 ? 'stok_masuk' : 'penjualan','jumlah_perubahan'=>$i <= 5 ? 1 : -1,'stok_sebelum'=>5,'stok_sesudah'=>5,'referensi_tipe'=>'synthetic_baseline','referensi_id'=>$i,'catatan'=>'Synthetic baseline','created_at'=>$now]);
        }
        DB::table('pengajuan_mobil')->insertOrIgnore([
            ['id_pengajuan'=>1,'kode_pengajuan'=>'PJM-202609-BASE0001','id_pelanggan'=>2,'id_kendaraan_pelanggan'=>2,'merk'=>'YZA','model'=>'Synthetic Trade','tipe'=>'Stage 3','tahun'=>2025,'warna'=>'Hitam','transmisi'=>'automatic','bahan_bakar'=>'bensin','nomor_polisi'=>'STAGE3-001','nomor_rangka'=>'STAGE3-APP-001','nomor_mesin'=>'STAGE3-APP-ENGINE-001','kilometer'=>1000,'harga_harapan'=>50000000,'status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_pengajuan'=>2,'kode_pengajuan'=>'PJM-202609-BASE0002','id_pelanggan'=>2,'id_kendaraan_pelanggan'=>2,'merk'=>'YZA','model'=>'Synthetic Trade 2','tipe'=>'Stage 3','tahun'=>2025,'warna'=>'Putih','transmisi'=>'automatic','bahan_bakar'=>'bensin','nomor_polisi'=>'STAGE3-002','nomor_rangka'=>'STAGE3-APP-002','nomor_mesin'=>'STAGE3-APP-ENGINE-002','kilometer'=>1200,'harga_harapan'=>50000000,'status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
        ]);
        DB::table('inspeksi_mobil')->insertOrIgnore([
            ['id_inspeksi'=>1,'id_pengajuan'=>1,'id_petugas'=>2,'tanggal_inspeksi'=>$now,'kondisi_mesin'=>'baik','kondisi_body'=>'baik','kondisi_interior'=>'baik','kondisi_kelistrikan'=>'baik','kondisi_ban'=>'baik','kondisi_suspensi'=>'baik','kondisi_ac'=>'baik','kondisi_dokumen'=>'lengkap','kilometer'=>1000,'harga_penawaran'=>50000000,'catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
            ['id_inspeksi'=>2,'id_pengajuan'=>2,'id_petugas'=>2,'tanggal_inspeksi'=>$now,'kondisi_mesin'=>'baik','kondisi_body'=>'baik','kondisi_interior'=>'baik','kondisi_kelistrikan'=>'baik','kondisi_ban'=>'baik','kondisi_suspensi'=>'baik','kondisi_ac'=>'baik','kondisi_dokumen'=>'lengkap','kilometer'=>1200,'harga_penawaran'=>50000000,'catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now],
        ]);
        DB::table('pembelian_mobil_bekas')->insertOrIgnore(['id_pembelian'=>1,'kode_pembelian'=>'BUY-202609-487E3F23','id_transaksi'=>6,'id_pengajuan'=>1,'id_inspeksi'=>1,'id_pelanggan'=>1,'id_petugas'=>2,'id_mobil'=>6,'tanggal_pembelian'=>$now,'harga_pembelian'=>100000000,'status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now]);
        DB::table('trade_in')->insertOrIgnore(['id_trade_in'=>1,'kode_trade_in'=>'TRD-202609-032CEF4C','id_transaksi'=>7,'id_pelanggan'=>1,'id_pengajuan'=>2,'id_inspeksi'=>2,'id_mobil_lama_masuk'=>8,'id_mobil_dibeli'=>7,'id_petugas'=>2,'nilai_mobil_lama'=>50000000,'harga_mobil_dibeli'=>80000000,'diskon'=>0,'selisih_bayar'=>30000000,'tanggal'=>$now,'status'=>'selesai','catatan'=>'Synthetic baseline','created_at'=>$now,'updated_at'=>$now]);
        DB::table('pengaturan')->insertOrIgnore(['kunci_pengaturan'=>'nama_singkat','nilai'=>'YZA','deskripsi'=>'Synthetic baseline','updated_at'=>$now]);
        DB::table('pengaturan')->where('kunci_pengaturan','nama_singkat')->update(['nilai'=>'YZA','updated_at'=>$now]);
        DB::table('pengaturan')->where('kunci_pengaturan','timezone')->update(['nilai'=>'Asia/Jakarta','updated_at'=>$now]);
        DB::table('poin_member')->insertOrIgnore(['id_poin_member'=>1,'id_pelanggan'=>1,'saldo'=>24000,'total_diperoleh'=>24000,'created_at'=>$now,'updated_at'=>$now]);
        DB::table('riwayat_poin')->insertOrIgnore(['id_riwayat_poin'=>1,'id_pelanggan'=>1,'id_transaksi'=>1,'jenis'=>'perolehan','perubahan_poin'=>24000,'saldo_setelah'=>24000,'idempotency_key'=>'earn:transaction:1','keterangan'=>'Synthetic baseline points','created_at'=>$now,'updated_at'=>$now]);
    }
}
