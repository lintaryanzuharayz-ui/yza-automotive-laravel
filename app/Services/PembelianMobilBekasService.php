<?php
namespace App\Services;

use App\Models\{InspeksiMobil,Mobil,PembelianMobilBekas,PengajuanMobil,Transaksi};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PembelianMobilBekasService
{
    private function code(string $prefix,string $column,string $table): string { do {$c=$prefix.'-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4)));} while(DB::table($table)->where($column,$c)->exists()); return $c; }
    public function createFromApproved(int $pengajuanId,int $inspectionId,int $petugasId,?string $note): PembelianMobilBekas
    {
        return DB::transaction(function() use($pengajuanId,$inspectionId,$petugasId,$note) {
            $p=PengajuanMobil::lockForUpdate()->findOrFail($pengajuanId); $i=InspeksiMobil::lockForUpdate()->where('id_inspeksi',$inspectionId)->where('id_pengajuan',$pengajuanId)->first();
            if($p->status!=='disetujui'||!$i||$i->harga_penawaran===null||(float)$i->harga_penawaran<0) throw new RuntimeException('Pengajuan belum disetujui atau penawaran tidak valid.');
            if(PembelianMobilBekas::where('id_pengajuan',$pengajuanId)->where('status','<>','dibatalkan')->exists()) throw new RuntimeException('Pembelian untuk pengajuan ini sudah ada.');
            $tx=new Transaksi(); $tx->forceFill(['kode_transaksi'=>$this->code('TRX','kode_transaksi','transaksi'),'id_pelanggan'=>$p->id_pelanggan,'id_petugas'=>$petugasId,'jenis_transaksi'=>'pembelian_mobil','tanggal'=>now(),'subtotal'=>$i->harga_penawaran,'diskon'=>0,'total'=>$i->harga_penawaran,'status'=>'belum_bayar','catatan'=>$note])->save();
            $b=new PembelianMobilBekas();$b->forceFill(['kode_pembelian'=>$this->code('BUY','kode_pembelian','pembelian_mobil_bekas'),'id_transaksi'=>$tx->id_transaksi,'id_pengajuan'=>$p->id_pengajuan,'id_inspeksi'=>$i->id_inspeksi,'id_pelanggan'=>$p->id_pelanggan,'id_petugas'=>$petugasId,'tanggal_pembelian'=>now(),'harga_pembelian'=>$i->harga_penawaran,'status'=>'proses','catatan'=>$note])->save();return $b;
        });
    }
    public function finalize(int $purchaseId): Mobil
    {
        return DB::transaction(function() use($purchaseId) {
            $b= PembelianMobilBekas::lockForUpdate()->findOrFail($purchaseId); $tx=Transaksi::lockForUpdate()->findOrFail($b->id_transaksi); $p=PengajuanMobil::lockForUpdate()->findOrFail($b->id_pengajuan); $i=InspeksiMobil::lockForUpdate()->findOrFail($b->id_inspeksi);
            if($b->status!=='proses'||$b->id_mobil!==null||$tx->status!=='lunas'||$p->status!=='disetujui') throw new RuntimeException('Pembelian belum lunas, sudah selesai, atau pengajuan tidak valid.');
            if(Mobil::where('nomor_rangka',$p->nomor_rangka)->orWhere('nomor_mesin',$p->nomor_mesin)->exists()) throw new RuntimeException('Nomor rangka atau nomor mesin sudah terdaftar di inventory.');
            $m=new Mobil();$m->forceFill(['kode_mobil'=>$this->code('MOB','kode_mobil','mobil'),'merk'=>$p->merk,'model'=>$p->model,'tipe'=>$p->tipe,'tahun'=>$p->tahun,'warna'=>$p->warna,'transmisi'=>$p->transmisi,'bahan_bakar'=>$p->bahan_bakar,'nomor_rangka'=>$p->nomor_rangka,'nomor_mesin'=>$p->nomor_mesin,'kilometer'=>max((int)$p->kilometer,(int)($i->kilometer??0)),'harga_beli'=>$b->harga_pembelian,'harga_jual'=>0,'kondisi'=>'bekas','status'=>'tersedia','deskripsi'=>$p->catatan,'tanggal_masuk'=>now()->toDateString(),'sumber_mobil'=>'pembelian_bekas'])->save();
            $b->forceFill(['id_mobil'=>$m->id_mobil,'status'=>'selesai'])->save();$p->forceFill(['status'=>'selesai'])->save();
            if($p->id_kendaraan_pelanggan) DB::table('kendaraan_pelanggan')->where('id_kendaraan_pelanggan',$p->id_kendaraan_pelanggan)->where('status','aktif')->update(['status'=>'dijual','updated_at'=>now()]);
            return $m;
        });
    }
}
