<?php

namespace App\Services;

use App\Models\{BookingServis, FinancialAdjustment, Mobil, Pembayaran, PenjualanMobil, Petugas, SalesLead, SalesTarget, Servis, TestDrive, Transaksi};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OwnerAnalyticsService
{
    public function period(?string $from = null, ?string $to = null): array
    {
        $end = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        $start = $from ? Carbon::parse($from)->startOfDay() : $end->copy()->startOfYear();
        return [$start, $end];
    }

    public function finalVehicleSales(?Carbon $from = null, ?Carbon $to = null, array $filters = []): Builder
    {
        $query = PenjualanMobil::query()->with(['pelanggan', 'mobil', 'sales', 'transaksi.pembayarans', 'memberOrder.serahTerima']);
        $query->where('penjualan_mobil.status', 'selesai')
            ->whereHas('transaksi', fn ($q) => $q->where('status', '<>', 'dibatalkan'))
            ->where(function ($q): void {
                $q->whereDoesntHave('memberOrder')->orWhereHas('memberOrder', fn ($order) => $order->where('status', 'diserahkan'));
            });
        if ($from) $query->where('tanggal_penjualan', '>=', $from);
        if ($to) $query->where('tanggal_penjualan', '<=', $to);
        if (!empty($filters['sales'])) $query->where('id_sales', $filters['sales']);
        if (!empty($filters['brand'])) $query->whereHas('mobil', fn ($q) => $q->where('merk', $filters['brand']));
        if (!empty($filters['model'])) $query->whereHas('mobil', fn ($q) => $q->where('model', $filters['model']));
        return $query;
    }

    public function kpis(?Carbon $from = null, ?Carbon $to = null): array
    {
        $sales = $this->finalVehicleSales($from, $to);
        return [
            'final_sales' => (clone $sales)->count(),
            'handed_over' => (clone $sales)->whereHas('memberOrder', fn ($q) => $q->where('status', 'diserahkan'))->count(),
            'final_sales_value' => (float) (clone $sales)->sum('total_akhir'),
            'active_orders' => $this->activeOrders($from, $to),
            'verified_payments' => (float) Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')->when($from, fn ($q) => $q->where('verified_at', '>=', $from))->when($to, fn ($q) => $q->where('verified_at', '<=', $to))->sum('jumlah'),
            'service_revenue' => (float) Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')->whereHas('transaksi', fn ($q) => $q->where('jenis_transaksi', 'servis'))->when($from, fn ($q) => $q->where('verified_at', '>=', $from))->when($to, fn ($q) => $q->where('verified_at', '<=', $to))->sum('jumlah'),
            'available_stock' => Mobil::where('status', 'tersedia')->count(),
            'sold_stock' => Mobil::where('status', 'terjual')->count(),
            'active_work_orders' => Servis::whereIn('status', ['pemeriksaan', 'dikerjakan'])->count(),
            'customers' => DB::table('pelanggan')->count(),
        ];
    }

    public function activeOrders(?Carbon $from = null, ?Carbon $to = null): int
    {
        return DB::table('pesanan_mobil_member')->whereNotIn('status', ['diserahkan', 'dibatalkan', 'ditolak'])->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))->count();
    }

    public function monthlySales(?Carbon $from = null, ?Carbon $to = null): array
    {
        return $this->finalVehicleSales($from, $to)->selectRaw("DATE_FORMAT(tanggal_penjualan, '%Y-%m') AS period, COUNT(*) AS units, SUM(total_akhir) AS amount")->groupBy('period')->orderBy('period')->get()->map(fn ($row) => ['period' => $row->period, 'units' => (int) $row->units, 'amount' => (float) $row->amount])->all();
    }

    public function brandBreakdown(?Carbon $from = null, ?Carbon $to = null): array
    {
        return $this->finalVehicleSales($from, $to)->join('mobil as report_mobil', 'report_mobil.id_mobil', '=', 'penjualan_mobil.id_mobil')->selectRaw('report_mobil.merk AS label, COUNT(DISTINCT penjualan_mobil.id_penjualan) AS units')->groupBy('report_mobil.merk')->orderByDesc('units')->get()->map(fn ($row) => ['label' => $row->label, 'units' => (int) $row->units])->all();
    }

    public function salesPerformance(?Carbon $from = null, ?Carbon $to = null): array
    {
        $sales = Petugas::query()->whereHas('user.role', fn ($q) => $q->where('nama_role', 'sales'))->where('status', 'aktif')->get();
        $leadCounts = SalesLead::whereNotNull('id_sales')->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))->selectRaw('id_sales, COUNT(*) AS total')->groupBy('id_sales')->pluck('total', 'id_sales');
        $driveCounts = TestDrive::whereNotNull('id_sales')->when($from, fn ($q) => $q->where('tanggal', '>=', $from->toDateString()))->when($to, fn ($q) => $q->where('tanggal', '<=', $to->toDateString()))->selectRaw('id_sales, COUNT(*) AS total')->groupBy('id_sales')->pluck('total', 'id_sales');
        $orderCounts = DB::table('pesanan_mobil_member')->whereNotNull('id_sales')->when($from, fn ($q) => $q->where('created_at', '>=', $from))->when($to, fn ($q) => $q->where('created_at', '<=', $to))->selectRaw('id_sales, COUNT(*) AS total')->groupBy('id_sales')->pluck('total', 'id_sales');
        $final = $this->finalVehicleSales($from, $to)->whereNotNull('id_sales')->selectRaw('id_sales, COUNT(*) AS units, SUM(total_akhir) AS amount')->groupBy('id_sales')->get()->keyBy('id_sales');
        $targets = SalesTarget::where('status', 'aktif')->whereIn('id_sales', $sales->pluck('id_petugas'))
            ->when($from, fn ($q) => $q->whereDate('periode_selesai', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->whereDate('periode_mulai', '<=', $to->toDateString()))
            ->orderByDesc('periode_mulai')->get()->groupBy('id_sales');
        return $sales->map(function (Petugas $person) use ($leadCounts, $driveCounts, $orderCounts, $final, $targets): array {
            $row = $final->get($person->id_petugas);
            $target = $targets->get($person->id_petugas)?->first();
            $units = (int) ($row->units ?? 0);
            return ['id' => $person->id_petugas, 'name' => $person->nama_lengkap, 'leads' => (int) ($leadCounts[$person->id_petugas] ?? 0), 'test_drives' => (int) ($driveCounts[$person->id_petugas] ?? 0), 'orders' => (int) ($orderCounts[$person->id_petugas] ?? 0), 'units' => $units, 'amount' => (float) ($row->amount ?? 0), 'target_units' => (int) ($target?->target_unit ?? 0), 'achievement' => $target?->target_unit ? round($units / $target->target_unit * 100, 1) : 0];
        })->sortByDesc('units')->values()->all();
    }

    public function bookingSalesTrend(?Carbon $from = null, ?Carbon $to = null): array
    {
        $bookings = DB::table('pesanan_mobil_member')->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->whereNotIn('status', ['dibatalkan', 'ditolak'])->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS period, COUNT(*) AS bookings")
            ->groupBy('period')->pluck('bookings', 'period');
        $sales = $this->finalVehicleSales($from, $to)->selectRaw("DATE_FORMAT(tanggal_penjualan, '%Y-%m') AS period, COUNT(*) AS sales")
            ->groupBy('period')->pluck('sales', 'period');
        return collect($bookings->keys()->merge($sales->keys())->unique()->sort()->values())->map(fn ($period) => [
            'period' => $period, 'bookings' => (int) ($bookings[$period] ?? 0), 'sales' => (int) ($sales[$period] ?? 0),
        ])->all();
    }

    public function modelBreakdown(?Carbon $from = null, ?Carbon $to = null): array
    {
        return $this->finalVehicleSales($from, $to)->join('mobil as report_model', 'report_model.id_mobil', '=', 'penjualan_mobil.id_mobil')
            ->selectRaw('CONCAT(report_model.merk, " ", report_model.model) AS label, COUNT(DISTINCT penjualan_mobil.id_penjualan) AS units')
            ->groupBy('report_model.merk', 'report_model.model')->orderByDesc('units')->limit(10)->get()
            ->map(fn ($row) => ['label' => $row->label, 'units' => (int) $row->units])->all();
    }

    public function serviceTrends(?Carbon $from = null, ?Carbon $to = null): array
    {
        $booking = BookingServis::query()->when($from, fn ($q) => $q->where('tanggal', '>=', $from->toDateString()))
            ->when($to, fn ($q) => $q->where('tanggal', '<=', $to->toDateString()))
            ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') AS period, COUNT(*) AS bookings")->groupBy('period')->pluck('bookings', 'period');
        $completed = Servis::query()->whereIn('status', ['selesai', 'ditutup'])
            ->when($from, fn ($q) => $q->where('tanggal_selesai', '>=', $from))->when($to, fn ($q) => $q->where('tanggal_selesai', '<=', $to))
            ->selectRaw("DATE_FORMAT(tanggal_selesai, '%Y-%m') AS period, COUNT(*) AS completed")->groupBy('period')->pluck('completed', 'period');
        $revenue = Pembayaran::query()->where('arah', 'masuk')->where('status', 'terverifikasi')->whereHas('transaksi', fn ($q) => $q->where('jenis_transaksi', 'servis'))
            ->when($from, fn ($q) => $q->where('verified_at', '>=', $from))->when($to, fn ($q) => $q->where('verified_at', '<=', $to))
            ->selectRaw("DATE_FORMAT(verified_at, '%Y-%m') AS period, SUM(jumlah) AS revenue")->groupBy('period')->pluck('revenue', 'period');
        return collect($booking->keys()->merge($completed->keys())->merge($revenue->keys())->unique()->sort()->values())->map(fn ($period) => [
            'period' => $period, 'bookings' => (int) ($booking[$period] ?? 0), 'completed' => (int) ($completed[$period] ?? 0), 'revenue' => (float) ($revenue[$period] ?? 0),
        ])->all();
    }

    public function serviceReports(?Carbon $from = null, ?Carbon $to = null, array $filters = []): Builder
    {
        return Servis::query()->with(['bookingServis.jenisServis', 'pelanggan', 'kendaraan', 'petugas', 'transaksi.pembayarans', 'assignments.petugas'])
            ->when($from, fn ($q) => $q->where('tanggal_masuk', '>=', $from))
            ->when($to, fn ($q) => $q->where('tanggal_masuk', '<=', $to))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['mechanic'] ?? null, fn ($q, $value) => $q->whereHas('assignments', fn ($a) => $a->where('id_petugas', $value)->whereNull('unassigned_at')))
            ->latest('tanggal_masuk');
    }

    public function paymentReport(?Carbon $from = null, ?Carbon $to = null): array
    {
        $query = Pembayaran::query()->with('transaksi')->where('arah', 'masuk')->when($from, fn ($q) => $q->where('tanggal', '>=', $from))->when($to, fn ($q) => $q->where('tanggal', '<=', $to));
        $rows = $query->latest('tanggal')->get();
        return ['rows' => $rows, 'verified' => (float) $rows->where('status', 'terverifikasi')->sum('jumlah'), 'pending' => (float) $rows->where('status', 'menunggu')->sum('jumlah'), 'rejected' => (float) $rows->where('status', 'ditolak')->sum('jumlah'), 'online' => (float) $rows->whereIn('metode', ['midtrans', 'online'])->where('status', 'terverifikasi')->sum('jumlah'), 'offline' => (float) $rows->whereNotIn('metode', ['midtrans', 'online'])->where('status', 'terverifikasi')->sum('jumlah')];
    }

    public function inventoryRows(): Builder
    {
        return Mobil::query()->select(['id_mobil', 'kode_mobil', 'merk', 'model', 'tahun', 'harga_beli', 'harga_jual', 'status', 'tanggal_masuk'])->orderBy('status')->orderBy('merk')->orderBy('model');
    }

    public function serviceSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $scope = fn ($q, string $column = 'created_at') => $q->when($from, fn ($query) => $query->where($column, '>=', $from))->when($to, fn ($query) => $query->where($column, '<=', $to));
        return ['bookings' => $scope(BookingServis::query(), 'created_at')->count(), 'active_work_orders' => $scope(Servis::whereIn('status', ['pemeriksaan', 'dikerjakan']))->count(), 'completed_work_orders' => $scope(Servis::whereIn('status', ['selesai', 'ditutup']), 'tanggal_selesai')->count(), 'waiting_qc' => $scope(Servis::whereHas('qualityControl', fn ($q) => $q->whereIn('status', ['pending', 'in_progress'])))->count(), 'revenue' => (float) $scope(Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi')->whereHas('transaksi', fn ($q) => $q->where('jenis_transaksi', 'servis')), 'verified_at')->sum('jumlah'), 'vehicles_served' => $scope(Servis::distinct('id_kendaraan_pelanggan'), 'tanggal_selesai')->count('id_kendaraan_pelanggan')];
    }

    public function paymentSummary(?Carbon $from = null, ?Carbon $to = null): array
    {
        $scope = fn ($q, string $column = 'created_at') => $q->when($from, fn ($query) => $query->where($column, '>=', $from))->when($to, fn ($query) => $query->where($column, '<=', $to));
        $verified = (float) $scope(Pembayaran::where('arah', 'masuk')->where('status', 'terverifikasi'))->sum('jumlah');
        $confirmedRefunds = (float) $scope(FinancialAdjustment::where('status', 'confirmed'))->sum('amount');
        $pendingRefunds = (float) $scope(FinancialAdjustment::whereIn('status', ['requested', 'under_review', 'approved', 'processing']))->sum('amount');
        return ['verified' => $verified, 'confirmed_refunds' => $confirmedRefunds, 'net_verified' => $verified - $confirmedRefunds, 'pending' => (float) $scope(Pembayaran::where('arah', 'masuk')->where('status', 'menunggu'))->sum('jumlah'), 'pending_refunds' => $pendingRefunds, 'rejected' => (float) $scope(Pembayaran::where('arah', 'masuk')->where('status', 'ditolak'))->sum('jumlah'), 'midtrans_settled' => (float) $scope(DB::table('pembayaran_gateway')->whereIn('status_provider', ['settlement', 'capture']))->sum('gross_amount')];
    }

    public function inventorySummary(): array
    {
        return ['available' => Mobil::where('status', 'tersedia')->count(), 'reserved' => Mobil::where('status', 'dibooking')->count(), 'sold' => Mobil::where('status', 'terjual')->count(), 'brands' => Mobil::selectRaw('merk AS label, COUNT(*) AS units')->groupBy('merk')->orderByDesc('units')->get()->map(fn ($row) => ['label' => $row->label, 'units' => (int) $row->units])->all()];
    }

    public function growth(float $current, float $previous): ?float
    {
        return $previous == 0.0 ? ($current == 0.0 ? 0.0 : null) : round(($current - $previous) / $previous * 100, 1);
    }
}
