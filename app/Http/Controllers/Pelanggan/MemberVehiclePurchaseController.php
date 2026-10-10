<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberVehiclePurchaseRequest;
use App\Models\{Mobil, PesananMobilMember};
use App\Services\MemberVehiclePurchaseService;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

class MemberVehiclePurchaseController extends Controller
{
    public function index(MemberVehiclePurchaseService $service): View { $customer = $service->customerFor(request()->user()); $orders = PesananMobilMember::with(['mobil', 'pdi', 'transaksi', 'serahTerima'])->where('id_pelanggan', $customer->id_pelanggan)->latest()->paginate(10); return view('pelanggan.pesanan-mobil.index', compact('orders')); }
    public function create(Mobil $mobil, MemberVehiclePurchaseService $service, VehicleAvailabilityService $availability): View { abort_unless($availability->for($mobil)['can_purchase'], 404); $customer = $service->customerFor(request()->user()); return view('pelanggan.pesanan-mobil.create', compact('mobil', 'customer')); }
    public function store(StoreMemberVehiclePurchaseRequest $request, Mobil $mobil, MemberVehiclePurchaseService $service): RedirectResponse { try { $order = $service->create($service->customerFor($request->user()), $mobil, $request->validated()); return redirect()->route('pelanggan.pesanan-mobil.show', $order)->with('success', 'Pesanan diterima dan menunggu verifikasi admin.'); } catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); } }
    public function show(PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service): View { $customer = $service->customerFor(request()->user()); abort_unless((int) $pesanan_mobil->id_pelanggan === (int) $customer->id_pelanggan, 404); $pesanan_mobil->load(['mobil', 'transaksi.pembayarans', 'penjualan', 'pdi', 'serahTerima']); return view('pelanggan.pesanan-mobil.show', ['order' => $pesanan_mobil]); }
}
