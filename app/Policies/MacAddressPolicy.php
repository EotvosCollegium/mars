<?php

namespace App\Policies;

use App\Models\Internet\MacAddress;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MacAddressPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
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
        return true;
    }

    /**
     * Determine whether the user can approve a mac address.
     *
     * @param User $user
     * @return bool
     */
    public function update(User $user, MacAddress $macAddress): bool
    {
        return false;
    }


    /**
     * Determine whether the user can delete the mac address.
     *
     * @param User $user
     * @param MacAddress $macAddress
     * @return bool
     */
    public function delete(User $user, MacAddress $macAddress): bool
    {
        return $user->can('handle', $macAddress->internetAccess);
    }
}
