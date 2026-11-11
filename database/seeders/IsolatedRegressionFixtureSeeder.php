<?php

namespace Database\Seeders;

use App\Models\{Membership, Mobil, Pelanggan, PembayaranGateway};
use App\Services\{MidtransClient, MidtransGatewayProcessor, VehicleColorStockService, VehicleInventoryService};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Completes synthetic prerequisites on the disposable regression server only. */
class IsolatedRegressionFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $server = DB::selectOne('SELECT @@port AS port, @@datadir AS directory');
        $expected = realpath(base_path('.remediation/mysql-data'));
        if (!app()->environment('testing') || DB::getDatabaseName() !== 'yza_automotive_laravel_test'
            || (int) $server->port === 3306 || !$expected || realpath($server->directory) !== $expected) {
            throw new RuntimeException('Only the disposable regression database may receive these fixtures.');
        }

        // Staff are seeded after schema migrations, so their dependent profiles
        // and working hours cannot have been created by the earlier migration.
        foreach (DB::table('petugas')->pluck('id_petugas') as $id) {
            DB::table('service_staff_profiles')->updateOrInsert(['id_petugas' => $id], [
                'can_be_technician' => false, 'can_be_service_advisor' => false,
                'is_bookable' => false, 'created_at' => now(), 'updated_at' => now(),
            ]);
            for ($day = 1; $day <= 7; $day++) {
                DB::table('technician_working_hours')->insertOrIgnore([
                    'id_petugas' => $id, 'day_of_week' => $day, 'is_working' => $day <= 5,
                    'open_time' => $day <= 5 ? '08:00:00' : null, 'close_time' => $day <= 5 ? '17:00:00' : null,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // These physical units are inserted after the inventory migration.
        // Register their real synthetic identities through the inventory service.
        foreach (Mobil::where('kode_mobil', 'like', 'STAGE2-UNIT-%')->where('status', 'tersedia')->get() as $car) {
            if (!app(VehicleColorStockService::class)->sold($car)) {
                app(VehicleInventoryService::class)->registerPhysicalUnit($car);
            }
        }

        // Exercise the real activation processor using an in-process synthetic
        // provider. Do not backdate membership or weaken paid-event validation.
        $originalClient = app(MidtransClient::class);
        app()->instance(MidtransClient::class, new class extends MidtransClient {
            public function getTransactionStatus(string $orderId): array
            {
                $gateway = PembayaranGateway::where('order_id', $orderId)->firstOrFail();
                return ['order_id' => $orderId, 'gross_amount' => (string) $gateway->gross_amount,
                    'transaction_status' => 'settlement', 'status_code' => '200',
                    'payment_type' => 'bank_transfer', 'transaction_id' => 'SYNTHETIC-'.$gateway->id_pembayaran_gateway,
                    'fraud_status' => 'accept', 'currency' => 'IDR'];
            }
        });
        try {
            foreach (Membership::whereIn('member_id', ['YZA-STAGE2', 'YZA-STAGE2-2'])->get() as $membership) {
                if ($membership->gateway_payment_id) {
                    continue;
                }
                $membership->forceFill(['status' => 'pending_payment', 'activated_at' => null])->save();
                $gateway = PembayaranGateway::forceCreate([
                    'id_membership' => $membership->id_membership, 'provider' => 'midtrans',
                    'order_id' => 'YZA-MBR-'.$membership->id_membership.'-ISOLATED',
                    'gross_amount' => $membership->activation_fee, 'status_provider' => 'pending',
                    'initiated_by' => Pelanggan::findOrFail($membership->id_pelanggan)->id_user,
                ]);
                app(MidtransGatewayProcessor::class)->inquiry($gateway);
            }
        } finally {
            app()->instance(MidtransClient::class, $originalClient);
        }
    }
}
