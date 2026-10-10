<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobilGaleri extends Model
{
    protected $table = 'mobil_galeri';
    protected $primaryKey = 'id_galeri';
    protected $guarded = ['id_galeri'];
    public function warna(): BelongsTo { return $this->belongsTo(MobilWarna::class, 'id_warna', 'id_warna'); }
}
