<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection();
$db->statement('SET TRANSACTION READ ONLY'); $db->beginTransaction();
try {
    $users = App\Models\User::with(['role.permissions', 'petugas'])->whereIn('username', ['admin506', 'superadmin506'])->get();
    if ($users->count() !== 2) throw new RuntimeException('Expected exactly two distinct accounts before retirement.');
    $keys = $db->select('SELECT k.TABLE_NAME AS child_table, k.COLUMN_NAME AS child_column, k.REFERENCED_TABLE_NAME AS parent_table, k.REFERENCED_COLUMN_NAME AS parent_column, r.DELETE_RULE AS delete_rule FROM information_schema.KEY_COLUMN_USAGE k JOIN information_schema.REFERENTIAL_CONSTRAINTS r ON r.CONSTRAINT_SCHEMA=k.CONSTRAINT_SCHEMA AND r.CONSTRAINT_NAME=k.CONSTRAINT_NAME AND r.TABLE_NAME=k.TABLE_NAME WHERE k.CONSTRAINT_SCHEMA=? AND k.REFERENCED_TABLE_NAME IS NOT NULL', [$db->getDatabaseName()]);
    $data = [];
    foreach ($users as $user) {
        $refs = [];
        foreach ($keys as $fk) {
            $value = $fk->parent_table === 'users' ? $user->getAttribute($fk->parent_column)
                : ($fk->parent_table === 'petugas' ? $user->petugas?->getAttribute($fk->parent_column) : null);
            if ($value === null) continue;
            $refs[] = (array) $fk + ['rows' => $db->table($fk->child_table)->where($fk->child_column, $value)->count()];
        }
        $data[] = ['username' => $user->username, 'id_user' => $user->getKey(), 'stored_role' => $user->roleName(), 'effective_role' => $user->roleKey(),
            'status' => $user->status, 'last_login' => $user->last_login, 'all_permissions' => $user->roleKey() === 'super_admin',
            'explicit_permissions' => $user->role->permissions->pluck('kode')->all(),
            'staff' => $user->petugas?->only(['id_petugas','kode_petugas','nama_lengkap','status']), 'references' => $refs];
    }
    $columns = $db->select('SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND (COLUMN_NAME IN (\'deleted_at\',\'remember_token\') OR COLUMN_NAME REGEXP \'(^id_user$|_by$|^id_petugas$|^id_sales$)\')', [$db->getDatabaseName()]);
    $sessionCounts = array_fill_keys($users->pluck('id_user')->map(fn ($id) => (string) $id)->all(), 0);
    $actorColumns = [];
    foreach ($columns as $column) {
        if (in_array($column->table_name, ['users', 'petugas'], true)) continue;
        $isFk = collect($keys)->contains(fn ($fk) => $fk->child_table === $column->table_name && $fk->child_column === $column->column_name);
        if ($isFk || !str_ends_with($column->column_name, '_by')) continue;
        $candidateIds = $users->flatMap(fn ($user) => [$user->getKey(), $user->petugas?->getKey()])->filter()->unique()->values();
        $byAccount = [];
        foreach ($users as $account) $byAccount[$account->username] = $db->table($column->table_name)
            ->whereIn($column->column_name, array_values(array_filter([$account->getKey(), $account->petugas?->getKey()])))->count();
        $actorColumns[] = ['table' => $column->table_name, 'column' => $column->column_name, 'by_account_candidates' => $byAccount,
            'candidate_rows' => $db->table($column->table_name)->whereIn($column->column_name, $candidateIds)->count(),
            'note' => 'No FK: a numeric match alone does not prove actor identity'];
    }
    $logEvidence = [];
    foreach (glob(storage_path('logs/laravel*.log')) ?: [] as $log) {
        if (!is_file($log)) continue;
        $counts = array_fill_keys($users->pluck('id_user')->map(fn ($id) => (string) $id)->all(), 0);
        foreach (new SplFileObject($log) as $line) foreach ($counts as $id => $unused) if (preg_match('/"actor_id"\s*:\s*'.preg_quote((string) $id, '/').'\b/', $line)) $counts[$id]++;
        $logEvidence[] = ['file' => basename($log), 'bytes' => filesize($log), 'sha256' => hash_file('sha256', $log), 'actor_occurrences' => $counts];
    }
    $unreadable = 0;
    if (config('session.driver') === 'file') foreach (glob(config('session.files').'/*') ?: [] as $file) {
        if (!is_file($file) || basename($file)[0] === '.') continue;
        $raw = file_get_contents($file);
        if (config('session.encrypt')) { try { $raw = app('encrypter')->decrypt($raw); } catch (Throwable) { $unreadable++; continue; } }
        $payload = config('session.serialization', 'php') === 'json' ? json_decode($raw, true) : @unserialize($raw, ['allowed_classes' => false]);
        if (!is_array($payload)) { $unreadable++; continue; }
        foreach ($payload as $key => $value) if (str_starts_with((string) $key, 'login_') && is_scalar($value) && array_key_exists((string) $value, $sessionCounts)) $sessionCounts[(string) $value]++;
    }
    echo json_encode(['database' => $db->getDatabaseName(), 'read_only' => true, 'users' => $data,
        'session_driver' => config('session.driver'), 'session_encrypted' => (bool) config('session.encrypt'), 'session_lifetime_minutes' => config('session.lifetime'), 'session_matches' => $sessionCounts,
        'unreadable_session_files' => $unreadable, 'auth_columns' => $columns, 'foreign_keys' => $keys, 'non_fk_actor_columns' => $actorColumns, 'audit_logs' => $logEvidence,
        'token_tables' => array_values(array_filter(array_map(fn ($r) => array_values((array) $r)[0], $db->select('SHOW TABLES')), fn ($name) => str_contains($name, 'token') || $name === 'sessions'))], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} finally { $db->rollBack(); }
