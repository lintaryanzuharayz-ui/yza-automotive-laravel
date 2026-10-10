<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\MemberDashboardService;
use App\Services\VerifiedCarOwnershipService;
use App\Services\ServiceOperationsDashboardService;
use App\Models\Notifikasi;

class DashboardController extends Controller
{
    public function admin(Request $request, ServiceOperationsDashboardService $operations): View
    {
        return view('admin.dashboard', ['user' => $request->user(), 'area' => 'Admin', 'serviceData' => $operations->data()]);
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
