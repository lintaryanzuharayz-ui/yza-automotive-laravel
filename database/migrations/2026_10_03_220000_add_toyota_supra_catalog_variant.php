<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $code = 'YZA-TOYOTA-SUPRA-3-0-2025';
            $existing = DB::table('mobil')->where('kode_mobil', $code)->first();

            if (!$existing) {
                $id = DB::table('mobil')->insertGetId([
                    'kode_mobil' => $code,
                    'merk' => 'Toyota',
                    'model' => 'Supra',
                    'tipe' => '3.0L A/T',
                    'tahun' => 2025,
                    'warna' => 'Supra Red',
                    'transmisi' => 'automatic',
                    'bahan_bakar' => 'bensin',
                    'nomor_rangka' => 'YZA-SUPRA-2025-001',
                    'nomor_mesin' => 'YZA-SUPRA-B58-001',
                    'kilometer' => 0,
                    'harga_beli' => 1800000000,
                    'harga_jual' => 2268600000,
                    'kondisi' => 'baru',
                    'status' => 'tersedia',
                    'deskripsi' => 'Toyota Supra 3.0L A/T 2025 dengan mesin turbo, karakter coupe ikonik, dan pengalaman berkendara premium.',
                    'tanggal_masuk' => '2026-10-03',
                    'sumber_mobil' => 'dealer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $existing->id_mobil;
            }

            DB::table('foto_mobil')->updateOrInsert(
                ['id_mobil' => $id, 'nama_file' => 'toyota-supra.png'],
                ['foto_utama' => true, 'urutan' => 0, 'created_at' => now()]
            );
        });
    }

    public function down(): void
    {
        $car = DB::table('mobil')->where('kode_mobil', 'YZA-TOYOTA-SUPRA-3-0-2025')->first();
        if (!$car) return;

        DB::table('foto_mobil')->where('id_mobil', $car->id_mobil)->delete();
        DB::table('mobil')->where('id_mobil', $car->id_mobil)->delete();
    }
};
