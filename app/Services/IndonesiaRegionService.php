<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class IndonesiaRegionService
{
    private string $directory;

    public function __construct()
    {
        $this->directory = resource_path('data/indonesia');
    }

    public function provinces(): Collection
    {
        return $this->read('provinces.csv')->map(fn (array $row) => ['code' => $row[0], 'name' => $row[1]]);
    }

    public function children(string $level, ?string $parentCode): Collection
    {
        $file = match ($level) {
            'provinces' => 'provinces.csv',
            'cities' => 'regencies.csv',
            'districts' => 'districts.csv',
            'villages' => 'villages.csv',
            default => null,
        };

        if (!$file) {
            return collect();
        }

        return $this->read($file)
            ->when($parentCode && $level !== 'provinces', fn (Collection $rows) => $rows->filter(fn (array $row) => $row[1] === $parentCode))
            ->map(fn (array $row) => ['code' => $row[0], 'name' => $row[count($row) === 2 ? 1 : 2]])
            ->values();
    }

    public function resolveHierarchy(array $data): ?array
    {
        $province = $this->provinces()->firstWhere('code', $data['province_code'] ?? null);
        $city = $province ? $this->children('cities', $province['code'])->firstWhere('code', $data['city_code'] ?? null) : null;
        $district = $city ? $this->children('districts', $city['code'])->firstWhere('code', $data['district_code'] ?? null) : null;
        $village = $district ? $this->children('villages', $district['code'])->firstWhere('code', $data['village_code'] ?? null) : null;

        if (!$province || !$city || !$district || !$village) {
            return null;
        }

        return compact('province', 'city', 'district', 'village');
    }

    private function read(string $filename): Collection
    {
        $path = $this->directory.DIRECTORY_SEPARATOR.$filename;
        if (!File::exists($path)) {
            return collect();
        }

        return collect(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
            ->map(fn (string $line) => str_getcsv($line))
            ->filter(fn (array $row) => count($row) >= 2);
    }
}
