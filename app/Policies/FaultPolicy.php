<?php

namespace App\Policies;

use App\Models\Fault;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FaultPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create fault.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole([Role::STAFF, Role::COLLEGIST, Role::TENANT]);
    }

    /**
     * Determine whether the user can view faults.
     *
     * @param User $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $this->create($user);
    }

    /**
     * Determine whether the user can update the status of the fault.
     *
     * @param User $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->hasRole(Role::STAFF);
    }
}
