<?php

namespace App\Services;

use App\Models\{Pelanggan, PromoMember, PromoRedemption, Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PromoEngineService
{
    // DECIMAL values stay strings until converted into integer cents.
    private function cents(int|float|string|null $value): int
    {
        $decimal = (string) DB::selectOne('SELECT CAST(? AS DECIMAL(15,2)) AS amount', [$value ?? 0])->amount;
        $negative = str_starts_with($decimal, '-');
        [$whole, $fraction] = explode('.', ltrim($decimal, '-'));
        $amount = (int) $whole * 100 + (int) $fraction;
        return $negative ? -$amount : $amount;
    }

    private function amount(int $cents): int|string
    {
        return $cents % 100 === 0 ? intdiv($cents, 100) : intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function calculate(PromoMember $promo, int|float|string $subtotal, string $scope, Pelanggan $customer): array
    {
        $cents = max(0, $this->cents($subtotal));
        $whole = intdiv($cents + 50, 100); // Preserve existing whole-rupiah rounding.
        if (!$promo->isVisibleAt() || $promo->status !== 'aktif') throw new RuntimeException('Promo tidak aktif.');
        if ($promo->berlaku_untuk !== 'semua' && $promo->berlaku_untuk !== $scope) throw new RuntimeException('Promo tidak berlaku untuk transaksi ini.');
        if ($cents < $this->cents($promo->minimum_transaksi)) throw new RuntimeException('Minimum transaksi promo belum terpenuhi.');
        if ($promo->member_only && (!$customer->membership || !$customer->membership->isActive())) throw new RuntimeException('Promo hanya untuk YZA Member aktif.');
        $this->assertQuota($promo, $customer->id_pelanggan);
        $benefit = max(0, $this->cents($promo->nilai_benefit));
        $raw = $promo->jenis_benefit === 'persentase' ? intdiv($whole * $benefit, 10000) : intdiv($benefit + 50, 100);
        $discount = $promo->maksimum_benefit !== null ? min($raw, intdiv($this->cents($promo->maksimum_benefit) + 50, 100)) : $raw;
        $discount = max(0, min(intdiv($cents, 100), $discount));
        return ['promo_id' => $promo->id_promo, 'promo_code' => $promo->kode_promo, 'promo_name' => $promo->nama,
            'subtotal' => $this->amount($cents), 'discount_type' => $promo->jenis_benefit,
            'discount_value' => (float) $promo->nilai_benefit, 'discount' => $discount,
            'max_discount_applied' => $discount < $raw, 'total_final' => $this->amount($cents - $discount * 100),
            'scope' => $scope, 'eligible' => true, 'calculated_at' => now()->toISOString()];
    }

    private function assertQuota(PromoMember $promo, ?int $customerId): void
    {
        $completed = PromoRedemption::where('id_promo', $promo->id_promo)->whereIn('status', ['reserved', 'completed']);
        // A locking read sees the latest committed ledger even when a caller
        // already established a REPEATABLE READ snapshot before waiting.
        if ($promo->quota !== null && (clone $completed)->lockForUpdate()->get(['id_redemption'])->count() >= $promo->quota) throw new RuntimeException('Kuota promo sudah habis.');
        if ($promo->usage_per_member !== null && (clone $completed)->where('id_pelanggan', $customerId)->lockForUpdate()->get(['id_redemption'])->count() >= $promo->usage_per_member) throw new RuntimeException('Batas penggunaan promo per member sudah tercapai.');
    }

    public function applyToTransaction(Transaksi $transaction, int $promoId, string $scope): array
    {
        return DB::transaction(function () use ($transaction, $promoId, $scope): array {
            $tx = Transaksi::lockForUpdate()->findOrFail($transaction->id_transaksi);
            if ($tx->id_promo !== null) throw new RuntimeException('Satu transaksi hanya boleh memakai satu promo.');
            if ($scope !== $tx->jenis_transaksi) throw new RuntimeException('Jenis transaksi promo tidak sesuai.');
            if ($tx->status !== 'belum_bayar' || $tx->pembayarans()->exists() || $tx->gatewayPayments()->exists()) throw new RuntimeException('Promo hanya dapat diterapkan sebelum proses pembayaran dimulai.');
            if ($this->cents($tx->diskon) !== 0) throw new RuntimeException('Harga khusus dan promo merupakan pilihan alternatif dan tidak dapat ditumpuk.');
            if ($this->cents($tx->subtotal) < 0) throw new RuntimeException('Subtotal transaksi tidak valid.');
            $customer = Pelanggan::with('membership')->findOrFail($tx->id_pelanggan);
            $promo = PromoMember::lockForUpdate()->findOrFail($promoId);
            $result = $this->calculate($promo, (string) $tx->subtotal, $scope, $customer);
            $tx->forceFill(['id_promo' => $promo->id_promo, 'promo_code_snapshot' => $promo->kode_promo,
                'promo_name_snapshot' => $promo->nama, 'promo_type_snapshot' => $promo->jenis_benefit,
                'promo_value_snapshot' => $promo->nilai_benefit, 'diskon' => $result['discount'], 'total' => $result['total_final']])->save();
            $this->syncDomainPrice($tx);
            PromoRedemption::create(['id_promo' => $promo->id_promo, 'id_transaksi' => $tx->id_transaksi,
                'id_pelanggan' => $tx->id_pelanggan, 'subtotal' => $tx->subtotal, 'discount' => $tx->diskon,
                'total_final' => $tx->total, 'used_at' => now(), 'status' => 'reserved']);
            return $result;
        });
    }

    private function syncDomainPrice(Transaksi $tx): void
    {
        if ($tx->jenis_transaksi === 'penjualan_mobil' && $this->cents($tx->penjualanMobil?->dp) > $this->cents($tx->total)) throw new RuntimeException('DP melebihi total setelah promo.');
        // Gross line snapshots are retained. Net payable fields match the invoice.
        match ($tx->jenis_transaksi) {
            'servis' => $tx->servis()->update(['total_biaya' => $tx->total]),
            'penjualan_mobil' => $tx->penjualanMobil()->update(['diskon' => $tx->diskon, 'total_akhir' => $tx->total]),
            'penjualan_sparepart' => $tx->penjualanSparepart()->update(['diskon' => $tx->diskon, 'total' => $tx->total]),
            'trade_in' => $tx->tradeIn()->update(['selisih_bayar' => $tx->total]),
            default => null,
        };
    }

    public function finalizeRedemption(Transaksi $transaction): ?PromoRedemption
    {
        return DB::transaction(function () use ($transaction): ?PromoRedemption {
            $tx = Transaksi::lockForUpdate()->findOrFail($transaction->id_transaksi);
            if (!$tx->id_promo || $tx->status !== 'lunas' || !$tx->id_pelanggan) return null;
            $promo = PromoMember::lockForUpdate()->findOrFail($tx->id_promo);
            $existing = PromoRedemption::where('id_transaksi', $tx->id_transaksi)->lockForUpdate()->first();
            if ($existing) {
                if ((int) $existing->id_promo !== (int) $tx->id_promo || (int) $existing->id_pelanggan !== (int) $tx->id_pelanggan) throw new RuntimeException('Riwayat pemakaian promo tidak sesuai transaksi.');
                if ($existing->status === 'completed') return $existing;
                if ($existing->status !== 'reserved') throw new RuntimeException('Reservasi promo sudah dilepas dan tidak dapat dipakai kembali.');
                if ($existing->subtotal !== $tx->subtotal || $existing->discount !== $tx->diskon || $existing->total_final !== $tx->total) throw new RuntimeException('Snapshot reservasi promo tidak sesuai invoice.');
                $existing->forceFill(['status' => 'completed', 'used_at' => now()])->save();
                return $existing;
            }
            // Eligibility/price were snapshotted on the invoice. Do not reprice
            // paid invoices when the campaign changes or expires.
            $this->assertQuota($promo, $tx->id_pelanggan);
            return PromoRedemption::updateOrCreate(['id_promo' => $tx->id_promo, 'id_transaksi' => $tx->id_transaksi],
                ['id_pelanggan' => $tx->id_pelanggan, 'subtotal' => $tx->subtotal, 'discount' => $tx->diskon,
                    'total_final' => $tx->total, 'used_at' => now(), 'status' => 'completed']);
        });
    }

    public function redeem(PromoMember $promo, Transaksi $transaction, Pelanggan $customer, string $scope): PromoRedemption
    {
        $tx = $transaction->fresh();
        if ((int) $tx->id_promo !== (int) $promo->id_promo || (int) $tx->id_pelanggan !== (int) $customer->id_pelanggan
            || $tx->jenis_transaksi !== $scope || $tx->status !== 'lunas') throw new RuntimeException('Pemakaian promo harus sesuai invoice lunas dan pelanggan yang sah.');
        return $this->finalizeRedemption($tx);
    }

    public function releaseReservation(Transaksi $transaction): ?PromoRedemption
    {
        return DB::transaction(function () use ($transaction): ?PromoRedemption {
            $tx = Transaksi::lockForUpdate()->findOrFail($transaction->getKey());
            if (!$tx->id_promo) return null;
            if ($tx->status !== 'dibatalkan') throw new RuntimeException('Kuota promo hanya boleh dilepas setelah transaksi dibatalkan.');
            PromoMember::lockForUpdate()->findOrFail($tx->id_promo);
            $entry = PromoRedemption::where('id_transaksi', $tx->getKey())->lockForUpdate()->first();
            if ($entry && ((int) $entry->id_promo !== (int) $tx->id_promo || (int) $entry->id_pelanggan !== (int) $tx->id_pelanggan)) throw new RuntimeException('Reservasi promo tidak sesuai transaksi.');
            // Completed usage is a receipt, not a reservation. Cancellation is
            // not an authorization for automatic refund/reversal of that receipt.
            if ($entry?->status === 'reserved') $entry->forceFill(['status' => 'released'])->save();
            return $entry;
        });
    }
}
