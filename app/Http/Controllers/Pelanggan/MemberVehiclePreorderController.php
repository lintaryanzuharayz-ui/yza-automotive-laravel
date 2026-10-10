<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\{MemberVehiclePreorder, Mobil};
use App\Services\{MemberVehiclePreorderService, MemberVehiclePurchaseService, VehicleAvailabilityService};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Throwable;

class MemberVehiclePreorderController extends Controller
{
    public function index(MemberVehiclePurchaseService $purchase): View
    {
        $customer = $purchase->customerFor(request()->user());
        $preorders = MemberVehiclePreorder::with('mobil')->where('id_pelanggan', $customer->id_pelanggan)->latest()->paginate(12);
        return view('pelanggan.preorder.index', compact('preorders'));
    }

    public function create(Request $request, MemberVehiclePurchaseService $purchase, VehicleAvailabilityService $availability): View
    {
        $customer = $purchase->customerFor($request->user());
        $car = $request->filled('slug') ? app(\App\Http\Controllers\Public\MobilController::class)->catalogPreorder((string) $request->string('slug')) : Mobil::findOrFail($request->integer('mobil'));
        $state = $availability->for($car);
        abort_if(($state['can_purchase'] ?? false) || (($state['stock'] ?? 0) > 0), 409, 'Stok tersedia. Silakan lanjutkan ke Beli Sekarang.');
        return view('pelanggan.preorder.create', ['car' => $car, 'customer' => $customer, 'availability' => $state]);
    }

    public function store(Request $request, MemberVehiclePreorderService $service, MemberVehiclePurchaseService $purchase): RedirectResponse
    {
        try {
            $customer = $purchase->customerFor($request->user());
            $car = $request->filled('slug') ? app(\App\Http\Controllers\Public\MobilController::class)->catalogPreorder((string) $request->string('slug')) : Mobil::findOrFail($request->integer('mobil'));
            $service->create($customer, $car);
            return redirect()->route('pelanggan.preorder.index')->with('status', 'Pre-order berhasil dicatat. Tidak ada pembayaran sampai stok benar-benar tersedia.');
        } catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); }
    }

    public function cancel(MemberVehiclePreorder $preorder, MemberVehiclePreorderService $service, MemberVehiclePurchaseService $purchase): RedirectResponse
    {
        try { $service->cancel($purchase->customerFor(request()->user()), $preorder); return back()->with('status', 'Pre-order dibatalkan.'); }
        catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
}
