<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

class SittingPolicy
{
    /**
     * Determine whether the user can view any sittings.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isCollegist() && $user->isActive() || $user->isAdmin();
    }

    /**
     * Determine whether the user can administer votings (add sitting, add or change question etc.).
     */
    public function administer(User $user)
    {
        return $user->hasRole([Role::SYS_ADMIN, Role::STUDENT_COUNCIL => Role::PRESIDENT, Role::STUDENT_COUNCIL_SECRETARY]);
    }
}
