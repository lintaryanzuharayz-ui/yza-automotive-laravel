<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInventoryHistory extends Model
{
    public $timestamps = false;
    protected $table = 'vehicle_inventory_histories';
    protected $primaryKey = 'id_vehicle_inventory_history';
    protected $guarded = ['id_vehicle_inventory_history'];
}
