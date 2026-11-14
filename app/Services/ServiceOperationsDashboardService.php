<?php

namespace App\Services;

use App\Models\{BookingServis, Servis, Sparepart, Transaksi, Pembayaran, JenisServis, KategoriSparepart};
use Illuminate\Support\Carbon;

class ServiceOperationsDashboardService
{
    public function data(): array
    {
        $today = Carbon::today('Asia/Jakarta');
        $serviceTransactions = Transaksi::query()
            ->where('jenis_transaksi', 'servis')
            ->whereHas('servis');

        $metrics = [
            ['label' => 'Total Booking', 'value' => BookingServis::whereNotIn('status', ['ditolak', 'dibatalkan'])->count(), 'hint' => 'Semua antrean aktif', 'tone' => 'red'],
            ['label' => 'Menunggu Pemeriksaan', 'value' => Servis::where('status', 'pemeriksaan')->count(), 'hint' => 'WO siap inspeksi', 'tone' => 'amber'],
            ['label' => 'Sedang Dikerjakan', 'value' => Servis::where('status', 'dikerjakan')->count(), 'hint' => 'Pengerjaan mekanik', 'tone' => 'red'],
            ['label' => 'Menunggu Pembayaran', 'value' => (clone $serviceTransactions)->whereIn('status', ['belum_bayar', 'dp'])->count(), 'hint' => 'Tagihan belum lunas', 'tone' => 'dark'],
            ['label' => 'Servis Selesai', 'value' => Servis::where('status', 'selesai')->count(), 'hint' => 'Total servis tuntas', 'tone' => 'green'],
            ['label' => 'Stok Kritis', 'value' => Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->count(), 'hint' => 'Perlu restock segera', 'tone' => 'amber'],
        ];

        $queue = BookingServis::with(['pelanggan', 'kendaraan', 'jenisServis', 'servis.transaksi'])
            ->whereNotIn('status', ['ditolak', 'dibatalkan'])
            ->orderByDesc('tanggal')
            ->orderBy('jam')
            ->get()
            ->map(function (BookingServis $booking): array {
                $status = $booking->servis?->status ?: $booking->status;
                return [
                    'booking' => $booking,
                    'status' => $status,
                    'statusLabel' => ServiceStatusPresenter::label($status),
                    'tone' => ServiceStatusPresenter::tone($status),
                ];
            });

        $attention = collect();
        $pendingBookings = BookingServis::with(['pelanggan', 'kendaraan'])
            ->where('status', 'menunggu')->orderBy('tanggal')->orderBy('jam')->limit(5)->get();
        foreach ($pendingBookings as $booking) {
            $attention->push(['label' => 'Booking belum diproses', 'detail' => $booking->kode_booking.' · '.($booking->pelanggan?->nama_lengkap ?? 'Customer'), 'tone' => 'warning', 'booking_id' => $booking->id_booking_servis]);
        }
        $pendingPayments = (clone $serviceTransactions)->whereIn('status', ['belum_bayar', 'dp'])->with('servis')->latest('tanggal')->limit(5)->get();
        foreach ($pendingPayments as $transaction) {
            $attention->push(['label' => 'Pembayaran servis pending', 'detail' => $transaction->kode_transaksi, 'tone' => 'dark']);
        }
        $critical = Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->orderBy('stok')->limit(5)->get();
        foreach ($critical as $part) {
            $attention->push(['label' => 'Stok kritis', 'detail' => $part->nama_sparepart.' · '.$part->stok.' '.$part->satuan.' tersisa', 'tone' => 'warning']);
        }

        // 1. Service Advisor
        $advisorBookings = BookingServis::with(['pelanggan', 'kendaraan', 'jenisServis', 'servis'])
            ->orderByDesc('tanggal')->orderByDesc('jam')->get();
        $readyForCheckin = BookingServis::with(['pelanggan', 'kendaraan', 'jenisServis'])
            ->where('status', 'diterima')
            ->whereDoesntHave('servis')
            ->orderByDesc('tanggal')->orderBy('jam')
            ->get();

        // 2. Mekanik
        $activeWorkOrders = Servis::with(['bookingServis.kendaraan', 'bookingServis.pelanggan', 'petugas', 'details.jenisServis', 'sparepartItems.sparepart', 'transaksi.pembayarans'])
            ->whereIn('status', ['pemeriksaan', 'dikerjakan'])
            ->orderByDesc('id_servis')
            ->get();
        $completedWorkOrders = Servis::with(['bookingServis.kendaraan', 'bookingServis.pelanggan', 'petugas', 'details.jenisServis', 'sparepartItems.sparepart', 'transaksi'])
            ->where('status', 'selesai')
            ->orderByDesc('id_servis')
            ->limit(10)
            ->get();
        $serviceTypes = JenisServis::where('status', 'aktif')->orderBy('nama_servis')->get();
        $availableParts = Sparepart::where('status', 'aktif')->where('stok', '>', 0)->orderBy('nama_sparepart')->get();

        // 3. Staf Gudang
        $warehouseParts = Sparepart::with('kategori')->orderBy('nama_sparepart')->get();
        $lowStockParts = Sparepart::with('kategori')->whereColumn('stok', '<=', 'stok_minimum')->orderBy('stok')->get();
        $categories = KategoriSparepart::where('status', 'aktif')->orderBy('nama_kategori')->get();

        // 4. Finance
        $verificationPayments = Pembayaran::where('status', 'menunggu')->with(['transaksi.pelanggan', 'transaksi.servis.bookingServis.kendaraan'])->latest('id_pembayaran')->get();
        $recentPayments = Pembayaran::with(['transaksi.pelanggan', 'transaksi.servis'])->latest('id_pembayaran')->limit(12)->get();
        $serviceTransactionList = Transaksi::with(['pelanggan', 'servis.bookingServis.kendaraan', 'pembayarans'])->where('jenis_transaksi', 'servis')->latest('id_transaksi')->limit(20)->get();

        return compact(
            'metrics',
            'queue',
            'attention',
            'advisorBookings',
            'readyForCheckin',
            'activeWorkOrders',
            'completedWorkOrders',
            'serviceTypes',
            'availableParts',
            'warehouseParts',
            'lowStockParts',
            'categories',
            'verificationPayments',
            'recentPayments',
            'serviceTransactionList'
        );
    }
}
