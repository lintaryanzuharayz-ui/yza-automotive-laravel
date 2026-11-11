<?php

namespace Tests\Feature;

use App\Models\{Servis, User, ServiceWorkOrderAssignment};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{Artisan, DB};
use Tests\TestCase;

class AuthorizationHardeningStage1Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    private function staff(string $role): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    private function workOrder(?User $technician = null, string $status = 'dikerjakan'): Servis
    {
        $service = Servis::firstOrFail()->replicate();
        $service->forceFill([
            'kode_servis' => 'AUTH-'.bin2hex(random_bytes(6)),
            'id_booking_servis' => null, 'id_transaksi' => null,
            'id_petugas' => ($technician ?? $this->staff('petugas'))->petugas->id_petugas,
            'status' => $status, 'hasil_pemeriksaan' => 'Diagnosis QA', 'tanggal_selesai' => null,
        ])->save();
        return $service;
    }

    private function snapshot(): string
    {
        $tables = ['pengaturan', 'kategori_sparepart', 'supplier', 'servis', 'detail_servis', 'servis_sparepart',
            'service_work_order_assignments', 'service_findings', 'service_approval_requests',
            'service_approval_request_items', 'service_quality_controls', 'service_quality_control_items',
            'service_execution_events', 'sparepart', 'riwayat_stok', 'transaksi', 'pembayaran', 'notifikasi',
            'penjualan_mobil', 'mobil', 'vehicle_inventories', 'financial_adjustments', 'booking_servis',
            'kendaraan_pelanggan', 'users', 'petugas', 'role_permissions', 'service_staff_profiles'];
        return hash('sha256', json_encode(collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->get()->all()])->all()));
    }

    public function test_owner_cannot_update_settings_without_changing_database(): void
    {
        $before = $this->snapshot();
        $this->actingAs($this->staff('owner'))->put('/admin/pengaturan', ['settings' => ['nama_singkat' => 'UNAUTHORIZED']])->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_owner_cannot_toggle_category_without_changing_database(): void
    {
        $id = DB::table('kategori_sparepart')->value('id_kategori');
        $before = $this->snapshot();
        $this->actingAs($this->staff('owner'))->patch('/admin/kategori-sparepart/'.$id.'/toggle')->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_unassigned_mechanic_cannot_create_finding(): void
    {
        $service = $this->workOrder();
        $before = $this->snapshot();
        $this->actingAs($this->staff('mechanic'))->post('/admin/servis/'.$service->id_servis.'/findings', ['title' => 'Unauthorized', 'description' => 'Must not persist'])->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_revoked_primary_cannot_use_stale_primary_column_to_open_work_order(): void
    {
        $mechanic = $this->staff('mechanic');
        $service = $this->workOrder($mechanic);
        $service->assignments()->update(['unassigned_at' => now()]);
        $before = $this->snapshot();
        $this->actingAs($mechanic)->get('/admin/servis/'.$service->id_servis)->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_owner_keeps_report_access_but_all_business_mutation_routes_are_denied(): void
    {
        $owner = $this->staff('owner');
        $this->actingAs($owner);
        foreach (['owner.dashboard', 'owner.sales-report', 'owner.service-report', 'owner.payment-report', 'owner.inventory-report', 'owner.performance-report'] as $name) {
            $this->get(route($name))->assertOk();
        }
        $service = $this->workOrder();
        $before = $this->snapshot();
        $cases = [
            ['PUT', '/admin/servis/'.$service->id_servis.'/assignment'],
            ['POST', '/admin/servis/'.$service->id_servis.'/inspection'],
            ['POST', '/admin/servis/'.$service->id_servis.'/qc/start'],
            ['POST', '/admin/servis/'.$service->id_servis.'/finalize'],
            ['POST', '/admin/inventory/penyesuaian'], ['POST', '/admin/penjualan-mobil'],
            ['POST', '/admin/penjualan-sparepart'], ['POST', '/admin/financial-adjustments'],
            ['POST', '/admin/mobil/'.DB::table('mobil')->value('id_mobil').'/stok'],
            ['POST', '/admin/pembayaran/'.DB::table('pembayaran')->value('id_pembayaran').'/confirm'],
        ];
        foreach ($cases as [$method, $uri]) {
            $this->call($method, $uri, ['id_petugas' => $owner->petugas->id_petugas])->assertForbidden();
            $this->assertSame($before, $this->snapshot(), $uri);
        }
    }

    private function mutationCases(Servis $service): array
    {
        $detail = $service->details()->create(['deskripsi_pekerjaan' => 'QA', 'biaya' => 100]);
        $line = \App\Models\ServisSparepart::forceCreate(['id_servis' => $service->id_servis, 'id_sparepart' => DB::table('sparepart')->value('id_sparepart'), 'jumlah' => 1, 'harga_satuan' => 100, 'subtotal' => 100]);
        $approval = $service->approvalRequests()->create(['title' => 'QA', 'reason' => 'QA', 'status' => 'approved', 'requested_at' => now(), 'requested_by' => $service->id_petugas]);
        $root = '/admin/servis/'.$service->id_servis;
        return [
            ['POST', $root.'/inspection', ['hasil_pemeriksaan' => 'Modified']],
            ['POST', $root.'/detail', ['deskripsi_pekerjaan' => 'Modified', 'biaya' => 100]],
            ['DELETE', $root.'/detail/'.$detail->id_detail_servis, []],
            ['POST', $root.'/sparepart', ['id_sparepart' => $line->id_sparepart, 'jumlah' => 1]],
            ['DELETE', $root.'/sparepart/'.$line->id_servis_sparepart, []],
            ['POST', $root.'/findings', ['title' => 'Modified', 'description' => 'Modified']],
            ['POST', $root.'/approvals', ['title' => 'Modified', 'reason' => 'Modified', 'items' => []]],
            ['POST', $root.'/approvals/'.$approval->id_service_approval_request.'/clarification', ['reason' => 'Modified']],
            ['POST', $root.'/qc/start', []], ['PUT', $root.'/qc', ['items' => []]],
            ['POST', $root.'/finalize', []],
            ['PUT', $root.'/assignment', ['id_petugas' => $this->staff('mechanic')->petugas->id_petugas, 'reason' => 'Self assign']],
        ];
    }

    public function test_every_work_order_mutation_rejects_other_mechanics_and_body_id_spoofing(): void
    {
        $mechanic = $this->staff('mechanic');
        $own = $this->workOrder($mechanic);
        $other = $this->workOrder();
        $cases = $this->mutationCases($other);
        $before = $this->snapshot();
        $this->actingAs($mechanic);
        foreach ($cases as [$method, $uri, $data]) {
            $data['id_servis'] = $own->id_servis;
            $data['id_petugas'] = $mechanic->petugas->id_petugas;
            $this->call($method, $uri, $data)->assertForbidden();
            $this->assertSame($before, $this->snapshot(), $uri);
        }
        $this->get('/admin/servis/'.$other->id_servis.'/spareparts/search')->assertForbidden();
    }

    public function test_missing_revoked_future_and_inactive_assignments_reject_mutations(): void
    {
        $mechanic = $this->staff('mechanic');
        foreach (['missing', 'revoked', 'future', 'inactive_staff'] as $state) {
            $service = $this->workOrder($mechanic);
            if ($state === 'missing') $service->assignments()->delete();
            if ($state === 'revoked') $service->assignments()->update(['unassigned_at' => now()]);
            if ($state === 'future') $service->assignments()->update(['assigned_at' => now()->addDay()]);
            if ($state === 'inactive_staff') $mechanic->petugas->update(['status' => 'nonaktif']);
            $before = $this->snapshot();
            $this->actingAs($mechanic)->postJson('/admin/servis/'.$service->id_servis.'/findings', ['title' => 'Modified', 'description' => 'Modified'])->assertForbidden();
            $this->assertSame($before, $this->snapshot(), $state);
            $mechanic->petugas->update(['status' => 'aktif']);
        }
    }

    public function test_primary_and_assistant_can_work_but_cannot_assign_finalize_or_qc(): void
    {
        $mechanic = $this->staff('mechanic');
        foreach (['primary', 'assistant'] as $role) {
            $service = $this->workOrder($role === 'primary' ? $mechanic : null, 'pemeriksaan');
            if ($role === 'assistant') ServiceWorkOrderAssignment::create(['id_servis' => $service->id_servis, 'id_petugas' => $mechanic->petugas->id_petugas, 'assignment_role' => 'assistant', 'assigned_at' => now()]);
            $this->actingAs($mechanic)->get('/admin/servis/'.$service->id_servis)->assertRedirect();
            $this->post('/admin/servis/'.$service->id_servis.'/inspection', ['hasil_pemeriksaan' => 'Authorized '.$role])->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame('Authorized '.$role, $service->fresh()->hasil_pemeriksaan);
            $this->postJson('/admin/servis/'.$service->id_servis.'/findings', ['title' => 'Authorized', 'description' => 'Authorized'])->assertRedirect()->assertSessionHasNoErrors();
            $this->assertSame(1, $service->findings()->count());
            $before = $this->snapshot();
            foreach ([['POST', '/qc/start'], ['PUT', '/qc'], ['POST', '/finalize'], ['PUT', '/assignment']] as [$method, $suffix]) {
                $this->call($method, '/admin/servis/'.$service->id_servis.$suffix, [])->assertForbidden();
                $this->assertSame($before, $this->snapshot(), $role.$suffix);
            }
            $this->get('/admin/servis')->assertOk()->assertSee($service->kode_servis);
        }
    }

    public function test_mismatched_child_url_returns_404_without_deleting_another_order_detail(): void
    {
        $mechanic = $this->staff('mechanic');
        $own = $this->workOrder($mechanic);
        $other = $this->workOrder();
        $detail = $other->details()->create(['deskripsi_pekerjaan' => 'Protected', 'biaya' => 100]);
        $before = $this->snapshot();
        $this->actingAs($mechanic)->delete('/admin/servis/'.$own->id_servis.'/detail/'.$detail->id_detail_servis)->assertNotFound();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_advisor_can_work_but_alias_does_not_grant_finance_or_inventory_actions(): void
    {
        $service = $this->workOrder();
        $this->actingAs($this->staff('service_advisor'));
        foreach (['admin', 'petugas'] as $prefix) {
            $this->post('/'.$prefix.'/servis/'.$service->id_servis.'/findings', ['title' => 'Advisor', 'description' => 'Authorized'])->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->assertSame(2, $service->findings()->count());
        $before = $this->snapshot();
        foreach (['admin', 'petugas'] as $prefix) {
            $payment = DB::table('pembayaran')->value('id_pembayaran');
            $this->post('/'.$prefix.'/pembayaran/'.$payment.($prefix === 'admin' ? '/confirm' : '/verify'))->assertForbidden();
        }
        $this->post('/admin/inventory/penyesuaian', [])->assertForbidden();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_quality_control_can_check_but_cannot_change_diagnosis_or_assignment(): void
    {
        $service = $this->workOrder();
        $this->actingAs($this->staff('quality_control'));
        $this->post('/admin/servis/'.$service->id_servis.'/qc/start')->assertRedirect()->assertSessionHasNoErrors();
        $qc = $service->fresh('qualityControl.items')->qualityControl;
        $this->assertNotNull($qc);
        $items = $qc->items->mapWithKeys(fn ($item) => [$item->id_service_quality_control_item => ['result' => 'PASS']])->all();
        $this->put('/admin/servis/'.$service->id_servis.'/qc', ['items' => $items])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('passed', $qc->fresh()->status);
        $before = $this->snapshot();
        foreach ([['POST', '/inspection'], ['POST', '/detail'], ['PUT', '/assignment'], ['POST', '/finalize']] as [$method, $suffix]) {
            $this->call($method, '/admin/servis/'.$service->id_servis.$suffix)->assertForbidden();
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function test_cross_role_direct_requests_and_unknown_modules_fail_closed(): void
    {
        $service = $this->workOrder();
        foreach (['sales', 'finance', 'warehouse_staff', 'operational_staff', 'pelanggan'] as $role) {
            $before = $this->snapshot();
            $this->actingAs($this->staff($role))->postJson('/admin/servis/'.$service->id_servis.'/findings', ['title' => 'Forbidden', 'description' => 'Forbidden'])->assertForbidden();
            $this->put('/admin/pengaturan', ['settings' => ['nama_singkat' => 'Forbidden']])->assertForbidden();
            $this->assertSame($before, $this->snapshot(), $role);
        }
        \Illuminate\Support\Facades\Route::post('/authorization-unknown', fn () => response('must not execute'))
            ->middleware(['web', 'auth', 'module.access'])->name('admin.unmapped.store');
        $this->actingAs($this->staff('service_advisor'))->post('/authorization-unknown')->assertForbidden();
    }

    public function test_guest_requests_redirect_or_return_401_without_side_effects(): void
    {
        $service = $this->workOrder();
        $before = $this->snapshot();
        $this->post('/admin/servis/'.$service->id_servis.'/findings')->assertRedirect('/login');
        $this->postJson('/admin/servis/'.$service->id_servis.'/findings')->assertUnauthorized();
        $this->assertSame($before, $this->snapshot());
    }

    public function test_authenticated_direct_service_calls_cannot_bypass_controller_guards(): void
    {
        $service = $this->workOrder();
        $this->actingAs($this->staff('mechanic'));
        $before = $this->snapshot();
        $attempts = [
            fn () => app(\App\Services\ServisService::class)->updateInspection($service->id_servis, 'Denied', null),
            fn () => app(\App\Services\ServisService::class)->addDetail($service->id_servis, null, 'Denied', '100', null),
            fn () => app(\App\Services\ServisSparepartService::class)->remove($service->id_servis, 1),
            fn () => app(\App\Services\ServiceExecutionService::class)->createFinding($service->id_servis, ['title' => 'Denied', 'description' => 'Denied'], $service->id_petugas),
            fn () => app(\App\Services\ServiceExecutionService::class)->startQualityControl($service->id_servis, $service->id_petugas),
            fn () => app(\App\Services\ServiceExecutionService::class)->decide($service->id_servis, 1, 'approve', null, $service->id_pelanggan),
            fn () => app(\App\Services\ServiceAssignmentService::class)->assignPrimary($service, $this->staff('mechanic')->petugas->id_petugas),
            fn () => app(\App\Services\ServisService::class)->finalizeService($service->id_servis, $service->id_petugas),
        ];
        foreach ($attempts as $attempt) {
            try { $attempt(); $this->fail('Expected authorization denial'); }
            catch (\Illuminate\Auth\Access\AuthorizationException $e) { $this->assertTrue(true); }
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function test_all_staff_work_order_resource_routes_have_shared_assignment_guard(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (preg_match('#^(admin|petugas)/servis/\{servis\}#', $route->uri())) {
                $this->assertContains('assigned.wo', $route->gatherMiddleware(), $route->getName());
                $this->assertContains('module.access', $route->gatherMiddleware(), $route->getName());
            }
        }
    }

    public function test_owner_gate_covers_every_registered_admin_business_mutation(): void
    {
        $owner = $this->staff('owner');
        $before = $this->snapshot();
        $checked = 0;
        foreach (app('router')->getRoutes() as $route) {
            if (!str_starts_with((string) $route->getName(), 'admin.') || $route->getName() === 'admin.notifikasi.read') continue;
            foreach (array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']) as $method) {
                $request = \Illuminate\Http\Request::create('/gate-probe', $method);
                $request->setUserResolver(fn () => $owner);
                $request->setRouteResolver(fn () => $route);
                try {
                    app(\App\Http\Middleware\RoleModuleAccessMiddleware::class)->handle($request, fn () => response('must not execute'));
                    $this->fail('Owner mutation allowed: '.$route->getName());
                } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                    $this->assertSame(403, $e->getStatusCode(), $route->getName());
                }
                $checked++;
            }
        }
        $this->assertGreaterThan(50, $checked);
        $this->assertSame($before, $this->snapshot());
    }

    public function test_advisor_assignment_validates_technician_server_side_and_preserves_history(): void
    {
        $service = $this->workOrder();
        $profile = \App\Models\ServiceStaffProfile::where('id_petugas', '<>', $service->id_petugas)->firstOrFail();
        $profile->update(['can_be_technician' => true]);
        $this->actingAs($this->staff('service_advisor'))->put('/admin/servis/'.$service->id_servis.'/assignment', [
            'id_petugas' => $profile->id_petugas, 'assistant_ids' => [], 'reason' => 'Authorized reassignment',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame((int) $profile->id_petugas, (int) $service->fresh()->id_petugas);
        $this->assertDatabaseHas('service_work_order_assignments', ['id_servis' => $service->id_servis,
            'id_petugas' => $profile->id_petugas, 'assignment_role' => 'primary', 'unassigned_at' => null]);
    }

    public function test_route_work_order_id_wins_over_body_id_for_authorized_mechanic(): void
    {
        $mechanic = $this->staff('mechanic');
        $own = $this->workOrder($mechanic);
        $other = $this->workOrder();
        $this->actingAs($mechanic)->postJson('/admin/servis/'.$own->id_servis.'/findings', [
            'id_servis' => $other->id_servis, 'id_petugas' => $other->id_petugas,
            'title' => 'Own only', 'description' => 'Server must use URL resource and session actor',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, $own->findings()->count());
        $this->assertSame(0, $other->findings()->count());
        $this->assertSame((int) $mechanic->petugas->id_petugas, (int) $own->findings()->firstOrFail()->created_by);
    }
}
