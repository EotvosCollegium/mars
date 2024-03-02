<?php

namespace App\Policies;

use App\Models\Internet\InternetAccess;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Access\HandlesAuthorization;

class InternetAccessPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if(! Feature::isFeatureEnabled("internet")) return false;
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can handle and view any internet accesses.
     *
     * @param User $user
     * @return mixed
     */
    public function handleAny(User $user): bool
    {
        if(! Feature::isFeatureEnabled("internet")) return false;
        return false;
    }

    /**
     * Determine whether the user can view and edit basic details about the internet access.
     *
     * @param User $user
     * @param InternetAccess $internetAccess
     * @return mixed
     */
    public function handle(User $user, InternetAccess $internetAccess): bool
    {
        if(! Feature::isFeatureEnabled("internet")) return false;
        return $user->id === $internetAccess->user_id;
    }

    /**
     * Check if they can view some features about the internet
     */
    public function viewSome(User $user): bool
    {
        if(! Feature::isFeatureEnabled("internet")) return false;
        return true;
    }

    /**
     * Determine whether the user can extend the internet access.
     *
     * @param User $user
     * @param InternetAccess $internetAccess
     * @return mixed
     */
    public function extend(User $user, InternetAccess $internetAccess): bool
    {
        if(! Feature::isFeatureEnabled("internet")) return false;
        return false;
    }
}
