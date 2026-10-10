<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemberVehiclePreorder;
use Illuminate\View\View;

class MemberVehiclePreorderController extends Controller
{
    public function index(): View
    {
        $preorders = MemberVehiclePreorder::with(['pelanggan', 'mobil'])->latest()->paginate(20);
        $waiting = MemberVehiclePreorder::where('status', 'menunggu_stok')->count();
        return view('admin.preorder.index', compact('preorders', 'waiting'));
    }
}
