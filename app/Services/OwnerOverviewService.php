<?php

namespace App\Services;

use App\Models\{Mobil, Pembayaran, Servis};
use Carbon\Carbon;

/** Read-only presentation queries; canonical business scopes remain in OwnerAnalyticsService. */
class OwnerOverviewService
{
    public function inventory(): array
    {
        return Mobil::query()->selectRaw('status, COUNT(*) as units')->groupBy('status')->orderBy('status')->get()
            ->map(fn ($row) => ['status' => $row->status, 'units' => (int) $row->units])->all();
    }

    public function activities(Carbon $from, Carbon $to, OwnerAnalyticsService $analytics): array
    {
        $sales = $analytics->finalVehicleSales($from, $to)->latest('tanggal_penjualan')->limit(6)->get()
            ->map(fn ($row) => ['title' => 'Penjualan kendaraan final', 'reference' => $row->kode_penjualan, 'date' => $row->tanggal_penjualan, 'status' => 'Selesai', 'route' => 'owner.sales-report', 'icon' => 'car']);
        $payments = Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')->whereBetween('verified_at', [$from, $to])->latest('verified_at')->limit(6)->get()
            ->map(fn ($row) => ['title' => 'Pembayaran terverifikasi', 'reference' => $row->kode_pembayaran, 'date' => $row->verified_at, 'status' => 'Terverifikasi', 'route' => 'owner.payment-report', 'icon' => 'wallet']);
        $workOrders = Servis::whereIn('status', ['selesai', 'ditutup'])->whereBetween('tanggal_selesai', [$from, $to])->latest('tanggal_selesai')->limit(6)->get()
            ->map(fn ($row) => ['title' => 'Work Order selesai', 'reference' => $row->kode_servis, 'date' => $row->tanggal_selesai, 'status' => ucfirst($row->status), 'route' => 'owner.service-report', 'icon' => 'tool']);

        return $sales->concat($payments)->concat($workOrders)->sortByDesc(fn ($row) => $row['date']->getTimestamp())->take(6)->values()->all();
    }
}
