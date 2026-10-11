<?php

namespace App\Services;

use App\Models\{Mobil, PdiPesananMobil, Pelanggan, PenjualanMobil, PesananMobilMember, SerahTerimaPesananMobil, Transaksi, VehicleInventory};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MemberVehiclePurchaseService
{
    private const CHECKLIST = [
        'identitas' => ['nomor_rangka', 'nomor_mesin'],
        'mesin_fluid' => ['kondisi_mesin', 'oli_mesin', 'coolant', 'brake_fluid', 'washer_fluid'],
        'kaki_kaki' => ['rem', 'ban', 'tekanan_ban', 'velg'],
        'electrical' => ['aki', 'lampu_utama', 'lampu_belakang', 'lampu_sein', 'dashboard_indicator'],
        'cabin' => ['ac', 'infotainment', 'interior', 'seat', 'seatbelt'],
        'exterior' => ['body', 'cat', 'kaca', 'wiper', 'mirror'],
        'feature' => ['sensor', 'camera', 'central_lock', 'key_keyless'],
        'completeness' => ['kunci_utama_cadangan', 'toolkit', 'accessories', 'dokumen'],
        'final' => ['function_test', 'road_test'],
    ];

    public function checklistDefinition(): array
    {
        return self::CHECKLIST;
    }
    private function code(string $prefix, string $table, string $column): string
    {
        do {
            $value = $prefix.'-'.now()->format('Ym').'-'.strtoupper(bin2hex(random_bytes(4)));
        } while (DB::table($table)->where($column, $value)->exists());
        return $value;
    }

    public function customerFor($user): Pelanggan
    {
        $customer = $user->pelanggan;
        abort_unless($customer && $customer->status === 'aktif', 403);
        return $customer;
    }

    public function create(Pelanggan $customer, Mobil $car, array $input): PesananMobilMember
    {
        return DB::transaction(function () use ($customer, $car, $input): PesananMobilMember {
            $car = Mobil::lockForUpdate()->findOrFail($car->id_mobil);
            $inventoryService = app(VehicleInventoryService::class);
            $inventory = VehicleInventory::lockForUpdate()->where('inventory_key', $inventoryService->key($car))->first();
            $isAggregate = (bool) $inventory;
            if ($isAggregate) {
                $available = (int) $inventory->stock_total - (int) $inventory->sold_units - $inventoryService->reserveCount($car);
                if ($available < 1) throw new RuntimeException('Stok kendaraan sudah habis.');
            } elseif ($car->status !== 'tersedia') {
                throw new RuntimeException('Unit sudah tidak tersedia. Silakan pilih kendaraan lain.');
            }

            if (!$isAggregate) {
                $active = PesananMobilMember::where('id_mobil', $car->id_mobil)
                    ->whereNotIn('status', ['ditolak', 'dibatalkan', 'diserahkan'])
                    ->exists();
                if ($active) throw new RuntimeException('Unit sedang diproses oleh pesanan lain.');
            }

            $gallery = app(VehicleGalleryService::class)->for($car);
            $requestedColor = strtolower(trim((string) ($input['warna_pilihan'] ?? '')));
            $selectedColor = $gallery[$requestedColor]['label'] ?? $car->warna;
            $order = new PesananMobilMember();
            $order->forceFill([
                'nomor_pesanan' => $this->code('ORD', 'pesanan_mobil_member', 'nomor_pesanan'),
                'id_pelanggan' => $customer->id_pelanggan,
                'id_mobil' => $car->id_mobil,
                'nama_pembeli' => trim($input['nama_pembeli']),
                'email_pembeli' => trim((string) ($input['email_pembeli'] ?? '')) ?: null,
                'telepon_pembeli' => trim((string) ($input['telepon_pembeli'] ?? '')) ?: null,
                'alamat_pembeli' => trim((string) ($input['alamat_pembeli'] ?? '')) ?: null,
                'snapshot_merk' => $car->merk,
                'snapshot_model' => $car->model,
                'snapshot_tipe' => $car->tipe,
                'snapshot_tahun' => $car->tahun,
                'snapshot_warna' => $selectedColor,
                'snapshot_harga' => $car->harga_jual,
                'total_tagihan' => $car->harga_jual,
                'syarat_disetujui' => true,
                'status' => 'menunggu_verifikasi',
            ])->save();
            if (!$isAggregate) $car->forceFill(['status' => 'dibooking'])->save();
            return $order->fresh(['mobil', 'pelanggan']);
        });
    }

    public function approve(PesananMobilMember $order, int $petugasId, ?string $note = null): PesananMobilMember
    {
        return DB::transaction(function () use ($order, $petugasId, $note): PesananMobilMember {
            $order = PesananMobilMember::lockForUpdate()->findOrFail($order->id_pesanan);
            if ($order->status !== 'menunggu_verifikasi') throw new RuntimeException('Pesanan tidak menunggu verifikasi.');
            $car = Mobil::lockForUpdate()->findOrFail($order->id_mobil);
            $isAggregate = (bool) app(VehicleInventoryService::class)->find($car);
            if (!$isAggregate && $car->status !== 'dibooking') throw new RuntimeException('Unit tidak lagi aman untuk diproses.');

            $tx = new Transaksi();
            $tx->forceFill([
                'kode_transaksi' => $this->code('TRX', 'transaksi', 'kode_transaksi'),
                'id_pelanggan' => $order->id_pelanggan,
                'id_petugas' => $petugasId,
                'jenis_transaksi' => 'penjualan_mobil',
                'tanggal' => now(),
                'subtotal' => $order->snapshot_harga,
                'diskon' => 0,
                'total' => $order->total_tagihan,
                'status' => 'belum_bayar',
                'catatan' => 'Checkout member '.$order->nomor_pesanan,
            ])->save();

            $sale = new PenjualanMobil();
            $sale->forceFill([
                'kode_penjualan' => $this->code('SALE', 'penjualan_mobil', 'kode_penjualan'),
                'id_transaksi' => $tx->id_transaksi,
                'id_pelanggan' => $order->id_pelanggan,
                'id_mobil' => $order->id_mobil,
                'id_petugas' => $petugasId,
                'tanggal_penjualan' => now(),
                'harga_mobil' => $order->snapshot_harga,
                'diskon' => 0,
                'dp' => 0,
                'total_akhir' => $order->total_tagihan,
                'metode_pembelian' => 'tunai',
                'status' => 'proses',
                'catatan' => 'Pesanan member '.$order->nomor_pesanan,
            ])->save();

            $order->forceFill(['id_transaksi' => $tx->id_transaksi, 'id_penjualan' => $sale->id_penjualan, 'status' => 'menunggu_pembayaran', 'id_petugas_keputusan' => $petugasId, 'diputuskan_at' => now(), 'catatan_admin' => $note])->save();
            return $order->fresh(['mobil', 'pelanggan', 'transaksi', 'penjualan']);
        });
    }

    public function reject(PesananMobilMember $order, int $petugasId, string $note): PesananMobilMember
    {
        return DB::transaction(function () use ($order, $petugasId, $note): PesananMobilMember {
            $order = PesananMobilMember::lockForUpdate()->findOrFail($order->id_pesanan);
            if ($order->status !== 'menunggu_verifikasi') throw new RuntimeException('Pesanan tidak menunggu verifikasi.');
            $car = Mobil::lockForUpdate()->findOrFail($order->id_mobil);
            if (!app(VehicleInventoryService::class)->find($car)) {
                Mobil::whereKey($order->id_mobil)->where('status', 'dibooking')->update(['status' => 'tersedia']);
            }
            $order->forceFill(['status' => 'ditolak', 'id_petugas_keputusan' => $petugasId, 'diputuskan_at' => now(), 'catatan_admin' => $note])->save();
            return $order;
        });
    }

    public function schedule(PesananMobilMember $order, string $schedule, ?string $location = null, ?string $notes = null): PesananMobilMember
    {
        return DB::transaction(function () use ($order, $schedule, $location, $notes): PesananMobilMember {
            $order = PesananMobilMember::lockForUpdate()->with('transaksi')->findOrFail($order->id_pesanan);
            if (!in_array($order->status, ['pdi_lulus', 'siap_diambil'], true) || !$order->transaksi || $order->transaksi->status !== 'lunas') {
                throw new RuntimeException('Jadwal pengambilan hanya dapat dibuat setelah PDI lulus dan pembayaran lunas.');
            }
            if (!$order->pdi || $order->pdi->status !== 'lulus') throw new RuntimeException('PDI belum lulus.');
            $order->forceFill(['status' => 'siap_diambil', 'jadwal_pengambilan' => $schedule, 'lokasi_pengambilan' => $location ?: 'YZA Automotive', 'status_pengambilan' => 'dijadwalkan', 'jadwal_dikonfirmasi_at' => now(), 'catatan_pengambilan' => $notes])->save();
            $this->notify($order, 'Jadwal pengambilan tersedia', 'Jadwal pengambilan kendaraan Anda telah tersedia.');
            $this->notify($order, 'Kendaraan siap diserahkan', 'Kendaraan Anda siap diserahkan sesuai jadwal pengambilan.');
            $this->audit('pickup_scheduled', $order);
            $this->audit('ready_for_pickup', $order);
            return $order->fresh(['pdi', 'transaksi']);
        });
    }

    public function startPdi(PesananMobilMember $order, int $petugasId): PdiPesananMobil
    {
        return DB::transaction(function () use ($order, $petugasId): PdiPesananMobil {
            $order = PesananMobilMember::lockForUpdate()->with(['transaksi', 'mobil'])->findOrFail($order->id_pesanan);
            $this->assertPdiEntry($order);
            $pdi = PdiPesananMobil::lockForUpdate()->firstOrNew(['id_pesanan' => $order->id_pesanan]);
            if ($pdi->status === 'lulus') return $pdi;
            $pdi->forceFill([
                'id_pesanan' => $order->id_pesanan,
                'id_petugas' => $petugasId,
                'status' => 'sedang_diperiksa',
                'result' => 'in_progress',
                'started_at' => $pdi->started_at ?: now(),
                'nomor_rangka_snapshot' => $order->mobil?->nomor_rangka,
                'nomor_mesin_snapshot' => $order->mobil?->nomor_mesin,
            ])->save();
            $this->notify($order, 'PDI dimulai', 'Kendaraan Anda sedang menjalani pemeriksaan PDI.');
            $this->audit('pdi_started', $order, $petugasId);
            return $pdi->fresh();
        });
    }

    public function completePdi(PesananMobilMember $order, int $petugasId, array $input): PdiPesananMobil
    {
        return DB::transaction(function () use ($order, $petugasId, $input): PdiPesananMobil {
            $order = PesananMobilMember::lockForUpdate()->with(['transaksi', 'mobil', 'pdi'])->findOrFail($order->id_pesanan);
            $this->assertPdiEntry($order);
            $pdi = PdiPesananMobil::lockForUpdate()->firstOrNew(['id_pesanan' => $order->id_pesanan]);
            if (!$pdi->started_at) $pdi->started_at = now();
            $checklist = $this->normalizeChecklist((array) ($input['checklist'] ?? []));
            $issues = collect($checklist)->flatten(1)->filter(fn (array $item): bool => $item['result'] === 'issue');
            $hasMissing = collect($checklist)->flatten(1)->count() < collect(self::CHECKLIST)->flatten()->count();
            if ($hasMissing) throw new RuntimeException('Checklist PDI belum lengkap.');
            if ($issues->contains(fn (array $item): bool => trim((string) ($item['notes'] ?? '')) === '')) throw new RuntimeException('Setiap temuan ISSUE wajib memiliki catatan.');
            $status = $issues->isNotEmpty() ? 'perlu_perbaikan' : 'lulus';
            $pdi->forceFill([
                'id_pesanan' => $order->id_pesanan,
                'id_petugas' => $petugasId,
                'status' => $status,
                'result' => $status === 'lulus' ? 'passed' : 'needs_correction',
                'checklist' => $checklist,
                'completed_at' => now(),
                'diperiksa_at' => now(),
                'nomor_rangka_snapshot' => $order->mobil?->nomor_rangka,
                'nomor_mesin_snapshot' => $order->mobil?->nomor_mesin,
                'catatan' => $input['catatan'] ?? null,
            ] + $this->legacyChecklistFlags($checklist))->save();
            $order->forceFill(['status' => $status === 'lulus' ? 'pdi_lulus' : 'pdi_gagal', 'status_pengambilan' => $status === 'lulus' ? 'belum_dijadwalkan' : 'belum_dijadwalkan'])->save();
            $this->notify($order, $status === 'lulus' ? 'PDI lulus' : 'PDI memerlukan tindak lanjut', $status === 'lulus' ? 'Kendaraan Anda telah lulus pemeriksaan PDI.' : 'Pemeriksaan kendaraan memerlukan tindak lanjut sebelum kendaraan dapat diserahkan.');
            $this->audit($status === 'lulus' ? 'pdi_completed' : 'pdi_issue', $order, $petugasId);
            return $pdi->fresh();
        });
    }

    public function savePdi(PesananMobilMember $order, int $petugasId, array $input): PdiPesananMobil
    {
        return DB::transaction(function () use ($order, $petugasId, $input): PdiPesananMobil {
            $order = PesananMobilMember::lockForUpdate()->with('transaksi')->findOrFail($order->id_pesanan);
            if (in_array($order->status, ['diserahkan', 'dibatalkan', 'ditolak'], true) || !$order->transaksi || $order->transaksi->status !== 'lunas') throw new RuntimeException('PDI hanya dapat dilakukan setelah pelunasan terverifikasi dan sebelum serah-terima.');
            $items = ['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'];
            $values = array_fill_keys($items, false);
            foreach ($items as $item) $values[$item] = (bool) ($input[$item] ?? false);
            $pdi = PdiPesananMobil::firstOrNew(['id_pesanan' => $order->id_pesanan]);
            $status = in_array(false, $values, true) ? 'perlu_perbaikan' : 'lulus';
            $pdi->forceFill($values + ['id_pesanan' => $order->id_pesanan, 'id_petugas' => $petugasId, 'status' => $status, 'result' => $status === 'lulus' ? 'passed' : 'needs_correction', 'started_at' => $pdi->started_at ?: now(), 'completed_at' => now(), 'catatan' => $input['catatan'] ?? null, 'diperiksa_at' => now()])->save();
            $order->update(['status' => $status === 'lulus' ? 'pdi_lulus' : 'pdi_gagal']);
            return $pdi;
        });
    }

    public function handover(PesananMobilMember $order, int $petugasId, array $input): SerahTerimaPesananMobil
    {
        return DB::transaction(function () use ($order, $petugasId, $input): SerahTerimaPesananMobil {
            $order = PesananMobilMember::lockForUpdate()->with(['transaksi', 'penjualan', 'pdi', 'serahTerima'])->findOrFail($order->id_pesanan);
            if ($order->status === 'diserahkan' && $order->serahTerima) return $order->serahTerima;
            if ($order->status !== 'siap_diambil' || !$order->transaksi || $order->transaksi->status !== 'lunas') throw new RuntimeException('Pesanan belum memenuhi syarat serah-terima.');
            if (!$order->pdi || $order->pdi->status !== 'lulus') throw new RuntimeException('PDI belum lulus.');
            if (!$order->jadwal_pengambilan || !in_array($order->status_pengambilan, ['dijadwalkan', 'siap_diambil'], true)) throw new RuntimeException('Jadwal pengambilan belum valid.');
            $pdi = PdiPesananMobil::where('id_pesanan', $order->id_pesanan)->where('status', 'lulus')->exists();
            if (!$pdi) throw new RuntimeException('PDI belum lulus.');
            $handover = SerahTerimaPesananMobil::firstOrNew(['id_pesanan' => $order->id_pesanan]);
            $handover->forceFill(['id_pesanan' => $order->id_pesanan, 'id_petugas' => $petugasId, 'nama_penerima' => trim($input['nama_penerima']), 'nomor_identitas' => $input['nomor_identitas'] ?? null, 'pelunasan_terverifikasi' => true, 'dokumen_lengkap' => true, 'kendaraan_sesuai' => true, 'catatan' => $input['catatan'] ?? null, 'diserahkan_at' => now()])->save();
            $order->penjualan->forceFill(['status' => 'selesai'])->save();
            $car = Mobil::lockForUpdate()->findOrFail($order->id_mobil);
            $inventoryService = app(VehicleInventoryService::class);
            if ($inventoryService->find($car)) {
                $inventoryService->recordSale($car, 1, 'pesanan_mobil_member', $order->id_pesanan, 'Unit agregat diserahkan kepada pelanggan.');
            } else {
                $car->forceFill(['status' => 'terjual'])->save();
            }
            $order->forceFill(['status' => 'diserahkan', 'status_pengambilan' => 'diserahkan', 'diserahkan_at' => now()])->save();
            $vehicle = app(VerifiedCarOwnershipService::class)->syncVehicleFromCompletedSale($order->penjualan->fresh(['mobil', 'transaksi', 'memberOrder']), $handover);
            $this->notify($order, 'Serah terima selesai', 'Serah terima kendaraan telah selesai. Kendaraan Anda sekarang tersedia di My Garage.');
            $this->audit('handover_completed', $order, $petugasId);
            return $handover;
        });
    }

    private function assertPdiEntry(PesananMobilMember $order): void
    {
        if (in_array($order->status, ['diserahkan', 'dibatalkan', 'ditolak'], true) || !$order->transaksi || $order->transaksi->status !== 'lunas') {
            throw new RuntimeException('PDI hanya dapat dimulai setelah pembayaran lunas dan order terverifikasi.');
        }
    }

    private function normalizeChecklist(array $input): array
    {
        $normalized = [];
        foreach (self::CHECKLIST as $section => $items) {
            foreach ($items as $key) {
                $value = $input[$key] ?? $input[$section][$key] ?? [];
                $result = strtolower((string) (is_array($value) ? ($value['result'] ?? '') : $value));
                if (!in_array($result, ['pass', 'issue', 'na'], true)) $result = 'issue';
                $normalized[$section][$key] = ['result' => $result, 'notes' => trim((string) (is_array($value) ? ($value['notes'] ?? '') : ''))];
            }
        }
        return $normalized;
    }

    private function legacyChecklistFlags(array $checklist): array
    {
        $mapping = ['mesin' => ['kondisi_mesin'], 'eksterior' => ['body', 'cat', 'kaca'], 'interior' => ['interior'], 'rem' => ['rem'], 'ban' => ['ban', 'tekanan_ban'], 'kelistrikan' => ['aki', 'dashboard_indicator'], 'lampu' => ['lampu_utama', 'lampu_belakang', 'lampu_sein'], 'ac' => ['ac'], 'dokumen' => ['dokumen'], 'final_quality' => ['function_test']];
        return collect($mapping)->mapWithKeys(fn (array $keys, string $legacy): array => [$legacy => collect($keys)->every(fn (string $key): bool => data_get($checklist, collect(self::CHECKLIST)->search(fn (array $items): bool => in_array($key, $items, true)).'.'.$key.'.result') === 'pass')])->all();
    }

    private function notify(PesananMobilMember $order, string $title, string $message): void
    {
        app(ServiceNotificationService::class)->forMemberVehicleOrder($order->fresh('pelanggan'), $title, $message);
    }

    private function audit(string $action, PesananMobilMember $order, ?int $actorId = null): void
    {
        Log::info('member_vehicle_purchase.audit', ['action' => $action, 'order_id' => $order->id_pesanan, 'order_number' => $order->nomor_pesanan, 'actor_id' => $actorId ?: auth()->user()?->id_user, 'at' => now()->toISOString()]);
    }
}
