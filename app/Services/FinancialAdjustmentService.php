<?php

namespace App\Services;

use App\Models\{FinancialAdjustment, FinancialAdjustmentEvent, Pembayaran, Petugas};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialAdjustmentService
{
    public const STATUSES = ['requested', 'under_review', 'approved', 'processing', 'confirmed', 'rejected', 'failed'];

    public function request(int $paymentId, float $amount, string $reason, int $actorPetugasId, ?string $evidence = null, string $type = 'refund'): FinancialAdjustment
    {
        return DB::transaction(function () use ($paymentId, $amount, $reason, $actorPetugasId, $evidence, $type): FinancialAdjustment {
            $payment = Pembayaran::lockForUpdate()->with('transaksi')->findOrFail($paymentId);
            if ($payment->arah !== 'masuk' || $payment->status !== 'terverifikasi') throw new RuntimeException('Hanya pembayaran masuk terverifikasi yang dapat diajukan refund.');
            if (\App\Models\PembayaranGateway::where('id_pembayaran', $payment->id_pembayaran)->exists()) throw new RuntimeException('Refund online Midtrans belum diaktifkan; gunakan rekonsiliasi provider terverifikasi.');
            if ($amount <= 0) throw new RuntimeException('Nominal adjustment harus lebih besar dari nol.');
            $reserved = (float) FinancialAdjustment::where('id_pembayaran', $payment->id_pembayaran)->whereIn('status', ['requested', 'under_review', 'approved', 'processing', 'confirmed'])->sum('amount');
            if ($reserved + $amount > (float) $payment->jumlah + 0.005) throw new RuntimeException('Total refund melebihi nominal pembayaran yang dapat dikembalikan.');
            $adjustment = FinancialAdjustment::create(['id_pembayaran' => $payment->id_pembayaran, 'id_transaksi' => $payment->id_transaksi, 'adjustment_type' => $type, 'channel' => 'offline', 'amount' => $amount, 'status' => 'requested', 'reason' => $reason, 'evidence_reference' => $evidence, 'requested_by' => $actorPetugasId]);
            $this->event($adjustment, null, 'requested', $actorPetugasId, $reason, $evidence);
            return $adjustment->fresh(['payment', 'events']);
        });
    }

    public function transition(FinancialAdjustment $adjustment, string $to, int $actorPetugasId, ?string $reason = null, ?string $evidence = null): FinancialAdjustment
    {
        return DB::transaction(function () use ($adjustment, $to, $actorPetugasId, $reason, $evidence): FinancialAdjustment {
            $item = FinancialAdjustment::lockForUpdate()->findOrFail($adjustment->id_financial_adjustment);
            $allowed = ['requested' => ['under_review', 'rejected'], 'under_review' => ['approved', 'rejected'], 'approved' => ['processing', 'rejected'], 'processing' => ['confirmed', 'failed']];
            if (!in_array($to, $allowed[$item->status] ?? [], true)) throw new RuntimeException('Perpindahan status adjustment tidak diizinkan.');
            $from = $item->status;
            if (in_array($to, ['approved', 'confirmed'], true) && (int) $item->requested_by === $actorPetugasId) throw new RuntimeException('Separation of duties: pengusul tidak dapat menyetujui atau mengonfirmasi refund sendiri.');
            if ($to === 'confirmed' && trim((string) ($evidence ?: $item->evidence_reference)) === '') throw new RuntimeException('Konfirmasi refund wajib memiliki referensi bukti dana keluar.');
            $fields = ['status' => $to];
            if ($to === 'approved') $fields += ['approved_by' => $actorPetugasId, 'approved_at' => now()];
            if ($to === 'confirmed') $fields += ['confirmed_by' => $actorPetugasId, 'confirmed_at' => now(), 'evidence_reference' => $evidence ?: $item->evidence_reference];
            if ($to === 'failed') $fields['failure_reason'] = $reason;
            $item->forceFill($fields)->save();
            $this->event($item, $from, $to, $actorPetugasId, $reason, $evidence);
            return $item->fresh(['payment', 'events']);
        });
    }

    public function confirmedForPayment(int $paymentId): float
    {
        return (float) FinancialAdjustment::where('id_pembayaran', $paymentId)->where('status', 'confirmed')->sum('amount');
    }

    public function confirmedForTransaction(int $transactionId): float
    {
        return (float) FinancialAdjustment::where('id_transaksi', $transactionId)->where('status', 'confirmed')->sum('amount');
    }

    private function event(FinancialAdjustment $adjustment, ?string $from, string $to, int $actor, ?string $reason, ?string $evidence): void
    {
        FinancialAdjustmentEvent::create(['id_financial_adjustment' => $adjustment->id_financial_adjustment, 'from_status' => $from, 'to_status' => $to, 'actor_petugas_id' => $actor, 'reason' => $reason, 'evidence_reference' => $evidence]);
    }
}
