<?php

namespace App\Policies;

use App\Models\Internet\InternetAccess;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class InternetAccessPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can handle and view any internet accesses.
     *
     * @param User $user
     * @return bool
     */
    public function handleAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view and edit basic details about the internet access.
     *
     * @param User $user
     * @param InternetAccess $internetAccess
     * @return bool
     */
    public function handle(User $user, InternetAccess $internetAccess): bool
    {
        return $user->id === $internetAccess->user_id;
    }

    /**
     * Determine whether the user can extend the internet access.
     *
     * @param User $user
     * @param InternetAccess $internetAccess
     * @return bool
     */
    public function extend(User $user, InternetAccess $internetAccess): bool
    {
        return false;
    }
}
