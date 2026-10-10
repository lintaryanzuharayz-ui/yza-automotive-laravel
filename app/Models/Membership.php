<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};

class Membership extends Model
{
    protected $table = 'memberships';
    protected $primaryKey = 'id_membership';
    protected $guarded = ['id_membership', 'id_pelanggan', 'member_id', 'activation_fee'];

    protected function casts(): array
    {
        return ['activation_fee' => 'decimal:2', 'registered_at' => 'datetime', 'activated_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function gatewayPayment(): BelongsTo { return $this->belongsTo(PembayaranGateway::class, 'gateway_payment_id', 'id_pembayaran_gateway'); }
    public function gatewayPayments(): HasMany { return $this->hasMany(PembayaranGateway::class, 'id_membership', 'id_membership'); }
    public function isActive(): bool { return $this->status === 'active'; }
}
