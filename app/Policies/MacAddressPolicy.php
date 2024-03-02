<?php

namespace App\Policies;

use App\Models\Internet\MacAddress;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Access\HandlesAuthorization;

class MacAddressPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if(! Feature::isFeatureEnabled("internet.wired")) return false;
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can create mac address instances.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        if(! Feature::isFeatureEnabled("internet.wired")) return false;
        return true;
    }

    /**
     * Determine whether the user can approve a mac address.
     *
     * @param User $user
     * @return mixed
     */
    public function update(User $user, MacAddress $macAddress): bool
    {
        if(! Feature::isFeatureEnabled("internet.wired")) return false;
        return false;
    }


    /**
     * Determine whether the user can delete the mac address.
     *
     * @param User $user
     * @param MacAddress $macAddress
     * @return mixed
     */
    public function delete(User $user, MacAddress $macAddress): bool
    {
        if(! Feature::isFeatureEnabled("internet.wired")) return false;
        return $user->can('handle', $macAddress->internetAccess);
    }
}
