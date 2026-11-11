<?php

namespace App\Services;

use App\Models\Mobil;
use Illuminate\Support\Collection;

class VehicleAvailabilityService
{
    public function for(Mobil $car): array
    {
        $state = app(VehicleColorStockService::class)->state($car);
        return array_replace($this->statusForStock($state['stock']), $state);
    }

    public function decorate(iterable $cars): Collection
    {
        return collect($cars)->map(function (Mobil $car): Mobil {
            $car->setAttribute('availability', $this->for($car));
            return $car;
        });
    }

    private function statusForStock(int $stock): array
    {
        if ($stock === 0) return ['stock' => 0, 'label' => 'STOK HABIS', 'detail' => 'Pre-order tersedia', 'status_class' => 'is-empty', 'can_preorder' => true, 'preorder_reason' => 'stok_habis'];
        if ($stock === 1) return ['stock' => 1, 'label' => 'SISA 1 UNIT', 'detail' => 'Sisa 1 Unit', 'status_class' => 'is-last'];
        if ($stock <= 3) return ['stock' => $stock, 'label' => 'STOK MENIPIS · SISA '.$stock.' UNIT', 'detail' => 'Sisa '.$stock.' Unit', 'status_class' => 'is-low'];
        return ['stock' => $stock, 'label' => 'TERSEDIA · '.$stock.' UNIT', 'detail' => 'Stok '.$stock.' Unit', 'status_class' => 'is-available'];
    }

}
