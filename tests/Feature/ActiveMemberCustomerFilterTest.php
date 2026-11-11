<?php

namespace Tests\Feature;

use App\Models\{Pelanggan, User, EventPembayaranGateway};
use App\Services\ActiveMemberCustomerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActiveMemberCustomerFilterTest extends TestCase
{
    use DatabaseTransactions, \Tests\Support\CreatesPaidMember, \Tests\Support\PromoIntegrityFixtures;

    private function admin(): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
    }
    private function eligible(Pelanggan $customer): bool
    {
        return app(ActiveMemberCustomerService::class)->query()->whereKey($customer->id_pelanggan)->exists();
    }
    private function input(Pelanggan $customer): array
    {
        return ['id_pelanggan' => $customer->id_pelanggan, 'id_mobil' => $this->stage4Car()->id_mobil,
            'nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789',
            'alamat_pembeli' => 'Alamat QA', 'syarat_disetujui' => '1'];
    }
    private function snapshot(): array
    {
        return collect(['users', 'pelanggan', 'memberships', 'pesanan_mobil_member', 'penjualan_mobil', 'transaksi',
            'pembayaran', 'pembayaran_gateway', 'promo_redemptions', 'kendaraan_pelanggan', 'serah_terima_pesanan_mobil'])
            ->mapWithKeys(fn ($t) => [$t => DB::table($t)->count()])->all();
    }

    public function test_picker_shows_real_member_id_and_only_eligible_customer_options(): void
    {
        $active = $this->newPaidMember('Member Sah QA');
        $pending = $this->newPaidMember('Demo Pending QA'); $pending->membership->update(['status' => 'pending_payment']);
        $admin = $this->newPaidMember('Admin QA'); $admin->user->update(['id_role' => $this->admin()->id_role]);
        $response = $this->actingAs($this->admin())->get(route('admin.pesanan-mobil.index'))->assertOk();
        $customers = $response->viewData('orderCustomers');
        $this->assertTrue($customers->contains('id_pelanggan', $active->id_pelanggan));
        $this->assertFalse($customers->contains('id_pelanggan', $pending->id_pelanggan));
        $this->assertFalse($customers->contains('id_pelanggan', $admin->id_pelanggan));
        $response->assertSee('Pilih Member Aktif')->assertSee($active->membership->member_id)
            ->assertSee('role="combobox"', false)->assertSee('role="listbox"', false);
    }

    public function test_same_name_is_not_identity_and_multiple_receipts_do_not_duplicate_options(): void
    {
        $first = $this->newPaidMember('Nama Sama QA'); $second = $this->newPaidMember('Nama Sama QA');
        $event = $first->membership->gatewayPayment->events()->firstOrFail()->replicate();
        $event->forceFill(['event_key' => 'QA-'.bin2hex(random_bytes(10))])->save();
        $customers = app(ActiveMemberCustomerService::class)->query()->whereIn('id_pelanggan', [$first->id_pelanggan, $second->id_pelanggan])->get();
        $this->assertCount(2, $customers); $this->assertSame(2, $customers->pluck('id_user')->unique()->count());
        $html = $this->actingAs($this->admin())->get(route('admin.pesanan-mobil.index'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'data-id="'.$first->id_pelanggan.'"'));
        $this->assertSame(1, substr_count($html, 'data-id="'.$second->id_pelanggan.'"'));
    }

    public function test_active_flag_without_payment_evidence_is_not_sufficient(): void
    {
        $customer = $this->newPaidMember();
        $customer->membership->update(['gateway_payment_id' => null]);
        $this->assertFalse($this->eligible($customer));
    }

    public function test_legacy_paid_activation_matches_p0_active_linked_receipt_fallback(): void
    {
        $customer = $this->newPaidMember();
        $customer->membership->gatewayPayment->update(['processed_at' => null]);
        $this->assertTrue($this->eligible($customer));
        $customer->membership->gatewayPayment->events()->delete();
        $this->assertFalse($this->eligible($customer));
    }

    public function test_account_membership_or_customer_revocation_removes_eligibility(): void
    {
        $customer = $this->newPaidMember();
        foreach ([['users', 'id_user', $customer->id_user, 'nonaktif', 'aktif'],
            ['pelanggan', 'id_pelanggan', $customer->id_pelanggan, 'nonaktif', 'aktif'],
            ['memberships', 'id_membership', $customer->membership->id_membership, 'cancelled', 'active']] as [$table,$key,$id,$inactive,$active]) {
            DB::table($table)->where($key,$id)->update(['status'=>$inactive]);
            $this->assertFalse($this->eligible($customer));
            DB::table($table)->where($key,$id)->update(['status'=>$active]);
            $this->assertTrue($this->eligible($customer));
        }
        $customer->membership->update(['cancelled_at' => now()]);
        $this->assertFalse($this->eligible($customer));
    }

    public function test_gateway_customer_binding_amount_and_failed_states_are_checked(): void
    {
        $customer = $this->newPaidMember(); $gateway = $customer->membership->gatewayPayment;
        foreach ([['initiated_by', null], ['gross_amount', 1], ['status_provider', 'pending'],
            ['status_provider', 'requires_review'], ['status_provider', 'refund'], ['order_id', 'OTHER-ORDER']] as [$field,$invalid]) {
            $old = $gateway->$field; $gateway->forceFill([$field => $invalid])->save();
            $this->assertFalse($this->eligible($customer), $field);
            $gateway->forceFill([$field => $old])->save();
        }
    }

    public function test_orphan_or_wrong_membership_receipt_does_not_map_to_another_user(): void
    {
        $customer = $this->newPaidMember(); $other = $this->newPaidMember();
        $otherGateway = $other->membership->gateway_payment_id;
        $other->membership->update(['gateway_payment_id' => null]);
        $customer->membership->update(['gateway_payment_id' => $otherGateway]);
        $this->assertFalse($this->eligible($customer));
        $other->update(['id_user' => null]);
        $this->assertFalse($this->eligible($other));
    }

    public function test_backend_rechecks_revoked_membership_without_mutating_business_records(): void
    {
        $customer = $this->newPaidMember(); $input = $this->input($customer);
        $this->actingAs($this->admin())->get(route('admin.pesanan-mobil.index'))->assertOk();
        $customer->membership->update(['status' => 'cancelled']);
        $before = $this->snapshot();
        $this->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasErrors('order', null, 'createOrder');
        $this->assertSame($before, $this->snapshot());
        $this->assertDatabaseHas('mobil', ['id_mobil' => $input['id_mobil'], 'status' => 'tersedia']);
    }

    public function test_identity_injection_is_rejected_and_valid_order_reuses_customer(): void
    {
        $customer = $this->newPaidMember(); $input = $this->input($customer);
        $before = DB::table('pelanggan')->count();
        $this->actingAs($this->admin())->post(route('admin.pesanan-mobil.store'), $input + ['id_user' => $this->admin()->id_user])
            ->assertSessionHasErrors('id_user', null, 'createOrder');
        $this->post(route('admin.pesanan-mobil.store'), $input)->assertSessionHasNoErrors();
        $this->assertSame($before, DB::table('pelanggan')->count());
        $this->assertDatabaseHas('pesanan_mobil_member', ['id_pelanggan' => $customer->id_pelanggan, 'id_mobil' => $input['id_mobil'], 'status' => 'menunggu_verifikasi', 'id_transaksi' => null, 'id_penjualan' => null]);
    }

    public function test_newly_paid_member_appears_on_next_query_without_cached_stale_identity(): void
    {
        $customer = $this->newPaidMember(); $customer->membership->update(['status' => 'pending_payment']);
        $this->assertFalse($this->eligible($customer));
        $this->activatePaidMember($customer->fresh());
        $this->assertTrue($this->eligible($customer));
    }

    public function test_direct_sales_keeps_non_member_customer_and_historical_records_intact(): void
    {
        $customer = $this->newPaidMember(); $customer->membership->update(['status' => 'pending_payment']);
        $before = $this->snapshot();
        $response = $this->actingAs($this->admin())->get(route('admin.penjualan-mobil.create'))->assertOk();
        $response->assertDontSee('id="sale-create-form"', false);
        $this->get(route('admin.pesanan-mobil.index'))->assertOk();
        $this->assertSame($before, $this->snapshot());
        $car = $this->stage4Car();
        $this->post(route('admin.penjualan-mobil.store'), array_replace($this->stage4SaleInput($car), [
            'id_pelanggan' => $customer->id_pelanggan, 'submission_key' => (string) \Illuminate\Support\Str::uuid(),
        ]))->assertSessionHasErrors('id_pelanggan');
        $this->assertSame($before, $this->snapshot());
    }
}
