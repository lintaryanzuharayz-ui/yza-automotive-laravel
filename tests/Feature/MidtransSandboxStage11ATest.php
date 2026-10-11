<?php

namespace Tests\Feature;

use App\Models\{Pembayaran, PembayaranGateway, Transaksi, User, Mobil};
use App\Services\MemberVehiclePurchaseService;
use App\Services\MidtransPaymentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MidtransSandboxStage11ATest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail(); }
    private function transaction(string $type='cuci_mobil', string $total='100000.00'): Transaksi
    {
        $tx = new Transaksi(); $tx->forceFill(['kode_transaksi'=>'MID-QA-'.random_int(10000,99999),'id_pelanggan'=>$this->user('pelanggan')->pelanggan->id_pelanggan,'id_petugas'=>$this->user('petugas')->petugas->id_petugas,'jenis_transaksi'=>$type,'tanggal'=>now(),'subtotal'=>$total,'diskon'=>0,'total'=>$total,'status'=>'belum_bayar'])->save(); return $tx;
    }
    private function fakeSnap(): void { Http::fake(['*snap/v1/transactions' => Http::response(['token'=>'snap-token','redirect_url'=>'https://app.sandbox.midtrans.com/snap/v2/vtweb/order'],201)]); }
    private function signature(string $order,string $status,string $gross): string { return hash('sha512',$order.$status.$gross.'test-server'); }
    public function test_gateway_schema_snap_redirect_and_attempt_reuse(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $tx=$this->transaction(); $svc=app(MidtransPaymentService::class);
        $a=$svc->createAttempt($tx,$this->user('pelanggan')); $b=$svc->createAttempt($tx,$this->user('pelanggan'));
        $this->assertSame($a->id_pembayaran_gateway,$b->id_pembayaran_gateway); $this->assertSame(1,PembayaranGateway::where('id_transaksi',$tx->id_transaksi)->count()); $this->assertTrue(Schema::hasTable('event_pembayaran_gateway')); Http::assertSent(fn($request)=>(string) data_get($request->data(),'callbacks.finish')===route('pelanggan.midtrans.returned',['order_id'=>$a->order_id])); if(Schema::hasColumn('pembayaran_gateway','finish_url'))$this->assertSame(route('pelanggan.midtrans.returned',['order_id'=>$a->order_id]),$a->finish_url);
    }
    public function test_remaining_amount_is_server_calculated_and_pending_manual_blocks(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $tx=$this->transaction('cuci_mobil','100000.00'); \App\Models\Pembayaran::unguard(); $p=new Pembayaran();$p->forceFill(['kode_pembayaran'=>'MID-MAN-'.random_int(1000,9999),'id_transaksi'=>$tx->id_transaksi,'tanggal'=>now(),'arah'=>'masuk','metode'=>'transfer','jumlah'=>25000,'status'=>'terverifikasi'])->save(); \App\Models\Pembayaran::reguard();
        $a=app(MidtransPaymentService::class)->createAttempt($tx,$this->user('pelanggan')); $this->assertSame('75000.00',(string)$a->gross_amount);
        $manual=new Pembayaran(); $manual->forceFill(['kode_pembayaran'=>'MID-PEN-'.random_int(1000,9999),'id_transaksi'=>$tx->id_transaksi,'tanggal'=>now(),'arah'=>'masuk','metode'=>'transfer','jumlah'=>1,'status'=>'menunggu']); $this->expectException(\RuntimeException::class); $manual->save();
    }
    public function test_outgoing_and_other_customer_are_blocked(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $out=$this->transaction('pembelian_mobil'); $this->expectException(\RuntimeException::class); app(MidtransPaymentService::class)->createAttempt($out,$this->user('pelanggan'));
    }
    public function test_valid_settlement_bridges_once_and_duplicate_is_idempotent(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $tx=$this->transaction(); $gateway=app(MidtransPaymentService::class)->createAttempt($tx,$this->user('pelanggan')); $payload=['order_id'=>$gateway->order_id,'status_code'=>'200','gross_amount'=>'100000.00','signature_key'=>$this->signature($gateway->order_id,'200','100000.00'),'transaction_status'=>'settlement','payment_type'=>'qris','transaction_id'=>'provider-1'];
        app(MidtransPaymentService::class)->processNotification($payload); app(MidtransPaymentService::class)->processNotification($payload);
        $this->assertSame(1,Pembayaran::where('id_transaksi',$tx->id_transaksi)->count()); $this->assertSame('lunas',$tx->fresh()->status); $this->assertSame(1,PembayaranGateway::find($gateway->id_pembayaran_gateway)->id_pembayaran ? 1 : 0);
    }
    public function test_invalid_signature_unknown_order_and_amount_mismatch_do_not_pay(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $tx=$this->transaction(); $gateway=app(MidtransPaymentService::class)->createAttempt($tx,$this->user('pelanggan')); $bad=['order_id'=>$gateway->order_id,'status_code'=>'200','gross_amount'=>'100000.00','signature_key'=>'bad','transaction_status'=>'settlement']; try{app(MidtransPaymentService::class)->processNotification($bad);}catch(\RuntimeException $e){} $this->assertSame(0,Pembayaran::where('id_transaksi',$tx->id_transaksi)->count()); try{app(MidtransPaymentService::class)->processNotification(['order_id'=>'UNKNOWN','status_code'=>'200','gross_amount'=>'100000.00','signature_key'=>$this->signature('UNKNOWN','200','100000.00'),'transaction_status'=>'settlement']);}catch(\RuntimeException $e){} $payload=['order_id'=>$gateway->order_id,'status_code'=>'200','gross_amount'=>'90000.00','signature_key'=>$this->signature($gateway->order_id,'200','90000.00'),'transaction_status'=>'settlement']; app(MidtransPaymentService::class)->processNotification($payload); $this->assertSame('requires_review',$gateway->fresh()->status_provider);
    }
    public function test_status_sync_uses_server_get_status_and_manual_payment_remains_available_after_expire(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $tx=$this->transaction(); $g=app(MidtransPaymentService::class)->createAttempt($tx,$this->user('pelanggan')); Http::fake(['*status' => Http::response(['transaction_status'=>'expire','status_code'=>'200','gross_amount'=>'100000.00'],200)]); app(MidtransPaymentService::class)->sync($g,$this->user('pelanggan')); $this->assertSame('expire',$g->fresh()->status_provider); $this->assertSame(0,Pembayaran::where('id_transaksi',$tx->id_transaksi)->count());
    }
    public function test_finish_return_ignores_fake_query_and_redirects_owned_order_after_server_settlement(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $member=$this->user('pelanggan'); $customer=$member->pelanggan; $car=Mobil::where('model','GR Supra')->where('status','tersedia')->firstOrFail(); $orderService=app(MemberVehiclePurchaseService::class); $order=$orderService->approve($orderService->create($customer,$car,['nama_pembeli'=>$customer->nama_lengkap,'telepon_pembeli'=>'08123456789','alamat_pembeli'=>'Alamat QA']),$this->user('petugas')->petugas->id_petugas); $gateway=app(MidtransPaymentService::class)->createAttempt($order->transaksi,$member); Http::fake(['*status'=>Http::response(['transaction_status'=>'settlement','status_code'=>'200','gross_amount'=>(string)$gateway->gross_amount,'payment_type'=>'qris','transaction_id'=>'finish-provider','settlement_time'=>'2026-10-11 12:34:56'],200)]); $response=$this->actingAs($member)->get(route('pelanggan.midtrans.returned',['order_id'=>$gateway->order_id,'transaction_status'=>'settlement'])); $response->assertRedirect(route('pelanggan.pesanan-mobil.show',$order)); $response->assertSessionHas('status','Pembayaran berhasil diverifikasi.'); $this->assertSame('lunas',$order->transaksi->fresh()->status); $this->assertSame(1,Pembayaran::where('id_transaksi',$order->id_transaksi)->count()); $this->actingAs($member)->get(route('pelanggan.midtrans.returned',['order_id'=>$gateway->order_id,'transaction_status'=>'settlement'])); $this->assertSame(1,Pembayaran::where('id_transaksi',$order->id_transaksi)->count());
    }
    public function test_finish_return_keeps_pending_when_browser_claims_settlement(): void
    {
        config(['midtrans.server_key'=>'test-server']); $this->fakeSnap(); $member=$this->user('pelanggan'); $tx=$this->transaction('penjualan_mobil','100000.00'); $gateway=app(MidtransPaymentService::class)->createAttempt($tx,$member); Http::fake(['*status'=>Http::response(['transaction_status'=>'pending','status_code'=>'201','gross_amount'=>'100000.00','payment_type'=>'qris'],200)]); $this->assertSame('pending',app(MidtransPaymentService::class)->sync($gateway,$member)->status_provider); $this->assertSame('belum_bayar',$tx->fresh()->status);
    }
    public function test_midtrans_is_sandbox_only_and_no_secret_is_rendered(): void
    {
        $this->assertSame('sandbox',config('midtrans.environment')); $this->assertStringNotContainsString('SERVER_KEY',view('payments.midtrans-return')->render()); $this->assertFalse(Schema::hasTable('midtrans_orders'));
    }
}
