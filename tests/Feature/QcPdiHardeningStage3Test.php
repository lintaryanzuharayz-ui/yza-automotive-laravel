<?php

namespace Tests\Feature;

use App\Models\{Mobil, Servis, User};
use App\Services\{MemberVehiclePurchaseService, PaymentService, ServisService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QcPdiHardeningStage3Test extends TestCase
{
    use DatabaseTransactions;
    use \Tests\Support\CompletesQualityChecks;

    private function rejected(callable $operation, string $message = ''): void
    {
        try { $operation(); $this->fail('Operation must be rejected'); }
        catch (\RuntimeException $e) { $this->assertStringContainsString($message, $e->getMessage()); }
    }

    public static function invalidQcCases(): array { return ['incomplete' => ['incomplete'], 'failed' => ['failed'], 'stale' => ['stale'], 'missing item' => ['missing'], 'forged result' => ['forged'], 'wrong inspector' => ['inspector']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidQcCases')]
    public function test_invalid_qc_cannot_create_an_invoice(string $case): void
    {
        $s = $this->workOrder();
        $exec = app(\App\Services\ServiceExecutionService::class);
        if ($case === 'incomplete') $exec->startQualityControl($s->id_servis, $this->actor());
        elseif ($case === 'failed') {
            $qc = $exec->startQualityControl($s->id_servis, $this->actor());
            $exec->updateQualityControl($s->id_servis, [$qc->items->first()->getKey() => ['result' => 'FAIL', 'note' => 'Repair']], null, $this->actor());
        } else {
            $this->passQc($s);
            if ($case === 'stale') $s->forceFill(['hasil_pemeriksaan' => 'Changed diagnosis'])->save();
            if ($case === 'missing') $s->qualityControl->items()->first()->delete();
            if ($case === 'forged') $s->qualityControl->items()->first()->forceFill(['result' => 'FAIL'])->save();
            if ($case === 'inspector') $s->qualityControl->forceFill(['checked_by' => null])->save();
        }
        $count = \App\Models\Transaksi::count();
        $this->rejected(fn () => app(ServisService::class)->finalizeService($s->id_servis, $this->actor()));
        $this->assertNull($s->fresh()->id_transaksi);
        $this->assertSame($count, \App\Models\Transaksi::count());
    }

    public function test_work_change_invalidates_qc_and_preserves_inspection_history(): void
    {
        $s = $this->workOrder(); $this->passQc($s);
        app(ServisService::class)->addDetail($s->id_servis, null, 'Repair', '100', null);
        $this->assertSame('in_progress', $s->fresh()->qualityControl->status);
        $this->assertSame(1, $s->executionEvents()->where('event_key', 'qc_passed')->count());
        $this->assertSame(1, $s->executionEvents()->where('event_key', 'qc_invalidated')->count());
        $this->rejected(fn () => app(ServisService::class)->finalizeService($s->id_servis, $this->actor()));
        $this->passQc($s);
        $tx = app(ServisService::class)->finalizeService($s->id_servis, $this->actor());
        $this->assertSame('100.00', $tx->total);
        $this->rejected(fn () => app(ServisService::class)->finalizeService($s->id_servis, $this->actor()));
        $this->assertSame($tx->id_transaksi, $s->fresh()->id_transaksi);
    }

    public function test_qc_item_from_another_work_order_is_rejected_atomically(): void
    {
        $a = $this->workOrder(); $b = $this->workOrder();
        $exec = app(\App\Services\ServiceExecutionService::class);
        $aq = $exec->startQualityControl($a->id_servis, $this->actor());
        $bq = $exec->startQualityControl($b->id_servis, $this->actor());
        $this->rejected(fn () => $exec->updateQualityControl($a->id_servis, [$bq->items->first()->getKey() => ['result' => 'PASS']], null, $this->actor()));
        $this->assertNull($aq->items->first()->fresh()->result);
        $this->assertNull($bq->items->first()->fresh()->result);
    }

    public function test_valid_na_qc_can_finalize_and_payment_completes_service(): void
    {
        $s = $this->workOrder();
        app(ServisService::class)->addDetail($s->id_servis, null, 'Repair', '100', null);
        $exec = app(\App\Services\ServiceExecutionService::class);
        $qc = $exec->startQualityControl($s->id_servis, $this->actor());
        $exec->updateQualityControl($s->id_servis, $qc->items->mapWithKeys(fn ($i) => [$i->getKey() => ['result' => 'N/A']])->all(), null, $this->actor());
        $tx = app(ServisService::class)->finalizeService($s->id_servis, $this->actor());
        app(PaymentService::class)->create($tx->id_transaksi, $this->actor(), '100', 'tunai', null, null);
        $this->assertSame('selesai', $s->fresh()->status);
    }

    public function test_payment_cannot_complete_a_manually_linked_work_order_without_qc(): void
    {
        $s = $this->workOrder(); $tx = \App\Models\Transaksi::firstOrFail()->replicate();
        $tx->forceFill(['kode_transaksi' => 'S3-'.bin2hex(random_bytes(5)), 'jenis_transaksi' => 'servis', 'status' => 'belum_bayar', 'total' => 100])->save();
        $s->forceFill(['id_transaksi' => $tx->id_transaksi])->save();
        $this->rejected(fn () => app(PaymentService::class)->create($tx->id_transaksi, $this->actor(), '100', 'tunai', null, null));
        $this->assertSame('belum_bayar', $tx->fresh()->status);
        $this->assertSame(0, $tx->pembayarans()->count());
        $this->assertSame('dikerjakan', $s->fresh()->status);
    }

    public static function badPdiCases(): array { return ['absent' => ['absent'], 'incomplete' => ['incomplete'], 'issue' => ['issue'], 'vehicle changed' => ['vehicle'], 'replacement vehicle' => ['replacement'], 'order changed' => ['order'], 'checklist forged' => ['forged']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('badPdiCases')]
    public function test_invalid_pdi_blocks_handover_without_ownership(string $case): void
    {
        $o = $this->paidOrder(); $svc = app(MemberVehiclePurchaseService::class);
        if ($case === 'incomplete') {
            $this->rejected(fn () => $svc->completePdi($o, $this->actor(), ['checklist' => ['nomor_rangka' => 'pass']]));
        } elseif ($case !== 'absent') {
            $pdi = $svc->completePdi($o, $this->actor(), $this->pdiInput($case === 'issue' ? 'issue' : 'pass'));
            if ($case === 'vehicle') $o->mobil->forceFill(['nomor_mesin' => 'S3-CHANGED'])->save();
            if ($case === 'replacement') $o->forceFill(['id_mobil' => Mobil::whereKeyNot($o->id_mobil)->firstOrFail()->id_mobil])->save();
            if ($case === 'order') $o->forceFill(['id_penjualan' => null])->save();
            if ($case === 'forged') { $items = $pdi->checklist; $items['final']['road_test']['result'] = 'issue'; $pdi->forceFill(['checklist' => $items])->save(); }
        }
        $o->forceFill(['status' => 'siap_diambil', 'jadwal_pengambilan' => now()->subHour(), 'status_pengambilan' => 'dijadwalkan'])->save();
        $this->rejected(fn () => $svc->handover($o, $this->actor(), ['nama_penerima' => 'QA']));
        $this->assertSame(0, \App\Models\KendaraanPelanggan::where('id_pesanan', $o->id_pesanan)->count());
        $this->assertNull($o->fresh()->serahTerima);
    }

    public function test_valid_na_pdi_handover_is_idempotent_and_preserves_failed_history(): void
    {
        $o = $this->paidOrder(); $svc = app(MemberVehiclePurchaseService::class);
        $svc->completePdi($o, $this->actor(), $this->pdiInput('issue'));
        $pdi = $svc->completePdi($o, $this->actor(), $this->pdiInput('na'));
        $this->assertSame('perlu_perbaikan', $pdi->checklist['_history'][0]['inspection']['status']);
        $svc->schedule($o, now()->addHour()->toDateTimeString());
        \Illuminate\Support\Carbon::setTestNow(now()->addHours(2));
        $handover = $svc->handover($o, $this->actor(), ['nama_penerima' => 'QA']);
        $count = \Illuminate\Support\Facades\DB::table('notifikasi')->count();
        $again = $svc->handover($o, $this->actor(), ['nama_penerima' => 'QA']);
        $this->assertSame($handover->getKey(), $again->getKey());
        $this->assertSame(1, \App\Models\KendaraanPelanggan::where('id_pesanan', $o->id_pesanan)->count());
        $this->assertSame($count, \Illuminate\Support\Facades\DB::table('notifikasi')->count());
    }

    public function test_payment_cannot_be_bypassed_with_a_passed_pdi(): void
    {
        $o = $this->paidOrder(); $svc = app(MemberVehiclePurchaseService::class);
        $svc->completePdi($o, $this->actor(), $this->pdiInput());
        $o->transaksi->forceFill(['status' => 'belum_bayar'])->save();
        $this->rejected(fn () => $svc->schedule($o, now()->addDay()->toDateTimeString()));
        $this->rejected(fn () => $svc->handover($o, $this->actor(), ['nama_penerima' => 'QA']));
        $this->assertNull($o->fresh()->serahTerima);
    }

    public function test_qc_and_pdi_roles_are_enforced_on_direct_service_calls_and_routes(): void
    {
        \Illuminate\Support\Facades\Artisan::call('yza:seed-local-staff');
        $s = $this->workOrder(); $o = $this->paidOrder();
        foreach (['owner', 'mechanic', 'service_advisor'] as $role) {
            $user = User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
            $this->actingAs($user);
            if ($role !== 'service_advisor') $this->post('/admin/servis/'.$s->id_servis.'/qc/start')->assertForbidden();
            $this->post('/admin/pesanan-mobil/'.$o->id_pesanan.'/pdi', $this->pdiInput())->assertForbidden();
            try { app(MemberVehiclePurchaseService::class)->completePdi($o, $user->petugas->id_petugas, $this->pdiInput()); $this->fail('Unauthorized inspector'); }
            catch (\Illuminate\Auth\Access\AuthorizationException $e) { $this->assertTrue(true); }
        }
        $qc = User::whereHas('role', fn ($q) => $q->where('nama_role', 'quality_control'))->firstOrFail();
        $this->actingAs($qc)->get('/admin/pdi')->assertOk();
        $this->post('/admin/pesanan-mobil/'.$o->id_pesanan.'/pdi', $this->pdiInput())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($qc->petugas->id_petugas, $o->fresh()->pdi->id_petugas);
        $this->post('/admin/pesanan-mobil/'.$o->id_pesanan.'/handover', ['nama_penerima' => 'QA'])->assertForbidden();
    }

    private function actor(): int { return (int) User::whereHas('role', fn ($q) => $q->where('nama_role', 'petugas'))->firstOrFail()->petugas->id_petugas; }

    public function test_direct_post_without_qc_cannot_create_invoice(): void
    {
        $s = $this->workOrder();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        $this->actingAs($admin)->post('/admin/servis/'.$s->id_servis.'/finalize')->assertRedirect()->assertSessionHasErrors();
        $this->assertNull($s->fresh()->id_transaksi);
    }

    public function test_handover_database_failure_rolls_back_sale_inventory_ownership_and_notifications(): void
    {
        $o = $this->paidOrder(); $svc = app(MemberVehiclePurchaseService::class);
        $svc->completePdi($o, $this->actor(), $this->pdiInput());
        $svc->schedule($o, now()->addHour()->toDateTimeString());
        $o->forceFill(['jadwal_pengambilan' => now()->subHour()])->save();
        $inventory = \Illuminate\Support\Facades\DB::table('vehicle_inventories')->get()->toJson();
        $notifications = \Illuminate\Support\Facades\DB::table('notifikasi')->count();
        $event = 'eloquent.creating: '.\App\Models\KendaraanPelanggan::class;
        \Illuminate\Support\Facades\Event::listen($event, fn () => throw new \RuntimeException('Synthetic handover failure'));
        try { $this->rejected(fn () => $svc->handover($o, $this->actor(), ['nama_penerima' => 'QA']), 'Synthetic handover failure'); }
        finally { \Illuminate\Support\Facades\Event::forget($event); }
        $this->assertSame('siap_diambil', $o->fresh()->status);
        $this->assertSame('proses', $o->penjualan->fresh()->status);
        $this->assertNull($o->fresh()->serahTerima);
        $this->assertSame($notifications, \Illuminate\Support\Facades\DB::table('notifikasi')->count());
        $this->assertSame($inventory, \Illuminate\Support\Facades\DB::table('vehicle_inventories')->get()->toJson());
    }

    public function test_stale_pdi_can_be_reopened_without_erasing_previous_result(): void
    {
        $o = $this->paidOrder(); $svc = app(MemberVehiclePurchaseService::class);
        $svc->completePdi($o, $this->actor(), $this->pdiInput());
        $o->mobil->forceFill(['nomor_mesin' => 'CHANGED'])->save();
        $this->rejected(fn () => $svc->schedule($o, now()->addHour()->toDateTimeString()));
        $pdi = $svc->startPdi($o, $this->actor());
        $this->assertSame('sedang_diperiksa', $pdi->status);
        $this->assertSame('lulus', $pdi->checklist['_history'][0]['inspection']['status']);
        $svc->completePdi($o, $this->actor(), $this->pdiInput());
        $svc->schedule($o, now()->addHour()->toDateTimeString());
        $this->assertSame('siap_diambil', $o->fresh()->status);
    }

    private function workOrder(): Servis
    {
        $s = Servis::firstOrFail()->replicate();
        $s->forceFill(['kode_servis' => 'S3-'.bin2hex(random_bytes(5)), 'status' => 'dikerjakan',
            'id_transaksi' => null, 'id_booking_servis' => null, 'id_petugas' => $this->actor(),
            'hasil_pemeriksaan' => 'Diagnosis stage 3', 'tanggal_selesai' => null])->save();
        return $s;
    }

    private function paidOrder()
    {
        $customer = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->pelanggan;
        $car = Mobil::where('model', 'GR Supra')->where('status', 'tersedia')->firstOrFail();
        $svc = app(MemberVehiclePurchaseService::class);
        $order = $svc->approve($svc->create($customer, $car, ['nama_pembeli' => 'S3 QA',
            'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA']), $this->actor());
        app(PaymentService::class)->create($order->id_transaksi, $this->actor(), (string) $order->total_tagihan, 'tunai', null, null);
        return $order->fresh();
    }

    public function test_finalization_without_quality_control_is_rejected(): void
    {
        $s = $this->workOrder();
        $this->expectException(\RuntimeException::class);
        app(ServisService::class)->finalizeService($s->id_servis, $this->actor());
    }

    public function test_legacy_boolean_pdi_cannot_pass_without_detailed_checklist(): void
    {
        $order = $this->paidOrder();
        $this->expectException(\RuntimeException::class);
        app(MemberVehiclePurchaseService::class)->savePdi($order, $this->actor(),
            array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], true));
    }
}
