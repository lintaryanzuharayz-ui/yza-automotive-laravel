<?php

// CLI-only aggregate audit. This script never changes operational records.
if (PHP_SAPI !== 'cli') exit(2);
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
DB::statement('SET TRANSACTION READ ONLY');
DB::beginTransaction();
try {
    $counts = ['environment' => app()->environment(), 'database' => DB::connection()->getDatabaseName(), 'read_only' => true];
    $counts['invoice_arithmetic_mismatch'] = DB::table('transaksi')->whereRaw('subtotal - diskon <> total')->count();
    foreach (['servis' => 'total_biaya', 'penjualan_mobil' => 'total_akhir', 'penjualan_sparepart' => 'total', 'trade_in' => 'selisih_bayar'] as $table => $column) {
        $counts[$table.'_promo_payable_mismatch'] = DB::table($table.' as d')->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')->whereNotNull('t.id_promo')->whereColumn('d.'.$column, '<>', 't.total')->count();
    }
    echo json_encode($counts, JSON_PRETTY_PRINT).PHP_EOL;
} finally { DB::rollBack(); }
