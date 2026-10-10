<?php
namespace App\Services;
use App\Models\{InspeksiMobil,Mobil,PengajuanMobil,TradeIn,Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;
class TradeInService
{
 private function code(string $table,string $column,string $prefix):string{do{$c=$prefix.'-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4)));}while(DB::table($table)->where($column,$c)->exists());return $c;}
 public function create(int $customerId,int $pengajuanId,int $inspectionId,int $dealerCarId,int $petugasId,string $discount='0',?string $note=null):TradeIn
 {
  return DB::transaction(function()use($customerId,$pengajuanId,$inspectionId,$dealerCarId,$petugasId,$discount,$note){
   $p=PengajuanMobil::lockForUpdate()->where('id_pengajuan',$pengajuanId)->where('id_pelanggan',$customerId)->findOrFail($pengajuanId);
   $i=InspeksiMobil::lockForUpdate()->where('id_inspeksi',$inspectionId)->where('id_pengajuan',$p->id_pengajuan)->first();
   if($p->status!=='penawaran_diberikan'||!$i||$i->harga_penawaran===null||(float)$i->harga_penawaran<0)throw new RuntimeException('Pengajuan belum memiliki penawaran yang dapat dipakai trade-in.');
   if(TradeIn::where('id_pengajuan',$p->id_pengajuan)->where('status','<>','dibatalkan')->exists())throw new RuntimeException('Trade-in untuk pengajuan ini sudah ada.');
   $car=Mobil::lockForUpdate()->find($dealerCarId);if(!$car||$car->status!=='tersedia')throw new RuntimeException('Mobil dealer tidak tersedia.');
   $d=(float)$discount;if($d<0||$d>(float)$car->harga_jual)throw new RuntimeException('Diskon tidak valid.');
   $difference=max(0,(float)$car->harga_jual-$d-(float)$i->harga_penawaran);
   $tx=new Transaksi();$tx->forceFill(['kode_transaksi'=>$this->code('transaksi','kode_transaksi','TRX'),'id_pelanggan'=>$customerId,'id_petugas'=>$petugasId,'jenis_transaksi'=>'trade_in','tanggal'=>now(),'subtotal'=>$difference,'diskon'=>0,'total'=>$difference,'status'=>'belum_bayar','catatan'=>$note])->save();
   $trade=new TradeIn();$trade->forceFill(['kode_trade_in'=>$this->code('trade_in','kode_trade_in','TRD'),'id_transaksi'=>$tx->id_transaksi,'id_pelanggan'=>$customerId,'id_pengajuan'=>$p->id_pengajuan,'id_inspeksi'=>$i->id_inspeksi,'id_mobil_dibeli'=>$car->id_mobil,'id_petugas'=>$petugasId,'nilai_mobil_lama'=>$i->harga_penawaran,'harga_mobil_dibeli'=>$car->harga_jual,'diskon'=>$d,'selisih_bayar'=>$difference,'tanggal'=>now(),'status'=>'proses','catatan'=>$note])->save();
   $car->forceFill(['status'=>'dibooking'])->save();return $trade;
  });
 }
 public function finalize(int $tradeId):Mobil
 {
  return DB::transaction(function()use($tradeId){
   $trade=TradeIn::lockForUpdate()->findOrFail($tradeId);$tx=Transaksi::lockForUpdate()->findOrFail($trade->id_transaksi);$p=PengajuanMobil::lockForUpdate()->findOrFail($trade->id_pengajuan);$dealer=Mobil::lockForUpdate()->findOrFail($trade->id_mobil_dibeli);
   if($trade->status!=='proses'||$trade->id_mobil_lama_masuk!==null||$tx->status!=='lunas')throw new RuntimeException('Trade-in belum lunas atau sudah selesai.');
   if($dealer->status!=='dibooking')throw new RuntimeException('Mobil dealer tidak lagi direservasi.');
   if(Mobil::where('nomor_rangka',$p->nomor_rangka)->orWhere('nomor_mesin',$p->nomor_mesin)->exists())throw new RuntimeException('Nomor rangka atau nomor mesin sudah terdaftar.');
   $old=new Mobil();$old->forceFill(['kode_mobil'=>$this->code('mobil','kode_mobil','MOB'),'merk'=>$p->merk,'model'=>$p->model,'tipe'=>$p->tipe,'tahun'=>$p->tahun,'warna'=>$p->warna,'transmisi'=>$p->transmisi,'bahan_bakar'=>$p->bahan_bakar,'nomor_rangka'=>$p->nomor_rangka,'nomor_mesin'=>$p->nomor_mesin,'kilometer'=>$p->kilometer,'harga_beli'=>$trade->nilai_mobil_lama,'harga_jual'=>0,'kondisi'=>'bekas','status'=>'tersedia','deskripsi'=>'Trade-in '.$trade->kode_trade_in,'tanggal_masuk'=>now()->toDateString(),'sumber_mobil'=>'trade_in'])->save();
   $dealer->forceFill(['status'=>'terjual'])->save();$trade->forceFill(['id_mobil_lama_masuk'=>$old->id_mobil,'status'=>'selesai'])->save();$p->forceFill(['status'=>'selesai'])->save();app(YzaPointService::class)->awardIfEligible($tx);
   if($p->id_kendaraan_pelanggan)DB::table('kendaraan_pelanggan')->where('id_kendaraan_pelanggan',$p->id_kendaraan_pelanggan)->where('status','aktif')->update(['status'=>'dijual','updated_at'=>now()]);
   return $old;
  });
 }
 public function cancel(int $tradeId):void{DB::transaction(function()use($tradeId){$trade=TradeIn::lockForUpdate()->findOrFail($tradeId);$tx=Transaksi::lockForUpdate()->findOrFail($trade->id_transaksi);$car=Mobil::lockForUpdate()->findOrFail($trade->id_mobil_dibeli);if($trade->status!=='proses'||$tx->status!=='belum_bayar')throw new RuntimeException('Trade-in tidak dapat dibatalkan pada status ini.');$trade->forceFill(['status'=>'dibatalkan'])->save();$tx->forceFill(['status'=>'dibatalkan'])->save();if($car->status==='dibooking')$car->forceFill(['status'=>'tersedia'])->save();});}
 public function createWithPromo(int $customerId,int $pengajuanId,int $inspectionId,int $dealerCarId,int $petugasId,string $discount='0',?string $note=null,?int $promoId=null):TradeIn{$trade=$this->create($customerId,$pengajuanId,$inspectionId,$dealerCarId,$petugasId,$discount,$note);if($promoId!==null)app(PromoEngineService::class)->applyToTransaction($trade->transaksi,$promoId,'trade_in');return$trade->fresh(['transaksi']);}
}
