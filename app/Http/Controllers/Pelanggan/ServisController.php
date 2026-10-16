<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\KendaraanPelanggan;
use App\Models\Servis;
use App\Services\ServiceExecutionService;
use App\Services\VehicleGalleryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ServisController extends Controller
{
    private function customer(Request $request)
    {
        return $request->user()->pelanggan;
    }

    public function index(Request $request, VehicleGalleryService $gallery)
    {
        $customerId = (int) $this->customer($request)->id_pelanggan;
        $vehicles = KendaraanPelanggan::with(['penjualan.mobil', 'pesanan'])
            ->where('id_pelanggan', $customerId)
            ->where('status', 'aktif')
            ->orderBy('merk')
            ->orderBy('model')
            ->get();

        $selectedVehicleId = $request->integer('kendaraan');
        $selectedVehicle = $vehicles->firstWhere('id_kendaraan_pelanggan', $selectedVehicleId) ?: $vehicles->first();
        $selectedVehicleId = $selectedVehicle?->id_kendaraan_pelanggan;

        $assignmentTable = Schema::hasTable('service_work_order_assignments');
        $rows = Servis::with(['kendaraan', 'bookingServis.jenisServis', 'details.jenisServis', 'transaksi', 'petugas'])
            ->when($assignmentTable, fn ($query) => $query->with(['assignments.petugas']))
            ->where('id_pelanggan', $customerId)
            ->when($selectedVehicleId, fn ($query) => $query->where('id_kendaraan_pelanggan', $selectedVehicleId))
            ->latest('tanggal_masuk')
            ->paginate(15)
            ->withQueryString();

        $serviceCount = $selectedVehicle ? (int) $selectedVehicle->servis()->where('id_pelanggan', $customerId)->count() : 0;
        $lastService = $selectedVehicle ? $selectedVehicle->servis()->where('id_pelanggan', $customerId)->latest('tanggal_masuk')->first() : null;
        $nextBooking = $selectedVehicle?->bookingServis()->with('jenisServis')->whereDate('tanggal', '>=', now()->toDateString())->whereNotIn('status', ['selesai', 'ditolak', 'dibatalkan', 'kadaluarsa', 'expired'])->orderBy('tanggal')->orderBy('jam')->first();

        $vehicleImage = null;
        if ($selectedVehicle?->penjualan?->mobil) {
            $mobil = $selectedVehicle->penjualan->mobil;
            $preferred = $selectedVehicle->pesanan?->snapshot_warna ?: $selectedVehicle->warna;
            $selectedGallery = $gallery->forColor($mobil, $preferred);
            if (!$selectedGallery && !$preferred) $selectedGallery = $gallery->for($mobil)[$gallery->defaultColor($mobil)] ?? [];
            $vehicleImage = $selectedGallery['images'][0]['src'] ?? null;
        }

        if (!$assignmentTable) $rows->getCollection()->each(fn (Servis $service) => $service->setRelation('assignments', collect()));
        return view('pelanggan.servis.index', compact('rows', 'vehicles', 'selectedVehicle', 'selectedVehicleId', 'serviceCount', 'lastService', 'nextBooking', 'vehicleImage'));
    }

    public function show(Request $request, Servis $servis, VehicleGalleryService $gallery, ServiceExecutionService $execution)
    {
        abort_unless((int) $servis->id_pelanggan === (int) $this->customer($request)->id_pelanggan, 403);
        $servis->load(['kendaraan.penjualan.mobil', 'kendaraan.pesanan', 'bookingServis.jenisServis', 'details.jenisServis', 'sparepartItems.sparepart', 'transaksi.pembayarans', 'approvalRequests.items', 'petugas']);
        if (Schema::hasTable('service_work_order_assignments')) $servis->load('assignments.petugas');
        else $servis->setRelation('assignments', collect());
        $vehicleImage = null;
        $mobil = $servis->kendaraan?->penjualan?->mobil;
        if ($mobil) {
            $preferred = $servis->kendaraan->pesanan?->snapshot_warna ?: $servis->kendaraan->warna;
            $selectedGallery = $gallery->forColor($mobil, $preferred);
            if (!$selectedGallery && !$preferred) $selectedGallery = $gallery->for($mobil)[$gallery->defaultColor($mobil)] ?? [];
            $vehicleImage = $selectedGallery['images'][0]['src'] ?? null;
        }
        $executionTracking = $execution->tracking($servis);

        return view('pelanggan.servis.show-premium', compact('servis', 'vehicleImage', 'executionTracking'));
    }

    public function approval(Request $request, Servis $servis, VehicleGalleryService $gallery, ServiceExecutionService $execution)
    {
        abort_unless((int) $servis->id_pelanggan === (int) $this->customer($request)->id_pelanggan, 403);
        $servis->load(['kendaraan.penjualan.mobil', 'kendaraan.pesanan', 'bookingServis.jenisServis', 'approvalRequests.items']);
        $vehicleImage = null;
        $mobil = $servis->kendaraan?->penjualan?->mobil;
        if ($mobil) {
            $preferred = $servis->kendaraan->pesanan?->snapshot_warna ?: $servis->kendaraan->warna;
            $selectedGallery = $gallery->forColor($mobil, $preferred);
            if (!$selectedGallery && !$preferred) $selectedGallery = $gallery->for($mobil)[$gallery->defaultColor($mobil)] ?? [];
            $vehicleImage = $selectedGallery['images'][0]['src'] ?? null;
        }
        $executionTracking = $execution->tracking($servis);

        return view('pelanggan.servis.approval', compact('servis', 'vehicleImage', 'executionTracking'));
    }
}
