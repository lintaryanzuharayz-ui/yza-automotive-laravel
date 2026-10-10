<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Servis;
use Illuminate\Http\Request;

class ServisController extends Controller
{
    private function customer(Request $request)
    {
        return $request->user()->pelanggan;
    }

    public function index(Request $request)
    {
        $rows = Servis::with(['kendaraan', 'bookingServis.jenisServis', 'details.jenisServis'])
            ->where('id_pelanggan', $this->customer($request)->id_pelanggan)
            ->latest('id_servis')->paginate(15);

        return view('pelanggan.servis.index', compact('rows'));
    }

    public function show(Request $request, Servis $servis)
    {
        abort_unless((int) $servis->id_pelanggan === (int) $this->customer($request)->id_pelanggan, 403);
        $servis->load(['kendaraan', 'bookingServis.jenisServis', 'details.jenisServis', 'sparepartItems.sparepart', 'transaksi.pembayarans']);

        return view('pelanggan.servis.show-premium', compact('servis'));
    }
}
