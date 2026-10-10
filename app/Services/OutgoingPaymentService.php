<?php
namespace App\Services;

use App\Models\{Pembayaran,Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OutgoingPaymentService
{
    private function code(): string { do {$c='PAY-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4)));} while(Pembayaran::where('kode_pembayaran',$c)->exists()); return $c; }
    private function paid(Transaksi $tx): float { return (float) DB::table('pembayaran')->where('id_transaksi',$tx->id_transaksi)->where('arah','keluar')->where('status','terverifikasi')->sum('jumlah'); }
    private function assertSupported(Transaksi $tx): void { if($tx->jenis_transaksi!=='pembelian_mobil') throw new RuntimeException('Jenis transaksi bukan pembelian mobil bekas.'); }
    private function status(float $total,float $paid): string { if($paid<=0)return 'belum_bayar'; if($paid<$total)return 'dp'; return 'lunas'; }
    public function create(int $transactionId,int $actorId,string $amount,string $method,?string $proof,?string $note): Pembayaran
    {
        return DB::transaction(function() use($transactionId,$amount,$method,$proof,$note) {
            $tx=Transaksi::lockForUpdate()->findOrFail($transactionId); $this->assertSupported($tx);
            if(in_array($tx->status,['dibatalkan','lunas'],true)) throw new RuntimeException('Transaksi tidak tersedia untuk pembayaran baru.');
            if(!in_array($method,['tunai','transfer','kredit','lainnya'],true) || (float)$amount<=0) throw new RuntimeException('Pembayaran tidak valid.');
            $paid=$this->paid($tx); $state=$method==='tunai'?'terverifikasi':'menunggu';
            if($state==='terverifikasi' && $paid+(float)$amount>(float)$tx->total) throw new RuntimeException('Pembayaran melebihi total transaksi.');
            $p=new Pembayaran(); $p->forceFill(['kode_pembayaran'=>$this->code(),'id_transaksi'=>$tx->id_transaksi,'tanggal'=>now(),'arah'=>'keluar','metode'=>$method,'jumlah'=>$amount,'bukti_transfer'=>$proof,'status'=>$state,'catatan'=>$note])->save();
            if($state==='terverifikasi') $tx->forceFill(['status'=>$this->status((float)$tx->total,$paid+(float)$amount)])->save();
            return $p;
        });
    }
    public function verify(int $paymentId,int $actorId): Pembayaran
    {
        return DB::transaction(function() use($paymentId) {
            $p=Pembayaran::lockForUpdate()->findOrFail($paymentId); if($p->status!=='menunggu'||$p->arah!=='keluar') throw new RuntimeException('Pembayaran keluar tidak menunggu verifikasi.');
            $tx=Transaksi::lockForUpdate()->findOrFail($p->id_transaksi); $this->assertSupported($tx); $paid=$this->paid($tx);
            if($paid+(float)$p->jumlah>(float)$tx->total) throw new RuntimeException('Pembayaran menyebabkan overpayment.');
            $p->forceFill(['status'=>'terverifikasi'])->save(); $tx->forceFill(['status'=>$this->status((float)$tx->total,$paid+(float)$p->jumlah)])->save(); return $p;
        });
    }
    public function reject(int $paymentId,int $actorId): Pembayaran { return DB::transaction(function() use($paymentId){$p=Pembayaran::lockForUpdate()->findOrFail($paymentId);if($p->status!=='menunggu'||$p->arah!=='keluar')throw new RuntimeException('Pembayaran tidak dapat ditolak.');$p->forceFill(['status'=>'ditolak'])->save();return $p;}); }
    public function summary(int $transactionId): array { $tx=Transaksi::findOrFail($transactionId);$paid=$this->paid($tx);return ['paid'=>number_format($paid,2,'.',''),'remaining'=>number_format(max(0,(float)$tx->total-$paid),2,'.',''),'status'=>$tx->status]; }
}
