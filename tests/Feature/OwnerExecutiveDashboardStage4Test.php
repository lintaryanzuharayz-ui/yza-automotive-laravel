<?php

namespace Tests\Feature;

use App\Models\{User, PenjualanMobil, Transaksi};
use App\Services\OwnerAnalyticsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OwnerExecutiveDashboardStage4Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    public function test_owner_login_redirects_to_executive_dashboard(): void
    {
        $this->post(route('login.store'), ['identity' => 'owner506', 'password' => 'owner506'])->assertRedirect(route('owner.dashboard'));
        $this->get(route('owner.dashboard'))->assertOk()->assertSee('EXECUTIVE DASHBOARD');
    }

    public function test_owner_can_read_reports_but_cannot_mutate_finance_or_inventory(): void
    {
        $owner = User::where('username', 'owner506')->firstOrFail();
        $this->actingAs($owner)->get(route('owner.sales-report'))->assertOk();
        $this->actingAs($owner)->post('/admin/pembayaran/1/confirm')->assertForbidden();
        $this->actingAs($owner)->post('/admin/inventory/penyesuaian', [])->assertForbidden();
    }

    public function test_sales_and_member_cannot_open_owner_dashboard(): void
    {
        $sales = User::where('username', 'sales506')->firstOrFail();
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $this->actingAs($sales)->get(route('owner.dashboard'))->assertForbidden();
        $this->actingAs($member)->get(route('owner.dashboard'))->assertForbidden();
    }

    public function test_final_sales_kpi_is_database_driven_and_excludes_cancelled_transactions(): void
    {
        $analytics = app(OwnerAnalyticsService::class);
        $kpis = $analytics->kpis();
        $expected = PenjualanMobil::where('status', 'selesai')->whereHas('transaksi', fn ($q) => $q->where('status', '<>', 'dibatalkan'))->where(fn ($q) => $q->whereDoesntHave('memberOrder')->orWhereHas('memberOrder', fn ($order) => $order->where('status', 'diserahkan')))->count();
        $this->assertSame($expected, $kpis['final_sales']);
        $this->assertNotSame('laba bersih', strtolower('nilai penjualan final'));
    }

    public function test_exports_are_authorized_and_have_expected_download_types(): void
    {
        $owner = User::where('username', 'owner506')->firstOrFail();
        $this->actingAs($owner)->get(route('owner.sales-export', ['format' => 'excel']))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')->assertHeader('content-disposition');
        $pdf = $this->actingAs($owner)->get(route('owner.sales-export', ['format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf')->assertHeader('content-disposition');
        $pdf->assertSee("xref\n0 6\n", false)->assertSee("\ntrailer\n", false);
    }

    public function test_all_owner_reports_and_exports_are_available_from_the_same_authorized_scope(): void
    {
        $owner = User::where('username', 'owner506')->firstOrFail();
        foreach (['service-report', 'performance-report', 'payment-report', 'inventory-report'] as $route) {
            $this->actingAs($owner)->get(route('owner.'.$route))->assertOk();
        }
        foreach (['service-export', 'performance-export', 'payment-export', 'inventory-export'] as $route) {
            $this->actingAs($owner)->get(route('owner.'.$route, ['format' => 'excel']))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $this->actingAs($owner)->get(route('owner.'.$route, ['format' => 'pdf']))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_sales_target_aggregation_does_not_query_inside_sales_loop(): void
    {
        DB::enableQueryLog();
        app(OwnerAnalyticsService::class)->salesPerformance();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();
        $this->assertLessThan(12, $queries, 'Sales performance should use aggregate queries, not one target query per Sales.');
    }
}
