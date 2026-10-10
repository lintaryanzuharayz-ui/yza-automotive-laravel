<?php

namespace App\Support;

use App\Models\Mobil;

final class VehicleBadge
{
    /**
     * Split the vehicle's existing technical identity into a reusable catalog badge.
     * Values are derived from the vehicle record; no engine/spec data is invented.
     */
    public static function parts(Mobil $vehicle): array
    {
        $identity = strtolower(trim($vehicle->merk.' '.$vehicle->model));
        $type = trim((string) $vehicle->tipe);
        $type = $type !== '' ? $type : 'Collection';

        if (str_contains($identity, 'lexus lfa')) {
            return ['label' => 'Performance', 'value' => self::withoutWords($type, ['performance']) ?: $type];
        }

        if (str_contains($identity, 'nissan gt-r nismo')) {
            return ['label' => 'Nismo', 'value' => self::withoutWords($type, ['nismo']) ?: 'Performance'];
        }

        if (str_contains($identity, 'dodge challenger')) {
            return ['label' => 'Engine', 'value' => $type];
        }

        if (str_contains($identity, 'ford mustang shelby')) {
            return ['label' => 'Shelby', 'value' => self::withoutWords($type, ['shelby']) ?: $type];
        }

        if (str_contains($identity, 'bmw m')) {
            return ['label' => 'M', 'value' => $type];
        }

        if (str_contains($identity, 'mercedes amg')) {
            return ['label' => 'AMG', 'value' => self::withoutWords($type, ['amg']) ?: $type];
        }

        if (str_contains($identity, 'ferrari sf90') && str_contains(strtolower($type), 'assetto')) {
            return ['label' => 'Assetto', 'value' => self::withoutWords($type, ['assetto']) ?: $type];
        }

        if (str_contains($identity, 'bugatti chiron') && str_contains(strtolower($type), 'super sport')) {
            return ['label' => 'Super', 'value' => self::withoutWords($type, ['super']) ?: $type];
        }

        if (str_contains($identity, 'toyota gr supra')) {
            return ['label' => 'GR', 'value' => $type];
        }

        if (preg_match('/\bperformance\b/i', $type) === 1) {
            $value = self::withoutWords($type, ['performance']);
            return ['label' => 'Performance', 'value' => $value !== '' ? $value : $type];
        }

        return ['label' => 'Variant', 'value' => $type];
    }

    private static function withoutWords(string $value, array $words): string
    {
        $pattern = '/\s*\b(?:'.implode('|', array_map('preg_quote', $words)).')\b\s*/i';
        return trim((string) preg_replace($pattern, ' ', $value), " \t\n\r\0\x0B-");
    }
}
