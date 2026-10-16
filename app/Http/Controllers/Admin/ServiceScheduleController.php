<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{JenisServis, ServiceBlackoutDate, ServiceOperatingHour};
use App\Services\{ServicePersonnelAvailabilityService,ServiceSchedulingService};
use Carbon\Carbon;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class ServiceScheduleController extends Controller
{
    public function index(Request $request, ServiceSchedulingService $s, ServicePersonnelAvailabilityService $personnel): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        try { $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth(); } catch (\Throwable) { $monthDate = now()->startOfMonth(); $month = $monthDate->format('Y-m'); }
        $types = JenisServis::where('status', 'aktif')->orderBy('nama_servis')->get();
        $typeId = (int) ($request->input('jenis_servis_id') ?: $types->first()?->id_jenis_servis);
        $selected = Carbon::parse($request->input('date', now()->toDateString()));
        $calendar = collect();
        for ($d = $monthDate->copy(); $d->month === $monthDate->month; $d->addDay()) {
            $summary = $s->availability($d, $typeId ?: (int) ($types->first()?->id_jenis_servis));
            $calendar->push(['date' => $d->toDateString(), 'day' => $d->day, 'is_operating_day' => $summary['is_operating_day'], 'available_slots' => collect($summary['slots'])->whereIn('status', ['available','almost_full'])->count(), 'full_slots' => collect($summary['slots'])->where('status','full')->count(), 'booking_count' => \App\Models\BookingServis::whereDate('tanggal',$d)->whereIn('status', ServiceSchedulingService::OCCUPYING_STATUSES)->count()]);
        }
        return view('admin.servis.jadwal', ['month' => $month, 'monthDate' => $monthDate, 'types' => $types, 'typeId' => $typeId, 'selectedDate' => $selected->toDateString(), 'daily' => $s->availability($selected, $typeId ?: 0), 'personnelToday' => $typeId ? $personnel->technicians($selected->toDateString(), '08:00', $typeId) : [], 'calendar' => $calendar, 'hours' => ServiceOperatingHour::orderBy('day_of_week')->get(), 'blackouts' => ServiceBlackoutDate::orderBy('date','asc')->whereDate('date','>=',now()->toDateString())->limit(30)->get(), 'capacity' => $s->capacity()]);
    }

    public function updateHours(Request $request): RedirectResponse
    {
        $data = $request->validate(['hours' => 'required|array', 'hours.*.is_open' => 'nullable|boolean', 'hours.*.open_time' => 'nullable|date_format:H:i', 'hours.*.close_time' => 'nullable|date_format:H:i', 'hours.*.break_start' => 'nullable|date_format:H:i', 'hours.*.break_end' => 'nullable|date_format:H:i', 'hours.*.slot_interval_minutes' => 'required|integer|min:15|max:240']);
        foreach ($data['hours'] as $id => $row) ServiceOperatingHour::whereKey($id)->update(array_merge($row, ['is_open' => (bool)($row['is_open'] ?? false)]));
        return back()->with('success', 'Jam operasional diperbarui.');
    }

    public function storeBlackout(Request $request): RedirectResponse
    {
        $data = $request->validate(['date' => 'required|date', 'reason' => 'required|string|max:255']);
        ServiceBlackoutDate::updateOrCreate(['date' => $data['date']], ['reason' => $data['reason'], 'is_closed' => true]);
        return back()->with('success', 'Tanggal tutup ditambahkan.');
    }

    public function destroyBlackout(ServiceBlackoutDate $blackout): RedirectResponse { $blackout->delete(); return back()->with('success', 'Tanggal tutup dihapus.'); }
}
