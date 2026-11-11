<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (in_array('--testing',$argv,true)) {
    config(['database.connections.mysql.database'=>'yza_automotive_laravel_test']);
    Illuminate\Support\Facades\DB::purge('mysql');
}
$db=Illuminate\Support\Facades\DB::connection();$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try {
    $identity=app(App\Services\CatalogVehicleIdentityService::class);$gallery=app(App\Services\VehicleGalleryService::class);
    $rows=$identity->models()->unique(fn($car)=>$identity->key($car))->map(function ($car) use($identity,$gallery) {
        $key=$identity->key($car);$units=$identity->physical($car);$availability=(new App\Services\VehicleAvailabilityService)->for($car);
        $image=$gallery->forColor($car,$car->warna)['images'][0]['src']??null;
        $relative=$image?str_replace(rtrim(config('app.url'),'/').'/', '',$image):'';
        return ['canonical_id'=>$key,'vehicle'=>$car->merk.' '.$car->model,'variant'=>$car->tipe,'year'=>$car->tahun,
            'catalog_color'=>$car->warna,'price'=>$car->harga_jual,'source'=>$car->exists?'mobil':'vehicle_showcases config (virtual)',
            'physical_records'=>$units->count(),'eligible_stock'=>$availability['stock']??0,
            'admin_found'=>$identity->resolve($key)!==null,'gallery_available'=>$relative && file_exists(public_path($relative)),
            'active_preorders'=>$identity->activePreorders($car),'manage_url'=>route('admin.mobil.index',['catalog'=>$key]),
            'public_visible'=>$car->status!=='nonaktif'&&(!str_starts_with($car->kode_mobil,'PREM-')||$gallery->isReady($car)),
            'mapping_ready'=>$identity->resolve($key)!==null];
    })->values();
    $requests=App\Models\MemberVehiclePreorder::all()->map(function ($item) use($identity) {
        $model=$identity->forPreorder($item);return ['id'=>$item->getKey(),'product_key'=>$item->product_key,'mobil_id'=>$item->id_mobil,
            'showcase_slug'=>$item->showcase_slug,'status'=>$item->status,'resolved_catalog'=>$model?$identity->key($model):null];
    });
    $result=['database'=>$db->getDatabaseName(),'read_only'=>true,
        'incorrect_old_table_exists'=>Illuminate\Support\Facades\Schema::hasTable('member_vehicle_preorders'),
        'correct_preorder_table'=> (new App\Models\MemberVehiclePreorder)->getTable(),
        'active_preorders_global'=>App\Models\MemberVehiclePreorder::active()->count(),
        'catalog_identity_column_installed'=>Illuminate\Support\Facades\Schema::hasColumn('mobil','catalog_identity'),
        'notification_outbox_installed'=>Illuminate\Support\Facades\Schema::hasTable('preorder_inventory_events'),
        'vehicles'=>$rows,'preorders'=>$requests];
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
} finally {$db->rollBack();}
