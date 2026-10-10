<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\RiwayatPoin;
use App\Services\{MemberIdentityService, YzaPointService};
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointController extends Controller
{
    public function index(Request $request, MemberIdentityService $identity, YzaPointService $points): View
    {
        $customer = $request->user()->pelanggan;
        abort_unless($customer, 403);
        $identity->assertActive($customer, $request->user());
        $account = $customer->poinMember;
        $hasVerifiedOwnership = app(\App\Services\VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase((int) $customer->id_pelanggan);
        $history = RiwayatPoin::with('transaksi')
            ->where('id_pelanggan', $customer->id_pelanggan)
            ->latest('created_at')
            ->paginate(15);
        return view('pelanggan.poin.index', compact('customer', 'account', 'history', 'points', 'hasVerifiedOwnership'));
    }
}
