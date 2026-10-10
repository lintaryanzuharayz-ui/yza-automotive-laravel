<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MasterAuditService
{
    public function record(string $action, string $table, int $referenceId, string $description): void
    {
        Log::info('master.audit', ['action' => $action, 'table' => $table, 'reference_id' => $referenceId, 'description' => $description, 'actor_id' => auth()->id()]);
    }
}
