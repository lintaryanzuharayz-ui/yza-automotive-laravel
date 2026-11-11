<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FinalQaReleaseReadinessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('yza:seed-local-staff');
    }

    public function test_all_nine_local_staff_accounts_login_to_their_role_dashboard(): void
    {
        $expected = ['superadmin506' => 'admin.dashboard', 'owner506' => 'owner.dashboard', 'sales506' => 'sales.dashboard', 'advisor506' => 'staff.dashboard', 'mekanik506' => 'staff.dashboard', 'gudang506' => 'staff.dashboard', 'finance506' => 'staff.dashboard', 'operasional506' => 'staff.dashboard', 'qc506' => 'staff.dashboard'];
        foreach ($expected as $username => $route) {
            $this->post(route('login.store'), ['identity' => $username, 'password' => $username])->assertRedirect(route($route));
            $this->post(route('logout'));
        }
    }

    public function test_internal_awards_are_not_reachable_by_member_or_mechanic(): void
    {
        $member = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
        $mechanic = User::where('username', 'mekanik506')->firstOrFail();
        $this->actingAs($member)->get(route('owner.sales-awards'))->assertForbidden();
        $this->actingAs($mechanic)->get(route('owner.sales-awards'))->assertForbidden();
    }
}
