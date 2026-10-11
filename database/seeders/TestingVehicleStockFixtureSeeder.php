<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Repeatable inventory fixture for the isolated yza_automotive_laravel_test DB.
 * It must never be used against the production database.
 */
class TestingVehicleStockFixtureSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::getDatabaseName() !== 'yza_automotive_laravel_test') {
            throw new RuntimeException('TestingVehicleStockFixtureSeeder hanya boleh dijalankan pada yza_automotive_laravel_test.');
        }

        DB::transaction(function (): void {
            DB::table('mobil')->where('kode_mobil', 'like', 'TEST-STOCK-%')->delete();

            $targets = [
                ['key' => 'toyota-gr-supra', 'code' => 'TGR', 'merk' => 'Toyota', 'model' => 'GR Supra', 'jumlah' => 5],
                ['key' => 'lamborghini-revuelto', 'code' => 'LREV', 'merk' => 'Lamborghini', 'model' => 'Revuelto', 'jumlah' => 8],
                ['key' => 'toyota-supra-mk4', 'code' => 'TSMK4', 'merk' => 'Toyota', 'model' => 'Supra MK4', 'jumlah' => 3],
            ];

            foreach ($targets as $target) {
                $template = DB::table('mobil')
                    ->where('merk', $target['merk'])
                    ->where('model', $target['model'])
                    ->where('status', '!=', 'nonaktif')
                    ->first();

                $templateData = $template ? (array) $template : [
                    'tipe' => 'V12 Hybrid Performance', 'tahun' => 2025, 'warna' => 'Rosso Mars',
                    'transmisi' => 'automatic', 'bahan_bakar' => 'bensin', 'kilometer' => 0,
                    'harga_beli' => 11250000000, 'harga_jual' => 12500000000, 'kondisi' => 'baru',
                    'is_limited_edition' => 0, 'deskripsi' => 'Synthetic test inventory fixture.',
                    'tanggal_masuk' => now()->toDateString(), 'sumber_mobil' => 'dealer',
                ];

                $realAvailable = DB::table('mobil')
                    ->where('merk', $target['merk'])->where('model', $target['model'])
                    ->where('status', 'tersedia')->where('kode_mobil', 'not like', 'TEST-STOCK-%')->count();
                if ($realAvailable > $target['jumlah']) {
                    throw new RuntimeException("Fixture {$target['key']} menemukan stok nyata melebihi target.");
                }

                for ($index = $realAvailable; $index < $target['jumlah']; $index++) {
                    $row = $templateData;
                    unset($row['id_mobil'], $row['showcase_slug'], $row['created_at'], $row['updated_at']);
                    $suffix = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                    $row['kode_mobil'] = 'TEST-STOCK-'.$target['code'].'-'.$suffix;
                    $row['merk'] = $target['merk'];
                    $row['model'] = $target['model'];
                    $row['nomor_rangka'] = 'TEST-STOCK-VIN-'.$target['code'].'-'.$suffix;
                    $row['nomor_mesin'] = 'TEST-STOCK-ENGINE-'.$target['code'].'-'.$suffix;
                    $row['status'] = 'tersedia';
                    $row['created_at'] = now();
                    $row['updated_at'] = now();
                    DB::table('mobil')->insert($row);
                }
            }
        });
    }
}
