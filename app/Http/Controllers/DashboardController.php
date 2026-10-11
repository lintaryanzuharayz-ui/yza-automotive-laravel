<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\MemberDashboardService;
use App\Services\VerifiedCarOwnershipService;
use App\Services\ServiceOperationsDashboardService;
use App\Models\{BookingMobil, Mobil, Notifikasi, Pembayaran, PesananMobilMember, Pelanggan, TestDrive, Transaksi};

class DashboardController extends Controller
{
    public function admin(Request $request, ServiceOperationsDashboardService $operations): View
    {
        $serviceData = $operations->data();
        $metrics = [
            ['label' => 'Pesanan Mobil', 'value' => PesananMobilMember::count(), 'hint' => 'Seluruh order member', 'tone' => 'red'],
            ['label' => 'Transaksi Aktif', 'value' => Transaksi::whereIn('status', ['draft', 'belum_bayar', 'dp'])->count(), 'hint' => 'Belum ditutup', 'tone' => 'amber'],
            ['label' => 'Booking Hari Ini', 'value' => BookingMobil::whereDate('tanggal_booking', now('Asia/Jakarta')->toDateString())->whereNotIn('status', ['dibatalkan', 'kadaluarsa'])->count(), 'hint' => 'Agenda showroom', 'tone' => 'dark'],
            ['label' => 'Mobil Aktif', 'value' => Mobil::whereIn('status', ['tersedia', 'dibooking', 'dalam_pemeriksaan', 'terjual'])->count(), 'hint' => 'Unit di katalog', 'tone' => 'green'],
            ['label' => 'Pelanggan', 'value' => Pelanggan::count(), 'hint' => 'Data pelanggan terdaftar', 'tone' => 'red'],
            ['label' => 'Test Drive', 'value' => TestDrive::whereNotIn('status', ['selesai', 'dibatalkan'])->count(), 'hint' => 'Agenda yang berjalan', 'tone' => 'amber'],
        ];
        $pendingOrders = PesananMobilMember::with(['pelanggan', 'mobil'])->latest('created_at')->limit(6)->get();
        $pendingPayments = Pembayaran::where('status', 'menunggu')->count();
        return view('admin.dashboard', compact('serviceData', 'metrics', 'pendingOrders', 'pendingPayments') + ['user' => $request->user(), 'area' => 'Admin']);
    }

    public function petugas(Request $request, ServiceOperationsDashboardService $operations): View
    {
        return view('petugas.dashboard', ['user' => $request->user(), 'area' => 'Petugas', ...$operations->data()]);
    }

    public function pelanggan(Request $request, MemberDashboardService $members, VerifiedCarOwnershipService $ownership)
    {
        $customer=$request->user()->pelanggan;abort_unless($customer,403);if($customer->membership&& !$customer->membership->isActive())return redirect()->route('pelanggan.membership.activation')->with('warning','Aktifkan YZA Member terlebih dahulu untuk menggunakan layanan ini.');$data=$members->for($customer,$request->user());$data['verifiedVehicles']=$ownership->verifiedVehiclesFor((int) $customer->id_pelanggan);$data['hasVerifiedOwnership']=$data['verifiedVehicles']->isNotEmpty();$data['catalogCars']=app(\App\Http\Controllers\Public\MobilController::class)->sportShowcases()->take(4)->values();$data['notifications']=Notifikasi::where('id_user',$request->user()->id_user)->latest('created_at')->limit(5)->get();$data['notificationUnreadCount']=Notifikasi::where('id_user',$request->user()->id_user)->where('is_read',false)->count();return view('pelanggan.member-center-empty',$data);
    }
}
