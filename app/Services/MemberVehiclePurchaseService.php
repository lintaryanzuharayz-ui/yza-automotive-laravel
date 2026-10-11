<?php

namespace App\Services;

use App\Models\{Mobil, PdiPesananMobil, Pelanggan, PenjualanMobil, PesananMobilMember, SerahTerimaPesananMobil, Transaksi, VehicleInventory};
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MemberVehiclePurchaseService
{
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

    public function schedule(PesananMobilMember $order, string $schedule): PesananMobilMember
    {
        if (!in_array($order->status, ['menunggu_pembayaran', 'lunas', 'pdi_lulus', 'siap_diambil'], true)) throw new RuntimeException('Pesanan belum dapat dijadwalkan.');
        $order->update(['jadwal_pengambilan' => $schedule]);
        return $order->fresh();
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
            $pdi->forceFill($values + ['id_pesanan' => $order->id_pesanan, 'id_petugas' => $petugasId, 'status' => in_array(false, $values, true) ? 'gagal' : 'lulus', 'catatan' => $input['catatan'] ?? null, 'diperiksa_at' => now()])->save();
            $order->update(['status' => $pdi->status === 'lulus' ? 'siap_diambil' : 'pdi_gagal']);
            return $pdi;
        });
    }

    public function handover(PesananMobilMember $order, int $petugasId, array $input): SerahTerimaPesananMobil
    {
        return DB::transaction(function () use ($order, $petugasId, $input): SerahTerimaPesananMobil {
            $order = PesananMobilMember::lockForUpdate()->with(['transaksi', 'penjualan'])->findOrFail($order->id_pesanan);
            if ($order->status !== 'siap_diambil' || !$order->transaksi || $order->transaksi->status !== 'lunas') throw new RuntimeException('Pesanan belum memenuhi syarat serah-terima.');
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
            $order->forceFill(['status' => 'diserahkan', 'diserahkan_at' => now()])->save();
            app(VerifiedCarOwnershipService::class)->syncVehicleFromCompletedSale($order->penjualan->fresh(['mobil', 'transaksi']));
            return $handover;
        });
    }
}
