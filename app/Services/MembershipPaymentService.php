<?php

namespace App\Services;

use App\Models\{EventPembayaranGateway, Membership, PembayaranGateway};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class MembershipPaymentService
{
    public function createAttempt(Membership $membership, $user): PembayaranGateway
    {
        return DB::transaction(function () use ($membership, $user) {
            $m = Membership::lockForUpdate()->with('customer.user')->findOrFail($membership->id_membership);
            if ($m->customer?->user?->id_user !== $user->id_user) throw new RuntimeException('Membership bukan milik akun ini.');
            if ($m->isActive()) throw new RuntimeException('YZA Member sudah aktif.');
            $existing = PembayaranGateway::where('id_membership', $m->id_membership)->whereIn('status_provider', ['created', 'pending'])->latest()->first();
            if ($existing && filled($existing->redirect_url)) return $existing;
            if ($existing) $existing->forceFill(['status_provider' => 'failure', 'last_synced_at' => now()])->save();
            $order = 'YZA-MBR-'.$m->id_membership.'-'.strtoupper(Str::random(8));
            $gateway = new PembayaranGateway();
            $gateway->forceFill(['id_membership' => $m->id_membership, 'provider' => 'midtrans', 'order_id' => $order, 'gross_amount' => $m->activation_fee, 'status_provider' => 'created', 'initiated_by' => $user->id_user])->save();
            try {
                $data = app(MidtransClient::class)->createSnapTransaction([
                    'transaction_details' => ['order_id' => $order, 'gross_amount' => (int) round($m->activation_fee)],
                    'customer_details' => ['first_name' => $m->customer->nama_lengkap, 'email' => $m->customer->user?->email, 'phone' => $m->customer->no_hp],
                    'item_details' => [['id' => 'yza-member-activation', 'price' => (int) round($m->activation_fee), 'quantity' => 1, 'name' => 'YZA Member Activation']],
                    'callbacks' => ['finish' => route('pelanggan.membership.finish')],
                ]);
                $gateway->forceFill(['status_provider' => 'pending', 'snap_token' => $data['token'], 'redirect_url' => $data['redirect_url'], 'last_synced_at' => now()])->save();
                return $gateway->fresh();
            } catch (\Throwable $e) {
                Log::error('YZA membership Midtrans payment initiation failed', ['membership_id' => $m->id_membership, 'order_id' => $order, 'exception' => $e->getMessage()]);
                $gateway->forceFill(['status_provider' => 'failure', 'last_synced_at' => now()])->save();
                throw new RuntimeException('Midtrans Sandbox gagal membuat pembayaran membership.');
            }
        });
    }

    public function processNotification(array $data, bool $verifySignature = true): PembayaranGateway
    {
        if ($verifySignature && !app(MidtransClient::class)->verifySignature($data)) throw new RuntimeException('Signature Midtrans tidak valid.');
        $gateway = PembayaranGateway::where('order_id', $data['order_id'] ?? '')->whereNotNull('id_membership')->first();
        if (!$gateway) throw new RuntimeException('Order membership tidak dikenal.');
        return DB::transaction(function () use ($gateway, $data) {
            $g = PembayaranGateway::lockForUpdate()->findOrFail($gateway->id_pembayaran_gateway);
            $status = (string) ($data['transaction_status'] ?? 'pending'); $code = (string) ($data['status_code'] ?? ''); $providerId = $data['transaction_id'] ?? null;
            $eventKey = 'midtrans:'.$g->order_id.':'.$status.':'.$code.':'.($providerId ?? 'none');
            EventPembayaranGateway::firstOrCreate(['event_key' => $eventKey], ['id_pembayaran_gateway' => $g->id_pembayaran_gateway, 'transaction_status' => $status, 'status_code' => $code, 'fraud_status' => $data['fraud_status'] ?? null, 'payment_type' => $data['payment_type'] ?? null, 'provider_transaction_id' => $providerId, 'payload_hash' => hash('sha256', json_encode($data)), 'received_at' => now()]);
            if ((float) ($data['gross_amount'] ?? 0) !== (float) $g->gross_amount) { $g->update(['status_provider' => 'requires_review', 'last_synced_at' => now()]); return $g->fresh(); }
            $success = $status === 'settlement' || ($status === 'capture' && ($data['fraud_status'] ?? null) !== 'deny');
            $g->forceFill(['status_provider' => $success ? ($status === 'capture' ? 'capture' : 'settlement') : (in_array($status, ['deny', 'cancel', 'expire', 'failure'], true) ? $status : 'pending'), 'payment_type' => $data['payment_type'] ?? $g->payment_type, 'provider_transaction_id' => $providerId, 'last_synced_at' => now()])->save();
            if ($success) Membership::lockForUpdate()->whereKey($g->id_membership)->where('status', 'pending_payment')->update(['status' => 'active', 'activated_at' => now(), 'gateway_payment_id' => $g->id_pembayaran_gateway, 'updated_at' => now()]);
            return $g->fresh();
        });
    }

    public function sync(PembayaranGateway $gateway, $user): PembayaranGateway
    {
        $membership = $gateway->membership()->with('customer')->firstOrFail();
        abort_unless((int) $membership->customer->id_user === (int) $user->id_user, 403);
        $data = app(MidtransClient::class)->getTransactionStatus($gateway->order_id);
        $data['order_id'] = $gateway->order_id; $data['gross_amount'] = $data['gross_amount'] ?? $gateway->gross_amount; $data['status_code'] = $data['status_code'] ?? '200';
        return $this->processNotification($data, false);
    }
}
