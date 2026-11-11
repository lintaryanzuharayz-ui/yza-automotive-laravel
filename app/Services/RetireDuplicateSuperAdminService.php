<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\{Crypt, DB, Schema};
use Illuminate\Support\Str;
use RuntimeException;

/** Fixed, owner-authorized maintenance operation. No HTTP endpoint or hard delete. */
class RetireDuplicateSuperAdminService
{
    public function accounts(bool $lock = false): array
    {
        $query = User::with(['role', 'petugas'])->whereIn('username', ['admin506', 'superadmin506'])->orderBy('id_user');
        if ($lock) $query->lockForUpdate();
        $users = $query->get()->keyBy('username');
        $keep = $users->get('admin506'); $target = $users->get('superadmin506');
        if (!$keep || !$target || $keep->getKey() === $target->getKey() || $keep->status !== 'aktif'
            || $keep->petugas?->status !== 'aktif' || $keep->roleKey() !== 'super_admin' || $target->roleKey() !== 'super_admin') {
            throw new RuntimeException('Identitas/akses akun tidak memenuhi audit. Tidak ada perubahan dilakukan.');
        }
        return [$keep, $target];
    }

    public function fingerprints(int $targetId, ?int $staffId): array
    {
        $tables = DB::select('SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_TYPE=\'BASE TABLE\'', [DB::connection()->getDatabaseName()]);
        $result = [];
        foreach ($tables as $table) {
            $query = DB::table($table->name);
            if ($table->name === 'users') $query->where('id_user', '!=', $targetId);
            if ($table->name === 'petugas' && $staffId) $query->where('id_petugas', '!=', $staffId);
            $rows = $query->get()->map(fn ($row) => json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))->all();
            sort($rows, SORT_STRING);
            $result[$table->name] = ['count' => count($rows), 'sha256' => hash('sha256', implode("\n", $rows))];
        }
        ksort($result);
        return $result;
    }

    public function retire(int $expectedTarget, int $expectedKeep): array
    {
        if (!app()->runningInConsole()) throw new RuntimeException('Operasi hanya tersedia melalui maintenance CLI.');
        $result = DB::transaction(function () use ($expectedTarget, $expectedKeep) {
            [$keep, $target] = $this->accounts(true);
            if ((int) $target->getKey() !== $expectedTarget || (int) $keep->getKey() !== $expectedKeep) throw new RuntimeException('Primary key tidak sesuai hasil audit.');
            $staff = $target->petugas;
            if ($staff) DB::table('petugas')->where('id_petugas', $staff->getKey())->lockForUpdate()->first();
            $before = $this->fingerprints($target->getKey(), $staff?->getKey());
            $keepRow = $keep->getRawOriginal();
            $targetRow = $target->getRawOriginal();
            $operation = (string) Str::uuid();
            $directory = storage_path('app/private/account-retirement');
            if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) throw new RuntimeException('Backup directory unavailable.');
            $backup = $directory.'/superadmin506-'.$target->getKey().'-'.$operation.'.json.enc';
            $plaintext = json_encode(['operation' => $operation, 'database' => DB::connection()->getDatabaseName(), 'created_at' => now()->toIso8601String(),
                'target_user' => $targetRow, 'target_staff' => $staff?->getRawOriginal(), 'preserved_account_id' => $keep->getKey(),
                'preserved_tables_before' => $before], JSON_THROW_ON_ERROR);
            $encrypted = Crypt::encryptString($plaintext);
            $handle = fopen($backup, 'x');
            if (!$handle) throw new RuntimeException('Encrypted backup could not be created.');
            try { if (fwrite($handle, $encrypted) !== strlen($encrypted)) throw new RuntimeException('Backup write incomplete.'); }
            finally { fclose($handle); }
            if (Crypt::decryptString(file_get_contents($backup)) !== $plaintext) throw new RuntimeException('Backup verification failed.');
            $changes = ['status' => 'nonaktif', 'updated_at' => now()];
            if (Schema::hasColumn('users', 'remember_token')) $changes['remember_token'] = null;
            if ($target->status !== 'nonaktif') DB::table('users')->where('id_user', $expectedTarget)->where('username', 'superadmin506')->update($changes);
            if ($staff && $staff->status !== 'nonaktif') DB::table('petugas')->where('id_petugas', $staff->getKey())->where('id_user', $expectedTarget)->update(['status' => 'nonaktif', 'updated_at' => now()]);
            if ($before !== $this->fingerprints($expectedTarget, $staff?->getKey()) || $keepRow !== $keep->fresh()->getRawOriginal()
                || $targetRow['password'] !== $target->fresh()->getRawOriginal('password')) throw new RuntimeException('Preservation check failed; rollback.');
            return ['operation' => $operation, 'method' => 'deactivated_history_preserved', 'target_id' => $expectedTarget,
                'target_staff_id' => $staff?->getKey(), 'retained_id' => $expectedKeep, 'backup' => $backup,
                'preserved_tables' => $before, 'historical_rows_unchanged' => true];
        });
        // Status is committed first. A stale or racing session is also blocked by EnsureActiveAccount.
        try { $result['sessions'] = app(AccountSessionRevoker::class)->revoke($expectedTarget); }
        catch (\Throwable $error) { $result['sessions'] = ['error' => $error->getMessage(), 'guard_blocks_inactive_account' => true]; }
        app(MasterAuditService::class)->record('deactivate', 'users', $expectedTarget,
            'Owner-authorized CLI retirement of superadmin506; admin506 retained; operation '.$result['operation'].'; no historical actor reassignment.');
        return $result;
    }
}
