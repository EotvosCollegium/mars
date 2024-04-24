<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

class GeneralAssemblyPolicy
{
    /**
     * Determine whether the user can view any general_assemblies.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->isCollegist(alumni: false) || $user->isAdmin() || $user->hasRole(Role::SECRETARY);
    }

    /**
     * Determine whether the user can administer votings (add general_assembly, add or change question etc.).
     */
    public function administer(User $user)
    {
        return $user->hasRole([Role::SYS_ADMIN, Role::STUDENT_COUNCIL => Role::PRESIDENT, Role::STUDENT_COUNCIL_SECRETARY]);
    }
}
