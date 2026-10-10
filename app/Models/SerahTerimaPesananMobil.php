<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerahTerimaPesananMobil extends Model
{
    protected $table = 'serah_terima_pesanan_mobil';
    protected $primaryKey = 'id_serah_terima';
    protected $guarded = ['id_serah_terima', 'id_pesanan'];

    protected function casts(): array
    {
        return ['pelunasan_terverifikasi' => 'boolean', 'dokumen_lengkap' => 'boolean', 'kendaraan_sesuai' => 'boolean', 'diserahkan_at' => 'datetime'];
    }

    public function pesanan() { return $this->belongsTo(PesananMobilMember::class, 'id_pesanan', 'id_pesanan'); }
}
