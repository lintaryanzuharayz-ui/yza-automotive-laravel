<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $table = 'sales_targets';
    protected $primaryKey = 'id_sales_target';
    protected $guarded = ['id_sales_target'];

    protected function casts(): array
    {
        return ['periode_mulai' => 'date', 'periode_selesai' => 'date', 'target_nominal' => 'decimal:2'];
    }

    public function sales() { return $this->belongsTo(Petugas::class, 'id_sales', 'id_petugas'); }
}
