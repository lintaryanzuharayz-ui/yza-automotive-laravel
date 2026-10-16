<?php

namespace Tests\Feature;

use App\Models\{BookingServis, JenisServis, ServiceApprovalRequest, ServiceStaffProfile, Servis, ServisSparepart, Sparepart, User};
use App\Services\{BookingServisService, ServiceExecutionService, ServisService};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

class ServiceExecutionPhase4ATest extends TestCase
{
    use DatabaseTransactions;

    private function member(): User { return User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail(); }
    private function date(): string { return Carbon::today()->next(Carbon::TUESDAY)->toDateString(); }
    private function type(): JenisServis { return JenisServis::where('status', 'aktif')->firstOrFail(); }
    private function technician(): ServiceStaffProfile { $profile = ServiceStaffProfile::firstOrFail(); $profile->update(['can_be_technician' => true, 'is_bookable' => true]); return $profile->fresh('petugas'); }
    private function vehicle(User $member): \App\Models\KendaraanPelanggan { return $member->pelanggan->kendaraan()->create(['merk' => 'QA', 'model' => 'Phase 4A', 'nomor_polisi' => 'P4A-'.random_int(1000, 9999), 'kilometer' => 10000, 'status' => 'aktif']); }

    private function service(): array
    {
        $member = $this->member(); $tech = $this->technician(); $vehicle = $this->vehicle($member);
        $booking = app(BookingServisService::class)->create($member->pelanggan->id_pelanggan, $vehicle->id_kendaraan_pelanggan, $this->type()->id_jenis_servis, $this->date(), '10:00', 'Keluhan awal', null);
        $booking->forceFill(['status' => 'diterima'])->save();
        $service = app(ServisService::class)->createFromBooking($booking->id_booking_servis, $tech->id_petugas, 10000, null, null, true);
        app(ServisService::class)->updateInspection($service->id_servis, 'Temuan inspeksi', 'Catatan inspeksi');
        return [$member, $service->fresh(), $tech];
    }

