<?php

namespace App\Services;

use App\Models\{MemberVehiclePreorder, Mobil};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/** Stable read-only catalog identities; persisted Mobil rows remain physical/inventory records. */
class CatalogVehicleIdentityService
{
    public function key(Mobil $car): string
    {
        if ($car->catalog_identity) return $car->catalog_identity;
        $slug = $car->exists ? $car->getRawOriginal('showcase_slug') : $car->showcase_slug;
        return $slug ? 'showcase:'.$slug : 'mobil:'.$car->getKey();
    }

    public function resolve(string $key): ?Mobil
    {
        if (preg_match('/^mobil:([1-9][0-9]*)$/', $key, $match)) return Mobil::with('foto')->find($match[1]);
        if (!preg_match('/^showcase:([a-z0-9-]+)$/', $key, $match)) return null;
        $slug = $match[1];
        $bound = Mobil::where('showcase_slug', $slug)->first();
        if (!$bound && Schema::hasColumn('mobil','catalog_identity')) $bound = Mobil::where('catalog_identity',$key)->orderByRaw("status = 'tersedia' DESC")->orderBy('id_mobil')->first();
        if ($bound) return $bound;
        $data = config('vehicle_showcases.'.$slug);
        if (!$data) return null;
        $image = asset($data['image']); unset($data['image']);
        $car = new Mobil($data + ['status'=>'katalog']);
        $car->setAttribute('showcase_slug', $slug); $car->setAttribute('image', $image);
        $car->setRelation('foto', collect()); $car->setRelation('warnaPilihan', collect());
        return $car;
    }

