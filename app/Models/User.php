<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $guarded = ['id_user', 'password', 'last_login'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'id_role' => 'integer',
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'id_role', 'id_role');
    }

    public function petugas(): HasOne
    {
        return $this->hasOne(Petugas::class, 'id_user', 'id_user');
    }

    public function pelanggan(): HasOne
    {
        return $this->hasOne(Pelanggan::class, 'id_user', 'id_user');
    }

    public function displayName(): string
    {
        return $this->petugas?->nama_lengkap
            ?? $this->pelanggan?->nama_lengkap
            ?? $this->username;
    }

    public function roleName(): string
    {
        return strtolower((string) ($this->role?->nama_role ?? ''));
    }
}
