<?php

namespace Tests\Feature;

use App\Models\{BookingServis, JenisServis, Petugas, ServiceStaffProfile, Servis, TechnicianTimeOff, User};
use App\Services\{BookingServisService, ServicePersonnelAvailabilityService, ServisService};
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use RuntimeException;
use Tests\TestCase;

class ServicePersonnelPhase3CIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    private function date(): string
    {
        return Carbon::today()->next(Carbon::TUESDAY)->toDateString();
    }

    private function type(): JenisServis
    {
        return JenisServis::where('status', 'aktif')->firstOrFail();
    }

    private function member(): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', 'pelanggan'))->firstOrFail();
    }

    private function admin(): User
    {
        return User::whereHas('role', fn ($q) => $q->where('nama_role', 'admin'))->firstOrFail();
    }

    private function vehicle(User $member): \App\Models\KendaraanPelanggan
    {
        return $member->pelanggan->kendaraan()->create([
            'merk' => 'QA', 'model' => 'Phase 3C', 'nomor_polisi' => 'P3C-'.random_int(1000, 9999),
            'kilometer' => 10000, 'status' => 'aktif',
        ]);
    }

    private function technician(bool $advisor = false): ServiceStaffProfile
    {
        $profile = ServiceStaffProfile::where('can_be_technician', false)
            ->where('can_be_service_advisor', false)
            ->firstOrFail();
        $profile->update(['can_be_technician' => true, 'is_bookable' => true, 'can_be_service_advisor' => $advisor]);
        return $profile->fresh('petugas');
    }

    public function test_preference_survives_acceptance_work_order_and_duplicate_protection(): void
    {
        $member = $this->member();
        $tech = $this->technician();
        $advisor = $this->technician(true);
        $vehicle = $this->vehicle($member);
        $booking = app(BookingServisService::class)->create(
            $member->pelanggan->id_pelanggan, $vehicle->id_kendaraan_pelanggan, $this->type()->id_jenis_servis,
            $this->date(), '10:00', null, null, $tech->id_petugas
        );

        $this->actingAs($this->admin())->get(route('admin.booking-servis.show', $booking))
            ->assertOk()->assertSee('Preferensi Teknisi Pelanggan')->assertSee($tech->petugas->nama_lengkap);
        $this->actingAs($this->admin())->post(route('admin.booking-servis.action', [$booking, 'confirm']))->assertRedirect();
        $this->assertSame($tech->id_petugas, $booking->fresh()->id_preferred_petugas);

        $wo = app(ServisService::class)->createFromBooking($booking->id_booking_servis, $tech->id_petugas, 10000, null, $advisor->id_petugas, true);
        $duplicate = app(ServisService::class)->createFromBooking($booking->id_booking_servis, $advisor->id_petugas, 10000, null, null, false);
        $this->assertSame($wo->id_servis, $duplicate->id_servis);
        $this->assertSame(1, Servis::where('id_booking_servis', $booking->id_booking_servis)->count());
        $this->assertDatabaseHas('booking_servis', ['id_booking_servis' => $booking->id_booking_servis, 'id_preferred_petugas' => $tech->id_petugas, 'status' => 'diproses']);
        $this->assertDatabaseHas('servis', ['id_servis' => $wo->id_servis, 'id_petugas' => $tech->id_petugas, 'id_service_advisor' => $advisor->id_petugas]);

        $this->actingAs($this->admin())->get(route('admin.servis.show', $wo))
            ->assertRedirect(route('admin.servis.pemeriksaan', $wo));
        $this->actingAs($this->admin())->get(route('admin.servis.pemeriksaan', $wo))
            ->assertOk()->assertSee('Preferensi Teknisi Pelanggan')->assertSee($tech->petugas->nama_lengkap)
            ->assertSee('Teknisi Penanggung Jawab')->assertSee('Service Advisor')->assertSee($advisor->petugas->nama_lengkap);
    }

    public function test_preference_can_differ_from_final_and_is_rejected_when_final_is_unavailable(): void
    {
        $member = $this->member();
        $preferred = $this->technician();
        $final = $this->technician();
        $vehicle = $this->vehicle($member);
        $booking = app(BookingServisService::class)->create(
            $member->pelanggan->id_pelanggan, $vehicle->id_kendaraan_pelanggan, $this->type()->id_jenis_servis,
            $this->date(), '13:00', null, null, $preferred->id_petugas
        );
        $booking->forceFill(['status' => 'diterima'])->save();
        TechnicianTimeOff::create(['id_petugas' => $final->id_petugas, 'date' => $this->date(), 'reason' => 'QA Phase 3C']);

        $this->expectException(RuntimeException::class);
        try {
            app(ServisService::class)->createFromBooking($booking->id_booking_servis, $final->id_petugas, 10000, null, null, true);
        } finally {
            $this->assertDatabaseMissing('servis', ['id_booking_servis' => $booking->id_booking_servis]);
            $this->assertSame('diterima', $booking->fresh()->status);
            $this->assertSame($preferred->id_petugas, $booking->fresh()->id_preferred_petugas);
        }
    }

    public function test_no_preference_remains_null_and_member_endpoint_is_private(): void
    {
        $member = $this->member();
        $vehicle = $this->vehicle($member);
        $booking = app(BookingServisService::class)->create(
            $member->pelanggan->id_pelanggan, $vehicle->id_kendaraan_pelanggan, $this->type()->id_jenis_servis,
            $this->date(), '15:00', null, null
        );
        $this->assertNull($booking->id_preferred_petugas);
        $json = $this->actingAs($member)->getJson(route('pelanggan.booking-servis.technicians', [
            'date' => $this->date(), 'time' => '15:00', 'jenis_servis_id' => $this->type()->id_jenis_servis,
        ]));
        $json->assertOk()->assertJsonMissing(['email', 'no_hp', 'password', 'salary', 'internal_notes']);
    }
}
