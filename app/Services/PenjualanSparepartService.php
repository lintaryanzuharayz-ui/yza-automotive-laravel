<?php
namespace App\Services;

use App\Models\{DetailPenjualanSparepart,Pelanggan,PenjualanSparepart,Sparepart,Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PenjualanSparepartService
{
    private function code(string $table,string $column,string $prefix): string { do {$code=$prefix.'-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4)));} while(DB::table($table)->where($column,$code)->exists()); return $code; }
    private function subtotal(string $price,int $qty): string { return (string) DB::selectOne('SELECT CAST(? * ? AS DECIMAL(15,2)) AS value',[$price,$qty])->value; }
    private function draft(int $id): PenjualanSparepart { $sale=PenjualanSparepart::lockForUpdate()->findOrFail($id); if($sale->status!=='draft') throw new RuntimeException('Penjualan tidak berstatus draft.'); return $sale; }

    public function createDraft(?int $customerId,int $petugasId,string $note=''): PenjualanSparepart
    {
        return DB::transaction(function() use($customerId,$petugasId,$note){
            if($customerId!==null && !Pelanggan::where('id_pelanggan',$customerId)->where('status','aktif')->exists()) throw new RuntimeException('Pelanggan tidak aktif.');
            $tx=new Transaksi(); $tx->forceFill(['kode_transaksi'=>$this->code('transaksi','kode_transaksi','TRX'),'id_pelanggan'=>$customerId,'id_petugas'=>$petugasId,'jenis_transaksi'=>'penjualan_sparepart','tanggal'=>now(),'subtotal'=>0,'diskon'=>0,'total'=>0,'status'=>'draft','catatan'=>$note])->save();
            $sale=new PenjualanSparepart(); $sale->forceFill(['kode_penjualan'=>$this->code('penjualan_sparepart','kode_penjualan','SPS'),'id_transaksi'=>$tx->id_transaksi,'id_pelanggan'=>$customerId,'id_petugas'=>$petugasId,'tanggal'=>now(),'subtotal'=>0,'diskon'=>0,'total'=>0,'status'=>'draft','catatan'=>$note])->save();
            return $sale;
        });
    }

    public function addDetail(int $saleId,int $partId,int $quantity): DetailPenjualanSparepart
    {
        return DB::transaction(function() use($saleId,$partId,$quantity){
            $sale=$this->draft($saleId); if($quantity<=0) throw new RuntimeException('Qty harus lebih dari 0.');
            $part=Sparepart::whereKey($partId)->where('status','aktif')->lockForUpdate()->first(); if(!$part) throw new RuntimeException('Spare part tidak ditemukan.');
            $detail=DetailPenjualanSparepart::where('id_penjualan_sparepart',$sale->id_penjualan_sparepart)->where('id_sparepart',$partId)->lockForUpdate()->first();
            $newQty=$quantity+($detail?(int)$detail->jumlah:0); if($newQty>(int)$part->stok) throw new RuntimeException('Qty melebihi stok tersedia.');
            $values=['jumlah'=>$newQty,'harga_satuan'=>$part->harga_jual,'subtotal'=>$this->subtotal((string)$part->harga_jual,$newQty)];
            if($detail){$detail->forceFill($values)->save();return $detail;} $detail=new DetailPenjualanSparepart();$detail->forceFill(['id_penjualan_sparepart'=>$sale->id_penjualan_sparepart,'id_sparepart'=>$partId]+$values)->save();return $detail;
        });
    }

    public function updateDetail(int $detailId,int $quantity): DetailPenjualanSparepart
    {
        return DB::transaction(function() use($detailId,$quantity){
            $detail=DetailPenjualanSparepart::lockForUpdate()->findOrFail($detailId); $this->draft((int)$detail->id_penjualan_sparepart); if($quantity<=0) throw new RuntimeException('Qty harus lebih dari 0.');
            $part=Sparepart::whereKey($detail->id_sparepart)->where('status','aktif')->lockForUpdate()->first(); if(!$part||$quantity>(int)$part->stok) throw new RuntimeException('Qty tidak valid atau melebihi stok.');
            $detail->forceFill(['jumlah'=>$quantity,'harga_satuan'=>$part->harga_jual,'subtotal'=>$this->subtotal((string)$part->harga_jual,$quantity)])->save(); return $detail;
        });
    }

    public function removeDetail(int $detailId): void { DB::transaction(function() use($detailId){$detail=DetailPenjualanSparepart::lockForUpdate()->findOrFail($detailId);$this->draft((int)$detail->id_penjualan_sparepart);$detail->delete();}); }
    public function cancel(int $saleId): void { DB::transaction(function() use($saleId){$sale=$this->draft($saleId);$sale->forceFill(['status'=>'dibatalkan'])->save();Transaksi::whereKey($sale->id_transaksi)->where('status','draft')->update(['status'=>'dibatalkan']);}); }

    public function finalize(int $saleId,int $petugasId,?callable $beforeCommit=null): Transaksi
    {
        return DB::transaction(function() use($saleId,$petugasId,$beforeCommit){
            $sale=PenjualanSparepart::lockForUpdate()->findOrFail($saleId); if($sale->status!=='draft'||$sale->id_transaksi===null) throw new RuntimeException('Draft penjualan tidak tersedia atau sudah difinalisasi.');
            $items=DetailPenjualanSparepart::where('id_penjualan_sparepart',$saleId)->orderBy('id_sparepart')->lockForUpdate()->get(); if($items->isEmpty()) throw new RuntimeException('Draft belum memiliki item.');
            $total=(string) DB::selectOne('SELECT CAST(COALESCE(SUM(subtotal),0) AS DECIMAL(15,2)) AS value FROM detail_penjualan_sparepart WHERE id_penjualan_sparepart=?',[$saleId])->value;
            $tx=Transaksi::lockForUpdate()->findOrFail($sale->id_transaksi); if($tx->status!=='draft') throw new RuntimeException('Transaksi draft tidak tersedia.');
            $sale->forceFill(['subtotal'=>$total,'diskon'=>0,'total'=>$total])->save(); $tx->forceFill(['subtotal'=>$total,'diskon'=>0,'total'=>$total,'status'=>'belum_bayar'])->save();
            app(InventoryService::class)->consumeSalePartsWithinTransaction($items,$petugasId,$saleId,$sale->kode_penjualan);
            if($beforeCommit)$beforeCommit();
            $sale->forceFill(['status'=>'selesai'])->save(); return $tx->fresh();
        });
    }
 public function finalizeWithPromo(int $saleId,int $petugasId,?int $promoId=null):Transaksi{$tx=$this->finalize($saleId,$petugasId);if($promoId!==null)app(PromoEngineService::class)->applyToTransaction($tx,$promoId,'penjualan_sparepart');return$tx->fresh();}
}
