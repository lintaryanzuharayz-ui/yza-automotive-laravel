<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdiPesananMobil extends Model
{
    protected $table = 'pdi_pesanan_mobil';
    protected $primaryKey = 'id_pdi';
    protected $guarded = ['id_pdi', 'id_pesanan'];

    protected function casts(): array
    {
        return array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], 'boolean') + ['diperiksa_at' => 'datetime'];
    }

    public function pesanan() { return $this->belongsTo(PesananMobilMember::class, 'id_pesanan', 'id_pesanan'); }
}
