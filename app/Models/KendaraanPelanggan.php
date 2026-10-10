<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KendaraanPelanggan extends Model
{
    protected $table = 'kendaraan_pelanggan';
    protected $primaryKey = 'id_kendaraan_pelanggan';
    protected $guarded = ['id_kendaraan_pelanggan', 'id_pelanggan'];
    protected function casts(): array { return ['id_kendaraan_pelanggan' => 'integer', 'id_pelanggan' => 'integer', 'tahun' => 'integer', 'kilometer' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime']; }
    public function pelanggan(): BelongsTo { return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan'); }
    public function bookingServis(): HasMany { return $this->hasMany(BookingServis::class, 'id_kendaraan_pelanggan', 'id_kendaraan_pelanggan'); }
    public function bookingCuci(): HasMany { return $this->hasMany(BookingCuci::class, 'id_kendaraan_pelanggan', 'id_kendaraan_pelanggan'); }
    public function servis(): HasMany { return $this->hasMany(Servis::class, 'id_kendaraan_pelanggan', 'id_kendaraan_pelanggan'); }
    public function pengajuanMobil(): HasMany { return $this->hasMany(PengajuanMobil::class, 'id_kendaraan_pelanggan', 'id_kendaraan_pelanggan'); }
}
