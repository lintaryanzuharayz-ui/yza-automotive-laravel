<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db=Illuminate\Support\Facades\DB::connection();$db->statement('SET TRANSACTION READ ONLY');$db->beginTransaction();
try {
    $service=app(App\Services\SaleVehiclePresentationService::class);
    $rows=App\Models\PenjualanMobil::with(['mobil.foto','mobil.warnaPilihan.galeri','pelanggan','memberOrders'])->get()->map(function ($sale) use($service) {
        $data=$service->for($sale);
        return ['sale_id'=>$sale->getKey(),'sale_code'=>$sale->kode_penjualan,'mobil_id'=>$sale->id_mobil,
            'customer_id'=>$sale->id_pelanggan,'order_ids'=>$sale->memberOrders->modelKeys(),'sale_total'=>$sale->total_akhir,
            'current_unit_color'=>$sale->mobil?->warna,'source'=>$data['source'],'vehicle'=>$data['name'],
            'variant'=>$data['variant'],'year'=>$data['year'],'ordered_color'=>$data['color'],'image'=>$data['image'],
            'photo_verified'=>$data['has_photo'],'warning'=>$data['warning'],'order_status'=>$data['order_status'],'sale_status'=>$sale->status];
    });
    echo json_encode(['database'=>$db->getDatabaseName(),'read_only'=>true,'sales'=>$rows],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
} finally {$db->rollBack();}
