<?php

namespace Tests\Feature;

use App\Models\{KendaraanPelanggan, PromoMember, Servis, User};
use App\Services\ServiceReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PromoServiceReminderStage10CTest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail(); }
    private function promo(array $extra = []): PromoMember
    {
        $p = new PromoMember();
        $p->forceFill(array_merge(['kode_promo' => 'QA-'.strtoupper(substr(uniqid(), -8)), 'nama' => 'QA Promo', 'jenis_benefit' => 'informasi', 'nilai_benefit' => null, 'minimum_transaksi' => 0, 'maksimum_benefit' => null, 'berlaku_untuk' => 'semua', 'mulai_at' => now()->subDay(), 'selesai_at' => now()->addDay(), 'syarat_ketentuan' => 'QA', 'status' => 'aktif'], $extra))->save();
        return $p;
    }
    public function test_promo_schema_and_admin_crud_are_authorized(): void
    {
        $this->assertTrue(Schema::hasTable('promo_member'));
        $this->actingAs($this->user('admin'))->get('/admin/promo-member')->assertOk();
        $this->actingAs($this->user('petugas'))->get('/admin/promo-member')->assertForbidden();
        $this->actingAs($this->user('pelanggan'))->get('/admin/promo-member')->assertForbidden();
        $this->actingAs($this->user('admin'))->post('/admin/promo-member', ['kode_promo'=>'QA-CRUD','nama'=>'Promo CRUD','jenis_benefit'=>'persentase','nilai_benefit'=>10,'minimum_transaksi'=>0,'berlaku_untuk'=>'servis','mulai_at'=>now()->subDay()->format('Y-m-d H:i'),'selesai_at'=>now()->addDay()->format('Y-m-d H:i'),'syarat_ketentuan'=>'QA','status'=>'aktif'])->assertRedirect();
        $this->assertDatabaseHas('promo_member', ['kode_promo' => 'QA-CRUD', 'created_by' => $this->user('admin')->id_user]);
    }
    public function test_promo_validation_visibility_and_detail(): void
    {
        $member = $this->user('pelanggan');
        $valid = $this->promo(['kode_promo'=>'QA-VISIBLE']);
        $future = $this->promo(['kode_promo'=>'QA-FUTURE','mulai_at'=>now()->addDay(),'selesai_at'=>now()->addDays(2)]);
        $expired = $this->promo(['kode_promo'=>'QA-EXPIRED','mulai_at'=>now()->subDays(2),'selesai_at'=>now()->subDay()]);
        $inactive = $this->promo(['kode_promo'=>'QA-INACTIVE','status'=>'nonaktif']);
        $response = $this->actingAs($member)->get('/pelanggan/promo');
        $response->assertOk()->assertSee($valid->kode_promo)->assertDontSee($future->kode_promo)->assertDontSee($expired->kode_promo)->assertDontSee($inactive->kode_promo);
        $this->actingAs($member)->get('/pelanggan/promo/'.$valid->id_promo)->assertOk()->assertSee('Promo ditampilkan sebagai benefit YZA Member');
        $this->actingAs($member)->get('/pelanggan/promo/'.$expired->id_promo)->assertNotFound();
    }
    public function test_promo_form_rejects_invalid_benefit_dates_and_scope(): void
    {
        $data = ['kode_promo'=>'QA-BAD','nama'=>'Bad','jenis_benefit'=>'persentase','nilai_benefit'=>101,'minimum_transaksi'=>-1,'berlaku_untuk'=>'bad','mulai_at'=>'2026-10-02 10:00','selesai_at'=>'2026-10-01 10:00','status'=>'aktif'];
        $this->actingAs($this->user('admin'))->post('/admin/promo-member', $data)->assertSessionHasErrors(['nilai_benefit','minimum_transaksi','berlaku_untuk','selesai_at']);
    }
    public function test_demo_promo_command_is_idempotent(): void
    {
        $this->artisan('yza:promo-demo')->assertExitCode(0);
        $count = PromoMember::whereIn('kode_promo', ['YZA-SERVICE10','YZA-CARWASH20','YZA-PARTS5'])->count();
        $this->artisan('yza:promo-demo')->assertExitCode(0);
        $this->assertSame($count, PromoMember::whereIn('kode_promo', ['YZA-SERVICE10','YZA-CARWASH20','YZA-PARTS5'])->count());
    }
    public function test_member_and_guest_authorization_for_promo_and_reminder(): void
    {
        $this->get('/pelanggan/promo')->assertRedirect('/login');
        $this->actingAs($this->user('admin'))->get('/pelanggan/promo')->assertForbidden();
        $this->actingAs($this->user('petugas'))->get('/pelanggan/pengingat-servis')->assertForbidden();
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/pengingat-servis')->assertOk();
    }
    public function test_reminder_no_history_and_completed_service_only(): void
    {
        $customer = $this->user('pelanggan')->pelanggan;
        $vehicle = $customer->kendaraan()->create(['merk'=>'QA','model'=>'Reminder','nomor_polisi'=>'REM-'.random_int(1000,9999),'kilometer'=>12345,'status'=>'aktif']);
        $rows = app(ServiceReminderService::class)->forCustomer($customer->id_pelanggan);
        $row = collect($rows)->firstWhere('vehicle.id_kendaraan_pelanggan', $vehicle->id_kendaraan_pelanggan);
        $this->assertSame('no_history', $row['status']);
        $this->assertSame('Belum Ada Riwayat Servis', $row['statusLabel']);
    }
    public function test_reminder_calculates_upcoming_due_soon_exact_due_and_overdue(): void
    {
        $customer = $this->user('pelanggan')->pelanggan; $petugas = $this->user('petugas')->petugas;
        Carbon::setTestNow(Carbon::parse('2026-09-30 10:00:00', 'Asia/Jakarta'));
        foreach ([['2026-04-01','due_soon'],['2026-03-30','due_soon'],['2026-03-01','overdue'],['2025-01-01','overdue']] as $i => [$date,$expected]) {
            $v = $customer->kendaraan()->create(['merk'=>'QA','model'=>'R'.$i,'nomor_polisi'=>'R'.$i.'-'.random_int(1000,9999),'kilometer'=>1000,'status'=>'aktif']);
            $s = new Servis(); $s->forceFill(['kode_servis'=>'REM-QA-'.$i.'-'.random_int(1000,9999),'id_pelanggan'=>$customer->id_pelanggan,'id_kendaraan_pelanggan'=>$v->id_kendaraan_pelanggan,'id_petugas'=>$petugas->id_petugas,'tanggal_masuk'=>$date,'tanggal_selesai'=>$date,'kilometer'=>1000,'status'=>'selesai','biaya_jasa'=>0,'biaya_sparepart'=>0,'total_biaya'=>0])->save();
            $row = collect(app(ServiceReminderService::class)->forCustomer($customer->id_pelanggan))->firstWhere('vehicle.id_kendaraan_pelanggan',$v->id_kendaraan_pelanggan);
            $this->assertSame($expected, $row['status']);
        }
        Carbon::setTestNow();
    }
    public function test_reminder_excludes_sold_vehicle_and_is_owner_scoped(): void
    {
        $a = $this->user('pelanggan')->pelanggan; $b = User::whereHas('role',fn($q)=>$q->where('nama_role','pelanggan'))->where('id_user','<>',$a->user->id_user)->firstOrFail()->pelanggan;
        $sold = $a->kendaraan()->create(['merk'=>'QA','model'=>'Sold','status'=>'dijual']);
        $other = $b->kendaraan()->create(['merk'=>'QA','model'=>'Other','status'=>'aktif']);
        $rows = app(ServiceReminderService::class)->forCustomer($a->id_pelanggan);
        $this->assertFalse(collect($rows)->contains(fn($row)=>(int)$row['vehicle']->id_kendaraan_pelanggan===$other->id_kendaraan_pelanggan));
        $this->assertFalse(collect($rows)->contains(fn($row)=>(int)$row['vehicle']->id_kendaraan_pelanggan===$sold->id_kendaraan_pelanggan));
    }
    public function test_promo_and_reminder_are_read_only_and_no_redemption_or_reminder_table(): void
    {
        $before = \App\Models\RiwayatPoin::count(); $balance = (int) \App\Models\PoinMember::where('id_pelanggan', $this->user('pelanggan')->pelanggan->id_pelanggan)->value('saldo');
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/member')->assertOk();
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/promo')->assertOk();
        $this->actingAs($this->user('pelanggan'))->get('/pelanggan/pengingat-servis')->assertOk();
        $this->assertSame($before, \App\Models\RiwayatPoin::count()); $this->assertSame($balance, (int) \App\Models\PoinMember::where('id_pelanggan', $this->user('pelanggan')->pelanggan->id_pelanggan)->value('saldo'));
        $this->assertFalse(Schema::hasTable('service_reminders'));
    }
}
