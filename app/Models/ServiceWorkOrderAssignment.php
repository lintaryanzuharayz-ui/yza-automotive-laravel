<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceWorkOrderAssignment extends Model
{
    protected $table = 'service_work_order_assignments';
    protected $primaryKey = 'id_service_work_order_assignment';
    protected $guarded = ['id_service_work_order_assignment'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'unassigned_at' => 'datetime'];
    }

    public function servis() { return $this->belongsTo(Servis::class, 'id_servis', 'id_servis'); }
    public function petugas() { return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas'); }
}
