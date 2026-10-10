<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class StokMasuk extends Model {
    protected $table='stok_masuk'; protected $primaryKey='id_stok_masuk'; protected $guarded=['id_stok_masuk'];
    protected function casts(): array { return ['id_supplier'=>'integer','id_petugas'=>'integer','tanggal'=>'datetime','total_biaya'=>'decimal:2']; }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class,'id_supplier','id_supplier'); }
    public function petugas(): BelongsTo { return $this->belongsTo(Petugas::class,'id_petugas','id_petugas'); }
    public function detail(): HasMany { return $this->hasMany(DetailStokMasuk::class,'id_stok_masuk','id_stok_masuk'); }
}
