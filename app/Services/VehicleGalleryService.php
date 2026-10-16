<?php

namespace App\Services;

use App\Models\Mobil;

class VehicleGalleryService
{
    /**
     * Presentation-only gallery configuration. Business tables remain unchanged.
     */
    public function for(Mobil $mobil, ?string $showcaseImage = null): array
    {
        $structured = $this->structuredGallery($mobil);
        if ($structured) return $structured;
        $key = $this->vehicleKey($mobil);
        $fallback = $showcaseImage ?: $this->databaseImage($mobil);
        $config = $this->configs()[$key] ?? null;

        if (!$config) {
            return ['default' => [
                'label' => $mobil->warna ?: 'Pilihan warna tersedia',
                'swatch' => '#20a9c7',
                'images' => [['type' => 'front', 'label' => 'Front view', 'src' => $fallback]],
            ]];
        }

        $resolved = [];
        foreach ($config['colors'] as $colorKey => $color) {
            $resolved[$colorKey] = [
                'label' => $color['label'],
                'swatch' => $color['swatch'],
                'images' => array_map(fn (array $image) => [
                    'type' => $image['type'],
                    'label' => $image['label'],
                    'src' => $this->asset($image['path']) ?: ($image['type'] === 'interior'
                        ? ($config['interiorFallback'] ?? $fallback)
                        : ($color['fallback'] ?? $fallback)),
                ], $color['images']),
            ];
        }

        return $resolved;
    }

    /**
     * Resolve the gallery for an already selected/purchased color.
     * An empty result is intentional: callers must not silently present another color.
     */
    public function forColor(Mobil $mobil, ?string $colorLabel): array
    {
        $needle = strtolower(trim((string) $colorLabel));
        if ($needle === '') return [];

        foreach ($this->for($mobil) as $variant) {
            if (strtolower(trim((string) ($variant['label'] ?? ''))) === $needle) {
                return $variant;
            }
        }

        return [];
    }

    public function defaultColor(Mobil $mobil): string
    {
        $colors = $mobil->warnaPilihan()->with('galeri')->get();
        $default = $colors->firstWhere('is_default', true) ?: $colors->first();
        if ($default) return (string) $default->slug;
        $config = $this->configs()[$this->vehicleKey($mobil)] ?? null;
        return $config['defaultColor'] ?? 'default';
    }

    public function isReady(Mobil $mobil): bool
    {
        $colors = $mobil->warnaPilihan()->with('galeri')->get();
        $default = $colors->firstWhere('is_default', true);
        if (!$default) return false;
        foreach (['front', 'side', 'rear', 'interior'] as $angle) {
            $image = $default->galeri->firstWhere('sudut', $angle);
            if (!$image || !is_file(public_path($image->path)) || filesize(public_path($image->path)) < 1) return false;
        }
        return true;
    }

    public function defaultImage(Mobil $mobil): ?string
    {
        $colors = $mobil->warnaPilihan()->with('galeri')->get();
        $default = $colors->firstWhere('is_default', true);
        $image = $default?->galeri->firstWhere('sudut', 'front');
        return $image && is_file(public_path($image->path)) && filesize(public_path($image->path)) > 0 ? asset($image->path) : null;
    }

    private function structuredGallery(Mobil $mobil): array
    {
        $colors = $mobil->warnaPilihan()->with('galeri')->get();
        if ($colors->isEmpty()) return [];
        $result = [];
        foreach ($colors as $color) {
            $images = [];
            foreach ($color->galeri as $image) {
                $src = $this->asset($image->path);
                if (!$src) continue;
                $images[] = ['type' => $image->sudut, 'label' => match ($image->sudut) { 'front' => 'Front 3/4', 'side' => 'Side profile', 'rear' => 'Rear 3/4', default => 'Interior / cockpit' }, 'src' => $src];
            }
            if (count($images) === 4) $result[$color->slug] = ['label' => $color->nama, 'swatch' => $color->hex, 'images' => $images];
        }
        return $result;
    }

