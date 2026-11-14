<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User, Role, Petugas, Pelanggan, KendaraanPelanggan, Mobil,
    PenjualanMobil, PesananMobilMember, SerahTerimaPesananMobil,
    Transaksi, JenisServis, KategoriSparepart, Supplier, Sparepart
};

class IsolatedDemoDatabaseSeeder extends Seeder
{
    /**
     * Seed synthetic, fully-verified demo dataset exclusively into the isolated demo database.
     * Fail-closed: Strictly rejects execution if active database is not 'yza_automotive_laravel_demo'.
     */
    public function run(): void
    {
        $currentDb = DB::connection()->getDatabaseName();
        if (!in_array($currentDb, ['yza_automotive_laravel_demo', 'yza_automotive_laravel_uat'], true)) {
            throw new \RuntimeException(
                "FAIL-CLOSED SAFETY TRIGGERED: Active connection is '{$currentDb}'. " .
                "Seeder hanya boleh berjalan pada database demo/UAT terisolasi."
            );
        }

        echo ">>> Seeding Isolated Demo Database: {$currentDb} <<<\n";

        // 1. Roles & Settings
        DB::statement("SET FOREIGN_KEY_CHECKS=0");

        // Roles
        DB::table('roles')->updateOrInsert(['id_role' => 1], ['nama_role' => 'admin', 'deskripsi' => 'Akses penuh admin demo']);
        DB::table('roles')->updateOrInsert(['id_role' => 2], ['nama_role' => 'petugas', 'deskripsi' => 'Akses operasional bengkel demo']);
        DB::table('roles')->updateOrInsert(['id_role' => 3], ['nama_role' => 'pelanggan', 'deskripsi' => 'Portal member demo']);

        // 2. Demo Users (Admin, Staff, Member) with isolated, synthetic credentials
        // Password for demo is 'DemoYZA@2026!'
        $demoPasswordHash = Hash::make('DemoYZA@2026!');

        // Admin Demo
        $adminUser = User::firstOrCreate(
            ['email' => 'admin.demo@yza.local'],
            [
                'id_role' => 1,
                'username' => 'admindemo',
                'password' => $demoPasswordHash,
                'status' => 'aktif',
            ]
        );
        $adminPetugas = Petugas::updateOrCreate(
            ['id_user' => $adminUser->id_user],
            [
                'kode_petugas' => 'PTG-DEMO-001',
                'nama_lengkap' => 'Budi Santoso (Admin Demo)',
                'jabatan' => 'Kepala Bengkel & Admin Demo',
                'no_hp' => '081299990001',
                'alamat' => 'YZA Workshop Studio Demo, Jakarta',
                'status' => 'aktif',
            ]
        );

        // Petugas / Teknisi Demo
        $petugasUser = User::firstOrCreate(
            ['email' => 'petugas.demo@yza.local'],
            [
                'id_role' => 2,
                'username' => 'petugasdemo',
                'password' => $demoPasswordHash,
                'status' => 'aktif',
            ]
        );
        $teknisiPetugas = Petugas::updateOrCreate(
            ['id_user' => $petugasUser->id_user],
            [
                'kode_petugas' => 'PTG-DEMO-002',
                'nama_lengkap' => 'Rian Hidayat (Teknisi Demo)',
                'jabatan' => 'Master Teknisi Bengkel',
                'no_hp' => '081299990002',
                'alamat' => 'YZA Service Center Demo, Jakarta',
                'status' => 'aktif',
            ]
        );

        // Member Demo (Verified Owner)
        $memberUser = User::firstOrCreate(
            ['email' => 'member.demo@yza.local'],
            [
                'id_role' => 3,
                'username' => 'memberdemo',
                'password' => $demoPasswordHash,
                'status' => 'aktif',
            ]
        );
        $customer = Pelanggan::updateOrCreate(
            ['id_user' => $memberUser->id_user],
            [
                'kode_pelanggan' => 'PLG-DEMO-001',
                'nama_lengkap' => 'Lintar YZA Member (Demo VIP)',
                'jenis_kelamin' => 'L',
                'no_hp' => '081299990003',
                'alamat' => 'Jl. Boulevard Raya No. 88, Puri Indah, Jakarta Barat',
                'tanggal_lahir' => '1995-05-15',
                'status' => 'aktif',
            ]
        );

        // Active VIP Membership for Demo
        \App\Models\Membership::updateOrCreate(
            ['id_pelanggan' => $customer->id_pelanggan],
            [
                'member_id' => 'YZA-VIP-2026-001',
                'status' => 'active',
                'activation_fee' => 0,
                'registered_at' => now()->subMonths(6),
                'activated_at' => now()->subMonths(6),
            ]
        );

        // 3. Jenis Servis
        $serviceTypes = [
            ['nama_servis' => 'Servis Berkala', 'deskripsi' => 'Pemeriksaan menyeluruh dan servis berkala kendaraan YZA', 'estimasi_harga' => 350000],
            ['nama_servis' => 'Ganti Oli', 'deskripsi' => 'Penggantian oli mesin dan filter oli berkala', 'estimasi_harga' => 150000],
            ['nama_servis' => 'Tune Up', 'deskripsi' => 'Penyetelan dan optimalisasi sistem pembakaran', 'estimasi_harga' => 400000],
            ['nama_servis' => 'Servis Rem', 'deskripsi' => 'Pembersihan kampas dan bleed minyak rem', 'estimasi_harga' => 300000],
            ['nama_servis' => 'Servis AC', 'deskripsi' => 'Pembersihan evaporator dan ganti filter kabin', 'estimasi_harga' => 300000],
            ['nama_servis' => 'Kelistrikan', 'deskripsi' => 'Pemeriksaan aki dan kelistrikan body', 'estimasi_harga' => 250000],
        ];
        foreach ($serviceTypes as $st) {
            DB::table('jenis_servis')->updateOrInsert(
                ['nama_servis' => $st['nama_servis']],
                ['deskripsi' => $st['deskripsi'], 'estimasi_harga' => $st['estimasi_harga'], 'status' => 'aktif']
            );
        }

        // 4. Kategori & Supplier Demo
        $categories = [
            ['nama_kategori' => 'Oli', 'deskripsi' => 'Pelumas mesin dan transmisi'],
            ['nama_kategori' => 'Filter', 'deskripsi' => 'Filter oli, udara, kabin, dan bahan bakar'],
            ['nama_kategori' => 'Rem', 'deskripsi' => 'Komponen pengereman dan kampas rem'],
            ['nama_kategori' => 'Mesin', 'deskripsi' => 'Komponen mesin pembakaran dan busi'],
            ['nama_kategori' => 'Kelistrikan', 'deskripsi' => 'Komponen elektrikal dan aki'],
            ['nama_kategori' => 'Perawatan', 'deskripsi' => 'Coolant radiator, wiper, dan cairan pembersih'],
        ];
        $catMap = [];
        foreach ($categories as $cat) {
            $c = KategoriSparepart::updateOrCreate(
                ['nama_kategori' => $cat['nama_kategori']],
                ['deskripsi' => $cat['deskripsi'], 'status' => 'aktif']
            );
            $catMap[$cat['nama_kategori']] = $c->id_kategori;
        }

        $supplier = Supplier::updateOrCreate(
            ['kode_supplier' => 'SUP-YZA-DEMO-01'],
            [
                'nama_supplier' => 'PT YZA Automotive Parts Indonesia (Demo)',
                'no_hp' => '081123456789',
                'email' => 'parts.demo@yza-automotive.com',
                'alamat' => 'YZA Automotive Tower, Jakarta Barat',
                'kontak_person' => 'Budi Santoso',
                'status' => 'aktif',
            ]
        );

        // 5. 16 Katalog Sparepart Demo (dengan saldo awal 0 untuk skenario stok masuk)
        $catalog = [
            ['kode' => 'YZA-OIL-SYN1L', 'nama' => 'Oli Mesin Fully Synthetic 0W-20 (1 Liter)', 'cat' => 'Oli', 'merk' => 'YZA Genuine Oil', 'satuan' => 'botol 1L', 'beli' => 125000, 'jual' => 165000, 'min' => 6],
            ['kode' => 'YZA-OIL-SYN4L', 'nama' => 'Oli Mesin Fully Synthetic 5W-30 (4 Liter)', 'cat' => 'Oli', 'merk' => 'YZA Genuine Oil', 'satuan' => 'galon 4L', 'beli' => 450000, 'jual' => 590000, 'min' => 4],
            ['kode' => 'YZA-OIL-ATF1L', 'nama' => 'Oli Transmisi Matic ATF WS / CVT (1 Liter)', 'cat' => 'Oli', 'merk' => 'YZA Fluid Tech', 'satuan' => 'botol 1L', 'beli' => 110000, 'jual' => 150000, 'min' => 4],
            ['kode' => 'YZA-FLT-OIL01', 'nama' => 'Filter Oli Mesin Presisi Tinggi (Oil Filter)', 'cat' => 'Filter', 'merk' => 'Denso by YZA', 'satuan' => 'pcs', 'beli' => 45000, 'jual' => 65000, 'min' => 5],
            ['kode' => 'YZA-FLT-AIR01', 'nama' => 'Filter Udara Mesin Heavy Duty (Air Filter)', 'cat' => 'Filter', 'merk' => 'YZA Air Clean', 'satuan' => 'pcs', 'beli' => 95000, 'jual' => 135000, 'min' => 4],
            ['kode' => 'YZA-FLT-CAB01', 'nama' => 'Filter Kabin AC Karbon Aktif (Cabin Filter)', 'cat' => 'Filter', 'merk' => 'YZA Air Clean', 'satuan' => 'pcs', 'beli' => 85000, 'jual' => 120000, 'min' => 4],
            ['kode' => 'YZA-IGN-SPK01', 'nama' => 'Busi Iridium Performance (Spark Plug)', 'cat' => 'Mesin', 'merk' => 'NGK Laser Iridium by YZA', 'satuan' => 'pcs', 'beli' => 115000, 'jual' => 160000, 'min' => 8],
            ['kode' => 'YZA-BRK-PADF1', 'nama' => 'Kampas Rem Depan Ceramic (Brake Pad Front)', 'cat' => 'Rem', 'merk' => 'Brembo / YZA Sport', 'satuan' => 'set', 'beli' => 380000, 'jual' => 520000, 'min' => 3],
            ['kode' => 'YZA-BRK-PADR1', 'nama' => 'Kampas Rem Belakang (Brake Pad Rear)', 'cat' => 'Rem', 'merk' => 'Brembo / YZA Sport', 'satuan' => 'set', 'beli' => 320000, 'jual' => 450000, 'min' => 2],
            ['kode' => 'YZA-BRK-FLD01', 'nama' => 'Minyak Rem DOT 4 Synthetic (500 ml)', 'cat' => 'Rem', 'merk' => 'YZA Brake Fluid', 'satuan' => 'botol 500ml', 'beli' => 55000, 'jual' => 80000, 'min' => 5],
            ['kode' => 'YZA-CLT-RAD01', 'nama' => 'Coolant Radiator Long Life Antifreeze (4L)', 'cat' => 'Perawatan', 'merk' => 'YZA Thermal Guard', 'satuan' => 'galon 4L', 'beli' => 90000, 'jual' => 130000, 'min' => 3],
            ['kode' => 'YZA-ELC-BAT01', 'nama' => 'Aki Kering Maintenance Free MF 55D23L', 'cat' => 'Kelistrikan', 'merk' => 'GS Astra / YZA Power', 'satuan' => 'unit', 'beli' => 95000, 'jual' => 1250000, 'min' => 2],
            ['kode' => 'YZA-WPR-SET01', 'nama' => 'Wiper Blade Hybrid Aerodinamis (Sepasang)', 'cat' => 'Perawatan', 'merk' => 'Bosch Aerotwin by YZA', 'satuan' => 'set', 'beli' => 140000, 'jual' => 195000, 'min' => 4],
            ['kode' => 'YZA-CLN-WSH01', 'nama' => 'Cairan Pembersih Kaca Konsentrat Wiper Fluid', 'cat' => 'Perawatan', 'merk' => 'YZA Crystal Clear', 'satuan' => 'botol 1L', 'beli' => 25000, 'jual' => 40000, 'min' => 5],
            ['kode' => 'YZA-CLN-ENG01', 'nama' => 'Engine Flush & Treatment Conditioner (300ml)', 'cat' => 'Oli', 'merk' => 'YZA Engine Care', 'satuan' => 'kaleng 300ml', 'beli' => 60000, 'jual' => 90000, 'min' => 5],
            ['kode' => 'DEV-SP-3', 'nama' => 'Filter Oli Standard Development', 'cat' => 'Filter', 'merk' => 'YZA Standard', 'satuan' => 'pcs', 'beli' => 25000, 'jual' => 35000, 'min' => 5],
        ];

        foreach ($catalog as $item) {
            Sparepart::updateOrCreate(
                ['kode_sparepart' => $item['kode']],
                [
                    'id_kategori' => $catMap[$item['cat']] ?? 1,
                    'nama_sparepart' => $item['nama'],
                    'merk' => $item['merk'],
                    'harga_beli' => $item['beli'],
                    'harga_jual' => $item['jual'],
                    'satuan' => $item['satuan'],
                    'stok' => 0, // Saldo awal 0 sehingga admin dapat mendemokan pengadaan stok masuk secara nyata
                    'stok_minimum' => $item['min'],
                    'status' => 'aktif',
                ]
            );
        }

        // 6. Mobil Showroom Demo & Relasi Kepemilikan Sah (Verified Car Ownership)
        $mobil = Mobil::updateOrCreate(
            ['nomor_rangka' => 'YZA-DEMO-GRSUPRA-2024-001'],
            [
                'kode_mobil' => 'YZA-DEMO-SUPRA-01',
                'merk' => 'Toyota',
                'model' => 'GR Supra',
                'tipe' => 'Performance 3.0L',
                'tahun' => 2024,
                'warna' => 'Prominence Red',
                'transmisi' => 'automatic',
                'bahan_bakar' => 'bensin',
                'nomor_mesin' => 'YZA-DEMO-B58-001',
                'kilometer' => 1200,
                'harga_beli' => 1800000000,
                'harga_jual' => 2200000000,
                'kondisi' => 'bekas',
                'status' => 'tersedia',
                'deskripsi' => 'Toyota GR Supra Performance Demo Unit dengan verifikasi penuh kepemilikan YZA.',
                'tanggal_masuk' => now()->subMonths(2),
            ]
        );

        // Transaksi Penjualan Mobil Demo Lunas
        $txMobil = Transaksi::updateOrCreate(
            ['kode_transaksi' => 'TRX-DEMO-SALE-SUPRA-01'],
            [
                'id_pelanggan' => $customer->id_pelanggan,
                'id_petugas' => $adminPetugas->id_petugas,
                'jenis_transaksi' => 'penjualan_mobil',
                'tanggal' => now()->subMonth(),
                'subtotal' => 2200000000,
                'diskon' => 0,
                'total' => 2200000000,
                'status' => 'lunas',
                'catatan' => 'Penjualan Mobil Member Resmi (Demo Dataset)',
            ]
        );

        $penjualan = PenjualanMobil::updateOrCreate(
            ['kode_penjualan' => 'SALE-DEMO-SUPRA-01'],
            [
                'id_transaksi' => $txMobil->id_transaksi,
                'id_pelanggan' => $customer->id_pelanggan,
                'id_mobil' => $mobil->id_mobil,
                'id_petugas' => $adminPetugas->id_petugas,
                'tanggal_penjualan' => now()->subMonth(),
                'harga_mobil' => 2200000000,
                'total_akhir' => 2200000000,
                'metode_pembelian' => 'tunai',
                'status' => 'selesai',
                'catatan' => 'Penjualan unit demo selesai',
            ]
        );

        $pesanan = PesananMobilMember::updateOrCreate(
            ['nomor_pesanan' => 'ORD-DEMO-SUPRA-01'],
            [
                'id_pelanggan' => $customer->id_pelanggan,
                'id_mobil' => $mobil->id_mobil,
                'id_transaksi' => $txMobil->id_transaksi,
                'id_penjualan' => $penjualan->id_penjualan,
                'nama_pembeli' => $customer->nama_lengkap,
                'email_pembeli' => $memberUser->email,
                'telepon_pembeli' => $customer->no_hp,
                'alamat_pembeli' => $customer->alamat,
                'snapshot_merk' => $mobil->merk,
                'snapshot_model' => $mobil->model,
                'snapshot_tipe' => $mobil->tipe,
                'snapshot_tahun' => $mobil->tahun,
                'snapshot_warna' => $mobil->warna,
                'snapshot_harga' => $mobil->harga_jual,
                'total_tagihan' => $mobil->harga_jual,
                'syarat_disetujui' => true,
                'status' => 'diserahkan',
                'id_petugas_keputusan' => $adminPetugas->id_petugas,
                'diputuskan_at' => now()->subMonth(),
                'diserahkan_at' => now()->subMonth(),
            ]
        );

        $serahTerima = SerahTerimaPesananMobil::updateOrCreate(
            ['id_pesanan' => $pesanan->id_pesanan],
            [
                'id_petugas' => $adminPetugas->id_petugas,
                'nama_penerima' => $customer->nama_lengkap,
                'nomor_identitas' => '3174000000000009',
                'pelunasan_terverifikasi' => true,
                'dokumen_lengkap' => true,
                'kendaraan_sesuai' => true,
                'catatan' => 'Serah terima resmi kendaraan demo YZA',
                'diserahkan_at' => now()->subMonth(),
            ]
        );

        // Kendaraan Pelanggan Terverifikasi Penuh di Garasi Member
        $kendaraan = KendaraanPelanggan::updateOrCreate(
            ['nomor_polisi' => 'B 1984 YZA'],
            [
                'id_pelanggan' => $customer->id_pelanggan,
                'id_penjualan' => $penjualan->id_penjualan,
                'id_pesanan' => $pesanan->id_pesanan,
                'id_serah_terima' => $serahTerima->id_serah_terima,
                'merk' => $mobil->merk,
                'model' => $mobil->model,
                'tipe' => $mobil->tipe,
                'tahun' => $mobil->tahun,
                'warna' => $mobil->warna,
                'transmisi' => $mobil->transmisi,
                'bahan_bakar' => $mobil->bahan_bakar,
                'nomor_rangka' => $mobil->nomor_rangka,
                'nomor_mesin' => $mobil->nomor_mesin,
                'kilometer' => 1500,
                'status' => 'aktif',
            ]
        );

        DB::statement("SET FOREIGN_KEY_CHECKS=1");

        echo ">>> Isolated Demo Seeding Completed Successfully! <<<\n";
        echo "Admin Demo User: {$adminUser->email}\n";
        echo "Member Demo User: {$memberUser->email}\n";
        echo "Verified Vehicle Nopol: {$kendaraan->nomor_polisi} ({$kendaraan->merk} {$kendaraan->model})\n";
        echo "Sparepart Catalog Count: " . Sparepart::count() . "\n";
    }
}
