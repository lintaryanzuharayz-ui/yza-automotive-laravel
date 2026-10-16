<?php

namespace Tests\Feature;

use App\Models\{Pembayaran, Transaksi, User};
use App\Services\FinancialAdjustmentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

class FinancialAdjustmentStage7Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    private function payment(float $amount = 100000): Pembayaran
    {
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $actor = User::where('username', 'finance506')->firstOrFail()->petugas;
        $transaction = new Transaksi();
        $transaction->forceFill(['kode_transaksi' => 'QA-'.Str::upper(Str::random(10)), 'id_pelanggan' => $member->pelanggan->id_pelanggan, 'id_petugas' => $actor->id_petugas, 'jenis_transaksi' => 'penjualan_mobil', 'tanggal' => now(), 'subtotal' => $amount, 'diskon' => 0, 'total' => $amount, 'status' => 'lunas'])->save();
        $payment = new Pembayaran();
        $payment->forceFill(['kode_pembayaran' => 'PAY-'.Str::upper(Str::random(10)), 'id_transaksi' => $transaction->id_transaksi, 'tanggal' => now(), 'arah' => 'masuk', 'metode' => 'tunai', 'jumlah' => $amount, 'status' => 'terverifikasi', 'verified_at' => now()])->save();
        return $payment;
    }

    public function test_full_refund_requires_separation_of_duties_and_evidence(): void
    {
        $payment = $this->payment();
        $finance = User::where('username', 'finance506')->firstOrFail()->petugas->id_petugas;
        $superAdmin = User::where('username', 'superadmin506')->firstOrFail()->petugas->id_petugas;
        $confirmer = User::where('username', 'advisor506')->firstOrFail()->petugas->id_petugas;
        $service = app(FinancialAdjustmentService::class);
        $item = $service->request($payment->id_pembayaran, 100000, 'Customer cancellation review', $finance);
        $service->transition($item, 'under_review', $finance);
        try { $service->transition($item, 'approved', $finance); $this->fail('Requester must not approve their own adjustment.'); } catch (\RuntimeException $e) { $this->assertStringContainsString('Separation of duties', $e->getMessage()); }
        $service->transition($item, 'approved', $superAdmin);
        $service->transition($item, 'processing', $superAdmin);
        $confirmed = $service->transition($item, 'confirmed', $confirmer, 'Dana dikembalikan', 'BANK-REF-001');
        $this->assertSame('confirmed', $confirmed->status);
    }

    public function test_partial_refunds_cannot_exceed_original_payment_and_pending_is_not_confirmed(): void
    {
        $payment = $this->payment();
        $service = app(FinancialAdjustmentService::class);
        $finance = User::where('username', 'finance506')->firstOrFail()->petugas->id_petugas;
        $first = $service->request($payment->id_pembayaran, 60000, 'Partial refund one', $finance);
        $second = $service->request($payment->id_pembayaran, 40000, 'Partial refund two', $finance);
        $this->assertSame('requested', $first->status);
        $this->assertSame('requested', $second->status);
        $this->assertSame(0.0, $service->confirmedForPayment($payment->id_pembayaran));
        $this->expectException(\RuntimeException::class);
        $service->request($payment->id_pembayaran, 1, 'Over refund', $finance);
    }

    public function test_invalid_payment_and_owner_mutation_are_blocked(): void
    {
        $payment = $this->payment();
        $finance = User::where('username', 'finance506')->firstOrFail();
        $owner = User::where('username', 'owner506')->firstOrFail();
        $this->actingAs($owner)->post(route('admin.financial-adjustments.store'), ['id_pembayaran' => $payment->id_pembayaran, 'amount' => 1, 'adjustment_type' => 'refund', 'reason' => 'invalid'])->assertForbidden();
        $pending = $payment->fresh();
        $pending->forceFill(['status' => 'menunggu'])->save();
        $this->expectException(\RuntimeException::class);
        app(FinancialAdjustmentService::class)->request($payment->id_pembayaran, 1, 'Not verified', $finance->petugas->id_petugas);
    }

    public function test_finance_can_view_adjustment_request_form_for_verified_offline_payment(): void
    {
        $payment = $this->payment(65000);
        $finance = User::where('username', 'finance506')->firstOrFail();

        $this->actingAs($finance)
            ->get(route('admin.financial-adjustments.index'))
            ->assertOk()
            ->assertSee('Ajukan Refund / Reversal Offline')
            ->assertSee($payment->kode_pembayaran);
    }
}
