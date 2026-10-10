<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RiwayatStok extends Model {
    public $timestamps=false; protected $table='riwayat_stok'; protected $primaryKey='id_riwayat'; protected $guarded=['id_riwayat'];
    protected function casts(): array { return ['tanggal'=>'datetime','jumlah_perubahan'=>'integer','stok_sebelum'=>'integer','stok_sesudah'=>'integer']; }
    public function sparepart(){ return $this->belongsTo(Sparepart::class,'id_sparepart','id_sparepart'); }
    public function petugas(){ return $this->belongsTo(Petugas::class,'id_petugas','id_petugas'); }
}
