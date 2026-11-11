<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', '');
        $date = (string) $request->input('date', '');
        $perPage = in_array((int) $request->input('perPage', 10), [10, 20, 50], true) ? (int) $request->input('perPage', 10) : 10;
        $statuses = ['pending_payment', 'active', 'cancelled'];

        $query = Membership::with(['customer.user', 'gatewayPayment'])->latest('id_membership');
        if (in_array($status, $statuses, true)) {
            $query->where('status', $status);
        }
        if ($date !== '') {
            $query->whereDate('registered_at', $date);
        }
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('member_id', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('nama_lengkap', 'like', "%{$search}%"));
            });
        }

        $items = $query->paginate($perPage)->withQueryString();
        $all = Membership::with('gatewayPayment')->get();
        $verifiedRevenue = (float) $all->filter(fn ($membership) => in_array($membership->gatewayPayment?->status_provider, ['settlement', 'capture'], true))
            ->sum(fn ($membership) => (float) $membership->gatewayPayment->gross_amount);
        $renewals = null;
        $stats = [
            'total' => $all->count(),
            'active' => $all->where('status', 'active')->count(),
            'pending' => $all->where('status', 'pending_payment')->count(),
            'revenue' => $verifiedRevenue,
            'renewals' => $renewals,
        ];
        $statusDistribution = collect($statuses)->map(fn ($value) => (object) [
            'status' => $value,
            'label' => match ($value) { 'active' => 'Active', 'pending_payment' => 'Pending Payment', 'cancelled' => 'Cancelled' },
            'total' => $all->where('status', $value)->count(),
        ]);
        $distributionTotal = max(1, $all->count());
        $operations = [
            'activatedToday' => $all->where('status', 'active')->filter(fn ($membership) => $membership->activated_at?->isToday())->count(),
            'pending' => $stats['pending'],
            'renewals' => null,
            'churnRisk' => null,
        ];

        return view('admin.memberships.index', compact('items', 'stats', 'operations', 'statusDistribution', 'distributionTotal', 'search', 'status', 'date', 'perPage'));
    }
}
