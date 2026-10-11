<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPaymentManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_open_payment_management(): void
    {
        $admin = User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))->firstOrFail();
        $this->actingAs($admin)->get('/admin/pembayaran')->assertOk()->assertSee('Manajemen Pembayaran')->assertSee('PEMBAYARAN OFFLINE');
    }

    public function test_member_cannot_open_payment_management(): void
    {
        $member = User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))->firstOrFail();
        $this->actingAs($member)->get('/admin/pembayaran')->assertForbidden();
    }
}
