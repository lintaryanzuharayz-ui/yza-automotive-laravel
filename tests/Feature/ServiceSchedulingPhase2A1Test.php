<?php

namespace Tests\Feature;

use App\Models\{JenisServis, User};
use App\Services\ServiceSchedulingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ServiceSchedulingPhase2A1Test extends TestCase
{
    use DatabaseTransactions;

    private function futureWeekday(): string { return Carbon::today()->next(Carbon::TUESDAY)->toDateString(); }
    private function service(int $minutes): JenisServis { $type = JenisServis::where('status','aktif')->firstOrFail(); $type->update(['duration_minutes' => $minutes]); return $type->fresh(); }
    private function slot(array $data, string $time): ?array { return collect($data['slots'])->firstWhere('time', $time); }
    private function member(): User { return User::whereHas('role', fn ($query) => $query->where('nama_role', 'pelanggan'))->firstOrFail(); }

    public function test_sixty_minute_service_fits_before_break_and_closing(): void
    {
        $type = $this->service(60); $data = app(ServiceSchedulingService::class)->availability($this->futureWeekday(), $type->id_jenis_servis);
        $this->assertSame('available', $this->slot($data, '11:00')['status']);
        $this->assertSame('available', $this->slot($data, '16:00')['status']);
    }

    public function test_two_hour_service_cannot_cross_break_or_closing(): void
    {
        $type = $this->service(120); $data = app(ServiceSchedulingService::class)->availability($this->futureWeekday(), $type->id_jenis_servis);
        $this->assertSame('available', $this->slot($data, '10:00')['status']);
        $this->assertSame('unavailable', $this->slot($data, '11:00')['status']);
        $this->assertSame('available', $this->slot($data, '15:00')['status']);
        $this->assertSame('unavailable', $this->slot($data, '16:00')['status']);
    }

    public function test_weekend_is_closed_and_month_contract_is_aggregate_only(): void
    {
        $type = $this->service(60); $service = app(ServiceSchedulingService::class);
        $sunday = Carbon::today()->next(Carbon::SUNDAY)->toDateString();
        $this->assertFalse($service->availability($sunday, $type->id_jenis_servis)['is_operating_day']);
        $month = $service->monthAvailability(Carbon::today()->format('Y-m'), $type->id_jenis_servis);
        $this->assertSame(Carbon::today()->format('Y-m'), $month['month']);
        $this->assertArrayHasKey('status', $month['days'][0]);
        $this->assertArrayNotHasKey('customer', $month['days'][0]);
        $this->assertArrayNotHasKey('booking_code', $month['days'][0]);
    }

    public function test_break_slot_is_never_offered(): void
    {
        $type = $this->service(60); $data = app(ServiceSchedulingService::class)->availability($this->futureWeekday(), $type->id_jenis_servis);
        $slot = $this->slot($data, '12:00');
        $this->assertTrue($slot === null || $slot['status'] === 'unavailable');
    }

    public function test_past_dates_are_never_available(): void
    {
        $type = $this->service(60);
        // Pick a past operating weekday so the assertion exercises slot state,
        // rather than the closed-day branch of the availability service.
        $past = Carbon::today()->subWeek()->startOfWeek()->addDays(1)->toDateString();
        $data = app(ServiceSchedulingService::class)->availability($past, $type->id_jenis_servis);

        $this->assertNotEmpty($data['slots']);
        $this->assertTrue(collect($data['slots'])->every(fn (array $slot): bool => $slot['status'] === 'unavailable'));
    }

    public function test_month_marks_dates_before_today_as_past(): void
    {
        $type = $this->service(60);
        $month = app(ServiceSchedulingService::class)->monthAvailability(Carbon::today()->format('Y-m'), $type->id_jenis_servis);
        $pastDays = collect($month['days'])->filter(fn (array $day): bool => $day['date'] < Carbon::today()->toDateString());

        $this->assertTrue($pastDays->isNotEmpty());
        $this->assertTrue($pastDays->every(fn (array $day): bool => $day['status'] === 'past'));
    }

    public function test_member_day_and_month_endpoints_return_only_schedule_data(): void
    {
        $type = $this->service(60); $user = $this->member();
        $this->actingAs($user)->getJson(route('pelanggan.booking-servis.availability', ['date' => $this->futureWeekday(), 'jenis_servis_id' => $type->id_jenis_servis]))->assertOk()->assertJsonStructure(['date','is_operating_day','slots']);
        $this->actingAs($user)->getJson(route('pelanggan.booking-servis.availability', ['month' => Carbon::today()->format('Y-m'), 'jenis_servis_id' => $type->id_jenis_servis]))->assertOk()->assertJsonStructure(['month','timezone','days'])->assertJsonMissing(['customer','plate','booking_code']);
    }
}
