<?php

namespace App\Services;

use App\Models\{Pelanggan, PoinMember, RiwayatPoin, Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class YzaPointService
{
    public const ELIGIBLE_TYPES = ['servis', 'cuci_mobil', 'penjualan_sparepart', 'penjualan_mobil', 'trade_in'];

    public function rate(): int { return (int) config('yza.points.rupiah_per_point', 10000); }

    public function calculate(string|int $total): int
    {
        $normalized = trim(str_replace([',', ' '], '', (string) $total));
        $whole = explode('.', $normalized, 2)[0] ?? '0';
        $whole = ltrim($whole, '+-');
        if ($whole === '' || !ctype_digit($whole)) return 0;
        return intdiv((int) $whole, $this->rate());
    }

    public function awardIfEligible(Transaksi $transaction): int
    {
        return DB::transaction(function () use ($transaction): int {
            $tx = Transaksi::lockForUpdate()->findOrFail($transaction->id_transaksi);
            if ($tx->status !== 'lunas' || !in_array($tx->jenis_transaksi, self::ELIGIBLE_TYPES, true)) return 0;
            $customer = Pelanggan::with('user.role')->find($tx->id_pelanggan);
            if (!$customer || !$this->isMember($customer)) return 0;
            if (!$this->domainCompleted($tx)) return 0;
            $points = $this->calculate((string) $tx->total);
            if ($points <= 0) return 0;
            $key = 'earn:transaction:'.$tx->id_transaksi;
            if (RiwayatPoin::where('idempotency_key', $key)->lockForUpdate()->exists()) return 0;

            DB::table('poin_member')->insertOrIgnore([
                'id_pelanggan' => $customer->id_pelanggan,
                'saldo' => 0,
                'total_diperoleh' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $account = PoinMember::where('id_pelanggan', $customer->id_pelanggan)->lockForUpdate()->firstOrFail();
            $newBalance = (int) $account->saldo + $points;
            $ledger = new RiwayatPoin();
            $ledger->forceFill([
                'id_pelanggan' => $customer->id_pelanggan,
                'id_transaksi' => $tx->id_transaksi,
                'jenis' => 'perolehan',
                'perubahan_poin' => $points,
                'saldo_setelah' => $newBalance,
                'idempotency_key' => $key,
                'keterangan' => 'Perolehan YZA Points dari '.$tx->kode_transaksi,
            ])->save();
            $account->forceFill([
                'saldo' => $newBalance,
                'total_diperoleh' => (int) $account->total_diperoleh + $points,
            ])->save();
            return $points;
        });
    }

    public function ensureAccount(Pelanggan $customer): ?PoinMember
    {
        if (!$this->isMember($customer)) return null;
        DB::table('poin_member')->insertOrIgnore([
            'id_pelanggan' => $customer->id_pelanggan,
            'saldo' => 0,
            'total_diperoleh' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return PoinMember::where('id_pelanggan', $customer->id_pelanggan)->first();
    }

    public function isMember(Pelanggan $customer): bool
    {
        return $customer->status === 'aktif'
            && $customer->user?->status === 'aktif'
            && $customer->user?->role?->nama_role === 'pelanggan'
            && (!$customer->membership || $customer->membership->isActive());
    }

    public function label(string $type): string
    {
        return [
            'servis' => 'Servis Kendaraan',
            'cuci_mobil' => 'Car Wash',
            'penjualan_sparepart' => 'Pembelian Spare Part',
            'penjualan_mobil' => 'Pembelian Mobil',
            'trade_in' => 'Trade-In',
        ][$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    private function domainCompleted(Transaksi $tx): bool
    {
        return match ($tx->jenis_transaksi) {
            'servis' => DB::table('servis')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            'cuci_mobil' => DB::table('booking_cuci')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            'penjualan_sparepart' => DB::table('penjualan_sparepart')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            'penjualan_mobil' => DB::table('penjualan_mobil')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')
                ->whereExists(fn($q) => $q->from('mobil')->whereColumn('mobil.id_mobil', 'penjualan_mobil.id_mobil')->where('mobil.status', 'terjual'))->exists(),
            'trade_in' => DB::table('trade_in')->where('id_transaksi', $tx->id_transaksi)->where('status', 'selesai')->exists(),
            default => false,
        };
    }
}
