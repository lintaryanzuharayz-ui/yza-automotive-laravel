<?php

// Only for the disposable MariaDB instance initialized under .remediation.
// Never reads .env and verifies the server's data directory before any DDL.
if (PHP_SAPI !== 'cli') {
    exit(2);
}

$root = dirname(__DIR__);
chdir($root);
$port = (int) (getenv('YZA_ISOLATED_DB_PORT') ?: 33177);
if ($port < 1024 || $port > 65535 || $port === 3306) {
    throw new RuntimeException('Use a dedicated, non-operational MariaDB port.');
}
$dataDirectory = realpath($root.'/.remediation/mysql-data');
if ($dataDirectory === false) {
    throw new RuntimeException('Initialize the disposable MariaDB instance first.');
}
$pdo = new PDO("mysql:host=127.0.0.1;port={$port}", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$server = $pdo->query('SELECT @@port AS port, @@datadir AS directory')->fetch(PDO::FETCH_ASSOC);
if ((int) $server['port'] !== $port || realpath($server['directory']) !== $dataDirectory) {
    throw new RuntimeException('Refusing to modify a server outside .remediation/mysql-data.');
}
$database = 'yza_automotive_laravel_test';
if ($pdo->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = '{$database}'")->fetchColumn()) {
    throw new RuntimeException('A fresh disposable instance is required; existing test data is preserved.');
}
$pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$settings = [
    'APP_ENV' => 'testing', 'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
    'APP_URL' => 'http://127.0.0.1:8000',
    'APP_CONFIG_CACHE' => $root.'/.remediation/config.php',
    'APP_ROUTES_CACHE' => $root.'/.remediation/routes.php',
    'APP_EVENTS_CACHE' => $root.'/.remediation/events.php',
    'DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => (string) $port,
    'DB_DATABASE' => $database, 'DB_USERNAME' => 'root', 'DB_PASSWORD' => '',
    'DB_URL' => '', 'DB_SOCKET' => '', 'DB_NATIVE_DATABASE' => $database, 'DB_DEMO_DATABASE' => $database,
    'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array', 'BROADCAST_CONNECTION' => 'null', 'LOG_CHANNEL' => 'null',
    'MIDTRANS_ENV' => 'sandbox', 'MIDTRANS_IS_PRODUCTION' => 'false',
    'MIDTRANS_SERVER_KEY' => '', 'MIDTRANS_CLIENT_KEY' => '', 'BCRYPT_ROUNDS' => '4',
    'VIEW_COMPILED_PATH' => $root.'/.remediation/views',
];
if (!is_dir($settings['VIEW_COMPILED_PATH'])) {
    mkdir($settings['VIEW_COMPILED_PATH'], 0700, true);
}
foreach ($settings as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->loadEnvironmentFrom('.env.isolated-unused');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (!app()->environment('testing') || config('database.connections.mysql.database') !== $database
    || (int) config('database.connections.mysql.port') !== $port) {
    throw new RuntimeException('Isolated Laravel configuration verification failed.');
}

// Reserve the explicit legacy IDs used by Stage2TestingSeeder before catalog
// data migrations allocate IDs. This changes only this fresh synthetic dataset.
$initialPaths = [
    'database/migrations/2026_09_28_000000_create_yza_identity_tables.php',
    'database/migrations/2026_09_28_010000_create_mobil_tables.php',
];
if (Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => $initialPaths, '--force' => true]) !== 0) {
    throw new RuntimeException('Initial isolated migrations failed.');
}
Illuminate\Support\Facades\DB::statement('ALTER TABLE mobil AUTO_INCREMENT = 100');
$earlyPaths = array_values(array_map(fn ($path) => 'database/migrations/'.basename($path),
    array_filter(glob($root.'/database/migrations/*.php'),
        fn ($path) => basename($path) < '2026_10_11_050000_create_vehicle_inventory_tables.php')));

foreach ([['migrate', ['--path' => $earlyPaths, '--force' => true]],
    ['db:seed', ['--class' => 'PremiumPerformanceSeeder', '--force' => true]],
    ['migrate', ['--force' => true]],
    ['db:seed', ['--class' => 'Stage2TestingSeeder', '--force' => true]],
    ['db:seed', ['--class' => 'TestingVehicleStockFixtureSeeder', '--force' => true]],
    ['yza:seed-local-staff', []],
    ['db:seed', ['--class' => 'IsolatedRegressionFixtureSeeder', '--force' => true]]] as [$command, $arguments]) {
    $exit = Illuminate\Support\Facades\Artisan::call($command, $arguments);
    echo Illuminate\Support\Facades\Artisan::output();
    if ($exit !== 0) {
        throw new RuntimeException('Isolated preparation failed: '.$command);
    }
}

$xml = new DOMDocument();
$xml->load($root.'/phpunit.xml');
$xpath = new DOMXPath($xml);
foreach ($xpath->query('//directory | //exclude') as $node) {
    $node->nodeValue = $root.'/'.ltrim($node->nodeValue, '/');
}
$xml->documentElement->setAttribute('bootstrap', $root.'/vendor/autoload.php');
$php = $xml->getElementsByTagName('php')->item(0);
foreach ($settings as $name => $value) {
    $existing = $xpath->query('//php/env[@name="'.$name.'"]')->item(0);
    $node = $existing ?: $php->appendChild($xml->createElement('env'));
    $node->setAttribute('name', $name);
    $node->setAttribute('value', $value);
    $node->setAttribute('force', 'true');
}
$xml->save($root.'/.remediation/phpunit.isolated.xml');
echo "Prepared synthetic test database on verified disposable MariaDB port {$port}.\n";
