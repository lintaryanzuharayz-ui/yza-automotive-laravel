<?php

namespace App\Http\Middleware;

use App\Services\VerifiedCarOwnershipService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedCarOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $customerId = (int) $request->user()?->pelanggan?->id_pelanggan;
        $ownership = app(VerifiedCarOwnershipService::class);
        if ($customerId && $ownership->customerHasVerifiedPurchase($customerId)) {
            return $next($request);
        }

        if ($request->isMethod('GET')) {
            return response()->view('pelanggan.access-locked', [
                'catalogUrl' => route('mobil.index'),
                'backUrl' => route('pelanggan.member'),
            ], 403);
        }

        return redirect()->route('pelanggan.member')->withErrors([
            'ownership' => 'Layanan ini hanya tersedia bagi pelanggan yang telah membeli mobil melalui YZA Automotive.',
        ]);
    }
}