    private function vehicleKey(Mobil $mobil): string
    {
        $identity = strtolower(trim(($mobil->merk ?? '').' '.($mobil->model ?? '').' '.($mobil->tipe ?? '')));
        return match (true) {
            str_contains($identity, 'civic') => 'honda-civic',
            str_contains($identity, 'raize') => 'toyota-raize',
            str_contains($identity, 'fortuner') => 'toyota-fortuner',
            str_contains($identity, 'xl7') => 'suzuki-xl7',
            str_contains($identity, 'xpander') => 'mitsubishi-xpander',
            str_contains($identity, 'hr-v') || str_contains($identity, 'hrv') => 'honda-hrv',
            str_contains($identity, 'brio') => 'honda-brio',
            str_contains($identity, 'avanza') => 'toyota-avanza',
            str_contains($identity, 'mobilio') => 'honda-mobilio',
            str_contains($identity, 'xenia') => 'daihatsu-xenia',
            str_contains($identity, 'innova') && str_contains($identity, 'zenix') => 'toyota-innova-zenix',
            str_contains($identity, 'supra mk4') || str_contains(strtolower((string) $mobil->kode_mobil), 'supra-mk4') || str_contains($identity, 'supra 3.0l a/t') => 'toyota-supra-mk4',
            str_contains($identity, 'toyota gr supra') => 'toyota-gr-supra',
            str_contains($identity, 'audi') && str_contains($identity, 'r8') => 'audi-r8',
            str_contains($identity, 'bmw') && str_contains($identity, 'm4') => 'bmw-m4',
            str_contains($identity, 'bugatti') && str_contains($identity, 'chiron') => 'bugatti-chiron',
            str_contains($identity, 'mclaren') && str_contains($identity, '720') => 'mclaren-720s',
            default => (string) ($mobil->kode_mobil ?: 'default'),
        };
    }

    private function databaseImage(Mobil $mobil): string
    {
        $photo = $mobil->primaryPhoto();
        return $photo && $photo->existsOnDisk()
            ? $photo->url()
            : asset('assets/images/hero/yza-hero-reference-v3.png');
    }

    private function asset(string $path): ?string
    {
        return file_exists(public_path($path)) ? asset($path) : null;
    }

    private function exterior(string $folder, string $color, string $fallback): array
    {
        return [
            'fallback' => $fallback,
            'images' => [
                ['type' => 'front', 'label' => 'Front 3/4', 'path' => "assets/images/vehicles/{$folder}/{$color}/front.png"],
                ['type' => 'side', 'label' => 'Side profile', 'path' => "assets/images/vehicles/{$folder}/{$color}/side.png"],
                ['type' => 'rear', 'label' => 'Rear 3/4', 'path' => "assets/images/vehicles/{$folder}/{$color}/rear.png"],
                ['type' => 'interior', 'label' => 'Interior / cockpit', 'path' => "assets/images/vehicles/{$folder}/interior/cockpit.png"],
            ],
        ];
    }

