<?php

namespace App\Http\Controllers;

use App\Models\{BookingServis, Mobil, Pembayaran, Pelanggan, PesananMobilMember, Servis, Transaksi};
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $role = $user->roleKey();
        $metrics = match ($role) {
            'sales' => [
                ['label' => 'Prospek Aktif', 'value' => \App\Models\SalesLead::where('id_sales', $user->petugas?->id_petugas)->whereNotIn('status', ['berhasil', 'tidak_lanjut'])->count(), 'hint' => 'Prospek yang masih berjalan'],
                ['label' => 'Test Drive', 'value' => \App\Models\TestDrive::where('id_sales', $user->petugas?->id_petugas)->whereIn('status', ['menunggu', 'disetujui'])->count(), 'hint' => 'Jadwal mendatang'],
                ['label' => 'Pesanan Saya', 'value' => PesananMobilMember::where('id_sales', $user->petugas?->id_petugas)->count(), 'hint' => 'Pesanan yang ditangani'],
                ['label' => 'Serah Terima Selesai', 'value' => PesananMobilMember::where('id_sales', $user->petugas?->id_petugas)->where('status', 'diserahkan')->count(), 'hint' => 'Prestasi final'],
            ],
            'owner' => [
                ['label' => 'Penjualan', 'value' => Transaksi::whereIn('jenis_transaksi', ['penjualan_mobil', 'pembelian_mobil'])->count(), 'hint' => 'Transaksi kendaraan tercatat'],
                ['label' => 'Pendapatan Servis', 'value' => 'Rp '.number_format((float) Transaksi::where('jenis_transaksi', 'servis')->sum('total'), 0, ',', '.'), 'hint' => 'Total dari data transaksi'],
                ['label' => 'Stok Kendaraan', 'value' => Mobil::whereIn('status', ['tersedia', 'dibooking'])->count(), 'hint' => 'Unit aktif'],
                ['label' => 'Pelanggan', 'value' => Pelanggan::count(), 'hint' => 'Data terdaftar'],
            ],
            'finance' => [
                ['label' => 'Pembayaran Menunggu', 'value' => Pembayaran::where('status', 'menunggu')->count(), 'hint' => 'Perlu verifikasi'],
                ['label' => 'Transaksi', 'value' => Transaksi::count(), 'hint' => 'Seluruh transaksi'],
            ],
            'service_advisor' => [
                ['label' => 'Booking Masuk', 'value' => BookingServis::whereIn('status', ['menunggu', 'diterima'])->count(), 'hint' => 'Perlu ditindaklanjuti'],
                ['label' => 'Work Order Aktif', 'value' => Servis::whereIn('status', ['pemeriksaan', 'dikerjakan'])->count(), 'hint' => 'Pekerjaan berjalan'],
            ],
            'mechanic' => [
                ['label' => 'Work Order Saya', 'value' => Servis::where('id_petugas', $user->petugas?->id_petugas)->whereIn('status', ['pemeriksaan', 'dikerjakan'])->count(), 'hint' => 'Terassign ke Anda'],
                ['label' => 'Menunggu QC', 'value' => Servis::where('id_petugas', $user->petugas?->id_petugas)->where('status', 'dikerjakan')->whereHas('qualityControl', fn ($q) => $q->where('status', 'in_progress'))->count(), 'hint' => 'Perlu pemeriksaan mutu'],
            ],
            'quality_control' => [
                ['label' => 'Work Order', 'value' => Servis::whereIn('status', ['pemeriksaan', 'dikerjakan'])->count(), 'hint' => 'Menunggu proses QC'],
            ],
            default => [
                ['label' => 'Pesanan Mobil', 'value' => PesananMobilMember::count(), 'hint' => 'Order member'],
            ],
        };

        return view('staff.dashboard', compact('user', 'role', 'metrics'));
    }
}
