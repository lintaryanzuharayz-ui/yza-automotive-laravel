<?php

if (PHP_SAPI !== 'cli' || getenv('APP_ENV') !== 'testing') exit(2);
foreach (['DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'yza_automotive_laravel_test', 'DB_HOST' => '127.0.0.1',
    'DB_PORT' => '3306', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => '', 'DB_URL' => '', 'LOG_CHANNEL' => 'null',
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync'] as $key => $value) putenv($key.'='.$value);
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yza_automotive_laravel_test') exit(3);
$data = json_decode(base64_decode($argv[1]), true, 512, JSON_THROW_ON_ERROR);
Illuminate\Support\Carbon::setTestNow($data['now']);
try {
    Illuminate\Support\Facades\DB::transaction(function () use ($data) {
        // Establish a repeatable-read snapshot before either worker obtains the
        // contested business lock. Quota checks must still use current reads.
        Illuminate\Support\Facades\DB::table('promo_redemptions')->where('id_promo', $data['promo'])->count();
        echo "READY\n"; flush();
        if ($data['action'] === 'pay') app(App\Services\PaymentService::class)->create($data['id'], $data['actor'], $data['amount'], 'tunai', null, null);
        elseif ($data['action'] === 'wash') app(App\Services\BookingCuciService::class)->finalizeWithPromo($data['id'], $data['actor'], $data['promo']);
        elseif ($data['action'] === 'car') app(App\Services\PenjualanMobilService::class)->createWithPromo($data['input'], $data['actor'], $data['promo']);
        else app(App\Services\ServisService::class)->finalizeWithPromo($data['id'], $data['actor'], $data['promo']);
    });
    echo "DONE\n";
} catch (RuntimeException $e) { echo "REJECTED\n"; }
catch (Throwable $e) { fwrite(STDERR, get_class($e).': '.$e->getMessage()); exit(1); }
