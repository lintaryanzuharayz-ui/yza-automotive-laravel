<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DetailStokMasuk extends Model {
    public $timestamps=false; protected $table='detail_stok_masuk'; protected $primaryKey='id_detail_stok_masuk'; protected $guarded=['id_detail_stok_masuk'];
    protected function casts(): array { return ['id_stok_masuk'=>'integer','id_sparepart'=>'integer','jumlah'=>'integer','harga_beli'=>'decimal:2','subtotal'=>'decimal:2']; }
    public function stokMasuk(): BelongsTo { return $this->belongsTo(StokMasuk::class,'id_stok_masuk','id_stok_masuk'); }
    public function sparepart(): BelongsTo { return $this->belongsTo(Sparepart::class,'id_sparepart','id_sparepart'); }
}
