<?php

namespace App\Services;

use App\Models\User;

class MemberAccessService
{
    /**
     * The membership table currently has no validity dates. Keep the rule
     * centralized so expiry/renewal can be added without changing callers.
     */
    public function hasActiveMembership(?User $user): bool
    {
        return $this->status($user) === 'active';
    }

    public function status(?User $user): ?string
    {
        if (!$user || $user->roleName() !== 'pelanggan' || $user->status !== 'aktif') {
            return null;
        }

        return $user->pelanggan?->membership?->status;
    }

    public function needsMembershipActivation(?User $user): bool
    {
        return $this->status($user) !== 'active';
    }
}
