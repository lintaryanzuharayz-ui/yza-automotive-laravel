<?php

namespace Tests\Feature;

use App\Models\{PromoRedemption, Sparepart, Transaksi, User};
use App\Services\BookingCuciService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PromoConcurrencyStage4Test extends TestCase
{
    use \Tests\Support\PromoIntegrityFixtures;

    public static function races(): array { return ['one quota two customers' => ['quota'], 'reserved invoice duplicate settlement' => ['settlement'], 'last sparepart' => ['stock'], 'double finalize with promo' => ['same'], 'same vehicle checkout' => ['car']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('races')]
    public function test_business_effects_serialize_across_independent_connections(string $case): void
    {
        $this->assertSame('yza_automotive_laravel_test', DB::connection()->getDatabaseName());
        $p = $car = $part = null; $orders = []; $txIds = []; $workers = []; $payloads = []; $customers = []; $users = [];
        try {
            $p = $this->stage4Promo(in_array($case, ['quota', 'settlement'], true) ? ['quota' => 1] : []);
            if ($case === 'quota') {
                for ($n = 0; $n < 2; $n++) {
                    $suffix = bin2hex(random_bytes(5));
                    $user = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->replicate();
                    $user->forceFill(['username' => 's4-'.$suffix, 'email' => $suffix.'@stage4.example.test'])->save(); $users[] = $user;
                    $customer = \App\Models\Pelanggan::firstOrFail()->replicate();
                    $customer->forceFill(['kode_pelanggan' => 'S4-'.$suffix, 'id_user' => $user->getKey()])->save(); $customers[] = $customer;
                }
                $this->assertCount(2, $customers);
                foreach ($customers as $customer) {
                    $wash = $this->stage4Wash(); $wash->forceFill(['id_pelanggan' => $customer->id_pelanggan])->save(); $orders[] = $wash;
                    $payloads[] = ['action' => 'wash', 'id' => $wash->getKey()];
                }
                $table = 'promo_member'; $column = 'id_promo'; $lockId = $p->getKey();
            } elseif ($case === 'settlement') {
                $wash = $this->stage4Wash(); $orders[] = $wash;
                $tx = app(BookingCuciService::class)->finalizeWithPromo($wash->getKey(), $this->stage4Actor(), $p->getKey()); $txIds[] = $tx->getKey();
                $payloads = array_fill(0, 2, ['action' => 'pay', 'id' => $tx->getKey(), 'amount' => $tx->total]);
                $table = 'transaksi'; $column = 'id_transaksi'; $lockId = $tx->getKey();
            } elseif ($case === 'car') {
                $car = $this->stage4Car(); $input = $this->stage4SaleInput($car);
                $payloads = array_fill(0, 2, ['action' => 'car', 'input' => $input]);
                $table = 'mobil'; $column = 'id_mobil'; $lockId = $car->getKey();
            } else {
                $part = Sparepart::firstOrFail()->replicate(); $part->forceFill(['kode_sparepart' => 'S4-RACE-'.bin2hex(random_bytes(5)), 'stok' => 1])->save();
                $orders[] = $this->stage4Work($part->getKey());
                if ($case === 'stock') $orders[] = $this->stage4Work($part->getKey());
                $payloads = [['action' => 'finalize', 'id' => $orders[0]->getKey()], ['action' => 'finalize', 'id' => $orders[$case === 'stock' ? 1 : 0]->getKey()]];
                $table = $case === 'stock' ? 'sparepart' : 'servis'; $column = $case === 'stock' ? 'id_sparepart' : 'id_servis';
                $lockId = $case === 'stock' ? $part->getKey() : $orders[0]->getKey();
            }
            DB::beginTransaction();
            DB::table($table)->where($column, $lockId)->lockForUpdate()->first();
            foreach ($payloads as $payload) {
                $data = $payload + ['promo' => $p->getKey(), 'actor' => $this->stage4Actor(), 'now' => now()->toIso8601String()];
                $worker = new Process([PHP_BINARY, base_path('tests/Support/promo_concurrent_worker.php'), base64_encode(json_encode($data))], base_path(), ['APP_ENV' => 'testing'], null, 20);
                $worker->start(); $workers[] = $worker;
            }
            $deadline = microtime(true) + 8;
            do { $ready = count(array_filter($workers, fn ($w) => str_contains($w->getOutput(), 'READY'))); if ($ready === 2) break; usleep(20000); } while (microtime(true) < $deadline);
            $this->assertSame(2, $ready, implode(' ', array_map(fn ($w) => $w->getErrorOutput(), $workers)));
            usleep(200000);
            foreach ($workers as $w) { $this->assertTrue($w->isRunning()); $this->assertStringNotContainsString('DONE', $w->getOutput()); }
            DB::commit();
            foreach ($workers as $w) { $w->wait(); $this->assertTrue($w->isSuccessful(), $w->getErrorOutput()); }
            $this->assertSame(1, count(array_filter($workers, fn ($w) => str_contains($w->getOutput(), 'DONE'))));
            $this->assertSame(1, count(array_filter($workers, fn ($w) => str_contains($w->getOutput(), 'REJECTED'))));
            if ($case === 'quota') {
                $ids = array_map(fn ($o) => $o->getKey(), $orders);
                $this->assertSame(1, PromoRedemption::where('id_promo', $p->getKey())->where('status', 'reserved')->count());
                $this->assertSame(0, PromoRedemption::where('id_promo', $p->getKey())->where('status', 'completed')->count());
                $this->assertSame(1, \App\Models\BookingCuci::whereKey($ids)->whereNotNull('id_transaksi')->count());
                $this->assertSame(0, DB::table('pembayaran')->whereIn('id_transaksi', PromoRedemption::where('id_promo', $p->getKey())->select('id_transaksi'))->count());
            } elseif ($case === 'settlement') {
                $this->assertSame(1, PromoRedemption::where('id_promo', $p->getKey())->where('status', 'completed')->count());
                $this->assertSame(1, Transaksi::whereKey($txIds)->where('status', 'lunas')->count());
                $this->assertSame(1, DB::table('pembayaran')->whereIn('id_transaksi', $txIds)->count());
            } elseif ($case === 'car') {
                $sales = $car->penjualan()->get();
                $this->assertCount(1, $sales); $this->assertSame('dibooking', $car->fresh()->status);
                $this->assertSame('90000000.00', $sales->first()->total_akhir);
            } else {
                $ids = array_map(fn ($s) => $s->getKey(), $orders);
                $this->assertSame(1, \App\Models\Servis::whereKey($ids)->whereNotNull('id_transaksi')->count());
                $this->assertSame(0, (int) $part->fresh()->stok);
                $this->assertSame(1, DB::table('riwayat_stok')->where('id_sparepart', $part->getKey())->count());
                $this->assertSame(1, Transaksi::whereHas('servis', fn ($q) => $q->whereIn('id_servis', $ids))->where('diskon', '>', 0)->count());
            }
        } finally {
            if (DB::transactionLevel()) DB::rollBack();
            foreach ($workers as $w) if ($w->isRunning()) $w->stop(1);
            foreach ($orders as $order) {
                $order->refresh(); if ($order->id_transaksi) $txIds[] = $order->id_transaksi;
                $work = $order instanceof \App\Models\Servis; $vehicleId = $work ? $order->id_kendaraan_pelanggan : null;
                DB::table('notifikasi')->where('referensi_tipe', $work ? 'servis' : 'booking_cuci')->where('referensi_id', $order->getKey())->delete();
                if ($work) DB::table('riwayat_stok')->where('referensi_tipe', 'servis')->where('referensi_id', $order->getKey())->delete();
                $order->delete();
                if ($vehicleId) DB::table('kendaraan_pelanggan')->where('id_kendaraan_pelanggan', $vehicleId)->delete();
            }
            if ($car) {
                $sales = \App\Models\PenjualanMobil::where('id_mobil', $car->getKey())->get();
                foreach ($sales as $sale) { $txIds[] = $sale->id_transaksi; $sale->delete(); }
                $car->delete();
            }
            foreach (array_unique($txIds) as $id) {
                DB::table('pembayaran')->where('id_transaksi', $id)->delete();
                DB::table('promo_redemptions')->where('id_transaksi', $id)->delete();
                DB::table('transaksi')->where('id_transaksi', $id)->delete();
            }
            if ($part) { DB::table('notifikasi')->where('referensi_tipe', 'sparepart')->where('referensi_id', $part->getKey())->delete(); $part->delete(); }
            if ($p) $p->delete();
            foreach ($customers as $customer) $customer->delete();
            foreach ($users as $user) $user->delete();
        }
    }
}
