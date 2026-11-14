<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\{MemberAccessService, PasswordCompatibilityService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly PasswordCompatibilityService $passwords)
    {
    }

    public function create(Request $request): \Illuminate\Http\Response|RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()
            ->view('auth.login')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    public function createPetugas(Request $request): \Illuminate\Http\Response|RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()
            ->view('auth.login-petugas')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    public function store(Request $request, MemberAccessService $access): RedirectResponse
    {
        $identity = trim((string) ($request->input('identity') ?: $request->input('username') ?: $request->input('email')));
        $password = (string) $request->input('password');

        if ($identity === '' || $password === '') {
            return back()->withErrors([
                'identity' => 'Username/email dan password wajib diisi.',
            ])->onlyInput('identity');
        }

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
                $query->whereRaw('LOWER(username) = ?', [strtolower($identity)])
                      ->orWhereRaw('LOWER(email) = ?', [strtolower($identity)]);
            })
            ->first();

        $passwordValid = $user && (
            Hash::check($password, $user->password) ||
            $this->passwords->verify($user, $password)
        );

        if (!$user || !$passwordValid) {
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

    public function storePetugas(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $identity = trim((string) ($request->input('identity') ?: $request->input('username') ?: $request->input('email')));
        $password = (string) $request->input('password');

        if ($identity === '' || $password === '') {
            return back()->withErrors([
                'identity' => 'Username/email dan password wajib diisi.',
            ])->onlyInput('identity');
        }

        $key = $this->throttleKey($request, 'petugas:'.$identity);

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
                $query->whereRaw('LOWER(username) = ?', [strtolower($identity)])
                      ->orWhereRaw('LOWER(email) = ?', [strtolower($identity)]);
            })
            ->first();

        $passwordValid = $user && (
            Hash::check($password, $user->password) ||
            $this->passwords->verify($user, $password)
        );

        if (!$user || !$passwordValid) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'identity' => 'Username/email atau password tidak valid.',
            ])->onlyInput('identity');
        }

        if (!$user->isStaff()) {
            RateLimiter::hit($key, 60);

            return back()->withErrors([
                'identity' => 'Akun ini terdaftar sebagai Member/Pelanggan. Silakan gunakan Portal Member untuk masuk.',
            ])->onlyInput('identity');
        }

        RateLimiter::clear($key);
        Auth::login($user);
        $request->session()->regenerate();
        User::query()->whereKey($user->getKey())->update(['last_login' => now()]);

        $targetRoute = $this->dashboardRoute($user->roleName());
        $intended = $request->session()->get('url.intended');
        if ($intended && !in_array($intended, [url('/'), url('/login'), url('/petugas/login'), url('/admin/login')], true)) {
            return redirect()->intended(route($targetRoute));
        }

        return redirect()->route($targetRoute);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $isStaff = $request->user()?->isStaff() || Str::contains(url()->previous(), ['petugas', 'admin', 'operations', 'service', 'inventory', 'invoice']);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isStaff ? 'petugas.login' : 'login');
    }

    private function throttleKey(Request $request, string $identity): string
    {
        return Str::transliterate(Str::lower($identity)).'|'.$request->ip();
    }

    private function dashboardRoute(string $role): string
    {
        return match ($role) {
            'admin', 'super_admin' => 'admin.dashboard',
            'admin', 'super_admin' => 'admin.dashboard',
            'sales' => 'sales.dashboard',
            'owner' => 'owner.dashboard',
            'petugas' => 'petugas.dashboard',
            'pelanggan' => 'pelanggan.member',
            default => 'petugas.dashboard',
        };
    }
}
