<?php

namespace App\Services;

use App\Models\Mobil;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Physical stock comes from identified Mobil rows, never from gallery or batch quantities. */
class VehicleColorStockService
{
    public function normalize(?string $value): string { return mb_strtolower(trim((string) $value)); }

    public function units(Mobil $model): Collection
    {
        $identity = app(CatalogVehicleIdentityService::class);
        $key = $identity->key($model);
        $source = $model;
        if ($model->catalog_identity && $key !== 'mobil:'.$model->getKey()) $source = $identity->resolve($key) ?: $model;
        return $identity->physical($source)->filter(function ($unit) use ($model, $key, $identity) {
            if (($unit->catalog_identity || $unit->showcase_slug) && $identity->key($unit) !== $key) return false;
            foreach (['merk', 'model', 'tipe', 'tahun', 'transmisi', 'bahan_bakar', 'kondisi', 'is_limited_edition'] as $field) {
                if ($this->normalize((string) $unit->$field) !== $this->normalize((string) $model->$field)) return false;
            }
            return true;
        })->values();
    }

    public function identified(Mobil $unit): bool
    {
        if (!filled($unit->nomor_rangka) || !filled($unit->nomor_mesin)) return false;
        return !Mobil::whereKeyNot($unit->getKey())->where(fn ($q) => $q
            ->where('nomor_rangka', trim($unit->nomor_rangka))->orWhere('nomor_mesin', trim($unit->nomor_mesin)))->exists();
    }

    public function sold(Mobil $unit): bool
    {
        return $unit->status === 'terjual' || DB::table('pesanan_mobil_member')->where('id_mobil', $unit->getKey())->where('status', 'diserahkan')->exists()
            || DB::table('penjualan_mobil as s')->join('transaksi as t', 's.id_transaksi', '=', 't.id_transaksi')
                ->where('s.id_mobil', $unit->getKey())->where('s.status', 'selesai')->where('t.status', 'lunas')->exists();
    }

