<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database'=>'yza_automotive_laravel_test', 'app.url'=>'http://127.0.0.1:8000']);
Illuminate\Support\Facades\DB::purge('mysql');
$db=Illuminate\Support\Facades\DB::connection();
if ($db->getDatabaseName() !== 'yza_automotive_laravel_test') throw new RuntimeException('Testing DB required');
$db->beginTransaction();
try {
    $user=App\Models\User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail();
    Illuminate\Support\Facades\Auth::setUser($user);
    $car=App\Models\Mobil::has('foto')->firstOrFail();
    $inventory=app(App\Services\VehicleInventoryService::class)->find($car);
    if (!$inventory) $inventory=app(App\Services\VehicleInventoryService::class)->ensure($car,0);
    $inventory->update(['stock_total'=>0,'sold_units'=>0]);
    $customer=app(App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
    $item=App\Models\MemberVehiclePreorder::create(['id_pelanggan'=>$customer->getKey(),'id_mobil'=>$car->getKey(),
        'product_key'=>'qa-preview:'.bin2hex(random_bytes(4)), 'nama_mobil'=>$car->merk.' '.$car->model,'varian'=>$car->tipe,
        'tahun'=>$car->tahun,'warna'=>$car->warna,'harga_referensi'=>$car->harga_jual,'status'=>'menunggu_stok']);
    $service=app(App\Services\PreorderManagementService::class);
    $stock=$service->stock($item,$user);
    app(App\Services\PreorderInventoryNotificationService::class)->activity($item,'stock_checked',['message'=>$stock['message'],'eligible'=>$stock['total']],$user->getKey());
    $detail=$service->detail($item,$user);$stock['history']=$detail['history'];
    $detail['stock_management_url']=str_replace('/admin/mobil','/__preorder_inventory_qa.html',$detail['stock_management_url']);
    $fixtures=json_encode(['detail'=>$detail,'stock'=>$stock],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
    $guard='<script>const qa='.$fixtures.';window.fetch=async(url,options={})=>new Response(JSON.stringify(String(url).endsWith("/detail")?qa.detail:String(url).endsWith("/stock")?qa.stock:{}),{headers:{"Content-Type":"application/json"}});document.addEventListener("submit",e=>e.preventDefault());document.addEventListener("click",e=>{const a=e.target.closest("a");if(!a)return;if(a.pathname==="/admin/pre-order-mobil"){e.preventDefault();location.href="/__preorder_auto_qa.html"+a.search;}else if(a.pathname!=="/__preorder_inventory_qa.html")e.preventDefault();});</script>';
    $render=function(string $path,string $route,array $query,callable $view) use($user,$guard){
        $r=Illuminate\Http\Request::create('http://127.0.0.1:8000/'.$path,'GET',$query);
        $r->setLaravelSession(new Illuminate\Session\Store('qa-preview',new Illuminate\Session\ArraySessionHandler(120)));
        $r->setRouteResolver(fn()=>app('router')->getRoutes()->getByName($route)->bind($r));
        $r->setUserResolver(fn()=>$user);app()->instance('request',$r);app('url')->setRequest($r);view()->share('errors',new Illuminate\Support\ViewErrorBag());
        return str_replace('<head>','<head>'.$guard,$view($r)->render());
    };
    file_put_contents(public_path('__preorder_auto_qa.html'),$render('admin/pre-order-mobil','admin.preorder-mobil.index',['review'=>$item->getKey()],fn($r)=>app(App\Http\Controllers\Admin\MemberVehiclePreorderController::class)->index($r)));
    file_put_contents(public_path('__preorder_inventory_qa.html'),$render('admin/mobil','admin.mobil.index',['q'=>$car->kode_mobil],fn($r)=>app(App\Http\Controllers\Admin\MobilController::class)->index(app(App\Services\VehicleAvailabilityService::class))));
    echo 'Static QA rendered with testing DB rollback; review='.$item->getKey();
} finally { $db->rollBack(); }
