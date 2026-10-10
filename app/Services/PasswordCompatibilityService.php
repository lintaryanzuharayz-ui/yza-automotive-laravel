<?php

namespace App\Services;

use App\Models\User;

class PasswordCompatibilityService
{
    public function verify(User $user, string $plainPassword): bool
    {
        return password_verify($plainPassword, (string) $user->getRawOriginal('password'));
    }
}
