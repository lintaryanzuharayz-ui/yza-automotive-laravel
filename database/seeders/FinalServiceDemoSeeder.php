<?php

namespace Database\Seeders;

use App\Models\{BookingServis, KendaraanPelanggan, ServiceOperatingHour, ServiceStaffProfile, Sparepart, SparepartFitment, TechnicianWorkingHour};
use App\Services\{BookingServisService, InventoryService};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinalServiceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $database = DB::connection()->getDatabaseName();
        if (!in_array($database, ['yza_automotive_laravel_demo', 'yza_automotive_laravel_uat'], true)) {
            throw new \RuntimeException("FAIL-CLOSED: demo/UAT seeder hanya boleh berjalan pada database terisolasi, aktif: {$database}");
        }

        $admin = DB::table('users')->where('email', 'admin.demo@yza.local')->first();
        $petugas = DB::table('petugas')->where('kode_petugas', 'PTG-DEMO-002')->first();
        $customer = DB::table('pelanggan')->where('kode_pelanggan', 'PLG-DEMO-001')->first();
        $vehicle = KendaraanPelanggan::where('nomor_polisi', 'B 1984 YZA')->firstOrFail();
        $serviceType = DB::table('jenis_servis')->where('nama_servis', 'Ganti Oli')->where('status', 'aktif')->firstOrFail();

        foreach (range(1, 5) as $day) {
            ServiceOperatingHour::updateOrCreate(
                ['day_of_week' => $day],
                ['is_open' => true, 'open_time' => '08:00:00', 'close_time' => '17:00:00', 'break_start' => '12:00:00', 'break_end' => '13:00:00', 'slot_interval_minutes' => 60]
            );
            TechnicianWorkingHour::updateOrCreate(
                ['id_petugas' => $petugas->id_petugas, 'day_of_week' => $day],
                ['is_working' => true, 'open_time' => '08:00:00', 'close_time' => '17:00:00']
            );
        }
        foreach (range(6, 7) as $day) {
            ServiceOperatingHour::updateOrCreate(['day_of_week' => $day], ['is_open' => false, 'open_time' => null, 'close_time' => null, 'break_start' => null, 'break_end' => null, 'slot_interval_minutes' => 60]);
            TechnicianWorkingHour::updateOrCreate(['id_petugas' => $petugas->id_petugas, 'day_of_week' => $day], ['is_working' => false, 'open_time' => null, 'close_time' => null]);
        }
        ServiceStaffProfile::updateOrCreate(
            ['id_petugas' => $petugas->id_petugas],
            ['can_be_technician' => true, 'can_be_service_advisor' => true, 'experience_years' => 8, 'is_bookable' => true]
        );

        $filter = Sparepart::where('kode_sparepart', 'YZA-FLT-OIL01')->firstOrFail();
        $filter->update(['nomor_part_oem' => 'DEMO-OEM-FILTER-001', 'nomor_part_aftermarket' => 'DEMO-AM-FILTER-001', 'deskripsi_spesifikasi' => 'DATA DEMO SINTETIS — bukan klaim kompatibilitas OEM nyata.', 'harga_diperbarui_at' => now()]);
        SparepartFitment::updateOrCreate(
            ['id_sparepart' => $filter->id_sparepart, 'model_kendaraan' => 'GR Supra', 'kode_mesin' => 'YZA-DEMO-B58-001'],
            ['merk_kendaraan' => 'Toyota', 'tahun_mulai' => 2024, 'tahun_akhir' => 2024, 'varian' => 'Performance 3.0L DEMO', 'nomor_part_referensi' => 'DEMO-REF-001', 'status_verifikasi' => 'verified_fit', 'sumber_bukti' => 'DEMO SYNTHETIC — training only, bukan bukti OEM', 'verified_by' => $petugas->id_petugas, 'verified_at' => now(), 'catatan' => 'Fitment sintetis untuk presentasi dosen; tidak boleh dipakai sebagai klaim produk nyata.']
        );

        if ((int) $filter->fresh()->stok < 3) {
            $inventory = app(InventoryService::class);
            $receipt = $inventory->createReceipt(['id_supplier' => DB::table('supplier')->where('kode_supplier', 'SUP-YZA-DEMO-01')->value('id_supplier'), 'tanggal' => now(), 'catatan' => 'DEMO PRESENTASI — stok sintetis untuk latihan alur penerimaan.'], $petugas->id_petugas);
            $inventory->addDetail($receipt->id_stok_masuk, ['id_sparepart' => $filter->id_sparepart, 'jumlah' => 3 - (int) $filter->fresh()->stok, 'harga_beli' => $filter->harga_beli]);
            $inventory->finalize($receipt->id_stok_masuk);
        }

        $date = now()->addDay();
        while ($date->isWeekend()) $date->addDay();
        $booking = BookingServis::where('id_pelanggan', $customer->id_pelanggan)->where('id_kendaraan_pelanggan', $vehicle->id_kendaraan_pelanggan)->where('keluhan', 'DEMO PRESENTASI — alur servis lengkap')->whereIn('status', ['menunggu', 'diterima'])->first();
        if (!$booking) {
            $booking = app(BookingServisService::class)->create($customer->id_pelanggan, $vehicle->id_kendaraan_pelanggan, $serviceType->id_jenis_servis, $date->toDateString(), '10:00', 'DEMO PRESENTASI — alur servis lengkap', 'Data sintetis untuk latihan browser, bukan transaksi operasional.');
        }

        echo "Final demo prepared: booking #{$booking->id_booking_servis}, vehicle {$vehicle->nomor_polisi}, sparepart {$filter->kode_sparepart} stock {$filter->fresh()->stok}.\n";
    }
}
