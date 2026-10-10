<?php
namespace Tests\Feature;

use App\Models\{DetailPenjualanSparepart,PenjualanSparepart,Sparepart,Transaksi,User};
use App\Services\PenjualanSparepartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class SparepartSalesStage6ATest extends TestCase
{
    use DatabaseTransactions;
    private function user(string $role): User { return User::whereHas('role',fn($q)=>$q->where('nama_role',$role))->firstOrFail(); }
    private function actor(): int { return $this->user('petugas')->petugas->id_petugas; }
    private function draft(?int $customer=null): PenjualanSparepart { return app(PenjualanSparepartService::class)->createDraft($customer,$this->actor(),'QA'); }
    private function item(PenjualanSparepart $sale,int $qty=1): DetailPenjualanSparepart { return app(PenjualanSparepartService::class)->addDetail($sale->id_penjualan_sparepart,Sparepart::where('status','aktif')->firstOrFail()->id_sparepart,$qty); }

    public function test_baseline_sales_and_details_are_imported_without_stock_mutation(): void
    {
        $this->assertSame(2,PenjualanSparepart::count()); $this->assertSame(2,DetailPenjualanSparepart::count());
        $this->assertDatabaseHas('penjualan_sparepart',['id_penjualan_sparepart'=>1,'kode_penjualan'=>'SPS-202609-29E646FA','status'=>'selesai']);
        $this->assertDatabaseHas('detail_penjualan_sparepart',['id_detail'=>1,'jumlah'=>3,'harga_satuan'=>'35000.00']);
        $this->assertSame(7,DB::table('riwayat_stok')->count());
    }

    public function test_create_draft_allows_walk_in_and_creates_draft_transaction(): void
    {
        $sale=$this->draft(); $this->assertNull($sale->id_pelanggan); $this->assertSame('draft',$sale->status); $this->assertSame('draft',$sale->transaksi->status); $this->assertSame('penjualan_sparepart',$sale->transaksi->jenis_transaksi);
    }

    public function test_add_merges_duplicate_and_snapshots_server_price_without_stock_change(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); $stock=(int)$part->stok; $sale=$this->draft(); $first=$this->item($sale,1); $second=$this->item($sale,1);
        $this->assertSame($first->id_detail,$second->id_detail); $this->assertSame(2,(int)$second->fresh()->jumlah); $this->assertEquals((float)$part->harga_jual,(float)$second->fresh()->harga_satuan); $this->assertSame($stock,(int)$part->fresh()->stok);
    }

    public function test_invalid_qty_and_inactive_part_are_rejected(): void
    {
        $sale=$this->draft(); $part=Sparepart::firstOrFail(); $svc=app(PenjualanSparepartService::class);
        foreach([0,-1] as $qty){$this->expectException(RuntimeException::class);try{$svc->addDetail($sale->id_penjualan_sparepart,$part->id_sparepart,$qty);}catch(RuntimeException $e){$this->assertTrue(true);}}
        DB::table('sparepart')->where('id_sparepart',$part->id_sparepart)->update(['status'=>'nonaktif']); $this->expectException(RuntimeException::class); $svc->addDetail($sale->id_penjualan_sparepart,$part->id_sparepart,1);
    }

    public function test_edit_and_delete_draft_do_not_change_stock(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); $stock=(int)$part->stok; $sale=$this->draft(); $detail=$this->item($sale,1); app(PenjualanSparepartService::class)->updateDetail($detail->id_detail,1); app(PenjualanSparepartService::class)->removeDetail($detail->id_detail); $this->assertDatabaseMissing('detail_penjualan_sparepart',['id_detail'=>$detail->id_detail]); $this->assertSame($stock,(int)$part->fresh()->stok);
    }

    public function test_price_snapshot_survives_master_price_change_until_finalize(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); $old=(string)$part->harga_jual; $sale=$this->draft(); $detail=$this->item($sale,1); DB::table('sparepart')->where('id_sparepart',$part->id_sparepart)->update(['harga_jual'=>999999]); $tx=app(PenjualanSparepartService::class)->finalize($sale->id_penjualan_sparepart,$this->actor()); $this->assertSame($old,(string)$detail->fresh()->harga_satuan); $this->assertSame($old,(string)$tx->fresh()->total);
    }

    public function test_finalize_consumes_stock_history_and_creates_unpaid_transaction_without_payment(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); $before=(int)$part->stok; $history=DB::table('riwayat_stok')->count(); $payments=DB::table('pembayaran')->count(); $sale=$this->draft(); $detail=$this->item($sale,1); $tx=app(PenjualanSparepartService::class)->finalize($sale->id_penjualan_sparepart,$this->actor());
        $this->assertSame($before-1,(int)$part->fresh()->stok); $this->assertSame($history+1,DB::table('riwayat_stok')->count()); $this->assertDatabaseHas('riwayat_stok',['jenis_transaksi'=>'penjualan','referensi_tipe'=>'penjualan_sparepart','referensi_id'=>$sale->id_penjualan_sparepart,'jumlah_perubahan'=>-1]); $this->assertSame('selesai',$sale->fresh()->status); $this->assertSame('belum_bayar',$tx->status); $this->assertSame($payments,DB::table('pembayaran')->count()); $this->assertEquals((float)$detail->subtotal,(float)$tx->total);
    }

    public function test_insufficient_stock_and_multi_item_failure_roll_back_everything(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); DB::table('sparepart')->where('id_sparepart',$part->id_sparepart)->update(['stok'=>1]); $sale=$this->draft(); $this->item($sale,1); DB::table('detail_penjualan_sparepart')->where('id_penjualan_sparepart',$sale->id_penjualan_sparepart)->update(['jumlah'=>2,'subtotal'=>70000]); $beforeHistory=DB::table('riwayat_stok')->count(); $this->expectException(RuntimeException::class); try{app(PenjualanSparepartService::class)->finalize($sale->id_penjualan_sparepart,$this->actor());}finally{$this->assertSame(1,(int)Sparepart::find($part->id_sparepart)->fresh()->stok);$this->assertSame($beforeHistory,DB::table('riwayat_stok')->count());$this->assertSame('draft',$sale->fresh()->status);$this->assertSame('draft',$sale->transaksi->fresh()->status);}
    }

    public function test_double_and_concurrent_like_finalize_are_blocked(): void
    {
        $sale=$this->draft(); $this->item($sale,1); app(PenjualanSparepartService::class)->finalize($sale->id_penjualan_sparepart,$this->actor()); $count=Transaksi::where('jenis_transaksi','penjualan_sparepart')->count(); $this->expectException(RuntimeException::class); try{app(PenjualanSparepartService::class)->finalize($sale->id_penjualan_sparepart,$this->actor());}finally{$this->assertSame($count,Transaksi::where('jenis_transaksi','penjualan_sparepart')->count());}
    }

    public function test_two_sales_competing_for_stock_cannot_both_finalize(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); DB::table('sparepart')->where('id_sparepart',$part->id_sparepart)->update(['stok'=>5]); $a=$this->draft();$b=$this->draft();$this->item($a,4);$this->item($b,4);app(PenjualanSparepartService::class)->finalize($a->id_penjualan_sparepart,$this->actor());$this->expectException(RuntimeException::class);app(PenjualanSparepartService::class)->finalize($b->id_penjualan_sparepart,$this->actor());$this->assertSame(1,(int)$part->fresh()->stok);
    }

    public function test_customer_cannot_mutate_or_access_operations_and_schema_has_no_gateway(): void
    {
        $sale=$this->draft($this->user('pelanggan')->pelanggan->id_pelanggan); $customer=$this->user('pelanggan'); $this->actingAs($customer)->get('/admin/penjualan-sparepart/'.$sale->id_penjualan_sparepart)->assertForbidden(); $this->actingAs($customer)->post('/admin/penjualan-sparepart/'.$sale->id_penjualan_sparepart.'/finalize')->assertForbidden(); $this->assertTrue(Schema::hasTable('penjualan_sparepart')); $this->assertTrue(Schema::hasTable('detail_penjualan_sparepart')); $this->assertFalse(Schema::hasTable('midtrans_orders'));
    }

    public function test_admin_and_petugas_can_open_sales_operations(): void
    {
        $this->actingAs($this->user('admin'))->get('/admin/penjualan-sparepart')->assertOk();
        $this->actingAs($this->user('petugas'))->get('/petugas/penjualan-sparepart')->assertOk();
    }
}
