<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceQualityControlItem extends Model
{
    protected $table = 'service_quality_control_items';
    protected $primaryKey = 'id_service_quality_control_item';
    protected $guarded = ['id_service_quality_control_item'];
    protected $casts = ['is_mandatory' => 'boolean'];
    public function qualityControl() { return $this->belongsTo(ServiceQualityControl::class, 'quality_control_id', 'id_service_quality_control'); }
}
