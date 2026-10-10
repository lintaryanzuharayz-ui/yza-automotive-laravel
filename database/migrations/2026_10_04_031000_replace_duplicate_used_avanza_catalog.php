<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('mobil')->where('id_mobil', 6)->update([
            'kode_mobil' => 'MOB-202609-MOBILIO',
            'merk' => 'Honda',
            'model' => 'Mobilio',
            'tipe' => '1.5 E CVT',
            'warna' => 'Deep Metallic Blue',
            'transmisi' => 'cvt',
            'deskripsi' => 'Honda Mobilio bekas dengan kabin luas dan praktis untuk kebutuhan keluarga.',
            'updated_at' => now(),
        ]);

        DB::table('mobil')->where('id_mobil', 8)->update([
            'kode_mobil' => 'MOB-202609-XENIA',
            'merk' => 'Daihatsu',
            'model' => 'Xenia',
            'tipe' => '1.5 R CVT',
            'warna' => 'Silver Metallic',
            'transmisi' => 'cvt',
            'deskripsi' => 'Daihatsu Xenia bekas yang nyaman dan efisien untuk perjalanan keluarga.',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('mobil')->where('id_mobil', 6)->update([
            'kode_mobil' => 'MOB-202609-75219595', 'merk' => 'Toyota', 'model' => 'Avanza',
            'tipe' => 'QA P4', 'warna' => null, 'transmisi' => null,
            'deskripsi' => null, 'updated_at' => now(),
        ]);
        DB::table('mobil')->where('id_mobil', 8)->update([
            'kode_mobil' => 'MOB-202609-E6CDE9D9', 'merk' => 'Toyota', 'model' => 'Avanza',
            'tipe' => '1.5 G', 'warna' => null, 'transmisi' => null,
            'deskripsi' => null, 'updated_at' => now(),
        ]);
    }
};
