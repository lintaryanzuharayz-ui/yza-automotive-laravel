<?php

namespace Tests\Feature;

use App\Models\{User,PesananMobilMember};
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class SmartSpkConcurrencyTest extends TestCase
{
    use \Tests\Support\CreatesPaidMember, \Tests\Support\PromoIntegrityFixtures;

    public function test_two_independent_customers_cannot_reserve_the_same_physical_unit(): void
    {
        $this->assertSame('yza_automotive_laravel_test',config('database.connections.mysql.database'));
        $members=[];$workers=[];$car=null;
        $before=DB::table('transaksi')->count();$payments=DB::table('pembayaran')->count();
        try {
            $members=[$this->newPaidMember('Race A QA'),$this->newPaidMember('Race B QA')];$car=$this->stage4Car();
            $actor=User::whereHas('role',fn($q)=>$q->where('nama_role','admin'))->firstOrFail();
            DB::beginTransaction();DB::table('mobil')->where('id_mobil',$car->id_mobil)->lockForUpdate()->first();
            foreach($members as $member){$worker=new Process([PHP_BINARY,base_path('tests/Support/spk_concurrent_worker.php'),json_encode([
                'actor'=>$actor->id_user,'customer'=>$member->id_pelanggan,'car'=>$car->id_mobil])],base_path());$worker->setTimeout(30);$worker->start();$workers[]=$worker;}
            $deadline=microtime(true)+8;
            do {$ready=count(array_filter($workers,fn($w)=>str_contains($w->getOutput(),'READY')));if($ready===2)break;usleep(20000);}while(microtime(true)<$deadline);
            $this->assertSame(2,$ready,implode(' ',array_map(fn($w)=>$w->getErrorOutput(),$workers)));
            foreach($workers as$worker)$this->assertStringNotContainsString('DONE',$worker->getOutput());
            DB::commit();
            foreach($workers as$worker){$worker->wait();$this->assertTrue($worker->isSuccessful(),$worker->getErrorOutput());}
            $this->assertSame(1,count(array_filter($workers,fn($w)=>str_contains($w->getOutput(),'DONE'))));
            $this->assertSame(1,count(array_filter($workers,fn($w)=>str_contains($w->getOutput(),'REJECTED'))));
            $this->assertSame(1,PesananMobilMember::where('id_mobil',$car->id_mobil)->count());
            $order=PesananMobilMember::where('id_mobil',$car->id_mobil)->firstOrFail();
            $this->assertSame('pending',$order->manual_discount_status);$this->assertNull($order->id_transaksi);
            $this->assertSame($before,DB::table('transaksi')->count());$this->assertSame($payments,DB::table('pembayaran')->count());
        } finally {
            if(DB::transactionLevel())DB::rollBack();foreach($workers as$worker)if($worker->isRunning())$worker->stop(1);
            if($car){$ids=PesananMobilMember::where('id_mobil',$car->id_mobil)->pluck('id_pesanan');
                DB::table('notifikasi')->where('referensi_tipe','pesanan_mobil_member')->whereIn('referensi_id',$ids)->delete();
                PesananMobilMember::where('id_mobil',$car->id_mobil)->delete();$car->delete();}
            foreach($members as$member){$m=$member->membership;$ids=$m->gatewayPayments()->pluck('id_pembayaran_gateway');
                $m->forceFill(['gateway_payment_id'=>null])->save();DB::table('event_pembayaran_gateway')->whereIn('id_pembayaran_gateway',$ids)->delete();
                $m->gatewayPayments()->delete();$m->delete();$user=$member->user;$member->delete();$user->delete();}
        }
    }
}
