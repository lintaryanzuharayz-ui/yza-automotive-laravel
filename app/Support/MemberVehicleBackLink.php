<?php

namespace App\Support;

use Illuminate\Http\Request;

final class MemberVehicleBackLink
{
    public static function for(Request $request): array
    {
        $source = $request->query('source');
        $returnTo = $request->query('return_to');
        $parts = is_string($returnTo) && $returnTo !== ''
            && !preg_match('/[\x00-\x20\x7f\\\\]/', $returnTo)
            && !str_starts_with($returnTo, '//') ? parse_url($returnTo) : false;
        $safe = is_array($parts) && !array_intersect(
            ['scheme', 'host', 'user', 'pass', 'port'], array_keys($parts)
        );
        $path = $safe ? ($parts['path'] ?? '') : '';
        $homePaths = ['/pelanggan/member', '/pelanggan/dashboard'];

        if ($source === 'member-home' || in_array($path, $homePaths, true)) {
            $url = in_array($path, $homePaths, true) ? $returnTo : route('pelanggan.member');
            if (!str_contains($url, '#')) $url .= '#yza-home-garage-title';

            return ['url' => $url, 'label' => 'Kembali ke Beranda'];
        }

        return [
            'url' => $path === '/pelanggan/katalog-mobil' ? $returnTo : route('pelanggan.katalog-mobil.index'),
            'label' => 'Kembali ke Katalog Member',
        ];
    }
}
