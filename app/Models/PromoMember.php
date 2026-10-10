<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoMember extends Model
{
    protected $table = 'promo_member';
    protected $primaryKey = 'id_promo';
    protected $guarded = ['id_promo', 'created_by'];
    protected function casts(): array
    {
        return ['nilai_benefit' => 'decimal:2', 'minimum_transaksi' => 'decimal:2', 'maksimum_benefit' => 'decimal:2', 'quota' => 'integer', 'usage_per_member' => 'integer', 'member_only' => 'boolean', 'projection_revenue'=>'decimal:2','projection_discount'=>'decimal:2','projection_margin'=>'decimal:2','estimated_cost_rate'=>'decimal:4','mulai_at' => 'datetime', 'selesai_at' => 'datetime'];
    }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by', 'id_user'); }
    public function isVisibleAt($at = null): bool
    {
        $at = $at ?: now();
        return $this->status === 'aktif' && $at->betweenIncluded($this->mulai_at, $this->selesai_at);
    }
    public function scopeVisible($query, $at = null)
    {
        $at = $at ?: now();
        return $query->where('status', 'aktif')->where('mulai_at', '<=', $at)->where('selesai_at', '>=', $at);
    }
}
