<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPoin extends Model
{
    protected $table = 'riwayat_poin';
    protected $primaryKey = 'id_riwayat_poin';
    protected $guarded = ['id_riwayat_poin', 'id_pelanggan', 'id_transaksi', 'jenis', 'perubahan_poin', 'saldo_setelah', 'idempotency_key'];
    protected function casts(): array { return ['perubahan_poin' => 'integer', 'saldo_setelah' => 'integer']; }
    public function pelanggan(): BelongsTo { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function transaksi(): BelongsTo { return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi'); }
}
