<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SparepartFitment extends Model
{
    protected $table = 'sparepart_fitments';
    protected $primaryKey = 'id_sparepart_fitment';
    protected $guarded = ['id_sparepart_fitment'];
    protected function casts(): array { return ['id_sparepart' => 'integer', 'tahun_mulai' => 'integer', 'tahun_akhir' => 'integer', 'verified_by' => 'integer', 'verified_at' => 'datetime']; }
    public function sparepart(): BelongsTo { return $this->belongsTo(Sparepart::class, 'id_sparepart', 'id_sparepart'); }
    public function verifier(): BelongsTo { return $this->belongsTo(Petugas::class, 'verified_by', 'id_petugas'); }
}
