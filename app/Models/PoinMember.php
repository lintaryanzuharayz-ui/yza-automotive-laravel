<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoinMember extends Model
{
    protected $table = 'poin_member';
    protected $primaryKey = 'id_poin_member';
    protected $guarded = ['id_poin_member', 'id_pelanggan', 'saldo', 'total_diperoleh'];
    protected function casts(): array
    {
        return ['saldo' => 'integer', 'total_diperoleh' => 'integer'];
    }
    public function pelanggan(): BelongsTo { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function riwayat(): HasMany { return $this->hasMany(RiwayatPoin::class, 'id_pelanggan', 'id_pelanggan'); }
}
