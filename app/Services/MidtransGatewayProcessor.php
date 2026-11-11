<?php

namespace App\Services;

use App\Models\{EventPembayaranGateway, Membership, Pembayaran, PembayaranGateway, Transaksi};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Carbon;
use RuntimeException;

/** Shared verification and atomic application for both existing payment purposes. */
class MidtransGatewayProcessor
{
    private const PAID = ['capture', 'settlement'];
    private const FAILED = ['deny', 'cancel', 'expire', 'failure'];
    private const AFTER_PAYMENT = ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'];

    public function notification(array $data, bool $membership): PembayaranGateway
    {
        if (!app(MidtransClient::class)->verifySignature($data)) throw new RuntimeException('Signature Midtrans tidak valid.');
        $gateway = PembayaranGateway::where('order_id', $data['order_id'])->first();
        if (!$gateway || (bool) $gateway->id_membership !== $membership) throw new RuntimeException('Order Midtrans tidak dikenal.');
        // Signature does not cover every status/fraud field: use authenticated
        // Get Status as the authoritative decision, never browser/callback claims.
        if (!$this->validShape($data) || $this->cents($data['gross_amount']) !== $this->cents($gateway->gross_amount)) {
            return $this->apply($gateway, $data, $data, 'notification_mismatch');
        }
        return $this->apply($gateway, app(MidtransClient::class)->getTransactionStatus($gateway->order_id), $data);
    }

    public function inquiry(PembayaranGateway $gateway): PembayaranGateway
    {
        return $this->apply($gateway, app(MidtransClient::class)->getTransactionStatus($gateway->order_id));
    }

    public function cents(mixed $value): ?int
    {
        if (!is_string($value) && !is_int($value)) return null;
        if (!preg_match('/^(0|[1-9][0-9]{0,12})(?:\.([0-9]{1,2}))?$/D', (string) $value, $m)) return null;
        return (int) $m[1] * 100 + (int) str_pad($m[2] ?? '', 2, '0');
    }

