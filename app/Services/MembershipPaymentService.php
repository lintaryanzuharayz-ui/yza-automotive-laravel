<?php

namespace App\Services;

use App\Models\{Membership, PembayaranGateway};
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
            if ($m->status !== 'pending_payment' || $user->roleName() !== 'pelanggan' || $user->status !== 'aktif') throw new RuntimeException('Membership tidak tersedia untuk pembayaran baru.');
            if ($m->gatewayPayments()->whereIn('status_provider', ['requires_review', 'capture', 'settlement', 'refund', 'partial_refund', 'chargeback', 'partial_chargeback'])->exists()) throw new RuntimeException('Pembayaran membership memerlukan rekonsiliasi.');
            $existing = PembayaranGateway::where('id_membership', $m->id_membership)->whereIn('status_provider', ['created', 'pending'])->latest('id_pembayaran_gateway')->first();
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
                Log::error('YZA membership Midtrans payment initiation failed', ['membership_id' => $m->id_membership, 'order_id' => $order, 'exception_class' => get_class($e)]);
                $gateway->forceFill(['status_provider' => 'failure', 'last_synced_at' => now()])->save();
                throw new RuntimeException('Midtrans Sandbox gagal membuat pembayaran membership.');
            }
        });
    }

    public function processNotification(array $data): PembayaranGateway
    {
        return app(MidtransGatewayProcessor::class)->notification($data, true);
    }

    public function sync(PembayaranGateway $gateway, $user): PembayaranGateway
    {
        $membership = $gateway->membership()->with('customer')->firstOrFail();
        abort_unless(!$gateway->id_transaksi && $user->roleName() === 'pelanggan'
            && (int) $membership->customer->id_user === (int) $user->id_user, 403);
        return app(MidtransGatewayProcessor::class)->inquiry($gateway);
    }
}
