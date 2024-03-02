<?php

namespace App\Policies;

use App\Models\PrintAccount;
use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrintAccountPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        if ($user->isAdmin()) {
            return true;
        }
        if (!$user->hasRole(Role::PRINTER)) {
            return false;
        }
    }

    /**
     * True if the user can use his/her print account.
     */
    public function use(User $user)
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return true;
    }

    public function handleAny(User $user)
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return false;
    }

    public function view(User $user, PrintAccount $printAccount): bool
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return $user->id === $printAccount->user_id;
    }

    public function modify(User $user): bool
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return false;
    }
}
