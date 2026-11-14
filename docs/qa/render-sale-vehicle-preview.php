<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database'=>'yza_automotive_laravel_test','app.url'=>'http://127.0.0.1:8000']);
Illuminate\Support\Facades\DB::purge('mysql');$db=Illuminate\Support\Facades\DB::connection();
if($db->getDatabaseName()!=='yza_automotive_laravel_test')throw new RuntimeException('Testing DB required');
$db->beginTransaction();
try {
    $admin=App\Models\User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail();
    Illuminate\Support\Facades\Auth::setUser($admin);
    $member=app(App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
    $staff=App\Models\User::whereHas('role',fn($q)=>$q->where('nama_role','petugas'))->firstOrFail()->petugas->getKey();
    foreach ([['GR Supra','black'],['Challenger SRT Hellcat Redeye','Destroyer Grey']] as [$model,$color]) {
        $order=app(App\Services\MemberVehiclePurchaseService::class)->create($member,App\Models\Mobil::where('model',$model)->firstOrFail(),[
            'nama_pembeli'=>$member->nama_lengkap,'telepon_pembeli'=>'08123456789','alamat_pembeli'=>'Data pengujian','warna_pilihan'=>$color]);
        $order=app(App\Services\MemberVehiclePurchaseService::class)->approve($order,$staff);
        $sale=$order->penjualan;$sale->update(['kode_penjualan'=>'QA-PHOTO-'.($model==='GR Supra'?'SUPRA':'DODGE')]);
        if ($model!=='GR Supra') {$dodge=$sale;$order->update(['status'=>'dibatalkan']);}
    }
    $car=App\Models\Mobil::firstOrFail()->replicate();$unique=bin2hex(random_bytes(4));
    $car->forceFill(['kode_mobil'=>'QA-YARIS-'.$unique,'merk'=>'Toyota','model'=>'Yaris','tipe'=>'1.5 S','tahun'=>2022,
        'warna'=>'Putih','showcase_slug'=>null,'catalog_identity'=>null,'nomor_rangka'=>'QA-VIN-'.$unique,'nomor_mesin'=>'QA-ENGINE-'.$unique])->save();
    $tx=$sale->transaksi->replicate();$tx->forceFill(['kode_transaksi'=>'QA-PHOTO-TX-'.$unique])->save();
    $legacy=$sale->replicate();$legacy->forceFill(['kode_penjualan'=>'QA-PHOTO-YARIS','id_mobil'=>$car->getKey(),'id_transaksi'=>$tx->getKey(),'harga_mobil'=>240000000,'total_akhir'=>240000000,'status'=>'selesai'])->save();
    $guard='<script>window.fetch=async()=>new Response("{}",{headers:{"Content-Type":"application/json"}});document.addEventListener("submit",e=>e.preventDefault());document.addEventListener("click",e=>{if(e.target.closest("a"))e.preventDefault()});</script>';
    $r=Illuminate\Http\Request::create('http://127.0.0.1:8000/admin/penjualan-mobil','GET',['q'=>'QA-PHOTO-']);
    $r->setLaravelSession(new Illuminate\Session\Store('sale-qa',new Illuminate\Session\ArraySessionHandler(120)));
    $r->setRouteResolver(fn()=>app('router')->getRoutes()->getByName('admin.penjualan-mobil.index')->bind($r));$r->setUserResolver(fn()=>$admin);
    app()->instance('request',$r);app('url')->setRequest($r);view()->share('errors',new Illuminate\Support\ViewErrorBag());
    $controller=app(App\Http\Controllers\OperationsPenjualanMobilController::class);
    file_put_contents(public_path('__sale_vehicle_qa.html'),str_replace('<head>','<head>'.$guard,$controller->index($r)->render()));
    file_put_contents(public_path('__sale_vehicle_detail_qa.html'),str_replace('<head>','<head>'.$guard,$controller->show($dodge)->render()));
    echo 'QA sales rendered in testing DB transaction; rolled back.';
} finally {$db->rollBack();}
