<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SalesAwardsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SalesExcellenceAwardsStage5Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    public function test_owner_can_view_provisional_awards_and_export(): void
    {
        $owner = User::where('username', 'owner506')->firstOrFail();
        $this->actingAs($owner)->get(route('owner.sales-awards'))->assertOk()->assertSee('Sales Excellence Awards')->assertSee('PROVISIONAL');
        $this->actingAs($owner)->get(route('owner.sales-awards-export', ['year' => now('Asia/Jakarta')->year, 'format' => 'excel']))->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_sales_can_view_own_awards_but_member_cannot(): void
    {
        $sales = User::where('username', 'sales506')->firstOrFail();
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $this->actingAs($sales)->get(route('sales.awards'))->assertOk();
        $this->actingAs($member)->get(route('owner.sales-awards'))->assertForbidden();
    }

    public function test_finalization_is_blocked_without_refund_authoritative_source_and_rule_is_deterministic(): void
    {
        $service = app(SalesAwardsService::class);
        $year = now('Asia/Jakarta')->year - 1;
        $rows = $service->leaderboard($year);
        $this->assertSame($rows, $service->leaderboard($year));
        $this->expectException(\RuntimeException::class);
        $service->finalize($year, User::where('username', 'owner506')->firstOrFail()->petugas->id_petugas);
    }
}
