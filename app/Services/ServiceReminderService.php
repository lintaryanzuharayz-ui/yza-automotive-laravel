<?php

namespace App\Services;

use App\Models\{KendaraanPelanggan, Servis};
use Illuminate\Support\Carbon;

class ServiceReminderService
{
    public function forCustomer(int $customerId): array
    {
        $vehicles = KendaraanPelanggan::where('id_pelanggan', $customerId)->where('status', 'aktif')->orderBy('merk')->get();
        $ids = $vehicles->pluck('id_kendaraan_pelanggan');
        $services = Servis::with('details.jenisServis')->whereIn('id_kendaraan_pelanggan', $ids)
            ->where('status', 'selesai')->whereNotNull('tanggal_selesai')
            ->orderByDesc('tanggal_selesai')->get()->groupBy('id_kendaraan_pelanggan');
        $rows = $vehicles->map(fn($vehicle) => $this->row($vehicle, $services->get($vehicle->id_kendaraan_pelanggan)?->first()))->values();
        $priority = ['overdue' => 0, 'due_soon' => 1, 'upcoming' => 2, 'no_history' => 3];
        return $rows->sortBy(fn($row) => [$priority[$row['status']], $row['nextDate']?->timestamp ?? PHP_INT_MAX])->values()->all();
    }

    private function row(KendaraanPelanggan $vehicle, ?Servis $service): array
    {
        if (!$service) return ['vehicle' => $vehicle, 'service' => null, 'status' => 'no_history', 'statusLabel' => 'Belum Ada Riwayat Servis', 'lastDate' => null, 'nextDate' => null, 'days' => null, 'kilometer' => null, 'serviceType' => null, 'bookingUrl' => route('pelanggan.booking-servis.create')];
        $last = Carbon::instance($service->tanggal_selesai);
        $next = $last->copy()->addMonths((int) config('yza.service_reminder.interval_months', 6));
        $today = Carbon::today('Asia/Jakarta'); $days = $today->diffInDays($next, false);
        if ($days < 0) [$status, $label] = ['overdue', 'Terlambat Servis'];
        elseif ($days <= (int) config('yza.service_reminder.due_soon_days', 30)) [$status, $label] = ['due_soon', 'Segera Servis'];
        else [$status, $label] = ['upcoming', 'Akan Datang'];
        return ['vehicle' => $vehicle, 'service' => $service, 'status' => $status, 'statusLabel' => $label, 'lastDate' => $last, 'nextDate' => $next, 'days' => $days, 'kilometer' => $service->kilometer, 'serviceType' => $service->details->first()?->jenisServis?->nama_servis, 'bookingUrl' => route('pelanggan.booking-servis.create')];
    }
}
