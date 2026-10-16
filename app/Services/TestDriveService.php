<?php
namespace App\Services;
use App\Models\{Mobil,TestDrive};
use Illuminate\Support\Facades\DB;
use RuntimeException;
class TestDriveService {
 public function actorPetugasId(?int $userId):?int{return DB::table('petugas')->where('id_user',$userId)->value('id_petugas') ?: DB::table('petugas')->where('status','aktif')->value('id_petugas');}
 private function code():string{$code='TDR-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3)));while(TestDrive::where('kode_test_drive',$code)->exists())$code='TDR-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(3)));return $code;}
 public function create(int $customerId,int $mobilId,string $date,string $time,?string $location,?string $note):TestDrive{if(strtotime($date.' '.$time)<=now()->timestamp)throw new RuntimeException('Tanggal dan jam harus berada di masa depan.');return DB::transaction(function()use($customerId,$mobilId,$date,$time,$location,$note){$car=Mobil::lockForUpdate()->findOrFail($mobilId);if(in_array($car->status,['terjual','nonaktif','dalam_pemeriksaan'],true))throw new RuntimeException('Mobil tidak layak untuk test drive.');$td=new TestDrive();$td->forceFill(['kode_test_drive'=>$this->code(),'id_pelanggan'=>$customerId,'id_mobil'=>$mobilId,'tanggal'=>$date,'jam'=>$time,'lokasi'=>$location,'catatan'=>$note,'status'=>'menunggu']);$td->save();return $td;});}
 public function approve(int $id,int $petugasId):void{$this->transition($id,'disetujui',$petugasId);}
 public function reject(int $id,int $petugasId):void{$this->transition($id,'ditolak',$petugasId);}
 public function complete(int $id,int $petugasId):void{$this->transition($id,'selesai',$petugasId);}
 public function cancel(int $id,?int $customerId=null,?int $petugasId=null):void{$this->transition($id,'dibatalkan',$petugasId,$customerId);}
 private function transition(int $id,string $target,?int $petugasId=null,?int $customerId=null):void{DB::transaction(function()use($id,$target,$petugasId,$customerId){$q=TestDrive::whereKey($id);if($customerId!==null)$q->where('id_pelanggan',$customerId);$td=$q->lockForUpdate()->first();$allowed=['menunggu'=>['disetujui','ditolak','dibatalkan'],'disetujui'=>['selesai','dibatalkan'],'ditolak'=>[],'selesai'=>[],'dibatalkan'=>[]];if(!$td||!in_array($target,$allowed[$td->status]??[],true))throw new RuntimeException('Perubahan status test drive tidak diizinkan.');$td->forceFill(['status'=>$target,'id_petugas'=>$petugasId??$td->id_petugas])->save();});}
}