    public function bindNewUnit(array $data): array
    {
        $key = $data['catalog_identity'] ?? (!empty($data['showcase_slug']) && config('vehicle_showcases.'.$data['showcase_slug']) ? 'showcase:'.$data['showcase_slug'] : null);
        if (!$key) return $data;
        $model = $this->resolve($key);
        if (!$model) throw \Illuminate\Validation\ValidationException::withMessages(['catalog_identity'=>'Identitas katalog tidak valid.']);
        foreach (['merk','model','tipe','tahun','transmisi','bahan_bakar'] as $field) {
            if (mb_strtolower(trim((string) ($data[$field] ?? ''))) !== mb_strtolower(trim((string) $model->$field))) {
                throw \Illuminate\Validation\ValidationException::withMessages([$field=>'Spesifikasi unit tidak cocok dengan model katalog yang dipilih.']);
            }
        }
        $data['showcase_slug'] = null;
        if (str_starts_with($key,'showcase:') && !Mobil::where('showcase_slug',substr($key,9))->exists()) $data['showcase_slug'] = substr($key,9);
        if (Schema::hasColumn('mobil','catalog_identity')) return $data;
        unset($data['catalog_identity']);
        if (str_starts_with($key,'showcase:') && !Mobil::where('showcase_slug',substr($key,9))->exists()) {
            $data['showcase_slug'] = substr($key,9);
        } elseif (!$model->exists || app(VehicleInventoryService::class)->key(new Mobil($data)) !== app(VehicleInventoryService::class)->key($model)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['catalog_identity'=>'Relasi unit dengan spesifikasi berbeda memerlukan pembaruan skema katalog. Tidak ada stok yang dibuat.']);
        }
        return $data;
    }

    public function models(): Collection
    {
        $cars = Mobil::with(['foto','warnaPilihan.galeri'])->get();
        foreach (array_keys(config('vehicle_showcases', [])) as $slug) {
            if (!$cars->contains(fn ($car) => $this->key($car) === 'showcase:'.$slug)) $cars->push($this->resolve('showcase:'.$slug));
        }
        $availability = new VehicleAvailabilityService();
        return $cars->filter()->map(function ($car) use ($availability) {
            $car->setAttribute('catalog_key', $this->key($car));
            $car->setAttribute('availability', $availability->for($car));
            return $car;
        });
    }

    public function validateExisting(Mobil $car, array $data): array
    {
        $key = $this->key($car);
        if (!str_starts_with($key,'showcase:')) return $data;
        $definition = config('vehicle_showcases.'.substr($key,9));
        if (!$definition) return $data;
        foreach (['merk','model','tipe','tahun','transmisi','bahan_bakar'] as $field) {
            if (array_key_exists($field,$data) && mb_strtolower(trim((string) $data[$field])) !== mb_strtolower(trim((string) $definition[$field]))) {
                throw \Illuminate\Validation\ValidationException::withMessages([$field=>'Perubahan ini tidak cocok dengan identitas katalog terhubung.']);
            }
        }
        if (!empty($data['showcase_slug']) && $data['showcase_slug'] !== substr($key,9)) throw \Illuminate\Validation\ValidationException::withMessages(['showcase_slug'=>'Unit sudah terhubung ke identitas katalog yang berbeda.']);
        return $data;
    }

    public function physical(Mobil $model): Collection
    {
        $key = $this->key($model);
        $query = Mobil::query()->where(function ($query) use ($model, $key) {
            if ($model->exists) $query->whereKey($model->getKey()); else $query->whereRaw('1=0');
            if (str_starts_with($key, 'showcase:')) $query->orWhere('showcase_slug', substr($key, 9));
            if (Schema::hasColumn('mobil','catalog_identity')) $query->orWhere('catalog_identity', $key);
        });
        $cars = $query->get();
        if ($model->exists) {
            $group = app(VehicleInventoryService::class)->key($model);
            $cars = $cars->concat(Mobil::where('tahun',$model->tahun)->get()->filter(fn ($car) => app(VehicleInventoryService::class)->key($car) === $group));
        }
        return $cars->unique('id_mobil')->values();
    }

    public function forPreorder(MemberVehiclePreorder $item): ?Mobil
    {
        if ($item->id_mobil) return Mobil::find($item->id_mobil);
        // Existing stored showcase_slug is the explicit legacy relation, not a name search.
        return $item->showcase_slug ? $this->resolve('showcase:'.$item->showcase_slug) : $this->resolve($item->product_key);
    }

    public function activePreorders(Mobil $model, ?Collection $preloaded = null): int
    {
        if ($preloaded !== null) {
            return $this->filterMatchingPreorders($model, $preloaded)->count();
        }
        $key = $this->key($model);
        $ids = $this->physical($model)->pluck('id_mobil');
        return MemberVehiclePreorder::active()->where(function ($query) use ($key, $ids) {
            $query->where('product_key', $key)->orWhereIn('id_mobil', $ids);
            if (str_starts_with($key, 'showcase:')) $query->orWhere('showcase_slug', substr($key, 9));
        })->count();
    }

    public function activePreordersList(Mobil $model, ?Collection $preloaded = null): Collection
    {
        if ($preloaded !== null) {
            return $this->filterMatchingPreorders($model, $preloaded)->values();
        }
        $key = $this->key($model);
        $ids = $this->physical($model)->pluck('id_mobil');
        return MemberVehiclePreorder::active()
            ->with(['pelanggan.user', 'mobil'])
            ->where(function ($query) use ($key, $ids) {
                $query->where('product_key', $key)->orWhereIn('id_mobil', $ids);
                if (str_starts_with($key, 'showcase:')) {
                    $query->orWhere('showcase_slug', substr($key, 9));
                }
            })
            ->latest('created_at')
            ->get();
    }

    public function filterMatchingPreorders(Mobil $model, Collection $preorders): Collection
    {
        $key = $this->key($model);
        $ids = $this->physical($model)->pluck('id_mobil')->all();
        $slug = str_starts_with($key, 'showcase:') ? substr($key, 9) : $model->showcase_slug;

        return $preorders->filter(function ($po) use ($key, $ids, $slug, $model) {
            if ($model->id_mobil && $po->id_mobil && (int) $po->id_mobil === (int) $model->id_mobil) {
                return true;
            }
            if ($po->id_mobil && in_array((int) $po->id_mobil, $ids, true)) {
                return true;
            }
            if ($po->product_key && $po->product_key === $key) {
                return true;
            }
            if ($slug && $po->showcase_slug === $slug) {
                return true;
            }
            return false;
        });
    }
}
