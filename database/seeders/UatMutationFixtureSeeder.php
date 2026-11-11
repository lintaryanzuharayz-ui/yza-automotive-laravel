<?php

namespace Database\Seeders;

use App\Models\{BookingServis, KendaraanPelanggan, Mobil, Pembayaran, ServiceOperatingHour, ServiceStaffProfile, ServiceWorkOrderAssignment, Servis, Sparepart, TestDrive, TechnicianWorkingHour, Transaksi};
use App\Services\{BookingServisService, InventoryService, ServiceAssignmentService, ServisService, TestDriveService};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Safe fixtures for browser mutation UAT. This seeder is fail-closed to the
 * dedicated UAT database and never resets or deletes existing records.
 */
class UatMutationFixtureSeeder extends Seeder
{
    private const MARKER = 'UAT_MUTATION_202610';

    public function run(): void
    {
        if (DB::connection()->getDatabaseName() !== 'yza_automotive_laravel_uat') {
            throw new RuntimeException('FAIL-CLOSED: mutation fixtures hanya boleh dibuat pada database UAT.');
        }

        $member = DB::table('pelanggan')->where('kode_pelanggan', 'PLG-DEMO-001')->first();
        $vehicle = KendaraanPelanggan::where('id_pelanggan', $member?->id_pelanggan)->where('status', 'aktif')->first();
        $serviceType = DB::table('jenis_servis')->where('nama_servis', 'Ganti Oli')->where('status', 'aktif')->first();
        $advisor = $this->staff('advisor506');
        $mechanic = $this->staff('mekanik506');
        $warehouse = $this->staff('gudang506');
        $finance = $this->staff('finance506');
        $operational = $this->staff('operasional506');

        if (!$member || !$vehicle || !$serviceType || !$advisor || !$mechanic || !$warehouse || !$finance || !$operational) {
            throw new RuntimeException('Fixture dasar member, kendaraan, jenis servis, atau staff UAT tidak lengkap.');
        }

        $this->ensurePersonnelAvailability($advisor, $mechanic);
        $this->ensureServiceBooking($member->id_pelanggan, $vehicle->id_kendaraan_pelanggan, (int) $serviceType->id_jenis_servis);
        $service = $this->ensureWorkOrder($member->id_pelanggan, $vehicle->id_kendaraan_pelanggan, (int) $serviceType->id_jenis_servis, $mechanic, $advisor);
        $this->ensureSparepart($warehouse);
        $this->ensureFinancialFixture($member->id_pelanggan, $finance);
        $this->ensureTestDrive($member->id_pelanggan, $operational);

        $this->command?->info('UAT mutation fixtures ready: '.self::MARKER.'; service='.$service->kode_servis);
    }

    private function staff(string $username): ?object
    {
        return DB::table('petugas')->join('users', 'users.id_user', '=', 'petugas.id_user')->where('users.username', $username)->where('petugas.status', 'aktif')->select('petugas.*')->first();
    }

    private function futureDate(int $days = 5): Carbon
    {
        $date = now()->addDays($days);
        while ($date->isWeekend()) $date->addDay();
        return $date;
    }

    private function ensureServiceBooking(int $customerId, int $vehicleId, int $serviceTypeId): BookingServis
    {
        $booking = BookingServis::where('catatan', 'like', '%'.self::MARKER.'%')->whereNull('id_preferred_petugas')->first();
        if ($booking) return $booking;

        return app(BookingServisService::class)->create(
            $customerId,
            $vehicleId,
            $serviceTypeId,
            $this->futureDate(4)->toDateString(),
            '10:00',
            self::MARKER.' Service Advisor booking',
            self::MARKER.' booking menunggu konfirmasi melalui UI.'
        );
    }

    private function ensureWorkOrder(int $customerId, int $vehicleId, int $serviceTypeId, object $mechanic, object $advisor): Servis
    {
        $service = Servis::where('catatan', 'like', '%'.self::MARKER.'%')->first();
        if ($service) return $service;

        $booking = BookingServis::where('keluhan', self::MARKER.' Mechanic/QC booking')->first();
        if (!$booking) {
            $booking = app(BookingServisService::class)->create($customerId, $vehicleId, $serviceTypeId, $this->futureDate(6)->toDateString(), '11:00', self::MARKER.' Mechanic/QC booking', self::MARKER.' booking setup sah untuk Work Order browser UAT.');
        }
        if ($booking->status === 'menunggu') app(BookingServisService::class)->confirm($booking->id_booking_servis);
        $service = app(ServisService::class)->createFromBooking($booking->id_booking_servis, (int) $mechanic->id_petugas, null, self::MARKER.' Work Order', (int) $advisor->id_petugas, true);
        if (!ServiceWorkOrderAssignment::where('id_servis', $service->id_servis)->where('assignment_role', 'primary')->whereNull('unassigned_at')->exists()) {
            app(ServiceAssignmentService::class)->recordInitial($service, (int) $mechanic->id_petugas, (int) $advisor->id_petugas, self::MARKER.' initial assignment');
        }
        return $service;
    }

