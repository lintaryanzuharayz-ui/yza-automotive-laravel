<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MobilAuditService
{
    public function record(string $action, int $mobilId, string $description): void
    {
        Log::info('mobil.audit', ['action' => $action, 'id_mobil' => $mobilId, 'description' => $description, 'actor_id' => auth()->id()]);
    }
}
