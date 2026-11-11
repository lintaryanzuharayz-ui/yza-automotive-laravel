<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.mysql.database'=>'yza_automotive_laravel_test','app.url'=>'http://127.0.0.1:8000']);
Illuminate\Support\Facades\DB::purge('mysql');$db=Illuminate\Support\Facades\DB::connection();
if ($db->getDatabaseName()!=='yza_automotive_laravel_test') throw new RuntimeException('Testing DB required');
$db->beginTransaction();
try {
    $user=App\Models\User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail();Illuminate\Support\Facades\Auth::setUser($user);
    $key='showcase:bugatti-chiron';$model=app(App\Services\CatalogVehicleIdentityService::class)->resolve($key);
    $customer=app(App\Services\ActiveMemberCustomerService::class)->query()->firstOrFail();
    $item=app(App\Services\MemberVehiclePreorderService::class)->create($customer,$model);
    $service=app(App\Services\PreorderManagementService::class);
    $fixtures=json_encode(['detail'=>$service->detail($item,$user),'stock'=>$service->stock($item,$user)],JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT);
    $guard='<script>const qa='.$fixtures.';window.fetch=async(url)=>new Response(JSON.stringify(String(url).endsWith("/detail")?qa.detail:String(url).endsWith("/stock")?qa.stock:{}),{headers:{"Content-Type":"application/json"}});document.addEventListener("submit",e=>e.preventDefault());document.addEventListener("click",e=>{const a=e.target.closest("a");if(!a)return;e.preventDefault();const routes={"/admin/pre-order-mobil":"/__catalog_preorder_qa.html","/admin/mobil":"/__catalog_inventory_qa.html","/admin/mobil/create":"/__catalog_unit_qa.html"};if(routes[a.pathname])location.href=routes[a.pathname]+a.search;});</script>';
    $render=function($path,$name,$query,$callback)use($user,$guard){
        $r=Illuminate\Http\Request::create('http://127.0.0.1:8000/'.$path,'GET',$query);
        $r->setLaravelSession(new Illuminate\Session\Store('catalog-qa',new Illuminate\Session\ArraySessionHandler(120)));
        $r->setRouteResolver(fn()=>app('router')->getRoutes()->getByName($name)->bind($r));$r->setUserResolver(fn()=>$user);
        app()->instance('request',$r);app('url')->setRequest($r);view()->share('errors',new Illuminate\Support\ViewErrorBag());
        return str_replace('<head>','<head>'.$guard,$callback($r)->render());
    };
    file_put_contents(public_path('__catalog_preorder_qa.html'),$render('admin/pre-order-mobil','admin.preorder-mobil.index',['review'=>$item->getKey()],fn($r)=>app(App\Http\Controllers\Admin\MemberVehiclePreorderController::class)->index($r)));
    file_put_contents(public_path('__catalog_inventory_qa.html'),$render('admin/mobil','admin.mobil.index',['catalog'=>$key,'preorder'=>$item->getKey()],fn($r)=>app(App\Http\Controllers\Admin\MobilController::class)->index(app(App\Services\VehicleAvailabilityService::class))));
    file_put_contents(public_path('__catalog_unit_qa.html'),$render('admin/mobil/create','admin.mobil.create',['catalog'=>$key,'preorder'=>$item->getKey()],fn($r)=>app(App\Http\Controllers\Admin\MobilController::class)->create()));
    echo 'Safe static QA rendered from testing transaction; all fixtures rolled back.';
}finally{$db->rollBack();}
