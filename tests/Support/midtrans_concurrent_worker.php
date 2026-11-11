<?php

// CLI-only synthetic provider worker. Never load operational database config.
if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'testing') exit(2);
foreach (['DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'yza_automotive_laravel_test', 'DB_HOST' => '127.0.0.1',
    'DB_PORT' => getenv('DB_PORT') ?: '3306', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => '', 'DB_URL' => '', 'LOG_CHANNEL' => 'null',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'] as $key => $value) putenv($key.'='.$value);
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yza_automotive_laravel_test') exit(3);
config(['midtrans.server_key' => 'stage2-synthetic-key']);
$data = json_decode(base64_decode($argv[1] ?? ''), true, 512, JSON_THROW_ON_ERROR);
Illuminate\Support\Facades\Http::preventStrayRequests();
Illuminate\Support\Facades\Http::fake(['*status' => Illuminate\Support\Facades\Http::response($data)]);
echo "READY\n";
flush();
try {
    $service = str_starts_with($data['order_id'], 'YZA-MBR-')
        ? App\Services\MembershipPaymentService::class : App\Services\MidtransPaymentService::class;
    app($service)->processNotification($data);
    echo "DONE\n";
} catch (Throwable $e) {
    fwrite(STDERR, get_class($e));
    exit(1);
}
