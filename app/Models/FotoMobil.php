<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FotoMobil extends Model
{
    public $timestamps = false;
    protected $table = 'foto_mobil';
    protected $primaryKey = 'id_foto';
    protected $guarded = ['id_foto'];
    protected function casts(): array { return ['foto_utama' => 'boolean', 'urutan' => 'integer', 'created_at' => 'datetime']; }
    public function mobil(): BelongsTo { return $this->belongsTo(Mobil::class, 'id_mobil', 'id_mobil'); }
    public function storagePath(): string { return 'uploads/mobil/'.basename((string) $this->nama_file); }
    public function existsOnDisk(): bool { return Storage::disk('public')->exists($this->storagePath()); }
    public function url(): string { return url('storage/'.$this->storagePath()); }
}
