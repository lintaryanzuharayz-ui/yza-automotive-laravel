<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ServiceWorkflowRefinementTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithRole(string $role): User
    {
        return User::whereHas('role', fn ($query) => $query->where('nama_role', $role))->firstOrFail();
    }

    public function test_petugas_can_open_service_command_center(): void
    {
        $this->actingAs($this->userWithRole('petugas'))
            ->get('/petugas/dashboard')
            ->assertOk()
            ->assertSee('Service Operations')
            ->assertSee('Antrean Servis Hari Ini');
    }

    public function test_admin_can_open_service_report(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/admin/laporan-servis')
            ->assertOk()
            ->assertSee('Laporan Servis')
            ->assertSee('Avg Transaction');
    }

    public function test_customer_cannot_open_admin_service_report(): void
    {
        $this->actingAs($this->userWithRole('pelanggan'))
            ->get('/admin/laporan-servis')
            ->assertForbidden();
    }

    public function test_guest_cannot_open_internal_notifications(): void
    {
        $this->get('/notifikasi')->assertRedirect('/login');
    }
}
