<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSparepart extends Model
{
    protected $table = 'kategori_sparepart';
    protected $primaryKey = 'id_kategori';
    protected $guarded = ['id_kategori'];
    protected function casts(): array { return ['id_kategori' => 'integer']; }
    public function sparepart(): HasMany { return $this->hasMany(Sparepart::class, 'id_kategori', 'id_kategori'); }
}
