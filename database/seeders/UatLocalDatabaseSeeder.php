<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * UAT-only entry point. Existing demo seeders remain fail-closed to their
 * original demo database; this wrapper protects the dedicated UAT database.
 */
class UatLocalDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();
        if ($database !== 'yza_automotive_laravel_uat') {
            throw new \RuntimeException("FAIL-CLOSED: UAT seeder hanya boleh berjalan pada yza_automotive_laravel_uat; aktif: {$database}");
        }

        $this->call([
            IsolatedDemoDatabaseSeeder::class,
            FinalServiceDemoSeeder::class,
        ]);
    }
}
