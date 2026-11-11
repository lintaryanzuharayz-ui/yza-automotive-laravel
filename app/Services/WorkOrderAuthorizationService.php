<?php

namespace App\Services;

use App\Models\{Servis, User};
use Illuminate\Auth\Access\AuthorizationException;

class WorkOrderAuthorizationService
{
    public function authorize(User $user, ?Servis $service, string $ability): void
    {
        $user = $user->fresh(['role', 'petugas']);
        if (!$user || $user->status !== 'aktif' || $user->petugas?->status !== 'aktif') {
            throw new AuthorizationException();
        }
        $role = $user->roleKey();
        $manager = $role === 'super_admin' || $user->roleName() === 'petugas'
            || ($role !== 'owner' && $role !== 'mechanic' && $user->hasPermission('service.manage'));
        $allowed = match ($ability) {
            'view' => $role === 'super_admin' || $user->roleName() === 'petugas' || $user->hasPermission('service.view'),
            'manage' => $manager,
            'qc' => $manager || ($role !== 'owner' && $user->hasPermission('qc.manage')),
            'work' => $manager || ($role === 'mechanic' && $user->hasPermission('service.assigned')),
            default => false,
        };
        if (!$allowed) throw new AuthorizationException();

        // Only the active ledger grants mechanic access. id_petugas alone must
        // not restore a revoked/missing assignment. Check both staff statuses.
        if ($role === 'mechanic') {
            if (!$service || !$service->assignments()
                ->where('id_petugas', $user->petugas->id_petugas)
                ->whereIn('assignment_role', ['primary', 'assistant'])
                ->where('assigned_at', '<=', now())
                ->whereNull('unassigned_at')->exists()) {
                throw new AuthorizationException();
            }
        }
    }

    public function assertCurrentActor(Servis|int|null $service, string $ability = 'work'): void
    {
        $user = auth()->user();
        // Trusted console imports and fixture setup have no browser principal.
        // HTTP entry points always require authentication.
        if (!$user && app()->runningInConsole()) return;
        if (!$user) throw new AuthorizationException();
        $service = is_int($service) ? Servis::findOrFail($service) : $service;
        $this->authorize($user, $service, $ability);
    }
}
