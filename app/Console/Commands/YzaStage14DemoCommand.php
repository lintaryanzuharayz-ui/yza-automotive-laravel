<?php
namespace App\Console\Commands;
use App\Models\{Membership,PromoMember,PromoSimulation};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB,Hash};
use Illuminate\Support\Carbon;

class YzaStage14DemoCommand extends Command
{
    protected $signature='yza:stage14-demo';
    protected $description='Menyiapkan data demo Stage 14 secara idempotent di Laravel';
    public function handle():int
    {
        $role=DB::table('roles')->where('nama_role','pelanggan')->value('id_role');$now=Carbon::now('Asia/Jakarta');
        foreach([['demo.active','active','Demo Active'],['demo.pending','pending_payment','Demo Pending']] as [$username,$status,$name]){
            $user=DB::table('users')->where('username',$username)->first();
            if(!$user){$uid=DB::table('users')->insertGetId(['id_role'=>$role,'username'=>$username,'email'=>$username.'@yza.local','password'=>Hash::make('DemoStage14!'),'status'=>'aktif','created_at'=>$now,'updated_at'=>$now]);$pid=DB::table('pelanggan')->insertGetId(['id_user'=>$uid,'kode_pelanggan'=>'DEMO-'.strtoupper(substr(md5($username),0,8)),'nama_lengkap'=>$name,'no_hp'=>'080000000000','status'=>'aktif','created_at'=>$now,'updated_at'=>$now]);}else{$pid=DB::table('pelanggan')->where('id_user',$user->id_user)->value('id_pelanggan');}
            DB::table('memberships')->updateOrInsert(['id_pelanggan'=>$pid],['member_id'=>'YZA-DEMO-'.strtoupper(substr(md5($username),0,8)),'status'=>$status,'activation_fee'=>249000,'registered_at'=>$now,'activated_at'=>$status==='active'?$now:null,'cancelled_at'=>null,'updated_at'=>$now,'created_at'=>$now]);
        }
        $start=$now->copy()->startOfDay();$end=$start->copy()->addYear();
        foreach([['YZA-CARSALE5','Promo Penjualan Mobil Member','penjualan_mobil','persentase',5,10000000],['YZA-TRADEIN5','Promo Trade-In Member','trade_in','nominal',5000000,null]] as [$code,$name,$scope,$type,$value,$max]) PromoMember::updateOrCreate(['kode_promo'=>$code],['nama'=>$name,'jenis_benefit'=>$type,'nilai_benefit'=>$value,'minimum_transaksi'=>0,'maksimum_benefit'=>$max,'berlaku_untuk'=>$scope,'mulai_at'=>$start,'selesai_at'=>$end,'syarat_ketentuan'=>'Data demo Stage 14.','status'=>'aktif','member_only'=>false]);
        foreach([['LAYAK','AMAN / LAYAK',88],['BERISIKO','BERISIKO',45]] as [$label,$status,$score]){
            $sid='SPK-DEMO-'.$label;$result=['before'=>['revenue'=>1000000,'cost'=>300000,'margin'=>700000],'after'=>['revenue'=>$label==='LAYAK'?900000:500000,'cost'=>300000,'margin'=>$label==='LAYAK'?600000:200000],'projection'=>['transactions'=>10,'revenue'=>$label==='LAYAK'?9000000:5000000,'discount'=>$label==='LAYAK'?1000000:5000000,'margin'=>$label==='LAYAK'?6000000:2000000],'scores'=>[],'final_score'=>$score,'status'=>$status,'recommendations'=>[]];PromoSimulation::updateOrCreate(['simulation_id'=>$sid],['created_by'=>null,'scope'=>'penjualan_mobil','discount_type'=>'percentage','discount_value'=>$label==='LAYAK'?5:50,'max_discount'=>10000000,'min_transaction'=>0,'input_payload'=>['demo'=>true],'result_payload'=>$result,'score'=>$score,'status'=>$status,'recommendation'=>'Data demo Stage 14']);PromoMember::updateOrCreate(['kode_promo'=>'YZA-SPK-'.$label],['nama'=>'Promo SPK '.$label,'jenis_benefit'=>'persentase','nilai_benefit'=>$label==='LAYAK'?5:50,'minimum_transaksi'=>0,'maksimum_benefit'=>10000000,'berlaku_untuk'=>'penjualan_mobil','mulai_at'=>$start,'selesai_at'=>$end,'syarat_ketentuan'=>'Draft demo SPK.','status'=>'nonaktif','member_only'=>false,'approved_spk_simulation_id'=>$sid,'spk_score'=>$score,'spk_recommendation'=>$status]);
        }
        $this->info('Data demo Stage 14 siap secara idempotent.');return self::SUCCESS;
    }
}
