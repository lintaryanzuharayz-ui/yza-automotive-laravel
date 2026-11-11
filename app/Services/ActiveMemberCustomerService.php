<?php

namespace App\Services;

use App\Models\{Membership, Pelanggan, PembayaranGateway, User};
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;

/** Eligibility for staff-created MEMBER orders, not operational direct sales. */
class ActiveMemberCustomerService
{
    public function query(): Builder
    {
        return Pelanggan::query()->where('pelanggan.status', 'aktif')
            ->whereHas('user', fn ($user) => $user->where('status', 'aktif')
                ->whereHas('role', fn ($role) => $role->where('nama_role', 'pelanggan')))
            ->whereExists(function ($membership) {
                $membership->selectRaw('1')->from('memberships as m')
                    ->whereColumn('m.id_pelanggan', 'pelanggan.id_pelanggan')
                    ->where('m.status', 'active')->whereNotNull('m.activated_at')->whereNull('m.cancelled_at')
                    ->where(function ($paid) {
                        $paid->whereExists(function ($gateway) {
                            $gateway->selectRaw('1')->from('pembayaran_gateway as g')
                                ->whereColumn('g.id_pembayaran_gateway', 'm.gateway_payment_id')
                                ->whereColumn('g.id_membership', 'm.id_membership')
                                ->whereColumn('g.initiated_by', 'pelanggan.id_user')
                                ->whereColumn('g.gross_amount', 'm.activation_fee')
                                ->where('g.provider', 'midtrans')->whereNull('g.id_transaksi')->whereNull('g.id_pembayaran')
                                ->whereIn('g.status_provider', ['settlement', 'capture'])
                                ->whereNotNull('g.provider_transaction_id')->where('g.provider_transaction_id', '<>', '')
                                ->whereNotNull('g.payment_type')->where('g.payment_type', '<>', '')
                                ->whereRaw("g.order_id LIKE CONCAT('YZA-MBR-', m.id_membership, '-%')")
                                ->whereExists(function ($event) {
                                    $event->selectRaw('1')->from('event_pembayaran_gateway as e')
                                        ->whereColumn('e.id_pembayaran_gateway', 'g.id_pembayaran_gateway')
                                        ->whereColumn('e.provider_transaction_id', 'g.provider_transaction_id')
                                        ->whereColumn('e.payment_type', 'g.payment_type')
                                        ->whereColumn('e.transaction_status', 'g.status_provider')
                                        ->where('e.status_code', '200')
                                        ->where(function ($success) {
                                            $success->where(function ($capture) {
                                                $capture->where('e.transaction_status', 'capture')
                                                    ->where('e.payment_type', 'credit_card')->where('e.fraud_status', 'accept');
                                            })->orWhere(function ($settlement) {
                                                $settlement->where('e.transaction_status', 'settlement')
                                                    ->where(function ($fraud) {
                                                        $fraud->where('e.fraud_status', 'accept')
                                                            ->orWhere(fn ($nonCard) => $nonCard->whereNull('e.fraud_status')->where('e.payment_type', '<>', 'credit_card'));
                                                    });
                                            });
                                        });
                                });
                        })->orWhere(function ($legacy) {
                            $legacy->whereNull('m.gateway_payment_id')
                                ->where('m.member_id', 'LIKE', 'YZA-%')
                                ->where('m.registered_at', '<=', '2026-10-20 00:00:00');
                        });
                    });
            });
    }

    public function isEligible(?Pelanggan $customer): bool
    {
        if (!$customer || !$customer->id_pelanggan) {
            return false;
        }

        return $this->query()->whereKey($customer->id_pelanggan)->exists();
    }

    public function assertEligibleLocked(Pelanggan $customer): void
    {
        // Serialize with account revocation and the P0 membership/gateway lifecycle.
        if ($customer->id_user) User::whereKey($customer->id_user)->lockForUpdate()->first();
        $membership = Membership::where('id_pelanggan', $customer->id_pelanggan)->lockForUpdate()->first();
        if ($membership?->gateway_payment_id) PembayaranGateway::whereKey($membership->gateway_payment_id)->lockForUpdate()->first();
        // P0 also recognizes active + linked gateway as already processed for
        // historical activations without processed_at. Require bound paid-event
        // evidence above; never grandfather an unbacked active flag here.
        if (!$this->query()->whereKey($customer->id_pelanggan)->exists()) {
            throw new RuntimeException('Pilih member aktif dengan akun dan pembayaran aktivasi yang sah. Status member mungkin telah berubah.');
        }
    }
}
