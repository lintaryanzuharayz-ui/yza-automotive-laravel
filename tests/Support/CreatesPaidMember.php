<?php

namespace Tests\Support;

use App\Models\{Membership, Pelanggan, PembayaranGateway};
use App\Services\MidtransGatewayProcessor;

trait CreatesPaidMember
{
    private function activatePaidMember(Pelanggan $customer): Membership
    {
        $membership = $customer->membership ?: Membership::forceCreate([
            'id_pelanggan' => $customer->id_pelanggan, 'member_id' => 'QA-MBR-'.bin2hex(random_bytes(5)),
            'status' => 'pending_payment', 'activation_fee' => 249000, 'registered_at' => now(),
        ]);
        $membership->forceFill(['status' => 'pending_payment', 'gateway_payment_id' => null, 'cancelled_at' => null])->save();
        $gateway = PembayaranGateway::forceCreate([
            'id_membership' => $membership->id_membership, 'provider' => 'midtrans',
            'order_id' => 'YZA-MBR-'.$membership->id_membership.'-'.bin2hex(random_bytes(4)),
            'gross_amount' => $membership->activation_fee, 'status_provider' => 'pending', 'initiated_by' => $customer->id_user,
        ]);
        $payload = [
            'order_id' => $gateway->order_id, 'gross_amount' => (string) $membership->activation_fee,
            'transaction_status' => 'settlement', 'status_code' => '200', 'payment_type' => 'bank_transfer',
            'transaction_id' => 'QA-'.bin2hex(random_bytes(8)), 'fraud_status' => 'accept', 'currency' => 'IDR',
        ];
        $this->mock(\App\Services\MidtransClient::class, function ($mock) use ($gateway, $payload) {
            $mock->shouldReceive('getTransactionStatus')->with($gateway->order_id)->andReturn($payload);
        });
        app(MidtransGatewayProcessor::class)->inquiry($gateway);
        $customer->unsetRelation('membership');
        return $membership->fresh();
    }

    private function newPaidMember(string $name = 'Member QA'): Pelanggan
    {
        $base = \App\Models\User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $user = $base->replicate(); $suffix = bin2hex(random_bytes(6));
        $user->forceFill(['username' => 'member-'.$suffix, 'email' => $suffix.'@example.test', 'status' => 'aktif'])->save();
        $customer = $base->pelanggan->replicate();
        $customer->forceFill(['id_user' => $user->id_user, 'kode_pelanggan' => 'QA-'.$suffix,
            'nama_lengkap' => $name, 'status' => 'aktif'])->save();
        $this->activatePaidMember($customer);
        return $customer->fresh();
    }
}
