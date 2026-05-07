<?php

namespace App\Policies;

use App\Models\PendingAction;
use App\Models\User;

class PendingActionPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, ['Administrator', 'Expert in effects'], true);
    }

    public function view(User $user, PendingAction $pendingAction): bool
    {
        return $this->viewAny($user);
    }
}