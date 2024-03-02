<?php

namespace App\Policies;

use App\Models\Fault;
use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Access\HandlesAuthorization;

class FaultPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create fault.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user): bool
    {
        if(! Feature::isFeatureEnabled("faults")) return false;
        return $user->hasRole([Role::STAFF, Role::COLLEGIST, Role::TENANT]);
    }

    /**
     * Determine whether the user can view faults.
     *
     * @param User $user
     * @return mixed
     */
    public function view(User $user): bool
    {
        if(! Feature::isFeatureEnabled("faults")) return false;
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
        if(! Feature::isFeatureEnabled("faults")) return false;
        return $user->hasRole(Role::STAFF);
    }
}
