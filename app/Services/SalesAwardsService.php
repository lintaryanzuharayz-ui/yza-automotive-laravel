<?php

namespace App\Services;

use App\Models\{PenjualanMobil, Petugas, SalesAward, SalesAwardAudit, SalesAwardResult, SalesTarget};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesAwardsService
{
    public const RULE_VERSION = 'units-target-achievement-value-v1';

    public function year(int $year): int
    {
        return min(max($year, 2000), (int) now('Asia/Jakarta')->year);
    }

    public function eligibleSales(int $year): Builder
    {
        $from = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Jakarta')->utc();
        $to = Carbon::create($year, 12, 31, 23, 59, 59, 'Asia/Jakarta')->utc();
        return PenjualanMobil::query()->with(['sales', 'mobil', 'transaksi.pembayarans', 'memberOrder.serahTerima'])
            ->where('penjualan_mobil.status', 'selesai')
            ->whereHas('transaksi', fn ($q) => $q->where('status', '<>', 'dibatalkan'))
            ->whereDoesntHave('transaksi.financialAdjustments', fn ($q) => $q->where('status', 'confirmed'))
            ->whereHas('transaksi.pembayarans', fn ($q) => $q->where('arah', 'masuk')->where('status', 'terverifikasi'))
            ->whereHas('memberOrder', fn ($q) => $q->where('status', 'diserahkan')->whereBetween('diserahkan_at', [$from, $to])->whereHas('serahTerima', fn ($handover) => $handover->where('diserahkan_at', '>=', $from)->where('diserahkan_at', '<=', $to)));
    }

    public function leaderboard(int $year): array
    {
        $year = $this->year($year);
        $sales = Petugas::query()->where('status', 'aktif')->whereHas('user.role', fn ($q) => $q->where('nama_role', 'sales'))->orderBy('nama_lengkap')->get();
        $aggregate = $this->eligibleSales($year)->whereNotNull('id_sales')->selectRaw('id_sales, COUNT(DISTINCT penjualan_mobil.id_penjualan) AS units, SUM(total_akhir) AS amount')->groupBy('id_sales')->get()->keyBy('id_sales');
        $targets = SalesTarget::query()->where('status', 'aktif')->whereIn('id_sales', $sales->pluck('id_petugas'))->whereDate('periode_selesai', '>=', $year.'-01-01')->whereDate('periode_mulai', '<=', $year.'-12-31')->get()->groupBy('id_sales');
        $rows = $sales->map(function (Petugas $person) use ($aggregate, $targets): array {
            $actual = $aggregate->get($person->id_petugas);
            $target = $targets->get($person->id_petugas, collect())->sum('target_unit');
            $units = (int) ($actual->units ?? 0);
            return ['id' => $person->id_petugas, 'name' => $person->nama_lengkap, 'units' => $units, 'amount' => (float) ($actual->amount ?? 0), 'target_units' => $target > 0 ? (int) $target : null, 'achievement' => $target > 0 ? round($units / $target * 100, 2) : null, 'eligibility' => $target > 0 ? 'eligible' : 'target belum tersedia'];
        })->sort(function (array $a, array $b): int {
            return [$b['units'], $b['achievement'] ?? -1, $b['amount'], $a['name']] <=> [$a['units'], $a['achievement'] ?? -1, $a['amount'], $b['name']];
        })->values()->all();
        $rank = 0; $previous = null; $tie = null;
        foreach ($rows as $index => &$row) {
            $key = implode('|', [$row['units'], $row['achievement'] ?? 'na', number_format($row['amount'], 2, '.', '')]);
            if ($key !== $previous) { $rank = $index + 1; $tie = null; $previous = $key; }
            else { $tie ??= 'TIE-'.$rank; }
            $row['rank'] = $rank; $row['tie_group'] = $tie;
        }
        unset($row);
        return $rows;
    }

    public function award(int $year): SalesAward
    {
        return SalesAward::firstOrCreate(['award_year' => $this->year($year)], ['status' => 'provisional', 'rule_version' => self::RULE_VERSION, 'method_snapshot' => $this->methodSnapshot()]);
    }

    public function finalize(int $year, int $actorPetugasId): SalesAward
    {
        $year = $this->year($year);
        if ($year >= (int) now('Asia/Jakarta')->year) throw new RuntimeException('Tahun berjalan belum dapat difinalisasi.');
        if (!$this->refundAuthoritativeAvailable()) throw new RuntimeException('Finalisasi ditolak: sumber refund/reversal authoritative belum tersedia.');
        $rows = $this->leaderboard($year);
        if (collect($rows)->contains(fn ($row) => $row['target_units'] === null)) throw new RuntimeException('Finalisasi ditolak: target unit belum lengkap untuk seluruh Sales.');
        return DB::transaction(function () use ($year, $actorPetugasId, $rows): SalesAward {
            $award = $this->award($year);
            if ($award->status === 'finalized') throw new RuntimeException('Penghargaan tahun ini sudah difinalisasi.');
            $award->update(['status' => 'finalized', 'rule_version' => self::RULE_VERSION, 'method_snapshot' => $this->methodSnapshot(), 'finalized_by' => $actorPetugasId, 'finalized_at' => now()]);
            $award->results()->delete();
            foreach ($rows as $row) $award->results()->create(['id_sales' => $row['id'], 'ranking' => $row['rank'], 'final_units' => $row['units'], 'target_units' => $row['target_units'], 'achievement_percentage' => $row['achievement'], 'final_amount' => $row['amount'], 'eligibility_status' => $row['eligibility'], 'tie_group' => $row['tie_group']]);
            SalesAwardAudit::create(['id_sales_award' => $award->id_sales_award, 'actor_petugas_id' => $actorPetugasId, 'action' => 'finalized', 'payload' => ['year' => $year, 'rule_version' => self::RULE_VERSION, 'result_count' => count($rows)]]);
            return $award->fresh(['results.sales', 'audits']);
        });
    }

    public function refundAuthoritativeAvailable(): bool
    {
        // No authoritative refund/reversal ledger exists in the current schema.
        // Do not infer refund state from free-text notes or provider status labels.
        return false;
    }

    public function methodSnapshot(): array
    {
        return ['rule_version' => self::RULE_VERSION, 'priority' => ['final_units', 'target_achievement', 'final_amount'], 'valid_sale' => ['transaction_not_cancelled', 'verified_incoming_payment', 'handover_status_diserahkan', 'handover_date_in_award_year'], 'digital_sales' => 'Included in company totals but not attributed when id_sales is null', 'timezone' => 'Asia/Jakarta'];
    }
}
