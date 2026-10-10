<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\PromoMember;
use App\Services\{MemberIdentityService, PromoMemberService};
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoMemberController extends Controller
{
    public function index(Request $request, MemberIdentityService $identity, PromoMemberService $promos): View
    {
        $customer = $request->user()->pelanggan; abort_unless($customer, 403);
        $hasVerifiedOwnership = app(\App\Services\VerifiedCarOwnershipService::class)->customerHasVerifiedPurchase((int) $customer->id_pelanggan);
        return view('pelanggan.promo.index', ['customer' => $customer, 'items' => $promos->visible(), 'hasVerifiedOwnership' => $hasVerifiedOwnership]);
    }
    public function show(Request $request, PromoMember $promoMember, MemberIdentityService $identity): View
    {
        $customer = $request->user()->pelanggan; abort_unless($customer, 403);
        abort_unless($promoMember->isVisibleAt(), 404);
        return view('pelanggan.promo.show', ['customer' => $customer, 'item' => $promoMember]);
    }
}
