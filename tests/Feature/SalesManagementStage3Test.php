<?php

namespace Tests\Feature;

use App\Models\{PenjualanMobil, SalesAssignmentAudit, SalesLead, SalesTarget, TestDrive, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SalesManagementStage3Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    public function test_sales_is_redirected_to_sales_dashboard_and_sees_only_sales_routes(): void
    {
        $sales = User::where('username', 'sales506')->firstOrFail();
        $this->actingAs($sales)->get(route('sales.dashboard'))->assertOk()->assertSee('Dashboard Sales');
        $this->actingAs($sales)->get(route('admin.payments.index'))->assertForbidden();
    }

    public function test_sales_can_create_and_progress_own_lead(): void
    {
        $sales = User::where('username', 'sales506')->firstOrFail();
        $response = $this->actingAs($sales)->post(route('sales.leads.store'), [
            'nama_prospek' => 'Prospek Tahap 3', 'kontak' => '08123456789', 'sumber' => 'website', 'status' => 'baru', 'catatan' => 'Kontak awal',
        ]);
        $response->assertRedirect();
        $lead = SalesLead::where('nama_prospek', 'Prospek Tahap 3')->firstOrFail();
        $this->actingAs($sales)->post(route('sales.leads.activity', $lead), ['jenis' => 'panggilan', 'status' => 'tertarik', 'activity_note' => 'Prospek tertarik test drive'])->assertRedirect();
        $this->assertSame('tertarik', $lead->fresh()->status);
        $this->assertDatabaseHas('sales_lead_activities', ['id_sales_lead' => $lead->id_sales_lead, 'jenis' => 'panggilan']);
    }

    public function test_only_super_admin_can_reassign_lead_and_audit_is_written(): void
    {
        $sales = User::where('username', 'sales506')->firstOrFail();
        $owner = User::where('username', 'owner506')->firstOrFail();
        $lead = SalesLead::create(['nama_prospek' => 'Lead Boundary', 'id_sales' => $sales->petugas->id_petugas, 'status' => 'baru']);
        $this->actingAs($owner)->put(route('sales.leads.assignment', $lead), ['id_sales' => null])->assertForbidden();
        $admin = User::where('username', 'superadmin506')->firstOrFail();
        $this->actingAs($admin)->put(route('sales.leads.assignment', $lead), ['id_sales' => null, 'reason' => 'QA audit'])->assertRedirect();
        $this->assertSame(1, SalesAssignmentAudit::where('entity_id', $lead->id_sales_lead)->count());
    }

    public function test_target_is_visible_to_sales_but_only_super_admin_can_write(): void
    {
        $sales = User::where('username', 'sales506')->firstOrFail();
        $this->actingAs($sales)->post(route('sales.targets.store'), [])->assertForbidden();
        $admin = User::where('username', 'superadmin506')->firstOrFail();
        $this->actingAs($admin)->post(route('sales.targets.store'), ['id_sales' => $sales->petugas->id_petugas, 'periode_mulai' => now()->startOfMonth()->toDateString(), 'periode_selesai' => now()->endOfMonth()->toDateString(), 'target_unit' => 3, 'target_nominal' => 100000000])->assertRedirect();
        $this->assertSame(1, SalesTarget::where('id_sales', $sales->petugas->id_petugas)->count());
        $this->actingAs($sales)->get(route('sales.targets.index'))->assertOk()->assertSee('3');
    }

    public function test_sales_assignment_is_audited_for_existing_sale_and_test_drive_without_changing_financial_data(): void
    {
        $admin = User::where('username', 'superadmin506')->firstOrFail();
        $sales = User::where('username', 'sales506')->firstOrFail();
        $sale = PenjualanMobil::firstOrFail();
        $total = (string) $sale->total_akhir;
        $this->actingAs($admin)->put(route('sales.sales.assignment', $sale), ['id_sales' => $sales->petugas->id_petugas, 'reason' => 'Tahap 3 QA'])->assertRedirect();
        $this->assertSame($total, (string) $sale->fresh()->total_akhir);
        $this->assertSame($sales->petugas->id_petugas, $sale->fresh()->id_sales);
        $drive = TestDrive::firstOrFail();
        $this->actingAs($admin)->put(route('sales.test-drives.assignment', $drive), ['id_sales' => $sales->petugas->id_petugas, 'reason' => 'Pendamping test drive'])->assertRedirect();
        $this->assertSame($sales->petugas->id_petugas, $drive->fresh()->id_sales);
        $this->assertSame(2, SalesAssignmentAudit::whereIn('entity_type', ['sale', 'test_drive'])->count());
    }
}
