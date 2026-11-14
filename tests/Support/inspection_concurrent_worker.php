<?php

if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'testing') exit(2);
foreach (['DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'yza_automotive_laravel_test', 'DB_HOST' => '127.0.0.1',
    'DB_PORT' => getenv('DB_PORT') ?: '3306', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => '', 'DB_URL' => '', 'LOG_CHANNEL' => 'null',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync'] as $key => $value) putenv($key.'='.$value);
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yza_automotive_laravel_test') exit(3);
$data = json_decode(base64_decode($argv[1]), true, 512, JSON_THROW_ON_ERROR);
Illuminate\Support\Carbon::setTestNow($data['now']);
echo "READY\n"; flush();
try {
    if ($data['action'] === 'finalize') app(App\Services\ServisService::class)->finalizeService($data['id'], $data['actor']);
    elseif ($data['action'] === 'qc') app(App\Services\ServisService::class)->addDetail($data['id'], null, 'Concurrent repair', '100', null);
    else {
        $order = App\Models\PesananMobilMember::findOrFail($data['id']);
        $service = app(App\Services\MemberVehiclePurchaseService::class);
        if ($data['action'] === 'handover') $service->handover($order, $data['actor'], ['nama_penerima' => 'Concurrency QA']);
        else $service->completePdi($order, $data['actor'], $data['input']);
    }
    echo "DONE\n";
} catch (RuntimeException $e) { echo "REJECTED\n"; }
catch (Throwable $e) { fwrite(STDERR, get_class($e).': '.$e->getMessage()); exit(1); }
