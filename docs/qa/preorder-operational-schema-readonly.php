<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db=Illuminate\Support\Facades\DB::connection();
echo json_encode(['database'=>$db->getDatabaseName(),
    'outbox_installed'=>Illuminate\Support\Facades\Schema::hasTable('preorder_inventory_events'),
    'availability_state_installed'=>Illuminate\Support\Facades\Schema::hasColumn('pre_order_mobil_member','stock_generation'),
    'inspection'=>'read-only schema queries; no migration or business writes'],JSON_PRETTY_PRINT);