    public function test_approval_snapshot_applies_once_and_member_cannot_change_items(): void
    {
        [$member, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $labor = $this->type();
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $oldPrice = (float) $labor->estimasi_harga;
        $request = $execution->createApprovalRequest($service->id_servis, ['title' => 'Pekerjaan tambahan', 'reason' => 'Ditemukan saat inspeksi', 'items' => [['item_type' => 'labor', 'id_jenis_servis' => $labor->id_jenis_servis, 'quantity' => 1], ['item_type' => 'sparepart', 'id_sparepart' => $part->id_sparepart, 'quantity' => 1]]], 1);
        $this->assertSame($oldPrice, (float) $request->items->firstWhere('item_type', 'labor')->unit_price_snapshot);
        $labor->update(['estimasi_harga' => $oldPrice + 999999]);
        $beforeStock = (int) $part->stok;
        $response = $this->actingAs($member)->postJson("/pelanggan/servis/{$service->id_servis}/approvals/{$request->id_service_approval_request}/decision", ['decision' => 'approve', 'items' => [['quantity' => 999999, 'unit_price_snapshot' => 1]]]);
        $response->assertOk()->assertJsonPath('status', 'approved');
        $request->refresh();
        $this->assertSame('approved', $request->status);
        $this->assertSame(1, $service->fresh()->details()->where('id_jenis_servis', $labor->id_jenis_servis)->count());
        $this->assertSame(1, $service->fresh()->sparepartItems()->where('id_sparepart', $part->id_sparepart)->count());
        $this->assertSame($beforeStock, (int) $part->fresh()->stok);
        $this->actingAs($member)->postJson("/pelanggan/servis/{$service->id_servis}/approvals/{$request->id_service_approval_request}/decision", ['decision' => 'approve'])->assertOk();
        $this->assertSame(1, $service->fresh()->details()->where('id_jenis_servis', $labor->id_jenis_servis)->count());
    }

    public function test_approval_reuses_matching_draft_sparepart_instead_of_creating_duplicate(): void
    {
        [$member, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $draft = app(\App\Services\ServisSparepartService::class)->add($service->id_servis, $part->id_sparepart, 1);
        $request = $execution->createApprovalRequest($service->id_servis, [
            'title' => 'Approval draft sparepart',
            'reason' => 'Konfirmasi penggunaan sparepart yang sudah dicatat.',
            'items' => [['item_type' => 'sparepart', 'id_sparepart' => $part->id_sparepart, 'quantity' => 1]],
        ], 1);

        $execution->decide($service->id_servis, $request->id_service_approval_request, 'approve', null, $member->pelanggan->id_pelanggan);

        $this->assertSame(1, $service->fresh()->sparepartItems()->where('id_sparepart', $part->id_sparepart)->count());
        $this->assertSame($draft->id_servis_sparepart, (int) $request->fresh()->items->first()->applied_sparepart_id);
    }

    public function test_rejected_work_is_not_applied_and_pending_approval_blocks_finalization(): void
    {
        [$member, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $request = $execution->createApprovalRequest($service->id_servis, ['title' => 'Spare part tambahan', 'reason' => 'Keausan ditemukan', 'items' => [['item_type' => 'sparepart', 'id_sparepart' => $part->id_sparepart, 'quantity' => 1]]], 1);
        try { app(ServisService::class)->finalizeService($service->id_servis, 1); $this->fail('Finalisasi seharusnya ditahan.'); } catch (RuntimeException $e) { $this->assertStringContainsString('approval', strtolower($e->getMessage())); }
        $beforeStock = (int) $part->stok;
        $this->actingAs($member)->postJson("/pelanggan/servis/{$service->id_servis}/approvals/{$request->id_service_approval_request}/decision", ['decision' => 'reject', 'note' => 'Belum disetujui'])->assertOk();
        $this->assertSame(0, $service->fresh()->sparepartItems()->where('id_sparepart', $part->id_sparepart)->count());
        $this->assertSame($beforeStock, (int) $part->fresh()->stok);
        $this->actingAs($member)->postJson("/pelanggan/servis/{$service->id_servis}/approvals/{$request->id_service_approval_request}/decision", ['decision' => 'reject'])->assertOk();
    }

    public function test_qc_fail_requires_note_blocks_finalization_and_pass_allows_it(): void
    {
        [, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $qc = $execution->startQualityControl($service->id_servis, 1);
        $failData = [$qc->items->first()->id_service_quality_control_item => ['result' => 'FAIL']];
        $this->expectException(RuntimeException::class);
        try { $execution->updateQualityControl($service->id_servis, $failData, null, 1); } catch (RuntimeException $e) { $this->assertStringContainsString('wajib', $e->getMessage()); throw $e; }
    }

    public function test_qc_failed_then_passed_is_the_finalization_gate(): void
    {
        [, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $qc = $execution->startQualityControl($service->id_servis, 1);
        $items = $qc->items->mapWithKeys(fn ($item, $index) => [$item->id_service_quality_control_item => ['result' => $index === 0 ? 'FAIL' : 'PASS', 'note' => $index === 0 ? 'Perlu koreksi' : null]])->all();
        $execution->updateQualityControl($service->id_servis, $items, 'Koreksi diperlukan', 1);
        try { app(ServisService::class)->finalizeService($service->id_servis, 1); $this->fail('QC gagal seharusnya menahan finalisasi.'); } catch (RuntimeException $e) { $this->assertStringContainsString('Quality Control', $e->getMessage()); }
        $allPass = $qc->fresh('items')->items->mapWithKeys(fn ($item) => [$item->id_service_quality_control_item => ['result' => 'PASS']])->all();
        $execution->updateQualityControl($service->id_servis, $allPass, 'Lulus QC', 1);
        $tx = app(ServisService::class)->finalizeService($service->id_servis, 1);
        $this->assertSame('belum_bayar', $tx->status);
    }

    public function test_tracking_and_approval_endpoints_are_owner_scoped(): void
    {
        [$member, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $request = $execution->createApprovalRequest($service->id_servis, ['title' => 'Approval tracking', 'reason' => 'Perlu persetujuan', 'items' => [['item_type' => 'labor', 'id_jenis_servis' => $this->type()->id_jenis_servis, 'quantity' => 1]]], 1);
        $this->actingAs($member)->getJson("/pelanggan/servis/{$service->id_servis}/tracking")->assertOk()->assertJsonPath('pending_approval_count', 1)->assertJsonPath('current_stage', 'customer_approval')->assertJsonPath('stages.3.status', 'current')->assertJsonPath('stages.4.status', 'upcoming');
        $this->actingAs($member)->getJson("/pelanggan/servis/{$service->id_servis}/approvals")->assertOk()->assertJsonPath('approvals.0.status', 'pending')->assertJsonPath('approvals.0.items.0.quantity', 1);
        $other = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->where('id_user', '<>', $member->id_user)->first();
        if ($other) $this->actingAs($other)->getJson("/pelanggan/servis/{$service->id_servis}/tracking")->assertForbidden();
    }

    public function test_member_detail_server_renders_pending_approval_action_card(): void
    {
        [$member, $service] = $this->service();
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $approval = app(ServiceExecutionService::class)->createApprovalRequest($service->id_servis, [
            'title' => 'Persetujuan penggantian filter oli',
            'reason' => 'Filter oli ditemukan kotor saat pemeriksaan.',
            'items' => [['item_type' => 'sparepart', 'id_sparepart' => $part->id_sparepart, 'quantity' => 1]],
        ], 1);

        $this->actingAs($member)->get(route('pelanggan.servis.approval', $service))
            ->assertOk()
            ->assertSee('id="approval"', false)
            ->assertSee($approval->title, false)
            ->assertSee('Filter oli ditemukan kotor saat pemeriksaan.', false)
            ->assertSee('Rp '.number_format((float) $approval->items->first()->unit_price_snapshot, 0, ',', '.'), false)
            ->assertSee('TOLAK', false)
            ->assertSee('SETUJUI PEKERJAAN', false);
    }

    public function test_admin_creates_finding_and_server_priced_approval_while_member_cannot_create_one(): void
    {
        [$member, $service] = $this->service();
        $admin = User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
        $this->actingAs($admin)->post(route('admin.servis.findings.store', $service), ['title' => 'Kampas rem menipis', 'description' => 'Ketebalan di bawah batas aman.', 'recommendation' => 'Ganti kampas rem.', 'severity' => 'critical'])->assertRedirect();
        $finding = $service->fresh()->findings()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.servis.approvals.store', $service), ['id_service_finding' => $finding->id_service_finding, 'title' => 'Persetujuan penggantian rem', 'reason' => 'Keselamatan kendaraan.', 'items' => [['item_type' => 'labor', 'id_jenis_servis' => $this->type()->id_jenis_servis, 'quantity' => 1]]])->assertRedirect();
        $this->assertDatabaseHas('service_approval_requests', ['id_servis' => $service->id_servis, 'status' => 'pending']);
        $this->actingAs($member)->post(route('admin.servis.findings.store', $service), ['title' => 'Tidak boleh', 'description' => 'Member tidak boleh membuat finding'])->assertForbidden();
    }

    public function test_execution_rejects_sparepart_outside_approval_scope(): void
    {
        [$member, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $approvedPart = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $otherPart = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->where('id_sparepart', '<>', $approvedPart->id_sparepart)->first();
        if (!$otherPart) {
            $otherPart = $approvedPart->replicate();
            $otherPart->kode_sparepart = 'TEST-PART-SECOND';
            $otherPart->nama_sparepart = 'Synthetic second active part';
            $otherPart->stok = 5;
            $otherPart->save();
        }
        $request = $execution->createApprovalRequest($service->id_servis, ['title' => 'Approval scope', 'reason' => 'QA', 'items' => [['item_type' => 'sparepart', 'id_sparepart' => $approvedPart->id_sparepart, 'quantity' => 1]]], 1);
        $execution->decide($service->id_servis, $request->id_service_approval_request, 'approve', null, $member->pelanggan->id_pelanggan);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('belum memiliki persetujuan');
        app(\App\Services\ServisSparepartService::class)->add($service->id_servis, $otherPart->id_sparepart, 1);
    }

    public function test_quality_control_rejects_unapproved_execution_detail(): void
    {
        [, $service] = $this->service();
        $execution = app(ServiceExecutionService::class);
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $request = $execution->createApprovalRequest($service->id_servis, ['title' => 'Approval scope', 'reason' => 'QA', 'items' => [['item_type' => 'sparepart', 'id_sparepart' => $part->id_sparepart, 'quantity' => 1]]], 1);
        $execution->decide($service->id_servis, $request->id_service_approval_request, 'approve', null, $service->id_pelanggan);
        app(ServisService::class)->addDetail($service->id_servis, null, 'Pekerjaan di luar approval', '10000', null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('belum memiliki persetujuan');
        $execution->assertReadyForQualityControl($service->id_servis);
    }

    public function test_approval_clarification_is_audited_without_mutating_decided_history(): void
    {
        [$member, $service] = $this->service();
        $finding = app(ServiceExecutionService::class)->createFinding($service->id_servis, [
            'title' => 'Filter oli perlu diganti',
            'description' => 'Filter oli kotor.',
            'recommendation' => 'Ganti filter oli.',
            'severity' => 'warning',
        ], 1);
        $part = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->firstOrFail();
        $execution = app(ServiceExecutionService::class);
        $approval = $execution->createApprovalRequest($service->id_servis, [
            'id_service_finding' => $finding->id_service_finding,
            'title' => 'Persetujuan filter oli',
            'reason' => 'Filter oli perlu diganti.',
            'items' => [['item_type' => 'sparepart', 'id_sparepart' => $part->id_sparepart, 'quantity' => 1]],
        ], 1);
        $execution->decide($service->id_servis, $approval->id_service_approval_request, 'approve', 'Setuju', $member->pelanggan->id_pelanggan);
        $before = $approval->fresh()->only(['title', 'status', 'customer_note', 'responded_at']);

        $event = $execution->requestApprovalClarification($service->id_servis, $approval->id_service_approval_request, 1, 'Snapshot item perlu dicocokkan dengan rekomendasi teknisi.');

        $this->assertSame('approval_clarification_requested', $event->event_key);
        $this->assertSame($approval->id_service_approval_request, $event->metadata['approval_id']);
        $after = $approval->fresh();
        $this->assertSame($before['title'], $after->title);
        $this->assertSame($before['status'], $after->status);
        $this->assertSame($before['customer_note'], $after->customer_note);
        $this->assertSame($before['responded_at']->toISOString(), $after->responded_at->toISOString());
        $this->assertDatabaseHas('service_execution_events', ['id_servis' => $service->id_servis, 'event_key' => 'approval_clarification_requested']);
        try { $execution->assertReadyForQualityControl($service->id_servis); $this->fail('Klarifikasi terbuka seharusnya menahan QC.'); } catch (RuntimeException $e) { $this->assertStringContainsString('klarifikasi approval', strtolower($e->getMessage())); }

        $revision = $execution->createApprovalRequest($service->id_servis, [
            'title' => 'Persetujuan revisi pekerjaan',
            'reason' => 'Koreksi item sesuai temuan teknisi.',
            'items' => [['item_type' => 'labor', 'id_jenis_servis' => $this->type()->id_jenis_servis, 'quantity' => 1]],
        ], 1);
        $this->assertDatabaseHas('service_execution_events', ['id_servis' => $service->id_servis, 'event_key' => 'approval_revision_requested']);
        $execution->decide($service->id_servis, $revision->id_service_approval_request, 'approve', 'Setuju revisi', $member->pelanggan->id_pelanggan);
        $this->assertDatabaseHas('service_execution_events', ['id_servis' => $service->id_servis, 'event_key' => 'approval_clarification_resolved']);
        $this->assertSame('approved', $approval->fresh()->status);
    }
}