    public function reservedIds(Collection $ids): Collection
    {
        if ($ids->isEmpty()) return collect();
        $bookings = DB::table('booking_mobil')->whereIn('id_mobil', $ids)->whereIn('status', ['menunggu', 'dikonfirmasi'])->pluck('id_mobil');
        $orders = DB::table('pesanan_mobil_member')->whereIn('id_mobil', $ids)->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])->pluck('id_mobil');
        $sales = DB::table('penjualan_mobil as s')->join('transaksi as t', 't.id_transaksi', '=', 's.id_transaksi')
            ->whereIn('s.id_mobil', $ids)->where('s.status', 'proses')->where('t.status', '<>', 'dibatalkan')->pluck('s.id_mobil');
        return $bookings->concat($orders)->concat($sales)->unique()->map(fn ($id) => (int) $id)->values();
    }

    public function state(Mobil $model, ?string $color = null): array
    {
        $color = $color ?? $model->warna;
        $units = $this->units($model)->filter(fn ($unit) => $this->normalize($unit->warna) === $this->normalize($color));
        $physical = $units->filter(fn ($unit) => !in_array($unit->status, ['terjual', 'nonaktif'], true) && !$this->sold($unit) && $this->identified($unit));
        $reserved = $this->reservedIds($physical->pluck('id_mobil'));
        $ready = $physical->filter(fn ($unit) => $unit->status === 'tersedia' && !$reserved->contains((int) $unit->getKey()));
        $inventories = \App\Models\VehicleInventory::whereIn('id_mobil', $units->pluck('id_mobil'))->get();
        $legacy = (int) $inventories->sum(fn ($inv) => max(0, $inv->stock_total - $inv->sold_units));
        return ['stock' => $ready->count(), 'stock_total' => $physical->count(), 'reserved_units' => $reserved->count(),
            'active_reservation_count' => $reserved->count(), 'sold_units' => $units->filter(fn ($unit) => $this->sold($unit))->count(),
            'unverified_units' => max(0, $legacy - $physical->count()), 'orderable_ids' => $ready->pluck('id_mobil')->map(fn ($id) => (int) $id)->values()->all(),
            'can_purchase' => $ready->contains('id_mobil', $model->getKey()), 'can_preorder' => $ready->isEmpty(),
            'is_aggregate' => false, 'is_limited_edition' => (bool) $model->is_limited_edition];
    }

    public function colors(Mobil $model): array
    {
        $units = $this->units($model);
        $metadata = $units->prepend($model)->unique(fn ($unit) => $unit->getKey() ?: 'catalog')->flatMap(fn ($unit) => $unit->warnaPilihan()->with('galeri')->get())->unique(fn ($color) => $this->normalize($color->nama));
        $colors = [];
        foreach ($metadata as $color) {
            $colors[$color->slug] = ['label' => $color->nama, 'swatch' => preg_match('/^#[0-9a-f]{6}$/i', (string) $color->hex) ? $color->hex : '#cbd5e1', 'images' => [], 'slug' => $color->slug];
            foreach ($color->galeri as $image) {
                $path = realpath(public_path($image->path)); $root = realpath(public_path());
                if ($path && $root && str_starts_with(strtolower($path), strtolower($root.DIRECTORY_SEPARATOR)) && is_file($path)) {
                    $colors[$color->slug]['images'][] = ['type' => $image->sudut, 'label' => ucfirst($image->sudut), 'src' => asset($image->path)];
                }
            }
        }
        // Presentation configuration is used only when there is no database color master.
        if (!$colors) {
            foreach (app(VehicleGalleryService::class)->for($model) as $key => $color) {
                $photo = app(VehicleGalleryService::class)->verifiedColorImage($model, $color['label']);
                $images = [];
                if ($photo) {
                    $frontPath = (string) parse_url($photo, PHP_URL_PATH);
                    foreach ($color['images'] ?? [] as $image) {
                        $imagePath = (string) parse_url($image['src'], PHP_URL_PATH);
                        $sameColor = str_starts_with($frontPath, '/assets/images/vehicles/') && dirname($frontPath) === dirname($imagePath);
                        $interior = ($image['type'] ?? '') === 'interior' && str_starts_with($imagePath, dirname(dirname($frontPath)).'/interior/');
                        if (($image['src'] === $photo || $sameColor || $interior) && is_file(public_path(ltrim($imagePath, '/')))) $images[] = $image;
                    }
                    if (!$images) $images[] = ['type' => 'front', 'label' => 'Front', 'src' => $photo];
                }
                $colors[$key] = ['label' => $color['label'], 'swatch' => $color['swatch'], 'slug' => $key,
                    'images' => $images];
            }
        }
        foreach ($units->prepend($model) as $unit) {
            if (!filled($unit->warna) || collect($colors)->contains(fn ($c) => $this->normalize($c['label']) === $this->normalize($unit->warna))) continue;
            $key = \Illuminate\Support\Str::slug($unit->warna);
            $photo = app(VehicleGalleryService::class)->verifiedColorImage($unit, $unit->warna);
            $colors[$key] = ['label' => $unit->warna, 'swatch' => '#cbd5e1', 'slug' => $key,
                'images' => $photo ? [['type' => 'front', 'label' => 'Front', 'src' => $photo]] : []];
        }
        foreach ($colors as &$color) {
            $color += $this->state($model, $color['label']);
            $color['status'] = $color['stock'] > 0 ? 'Tersedia' : 'Stok Habis';
            $color['purchase_url'] = $color['orderable_ids'] ? route('pelanggan.pesanan-mobil.create', ['mobil' => $color['orderable_ids'][0], 'warna' => $color['label']]) : null;
        }
        return $colors;
    }

    public function selected(Mobil $model, ?string $requested): array
    {
        $colors = $this->colors($model);
        $needle = $this->normalize($requested ?: $model->warna);
        foreach ($colors as $key => $color) {
            if ($this->normalize($key) === $needle || $this->normalize($color['label']) === $needle) return $color;
        }
        if (in_array($needle, ['black', 'white', 'red', 'blue', 'gray', 'grey', 'silver', 'yellow', 'orange', 'green', 'brown'], true)) {
            $matches = collect($colors)->filter(fn ($color) => preg_match('/\b'.preg_quote($needle, '/').'\b/i', $color['label']));
            if ($matches->count() === 1) return $matches->first();
        }
        throw new RuntimeException('Warna pilihan tidak tersedia untuk model kendaraan ini.');
    }

    /** Caller holds the physical Mobil row lock throughout reservation creation. */
    public function assertOrderable(Mobil $unit): void
    {
        if ($unit->status !== 'tersedia' || $this->sold($unit) || !$this->identified($unit) || $this->reservedIds(collect([$unit->getKey()]))->isNotEmpty()) {
            throw new RuntimeException('Unit/warna sudah tidak tersedia atau identitas fisiknya belum terverifikasi.');
        }
    }
}
