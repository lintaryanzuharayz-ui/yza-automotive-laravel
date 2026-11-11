<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$receiptFiles = glob(storage_path('app/private/account-retirement/receipt-*.json')) ?: [];
usort($receiptFiles, fn ($a, $b) => filemtime($b) <=> filemtime($a));
$receipt = null;
foreach ($receiptFiles as $path) {
    $candidate = json_decode(file_get_contents($path), true);
    if (($candidate['target_id'] ?? null) === 992 && ($candidate['retained_id'] ?? null) === 1) { $receipt = $candidate; break; }
}
if (!$receipt) throw new RuntimeException('Operational receipt not found.');
$db = Illuminate\Support\Facades\DB::connection();
if ($db->getDatabaseName() !== 'yza_automotive_laravel') throw new RuntimeException('Wrong verification database');
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    [$keep, $target] = app(App\Services\RetireDuplicateSuperAdminService::class)->accounts();
    $now = app(App\Services\RetireDuplicateSuperAdminService::class)->fingerprints(992, $receipt['target_staff_id']);
    $diffs = [];
    foreach ($receipt['preserved_tables'] as $table => $fingerprint) if (($now[$table] ?? null) !== $fingerprint) $diffs[] = $table;
    $eligibleKeep = App\Models\User::where('username','admin506')->where('status','aktif')->value('id_user');
    $eligibleTarget = App\Models\User::where('username','superadmin506')->where('status','aktif')->exists();
    $before = json_decode(file_get_contents(__DIR__.'/duplicate-admin-before.json'), true);
    $logPrefixes = [];
    foreach ($before['audit_logs'] ?? [] as $evidence) {
        $logPath = storage_path('logs/'.$evidence['file']); $handle = fopen($logPath, 'rb');
        if (!$handle) throw new RuntimeException('Existing audit log missing');
        $hash = hash_init('sha256'); $remaining = $evidence['bytes'];
        while ($remaining > 0 && !feof($handle)) { $chunk = fread($handle, min(65536, $remaining)); hash_update($hash, $chunk); $remaining -= strlen($chunk); }
        fclose($handle);
        $logPrefixes[$evidence['file']] = $remaining === 0 && hash_final($hash) === $evidence['sha256'];
    }
    $backup = json_decode(Illuminate\Support\Facades\Crypt::decryptString(file_get_contents($receipt['backup'])), true);
    $fkRows = collect($db->select('SELECT CONSTRAINT_NAME AS constraint_name, TABLE_NAME AS child_table, COLUMN_NAME AS child_column, REFERENCED_TABLE_NAME AS parent_table, REFERENCED_COLUMN_NAME AS parent_column FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA=? AND REFERENCED_TABLE_NAME IS NOT NULL', [$db->getDatabaseName()]));
    $orphans = [];
    foreach ($fkRows->groupBy(fn ($row) => $row->child_table.'.'.$row->constraint_name) as $name => $parts) {
        $first = $parts->first();
        $query = $db->table($first->child_table.' as c')->leftJoin($first->parent_table.' as p', function ($join) use ($parts) {
            foreach ($parts as $part) $join->on('c.'.$part->child_column, '=', 'p.'.$part->parent_column);
        });
        foreach ($parts as $part) $query->whereNotNull('c.'.$part->child_column);
        $count = $query->whereNull('p.'.$first->parent_column)->count();
        if ($count) $orphans[$name] = $count;
    }
    $output = ['database' => $db->getDatabaseName(), 'method' => $receipt['method'],
        'retained' => ['id' => $keep->getKey(), 'username' => $keep->username, 'status' => $keep->status, 'staff_status' => $keep->petugas?->status, 'effective_role' => $keep->roleKey(), 'login_eligible' => (int) $eligibleKeep === 1,
            'all_attributes_preserved_at_retirement_commit' => (bool) $receipt['historical_rows_unchanged'],
            'last_login' => $keep->last_login?->toIso8601String()],
        'target' => ['id' => $target->getKey(), 'username' => $target->username, 'status' => $target->status, 'staff_id' => $target->petugas?->getKey(), 'staff_status' => $target->petugas?->status, 'login_eligible' => $eligibleTarget,
            'password_unchanged' => hash_equals($backup['target_user']['password'], $target->getRawOriginal('password'))],
        'other_table_fingerprints_preserved_at_retirement_commit' => (bool) $receipt['historical_rows_unchanged'],
        'later_snapshot_unchanged' => !$diffs, 'later_different_tables' => $diffs, 'preserved_table_count' => count($now),
        'audit_log_existing_prefix_unchanged' => $logPrefixes, 'session_cleanup' => $receipt['sessions'], 'encrypted_backup_verified' => true,
        'target_notifications_retained' => App\Models\Notifikasi::where('id_user', 992)->count(),
        'foreign_key_constraints_checked' => $fkRows->groupBy(fn ($row) => $row->child_table.'.'.$row->constraint_name)->count(), 'foreign_key_orphans' => $orphans];
    if ($eligibleTarget || $target->status !== 'nonaktif' || in_array(false, $logPrefixes, true) || $orphans) throw new RuntimeException('Verification failed');
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} finally { $db->rollBack(); }
