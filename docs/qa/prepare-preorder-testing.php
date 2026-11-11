<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database' => 'yza_automotive_laravel_test']);
Illuminate\Support\Facades\DB::purge('mysql');
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yza_automotive_laravel_test') throw new RuntimeException('Testing database required');
Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => in_array('--catalog',$argv,true) ? 'database/migrations/2026_11_08_200000_add_explicit_catalog_identity_to_mobil.php' : 'database/migrations/2026_11_08_190000_add_preorder_inventory_delivery_outbox.php', '--force' => true]);
echo Illuminate\Support\Facades\Artisan::output();
