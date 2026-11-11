<?php

namespace Tests\Feature;

use App\Models\{Mobil, Servis, User};
use App\Services\{MemberVehiclePurchaseService, PaymentService, ServisSparepartService};
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class InspectionConcurrencyStage3Test extends TestCase
{
    use \Tests\Support\CompletesQualityChecks;

    public static function races(): array { return ['two finalizers' => ['finalize','finalize'], 'work versus finalize' => ['qc','finalize'], 'two handovers' => ['handover','handover'], 'PDI versus handover' => ['pdi','handover']]; }

    #[\PHPUnit\Framework\Attributes\DataProvider('races')]
    public function test_independent_connections_serialize_inspection_transitions(string $a, string $b): void
    {
        $this->assertSame('yza_automotive_laravel_test', DB::connection()->getDatabaseName());
        $actor = User::whereHas('role', fn ($q) => $q->where('nama_role', 'petugas'))->firstOrFail()->petugas->id_petugas;
        $workers = []; $target = $car = $part = null; $txIds = [];
        $work = $b === 'finalize';
        try {
            if ($work) {
                $target = Servis::firstOrFail()->replicate();
                $target->forceFill(['kode_servis' => 'S3-RACE-'.bin2hex(random_bytes(5)), 'id_transaksi' => null, 'id_booking_servis' => null,
                    'status' => 'dikerjakan', 'hasil_pemeriksaan' => 'QA', 'id_petugas' => $actor, 'tanggal_selesai' => null])->save();
                $part = \App\Models\Sparepart::firstOrFail()->replicate();
                $part->forceFill(['kode_sparepart' => 'S3-RACE-'.bin2hex(random_bytes(5)), 'stok' => 5])->save();
                $part->fitments()->create(['merk_kendaraan' => $target->kendaraan->merk, 'model_kendaraan' => $target->kendaraan->model, 'status_verifikasi' => 'verified_fit']);
                app(ServisSparepartService::class)->add($target->id_servis, $part->id_sparepart, 1);
                $this->passQc($target);
            } else {
                $car = Mobil::where('status', 'tersedia')->firstOrFail()->replicate();
                $suffix = bin2hex(random_bytes(5));
                $car->forceFill(['kode_mobil' => 'S3-'.$suffix, 'nomor_rangka' => 'VIN-'.$suffix, 'nomor_mesin' => 'ENG-'.$suffix, 'model' => 'Stage3-'.$suffix])->save();
                $svc = app(MemberVehiclePurchaseService::class);
                $customer = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->pelanggan;
                $target = $svc->approve($svc->create($customer, $car, ['nama_pembeli' => 'Race QA']), $actor);
                $txIds[] = $target->id_transaksi;
                app(PaymentService::class)->create($target->id_transaksi, $actor, (string) $target->total_tagihan, 'tunai', null, null);
                $svc->completePdi($target, $actor, $this->pdiInput());
                $svc->schedule($target, now()->addHour()->toDateTimeString());
                $target->forceFill(['jadwal_pengambilan' => now()->subHour()])->save();
            }
            DB::beginTransaction();
            DB::table($target->getTable())->where($target->getKeyName(), $target->getKey())->lockForUpdate()->first();
            foreach ([$a,$b] as $action) {
                $data = ['id' => $target->getKey(), 'actor' => $actor, 'action' => $action, 'now' => now()->toIso8601String(), 'input' => $this->pdiInput('issue')];
                $process = new Process([PHP_BINARY, base_path('tests/Support/inspection_concurrent_worker.php'), base64_encode(json_encode($data))], base_path(), ['APP_ENV' => 'testing'], null, 20);
                $process->start(); $workers[] = $process;
            }
            $deadline = microtime(true) + 8;
            do { $ready = count(array_filter($workers, fn ($p) => str_contains($p->getOutput(), 'READY'))); if ($ready === 2) break; usleep(20000); } while (microtime(true) < $deadline);
            $this->assertSame(2, $ready);
            usleep(200000);
            foreach ($workers as $p) { $this->assertTrue($p->isRunning()); $this->assertStringNotContainsString('DONE', $p->getOutput()); }
            DB::commit();
            foreach ($workers as $p) { $p->wait(); $this->assertTrue($p->isSuccessful(), $p->getErrorOutput()); }
            $target->refresh();
            $done = count(array_filter($workers, fn ($p) => str_contains($p->getOutput(), 'DONE')));
            if ($work) {
                $this->assertSame(1, $done);
                if ($target->id_transaksi) {
                    $txIds[] = $target->id_transaksi;
                    $this->assertSame(4, (int) $part->fresh()->stok);
                    $this->assertSame(1, DB::table('riwayat_stok')->where('referensi_tipe', 'servis')->where('referensi_id', $target->getKey())->count());
                    $this->assertSame('passed', $target->qualityControl->status);
                } else {
                    $this->assertSame(5, (int) $part->fresh()->stok);
                    $this->assertSame('in_progress', $target->qualityControl->status);
                }
            } else {
                $this->assertSame($a === 'handover' ? 2 : 1, $done);
                $delivered = $target->status === 'diserahkan';
                $this->assertSame($delivered ? 1 : 0, DB::table('kendaraan_pelanggan')->where('id_pesanan', $target->getKey())->count());
                $this->assertSame($delivered ? 'lulus' : 'perlu_perbaikan', $target->pdi->status);
                $this->assertSame($delivered ? 1 : 0, DB::table('serah_terima_pesanan_mobil')->where('id_pesanan', $target->getKey())->count());
            }
        } finally {
            if (DB::transactionLevel()) DB::rollBack();
            foreach ($workers as $p) if ($p->isRunning()) $p->stop(1);
            if ($target) {
                if ($work && $target->fresh()->id_transaksi) $txIds[] = $target->fresh()->id_transaksi;
                DB::table('notifikasi')->where('referensi_tipe', $work ? 'servis' : 'pesanan_mobil_member')->where('referensi_id', $target->getKey())->delete();
                if (!$work) DB::table('kendaraan_pelanggan')->where('id_pesanan', $target->getKey())->delete();
                if ($work) DB::table('riwayat_stok')->where('referensi_tipe', 'servis')->where('referensi_id', $target->getKey())->delete();
                $saleId = $work ? null : $target->id_penjualan;
                $target->delete();
                if ($saleId) DB::table('penjualan_mobil')->where('id_penjualan', $saleId)->delete();
            }
            foreach (array_unique($txIds) as $id) { DB::table('pembayaran')->where('id_transaksi', $id)->delete(); DB::table('transaksi')->where('id_transaksi', $id)->delete(); }
            if ($part) $part->delete();
            if ($car) $car->delete();
        }
    }
}
