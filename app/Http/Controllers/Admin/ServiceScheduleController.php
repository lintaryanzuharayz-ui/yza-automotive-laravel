<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{BookingServis, JenisServis, ServiceBlackoutDate, ServiceOperatingHour, ServiceStaffProfile};
use App\Services\{ServicePersonnelAvailabilityService,ServiceSchedulingService};
use Carbon\Carbon;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ServiceScheduleController extends Controller
{
    public function index(Request $request, ServiceSchedulingService $s, ServicePersonnelAvailabilityService $personnel): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        try { $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth(); } catch (\Throwable) { $monthDate = now()->startOfMonth(); $month = $monthDate->format('Y-m'); }
        $types = JenisServis::where('status', 'aktif')->orderBy('nama_servis')->get();
        $typeId = (int) ($request->input('jenis_servis_id') ?: $types->first()?->id_jenis_servis);
        try { $selected = Carbon::parse($request->input('date', now()->toDateString())); } catch (\Throwable) { $selected = now(); }
        $calendar = collect();
        $monthCapacityTotal = 0;
        $monthCapacityUsed = 0;
        for ($d = $monthDate->copy(); $d->month === $monthDate->month; $d->addDay()) {
            $summary = $typeId ? $s->availability($d, $typeId) : ['is_operating_day' => false, 'slots' => [], 'blackout' => null];
            $slots = collect($summary['slots']);
            $validSlots = $slots->where('status', '!=', 'unavailable');
            $availableSlots = $slots->whereIn('status', ['available','almost_full'])->count();
            $fullSlots = $slots->where('status', 'full')->count();
            $monthCapacityTotal += $validSlots->sum('capacity_total');
            $monthCapacityUsed += $validSlots->sum('capacity_used');
            $calendar->push(['date' => $d->toDateString(), 'day' => $d->day, 'is_operating_day' => $summary['is_operating_day'], 'available_slots' => $availableSlots, 'full_slots' => $fullSlots, 'status' => !$summary['is_operating_day'] ? 'closed' : ($availableSlots ? 'available' : ($fullSlots ? 'full' : 'unavailable')), 'booking_count' => BookingServis::whereDate('tanggal',$d)->whereIn('status', ServiceSchedulingService::OCCUPYING_STATUSES)->count()]);
        }
        $daily = $typeId ? $s->availability($selected, $typeId) : ['slots' => [], 'is_operating_day' => false, 'blackout' => null];
        $personnelToday = $typeId ? $personnel->technicians($selected->toDateString(), '08:00', $typeId) : [];
        $todaySummary = $typeId ? $s->availability(now(), $typeId) : ['slots' => []];
        $monthStart = $monthDate->copy()->startOfMonth(); $monthEnd = $monthDate->copy()->endOfMonth();
        $stats = ['bookings' => BookingServis::whereBetween('tanggal', [$monthStart->toDateString(), $monthEnd->toDateString()])->whereIn('status', ServiceSchedulingService::OCCUPYING_STATUSES)->count(), 'available_today' => collect($todaySummary['slots'])->whereIn('status', ['available','almost_full'])->count(), 'technicians' => ServiceStaffProfile::where('can_be_technician', true)->where('is_bookable', true)->whereHas('petugas', fn ($q) => $q->where('status', 'aktif'))->count(), 'blackouts' => ServiceBlackoutDate::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])->where('is_closed', true)->count(), 'capacity_percent' => $monthCapacityTotal ? (int) round(($monthCapacityUsed / $monthCapacityTotal) * 100) : 0];
        return view('admin.servis.jadwal', ['month' => $month, 'monthDate' => $monthDate, 'types' => $types, 'typeId' => $typeId, 'selectedDate' => $selected->toDateString(), 'daily' => $daily, 'personnelToday' => $personnelToday, 'calendar' => $calendar, 'hours' => ServiceOperatingHour::orderBy('day_of_week')->get(), 'blackouts' => ServiceBlackoutDate::orderBy('date','asc')->whereDate('date','>=',now()->toDateString())->limit(30)->get(), 'capacity' => $s->capacity(), 'stats' => $stats]);
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $data = $request->validate(['hours' => 'required|array', 'hours.*.is_open' => 'nullable|boolean', 'hours.*.open_time' => 'nullable|date_format:H:i', 'hours.*.close_time' => 'nullable|date_format:H:i', 'hours.*.break_start' => 'nullable|date_format:H:i', 'hours.*.break_end' => 'nullable|date_format:H:i', 'hours.*.slot_interval_minutes' => 'required|integer|min:15|max:240']);
        foreach ($data['hours'] as $id => $row) {
            $isOpen = (bool) ($row['is_open'] ?? false);
            if ($isOpen && (!$row['open_time'] || !$row['close_time'])) throw ValidationException::withMessages(["hours.$id.open_time" => 'Jam buka dan jam tutup wajib diisi untuk hari yang dibuka.']);
            if ($isOpen && $row['open_time'] >= $row['close_time']) throw ValidationException::withMessages(["hours.$id.close_time" => 'Jam tutup harus lebih besar dari jam buka.']);
            if (($row['break_start'] ?? null) xor ($row['break_end'] ?? null)) throw ValidationException::withMessages(["hours.$id.break_start" => 'Jam istirahat harus diisi lengkap atau dikosongkan.']);
            if ($isOpen && ($row['break_start'] ?? null) && (($row['break_start'] >= $row['break_end']) || $row['break_start'] < $row['open_time'] || $row['break_end'] > $row['close_time'])) throw ValidationException::withMessages(["hours.$id.break_start" => 'Jam istirahat harus berada di antara jam buka dan jam tutup.']);
            ServiceOperatingHour::whereKey($id)->update(array_merge($row, ['is_open' => $isOpen]));
        }
        return back()->with('success', 'Jam operasional diperbarui.');
    }

    public function storeBlackout(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => 'required|date', 'reason' => 'required|string|max:255']);
        if (BookingServis::whereDate('tanggal', $data['date'])->whereIn('status', ServiceSchedulingService::OCCUPYING_STATUSES)->exists()) throw ValidationException::withMessages(['date' => 'Tanggal tersebut masih memiliki booking servis aktif dan tidak dapat ditutup.']);
        ServiceBlackoutDate::updateOrCreate(['date' => $data['date']], ['reason' => $data['reason'], 'is_closed' => true]);
        return back()->with('success', 'Tanggal tutup ditambahkan.');
    }

    public function destroyBlackout(ServiceBlackoutDate $blackout): RedirectResponse { $blackout->delete(); return back()->with('success', 'Tanggal tutup dihapus.'); }
}
