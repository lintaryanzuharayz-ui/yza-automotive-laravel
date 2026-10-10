<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $code = 'YZA-TOYOTA-GR-SUPRA-2024';
            $existing = DB::table('mobil')->where('kode_mobil', $code)->first();

            if (!$existing) {
                $id = DB::table('mobil')->insertGetId([
                    'kode_mobil' => $code,
                    'merk' => 'Toyota',
                    'model' => 'GR Supra',
                    'tipe' => 'Performance',
                    'tahun' => 2024,
                    'warna' => 'Supra Red',
                    'transmisi' => 'automatic',
                    'bahan_bakar' => 'bensin',
                    'nomor_rangka' => 'YZA-GRSUPRA-2024-001',
                    'nomor_mesin' => 'YZA-GRSUPRA-B58-001',
                    'kilometer' => 0,
                    'harga_beli' => 1850000000,
                    'harga_jual' => 2350000000,
                    'kondisi' => 'baru',
                    'status' => 'tersedia',
                    'deskripsi' => 'Toyota GR Supra Performance 2024 dengan karakter sporty, kabin premium, dan pengalaman berkendara eksklusif.',
                    'tanggal_masuk' => '2026-10-03',
                    'sumber_mobil' => 'dealer',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $id = $existing->id_mobil;
            }

            DB::table('foto_mobil')->updateOrInsert(
                ['id_mobil' => $id, 'nama_file' => 'toyota-gr-supra.png'],
                ['foto_utama' => true, 'urutan' => 0, 'created_at' => now()]
            );
        });
    }

    public function down(): void
    {
        $car = DB::table('mobil')->where('kode_mobil', 'YZA-TOYOTA-GR-SUPRA-2024')->first();
        if (!$car) return;

        DB::table('foto_mobil')->where('id_mobil', $car->id_mobil)->delete();
        DB::table('mobil')->where('id_mobil', $car->id_mobil)->delete();
    }
};