    private function configs(): array
    {
        $raizeFallback = asset('assets/images/catalog/inventory/toyota-raize.png');
        $fortunerFallback = asset('assets/images/catalog/inventory/toyota-fortuner.png');
        $xl7Fallback = asset('assets/images/catalog/inventory/suzuki-xl7.png');
        $xpanderFallback = asset('assets/images/vehicles/xpander/gray/front.png');
        $hrvFallback = asset('assets/images/vehicles/hrv/white/front.png');
        $brioFallback = asset('assets/images/vehicles/brio/orange/front.png');
        $avanzaFallback = asset('assets/images/vehicles/avanza/white/front.png');
        $mobilioFallback = asset('assets/images/vehicles/mobilio/blue/front.png');
        $xeniaFallback = asset('assets/images/vehicles/xenia/silver/front.png');
        $innovaFallback = asset('assets/images/vehicles/innova-zenix/silver/front.png');
        $civicFallback = asset('assets/images/catalog/inventory/honda-civic.png');
        $sportFallbacks = [
            'audi-r8' => asset('assets/images/catalog/sport/audi-r8.png'),
            'bmw-m4' => asset('assets/images/catalog/sport/bmw-m4.png'),
            'bugatti-chiron' => asset('assets/images/catalog/sport/bugatti-chiron.png'),
            'mclaren-720s' => asset('assets/images/catalog/sport/mclaren-720s.png'),
        ];

        return [
            'honda-civic' => [
                'defaultColor' => 'red',
                'interiorFallback' => $civicFallback,
                'colors' => [
                    'red' => ['label' => 'Ignite Red', 'swatch' => '#c91f2b', ...$this->exterior('civic', 'red', $civicFallback)],
                    'blue' => ['label' => 'Sky Blue', 'swatch' => '#9dc9ee', ...$this->exterior('civic', 'blue', $civicFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('civic', 'black', $civicFallback)],
                ],
            ],
            'toyota-raize' => [
                'defaultColor' => 'blue',
                'interiorFallback' => asset('assets/images/vehicles/raize/interior/cockpit.png'),
                'colors' => [
                    'blue' => ['label' => 'Sky Blue', 'swatch' => '#20b9df', ...$this->exterior('raize', 'blue', $raizeFallback)],
                    'red' => ['label' => 'Crimson Red', 'swatch' => '#bd1821', ...$this->exterior('raize', 'red', $raizeFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('raize', 'black', $raizeFallback)],
                ],
            ],
            'toyota-fortuner' => [
                'defaultColor' => 'blue',
                'interiorFallback' => asset('assets/images/vehicles/fortuner/interior/cockpit.png'),
                'colors' => [
                    'blue' => ['label' => 'Sky Blue', 'swatch' => '#9dc9ee', ...$this->exterior('fortuner', 'blue', $fortunerFallback)],
                    'red' => ['label' => 'Ignite Red', 'swatch' => '#c91f2b', ...$this->exterior('fortuner', 'red', $fortunerFallback)],
                    'black' => ['label' => 'Attitude Black', 'swatch' => '#15171b', ...$this->exterior('fortuner', 'black', $fortunerFallback)],
                ],
            ],
            'suzuki-xl7' => [
                'defaultColor' => 'maroon',
                'interiorFallback' => asset('assets/images/vehicles/xl7/interior/cockpit.png'),
                'colors' => [
                    'maroon' => ['label' => 'Brave Maroon', 'swatch' => '#70232b', ...$this->exterior('xl7', 'maroon', $xl7Fallback)],
                    'sky-blue' => ['label' => 'Sky Blue', 'swatch' => '#9dc9ee', ...$this->exterior('xl7', 'sky-blue', $xl7Fallback)],
                    'navy' => ['label' => 'Deep Navy Blue', 'swatch' => '#17345d', ...$this->exterior('xl7', 'navy', $xl7Fallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('xl7', 'black', $xl7Fallback)],
                    'gray' => ['label' => 'Graphite Gray', 'swatch' => '#737981', ...$this->exterior('xl7', 'gray', $xl7Fallback)],
                ],
            ],
            'mitsubishi-xpander' => [
                'defaultColor' => 'gray',
                'interiorFallback' => asset('assets/images/vehicles/xpander/interior/cockpit.png'),
                'colors' => [
                    'gray' => ['label' => 'Graphite Gray', 'swatch' => '#737981', ...$this->exterior('xpander', 'gray', $xpanderFallback)],
                    'maroon' => ['label' => 'Maroon Red', 'swatch' => '#72232d', ...$this->exterior('xpander', 'maroon', $xpanderFallback)],
                    'white' => ['label' => 'Pearl White', 'swatch' => '#f1f1ed', ...$this->exterior('xpander', 'white', $xpanderFallback)],
                    'navy' => ['label' => 'Deep Navy Blue', 'swatch' => '#1e355a', ...$this->exterior('xpander', 'navy', $xpanderFallback)],
                ],
            ],
            'honda-hrv' => [
                'defaultColor' => 'white',
                'interiorFallback' => asset('assets/images/vehicles/hrv/interior/cockpit.png'),
                'colors' => [
                    'white' => ['label' => 'Platinum White Pearl', 'swatch' => '#f1f1ed', ...$this->exterior('hrv', 'white', $hrvFallback)],
                    'red' => ['label' => 'Ignite Red', 'swatch' => '#c51f2b', ...$this->exterior('hrv', 'red', $hrvFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('hrv', 'black', $hrvFallback)],
                    'teal' => ['label' => 'Deep Teal', 'swatch' => '#155b61', ...$this->exterior('hrv', 'teal', $hrvFallback)],
                ],
            ],
            'honda-brio' => [
                'defaultColor' => 'orange',
                'interiorFallback' => asset('assets/images/vehicles/brio/interior/cockpit.png'),
                'colors' => [
                    'orange' => ['label' => 'Phoenix Orange Pearl', 'swatch' => '#f26a18', ...$this->exterior('brio', 'orange', $brioFallback)],
                    'white' => ['label' => 'Pearl White', 'swatch' => '#f1f1ed', ...$this->exterior('brio', 'white', $brioFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('brio', 'black', $brioFallback)],
                    'navy' => ['label' => 'Deep Navy Blue', 'swatch' => '#1e355c', ...$this->exterior('brio', 'navy', $brioFallback)],
                ],
            ],
            'toyota-avanza' => [
                'defaultColor' => 'white',
                'interiorFallback' => asset('assets/images/vehicles/avanza/interior/cockpit.png'),
                'colors' => [
                    'white' => ['label' => 'White Pearl', 'swatch' => '#f3f3ef', ...$this->exterior('avanza', 'white', $avanzaFallback)],
                    'red' => ['label' => 'Crimson Red', 'swatch' => '#b51f2b', ...$this->exterior('avanza', 'red', $avanzaFallback)],
                    'navy' => ['label' => 'Deep Navy Blue', 'swatch' => '#1f365f', ...$this->exterior('avanza', 'navy', $avanzaFallback)],
                    'gray' => ['label' => 'Graphite Gray', 'swatch' => '#737981', ...$this->exterior('avanza', 'gray', $avanzaFallback)],
                ],
            ],
            'honda-mobilio' => [
                'defaultColor' => 'blue',
                'interiorFallback' => asset('assets/images/vehicles/mobilio/interior/cockpit.png'),
                'colors' => [
                    'blue' => ['label' => 'Deep Metallic Blue', 'swatch' => '#245a91', ...$this->exterior('mobilio', 'blue', $mobilioFallback)],
                    'red' => ['label' => 'Crimson Red', 'swatch' => '#b51f2b', ...$this->exterior('mobilio', 'red', $mobilioFallback)],
                    'silver' => ['label' => 'Silver Metallic', 'swatch' => '#c7cbd0', ...$this->exterior('mobilio', 'silver', $mobilioFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('mobilio', 'black', $mobilioFallback)],
                ],
            ],
            'daihatsu-xenia' => [
                'defaultColor' => 'silver',
                'interiorFallback' => asset('assets/images/vehicles/xenia/interior/cockpit.png'),
                'colors' => [
                    'silver' => ['label' => 'Silver Metallic', 'swatch' => '#c7cbd0', ...$this->exterior('xenia', 'silver', $xeniaFallback)],
                    'red' => ['label' => 'Crimson Red', 'swatch' => '#b51f2b', ...$this->exterior('xenia', 'red', $xeniaFallback)],
                    'navy' => ['label' => 'Deep Navy Blue', 'swatch' => '#1f365f', ...$this->exterior('xenia', 'navy', $xeniaFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('xenia', 'black', $xeniaFallback)],
                ],
            ],
            'toyota-innova-zenix' => [
                'defaultColor' => 'silver',
                'interiorFallback' => asset('assets/images/vehicles/innova-zenix/interior/cockpit.png'),
                'colors' => [
                    'silver' => ['label' => 'Silver Metallic', 'swatch' => '#c7cbd0', ...$this->exterior('innova-zenix', 'silver', $innovaFallback)],
                    'maroon' => ['label' => 'Maroon Red', 'swatch' => '#72232d', ...$this->exterior('innova-zenix', 'maroon', $innovaFallback)],
                    'black' => ['label' => 'Obsidian Black', 'swatch' => '#15171b', ...$this->exterior('innova-zenix', 'black', $innovaFallback)],
                    'green' => ['label' => 'Forest Green', 'swatch' => '#244b3c', ...$this->exterior('innova-zenix', 'green', $innovaFallback)],
                ],
            ],
            'toyota-gr-supra' => $this->sportConfig('toyota-gr-supra', 'red', 'Supra Red', '#d51e2a', asset('assets/images/catalog/toyota-gr-supra/front.png'), [
                'black' => ['Obsidian Black', '#15171b'],
                'gray' => ['Graphite Gray', '#6d737b'],
                'white' => ['Pearl White', '#f1f1ee'],
                'navy' => ['Deep Navy Blue', '#142b52'],
            ]),
            'toyota-supra-mk4' => $this->supraMk4Config(),
            'audi-r8' => $this->sportConfig('audi-r8', 'nardo-silver', 'Nardo Silver', '#c5c9ce', $sportFallbacks['audi-r8'], ['mythos-black' => ['Mythos Black', '#15171a'], 'tango-red' => ['Tango Red', '#b51e25'], 'ascari-blue' => ['Ascari Blue', '#245d9b']]),
            'bmw-m4' => $this->sportConfig('bmw-m4', 'toronto-red', 'Toronto Red', '#b51c25', $sportFallbacks['bmw-m4'], ['alpine-white' => ['Alpine White', '#eeeeeb'], 'black-sapphire' => ['Black Sapphire', '#15171b'], 'portimao-blue' => ['Portimao Blue', '#1f5b9e']]),
            'bugatti-chiron' => $this->sportConfig('bugatti-chiron', 'atlantic-blue', 'Atlantic Blue', '#145b9e', $sportFallbacks['bugatti-chiron'], ['carbon-black' => ['Carbon Black', '#151618'], 'racing-red' => ['Racing Red', '#c51d2b'], 'glacier-white' => ['Glacier White', '#f2f2ee']]),
            'mclaren-720s' => $this->sportConfig('mclaren-720s', 'papaya-spark', 'Papaya Spark', '#f47b18', $sportFallbacks['mclaren-720s'], ['onyx-black' => ['Onyx Black', '#151618'], 'silica-white' => ['Silica White', '#eeeeeb'], 'vega-blue' => ['Vega Blue', '#2370a9'], 'racing-red' => ['Racing Red', '#c51d2b']]),
        ];
    }

    private function sportConfig(string $folder, string $default, string $defaultLabel, string $swatch, string $fallback, array $extra): array
    {
        $colors = [$default => ['label' => $defaultLabel, 'swatch' => $swatch]];
        foreach ($extra as $key => [$label, $color]) $colors[$key] = ['label' => $label, 'swatch' => $color];
        foreach ($colors as $key => &$color) $color += $this->exterior($folder, $key, $fallback);
        return ['defaultColor' => $default, 'interiorFallback' => $fallback, 'colors' => $colors];
    }

    private function supraMk4Config(): array
    {
        $config = $this->sportConfig('toyota-supra-mk4', 'red', 'Supra Red', '#c9212b', asset('assets/images/catalog/toyota-supra-mk4/front.png'), [
            'orange' => ['Burnt Orange', '#c7651c'],
            'white' => ['Pearl White', '#f1f1ee'],
            'green' => ['Racing Green', '#1c4f3a'],
            'black' => ['Jet Black', '#15171b'],
            'gray' => ['Gunmetal Grey', '#6d737b'],
        ]);

        // The existing rear files were stored under the wrong color folders.
        // Keep the stable color keys, but explicitly point each variant to the
        // existing rear asset that contains the matching vehicle color.
        $rearAssetByColor = [
            'green' => 'gray',  // green vehicle is currently in gray/rear.png
            'gray' => 'black',  // gray vehicle is currently in black/rear.png
            'black' => 'green', // black vehicle is currently in green/rear.png
        ];

        foreach ($rearAssetByColor as $colorKey => $assetColor) {
            foreach ($config['colors'][$colorKey]['images'] as &$image) {
                if ($image['type'] === 'rear') {
                    $image['path'] = "assets/images/vehicles/toyota-supra-mk4/{$assetColor}/rear.png";
                    break;
                }
            }
            unset($image);
        }

        return $config;
    }
}
