<?php

namespace Tests\Feature;

use App\Models\{Membership, Pelanggan, PembayaranGateway, Transaksi, User};
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PaymentConcurrencyStage2Test extends TestCase
{
    public static function concurrentCases(): array
    {
        return ['membership duplicate' => [true, false], 'invoice duplicate' => [false, false],
            'membership different attempts' => [true, true], 'invoice different attempts' => [false, true]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('concurrentCases')]
    public function test_simultaneous_callbacks_serialize_on_the_business_target(bool $membershipPurpose, bool $differentAttempts): void
    {
        $this->assertSame('yza_automotive_laravel_test', DB::connection()->getDatabaseName());
        $user = null; $customer = null; $target = null; $gateways = []; $workers = [];
        try {
            // Committed, uniquely identified fixtures are needed so independent
            // MySQL connections can see them. Delete only these fixtures in finally.
            DB::transaction(function () use ($membershipPurpose, $differentAttempts, &$user, &$customer, &$target, &$gateways) {
                $suffix = bin2hex(random_bytes(6));
                $user = User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail()->replicate();
                $user->forceFill(['username' => 's2-'.$suffix, 'email' => $suffix.'@stage2.example.test'])->save();
                $customer = Pelanggan::firstOrFail()->replicate();
                $customer->forceFill(['id_user' => $user->id_user, 'kode_pelanggan' => 'S2-'.$suffix])->save();
                $target = $membershipPurpose
                    ? Membership::forceCreate(['id_pelanggan' => $customer->id_pelanggan, 'member_id' => 'S2-'.$suffix,
                        'status' => 'pending_payment', 'activation_fee' => 249000, 'registered_at' => now()])
                    : Transaksi::forceCreate(['kode_transaksi' => 'S2-'.$suffix, 'id_pelanggan' => $customer->id_pelanggan,
                        'id_petugas' => DB::table('petugas')->where('status', 'aktif')->value('id_petugas'),
                        'jenis_transaksi' => 'cuci_mobil', 'tanggal' => now(), 'subtotal' => 100000, 'diskon' => 0, 'total' => 100000, 'status' => 'belum_bayar']);
                for ($i = 0; $i < ($differentAttempts ? 2 : 1); $i++) {
                    $gateways[] = PembayaranGateway::forceCreate([
                        $membershipPurpose ? 'id_membership' : 'id_transaksi' => $target->getKey(),
                        'provider' => 'midtrans', 'order_id' => ($membershipPurpose ? 'YZA-MBR-' : 'YZA-').$target->getKey().'-'.$suffix.$i,
                        'gross_amount' => $membershipPurpose ? 249000 : 100000, 'status_provider' => 'pending', 'initiated_by' => $user->id_user]);
                }
            });
            DB::beginTransaction();
            DB::table($target->getTable())->where($target->getKeyName(), $target->getKey())->lockForUpdate()->first();
            for ($i = 0; $i < 2; $i++) {
                $g = $gateways[$differentAttempts ? $i : 0];
                $data = ['order_id' => $g->order_id, 'gross_amount' => (string) $g->gross_amount, 'status_code' => '200',
                    'transaction_status' => 'settlement', 'payment_type' => 'qris', 'transaction_id' => 's2-'.hash('sha256', $g->order_id)];
                $data['signature_key'] = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].'stage2-synthetic-key');
                $workers[] = new Process([PHP_BINARY, base_path('tests/Support/midtrans_concurrent_worker.php'), base64_encode(json_encode($data))], base_path(), ['APP_ENV' => 'testing'], null, 20);
                $workers[$i]->start();
            }
            $deadline = microtime(true) + 8;
            do {
                $ready = count(array_filter($workers, fn ($p) => str_contains($p->getOutput(), 'READY')));
                if ($ready === 2) break;
                usleep(20000);
            } while (microtime(true) < $deadline);
            $this->assertSame(2, $ready, implode(' ', array_map(fn ($p) => $p->getErrorOutput(), $workers)));
            usleep(200000);
            foreach ($workers as $worker) { $this->assertTrue($worker->isRunning()); $this->assertStringNotContainsString('DONE', $worker->getOutput()); }
            DB::commit();
            foreach ($workers as $worker) { $worker->wait(); $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput()); }
            $states = PembayaranGateway::whereKey(array_map(fn ($g) => $g->getKey(), $gateways))->pluck('status_provider')->all();
            $this->assertSame(1, count(array_filter($states, fn ($s) => $s === 'settlement')));
            if ($differentAttempts) $this->assertContains('requires_review', $states);
            $this->assertSame($membershipPurpose ? 'active' : 'lunas', $target->fresh()->status);
            if (!$membershipPurpose) $this->assertSame(1, DB::table('pembayaran')->where('id_transaksi', $target->getKey())->count());
            $this->assertSame(0, DB::table('event_pembayaran_gateway')->select('event_key')->groupBy('event_key')->havingRaw('COUNT(*) > 1')->get()->count());
        } finally {
            if (DB::transactionLevel()) DB::rollBack();
            foreach ($workers as $worker) if ($worker->isRunning()) $worker->stop(1);
            if ($membershipPurpose && $target) $target->forceFill(['gateway_payment_id' => null])->save();
            if ($gateways) PembayaranGateway::whereKey(array_map(fn ($g) => $g->getKey(), $gateways))->delete();
            if (!$membershipPurpose && $target) DB::table('pembayaran')->where('id_transaksi', $target->getKey())->delete();
            if ($target) $target->delete();
            if ($customer) $customer->delete();
            if ($user) $user->delete();
        }
    }
}
