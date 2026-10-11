<?php

namespace Tests\Feature;

use App\Models\{Pembayaran,User};
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

    public function test_admin_payment_and_notification_pages_have_no_server_error(): void
    {
        $admin = User::whereHas('role', fn ($query) => $query->where('nama_role', 'admin'))->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/pembayaran?tab=online')->assertOk();
        $this->get('/admin/pembayaran?tab=offline')->assertOk();
        $payment = Pembayaran::where('arah', 'masuk')->first();
        if ($payment) {
            $this->get(route('admin.payments.show', $payment))->assertOk();
        }
        $this->get('/admin/notifikasi')->assertOk();
        $this->get('/admin/inventory/stok')->assertOk();
    }
}
