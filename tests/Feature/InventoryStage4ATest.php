<?php
namespace Tests\Feature;
use App\Models\Sparepart;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class InventoryStage4ATest extends TestCase
{
    use DatabaseTransactions;
    public function test_receipt_finalization_updates_stock_once_and_writes_history(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); $before=(int)$part->stok; $petugas=(int)DB::table('petugas')->where('status','aktif')->value('id_petugas'); $svc=app(InventoryService::class);
        $receipt=$svc->createReceipt(['id_supplier'=>null,'tanggal'=>now(),'catatan'=>'test'],$petugas); $svc->addDetail($receipt->id_stok_masuk,['id_sparepart'=>$part->id_sparepart,'jumlah'=>2,'harga_beli'=>'1000.00']); $svc->finalize($receipt->id_stok_masuk);
        $this->assertSame('diterima',$receipt->fresh()->status); $this->assertSame($before+2,(int)$part->fresh()->stok); $this->assertDatabaseHas('riwayat_stok',['referensi_id'=>$receipt->id_stok_masuk,'jenis_transaksi'=>'stok_masuk','jumlah_perubahan'=>2]);
        $this->expectException(\RuntimeException::class); $svc->finalize($receipt->id_stok_masuk);
    }
    public function test_adjustment_writes_signed_delta_and_draft_cancel_does_not_change_stock(): void
    {
        $part=Sparepart::where('status','aktif')->firstOrFail(); $before=(int)$part->stok; $petugas=(int)DB::table('petugas')->where('status','aktif')->value('id_petugas'); $svc=app(InventoryService::class);
        $svc->adjust($part->id_sparepart,$before+3,'audit fisik','test',$petugas); $this->assertSame($before+3,(int)$part->fresh()->stok); $this->assertDatabaseHas('riwayat_stok',['id_sparepart'=>$part->id_sparepart,'jenis_transaksi'=>'penyesuaian','jumlah_perubahan'=>3]);
        $receipt=$svc->createReceipt(['id_supplier'=>null,'tanggal'=>now(),'catatan'=>'test'],$petugas); $svc->cancel($receipt->id_stok_masuk); $this->assertSame($before+3,(int)$part->fresh()->stok); $this->assertSame('dibatalkan',$receipt->fresh()->status);
    }
}
