<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class YzaRoleManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_local_staff_command_is_idempotent_and_hashes_passwords(): void
    {
        $this->assertSame(0, Artisan::call('yza:seed-local-staff'));
        $this->assertSame(0, Artisan::call('yza:seed-local-staff'));

        $this->assertSame(1, User::where('username', 'superadmin506')->count());
        $this->assertSame(1, User::where('username', 'owner506')->count());
        $owner = User::where('username', 'owner506')->firstOrFail();
        $this->assertTrue(Hash::check('owner506', $owner->password));
        $this->assertSame('owner', $owner->roleKey());
    }

    public function test_role_dashboard_and_permission_boundary_are_server_side(): void
    {
        Artisan::call('yza:seed-local-staff');
        $sales = User::where('username', 'sales506')->firstOrFail();
        $finance = User::where('username', 'finance506')->firstOrFail();

        $this->actingAs($sales)->get(route('staff.dashboard'))->assertOk();
        $this->actingAs($sales)->get(route('admin.payments.index'))->assertForbidden();
        $this->actingAs($finance)->get(route('admin.payments.index'))->assertOk();
        $this->actingAs($finance)->get(route('admin.pelanggan.index'))->assertForbidden();
    }

    public function test_all_local_staff_accounts_can_login(): void
    {
        Artisan::call('yza:seed-local-staff');
        foreach (['superadmin506', 'owner506', 'sales506', 'advisor506', 'mekanik506', 'gudang506', 'finance506', 'operasional506', 'qc506'] as $username) {
            $this->post(route('login.store'), ['identity' => $username, 'password' => $username])
                ->assertRedirect();
            $this->post(route('logout'));
        }
    }

    public function test_existing_legacy_admin_maps_to_super_admin(): void
    {
        $admin = User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))->firstOrFail();
        $this->assertSame('super_admin', $admin->roleKey());
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }
}
