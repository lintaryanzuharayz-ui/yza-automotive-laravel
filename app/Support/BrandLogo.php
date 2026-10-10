<?php

namespace App\Support;

use Illuminate\Support\Str;

final class BrandLogo
{
    private const MAP = [
        'aston martin' => 'aston-martin.svg',
        'audi' => 'audi.svg',
        'bmw' => 'bmw.svg',
        'bugatti' => 'bugatti.svg',
        'chevrolet' => 'chevrolet.svg',
        'daihatsu' => 'daihatsu.svg',
        'dodge' => 'dodge.svg',
        'ferrari' => 'ferrari.svg',
        'ford' => 'ford.svg',
        'honda' => 'honda.svg',
        'lamborghini' => 'lamborghini.svg',
        'lexus' => 'lexus.svg',
        'mclaren' => 'mclaren.svg',
        'mercedes amg' => 'mercedes-amg.svg',
        'mercedes-benz' => 'mercedes-amg.svg',
        'mercedes benz' => 'mercedes-amg.svg',
        'mitsubishi' => 'mitsubishi.svg',
        'nissan' => 'nissan.svg',
        'porsche' => 'porsche.svg',
        'suzuki' => 'suzuki.svg',
        'toyota' => 'toyota.svg',
    ];

    public static function path(?string $brand, ?string $model = null): string
    {
        $key = self::normalize($brand ?: '').' '.self::normalize($model ?: '');
        foreach (self::MAP as $name => $file) {
            if (Str::contains($key, $name)) {
                return asset('assets/images/brands/'.$file);
            }
        }

        return asset('assets/images/brands/yza.svg');
    }

    public static function key(?string $brand, ?string $model = null): string
    {
        $path = self::path($brand, $model);
        return basename(parse_url($path, PHP_URL_PATH) ?: 'yza.svg', '.svg');
    }

    private static function normalize(string $value): string
    {
        return preg_replace('/[^a-z0-9]+/i', ' ', Str::lower(trim($value))) ?: '';
    }
}
