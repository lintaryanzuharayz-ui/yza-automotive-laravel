<?php

namespace App\Services;

use App\Models\{BookingServis, Servis, Sparepart, Transaksi};
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
            ['label' => 'Booking Hari Ini', 'value' => BookingServis::whereDate('tanggal', $today)->whereNotIn('status', ['ditolak', 'dibatalkan'])->count(), 'hint' => 'Agenda service hari ini', 'tone' => 'red'],
            ['label' => 'Menunggu Pemeriksaan', 'value' => Servis::where('status', 'pemeriksaan')->count(), 'hint' => 'Work order siap dicek', 'tone' => 'amber'],
            ['label' => 'Sedang Dikerjakan', 'value' => Servis::where('status', 'dikerjakan')->count(), 'hint' => 'Pekerjaan aktif', 'tone' => 'red'],
            ['label' => 'Menunggu Pembayaran', 'value' => (clone $serviceTransactions)->whereIn('status', ['belum_bayar', 'dp'])->count(), 'hint' => 'Transaksi belum lunas', 'tone' => 'dark'],
            ['label' => 'Selesai Hari Ini', 'value' => Servis::where('status', 'selesai')->whereDate('tanggal_selesai', $today)->count(), 'hint' => 'Servis ditutup hari ini', 'tone' => 'green'],
            ['label' => 'Stok Kritis', 'value' => Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->count(), 'hint' => 'Perlu pengadaan', 'tone' => 'amber'],
        ];

        $queue = BookingServis::with(['pelanggan', 'kendaraan', 'jenisServis', 'servis.transaksi'])
            ->whereDate('tanggal', $today)
            ->whereNotIn('status', ['ditolak', 'dibatalkan'])
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
            $attention->push(['label' => 'Booking belum diproses', 'detail' => $booking->kode_booking.' · '.$booking->pelanggan?->nama_lengkap, 'tone' => 'warning', 'booking_id' => $booking->id_booking_servis]);
        }
        $pendingPayments = (clone $serviceTransactions)->whereIn('status', ['belum_bayar', 'dp'])->with('servis')->latest('tanggal')->limit(5)->get();
        foreach ($pendingPayments as $transaction) {
            $attention->push(['label' => 'Pembayaran servis pending', 'detail' => $transaction->kode_transaksi, 'tone' => 'dark']);
        }
        $critical = Sparepart::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->orderBy('stok')->limit(5)->get();
        foreach ($critical as $part) {
            $attention->push(['label' => 'Stok kritis', 'detail' => $part->nama_sparepart.' · '.$part->stok.' tersisa', 'tone' => 'warning']);
        }

        return compact('metrics', 'queue', 'attention');
    }
}
