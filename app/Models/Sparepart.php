<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $table = 'sparepart';
    protected $primaryKey = 'id_sparepart';
    protected $guarded = ['id_sparepart', 'stok'];
    protected function casts(): array { return ['id_sparepart' => 'integer', 'id_kategori' => 'integer', 'harga_beli' => 'decimal:2', 'harga_jual' => 'decimal:2', 'stok' => 'integer', 'stok_minimum' => 'integer']; }
    public function kategori(): BelongsTo { return $this->belongsTo(KategoriSparepart::class, 'id_kategori', 'id_kategori'); }
    public function servisItems(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(ServisSparepart::class, 'id_sparepart', 'id_sparepart'); }
    public function penjualanItems(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(DetailPenjualanSparepart::class, 'id_sparepart', 'id_sparepart'); }
    public function stockLevel(): string { return $this->stok === 0 ? 'habis' : ($this->stok <= $this->stok_minimum ? 'menipis' : 'aman'); }
    public function photoUrl(): ?string { return $this->foto ? asset('storage/uploads/sparepart/'.rawurlencode(basename($this->foto))) : null; }
}
