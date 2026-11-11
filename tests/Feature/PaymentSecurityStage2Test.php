<?php

namespace Tests\Feature;

use App\Models\{Membership, PembayaranGateway, Transaksi, User};
use App\Services\{MembershipPaymentService, MidtransPaymentService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB, Http};
use Tests\TestCase;

class PaymentSecurityStage2Test extends TestCase
{
    use DatabaseTransactions;
    use \Tests\Support\CompletesQualityChecks;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => 'stage2-synthetic-key']);
        Http::preventStrayRequests();
    }

    private function member(): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
    }

    private function membershipGateway(): PembayaranGateway
    {
        $member = $this->member();
        $membership = $member->pelanggan->membership;
        $membership->forceFill(['status' => 'pending_payment', 'activated_at' => null, 'gateway_payment_id' => null])->save();
        return PembayaranGateway::forceCreate(['id_membership' => $membership->id_membership, 'provider' => 'midtrans',
            'order_id' => 'YZA-MBR-'.$membership->id_membership.'-'.bin2hex(random_bytes(4)),
            'gross_amount' => $membership->activation_fee, 'status_provider' => 'pending', 'initiated_by' => $member->id_user]);
    }

    private function transactionGateway(): PembayaranGateway
    {
        $member = $this->member();
        $tx = Transaksi::forceCreate(['kode_transaksi' => 'STAGE2-'.bin2hex(random_bytes(5)),
            'id_pelanggan' => $member->pelanggan->id_pelanggan,
            'id_petugas' => DB::table('petugas')->where('status', 'aktif')->value('id_petugas'),
            'jenis_transaksi' => 'cuci_mobil', 'tanggal' => now(), 'subtotal' => 100000, 'diskon' => 0, 'total' => 100000, 'status' => 'belum_bayar']);
        return PembayaranGateway::forceCreate(['id_transaksi' => $tx->id_transaksi, 'provider' => 'midtrans',
            'order_id' => 'YZA-'.$tx->id_transaksi.'-'.bin2hex(random_bytes(4)), 'gross_amount' => $tx->total,
            'status_provider' => 'pending', 'initiated_by' => $member->id_user]);
    }

    private function payload(PembayaranGateway $gateway, array $extra = []): array
    {
        $data = array_merge(['order_id' => $gateway->order_id, 'gross_amount' => (string) $gateway->gross_amount,
            'status_code' => '200', 'transaction_status' => 'settlement', 'payment_type' => 'qris',
            'transaction_id' => 'provider-'.hash('sha256', $gateway->order_id)], $extra);
        $data['signature_key'] = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].'stage2-synthetic-key');
        return $data;
    }

    private function notify(PembayaranGateway $gateway, array $data): PembayaranGateway
    {
        $this->fakeInquiry($data);
        return $gateway->id_membership
            ? app(MembershipPaymentService::class)->processNotification($data)
            : app(MidtransPaymentService::class)->processNotification($data);
    }

    private function fakeInquiry(array $data, int $status = 200): void
    {
        // Each transition needs a new provider response; clear earlier stubs
        // rather than allowing the first matching fake to win for every call.
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['*status' => Http::response($data, $status)]);
    }

    public function test_capture_challenge_never_activates_membership_or_pays_transaction(): void
    {
        foreach ([$this->membershipGateway(), $this->transactionGateway()] as $gateway) {
            $this->notify($gateway, $this->payload($gateway, ['transaction_status' => 'capture', 'fraud_status' => 'challenge', 'payment_type' => 'credit_card']));
            if ($gateway->id_membership) $this->assertSame('pending_payment', $gateway->membership->fresh()->status);
            else $this->assertSame('belum_bayar', $gateway->transaksi->fresh()->status);
            $this->assertNull($gateway->fresh()->id_pembayaran);
        }
    }

    public function test_late_pending_does_not_regress_paid_membership_gateway(): void
    {
        $gateway = $this->membershipGateway();
        $this->notify($gateway, $this->payload($gateway));
        $activated = $gateway->membership->fresh()->activated_at->toDateTimeString();
        $this->notify($gateway, $this->payload($gateway, ['transaction_status' => 'pending', 'status_code' => '201']));
        $this->assertSame('settlement', $gateway->fresh()->status_provider);
        $this->assertSame('active', $gateway->membership->fresh()->status);
        $this->assertSame($activated, $gateway->membership->fresh()->activated_at->toDateTimeString());
    }

    public static function statuses(): iterable
    {
        $cases = [
            ['capture', 'accept', 'credit_card', '200', true],
            ['capture', 'challenge', 'credit_card', '200', false],
            ['capture', 'deny', 'credit_card', '200', false],
            ['capture', '__absent', 'credit_card', '200', false],
            ['capture', 'accept', 'qris', '200', false],
            ['settlement', 'accept', 'credit_card', '200', true],
            ['settlement', '__absent', 'credit_card', '200', false],
            ['settlement', '__absent', 'qris', '200', true],
            ['settlement', '__absent', 'cstore', '200', true],
            ['settlement', 'accept', 'bank_transfer', '200', true],
            ['settlement', 'challenge', 'qris', '200', false],
            ['settlement', 'unknown', 'qris', '200', false],
            ['settlement', '', 'qris', '200', false],
            ['settlement', '__absent', 'qris', '201', false],
            ['pending', '__absent', 'qris', '201', false],
            ['deny', 'deny', 'credit_card', '202', false],
            ['cancel', '__absent', 'qris', '200', false],
            ['expire', '__absent', 'qris', '200', false],
            ['failure', '__absent', 'qris', '202', false],
            ['authorize', 'accept', 'credit_card', '200', false],
        ];
        foreach (['membership', 'transaction'] as $purpose) {
            foreach ($cases as $index => $case) yield $purpose.'-'.$index => [$purpose, ...$case];
        }
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statuses')]
    public function test_provider_status_and_fraud_matrix(string $purpose, string $status, string $fraud, string $method, string $code, bool $paid): void
    {
        $g = $purpose === 'membership' ? $this->membershipGateway() : $this->transactionGateway();
        $data = $this->payload($g, ['transaction_status' => $status, 'fraud_status' => $fraud, 'payment_type' => $method, 'status_code' => $code]);
        if ($fraud === '__absent') unset($data['fraud_status']);
        $this->notify($g, $data);
        if ($g->id_membership) {
            $this->assertSame($paid ? 'active' : 'pending_payment', $g->membership->fresh()->status);
            $this->assertSame($paid, $g->membership->fresh()->activated_at !== null);
        } else {
            $this->assertSame($paid ? 'lunas' : 'belum_bayar', $g->transaksi->fresh()->status);
            $this->assertSame($paid ? 1 : 0, $g->transaksi->pembayarans()->count());
        }
        $this->assertSame($paid, $g->fresh()->processed_at !== null);
    }

    public function test_http_webhook_rejects_invalid_signature_unknown_order_and_malformed_fields(): void
    {
        $g = $this->membershipGateway();
        $data = $this->payload($g);
        $this->fakeInquiry($data);
        $bad = $data; $bad['signature_key'] = 'invalid';
        $this->postJson('/payment/midtrans/notification', $bad)->assertStatus(400);
        $unknown = $this->payload($g, ['order_id' => 'YZA-MBR-999999-UNKNOWN']);
        $this->postJson('/payment/midtrans/notification', $unknown)->assertStatus(400);
        $this->postJson('/payment/midtrans/notification', ['order_id' => []])->assertStatus(400);
        $this->assertSame('pending_payment', $g->membership->fresh()->status);
        $this->assertSame(0, $g->events()->count());
        Http::assertNothingSent();
    }

    public function test_valid_webhook_uses_current_inquiry_instead_of_unsigned_status_or_fraud_fields(): void
    {
        foreach ([$this->membershipGateway(), $this->transactionGateway()] as $g) {
            $incoming = $this->payload($g, ['transaction_status' => 'capture', 'fraud_status' => 'accept', 'payment_type' => 'credit_card']);
            $actual = $incoming; $actual['fraud_status'] = 'challenge';
            $this->fakeInquiry($actual);
            $this->postJson('/payment/midtrans/notification', $incoming)->assertOk();
            $this->assertNull($g->fresh()->processed_at);
            $this->assertSame(2, $g->events()->count());
        }
    }

    public function test_amount_order_currency_initiator_and_target_mismatches_never_pay(): void
    {
        foreach (['amount', 'inquiry_order', 'currency', 'initiator', 'both_targets', 'invoice_total'] as $case) {
            $g = $this->transactionGateway();
            $data = $this->payload($g);
            if ($case === 'amount') $data = $this->payload($g, ['gross_amount' => '99999.00']);
            if ($case === 'inquiry_order') $data['order_id'] = 'YZA-OTHER-ORDER';
            if ($case === 'currency') $data['currency'] = 'USD';
            if ($case === 'initiator') $g->forceFill(['initiated_by' => User::where('id_user', '<>', $g->initiated_by)->value('id_user')])->save();
            if ($case === 'both_targets') $g->forceFill(['id_membership' => $this->member()->pelanggan->membership->id_membership])->save();
            if ($case === 'invoice_total') $g->transaksi->forceFill(['total' => 200000])->save();
            $this->fakeInquiry($data);
            if ($case === 'amount') app(MidtransPaymentService::class)->processNotification($data);
            else app(\App\Services\MidtransGatewayProcessor::class)->inquiry($g);
            $this->assertSame('requires_review', $g->fresh()->status_provider, $case);
            $this->assertSame(0, $g->transaksi->pembayarans()->count(), $case);
            $this->assertSame('belum_bayar', $g->transaksi->fresh()->status, $case);
        }
    }

    public function test_incomplete_or_failed_inquiry_cannot_supply_default_success_fields(): void
    {
        foreach (['order_id', 'gross_amount', 'status_code', 'transaction_status'] as $missing) {
            $g = $this->membershipGateway();
            $data = $this->payload($g); unset($data[$missing]);
            $this->fakeInquiry($data);
            app(MembershipPaymentService::class)->sync($g, $this->member());
            $this->assertSame('requires_review', $g->fresh()->status_provider);
            $this->assertSame('pending_payment', $g->membership->fresh()->status);
        }
        $g = $this->membershipGateway();
        $this->fakeInquiry(['message' => 'unavailable'], 503);
        try { app(MembershipPaymentService::class)->sync($g, $this->member()); $this->fail('Inquiry should fail'); }
        catch (\RuntimeException) { $this->assertSame('pending', $g->fresh()->status_provider); }
        $this->assertSame(0, $g->events()->count());
        $this->postJson('/payment/midtrans/notification', $this->payload($g))->assertStatus(503);
        $this->assertSame('pending_payment', $g->membership->fresh()->status);
    }

    public function test_duplicate_notifications_and_capture_to_settlement_apply_once(): void
    {
        foreach ([$this->membershipGateway(), $this->transactionGateway()] as $g) {
            $capture = $this->payload($g, ['transaction_status' => 'capture', 'payment_type' => 'credit_card', 'fraud_status' => 'accept']);
            $this->notify($g, $capture);
            $processed = $g->fresh()->processed_at->toDateTimeString();
            $events = $g->events()->count();
            $notifications = DB::table('notifikasi')->count(); $points = DB::table('riwayat_poin')->count();
            $this->travel(1)->hours();
            $this->notify($g, $capture);
            $this->assertSame($events, $g->events()->count());
            $this->assertSame($processed, $g->fresh()->processed_at->toDateTimeString());
            $this->notify($g, array_replace($capture, ['transaction_status' => 'settlement']));
            $this->assertSame('settlement', $g->fresh()->status_provider);
            $this->assertSame($processed, $g->fresh()->processed_at->toDateTimeString());
            $this->assertSame($notifications, DB::table('notifikasi')->count());
            $this->assertSame($points, DB::table('riwayat_poin')->count());
            if ($g->id_transaksi) $this->assertSame(1, $g->transaksi->pembayarans()->count());
            else $this->assertSame($processed, $g->membership->fresh()->activated_at->toDateTimeString());
        }
    }

    public function test_challenge_can_recover_but_late_pending_or_failed_notification_cannot_undo_settlement(): void
    {
        $g = $this->membershipGateway();
        $challenge = $this->payload($g, ['transaction_status' => 'capture', 'fraud_status' => 'challenge', 'payment_type' => 'credit_card']);
        $this->notify($g, $challenge);
        $settled = $this->payload($g, ['payment_type' => 'credit_card', 'fraud_status' => 'accept']);
        $this->notify($g, $settled);
        $stale = $this->payload($g, ['transaction_status' => 'deny', 'fraud_status' => 'deny']);
        $this->fakeInquiry($settled);
        app(MembershipPaymentService::class)->processNotification($stale);
        $this->assertSame('settlement', $g->fresh()->status_provider);
        $this->assertSame('active', $g->membership->fresh()->status);
    }

    public function test_post_payment_refund_reversal_is_audited_without_automatic_business_reversal(): void
    {
        foreach (['refund', 'partial_refund', 'chargeback', 'partial_chargeback', 'deny', 'cancel'] as $status) {
          foreach ([$this->membershipGateway(), $this->transactionGateway()] as $g) {
            $this->notify($g, $this->payload($g));
            $this->notify($g, $this->payload($g, ['transaction_status' => $status]));
            $this->assertSame($status, $g->fresh()->status_provider);
            if ($g->id_membership) $this->assertSame('active', $g->membership->fresh()->status);
            else {
                $this->assertSame('lunas', $g->transaksi->fresh()->status);
                $this->assertSame('terverifikasi', $g->transaksi->pembayarans()->firstOrFail()->status);
            }
            $this->assertTrue($g->events()->where('transaction_status', 'requires_review')->exists());
            $this->notify($g, $this->payload($g, ['transaction_status' => 'pending', 'status_code' => '201']));
            $this->assertSame($status, $g->fresh()->status_provider);
          }
        }
    }

    public function test_partial_refund_revisions_are_audited_without_duplicate_receipts_or_events(): void
    {
        $g = $this->transactionGateway();
        $this->notify($g, $this->payload($g));
        $first = $this->payload($g, ['transaction_status' => 'partial_refund', 'refund_amount' => '10000.00',
            'refunds' => [['refund_chargeback_id' => 'S2-REF-1', 'refund_amount' => '10000.00']]]);
        $this->notify($g, $first);
        $count = $g->events()->count();
        $second = $first; $second['refund_amount'] = '20000.00';
        $second['refunds'][] = ['refund_chargeback_id' => 'S2-REF-2', 'refund_amount' => '10000.00'];
        $this->notify($g, $second);
        $this->assertSame($count + 2, $g->events()->count());
        $this->notify($g, $second);
        $this->assertSame($count + 2, $g->events()->count());
        $this->assertSame('partial_refund', $g->fresh()->status_provider);
        $this->assertSame(1, $g->transaksi->pembayarans()->count());
        $this->assertSame('100000.00', (string) $g->transaksi->pembayarans()->firstOrFail()->jumlah);
    }

    public function test_manual_transfer_is_never_verified_by_late_gateway_callback(): void
    {
        $g = $this->transactionGateway();
        $payment = \App\Models\Pembayaran::withoutEvents(fn () => \App\Models\Pembayaran::forceCreate([
            'kode_pembayaran' => 'S2-MAN-'.bin2hex(random_bytes(5)), 'id_transaksi' => $g->id_transaksi,
            'tanggal' => now(), 'arah' => 'masuk', 'metode' => 'transfer', 'jumlah' => 100000, 'status' => 'menunggu',
        ]));
        $this->notify($g, $this->payload($g));
        $this->assertSame('requires_review', $g->fresh()->status_provider);
        $this->assertSame('menunggu', $payment->fresh()->status);
        $this->assertSame(1, $g->transaksi->pembayarans()->count());
        $this->notify($g, $this->payload($g));
        $this->assertNull($g->fresh()->id_pembayaran);
    }

    public function test_finish_browser_claim_and_other_customer_order_id_cannot_activate_membership(): void
    {
        $g = $this->membershipGateway();
        $pending = $this->payload($g, ['transaction_status' => 'pending', 'status_code' => '201']);
        $this->fakeInquiry($pending);
        $this->actingAs($this->member())->get('/pelanggan/membership/finish?order_id='.$g->order_id.'&transaction_status=settlement&status_code=200')->assertRedirect();
        $this->assertSame('pending_payment', $g->membership->fresh()->status);
        $this->get('/pelanggan/membership/finish?order_id=YZA-MBR-OTHER-ORDER')->assertForbidden();
        $other = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->where('id_user', '<>', $this->member()->id_user)->firstOrFail();
        try { app(MembershipPaymentService::class)->sync($g, $other); $this->fail('Customer ownership required'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
    }

    public function test_activation_failure_rolls_back_gateway_and_events_then_retry_is_safe(): void
    {
        $g = $this->membershipGateway();
        $event = 'eloquent.updating: '.Membership::class;
        \Illuminate\Support\Facades\Event::listen($event, function ($membership) { if ($membership->isDirty('status') && $membership->status === 'active') throw new \RuntimeException('Synthetic activation fault'); });
        try { $this->notify($g, $this->payload($g)); $this->fail('Expected injected fault'); }
        catch (\RuntimeException $e) { $this->assertSame('Synthetic activation fault', $e->getMessage()); }
        finally { \Illuminate\Support\Facades\Event::forget($event); }
        $this->assertSame('pending', $g->fresh()->status_provider);
        $this->assertNull($g->fresh()->processed_at);
        $this->assertSame(0, $g->events()->count());
        $this->notify($g, $this->payload($g));
        $this->assertSame('active', $g->membership->fresh()->status);
        $this->assertSame(2, $g->events()->count());
    }

    public function test_duplicate_paid_service_callback_awards_points_and_notifications_only_once(): void
    {
        $g = $this->transactionGateway();
        $g->transaksi->forceFill(['jenis_transaksi' => 'servis'])->save();
        $this->member()->pelanggan->membership->forceFill(['status' => 'active'])->save();
        $service = \App\Models\Servis::firstOrFail()->replicate();
        $service->forceFill(['kode_servis' => 'S2-POINTS-'.bin2hex(random_bytes(5)), 'id_booking_servis' => null,
            'id_transaksi' => null, 'id_pelanggan' => $g->transaksi->id_pelanggan,
            'status' => 'dikerjakan', 'tanggal_selesai' => null])->save();
        $service->forceFill(['hasil_pemeriksaan' => 'Diagnosis QA'])->save();
        $this->passQc($service);
        $service->forceFill(['id_transaksi' => $g->id_transaksi])->save();
        $this->notify($g, $this->payload($g));
        $notificationCount = DB::table('notifikasi')->where('referensi_tipe', 'servis')->where('referensi_id', $service->id_servis)->count();
        $this->assertGreaterThan(0, $notificationCount);
        $this->assertSame('selesai', $service->fresh()->status);
        $this->assertSame(1, DB::table('riwayat_poin')->where('id_transaksi', $g->id_transaksi)->count());
        $this->assertSame(10, (int) DB::table('riwayat_poin')->where('id_transaksi', $g->id_transaksi)->value('perubahan_poin'));
        $this->notify($g, $this->payload($g));
        $this->assertSame(1, $g->transaksi->pembayarans()->count());
        $this->assertSame(1, DB::table('riwayat_poin')->where('id_transaksi', $g->id_transaksi)->count());
        $this->assertSame($notificationCount, DB::table('notifikasi')->where('referensi_tipe', 'servis')->where('referensi_id', $service->id_servis)->count());
    }
}
