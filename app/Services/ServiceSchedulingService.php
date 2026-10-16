<?php

namespace App\Services;

use App\Models\{BookingServis, JenisServis, Pengaturan, ServiceBlackoutDate, ServiceOperatingHour};
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ServiceSchedulingService
{
    public const OCCUPYING_STATUSES = ['menunggu', 'diterima', 'diproses'];

    public function capacity(): int
    {
        return max(1, min(50, (int) (Pengaturan::where('kunci_pengaturan', 'service_capacity_per_slot')->value('nilai') ?: 3)));
    }

    public function availability(string|Carbon $date, int $serviceTypeId, ?int $excludeBookingId = null, bool $lock = false): array
    {
        $day = $this->date($date);
        $type = JenisServis::where('status', 'aktif')->findOrFail($serviceTypeId);
        $duration = max(1, (int) $type->duration_minutes);
        $hours = ServiceOperatingHour::where('day_of_week', $day->isoWeekday())->first();
        if ($lock && $hours) $hours = ServiceOperatingHour::whereKey($hours->getKey())->lockForUpdate()->first();
        $blackout = ServiceBlackoutDate::whereDate('date', $day)->first();
        if ($lock && $blackout) $blackout = ServiceBlackoutDate::whereKey($blackout->getKey())->lockForUpdate()->first();

        $open = $blackout?->is_closed ? null : ($blackout?->custom_open_time ?: $hours?->open_time);
        $close = $blackout?->is_closed ? null : ($blackout?->custom_close_time ?: $hours?->close_time);
        $isOpen = (bool) ($hours?->is_open && $open && $close && !$blackout?->is_closed);
        $bookings = $isOpen ? $this->bookings($day, $excludeBookingId, $lock) : collect();
        $capacity = $this->capacity();
        $slots = [];
        if ($isOpen) {
            $cursor = $this->time($day, $open);
            $end = $this->time($day, $close);
            $interval = max(15, (int) ($hours->slot_interval_minutes ?: 60));
            $breakStart = $hours->break_start ? $this->time($day, $hours->break_start) : null;
            $breakEnd = $hours->break_end ? $this->time($day, $hours->break_end) : null;
            while ($cursor->lt($end)) {
                if (!$breakStart || !$breakEnd || !$cursor->gte($breakStart) || !$cursor->lt($breakEnd)) {
                    $slotEnd = $cursor->copy()->addMinutes($duration);
                    $used = $slotEnd->lte($end) && !$this->overlapsBreak($cursor, $slotEnd, $breakStart, $breakEnd)
                        ? $bookings->filter(fn ($booking) => $this->overlaps($cursor, $slotEnd, $this->time($day, $booking->jam), $this->time($day, $booking->jam)->addMinutes((int) ($booking->jenisServis?->duration_minutes ?: 60))))->count()
                        : 0;
                    $validWindow = $slotEnd->lte($end) && !$this->overlapsBreak($cursor, $slotEnd, $breakStart, $breakEnd);
                    $past = $day->isBefore(Carbon::today(config('app.timezone'))) || ($day->isToday() && $cursor->lte(now(config('app.timezone'))));
                    $remaining = max(0, $capacity - $used);
                    $status = !$validWindow || $past ? 'unavailable' : ($remaining <= 0 ? 'full' : ($remaining <= 1 ? 'almost_full' : 'available'));
                    $slots[] = ['time' => $cursor->format('H:i'), 'status' => $status, 'capacity_total' => $capacity, 'capacity_used' => $used, 'capacity_remaining' => $remaining, 'estimated_duration' => $duration, 'available' => $status === 'available' || $status === 'almost_full'];
                }
                $cursor->addMinutes($interval);
            }
        }
        return ['date' => $day->toDateString(), 'is_operating_day' => $isOpen, 'blackout' => $blackout ? ['date' => $day->toDateString(), 'reason' => $blackout->reason, 'is_closed' => (bool) $blackout->is_closed] : null, 'slots' => $slots];
    }

    public function assertAvailable(string $date, string $time, int $serviceTypeId): void
    {
        $data = $this->availability($date, $serviceTypeId, null, true);
        $slot = collect($data['slots'])->firstWhere('time', substr($time, 0, 5));
        if (!$data['is_operating_day']) throw new RuntimeException($data['blackout'] ? 'Tanggal tersebut ditutup karena '.$data['blackout']['reason'].'.' : 'Bengkel tutup pada tanggal tersebut.');
        if (!$slot || !$slot['available']) throw new RuntimeException('Slot servis tidak tersedia atau kapasitas bengkel sudah penuh. Silakan pilih waktu lain.');
    }

    public function monthAvailability(string $month, int $serviceTypeId): array
    {
        $start = Carbon::createFromFormat('Y-m', $month, config('app.timezone'))->startOfMonth();
        $days = [];
        for ($cursor = $start->copy(); $cursor->month === $start->month; $cursor->addDay()) {
            $day = $this->availability($cursor, $serviceTypeId);
            $valid = collect($day['slots'])->whereIn('status', ['available', 'almost_full']);
            $almost = $valid->where('status', 'almost_full')->count();
            $status = $cursor->isBefore(Carbon::today(config('app.timezone'))) ? 'past' : (!$day['is_operating_day'] || $valid->isEmpty() ? 'unavailable' : ($almost > 0 && ($valid->count() <= 2 || $almost >= (int) ceil($valid->count() / 2)) ? 'almost_full' : 'available'));
            if ($day['is_operating_day'] && $valid->isEmpty() && collect($day['slots'])->where('status', 'full')->isNotEmpty()) $status = 'full';
            $days[] = ['date' => $day['date'], 'status' => $status, 'is_operating_day' => $day['is_operating_day'], 'available_slots' => $valid->count()];
        }
        return ['month' => $start->format('Y-m'), 'timezone' => config('app.timezone'), 'days' => $days];
    }

    private function bookings(Carbon $day, ?int $exclude, bool $lock): Collection
    {
        $query = BookingServis::with('jenisServis')->whereDate('tanggal', $day)->whereIn('status', self::OCCUPYING_STATUSES);
        if ($exclude) $query->where('id_booking_servis', '!=', $exclude);
        if ($lock) $query->lockForUpdate();
        return $query->get();
    }

    private function date(string|Carbon $date): Carbon { return ($date instanceof Carbon ? $date->copy() : Carbon::parse($date, config('app.timezone')))->startOfDay(); }
    private function time(Carbon $date, string $time): Carbon { return $date->copy()->setTimeFromTimeString($time); }
    private function overlaps(Carbon $aStart, Carbon $aEnd, Carbon $bStart, Carbon $bEnd): bool { return $aStart->lt($bEnd) && $aEnd->gt($bStart); }
    private function overlapsBreak(Carbon $start, Carbon $end, ?Carbon $breakStart, ?Carbon $breakEnd): bool { return $breakStart && $breakEnd && $this->overlaps($start, $end, $breakStart, $breakEnd); }
}
