<?php

namespace App\Policies;

use App\Models\FreePages;
use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use Illuminate\Auth\Access\HandlesAuthorization;

class FreePagesPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if(! Feature::isFeatureEnabled("printing")) return false;

        if ($user->isAdmin()) {
            return true;
        }

        if (!$user->hasRole(Role::PRINTER)) {
            return false;
        }
    }

    public function create(User $user)
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return false;
    }

    public function view(User $user, FreePages $freePages): bool
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return $freePages->user_id == $user->id;
    }

    public function viewSelf(User $user): bool
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return true;
    }

    public function viewAny(User $user)
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return false;
    }

    public function update(User $user, FreePages $freePages): bool
    {
        if(! Feature::isFeatureEnabled("printing")) return false;
        return $freePages->user_id == $user->id;
    }
}
