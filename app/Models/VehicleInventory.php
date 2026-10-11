<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleInventory extends Model
{
    protected $table = 'vehicle_inventories';
    protected $primaryKey = 'id_vehicle_inventory';
    protected $guarded = ['id_vehicle_inventory'];

    protected function casts(): array
    {
        return ['stock_total' => 'integer', 'sold_units' => 'integer'];
    }

    public function mobil() { return $this->belongsTo(Mobil::class, 'id_mobil', 'id_mobil'); }
    public function histories() { return $this->hasMany(VehicleInventoryHistory::class, 'id_vehicle_inventory', 'id_vehicle_inventory'); }
}
