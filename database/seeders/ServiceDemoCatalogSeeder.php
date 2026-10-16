<?php

namespace Database\Seeders;

use App\Models\KategoriSparepart;
use App\Models\Sparepart;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ServiceDemoCatalogSeeder extends Seeder
{
    /**
     * Seed katalog sparepart resmi bengkel YZA Automotive untuk demo.
     * Idempotent: aman dijalankan berulang kali (menggunakan updateOrCreate berdasarkan SKU).
     */
    public function run(): void
    {
        // Guard: Tolak eksekusi pada environment production untuk melindungi data bisnis
        if (app()->environment('production')) {
            throw new \RuntimeException('PENTING: ServiceDemoCatalogSeeder dilarang dijalankan pada environment production!');
        }
        $supplier = Supplier::updateOrCreate(
            ['kode_supplier' => 'SUP-YZA-OFFICIAL'],
            [
                'nama_supplier' => 'PT YZA Automotive Parts Indonesia',
                'no_hp' => '081123456789',
                'email' => 'parts@yza-automotive.com',
                'alamat' => 'YZA Automotive Tower, Jl. Otomotif Raya No. 1, Jakarta Barat',
                'kontak_person' => 'Budi Santoso (Logistik)',
                'status' => 'aktif',
            ]
        );

        // 2. Kategori Sparepart Lengkap Sesuai Permintaan Spesifikasi
        $categories = [
            'Oli' => 'Oli & Pelumas Mesin/Transmisi',
            'Filter' => 'Filter Udara, Oli & Kabin AC',
            'Rem' => 'Kampas Rem, Piringan & Minyak Rem',
            'Mesin' => 'Busi, Fanbelt, Komponen Pembakaran',
            'Kelistrikan' => 'Aki, Sekring, Sensor & Kabel',
            'Perawatan' => 'Coolant Radiator, Wiper & Cairan Pembersih',
        ];

        $catModels = [];
        foreach ($categories as $nama => $desc) {
            $catModels[$nama] = KategoriSparepart::firstOrCreate(
                ['nama_kategori' => $nama],
                ['status' => 'aktif', 'kode_kategori' => strtoupper(substr($nama, 0, 3))]
            );
        }

        // 3. 14 Katalog Barang Demo Resmi dengan SKU, Satuan, dan Harga Valid
        $catalog = [
            [
                'kode' => 'YZA-OIL-SYN1L',
                'nama' => 'Oli Mesin Fully Synthetic 0W-20 (1 Liter)',
                'kategori' => 'Oli',
                'merk' => 'YZA Genuine Oil',
                'beli' => 125000,
                'jual' => 165000,
                'satuan' => 'botol 1L',
                'stok' => 24,
                'min' => 6,
            ],
            [
                'kode' => 'YZA-OIL-SYN4L',
                'nama' => 'Oli Mesin Fully Synthetic 5W-30 (Galon 4 Liter)',
                'kategori' => 'Oli',
                'merk' => 'YZA Genuine Oil',
                'beli' => 450000,
                'jual' => 590000,
                'satuan' => 'galon 4L',
                'stok' => 12,
                'min' => 4,
            ],
            [
                'kode' => 'YZA-OIL-ATF1L',
                'nama' => 'Oli Transmisi Matic ATF WS / CVT Fluid (1 Liter)',
                'kategori' => 'Oli',
                'merk' => 'YZA Fluid Tech',
                'beli' => 110000,
                'jual' => 150000,
                'satuan' => 'botol 1L',
                'stok' => 16,
                'min' => 4,
            ],
            [
                'kode' => 'YZA-FLT-OIL01',
                'nama' => 'Filter Oli Mesin Presisi Tinggi (Oil Filter)',
                'kategori' => 'Filter',
                'merk' => 'YZA Parts',
                'beli' => 45000,
                'jual' => 65000,
                'satuan' => 'pcs',
                'stok' => 20,
                'min' => 5,
            ],
            [
                'kode' => 'YZA-FLT-AIR01',
                'nama' => 'Filter Udara Mesin Heavy Duty (Air Filter)',
                'kategori' => 'Filter',
                'merk' => 'YZA Filter',
                'beli' => 95000,
                'jual' => 135000,
                'satuan' => 'pcs',
                'stok' => 15,
                'min' => 4,
            ],
            [
                'kode' => 'YZA-FLT-CAB01',
                'nama' => 'Filter Kabin AC Karbon Aktif (Cabin Air Filter)',
                'kategori' => 'Filter',
                'merk' => 'YZA Filter',
                'beli' => 85000,
                'jual' => 120000,
                'satuan' => 'pcs',
                'stok' => 14,
                'min' => 4,
            ],
            [
                'kode' => 'YZA-IGN-SPK01',
                'nama' => 'Busi Iridium Performance (Spark Plug)',
                'kategori' => 'Mesin',
                'merk' => 'NGK Laser Iridium by YZA',
                'beli' => 115000,
                'jual' => 160000,
                'satuan' => 'pcs',
                'stok' => 32,
                'min' => 8,
            ],
            [
                'kode' => 'YZA-BRK-PADF1',
                'nama' => 'Kampas Rem Depan Ceramic (Brake Pad Front Set)',
                'kategori' => 'Rem',
                'merk' => 'YZA Performance Brakes',
                'beli' => 380000,
                'jual' => 520000,
                'satuan' => 'set',
                'stok' => 10,
                'min' => 3,
            ],
            [
                'kode' => 'YZA-BRK-PADR1',
                'nama' => 'Kampas Rem Belakang (Brake Pad Rear Set)',
                'kategori' => 'Rem',
                'merk' => 'YZA Performance Brakes',
                'beli' => 320000,
                'jual' => 450000,
                'satuan' => 'set',
                'stok' => 8,
                'min' => 2,
            ],
            [
                'kode' => 'YZA-BRK-FLD01',
                'nama' => 'Minyak Rem DOT 4 Synthetic (500 ml)',
                'kategori' => 'Rem',
                'merk' => 'Brembo / YZA Fluid',
                'beli' => 55000,
                'jual' => 80000,
                'satuan' => 'botol 500ml',
                'stok' => 18,
                'min' => 5,
            ],
            [
                'kode' => 'YZA-CLT-RAD01',
                'nama' => 'Coolant Radiator Long Life Antifreeze (Galon 4 Liter)',
                'kategori' => 'Perawatan',
                'merk' => 'YZA Cooling Tech',
                'beli' => 90000,
                'jual' => 130000,
                'satuan' => 'galon 4L',
                'stok' => 12,
                'min' => 3,
            ],
            [
                'kode' => 'YZA-ELC-BAT01',
                'nama' => 'Aki Kering Maintenance Free MF 55D23L (60 Ampere)',
                'kategori' => 'Kelistrikan',
                'merk' => 'GS Astra / YZA Power',
                'beli' => 950000,
                'jual' => 1250000,
                'satuan' => 'unit',
                'stok' => 6,
                'min' => 2,
            ],
            [
                'kode' => 'YZA-WPR-SET01',
                'nama' => 'Wiper Blade Hybrid Aerodinamis (Sepasang 24 + 16 inch)',
                'kategori' => 'Perawatan',
                'merk' => 'Bosch Aerotwin by YZA',
                'beli' => 140000,
                'jual' => 195000,
                'satuan' => 'set',
                'stok' => 15,
                'min' => 4,
            ],
            [
                'kode' => 'YZA-CLN-WSH01',
                'nama' => 'Cairan Pembersih Kaca Konsentrat Wiper Fluid (1 Liter)',
                'kategori' => 'Perawatan',
                'merk' => 'YZA Crystal Clear',
                'beli' => 25000,
                'jual' => 40000,
                'satuan' => 'botol 1L',
                'stok' => 25,
                'min' => 5,
            ],
            [
                'kode' => 'YZA-CLN-ENG01',
                'nama' => 'Engine Flush & Treatment Conditioner (300 ml)',
                'kategori' => 'Oli',
                'merk' => 'YZA Engine Care',
                'beli' => 60000,
                'jual' => 90000,
                'satuan' => 'kaleng 300ml',
                'stok' => 20,
                'min' => 5,
            ],
        ];

        foreach ($catalog as $item) {
            $catId = $catModels[$item['kategori']]->id_kategori ?? 1;

            Sparepart::updateOrCreate(
                ['kode_sparepart' => $item['kode']],
                [
                    'id_kategori' => $catId,
                    'nama_sparepart' => $item['nama'],
                    'merk' => $item['merk'],
                    'harga_beli' => $item['beli'],
                    'harga_jual' => $item['jual'],
                    'satuan' => $item['satuan'],
                    'stok' => $item['stok'],
                    'stok_minimum' => $item['min'],
                    'status' => 'aktif',
                ]
            );
        }
    }
}
