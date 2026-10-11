<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberVehiclePurchaseRequest;
use App\Models\{Mobil, PesananMobilMember};
use App\Services\MemberVehiclePurchaseService;
use App\Services\{VehicleAvailabilityService, VehicleGalleryService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;
use App\Services\PaymentService;

class MemberVehiclePurchaseController extends Controller
{
    public function index(MemberVehiclePurchaseService $service): View { $customer = $service->customerFor(request()->user()); $orders = PesananMobilMember::with(['mobil', 'pdi', 'transaksi', 'serahTerima'])->where('id_pelanggan', $customer->id_pelanggan)->latest()->paginate(10); return view('pelanggan.pesanan-mobil.index', compact('orders')); }
    public function create(Request $request, Mobil $mobil, MemberVehiclePurchaseService $service, VehicleAvailabilityService $availability, VehicleGalleryService $gallery): View
    {
        $stock = $availability->for($mobil);
        abort_unless($stock['can_purchase'], 404);
        $customer = $service->customerFor(request()->user());
        $galleryData = $gallery->for($mobil);
        $defaultColorKey = $gallery->defaultColor($mobil);
        $requestedColor = strtolower(trim((string) $request->query('warna', '')));
        $selectedColorKey = array_key_exists($requestedColor, $galleryData) ? $requestedColor : $defaultColorKey;
        $defaultGallery = $galleryData[$selectedColorKey] ?? reset($galleryData);
        $primaryImage = $defaultGallery['images'][0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png');

        return view('pelanggan.pesanan-mobil.create', compact(
            'mobil', 'customer', 'stock', 'galleryData', 'defaultColorKey', 'selectedColorKey', 'defaultGallery', 'primaryImage'
        ));
    }
    public function store(StoreMemberVehiclePurchaseRequest $request, Mobil $mobil, MemberVehiclePurchaseService $service): RedirectResponse { try { $order = $service->create($service->customerFor($request->user()), $mobil, $request->validated()); return redirect()->route('pelanggan.pesanan-mobil.show', $order)->with('success', 'Pesanan diterima dan menunggu verifikasi admin.'); } catch (Throwable $e) { return back()->withInput()->withErrors($e->getMessage()); } }
    public function show(PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, VehicleGalleryService $gallery): View { $customer = $service->customerFor(request()->user()); abort_unless((int) $pesanan_mobil->id_pelanggan === (int) $customer->id_pelanggan, 404); $pesanan_mobil->load(['mobil', 'transaksi.pembayarans', 'penjualan', 'pdi', 'serahTerima']); $galleryData = $pesanan_mobil->mobil ? $gallery->for($pesanan_mobil->mobil) : []; $selectedGallery = collect($galleryData)->first(fn (array $item) => strcasecmp((string) ($item['label'] ?? ''), (string) $pesanan_mobil->snapshot_warna) === 0) ?: (reset($galleryData) ?: []); $vehicleImage = $selectedGallery['images'][0]['src'] ?? asset('assets/images/hero/yza-hero-reference-v3.png'); return view('pelanggan.pesanan-mobil.show', compact('pesanan_mobil', 'galleryData', 'selectedGallery', 'vehicleImage'))->with('order', $pesanan_mobil); }
    public function offlinePayment(Request $request, PesananMobilMember $pesanan_mobil, MemberVehiclePurchaseService $service, PaymentService $payments): RedirectResponse
    {
        try {
            $customer = $service->customerFor($request->user());
            abort_unless((int) $pesanan_mobil->id_pelanggan === (int) $customer->id_pelanggan, 404);
            $data = $request->validate(['metode' => ['required', 'in:tunai,transfer'], 'bukti_transfer' => ['required_if:metode,transfer', 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'], 'catatan' => ['nullable', 'string', 'max:1000']]);
            abort_unless($pesanan_mobil->status === 'menunggu_pembayaran' && $pesanan_mobil->transaksi, 422, 'Pembayaran belum dibuka oleh admin.');
            $proof = $request->file('bukti_transfer')?->store('pembayaran', 'private');
            $payment = $payments->create($pesanan_mobil->transaksi->id_transaksi, 0, (string) $pesanan_mobil->transaksi->total, 'transfer', $proof, $data['catatan'] ?? null);
            if ($data['metode'] === 'tunai') $payment->forceFill(['metode' => 'tunai'])->save();
            return back()->with('status', 'Pengajuan pembayaran offline tersimpan dan menunggu verifikasi admin.');
        } catch (Throwable $e) { return back()->withErrors($e->getMessage()); }
    }
}
