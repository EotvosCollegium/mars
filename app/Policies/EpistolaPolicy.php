<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpistolaPolicy
{
    use HandlesAuthorization;

    public function view(User $user): bool
    {
        return $user->isCollegist();
    }

    public function create(User $user): bool
    {
        return $user->isCollegist();
    }

    public function edit(User $user): bool
    {
        return $user->hasRole(Role::STUDENT_COUNCIL, Role::COMMUNICATION_LEADER)
            || $user->hasRole(Role::STUDENT_COUNCIL, Role::COMMUNICATION_MEMBER);
    }

    public function send(User $user): bool
    {
        return $user->hasRole(Role::STUDENT_COUNCIL, Role::COMMUNICATION_LEADER);
    }
}