    private function validShape(array $data): bool
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'transaction_status'] as $field) {
            if (!isset($data[$field]) || (!is_string($data[$field]) && !is_int($data[$field]))) return false;
        }
        foreach (['fraud_status', 'payment_type', 'transaction_id', 'currency'] as $field) {
            if (array_key_exists($field, $data) && !is_string($data[$field])) return false;
        }
        return strlen((string) $data['order_id']) <= 50 && strlen((string) $data['status_code']) <= 10
            && strlen((string) $data['transaction_status']) <= 40 && strlen($data['fraud_status'] ?? '') <= 30
            && strlen($data['payment_type'] ?? '') <= 50 && strlen($data['transaction_id'] ?? '') <= 100
            && $this->cents($data['gross_amount']) !== null;
    }

    private function record(PembayaranGateway $g, array $data, string $source): void
    {
        $safe = [];
        foreach (['order_id', 'status_code', 'gross_amount', 'transaction_status', 'fraud_status', 'payment_type', 'transaction_id', 'currency'] as $key) {
            $safe[$key] = is_scalar($data[$key] ?? null) ? (string) $data[$key] : null;
        }
        // Repeated partial refunds may share status/transaction ID. Distinguish
        // financial revisions without persisting card data or customer details.
        $safe['refund_amount'] = is_scalar($data['refund_amount'] ?? null) ? (string) $data['refund_amount'] : null;
        $safe['refunds'] = array_map(static fn ($refund) => is_array($refund)
            ? array_intersect_key($refund, array_flip(['refund_chargeback_id', 'refund_amount', 'created_at'])) : [],
            is_array($data['refunds'] ?? null) ? $data['refunds'] : []);
        $hash = hash('sha256', json_encode([$source, $safe]));
        $eventKey = 'midtrans:'.$g->id_pembayaran_gateway.':'.$hash;
        if (EventPembayaranGateway::where('event_key', $eventKey)->lockForUpdate()->exists()) return;
        EventPembayaranGateway::create(['event_key' => $eventKey,
            'id_pembayaran_gateway' => $g->id_pembayaran_gateway,
            'transaction_status' => substr($safe['transaction_status'] ?? 'unknown', 0, 40),
            'status_code' => substr($safe['status_code'] ?? '', 0, 10),
            'fraud_status' => isset($safe['fraud_status']) ? substr($safe['fraud_status'], 0, 30) : null,
            'payment_type' => isset($safe['payment_type']) ? substr($safe['payment_type'], 0, 50) : null,
            'provider_transaction_id' => isset($safe['transaction_id']) ? substr($safe['transaction_id'], 0, 100) : null,
            'payload_hash' => $hash, 'received_at' => now(),
        ]);
    }

    private function review(PembayaranGateway $g, string $reason, bool $preserveStatus = false): PembayaranGateway
    {
        $this->record($g, ['transaction_status' => 'requires_review', 'status_code' => 'LOCAL'], $reason);
        Log::warning('midtrans.reconciliation_required', ['gateway_id' => $g->id_pembayaran_gateway, 'reason' => $reason]);
        if (!$preserveStatus) $g->forceFill(['status_provider' => 'requires_review', 'last_synced_at' => now()])->save();
        return $g->fresh();
    }

    private function apply(PembayaranGateway $gateway, array $data, ?array $notification = null, ?string $error = null): PembayaranGateway
    {
        // Read the immutable target reference before opening the transaction;
        // do not establish a stale InnoDB consistent-read snapshot before
        // waiting on the target lock held by another callback.
        $snapshot = PembayaranGateway::findOrFail($gateway->id_pembayaran_gateway);
        return DB::transaction(function () use ($snapshot, $data, $notification, $error) {
            // Lock the shared business target first: different attempts for the
            // same membership/invoice serialize as well as duplicate callbacks.
            $membership = $snapshot->id_membership ? Membership::with('customer.user')->lockForUpdate()->find($snapshot->id_membership) : null;
            $tx = $snapshot->id_transaksi ? Transaksi::with('pelanggan.user')->lockForUpdate()->find($snapshot->id_transaksi) : null;
            $g = PembayaranGateway::lockForUpdate()->findOrFail($snapshot->id_pembayaran_gateway);
            if ($notification) $this->record($g, $notification, 'notification');
            $this->record($g, $data, $error ? 'rejected_notification' : 'inquiry');
            if ($error || !$this->validShape($data)) return $this->review($g, $error ?? 'incomplete_inquiry');
            $gross = $this->cents($data['gross_amount']);
            if ($g->provider !== 'midtrans' || $g->id_membership !== $snapshot->id_membership || $g->id_transaksi !== $snapshot->id_transaksi
                || ((bool) $g->id_membership === (bool) $g->id_transaksi)
                || (string) $data['order_id'] !== (string) $g->order_id || $gross !== $this->cents($g->gross_amount)
                || (isset($data['currency']) && $data['currency'] !== 'IDR')) return $this->review($g, 'identity_amount_mismatch');
            $customer = $membership?->customer ?? $tx?->pelanggan;
            $prefix = $membership ? 'YZA-MBR-'.$membership->id_membership.'-' : 'YZA-'.$tx?->id_transaksi.'-';
            if (!$customer || !$g->initiated_by || (int) $customer->id_user !== (int) $g->initiated_by
                || !str_starts_with($g->order_id, $prefix) || ($membership && $gross !== $this->cents($membership->activation_fee))
                || ($tx && !in_array($tx->jenis_transaksi, MidtransPaymentService::TYPES, true))) return $this->review($g, 'target_binding_mismatch');

            $status = (string) $data['transaction_status'];
            $fraud = $data['fraud_status'] ?? null;
            $method = $data['payment_type'] ?? null;
            $paidStatus = in_array($status, self::PAID, true);
            $success = $paidStatus && filled($data['transaction_id'] ?? null) && (string) $data['status_code'] === '200'
                && ($status === 'capture'
                    ? $method === 'credit_card' && $fraud === 'accept'
                    : filled($method) && ($fraud === 'accept' || (!array_key_exists('fraud_status', $data) && $method !== 'credit_card')));
            $processed = $membership ? ($g->processed_at !== null || ($membership->status === 'active' && (int) $membership->gateway_payment_id === (int) $g->id_pembayaran_gateway)) : $g->id_pembayaran !== null;
            if ($processed && in_array($status, ['pending', 'authorize'], true)) return $g->fresh();
            if ($processed && ((filled($g->provider_transaction_id) && ($data['transaction_id'] ?? null) !== $g->provider_transaction_id)
                || (filled($g->payment_type) && $method !== $g->payment_type))) return $this->review($g, 'paid_identity_changed');

            // Legitimate provider post-payment transitions are recorded and
            // escalated. No automatic refund, membership revocation, stock or
            // ledger reversal without an approved business policy.
            if ($processed && (in_array($status, self::AFTER_PAYMENT, true) || in_array($status, self::FAILED, true))) {
                if (!in_array($g->status_provider, self::AFTER_PAYMENT, true) || $status === $g->status_provider
                    || ($g->status_provider === 'partial_refund' && $status === 'refund')
                    || ($g->status_provider === 'partial_chargeback' && $status === 'chargeback')) {
                    $g->forceFill(['status_provider' => $status, 'last_synced_at' => now()])->save();
                }
                return $this->review($g, 'provider_post_payment_change', true);
            }
            if ($processed && !$success) return $paidStatus ? $this->review($g, 'paid_fraud_conflict', true) : $g->fresh();
            if ($processed && in_array($g->status_provider, array_merge(self::AFTER_PAYMENT, self::FAILED, ['requires_review']), true)) return $g->fresh();
            if ($processed && $success) {
                if ($tx) {
                    $p = Pembayaran::find($g->id_pembayaran);
                    if (!$p || (int) $p->id_transaksi !== (int) $tx->id_transaksi || $p->status !== 'terverifikasi'
                        || $p->arah !== 'masuk' || $this->cents($p->jumlah) !== $gross) return $this->review($g, 'receipt_mismatch');
                }
                if ($g->status_provider !== 'settlement') $g->forceFill(['status_provider' => $status, 'last_synced_at' => now()])->save();
                // Retry a historically interrupted activation, without extending an active membership.
                if ($membership && $membership->status === 'pending_payment') {
                    $membership->forceFill(['status' => 'active', 'activated_at' => $g->processed_at ?: now(), 'gateway_payment_id' => $g->id_pembayaran_gateway])->save();
                }
                return $g->fresh();
            }
            if ($g->status_provider === 'requires_review' || $g->status_provider === 'manual_selected') return $this->review($g, 'manual_reconciliation_required');
            if (!$success) {
                if (in_array($g->status_provider, self::FAILED, true) && $status === 'pending') return $g->fresh();
                $state = in_array($status, array_merge(['pending', 'authorize'], self::FAILED), true)
                    ? ($status === 'authorize' ? 'pending' : $status)
                    : ($paidStatus ? 'pending' : 'requires_review');
                if ($status === 'capture' && $fraud === 'deny') $state = 'deny';
                $g->forceFill(['status_provider' => $state, 'last_synced_at' => now()])->save();
                return $g->fresh();
            }
            if ($membership) {
                if ($membership->status !== 'pending_payment') return $this->review($g, 'membership_already_active_or_cancelled');
            } else {
                $paid = $this->cents((string) $tx->pembayarans()->where('arah', 'masuk')->where('status', 'terverifikasi')->sum('jumlah'));
                if (in_array($tx->status, ['lunas', 'dibatalkan'], true)
                    || $this->cents($tx->total) - $paid !== $gross
                    || $tx->pembayarans()->where('arah', 'masuk')->where('status', 'menunggu')->exists()) return $this->review($g, 'invoice_balance_or_manual_conflict');
                $order = $tx->pesananMobilMember;
                if ($order && !in_array($order->status, ['menunggu_pembayaran', 'lunas'], true)) return $this->review($g, 'order_not_payable');
            }
            $verifiedAt = null;
            foreach (['settlement_time', 'transaction_time'] as $field) {
                if (is_string($data[$field] ?? null)) {
                    try { $verifiedAt = Carbon::parse($data[$field]); break; } catch (\Throwable) {}
                }
            }
            $g->forceFill(['status_provider' => $status, 'payment_type' => $method,
                'provider_transaction_id' => $data['transaction_id'] ?? null, 'last_synced_at' => now()])->save();
            if ($membership) {
                $membership->forceFill(['status' => 'active', 'activated_at' => $verifiedAt ?: now(), 'gateway_payment_id' => $g->id_pembayaran_gateway])->save();
            } else {
                $payment = Pembayaran::withoutEvents(function () use ($tx, $g, $verifiedAt) {
                    $service = app(PaymentService::class);
                    $p = $service->create($tx->id_transaksi, $tx->id_petugas, (string) $g->gross_amount, 'transfer', null, 'Midtrans Sandbox '.$g->order_id);
                    return $service->verify($p->id_pembayaran, $tx->id_petugas, $verifiedAt);
                });
                $g->forceFill(['id_pembayaran' => $payment->id_pembayaran])->save();
            }
            $g->forceFill(['processed_at' => $verifiedAt ?: now()])->save();
            return $g->fresh();
        }, 3);
    }
}

