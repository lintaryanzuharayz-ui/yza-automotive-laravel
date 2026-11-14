<?php

namespace App\Console\Commands;

use App\Services\{OwnerAnalyticsService, SalesAwardsService};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class YzaProfileAnalyticsCommand extends Command
{
    protected $signature = 'yza:profile-analytics';
    protected $description = 'Profile Owner and Sales Awards aggregate queries in the current environment';

    public function handle(OwnerAnalyticsService $owner, SalesAwardsService $awards): int
    {
        $queries = 0;
        DB::listen(function () use (&$queries): void { $queries++; });
        $start = microtime(true);
        [$from, $to] = $owner->period();
        $owner->kpis($from, $to);
        $owner->monthlySales($from, $to);
        $owner->salesPerformance($from, $to);
        $awards->leaderboard(now('Asia/Jakarta')->year);
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        $this->line(json_encode(['environment' => app()->environment(), 'queries' => $queries, 'milliseconds' => $elapsed, 'memory_mb' => round(memory_get_peak_usage(true) / 1048576, 2)], JSON_THROW_ON_ERROR));
        return self::SUCCESS;
    }
}
