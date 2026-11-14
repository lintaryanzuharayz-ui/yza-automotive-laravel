<?php

namespace Tests\Feature;

use App\Models\{Transaksi, User};
use App\Services\{BookingCuciService, PenjualanMobilService, PenjualanSparepartService, ServisService, TradeInService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PromoTransactionIntegrityStage4Test extends TestCase
{
    use DatabaseTransactions;
    use \Tests\Support\PromoIntegrityFixtures;

    private function snapshot(): string
    {
        $tables = ['transaksi','servis','booking_cuci','sparepart','riwayat_stok','service_execution_events',
            'service_approval_requests','service_approval_request_items','penjualan_mobil','mobil','vehicle_inventories',
            'penjualan_sparepart','trade_in','pengajuan_mobil','promo_redemptions','pembayaran','notifikasi'];
        return hash('sha256', json_encode(collect($tables)->mapWithKeys(fn ($t) => [$t => DB::table($t)->get()->all()])->all()));
    }

    public static function wrappers(): array { return ['service' => ['service'], 'wash' => ['wash'], 'car sale' => ['car'], 'parts sale' => ['parts'], 'trade-in' => ['trade']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrappers')]
    public function test_valid_promo_keeps_each_domain_payable_equal_to_invoice(string $type): void
    {
        $actor = $this->stage4Actor(); $p = $this->stage4Promo();
        if ($type === 'service') { $s = $this->stage4Work(); $tx = app(ServisService::class)->finalizeWithPromo($s->getKey(), $actor, $p->getKey()); $net = $s->fresh()->total_biaya; }
        elseif ($type === 'wash') { $s = $this->stage4Wash(); $tx = app(BookingCuciService::class)->finalizeWithPromo($s->getKey(), $actor, $p->getKey()); $net = $tx->total; $this->assertSame('100000.00', $s->fresh()->harga); }
        elseif ($type === 'car') { $s = app(PenjualanMobilService::class)->createWithPromo($this->stage4SaleInput($this->stage4Car()), $actor, $p->getKey()); $tx = $s->transaksi; $net = $s->total_akhir; }
        elseif ($type === 'parts') {
            $svc = app(PenjualanSparepartService::class); $s = $svc->createDraft(User::whereHas('role', fn ($q) => $q->where('nama_role','pelanggan'))->firstOrFail()->pelanggan->id_pelanggan, $actor);
            $svc->addDetail($s->getKey(), 1, 1); $tx = $svc->finalizeWithPromo($s->getKey(), $actor, $p->getKey()); $net = $s->fresh()->total;
        } else { [$a,$i,$car] = $this->stage4Trade(); $s = app(TradeInService::class)->createWithPromo($a->id_pelanggan, $a->getKey(), $i->getKey(), $car->getKey(), $actor, '0', null, $p->getKey()); $tx = $s->transaksi; $net = $s->selisih_bayar; }
        $this->assertSame($tx->total, $net);
        $this->assertGreaterThan(0, (int) $tx->diskon);
        $this->assertSame($p->getKey(), (int) $tx->id_promo);
        $this->assertSame($tx->total, (string) DB::selectOne('SELECT CAST(? - ? AS DECIMAL(15,2)) AS amount', [$tx->subtotal, $tx->diskon])->amount);
        $this->assertSame(0, $tx->pembayarans()->count());
        $this->assertSame(1, $tx->promoRedemption()->count());
        $this->assertSame('reserved', $tx->promoRedemption->status);
    }

    public function test_reusing_a_confirmed_vehicle_booking_cannot_create_a_second_sale(): void
    {
        $car = $this->stage4Car(); $input = $this->stage4SaleInput($car); $p = $this->stage4Promo();
        $booking = \App\Models\BookingMobil::forceCreate(['kode_booking' => 'S4-'.bin2hex(random_bytes(5)), 'id_mobil' => $car->getKey(),
            'id_pelanggan' => $input['id_pelanggan'], 'tanggal_booking' => now(), 'status' => 'dikonfirmasi']);
        $car->forceFill(['status' => 'dibooking'])->save(); $input['id_booking'] = $booking->getKey();
        app(PenjualanMobilService::class)->createWithPromo($input, $this->stage4Actor(), $p->getKey());
        $before = $this->snapshot();
        $this->denied(fn () => app(PenjualanMobilService::class)->createWithPromo($input, $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
        $this->assertSame(1, $car->penjualan()->count());
    }

    public function test_expiry_boundary_is_inclusive_and_revalidated_inside_the_transaction(): void
    {
        $p = $this->stage4Promo(['selesai_at' => now()]);
        $a = app(BookingCuciService::class)->finalizeWithPromo($this->stage4Wash()->getKey(), $this->stage4Actor(), $p->getKey());
        $this->assertSame($p->getKey(), (int) $a->id_promo);
        \Illuminate\Support\Carbon::setTestNow(now()->addSecond());
        $b = $this->stage4Wash(); $before = $this->snapshot();
        $this->denied(fn () => app(BookingCuciService::class)->finalizeWithPromo($b->getKey(), $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    private function denied(callable $operation): void
    {
        try { $operation(); $this->fail('Expected business rejection'); }
        catch (\RuntimeException $e) { $this->assertNotEmpty($e->getMessage()); }
    }

    public function test_invoice_reservation_blocks_quota_and_per_customer_limit_before_payment(): void
    {
        foreach ([['quota' => 1], ['usage_per_member' => 1]] as $limit) {
            $p = $this->stage4Promo($limit); $a = $this->stage4Wash(); $b = $this->stage4Wash();
            $tx = app(BookingCuciService::class)->finalizeWithPromo($a->getKey(), $this->stage4Actor(), $p->getKey());
            $this->assertSame('reserved', $tx->promoRedemption->status);
            $before = $this->snapshot();
            $this->denied(fn () => app(BookingCuciService::class)->finalizeWithPromo($b->getKey(), $this->stage4Actor(), $p->getKey()));
            $this->assertSame($before, $this->snapshot());
            $this->assertNull($b->fresh()->id_transaksi);
        }
    }

    public function test_reservation_has_no_automatic_expiry_and_settles_its_own_slot(): void
    {
        $p = $this->stage4Promo(['quota' => 1, 'usage_per_member' => 1]);
        $tx = app(BookingCuciService::class)->finalizeWithPromo($this->stage4Wash()->getKey(), $this->stage4Actor(), $p->getKey());
        $id = $tx->promoRedemption->getKey();
        \Illuminate\Support\Carbon::setTestNow(now()->addYear());
        $this->assertSame('reserved', $tx->promoRedemption->fresh()->status);
        $p->forceFill(['status' => 'nonaktif', 'quota' => 0, 'usage_per_member' => 0])->save();
        app(\App\Services\PaymentService::class)->create($tx->getKey(), $this->stage4Actor(), $tx->total, 'tunai', null, null);
        $entry = $tx->promoRedemption->fresh();
        $this->assertSame($id, $entry->getKey()); $this->assertSame('completed', $entry->status);
        $this->assertSame(1, $tx->promoRedemption()->count());
    }

    public function test_only_transaction_cancellation_releases_the_slot_idempotently(): void
    {
        $p = $this->stage4Promo(['quota' => 1]);
        [$a,$i,$car] = $this->stage4Trade();
        $trade = app(TradeInService::class)->createWithPromo($a->id_pelanggan, $a->getKey(), $i->getKey(), $car->getKey(), $this->stage4Actor(), '0', null, $p->getKey());
        $tx = $trade->transaksi; $entry = $tx->promoRedemption; $engine = app(\App\Services\PromoEngineService::class);
        $this->denied(fn () => $engine->releaseReservation($tx));
        $this->assertSame('reserved', $entry->fresh()->status);
        app(TradeInService::class)->cancel($trade->getKey());
        $this->assertSame('dibatalkan', $tx->fresh()->status);
        $this->assertSame('released', $entry->fresh()->status);
        $this->assertSame($entry->getKey(), $engine->releaseReservation($tx)->getKey());
        $new = app(BookingCuciService::class)->finalizeWithPromo($this->stage4Wash()->getKey(), $this->stage4Actor(), $p->getKey());
        $this->assertSame('reserved', $new->promoRedemption->status);
    }

    public function test_rejected_receipt_without_transaction_cancellation_keeps_reservation(): void
    {
        $p = $this->stage4Promo(['quota' => 1]);
        $tx = app(BookingCuciService::class)->finalizeWithPromo($this->stage4Wash()->getKey(), $this->stage4Actor(), $p->getKey());
        $payments = app(\App\Services\PaymentService::class);
        $receipt = $payments->create($tx->getKey(), $this->stage4Actor(), $tx->total, 'transfer', null, null);
        $payments->reject($receipt->getKey(), $this->stage4Actor());
        $this->assertSame('belum_bayar', $tx->fresh()->status);
        $this->assertSame('reserved', $tx->promoRedemption->status);
    }

    public function test_failure_to_release_rolls_back_the_cancellation_and_unit_release(): void
    {
        $p = $this->stage4Promo(['quota' => 1]); [$a,$i,$car] = $this->stage4Trade();
        $trade = app(TradeInService::class)->createWithPromo($a->id_pelanggan, $a->getKey(), $i->getKey(), $car->getKey(), $this->stage4Actor(), '0', null, $p->getKey());
        $before = $this->snapshot();
        $event = 'eloquent.updating: '.\App\Models\PromoRedemption::class;
        \Illuminate\Support\Facades\Event::listen($event, function ($r) { if ($r->status === 'released') throw new \RuntimeException('Synthetic release fault'); });
        try { $this->denied(fn () => app(TradeInService::class)->cancel($trade->getKey())); }
        finally { \Illuminate\Support\Facades\Event::forget($event); }
        $this->assertSame($before, $this->snapshot());
    }

    public function test_member_payment_rejection_releases_reservation_when_it_cancels_transaction(): void
    {
        $purchase = app(\App\Services\MemberVehiclePurchaseService::class);
        $customer = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->pelanggan;
        $order = $purchase->approve($purchase->create($customer, $this->stage4Car(), ['nama_pembeli' => 'Reservation QA']), $this->stage4Actor());
        // Existing generic engine API; a zero nominal benefit keeps the member
        // order's existing gross-price snapshot unchanged in this hook fixture.
        $promo = $this->stage4Promo(['quota' => 1, 'jenis_benefit' => 'nominal', 'nilai_benefit' => 0]);
        app(\App\Services\PromoEngineService::class)->applyToTransaction($order->transaksi, $promo->getKey(), 'penjualan_mobil');
        $entry = $order->transaksi->promoRedemption()->firstOrFail();
        $this->assertSame('reserved', $entry->status);
        $payments = app(\App\Services\PaymentService::class);
        $receipt = $payments->create($order->id_transaksi, $this->stage4Actor(), $order->total_tagihan, 'transfer', null, null);
        $payments->reject($receipt->getKey(), $this->stage4Actor());
        $this->assertSame('dibatalkan', $order->transaksi->fresh()->status);
        $this->assertSame('released', $entry->fresh()->status);
    }

    public function test_completed_usage_is_not_automatically_reversed_by_release(): void
    {
        $p = $this->stage4Promo(['quota' => 1]);
        $tx = app(BookingCuciService::class)->finalizeWithPromo($this->stage4Wash()->getKey(), $this->stage4Actor(), $p->getKey());
        app(\App\Services\PaymentService::class)->create($tx->getKey(), $this->stage4Actor(), $tx->total, 'tunai', null, null);
        $tx->forceFill(['status' => 'dibatalkan'])->save(); // Test-only cancellation fixture, no refund workflow.
        $entry = app(\App\Services\PromoEngineService::class)->releaseReservation($tx);
        $this->assertSame('completed', $entry->status);
        $this->assertSame(1, $tx->pembayarans()->count());
    }

    public function test_member_and_public_vehicle_promotions_are_exclusive_alternatives(): void
    {
        $car = $this->stage4Car(); $input = $this->stage4SaleInput($car);
        \App\Models\Membership::where('id_pelanggan', $input['id_pelanggan'])->update(['status' => 'active']);
        $member = $this->stage4Promo(['member_only' => true]); $public = $this->stage4Promo(['member_only' => false, 'nilai_benefit' => 20]);
        $sale = app(PenjualanMobilService::class)->createWithPromo($input, $this->stage4Actor(), $member->getKey());
        $before = $this->snapshot();
        $this->denied(fn () => app(\App\Services\PromoEngineService::class)->applyToTransaction($sale->transaksi, $public->getKey(), 'penjualan_mobil'));
        $this->assertSame($before, $this->snapshot());
        $this->assertSame('90000000.00', $sale->fresh()->total_akhir);
        $this->assertSame($member->getKey(), (int) $sale->transaksi->fresh()->id_promo);
    }

    public static function invalidPromos(): array
    {
        return ['expired' => [['selesai_at' => '2026-10-18 00:00:00']], 'inactive' => [['status' => 'nonaktif']],
            'future' => [['mulai_at' => '2026-10-20 00:00:00']], 'scope' => [['berlaku_untuk' => 'penjualan_mobil']],
            'minimum' => [['minimum_transaksi' => '999999999']], 'quota' => [['quota' => 0]],
            'per customer' => [['usage_per_member' => 0]]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidPromos')]
    public function test_invalid_promotion_rolls_back_all_service_effects(array $changes): void
    {
        $s = $this->stage4Work(); $p = $this->stage4Promo($changes); $before = $this->snapshot();
        $this->denied(fn () => app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_nonmember_cannot_apply_member_only_promotion(): void
    {
        $s = $this->stage4Work(); $p = $this->stage4Promo(['member_only' => true]);
        $s->pelanggan->membership?->forceFill(['status' => 'pending_payment'])->save();
        $before = $this->snapshot();
        $this->denied(fn () => app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_valid_service_promotion_has_one_invoice_stock_consumption_and_immutable_price(): void
    {
        $s = $this->stage4Work(); $p = $this->stage4Promo(['quota' => 1]);
        $stock = (int) \App\Models\Sparepart::findOrFail(1)->stok;
        $tx = app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey());
        $this->assertSame('135000.00', $tx->subtotal); $this->assertSame('13500.00', $tx->diskon); $this->assertSame('121500.00', $tx->total);
        $this->assertSame($tx->total, $s->fresh()->total_biaya);
        $this->assertSame($stock - 1, (int) \App\Models\Sparepart::findOrFail(1)->stok);
        $this->assertSame(0, $tx->pembayarans()->count());
        $p->forceFill(['nilai_benefit' => 99, 'status' => 'nonaktif'])->save();
        app(\App\Services\PaymentService::class)->create($tx->getKey(), $this->stage4Actor(), '121500', 'tunai', null, null);
        $ledger = $tx->promoRedemption()->firstOrFail();
        $this->assertSame('13500.00', $ledger->discount); $this->assertSame('121500.00', $ledger->total_final);
        $this->assertSame('selesai', $s->fresh()->status);
        $before = $this->snapshot();
        $this->denied(fn () => app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
        app(\App\Services\PromoEngineService::class)->finalizeRedemption($tx);
        $this->assertSame(1, $tx->promoRedemption()->count());
    }

    public function test_retry_after_promotion_failure_succeeds_without_double_stock_or_invoice(): void
    {
        $s = $this->stage4Work(); $p = $this->stage4Promo(['status' => 'nonaktif']);
        $this->denied(fn () => app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey()));
        $this->assertNull($s->fresh()->id_transaksi);
        $p->forceFill(['status' => 'aktif'])->save();
        $before = Transaksi::count();
        $tx = app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey());
        $this->assertSame($before + 1, Transaksi::count());
        $this->assertSame($tx->getKey(), $s->fresh()->id_transaksi);
        $this->assertSame(1, DB::table('riwayat_stok')->where('referensi_tipe','servis')->where('referensi_id',$s->getKey())->count());
    }

    public function test_exception_after_promo_update_rolls_back_nested_transactions(): void
    {
        $s = $this->stage4Work(); $p = $this->stage4Promo(); $before = $this->snapshot();
        $this->mock(\App\Services\PromoEngineService::class)->shouldReceive('applyToTransaction')->once()->andReturnUsing(function ($tx, $promo, $scope) {
            (new \App\Services\PromoEngineService())->applyToTransaction($tx, $promo, $scope);
            throw new \RuntimeException('Synthetic fault after price update');
        });
        $this->denied(fn () => app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_no_promotion_and_failed_qc_keep_existing_rules(): void
    {
        $s = $this->stage4Work(); $tx = app(ServisService::class)->finalizeWithPromo($s->getKey(), $this->stage4Actor());
        $this->assertNull($tx->id_promo); $this->assertSame($tx->subtotal, $tx->total);
        $other = $this->stage4Work(); $other->qualityControl->forceFill(['status' => 'failed'])->save();
        $before = $this->snapshot();
        $this->denied(fn () => app(ServisService::class)->finalizeWithPromo($other->getKey(), $this->stage4Actor(), $this->stage4Promo()->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_double_promo_and_wrong_scope_cannot_mutate_existing_invoice(): void
    {
        $b = $this->stage4Wash(); $p = $this->stage4Promo();
        $tx = app(BookingCuciService::class)->finalizeWithPromo($b->getKey(), $this->stage4Actor(), $p->getKey());
        $before = $this->snapshot();
        $this->denied(fn () => app(\App\Services\PromoEngineService::class)->applyToTransaction($tx, $p->getKey(), 'cuci_mobil'));
        $this->assertSame($before, $this->snapshot());
        $plain = app(BookingCuciService::class)->finalizeWithPromo($this->stage4Wash()->getKey(), $this->stage4Actor());
        $this->denied(fn () => app(\App\Services\PromoEngineService::class)->applyToTransaction($plain, $p->getKey(), 'servis'));
        $this->assertNull($plain->fresh()->id_promo);
    }

    public function test_car_discount_and_invoice_price_are_consistent_without_stacking(): void
    {
        $p = $this->stage4Promo(); $input = $this->stage4SaleInput($this->stage4Car());
        $sale = app(PenjualanMobilService::class)->createWithPromo($input + ['total' => '1', 'harga_mobil' => '1'], $this->stage4Actor(), $p->getKey());
        $this->assertSame('100000000.00', $sale->harga_mobil); $this->assertSame('10000000.00', $sale->diskon);
        $this->assertSame('90000000.00', $sale->total_akhir); $this->assertSame($sale->total_akhir, $sale->transaksi->total);
        $input = $this->stage4SaleInput($this->stage4Car()); $input['diskon'] = '5000000';
        $before = $this->snapshot();
        $this->denied(fn () => app(PenjualanMobilService::class)->createWithPromo($input, $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_dp_exceeding_discounted_car_total_rolls_back_reservation(): void
    {
        $input = $this->stage4SaleInput($this->stage4Car()); $input['dp'] = '95000000'; $p = $this->stage4Promo();
        $before = $this->snapshot();
        $this->denied(fn () => app(PenjualanMobilService::class)->createWithPromo($input, $this->stage4Actor(), $p->getKey()));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_cent_precision_cap_and_percentage_rounding_match_invoice_arithmetic(): void
    {
        $b = $this->stage4Wash(); $b->forceFill(['harga' => '999999.49'])->save();
        $p = $this->stage4Promo(['nilai_benefit' => '17.00']);
        $tx = app(BookingCuciService::class)->finalizeWithPromo($b->getKey(), $this->stage4Actor(), $p->getKey());
        $this->assertSame('169999.00', $tx->diskon); $this->assertSame('830000.49', $tx->total);
        $this->assertSame($tx->total, (string) DB::selectOne('SELECT CAST(? - ? AS DECIMAL(15,2)) AS amount', [$tx->subtotal, $tx->diskon])->amount);
        $p = $this->stage4Promo(['maksimum_benefit' => '1234']);
        $r = app(\App\Services\PromoEngineService::class)->calculate($p, '100000', 'servis', $tx->pelanggan);
        $this->assertSame(1234, $r['discount']);
    }

    public function test_direct_post_uses_server_price_and_owner_remains_read_only(): void
    {
        $s = $this->stage4Work(); $p = $this->stage4Promo();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role','admin'))->firstOrFail();
        $this->actingAs($admin)->post('/admin/servis/'.$s->getKey().'/finalize', ['promo_id' => $p->getKey(), 'subtotal' => 1, 'diskon' => 134999, 'total' => 1])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('121500.00', $s->fresh()->transaksi->total);
        \Illuminate\Support\Facades\Artisan::call('yza:seed-local-staff');
        $owner = User::whereHas('role', fn ($q) => $q->where('nama_role','owner'))->firstOrFail();
        $before = $this->snapshot();
        $this->actingAs($owner)->post('/admin/servis/'.$s->getKey().'/finalize', ['promo_id' => $p->getKey()])->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_payment_attempt_freezes_invoice_price(): void
    {
        $b = $this->stage4Wash(); $tx = app(BookingCuciService::class)->finalize($b->getKey(), $this->stage4Actor());
        app(\App\Services\PaymentService::class)->create($tx->getKey(), $this->stage4Actor(), '1000', 'transfer', null, null);
        $p = $this->stage4Promo(); $before = $this->snapshot();
        $this->denied(fn () => app(\App\Services\PromoEngineService::class)->applyToTransaction($tx, $p->getKey(), 'cuci_mobil'));
        $this->assertSame($before, $this->snapshot());
    }

    public function test_redeem_cannot_recalculate_or_claim_an_unrelated_unpaid_invoice(): void
    {
        $b = $this->stage4Wash(); $tx = app(BookingCuciService::class)->finalize($b->getKey(), $this->stage4Actor());
        $p = $this->stage4Promo(); $before = $this->snapshot();
        $this->denied(fn () => app(\App\Services\PromoEngineService::class)->redeem($p, $tx, $tx->pelanggan, 'cuci_mobil'));
        $this->assertSame($before, $this->snapshot());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrappers')]
    public function test_missing_promo_rolls_back_the_entire_domain_operation(string $type): void
    {
        $actor = $this->stage4Actor();
        $operation = match ($type) {
            'service' => (function () use ($actor) { $s = $this->stage4Work(); return fn () => app(ServisService::class)->finalizeWithPromo($s->getKey(), $actor, 999999999); })(),
            'wash' => (function () use ($actor) { $b = $this->stage4Wash(); return fn () => app(BookingCuciService::class)->finalizeWithPromo($b->getKey(), $actor, 999999999); })(),
            'car' => (function () use ($actor) { $data = $this->stage4SaleInput($this->stage4Car()); return fn () => app(PenjualanMobilService::class)->createWithPromo($data, $actor, 999999999); })(),
            'parts' => (function () use ($actor) { $svc = app(PenjualanSparepartService::class); $sale = $svc->createDraft(User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->pelanggan->id_pelanggan, $actor); $svc->addDetail($sale->getKey(), 1, 1); return fn () => $svc->finalizeWithPromo($sale->getKey(), $actor, 999999999); })(),
            'trade' => (function () use ($actor) { [$p,$i,$car] = $this->stage4Trade(); return fn () => app(TradeInService::class)->createWithPromo($p->id_pelanggan, $p->getKey(), $i->getKey(), $car->getKey(), $actor, '0', null, 999999999); })(),
        };
        $before = $this->snapshot();
        try { $operation(); $this->fail('Missing promo must fail'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertSame(\App\Models\PromoMember::class, $e->getModel()); }
        $this->assertSame($before, $this->snapshot(), 'Promo failure must leave no partial invoice, stock, reservation, history or notification.');
    }
}
