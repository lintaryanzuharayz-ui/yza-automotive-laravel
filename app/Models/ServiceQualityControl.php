<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceQualityControl extends Model
{
    protected $table = 'service_quality_controls';
    protected $primaryKey = 'id_service_quality_control';
    protected $guarded = ['id_service_quality_control'];
    protected function casts(): array { return ['checked_at' => 'datetime']; }
    public function servis() { return $this->belongsTo(Servis::class, 'id_servis', 'id_servis'); }
    public function items() { return $this->hasMany(ServiceQualityControlItem::class, 'quality_control_id', 'id_service_quality_control'); }
}
