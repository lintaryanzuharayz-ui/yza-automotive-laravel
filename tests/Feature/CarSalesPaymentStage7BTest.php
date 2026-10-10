<?php
namespace Tests\Feature;

use App\Models\{Mobil,User};
use App\Services\{BookingMobilService,PaymentService,PenjualanMobilService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CarSalesPaymentStage7BTest extends TestCase
{
 use DatabaseTransactions;
 private function user(string $role):User{return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail();}
 private function sale(array $extra=[]){$car=Mobil::where('status','tersedia')->firstOrFail();$customer=$this->user('pelanggan')->pelanggan;return app(PenjualanMobilService::class)->create(array_merge(['id_pelanggan'=>$customer->id_pelanggan,'id_mobil'=>$car->id_mobil,'diskon'=>0,'dp'=>0,'metode_pembelian'=>'tunai','catatan'=>'QA'], $extra),$this->user('petugas')->petugas->id_petugas);}
 private function pay($sale,string $amount,string $method='tunai'){return app(PaymentService::class)->create($sale->id_transaksi,$this->user('petugas')->petugas->id_petugas,$amount,$method,null,'QA');}

 public function test_historical_car_sale_payments_are_related():void{$this->assertSame(2,DB::table('pembayaran')->join('transaksi','transaksi.id_transaksi','=','pembayaran.id_transaksi')->where('transaksi.jenis_transaksi','penjualan_mobil')->count());}
 public function test_cash_completes_sale_car_and_direct_sale_without_booking():void{$sale=$this->sale();$this->pay($sale,(string)$sale->total_akhir);$this->assertSame('lunas',$sale->transaksi->fresh()->status);$this->assertSame('selesai',$sale->fresh()->status);$this->assertSame('terjual',$sale->mobil->fresh()->status);$this->assertNull($sale->id_booking);}
 public function test_booking_sale_completes_only_its_booking():void{$car=Mobil::where('status','tersedia')->firstOrFail();$customer=$this->user('pelanggan')->pelanggan;$actor=$this->user('petugas')->petugas->id_petugas;$booking=app(BookingMobilService::class)->create($customer->id_pelanggan,$car->id_mobil,'QA');app(BookingMobilService::class)->confirm($booking->id_booking,$actor);$sale=app(PenjualanMobilService::class)->create(['id_pelanggan'=>$customer->id_pelanggan,'id_mobil'=>$car->id_mobil,'id_booking'=>$booking->id_booking,'diskon'=>0,'dp'=>0,'metode_pembelian'=>'tunai'],$actor);$this->pay($sale,(string)$sale->total_akhir);$this->assertSame('selesai',$booking->fresh()->status);$this->assertSame('terjual',$car->fresh()->status);}
 public function test_non_cash_is_pending_and_not_counted():void{$sale=$this->sale();$p=$this->pay($sale,'100000','transfer');$this->assertSame('menunggu',$p->status);$this->assertSame('belum_bayar',$sale->transaksi->fresh()->status);$this->assertSame('proses',$sale->fresh()->status);$this->assertSame('dibooking',$sale->mobil->fresh()->status);}
 public function test_all_non_cash_methods_pending():void{foreach(['transfer','kredit','lainnya'] as $method){$sale=$this->sale();$this->assertSame('menunggu',$this->pay($sale,'100000',$method)->status);}}
 public function test_verify_transfer_moves_to_dp_without_completion():void{$sale=$this->sale();$p=$this->pay($sale,'100000','transfer');app(PaymentService::class)->verify($p->id_pembayaran,$this->user('petugas')->petugas->id_petugas);$this->assertSame('dp',$sale->transaksi->fresh()->status);$this->assertSame('proses',$sale->fresh()->status);$this->assertSame('dibooking',$sale->mobil->fresh()->status);}
 public function test_reject_keeps_history():void{$sale=$this->sale();$p=$this->pay($sale,'100000','transfer');app(PaymentService::class)->reject($p->id_pembayaran,$this->user('petugas')->petugas->id_petugas);$this->assertSame('ditolak',$p->fresh()->status);$this->assertSame('belum_bayar',$sale->transaksi->fresh()->status);}
 public function test_multiple_payments_and_overpayment_are_server_side():void{$sale=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$svc->create($sale->id_transaksi,'100000000','100000000','tunai',null,null);$this->assertSame('dp',$sale->transaksi->fresh()->status);$this->expectException(RuntimeException::class);$svc->create($sale->id_transaksi,$actor,(string)((float)$sale->total_akhir-999999),'tunai',null,null);}
 public function test_payment_after_lunas_and_invalid_replays_are_blocked():void{$sale=$this->sale();$p=$this->pay($sale,(string)$sale->total_akhir);$this->expectException(RuntimeException::class);$this->pay($sale,'1');}
 public function test_two_final_payment_attempts_cannot_both_overpay():void{$sale=$this->sale();$svc=app(PaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$remaining=(float)$sale->total_akhir-100000000;$svc->create($sale->id_transaksi,$actor,'100000000','tunai',null,null);$this->expectException(RuntimeException::class);$svc->create($sale->id_transaksi,$actor,(string)($remaining+1),'tunai',null,null);}
 public function test_completion_rollback_keeps_payment_and_unit_consistent():void{$sale=$this->sale();DB::table('mobil')->where('id_mobil',$sale->id_mobil)->update(['status'=>'terjual']);$before=DB::table('pembayaran')->count();$this->expectException(RuntimeException::class);try{$this->pay($sale,(string)$sale->total_akhir);}finally{$this->assertSame($before,DB::table('pembayaran')->count());$this->assertSame('belum_bayar',$sale->transaksi->fresh()->status);$this->assertSame('proses',$sale->fresh()->status);}}
 public function test_stock_isolation_and_no_gateway():void{$sale=$this->sale();$stock=DB::table('sparepart')->sum('stok');$this->pay($sale,'100000','transfer');$this->assertSame((string)$stock,(string)DB::table('sparepart')->sum('stok'));$this->assertFalse(DB::getSchemaBuilder()->hasTable('midtrans_orders'));}
 public function test_customer_cannot_use_payment_or_invoice_operations():void{$customer=$this->user('pelanggan');$this->actingAs($customer)->get('/admin/penjualan-mobil')->assertForbidden();$this->actingAs($customer)->post('/admin/transaksi-mobil/1/pembayaran')->assertForbidden();}
}
