<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    protected $table = 'sparepart';
    protected $primaryKey = 'id_sparepart';
    protected $guarded = ['id_sparepart', 'stok'];
    protected function casts(): array { return ['id_sparepart' => 'integer', 'id_kategori' => 'integer', 'id_supplier' => 'integer', 'harga_beli' => 'decimal:2', 'harga_jual' => 'decimal:2', 'stok' => 'integer', 'stok_minimum' => 'integer', 'harga_diperbarui_at' => 'datetime']; }
    public function kategori(): BelongsTo { return $this->belongsTo(KategoriSparepart::class, 'id_kategori', 'id_kategori'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier'); }
    public function fitments(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(SparepartFitment::class, 'id_sparepart', 'id_sparepart'); }
    public function stockHistory(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(RiwayatStok::class, 'id_sparepart', 'id_sparepart'); }
    public function servisItems(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(ServisSparepart::class, 'id_sparepart', 'id_sparepart'); }
    public function penjualanItems(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(DetailPenjualanSparepart::class, 'id_sparepart', 'id_sparepart'); }
    public function stockLevel(): string { return $this->stok === 0 ? 'habis' : ($this->stok <= $this->stok_minimum ? 'menipis' : 'aman'); }
    public function photoUrl(): ?string { return $this->foto ? asset('storage/uploads/sparepart/'.rawurlencode(basename($this->foto))) : null; }
}
