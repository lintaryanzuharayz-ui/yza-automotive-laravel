<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PremiumPerformanceSeeder extends Seeder
{
    public function run(): void
    {
        // Remove only rows from the interrupted first seed attempt; existing catalog rows are untouched.
        $legacyIds = DB::table('mobil')->where('kode_mobil', 'like', 'PREMIUM-%')->pluck('id_mobil');
        if ($legacyIds->isNotEmpty()) {
            DB::table('mobil_galeri')->whereIn('id_warna', DB::table('mobil_warna')->whereIn('id_mobil', $legacyIds)->pluck('id_warna'))->delete();
            DB::table('mobil_warna')->whereIn('id_mobil', $legacyIds)->delete();
            DB::table('foto_mobil')->whereIn('id_mobil', $legacyIds)->delete();
            DB::table('mobil')->whereIn('id_mobil', $legacyIds)->delete();
        }

        $cars = [
            ['lamborghini', 'Revuelto', 2025, 'V12 Hybrid Performance', 12500000000, 'Rosso Mars', '#b5121b', ['giallo' => ['Giallo Inti', '#e5b51b'], 'verde' => ['Verde Selvans', '#18513c'], 'nero' => ['Nero Noctis', '#151618'], 'bianco' => ['Bianco Monocerus', '#f1f1ee']]],
            ['ferrari', 'SF90 Stradale', 2024, 'Assetto Fiorano', 14500000000, 'Rosso Corsa', '#c81722', ['giallo' => ['Giallo Modena', '#e5b51b'], 'blu' => ['Blu Corsa', '#164b86'], 'nero' => ['Nero Daytona', '#151618'], 'bianco' => ['Bianco Avus', '#f3f3f0']]],
            ['porsche', '911 GT3 RS', 2024, '4.0 Performance', 8500000000, 'Guards Red', '#c91c26', ['giallo' => ['Racing Yellow', '#e4b51b'], 'silver' => ['GT Silver Metallic', '#aeb3b8'], 'black' => ['Jet Black Metallic', '#151618'], 'gray' => ['Arctic Grey', '#777d84']]],
            ['mercedes-amg', 'GT Black Series', 2024, 'AMG Performance', 8500000000, 'Magno Grey', '#555961', ['black' => ['Obsidian Black', '#151618'], 'yellow' => ['Solarbeam Yellow', '#e1b21c'], 'red' => ['Patagonia Red', '#9d171f']]],
            ['aston-martin', 'DBS 770 Ultimate', 2024, 'V12 Ultimate', 12000000000, 'Minotaur Green', '#315b46', ['gray' => ['Xenon Grey', '#737981'], 'black' => ['Onyx Black', '#151618'], 'silver' => ['Magnetic Silver', '#b8bdc2']]],
            ['chevrolet', 'Corvette Z06', 2024, '6.2L V8 Performance', 3500000000, 'Torch Red', '#c81c26', ['black' => ['Black', '#151618'], 'white' => ['Arctic White', '#f1f1ee'], 'blue' => ['Rapid Blue', '#1d609f'], 'yellow' => ['Accelerate Yellow', '#e3b51d']]],
            ['ford', 'Mustang Shelby GT500', 2024, '5.2L V8 Shelby', 3200000000, 'Grabber Blue', '#1d5e9c', ['red' => ['Race Red', '#b71c25'], 'black' => ['Shadow Black', '#151618'], 'white' => ['Oxford White', '#f1f1ee']]],
            ['dodge', 'Challenger SRT Hellcat Redeye', 2023, '6.2L Supercharged V8', 2500000000, 'TorRed', '#c71a25', ['black' => ['Pitch Black', '#151618'], 'blue' => ['Frostbite Blue', '#1e5790'], 'gray' => ['Destroyer Grey', '#737981']]],
            ['nissan', 'GT-R Nismo', 2024, 'Nismo Performance', 6500000000, 'Nismo Stealth Gray', '#565b62', ['white' => ['Pearl White', '#f1f1ee'], 'red' => ['Vibrant Red', '#bd1a24'], 'silver' => ['Super Silver', '#b8bdc2']]],
            ['lexus', 'LFA', 2012, '4.8L V10 Performance', 9000000000, 'Pearl White', '#f1f1ee', ['red' => ['Red', '#b51b24'], 'silver' => ['Silver Metallic', '#b8bdc2'], 'black' => ['Matte Black', '#151618']]],
        ];

        foreach ($cars as $index => [$brand, $model, $year, $type, $price, $defaultName, $defaultHex, $extras]) {
            $slug = trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $brand.'-'.$model)), '-');
            $code = 'PREM-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $car = DB::table('mobil')->where('merk', ucwords(str_replace('-', ' ', $brand)))->where('model', $model)->where('kode_mobil', 'like', 'PREM-%')->first();
            if (!$car) {
                $id = DB::table('mobil')->insertGetId([
                    'kode_mobil' => $code, 'merk' => ucwords(str_replace('-', ' ', $brand)), 'model' => $model, 'tipe' => $type,
                    'tahun' => $year, 'warna' => $defaultName, 'transmisi' => 'automatic', 'bahan_bakar' => 'bensin',
                    'nomor_rangka' => $code.'-VIN', 'nomor_mesin' => $code.'-ENGINE', 'kilometer' => 0,
                    'harga_beli' => $price * 0.9, 'harga_jual' => $price, 'kondisi' => 'baru', 'status' => 'tersedia',
                    'deskripsi' => $model.' premium performance dengan karakter eksklusif dan pengalaman berkendara YZA Automotive.',
                    'tanggal_masuk' => now()->toDateString(), 'sumber_mobil' => 'dealer', 'created_at' => now(), 'updated_at' => now(),
                ]);
            } else { $id = $car->id_mobil; }

            $existingColorIds = DB::table('mobil_warna')->where('id_mobil', $id)->pluck('id_warna');
            DB::table('mobil_galeri')->whereIn('id_warna', $existingColorIds)->delete();
            DB::table('mobil_warna')->where('id_mobil', $id)->delete();
            $colors = [['default', $defaultName, $defaultHex, true]];
            foreach ($extras as $key => [$name, $hex]) $colors[] = [$key, $name, $hex, false];
            foreach ($colors as $order => [$colorKey, $name, $hex, $isDefault]) {
                $colorSlug = Str::slug($name);
                DB::table('mobil_warna')->updateOrInsert(
                    ['id_mobil' => $id, 'slug' => $colorSlug],
                    ['nama' => $name, 'hex' => $hex, 'is_default' => $isDefault, 'urutan' => $order, 'created_at' => now(), 'updated_at' => now()]
                );
                $colorId = DB::table('mobil_warna')->where(['id_mobil' => $id, 'slug' => $colorSlug])->value('id_warna');
                foreach (['front', 'side', 'rear', 'interior'] as $angle) {
                    DB::table('mobil_galeri')->updateOrInsert(
                        ['id_warna' => $colorId, 'sudut' => $angle],
                        ['path' => "assets/images/vehicles/premium/{$slug}/{$colorSlug}/{$angle}.png", 'urutan' => array_search($angle, ['front', 'side', 'rear', 'interior'], true), 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }
}
