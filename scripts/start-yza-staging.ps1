$ErrorActionPreference = 'Stop'

$projectPath = 'C:\xampp\htdocs\yza_automotive_laravel'
$phpPath = 'C:\xampp\php\php.exe'
$databaseName = 'yza_automotive_laravel_staging'
$port = 8001

Set-Location -LiteralPath $projectPath
$env:APP_ENV = 'staging'
$env:APP_URL = "http://127.0.0.1:$port"
$env:DB_DATABASE = $databaseName
$env:MIDTRANS_ENV = 'sandbox'
$env:MIDTRANS_IS_PRODUCTION = 'false'
$env:SESSION_COOKIE = 'yza-staging-session'
$env:SESSION_DRIVER = 'file'
$env:SESSION_DOMAIN = ''
$env:SESSION_PATH = '/'
$env:SESSION_SECURE_COOKIE = 'false'
$env:SESSION_SAME_SITE = 'lax'

$effectiveDatabase = (& $phpPath artisan tinker --execute="echo config('database.connections.mysql.database');").Trim()
if ($effectiveDatabase -ne $databaseName) {
    throw "Staging launch cancelled: effective database was '$effectiveDatabase'."
}

& $phpPath artisan serve --host=127.0.0.1 --port=$port
