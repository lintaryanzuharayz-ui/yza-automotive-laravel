<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::transaction(function (): void {
            $car = DB::table('mobil')->whereIn('kode_mobil', [
                'YZA-TOYOTA-SUPRA-3-0-2025',
                'YZA-TOYOTA-SUPRA-MK4-2025',
            ])->first();

            if (!$car) {
                return;
            }

            DB::table('mobil')->where('id_mobil', $car->id_mobil)->update([
                'kode_mobil' => 'YZA-TOYOTA-SUPRA-MK4-2025',
                'model' => 'Supra MK4',
                'tipe' => '2JZ-GTE Turbo',
                'tahun' => 1998,
                'warna' => 'Supra Red',
                'transmisi' => 'manual',
                'deskripsi' => 'Toyota Supra MK4 A80 klasik dengan bodi coupe 90-an, mesin 2JZ-GTE Turbo, dan karakter ikonik untuk kolektor otomotif.',
                'updated_at' => now(),
            ]);

            DB::table('foto_mobil')->where('id_mobil', $car->id_mobil)->delete();
            DB::table('foto_mobil')->insert([
                'id_mobil' => $car->id_mobil,
                'nama_file' => 'toyota-supra-mk4.png',
                'foto_utama' => true,
                'urutan' => 0,
                'created_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        $car = DB::table('mobil')->where('kode_mobil', 'YZA-TOYOTA-SUPRA-MK4-2025')->first();
        if (!$car) {
            return;
        }

        DB::table('mobil')->where('id_mobil', $car->id_mobil)->update([
            'kode_mobil' => 'YZA-TOYOTA-SUPRA-3-0-2025',
            'model' => 'Supra',
            'tipe' => '3.0L A/T',
            'tahun' => 2025,
            'warna' => 'Supra Red',
            'transmisi' => 'automatic',
            'deskripsi' => 'Toyota Supra 3.0L A/T 2025 dengan mesin turbo, karakter coupe ikonik, dan pengalaman berkendara premium.',
            'updated_at' => now(),
        ]);

        DB::table('foto_mobil')->where('id_mobil', $car->id_mobil)->delete();
        DB::table('foto_mobil')->insert([
            'id_mobil' => $car->id_mobil,
            'nama_file' => 'toyota-supra.png',
            'foto_utama' => true,
            'urutan' => 0,
            'created_at' => now(),
        ]);
    }
};
