<?php

namespace Tests\Feature;

use App\Models\{Pelanggan, PoinMember, RiwayatPoin, Transaksi, User};
use App\Services\YzaPointService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class YzaPointsStage10BTest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail(); }
    public function test_points_schema_and_rate_are_present(): void
    {
        $this->assertTrue(Schema::hasTable('poin_member'));
        $this->assertTrue(Schema::hasTable('riwayat_poin'));
        $this->assertSame(10000, app(YzaPointService::class)->rate());
        $this->assertSame(0, app(YzaPointService::class)->calculate('9999'));
        $this->assertSame(1, app(YzaPointService::class)->calculate('10000'));
        $this->assertSame(9, app(YzaPointService::class)->calculate('99999'));
        $this->assertSame(10, app(YzaPointService::class)->calculate('100000'));
    }

    public function test_one_account_per_member_and_registration_account_is_safe(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $first = app(YzaPointService::class)->ensureAccount($customer);
        $second = app(YzaPointService::class)->ensureAccount($customer);
        $this->assertSame($first->id_poin_member, $second->id_poin_member);
        $this->assertSame(1, PoinMember::where('id_pelanggan', $customer->id_pelanggan)->count());
    }

    public function test_historical_backfill_is_idempotent_and_excludes_outgoing(): void
    {
        $this->artisan('yza:points-backfill')->assertExitCode(0);
        $count = RiwayatPoin::count();
        $balance = PoinMember::where('id_pelanggan', 1)->value('saldo');
        $this->artisan('yza:points-backfill')->assertExitCode(0);
        $this->assertSame($count, RiwayatPoin::count());
        $this->assertSame($balance, PoinMember::where('id_pelanggan', 1)->value('saldo'));
        $this->assertDatabaseMissing('riwayat_poin', ['id_transaksi' => 6]);
    }

    public function test_award_is_idempotent_and_uses_final_transaction_total(): void
    {
        $tx = Transaksi::findOrFail(1);
        $before = RiwayatPoin::where('id_transaksi', $tx->id_transaksi)->count();
        $this->assertSame(0, app(YzaPointService::class)->awardIfEligible($tx));
        $this->assertSame(0, app(YzaPointService::class)->awardIfEligible($tx));
        $this->assertSame($before, RiwayatPoin::where('id_transaksi', $tx->id_transaksi)->count());
        $this->assertSame(24000, (int) RiwayatPoin::where('id_transaksi', $tx->id_transaksi)->value('perubahan_poin'));
    }

    public function test_member_center_and_history_are_customer_scoped(): void
    {
        $customer = $this->user('pelanggan');
        $this->actingAs($customer)->get('/pelanggan/member')->assertOk()->assertSee('YZA Points');
        $this->actingAs($customer)->get('/pelanggan/poin')->assertOk()->assertSee('Riwayat Poin');
        $this->actingAs($this->user('admin'))->get('/pelanggan/poin')->assertForbidden();
        $this->actingAs($this->user('petugas'))->get('/pelanggan/poin')->assertForbidden();
        $this->assertStringNotContainsString('redeem', strtolower($this->actingAs($customer)->get('/pelanggan/poin')->getContent()));
    }

    public function test_legacy_customer_without_user_is_not_a_member(): void
    {
        $customer = Pelanggan::whereNull('id_user')->first();
        if (!$customer) {
            $customer = Pelanggan::create([
                'id_user' => null,
                'kode_pelanggan' => 'TEST-LEGACY-NO-USER',
                'nama_lengkap' => 'Synthetic Legacy Customer',
                'jenis_kelamin' => 'L',
                'no_hp' => '080000000008',
                'alamat' => 'Testing fixture only',
                'tanggal_lahir' => '1990-08-08',
                'status' => 'aktif',
            ]);
        }
        $this->assertNull(app(YzaPointService::class)->ensureAccount($customer));
        $this->assertDatabaseMissing('poin_member', ['id_pelanggan' => $customer->id_pelanggan]);
    }
}
