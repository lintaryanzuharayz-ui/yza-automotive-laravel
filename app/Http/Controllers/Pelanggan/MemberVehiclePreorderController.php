<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\{MemberVehiclePreorder, Mobil};
use App\Services\{MemberVehiclePreorderService, MemberVehiclePurchaseService, VehicleAvailabilityService};
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
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
        $color = app(\App\Services\VehicleColorStockService::class)->selected($car, $request->query('warna'));
        abort_if($color['stock'] > 0, 409, 'Warna tersedia. Silakan lanjutkan pembelian.');
        return view('pelanggan.preorder.create', ['car' => $car, 'customer' => $customer, 'availability' => $color, 'selectedColor' => $color['label']]);
    }

    public function store(Request $request, MemberVehiclePreorderService $service, MemberVehiclePurchaseService $purchase): Response
    {
        try {
            $customer = $purchase->customerFor($request->user());
            $car = $request->filled('slug') ? app(\App\Http\Controllers\Public\MobilController::class)->catalogPreorder((string) $request->string('slug')) : Mobil::findOrFail($request->integer('mobil'));
            $request->validate(['warna' => ['nullable', 'string', 'max:100']]);
            $service->create($customer, $car, $request->input('warna'));
            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'message' => 'Pre-order berhasil dicatat. Tidak ada pembayaran sampai stok benar-benar tersedia.', 'redirect' => route('pelanggan.preorder.index')]);
            }
            return redirect()->route('pelanggan.preorder.index')->with('status', 'Pre-order berhasil dicatat. Tidak ada pembayaran sampai stok benar-benar tersedia.');
        } catch (Throwable $e) {
            if ($request->expectsJson()) return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    public function cancel(MemberVehiclePreorder $preorder, MemberVehiclePreorderService $service, MemberVehiclePurchaseService $purchase): RedirectResponse
    {
        try { $service->cancel($purchase->customerFor(request()->user()), $preorder); return back()->with('status', 'Pre-order dibatalkan.'); }
        catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
}
