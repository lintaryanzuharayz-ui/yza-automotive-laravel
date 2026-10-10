<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\{MemberAccessService, PasswordCompatibilityService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly PasswordCompatibilityService $passwords)
    {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, MemberAccessService $access): RedirectResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($validated['identity']);
        $key = $this->throttleKey($request, $identity);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'identity' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('identity');
        }

        $user = User::query()
            ->with(['role', 'petugas', 'pelanggan'])
            ->where('status', 'aktif')
            ->where(function ($query) use ($identity): void {
                $query->where('username', $identity)->orWhere('email', $identity);
            })
            ->first();

        if (!$user || !$this->passwords->verify($user, $validated['password'])) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'identity' => 'Username/email atau password tidak valid.',
            ])->onlyInput('identity');
        }

        RateLimiter::clear($key);
        Auth::login($user);
        $request->session()->regenerate();
        User::query()->whereKey($user->getKey())->update(['last_login' => now()]);

        if ($user->roleName() === 'pelanggan' && $access->needsMembershipActivation($user)) {
            if ($user->pelanggan?->membership) {
                return redirect()->route('pelanggan.membership.activation');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'identity' => 'Akun pelanggan ini belum memiliki membership yang dapat diaktifkan. Silakan hubungi YZA Automotive.',
            ])->onlyInput('identity');
        }

        if ($user->roleName() === 'pelanggan') {
            return redirect()->route('pelanggan.member');
        }

        return redirect()->intended(route($this->dashboardRoute($user->roleName())));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function throttleKey(Request $request, string $identity): string
    {
        return Str::transliterate(Str::lower($identity)).'|'.$request->ip();
    }

    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'petugas' => 'petugas.dashboard',
            'pelanggan' => 'pelanggan.dashboard',
            default => 'login',
        };
    }
}
