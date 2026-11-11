<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBlackoutDate extends Model
{
    protected $table = 'service_blackout_dates';
    protected $primaryKey = 'id_service_blackout_date';
    protected $guarded = ['id_service_blackout_date'];
    protected function casts(): array { return ['date' => 'date', 'is_closed' => 'boolean']; }
}
