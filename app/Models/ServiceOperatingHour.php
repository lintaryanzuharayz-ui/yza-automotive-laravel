<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOperatingHour extends Model
{
    protected $table = 'service_operating_hours';
    protected $primaryKey = 'id_service_operating_hour';
    protected $guarded = ['id_service_operating_hour'];
    protected function casts(): array { return ['is_open' => 'boolean', 'day_of_week' => 'integer', 'slot_interval_minutes' => 'integer']; }
}
