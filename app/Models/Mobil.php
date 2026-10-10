<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyRelation;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    protected $table = 'mobil';
    protected $primaryKey = 'id_mobil';
    protected $guarded = ['id_mobil'];

    protected function casts(): array
    {
        return ['tahun' => 'integer', 'kilometer' => 'integer', 'harga_beli' => 'decimal:2', 'harga_jual' => 'decimal:2', 'tanggal_masuk' => 'date', 'id_mobil' => 'integer'];
    }

    public function getRouteKeyName(): string { return 'id_mobil'; }
    public function foto(): HasMany { return $this->hasMany(FotoMobil::class, 'id_mobil', 'id_mobil')->orderBy('foto_utama', 'desc')->orderBy('urutan')->orderBy('id_foto'); }
    public function fotoUtama(): HasMany { return $this->foto()->where('foto_utama', true); }
    public function primaryPhoto(): ?FotoMobil { return $this->foto()->first(); }
    public function warnaPilihan(): HasMany { return $this->hasMany(MobilWarna::class, 'id_mobil', 'id_mobil')->orderBy('urutan'); }
    public function bookings(): HasManyRelation { return $this->hasMany(BookingMobil::class, 'id_mobil', 'id_mobil'); }
    public function testDrives(): HasManyRelation { return $this->hasMany(TestDrive::class, 'id_mobil', 'id_mobil'); }
    public function penjualan(): HasManyRelation { return $this->hasMany(PenjualanMobil::class, 'id_mobil', 'id_mobil'); }
}
