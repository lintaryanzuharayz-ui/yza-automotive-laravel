<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceExecutionEvent extends Model
{
    public $timestamps = false;
    protected $table = 'service_execution_events';
    protected $primaryKey = 'id_service_execution_event';
    protected $guarded = ['id_service_execution_event'];
    protected function casts(): array { return ['metadata' => 'array', 'occurred_at' => 'datetime']; }
    public function servis() { return $this->belongsTo(Servis::class, 'id_servis', 'id_servis'); }
}
