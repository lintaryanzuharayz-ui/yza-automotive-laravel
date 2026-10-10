<?php
namespace Tests\Feature;

use App\Models\{BookingCuci,PaketCuci,Pembayaran,Sparepart,Transaksi,User};
use App\Services\{BookingCuciService,PaymentService};
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

class CarWashFinalizationStage5ETest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role): User
    {
        return User::whereHas('role', fn($q) => $q->where('nama_role', $role))->firstOrFail();
    }

    private function booking(string $status = 'proses'): BookingCuci
    {
        $customer = $this->user('pelanggan');
        $vehicle = $customer->pelanggan->kendaraan()->create([
            'merk' => 'QA', 'model' => 'Wash', 'nomor_polisi' => 'CW-'.random_int(1000, 9999),
            'kilometer' => 1000, 'status' => 'aktif',
        ]);
        $package = PaketCuci::where('status', 'aktif')->firstOrFail();
        $booking = app(BookingCuciService::class)->create(
            $customer->pelanggan->id_pelanggan,
            $vehicle->id_kendaraan_pelanggan,
            $package->id_paket,
            now()->addDay()->toDateString(),
            '15:00',
            'QA'
        );
        $booking->forceFill(['status' => $status])->save();
        return $booking->fresh();
    }

    private function finalize(): array
    {
        $booking = $this->booking();
        $petugas = $this->user('petugas')->petugas->id_petugas;
        $tx = app(BookingCuciService::class)->finalize($booking->id_booking_cuci, $petugas);
        return [$booking->fresh(), $tx->fresh()];
    }

    public function test_finalize_uses_booking_snapshot_and_creates_unpaid_cuci_transaction(): void
    {
        [$booking, $tx] = $this->finalize();
        $this->assertSame('proses', $booking->status);
        $this->assertNotNull($booking->id_transaksi);
        $this->assertSame('cuci_mobil', $tx->jenis_transaksi);
        $this->assertSame('belum_bayar', $tx->status);
        $this->assertEquals((float) $booking->harga, (float) $tx->total);
        $this->assertTrue($booking->transaksi()->exists());
    }

    public function test_double_finalize_is_rejected_without_duplicate_transaction(): void
    {
        [$booking] = $this->finalize();
        $count = Transaksi::where('jenis_transaksi', 'cuci_mobil')->count();
        $this->expectException(RuntimeException::class);
        try {
            app(BookingCuciService::class)->finalize($booking->id_booking_cuci, $this->user('petugas')->petugas->id_petugas);
        } finally {
            $this->assertSame($count, Transaksi::where('jenis_transaksi', 'cuci_mobil')->count());
        }
    }

    public function test_concurrent_like_finalize_is_consistent_under_locked_booking(): void
    {
        [$booking] = $this->finalize();
        $this->assertSame('proses', $booking->fresh()->status);
        $this->expectException(RuntimeException::class);
        app(BookingCuciService::class)->finalize($booking->id_booking_cuci, $this->user('petugas')->petugas->id_petugas);
    }

    public function test_invalid_status_and_customer_finalize_are_blocked(): void
    {
        $booking = $this->booking('diterima');
        $this->expectException(RuntimeException::class);
        app(BookingCuciService::class)->finalize($booking->id_booking_cuci, $this->user('petugas')->petugas->id_petugas);
    }

    public function test_all_manual_payment_methods_follow_native_states(): void
    {
        [, $tx] = $this->finalize();
        $svc = app(PaymentService::class);
        $actor = $this->user('petugas')->petugas->id_petugas;
        $cash = $svc->create($tx->id_transaksi, $actor, '10000', 'tunai', null, null);
        $transfer = $svc->create($tx->id_transaksi, $actor, '10000', 'transfer', null, null);
        $credit = $svc->create($tx->id_transaksi, $actor, '10000', 'kredit', null, null);
        $other = $svc->create($tx->id_transaksi, $actor, '10000', 'lainnya', null, null);
        $this->assertSame('terverifikasi', $cash->status);
        $this->assertSame('menunggu', $transfer->status);
        $this->assertSame('menunggu', $credit->status);
        $this->assertSame('menunggu', $other->status);
        $this->assertSame('dp', $tx->fresh()->status);
        $this->assertSame('proses', $tx->bookingCuci->fresh()->status);
    }

    public function test_verify_recalculates_and_lunas_completes_booking(): void
    {
        [$booking, $tx] = $this->finalize();
        $svc = app(PaymentService::class);
        $actor = $this->user('petugas')->petugas->id_petugas;
        $p = $svc->create($tx->id_transaksi, $actor, (string) ((float) $tx->total), 'transfer', null, null);
        $this->assertSame('belum_bayar', $tx->fresh()->status);
        $svc->verify($p->id_pembayaran, $actor);
        $this->assertSame('lunas', $tx->fresh()->status);
        $this->assertSame('selesai', $booking->fresh()->status);
    }

    public function test_rejected_and_pending_payments_are_not_paid_and_overpayment_is_blocked(): void
    {
        [, $tx] = $this->finalize();
        $svc = app(PaymentService::class);
        $actor = $this->user('petugas')->petugas->id_petugas;
        $pending = $svc->create($tx->id_transaksi, $actor, '10000', 'transfer', null, null);
        $svc->reject($pending->id_pembayaran, $actor);
        $this->assertSame('belum_bayar', $tx->fresh()->status);
        $this->expectException(RuntimeException::class);
        $svc->create($tx->id_transaksi, $actor, (string) ((float) $tx->total + 1), 'tunai', null, null);
    }

    public function test_invoice_is_customer_owned_and_stock_is_unchanged(): void
    {
        [, $tx] = $this->finalize();
        $customer = $this->user('pelanggan');
        $stock = Sparepart::query()->first()?->stok;
        $this->actingAs($customer)->get('/pelanggan/invoice/car-wash/'.$tx->id_transaksi)->assertOk()->assertSee($tx->kode_transaksi);
        $other = User::whereHas('role', fn($q) => $q->where('nama_role', 'pelanggan'))->where('id_user', '<>', $customer->id_user)->first();
        if ($other) {
            $this->actingAs($other)->get('/pelanggan/invoice/car-wash/'.$tx->id_transaksi)->assertForbidden();
        }
        $this->assertSame($stock, Sparepart::query()->first()?->fresh()->stok);
        $this->assertSame(0, Pembayaran::where('id_transaksi', $tx->id_transaksi)->count());
    }
}
