<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class InspeksiMobil extends Model { protected $table='inspeksi_mobil';protected $primaryKey='id_inspeksi';protected $guarded=['id_inspeksi','id_pengajuan','id_petugas'];protected function casts():array{return ['tanggal_inspeksi'=>'datetime','kilometer'=>'integer','harga_penawaran'=>'decimal:2'];}public function pengajuan(){return $this->belongsTo(PengajuanMobil::class,'id_pengajuan','id_pengajuan');}public function petugas(){return $this->belongsTo(Petugas::class,'id_petugas','id_petugas');}}
