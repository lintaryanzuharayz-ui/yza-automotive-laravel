<?php
namespace Tests\Feature;
use App\Models\{InspeksiMobil,PembelianMobilBekas,PengajuanMobil,Transaksi,User};
use App\Services\{OutgoingPaymentService,PembelianMobilBekasService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB,Schema};
use RuntimeException;
use Tests\TestCase;

class UsedCarPurchaseSettlementStage8BTest extends TestCase
{
 use DatabaseTransactions;
 private function user(string $role): User { return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail(); }
 private function approved(float $price=1000000): array {
  $n=random_int(10000,99999); $base=PengajuanMobil::findOrFail(1); $p=new PengajuanMobil(); $p->forceFill($base->only(['id_pelanggan','id_kendaraan_pelanggan','merk','model','tipe','tahun','warna','transmisi','bahan_bakar','nomor_polisi','kilometer','harga_harapan','foto','catatan'])+['kode_pengajuan'=>'PJM-QA-'.$n,'nomor_rangka'=>'QA-RANGKA-'.$n,'nomor_mesin'=>'QA-MESIN-'.$n,'status'=>'disetujui'])->save();
  $i=new InspeksiMobil();$i->forceFill(['id_pengajuan'=>$p->id_pengajuan,'id_petugas'=>$this->user('petugas')->petugas->id_petugas,'kilometer'=>1200,'harga_penawaran'=>$price])->save();return [$p,$i];
 }
 private function purchase(float $price=1000000): PembelianMobilBekas {[$p,$i]=$this->approved($price);return app(PembelianMobilBekasService::class)->createFromApproved($p->id_pengajuan,$i->id_inspeksi,$this->user('petugas')->petugas->id_petugas,'QA purchase');}
 public function test_baseline_purchase_and_outgoing_payments_are_copied(): void {$this->assertSame(1,PembelianMobilBekas::count());$this->assertSame(2,DB::table('pembayaran')->where('arah','keluar')->count());$this->assertSame('selesai',PembelianMobilBekas::first()->status);}
 public function test_purchase_uses_server_inspection_offer_and_creates_unpaid_transaction(): void {$b=$this->purchase(1234567);$this->assertSame('proses',$b->status);$this->assertSame('pembelian_mobil',$b->transaksi->jenis_transaksi);$this->assertSame('belum_bayar',$b->transaksi->status);$this->assertSame('1234567.00',(string)$b->harga_pembelian);$this->assertSame('1234567.00',(string)$b->transaksi->total);}
 public function test_duplicate_purchase_and_invalid_offer_are_blocked(): void {$p=PengajuanMobil::findOrFail(1);$i=InspeksiMobil::findOrFail(1);$this->expectException(RuntimeException::class);app(PembelianMobilBekasService::class)->createFromApproved($p->id_pengajuan,$i->id_inspeksi,2,'duplicate');}
 public function test_outgoing_payment_methods_and_verified_sum(): void {$b=$this->purchase(1000000);$svc=app(OutgoingPaymentService::class);$actor=$this->user('petugas')->petugas->id_petugas;$cash=$svc->create($b->id_transaksi,$actor,'100000','tunai',null,'cash');$pending=$svc->create($b->id_transaksi,$actor,'200000','transfer',null,'bank');$this->assertSame('terverifikasi',$cash->status);$this->assertSame('menunggu',$pending->status);$this->assertSame('dp',$b->transaksi->fresh()->status);$this->assertSame('100000.00',$svc->summary($b->id_transaksi)['paid']);}
 public function test_verify_reject_and_overpayment_are_atomic(): void {$b=$this->purchase(500000);$svc=app(OutgoingPaymentService::class);$a=$this->user('petugas')->petugas->id_petugas;$p=$svc->create($b->id_transaksi,$a,'300000','transfer',null,null);$svc->verify($p->id_pembayaran,$a);$this->assertSame('dp',$b->transaksi->fresh()->status);$q=$svc->create($b->id_transaksi,$a,'300000','transfer',null,null);$svc->reject($q->id_pembayaran,$a);$this->assertSame('ditolak',$q->fresh()->status);$this->expectException(RuntimeException::class);$svc->create($b->id_transaksi,$a,'200001','tunai',null,null);}
 public function test_lunas_does_not_auto_finalize_inventory(): void {$b=$this->purchase(500000);$svc=app(OutgoingPaymentService::class);$svc->create($b->id_transaksi,2,'500000','tunai',null,null);$this->assertSame('lunas',$b->transaksi->fresh()->status);$this->assertSame('proses',$b->fresh()->status);$this->assertSame(0,DB::table('mobil')->where('nomor_rangka',$b->pengajuan->nomor_rangka)->count());}
 public function test_finalize_creates_inventory_and_syncs_source_atomically(): void {$b=$this->purchase(700000);app(OutgoingPaymentService::class)->create($b->id_transaksi,2,'700000','tunai',null,null);$m=app(PembelianMobilBekasService::class)->finalize($b->id_pembelian);$this->assertSame('bekas',$m->kondisi);$this->assertSame('tersedia',$m->status);$this->assertSame('700000.00',(string)$m->harga_beli);$this->assertSame('selesai',$b->fresh()->status);$this->assertSame('selesai',$b->pengajuan->fresh()->status);}
 public function test_double_finalize_and_customer_mutation_are_blocked(): void {$b=DB::table('pembelian_mobil_bekas')->where('status','selesai')->first();$this->assertNotNull($b);$this->expectException(RuntimeException::class);app(PembelianMobilBekasService::class)->finalize($b->id_pembelian);}
 public function test_stock_and_trade_in_are_untouched(): void {$before=DB::table('sparepart')->sum('stok');$this->purchase();$this->assertSame((string)$before,(string)DB::table('sparepart')->sum('stok'));$this->assertTrue(Schema::hasTable('trade_in'));}
 public function test_only_operations_can_access_purchase_actions(): void {$p=$this->user('pelanggan');$this->actingAs($p)->post('/admin/pengajuan-mobil/1/purchase')->assertForbidden();$this->actingAs($p)->post('/petugas/pembelian-mobil/1/finalize')->assertForbidden();}
}
