<?php
if (PHP_SAPI !== 'cli') exit(2);
$root = dirname(__DIR__,2);
foreach(simplexml_load_file($root.'/phpunit.xml')->php->env as $e){$n=(string)$e['name'];$v=(string)$e['value'];putenv($n.'='.$v);$_ENV[$n]=$_SERVER[$n]=$v;}
require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('testing') || config('database.connections.mysql.database') !== 'yza_automotive_laravel_test') exit(3);
$input=json_decode($argv[1] ?? '',true,512,JSON_THROW_ON_ERROR);
auth()->setUser(App\Models\User::findOrFail($input['actor']));
echo "READY\n";flush();
try {
    app(App\Services\MemberVehiclePurchaseService::class)->createForStaff(
        App\Models\Pelanggan::findOrFail($input['customer']),App\Models\Mobil::findOrFail($input['car']),
        ['nama_pembeli'=>'Race QA','telepon_pembeli'=>'08123456789','alamat_pembeli'=>'Alamat QA',
            'manual_discount_type'=>'percentage','manual_discount_value'=>'30','manual_discount_reason'=>'Race QA']);
    echo "DONE\n";
} catch (RuntimeException $e) { echo "REJECTED\n"; }
