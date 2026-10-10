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
        DB::table('roles')->insertOrIgnore([
            ['id_role' => 1, 'nama_role' => 'admin', 'deskripsi' => 'Synthetic Stage 2 admin', 'created_at' => $now, 'updated_at' => $now],
            ['id_role' => 2, 'nama_role' => 'petugas', 'deskripsi' => 'Synthetic Stage 2 staff', 'created_at' => $now, 'updated_at' => $now],
            ['id_role' => 3, 'nama_role' => 'pelanggan', 'deskripsi' => 'Synthetic Stage 2 member', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('users')->insertOrIgnore([
            ['id_user' => 1, 'id_role' => 1, 'username' => 'stage2-admin', 'email' => 'stage2-admin@example.test', 'password' => Hash::make('stage2-test-password'), 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id_user' => 2, 'id_role' => 2, 'username' => 'stage2-petugas', 'email' => 'stage2-petugas@example.test', 'password' => Hash::make('stage2-test-password'), 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
            ['id_user' => 3, 'id_role' => 3, 'username' => 'stage2-member', 'email' => 'stage2-member@example.test', 'password' => Hash::make('stage2-test-password'), 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('petugas')->insertOrIgnore(['id_petugas' => 2, 'id_user' => 2, 'kode_petugas' => 'STAGE2-PETUGAS', 'nama_lengkap' => 'Stage 2 Petugas', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('pelanggan')->insertOrIgnore(['id_pelanggan' => 1, 'id_user' => 3, 'kode_pelanggan' => 'STAGE2-MEMBER', 'nama_lengkap' => 'Stage 2 Member', 'no_hp' => '080000000002', 'alamat' => 'Alamat sintetis untuk pengujian', 'status' => 'aktif', 'created_at' => $now, 'updated_at' => $now]);
        DB::table('memberships')->insertOrIgnore(['id_membership' => 1, 'id_pelanggan' => 1, 'member_id' => 'YZA-STAGE2', 'status' => 'active', 'activation_fee' => 249000, 'registered_at' => $now, 'activated_at' => $now, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('mobil')->insertOrIgnore(['id_mobil' => 1, 'kode_mobil' => 'STAGE2-UNIT-001', 'merk' => 'YZA', 'model' => 'Synthetic GT', 'tipe' => 'Stage 2', 'tahun' => 2026, 'warna' => 'Hitam', 'transmisi' => 'automatic', 'bahan_bakar' => 'bensin', 'nomor_rangka' => 'STAGE2-VIN-001', 'nomor_mesin' => 'STAGE2-ENGINE-001', 'kilometer' => 0, 'harga_beli' => 100000000, 'harga_jual' => 150000000, 'kondisi' => 'baru', 'status' => 'tersedia', 'sumber_mobil' => 'dealer', 'created_at' => $now, 'updated_at' => $now]);
    }
}
