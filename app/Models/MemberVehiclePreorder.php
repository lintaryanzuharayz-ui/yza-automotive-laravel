<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberVehiclePreorder extends Model
{
    protected $table = 'pre_order_mobil_member';
    protected $primaryKey = 'id_pre_order';
    protected $guarded = ['id_pre_order'];

    protected function casts(): array
    {
        return ['harga_referensi' => 'decimal:2', 'notified_at' => 'datetime', 'cancelled_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function pelanggan(): BelongsTo { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function mobil(): BelongsTo { return $this->belongsTo(Mobil::class, 'id_mobil', 'id_mobil'); }
}
