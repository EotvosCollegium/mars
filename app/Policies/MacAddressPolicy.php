<?php

namespace App\Policies;

use App\Models\Internet\MacAddress;
use App\Models\Role;
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
        if (! $user->hasRole(Role::INTERNET_USER)) {
            return false;
        }
    }

    /**
     * Determine whether the user can view any mac addresses (in general).
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the mac address.
     *
     * @param User $user
     * @param MacAddress $macAddress
     * @return mixed
     */
    public function view(User $user, MacAddress $macAddress): bool
    {
        return $user->id === $macAddress->user_id;
    }

    /**
     * Determine whether the user can create mac addresses.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the mac address.
     *
     * @param User $user
     * @param MacAddress $macAddress
     * @return mixed
     */
    public function update(User $user, MacAddress $macAddress): bool
    {
        return $user->id === $macAddress->user_id;
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
        return $user->id === $macAddress->user_id;
    }

    /**
     * Determine whether the user can accept the mac address request.
     *
     * @param User $user
     * @return mixed
     */
    public function accept(User $user): bool
    {
        return false;
    }
}
