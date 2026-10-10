<?php

namespace App\Console\Commands;

use App\Models\{Pelanggan, Transaksi};
use App\Services\YzaPointService;
use Illuminate\Console\Command;

class YzaPointsBackfillCommand extends Command
{
    protected $signature = 'yza:points-backfill {--dry-run : Tampilkan estimasi tanpa menyimpan}';
    protected $description = 'Backfill YZA Points untuk transaksi eligible member yang sudah completed';

    public function handle(YzaPointService $points): int
    {
        $members = Pelanggan::with('user.role')->where('status', 'aktif')
            ->whereHas('user', fn($q) => $q->where('status', 'aktif')->whereHas('role', fn($r) => $r->where('nama_role', 'pelanggan')))->get();
        $eligible = 0; $estimated = 0; $awarded = 0;
        foreach ($members as $customer) {
            if (!$this->option('dry-run')) $points->ensureAccount($customer);
            $transactions = Transaksi::where('id_pelanggan', $customer->id_pelanggan)
                ->where('status', 'lunas')->whereIn('jenis_transaksi', YzaPointService::ELIGIBLE_TYPES)->get();
            foreach ($transactions as $transaction) {
                $value = $points->calculate((string) $transaction->total);
                if ($value <= 0) continue;
                $eligible++; $estimated += $value;
                if (!$this->option('dry-run')) $awarded += $points->awardIfEligible($transaction);
            }
        }
        $this->line('Eligible members: '.$members->count());
        $this->line('Eligible transactions: '.$eligible);
        $this->line('Estimated point awards: '.$estimated);
        if ($this->option('dry-run')) $this->info('Dry-run: tidak ada perubahan disimpan.');
        else $this->info('New points awarded: '.$awarded);
        return self::SUCCESS;
    }
}
