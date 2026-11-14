<?php

namespace App\Services;

use App\Models\{Mobil, PesananMobilMember, User};
use Illuminate\Support\Facades\{DB, Schema};
use RuntimeException;

class SmartSpkOrderService
{
    public function ready(): bool
    {
        return Schema::hasColumn('pesanan_mobil_member', 'manual_discount_status');
    }

    private function cents(string $amount): int
    {
        if (!preg_match('/^\d{1,13}(?:\.\d{1,2})?$/D', $amount)) throw new RuntimeException('Nilai diskon/harga harus angka positif dengan maksimal dua desimal.');
        [$whole, $fraction] = array_pad(explode('.', $amount), 2, '');
        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    private function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function calculate(string $base, string $type = 'none', string $value = '0'): array
    {
        if (!in_array($type, ['none', 'percentage', 'nominal'], true)) throw new RuntimeException('Jenis diskon tidak valid.');
        $price = $this->cents($base); $units = $this->cents($value);
        if ($type === 'percentage' && $units > 10000) throw new RuntimeException('Persentase diskon maksimal 100%.');
        if ($type === 'none' && $units !== 0) throw new RuntimeException('Pilih jenis diskon untuk nilai di atas nol.');
        // Integer cents, half-up to one cent; avoid base*basisPoints overflow.
        $discount = $type === 'percentage' ? intdiv($price, 10000) * $units + intdiv(($price % 10000) * $units + 5000, 10000)
            : ($type === 'nominal' ? $units : 0);
        if ($discount > $price) throw new RuntimeException('Diskon tidak boleh melebihi harga kendaraan.');
        return ['base_price' => $this->money($price), 'discount_type' => $type, 'discount_value' => $this->money($units),
            'discount_amount' => $this->money($discount), 'estimated_total' => $this->money($price - $discount),
            'approval_required' => $units > 0, 'approval_status' => $units > 0 ? 'pending' : 'none',
            'binding_total' => $this->money($price), 'approval_available' => false];
    }

    public function vehicle(Mobil $car): array
    {
        $inventory = app(VehicleInventoryService::class)->find($car);
        $availability = app(VehicleAvailabilityService::class)->for($car);
        $stock = $car->status === 'tersedia' && in_array((int) $car->id_mobil, $availability['orderable_ids'] ?? [], true)
            ? 1 : 0;
        $gallery = app(VehicleGalleryService::class)->forColor($car, $car->warna);
        $photo = $gallery['images'][0]['src'] ?? null;
        if (in_array($photo, [asset('assets/images/hero/yza-hero-reference-v3.png'),asset('assets/images/hero/yza-hero-black-v2.png')], true)) $photo = null;
        return ['id' => (int) $car->id_mobil, 'group' => hash('sha256', json_encode([$car->merk,$car->model,$car->tipe,$car->tahun,$car->transmisi,$car->bahan_bakar,$car->kondisi,$car->is_limited_edition])),
            'model_key' => hash('sha256', json_encode([$car->merk,$car->model])),
            'name' => trim($car->merk.' '.$car->model), 'variant' => trim($car->tipe.' · '.$car->tahun), 'code' => $car->kode_mobil,
            'color' => $car->warna, 'swatch' => preg_match('/^#[0-9a-f]{3,8}$/i', $gallery['swatch'] ?? '') ? $gallery['swatch'] : '#cbd5e1',
            'image' => $photo ?: asset('assets/images/vehicle-photo-unavailable.svg'), 'base_price' => (string) $car->harga_jual,
            'stock' => $stock, 'aggregate' => false, 'inventory_id' => $inventory?->id_vehicle_inventory,
            'color_key' => mb_strtolower(trim((string) $car->warna)),
            'identity_complete' => filled($car->nomor_rangka) && filled($car->nomor_mesin),
            'vin' => $car->nomor_rangka];
    }

    public function vehicles(): array
    {
        return Mobil::with(['foto','warnaPilihan.galeri'])->where('status', 'tersedia')->orderBy('merk')->orderBy('model')->get()
            ->map(fn ($car) => $this->vehicle($car))->filter(fn ($car) => $car['stock'] > 0)->values()->all();
    }

    /** Catalog support and stock are separate; gallery colors never create stock. */
    public function catalog(array $eligible): array
    {
        $groups = [];
        foreach (Mobil::with('warnaPilihan.galeri')->where('status','<>','nonaktif')->orderBy('merk')->orderBy('model')->get() as $car) {
            $row = $this->vehicle($car); $key = $row['group'];
            $groups[$key] ??= ['group'=>$key,'model_key'=>$row['model_key'],'name'=>$row['name'],
                'variant'=>implode(' · ',array_filter([$car->tipe,$car->tahun,$car->transmisi,$car->bahan_bakar,$car->kondisi])),'colors'=>[]];
            foreach ($car->warnaPilihan as $color) {
                $colorKey = mb_strtolower(trim($color->nama));
                if ($colorKey === '') continue;
                $images = $color->galeri->sortBy(fn($photo)=>$photo->sudut === 'front' ? 0 : 1);
                $image = $images->first(fn($photo)=>is_file(public_path($photo->path)));
                $entry = ['key'=>$colorKey,'name'=>$color->nama,'code'=>$color->slug,
                    'swatch'=>preg_match('/^#[0-9a-f]{3,8}$/i',(string)$color->hex) ? $color->hex : '#cbd5e1',
                    'image'=>$image ? asset($image->path) : asset('assets/images/vehicle-photo-unavailable.svg'),
                    'catalog_color_ids'=>[(int)$color->id_warna]];
                if (!isset($groups[$key]['colors'][$colorKey])) $groups[$key]['colors'][$colorKey] = $entry;
                else $groups[$key]['colors'][$colorKey]['catalog_color_ids'][] = (int)$color->id_warna;
            }
            // An actual inventory color is supported even when catalog metadata is incomplete.
            if ($row['color_key'] !== '') $groups[$key]['colors'][$row['color_key']] ??= [
                'key'=>$row['color_key'],'name'=>$row['color'],'code'=>null,'swatch'=>$row['swatch'],'image'=>$row['image'],'catalog_color_ids'=>[],
            ];
        }
        foreach ($groups as &$group) {
            foreach ($group['colors'] as &$color) {
                $units = array_values(array_filter($eligible,fn($unit)=>$unit['group']===$group['group'] && $unit['color_key']===$color['key']));
                $batches = []; $physical = 0;
                foreach ($units as $unit) {
                    if ($unit['aggregate']) $batches[$unit['inventory_id']] = $unit['stock'];
                    else $physical++;
                }
                $color['stock'] = array_sum($batches) + $physical;
                $color['physical_units'] = $physical;
                $color['identified_units'] = count(array_filter($units,fn($unit)=>$unit['identity_complete']));
                $color['aggregate_stock'] = array_sum($batches);
                $color['unit_ids'] = array_column($units,'id');
                $color['available'] = $color['stock'] > 0;
                // Staff pre-order is read-only and existing member pre-order is per
                // product, not per color. Do not advertise an unsupported mutation.
                $color['can_preorder'] = false;
            }
            unset($color);
            $group['colors'] = array_values($group['colors']);
        }
        unset($group);
        return array_values($groups);
    }

    public function quote(Mobil $car, array $input): array
    {
        $vehicle = $this->vehicle($car);
        if ($vehicle['stock'] < 1) throw new RuntimeException('Unit/warna tidak tersedia. Periksa inventori atau menu Pre-Order.');
        if (isset($input['warna_pilihan']) && $input['warna_pilihan'] !== $car->warna) throw new RuntimeException('Warna harus sesuai unit inventori yang dipilih.');
        return $this->calculate((string) $car->harga_jual, $input['manual_discount_type'] ?? 'none', (string) ($input['manual_discount_value'] ?? '0')) + ['vehicle' => $vehicle];
    }

    public function recordProposal(PesananMobilMember $order, array $input, array $quote, User $actor, string $event = 'submitted'): void
    {
        abort_unless($actor->status === 'aktif' && $actor->roleKey() !== 'owner' && $actor->hasPermission('sales.manage'), 403);
        if (!$this->ready()) {
            if ($quote['approval_required']) throw new RuntimeException('Penyimpanan pengajuan diskon belum tersedia. Migration SPK belum diterapkan.');
            return;
        }
        if ($quote['approval_required'] && trim((string) ($input['manual_discount_reason'] ?? '')) === '') throw new RuntimeException('Alasan diskon wajib diisi.');
        $history = $order->manual_discount_history ?? [];
        $history[] = ['event'=>$event, 'actor_id'=>$actor->id_user, 'actor_role'=>$actor->roleKey(), 'at'=>now()->toIso8601String(),
            'base_price'=>$quote['base_price'], 'type'=>$quote['discount_type'], 'value'=>$quote['discount_value'], 'amount'=>$quote['discount_amount'],
            'previous_amount'=>$order->manual_discount_amount ?? '0.00', 'previous_status'=>$order->manual_discount_status ?? 'none',
            'status'=>$quote['approval_status'], 'reason'=>trim((string) ($input['manual_discount_reason'] ?? ''))];
        $order->forceFill(['manual_discount_type'=>$quote['discount_type'],'manual_discount_value'=>$quote['discount_value'],
            'manual_discount_amount'=>$quote['discount_amount'],'manual_discount_status'=>$quote['approval_status'],
            'manual_discount_reason'=>$input['manual_discount_reason'] ?? null,'manual_discount_requested_by'=>$actor->id_user,
            'manual_discount_reviewed_by'=>null,'manual_discount_reviewed_at'=>null,'manual_discount_history'=>$history])->save();
        app(MasterAuditService::class)->record($event, 'pesanan_mobil_member', $order->id_pesanan, 'Pengajuan diskon SPK; nominal dan aktor tersimpan dalam riwayat pesanan.');
    }

    public function review(PesananMobilMember $order, User $actor, string $decision, string $reason): void
    {
        abort_unless($actor->roleKey() === 'super_admin' && $actor->status === 'aktif', 403);
        DB::transaction(function () use ($order,$actor,$decision,$reason) {
            $order = PesananMobilMember::lockForUpdate()->findOrFail($order->id_pesanan);
            if ($order->status !== 'menunggu_verifikasi' || $order->id_transaksi || $order->manual_discount_status !== 'pending') throw new RuntimeException('Pengajuan tidak dapat ditinjau pada status ini.');
            if ((int) $order->manual_discount_requested_by === (int) $actor->id_user) {
                app(MasterAuditService::class)->record('review_blocked', 'pesanan_mobil_member', $order->id_pesanan, 'Pengaju mencoba meninjau pengajuan diskonnya sendiri.');
                throw new RuntimeException('Pengaju tidak boleh menyetujui/meninjau pengajuannya sendiri.');
            }
            if ($decision === 'approve') {
                app(MasterAuditService::class)->record('approval_blocked', 'pesanan_mobil_member', $order->id_pesanan, 'Persetujuan diskon diblokir: kebijakan harga minimum/margin belum tersedia.');
                throw new RuntimeException('Kebijakan harga minimum dan margin belum tersedia. Diskon tetap menunggu persetujuan.');
            }
            if ($decision !== 'reject' || trim($reason) === '') throw new RuntimeException('Alasan penolakan wajib diisi.');
            $history = $order->manual_discount_history ?? [];
            $history[] = ['event'=>'rejected','actor_id'=>$actor->id_user,'actor_role'=>$actor->roleKey(),'at'=>now()->toIso8601String(),'reason'=>$reason,
                'amount'=>$order->manual_discount_amount,'previous_status'=>'pending','status'=>'rejected'];
            $order->forceFill(['manual_discount_status'=>'rejected','manual_discount_reviewed_by'=>$actor->id_user,
                'manual_discount_reviewed_at'=>now(),'manual_discount_history'=>$history])->save();
            app(MasterAuditService::class)->record('rejected', 'pesanan_mobil_member', $order->id_pesanan, 'Pengajuan diskon SPK ditolak; harga dan invoice tidak diubah.');
        });
    }
}
