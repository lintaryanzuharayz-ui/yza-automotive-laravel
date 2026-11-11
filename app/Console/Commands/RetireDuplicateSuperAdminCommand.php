<?php

namespace App\Console\Commands;

use App\Services\RetireDuplicateSuperAdminService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RetireDuplicateSuperAdminCommand extends Command
{
    protected $signature = 'yza:retire-duplicate-super-admin {--apply} {--target-id=} {--retained-id=} {--database=}';
    protected $description = 'Retire only superadmin506, preserving admin506 and historical references';

    public function handle(RetireDuplicateSuperAdminService $service): int
    {
        try {
            [$keep, $target] = $service->accounts();
            if (!$this->option('apply')) {
                $this->line(json_encode(['dry_run' => true, 'database' => DB::connection()->getDatabaseName(),
                    'retained' => $keep->only(['id_user','username','status']), 'target' => $target->only(['id_user','username','status']),
                    'method' => 'deactivate account/staff; preserve rows and history'], JSON_PRETTY_PRINT));
                return self::SUCCESS;
            }
            if ($this->option('database') !== DB::connection()->getDatabaseName() || !$this->option('target-id') || !$this->option('retained-id')) {
                throw new \RuntimeException('Apply memerlukan database dan kedua primary key sesuai audit.');
            }
            $result = $service->retire((int) $this->option('target-id'), (int) $this->option('retained-id'));
            $receipt = storage_path('app/private/account-retirement/receipt-'.$result['operation'].'.json');
            file_put_contents($receipt, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            $this->line(json_encode(['method' => $result['method'], 'target_id' => $result['target_id'], 'retained_id' => $result['retained_id'],
                'history_unchanged' => true, 'sessions' => $result['sessions'], 'receipt' => $receipt], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        } catch (\Throwable $error) { $this->error($error->getMessage()); return self::FAILURE; }
    }
}
