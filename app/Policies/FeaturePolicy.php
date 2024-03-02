<?php

namespace App\Policies;

use App\Models\Fault;
use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Access\HandlesAuthorization;

class FeaturePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view features.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the status of the feature.
     *
     * @param User $user
     * @return mixed
     */
    public function update(User $user, Feature $feature)
    {
        return $user->isAdmin();
    }
}
