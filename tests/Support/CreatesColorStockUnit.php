<?php

namespace Tests\Support;

use App\Models\Mobil;
use App\Services\{CatalogVehicleIdentityService, VehicleColorStockService};

/** Synthetic identities are confined to the isolated testing database. */
trait CreatesColorStockUnit
{
    private function colorStockUnit(Mobil $template, ?string $requested = null): Mobil
    {
        $color = app(VehicleColorStockService::class)->selected($template, $requested ?: $template->warna);
        $unit = $template->replicate(); $suffix = bin2hex(random_bytes(6));
        $unit->forceFill(['kode_mobil' => 'QA-COLOR-'.$suffix, 'nomor_rangka' => 'QA-COLOR-VIN-'.$suffix,
            'nomor_mesin' => 'QA-COLOR-ENGINE-'.$suffix, 'warna' => $color['label'], 'status' => 'tersedia', 'showcase_slug' => null,
            'catalog_identity' => app(CatalogVehicleIdentityService::class)->key($template)])->save();
        foreach ($template->warnaPilihan()->with('galeri')->get() as $master) {
            $copy = $master->replicate(); $copy->id_mobil = $unit->getKey(); $copy->save();
            foreach ($master->galeri as $photo) { $image = $photo->replicate(); $image->id_warna = $copy->getKey(); $image->save(); }
        }
        return $unit;
    }
}
