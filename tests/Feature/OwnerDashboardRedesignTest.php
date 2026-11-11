<?php

namespace Tests\Feature;

use App\Models\{Mobil, Pembayaran, User};
use App\Services\OwnerAnalyticsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OwnerDashboardRedesignTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', 'owner'))->firstOrFail();
    }

    public function test_dashboard_and_sales_report_share_final_sales_scope_and_period(): void
    {
        $this->actingAs($this->owner());
        $period = ['from' => '2026-01-01', 'to' => '2026-10-27'];
        $dashboard = $this->get(route('owner.dashboard', $period))->assertOk();
        $report = $this->get(route('owner.sales-report', $period))->assertOk();
        $kpis = $dashboard->viewData('kpis');
        $this->assertSame($report->viewData('totalUnits'), $kpis['final_sales']);
        $this->assertSame($report->viewData('totalValue'), $kpis['final_sales_value']);
        $months = $dashboard->viewData('monthlySales');
        $this->assertSame($kpis['final_sales'], array_sum(array_column($months, 'units')));
        $this->assertEquals($kpis['final_sales_value'], array_sum(array_column($months, 'amount')));
        $this->assertEquals(Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')->whereBetween('verified_at', ['2026-01-01 00:00:00', '2026-10-27 23:59:59'])->sum('jumlah'), $kpis['verified_payments']);
        $this->assertSame(Mobil::count(), array_sum(array_column($dashboard->viewData('ownerInventory'), 'units')));
        $dashboard->assertSee('data-kpi="final_sales"', false)->assertSee('data-owner-shell', false)->assertDontSee('placeholder="Cari menu', false);
        $this->assertSame(8, substr_count($dashboard->getContent(), 'data-kpi="'));
        $this->assertSame(1, substr_count($dashboard->getContent(), 'aria-current="page"'));
    }

    public function test_empty_period_changes_flow_metrics_but_preserves_snapshots(): void
    {
        $this->actingAs($this->owner());
        $all = $this->get(route('owner.dashboard'))->assertOk()->viewData('kpis');
        $empty = $this->get(route('owner.dashboard', ['from' => '2000-01-01', 'to' => '2000-01-02']))->assertOk()->assertSee('Belum ada penjualan final');
        $filtered = $empty->viewData('kpis');
        foreach (['final_sales', 'final_sales_value', 'handed_over', 'active_orders', 'verified_payments'] as $key) $this->assertEquals(0, $filtered[$key]);
        foreach (['available_stock', 'active_work_orders', 'customers'] as $key) $this->assertSame($all[$key], $filtered[$key]);
        $this->assertSame([], $empty->viewData('ownerActivities'));
    }

    public function test_invalid_and_reversed_dates_are_rejected(): void
    {
        $this->actingAs($this->owner());
        $this->getJson(route('owner.dashboard', ['from' => 'invalid']))->assertUnprocessable()->assertJsonValidationErrors('from');
        $this->getJson(route('owner.dashboard', ['from' => '2026-10-27', 'to' => '2026-01-01']))->assertUnprocessable()->assertJsonValidationErrors('to');
    }

    public function test_activity_dates_are_in_period_and_expose_no_customer_details(): void
    {
        $this->actingAs($this->owner());
        $response = $this->get(route('owner.dashboard', ['from' => '2026-10-01', 'to' => '2026-10-27']))->assertOk();
        foreach ($response->viewData('ownerActivities') as $row) {
            $this->assertTrue($row['date']->between('2026-10-01 00:00:00', '2026-10-27 23:59:59'));
            $this->assertContains($row['route'], ['owner.sales-report', 'owner.payment-report', 'owner.service-report']);
            $this->assertArrayNotHasKey('customer', $row);
        }
    }

    public function test_owner_shell_does_not_replace_admin_or_sales_shell(): void
    {
        foreach (['admin', 'sales'] as $role) {
            $user = User::whereHas('role', fn ($q) => $q->where('nama_role', $role))->firstOrFail();
            $this->actingAs($user)->get(route($role.'.dashboard'))->assertOk()->assertDontSee('data-owner-shell', false);
        }
    }
}
