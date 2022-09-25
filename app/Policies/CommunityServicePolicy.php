<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommunityServicePolicy
{
    use HandlesAuthorization;

    public function view(User $user)
    {
        return $user->isCollegist();
    }

    public function create(User $user)
    {
        return $user->isCollegist();
    }

    public function approveAny(User $user)
    {
        return $user->hasRole([Role::STUDENT_COUNCIL]);
    }
}
