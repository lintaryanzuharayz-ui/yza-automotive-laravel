<?php

namespace Tests\Feature;

use App\Models\{PoinMember, RiwayatPoin, Transaksi, User};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinalSystemAuditTest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail(); }
    public function test_role_isolation_for_final_member_features(): void
    {
        $this->actingAs($this->user('admin'))->get('/pelanggan/member')->assertForbidden();
        $this->actingAs($this->user('petugas'))->get('/pelanggan/poin')->assertForbidden();
        $this->actingAs($this->user('pelanggan'))->get('/admin/promo-member')->assertForbidden();
    }
    public function test_member_center_empty_and_feature_routes_boot(): void
    {
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/member')->assertOk()->assertSee('YZA Points');
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/promo')->assertOk();
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/pengingat-servis')->assertOk();
    }
    public function test_financial_and_points_tables_are_present_without_future_redemption_tables(): void
    {
        foreach (['pembayaran','pembayaran_gateway','event_pembayaran_gateway','poin_member','riwayat_poin','promo_member'] as $table) $this->assertTrue(Schema::hasTable($table));
        foreach (['service_reminders','point_redemptions','voucher','midtrans_orders'] as $table) $this->assertFalse(Schema::hasTable($table));
    }
    public function test_points_balance_is_explainable_by_ledger(): void
    {
        $customerId = $this->user('pelanggan')->pelanggan->id_pelanggan;
        $earned = (int) RiwayatPoin::where('id_pelanggan',$customerId)->where('jenis','perolehan')->sum('perubahan_poin');
        $reversed = (int) RiwayatPoin::where('id_pelanggan',$customerId)->where('jenis','pembalikan')->sum('perubahan_poin');
        $this->assertSame($earned-$reversed,(int)PoinMember::where('id_pelanggan',$customerId)->value('saldo'));
    }
    public function test_no_negative_stock_and_no_orphan_payments(): void
    {
        $this->assertSame(0,(int)\DB::table('sparepart')->where('stok','<',0)->count());
        $this->assertSame(0,(int)\DB::table('pembayaran')->leftJoin('transaksi','transaksi.id_transaksi','=','pembayaran.id_transaksi')->whereNull('transaksi.id_transaksi')->count());
    }
    public function test_incoming_outgoing_payment_direction_isolated(): void
    {
        $this->assertSame(0,(int)Transaksi::where('jenis_transaksi','pembelian_mobil')->whereHas('pembayarans',fn($q)=>$q->where('arah','masuk'))->count());
        $this->assertSame(0,(int)Transaksi::where('jenis_transaksi','<>','pembelian_mobil')->whereHas('pembayarans',fn($q)=>$q->where('arah','keluar'))->count());
    }
    public function test_webhook_route_is_public_but_customer_gateway_route_requires_auth(): void
    {
        $this->postJson('/payment/midtrans/notification', [])->assertStatus(400);
        $this->post('/pelanggan/transaksi/1/midtrans')->assertRedirect('/login');
    }
}
