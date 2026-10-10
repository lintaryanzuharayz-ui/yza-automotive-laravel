<?php
namespace Tests\Feature;

use App\Models\{Pembayaran,PenjualanSparepart,Sparepart,Transaksi,User};
use App\Services\{PaymentService,PenjualanSparepartService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class SparepartSalesPaymentStage6BTest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail(); }
    private function sale(?int $customer=null): array { $actor=$this->user('petugas')->petugas->id_petugas;$svc=app(PenjualanSparepartService::class);$sale=$svc->createDraft($customer,$actor,'payment QA');$part=Sparepart::where('status','aktif')->firstOrFail();$svc->addDetail($sale->id_penjualan_sparepart,$part->id_sparepart,1);$tx=$svc->finalize($sale->id_penjualan_sparepart,$actor);return [$sale->fresh(),$tx]; }
    private function payment(Transaksi $tx,string $amount,string $method='tunai'): Pembayaran { return app(PaymentService::class)->create($tx->id_transaksi,$this->user('petugas')->petugas->id_petugas,$amount,$method,null,'payment QA'); }

    public function test_historical_sparepart_payment_relation_is_valid(): void
    {
        $this->assertSame(2,Pembayaran::whereIn('id_transaksi',Transaksi::where('jenis_transaksi','penjualan_sparepart')->pluck('id_transaksi'))->count());
        $this->assertSame('penjualan_sparepart',Transaksi::find(2)->jenis_transaksi); $this->assertTrue(Transaksi::find(2)->pembayarans()->exists());
    }

    public function test_cash_is_verified_and_lunas_without_changing_stock(): void
    {
        $history=DB::table('riwayat_stok')->count();[$sale,$tx]=$this->sale();$stock=(int)Sparepart::first()->stok;$this->payment($tx,(string)$tx->total,'tunai');$this->assertSame('terverifikasi',$tx->fresh()->pembayarans()->latest('id_pembayaran')->first()->status);$this->assertSame('lunas',$tx->fresh()->status);$this->assertSame('selesai',$sale->fresh()->status);$this->assertSame($stock,(int)Sparepart::first()->fresh()->stok);$this->assertSame($history+1,DB::table('riwayat_stok')->count());
    }

    public function test_non_cash_methods_are_pending_and_pending_is_not_paid(): void
    {
        [$sale,$tx]=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;foreach(['transfer','kredit','lainnya'] as $method){$p=$svc->create($tx->id_transaksi,$actor,'1',$method,null,null);$this->assertSame('menunggu',$p->status);}$this->assertSame('belum_bayar',$tx->fresh()->status);$this->assertSame('selesai',$sale->fresh()->status);$this->assertSame('0.00',$svc->summary($tx->id_transaksi)['paid']);
    }

    public function test_verify_and_reject_follow_native_history_rules(): void
    {
        [, $tx]=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$p=$svc->create($tx->id_transaksi,$actor,(string)$tx->total,'transfer',null,null);$svc->verify($p->id_pembayaran,$actor);$this->assertSame('lunas',$tx->fresh()->status);$this->expectException(RuntimeException::class);$svc->verify($p->id_pembayaran,$actor);
    }

    public function test_rejected_payment_is_kept_and_not_counted(): void
    {
        [, $tx]=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$p=$svc->create($tx->id_transaksi,$actor,'10','transfer',null,null);$svc->reject($p->id_pembayaran,$actor);$this->assertSame('ditolak',$p->fresh()->status);$this->assertSame('0.00',$svc->summary($tx->id_transaksi)['paid']);$this->assertSame('belum_bayar',$tx->fresh()->status);
    }

    public function test_multiple_payment_reaches_dp_then_lunas_and_sale_was_already_native_complete(): void
    {
        [$sale,$tx]=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$svc->create($tx->id_transaksi,$actor,'10000','tunai',null,null);$this->assertSame('dp',$tx->fresh()->status);$this->assertSame('selesai',$sale->fresh()->status);$svc->create($tx->id_transaksi,$actor,(string)((float)$tx->total-10000),'tunai',null,null);$this->assertSame('lunas',$tx->fresh()->status);$this->assertSame('selesai',$sale->fresh()->status);
    }

    public function test_zero_negative_and_overpayment_are_rejected(): void
    {
        [, $tx]=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;foreach(['0','-1',(string)((float)$tx->total+1)] as $amount){try{$svc->create($tx->id_transaksi,$actor,$amount,'tunai',null,null);$this->fail('Invalid payment accepted');}catch(RuntimeException $e){$this->assertTrue(true);}}$this->assertSame(0,Pembayaran::where('id_transaksi',$tx->id_transaksi)->count());
    }

    public function test_competing_payments_cannot_overpay_and_stock_isolation_holds(): void
    {
        $history=DB::table('riwayat_stok')->count();[, $tx]=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$stock=(int)Sparepart::first()->stok;$svc->create($tx->id_transaksi,$actor,(string)($tx->total/2),'tunai',null,null);$this->expectException(RuntimeException::class);try{$svc->create($tx->id_transaksi,$actor,(string)$tx->total,'tunai',null,null);}finally{$this->assertSame('dp',$tx->fresh()->status);$this->assertSame($stock,(int)Sparepart::first()->fresh()->stok);$this->assertSame($history+1,DB::table('riwayat_stok')->count());}
    }

    public function test_walk_in_invoice_is_staff_only_and_customer_invoice_is_owned(): void
    {
        [$walkin,$tx]=$this->sale();$this->actingAs($this->user('admin'))->get('/admin/invoice/sparepart/'.$tx->id_transaksi)->assertOk()->assertSee('Walk-in');$customer=$this->user('pelanggan');$this->actingAs($customer)->get('/pelanggan/invoice/sparepart/'.$tx->id_transaksi)->assertForbidden();[$owned,$ownedTx]=$this->sale($customer->pelanggan->id_pelanggan);$this->actingAs($customer)->get('/pelanggan/invoice/sparepart/'.$ownedTx->id_transaksi)->assertOk()->assertSee($owned->kode_penjualan);$other=User::whereHas('role',fn($q)=>$q->where('nama_role','pelanggan'))->where('id_user','<>',$customer->id_user)->first();if($other)$this->actingAs($other)->get('/pelanggan/invoice/sparepart/'.$ownedTx->id_transaksi)->assertForbidden();
    }

    public function test_payment_routes_reject_unsupported_transaction_and_no_gateway_exists(): void
    {
        $serviceTx=Transaksi::where('jenis_transaksi','servis')->firstOrFail();$this->expectException(RuntimeException::class);try{app(PaymentService::class)->create($serviceTx->id_transaksi,$this->user('petugas')->petugas->id_petugas,'1','tunai',null,null);}finally{$this->assertFalse(Schema::hasTable('midtrans_orders'));}
    }
}
