<?php

namespace App\Services;

use App\Models\Servis;

class WorkOrderEstimateService
{
    public function calculate(Servis $servis): array
    {
        $servis->loadMissing(['details', 'sparepartItems', 'transaksi']);

        if ($servis->transaksi) {
            $final = (float) $servis->transaksi->total;

            return [
                'labor' => (float) $servis->transaksi->subtotal - (float) $servis->transaksi->diskon,
                'parts' => (float) $servis->sparepartItems->sum('subtotal'),
                'other' => 0.0,
                'total' => $final,
                'is_final' => true,
            ];
        }

        $labor = (float) $servis->details->sum('biaya');
        $parts = (float) $servis->sparepartItems->sum('subtotal');

        return [
            'labor' => $labor,
            'parts' => $parts,
            'other' => 0.0,
            'total' => $labor + $parts,
            'is_final' => false,
        ];
    }
}
