<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $guarded = ['id_pelanggan'];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kendaraan(): HasMany
    {
        return $this->hasMany(KendaraanPelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
    public function bookings(): HasMany { return $this->hasMany(BookingMobil::class, 'id_pelanggan', 'id_pelanggan'); }
    public function testDrives(): HasMany { return $this->hasMany(TestDrive::class, 'id_pelanggan', 'id_pelanggan'); }
    public function bookingServis(): HasMany { return $this->hasMany(BookingServis::class, 'id_pelanggan', 'id_pelanggan'); }
    public function bookingCuci(): HasMany { return $this->hasMany(BookingCuci::class, 'id_pelanggan', 'id_pelanggan'); }
    public function servis(): HasMany { return $this->hasMany(Servis::class, 'id_pelanggan', 'id_pelanggan'); }
    public function transaksi(): HasMany { return $this->hasMany(Transaksi::class, 'id_pelanggan', 'id_pelanggan'); }
    public function penjualanMobil(): HasMany { return $this->hasMany(PenjualanMobil::class, 'id_pelanggan', 'id_pelanggan'); }
    public function pengajuanMobil(): HasMany { return $this->hasMany(PengajuanMobil::class, 'id_pelanggan', 'id_pelanggan'); }
    public function poinMember(): HasOne { return $this->hasOne(PoinMember::class, 'id_pelanggan', 'id_pelanggan'); }
    public function riwayatPoin(): HasMany { return $this->hasMany(RiwayatPoin::class, 'id_pelanggan', 'id_pelanggan'); }
    public function membership(): HasOne { return $this->hasOne(Membership::class, 'id_pelanggan', 'id_pelanggan'); }
}