    private function ensurePersonnelAvailability(object $advisor, object $mechanic): void
    {
        foreach ([$advisor, $mechanic] as $staff) {
            ServiceStaffProfile::updateOrCreate(['id_petugas' => $staff->id_petugas], ['can_be_technician' => $staff->id_petugas === $mechanic->id_petugas, 'can_be_service_advisor' => true, 'experience_years' => 2, 'is_bookable' => true]);
            foreach (range(1, 5) as $day) TechnicianWorkingHour::updateOrCreate(['id_petugas' => $staff->id_petugas, 'day_of_week' => $day], ['is_working' => true, 'open_time' => '08:00:00', 'close_time' => '17:00:00']);
        }
        foreach (range(1, 5) as $day) ServiceOperatingHour::updateOrCreate(['day_of_week' => $day], ['is_open' => true, 'open_time' => '08:00:00', 'close_time' => '17:00:00', 'break_start' => '12:00:00', 'break_end' => '13:00:00', 'slot_interval_minutes' => 60]);
    }

    private function ensureSparepart(object $warehouse): void
    {
        $category = DB::table('kategori_sparepart')->where('status', 'aktif')->first();
        $supplier = DB::table('supplier')->where('status', 'aktif')->first();
        if (!$category || !$supplier) throw new RuntimeException('Kategori atau supplier sparepart UAT tidak tersedia.');

        $part = Sparepart::updateOrCreate(
            ['kode_sparepart' => self::MARKER.'-SP'],
            ['id_kategori' => $category->id_kategori, 'nama_sparepart' => self::MARKER.' Filter', 'merk' => 'YZA Synthetic', 'harga_beli' => 10000, 'harga_jual' => 15000, 'satuan' => 'pcs', 'stok_minimum' => 1, 'status' => 'aktif']
        );
        if ((int) $part->stok > 0 || DB::table('stok_masuk')->where('catatan', 'like', '%'.self::MARKER.'%')->exists()) return;

        $inventory = app(InventoryService::class);
        $receipt = $inventory->createReceipt(['id_supplier' => $supplier->id_supplier, 'tanggal' => now(), 'catatan' => self::MARKER.' stock receipt'], (int) $warehouse->id_petugas);
        $inventory->addDetail($receipt->id_stok_masuk, ['id_sparepart' => $part->id_sparepart, 'jumlah' => 2, 'harga_beli' => 10000]);
        $inventory->finalize($receipt->id_stok_masuk);
    }

    private function ensureFinancialFixture(int $customerId, object $finance): void
    {
        $tx = Transaksi::where('catatan', 'like', '%'.self::MARKER.'%')->first();
        if (!$tx) {
            $tx = Transaksi::create(['kode_transaksi' => 'TRX-UAT-MUT-202610', 'id_pelanggan' => $customerId, 'id_petugas' => $finance->id_petugas, 'jenis_transaksi' => 'servis', 'tanggal' => now(), 'subtotal' => 250000, 'diskon' => 0, 'total' => 250000, 'status' => 'belum_bayar', 'catatan' => self::MARKER.' financial adjustment fixture']);
        }
        Pembayaran::firstOrCreate(['kode_pembayaran' => 'PAY-UAT-MUT-202610'], ['id_transaksi' => $tx->id_transaksi, 'tanggal' => now(), 'arah' => 'masuk', 'metode' => 'transfer', 'jumlah' => 250000, 'bukti_transfer' => self::MARKER.'-EVIDENCE', 'status' => 'menunggu', 'catatan' => self::MARKER.' no provider payment']);
    }

    private function ensureTestDrive(int $customerId, object $operational): void
    {
        if (TestDrive::where('catatan', 'like', '%'.self::MARKER.'%')->exists()) return;
        $car = Mobil::where('status', 'tersedia')->first();
        if (!$car) throw new RuntimeException('Tidak ada mobil tersedia untuk fixture Test Drive UAT.');
        app(TestDriveService::class)->create($customerId, $car->id_mobil, $this->futureDate(7)->toDateString(), '14:00', 'YZA Showroom UAT', self::MARKER.' operational status fixture');
    }
}
