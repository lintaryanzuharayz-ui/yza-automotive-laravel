<?php

namespace Tests\Feature;

use App\Models\{Mobil, User};
use App\Services\{MemberVehiclePurchaseService, PaymentService, VehicleAvailabilityService, VehicleInventoryService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VehicleAggregateInventoryFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function customer(): User { return User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail(); }
    private function actor(): int { return (int) User::whereHas('role', fn ($q) => $q->where('nama_role', 'petugas'))->firstOrFail()->petugas->id_petugas; }
    private function car(): Mobil { return Mobil::where('merk', 'Lamborghini')->where('model', 'Revuelto')->firstOrFail(); }
    private function stock(): array { return app(VehicleAvailabilityService::class)->for($this->car()); }

    public function test_reservation_settlement_handover_and_payment_failure_change_aggregate_availability_once(): void
    {
        $customer = $this->customer()->pelanggan;
        $actor = $this->actor();
        $purchase = app(MemberVehiclePurchaseService::class);
        $input = ['nama_pembeli' => $customer->nama_lengkap, 'telepon_pembeli' => '08123456789', 'alamat_pembeli' => 'Alamat QA'];

        $this->assertSame(8, $this->stock()['stock']);
        $first = $purchase->create($customer, $this->car(), $input);
        $this->assertSame(7, $this->stock()['stock']);
        $approved = $purchase->approve($first, $actor);
        app(PaymentService::class)->create($approved->id_transaksi, $actor, (string) $approved->total_tagihan, 'tunai', null, 'Aggregate stock QA');
        $this->assertSame(7, $this->stock()['stock']);

        $items = array_fill_keys(['mesin', 'eksterior', 'interior', 'rem', 'ban', 'kelistrikan', 'lampu', 'ac', 'dokumen', 'final_quality'], true);
        $purchase->savePdi($approved, $actor, $items);
        $purchase->schedule($approved, now()->addDay()->setTime(10, 0)->toDateTimeString(), 'YZA Automotive', 'Aggregate pickup');
        $purchase->handover($approved, $actor, ['nama_penerima' => 'Aggregate QA', 'nomor_identitas' => 'AGGREGATE-QA']);
        $this->assertSame(1, $this->stock()['sold_units']);
        $this->assertSame(7, $this->stock()['stock']);

        $second = $purchase->create($customer, $this->car(), $input);
        $this->assertSame(6, $this->stock()['stock']);
        $purchase->reject($second, $actor, 'QA cancellation');
        $this->assertSame(7, $this->stock()['stock']);

        $third = $purchase->approve($purchase->create($customer, $this->car(), $input), $actor);
        $payment = app(PaymentService::class)->create($third->id_transaksi, $actor, (string) $third->total_tagihan, 'transfer', null, 'QA rejected payment');
        $this->assertSame(6, $this->stock()['stock']);
        app(PaymentService::class)->reject($payment->id_pembayaran, $actor);
        $this->assertSame('dibatalkan', $third->fresh()->status);
        $this->assertSame(7, $this->stock()['stock']);
    }

    public function test_admin_stock_adjustment_is_audited_and_cannot_overwrite_sold_units(): void
    {
        $car = $this->car();
        $before = $car->getAttribute('availability') ?? app(VehicleAvailabilityService::class)->for($car);
        $inventory = app(VehicleInventoryService::class)->adjust($car, 10, 'QA restock', 'Penambahan operasional terverifikasi.');
        $this->assertSame(10, (int) $inventory->stock_total);
        $this->assertSame(10, app(VehicleAvailabilityService::class)->for($car)['stock']);
        $this->assertDatabaseHas('vehicle_inventory_histories', ['id_vehicle_inventory' => $inventory->id_vehicle_inventory, 'jenis' => 'penyesuaian', 'jumlah_perubahan' => 2]);
        $this->assertSame(8, (int) $before['stock']);
    }
}
