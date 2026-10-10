<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Petugas extends Model
{
    protected $table = 'petugas';
    protected $primaryKey = 'id_petugas';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $guarded = ['id_petugas'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
    public function confirmedBookings(): HasMany { return $this->hasMany(BookingMobil::class, 'id_petugas_konfirmasi', 'id_petugas'); }
    public function testDrives(): HasMany { return $this->hasMany(TestDrive::class, 'id_petugas', 'id_petugas'); }
    public function bookingCuci(): HasMany { return $this->hasMany(BookingCuci::class, 'id_petugas', 'id_petugas'); }
    public function servis(): HasMany { return $this->hasMany(Servis::class, 'id_petugas', 'id_petugas'); }
    public function transaksi(): HasMany { return $this->hasMany(Transaksi::class, 'id_petugas', 'id_petugas'); }
    public function penjualanMobil(): HasMany { return $this->hasMany(PenjualanMobil::class, 'id_petugas', 'id_petugas'); }
    public function inspeksiMobil(): HasMany { return $this->hasMany(InspeksiMobil::class, 'id_petugas', 'id_petugas'); }
}
