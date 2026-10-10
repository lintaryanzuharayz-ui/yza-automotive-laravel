<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobilWarna extends Model
{
    protected $table = 'mobil_warna';
    protected $primaryKey = 'id_warna';
    protected $guarded = ['id_warna'];
    protected function casts(): array { return ['is_default' => 'boolean', 'urutan' => 'integer']; }
    public function mobil(): BelongsTo { return $this->belongsTo(Mobil::class, 'id_mobil', 'id_mobil'); }
    public function galeri(): HasMany { return $this->hasMany(MobilGaleri::class, 'id_warna', 'id_warna')->orderBy('urutan'); }
}
