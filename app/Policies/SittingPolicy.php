<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class SittingPolicy
{
    /**
     * Determine whether the user can view any sittings.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can administer votings (add sitting, add or change question etc.).
     */
    public function administer(User $user)
    {
        return $user->hasRole(Role::SYS_ADMIN) || $user->hasRole(Role::PRESIDENT)
            || $user->hasRole(Role::SECRETARY);
    }
}
