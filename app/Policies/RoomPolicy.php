<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole([
            Role::DIRECTOR,
            Role::SECRETARY,
            Role::STAFF,
            Role::COLLEGIST,
            Role::SYS_ADMIN
        ]);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateAny(User $user)
    {
        return $user->hasRole([
            Role::SECRETARY,
            Role::STAFF,
            Role::SYS_ADMIN,
            Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS
        ]);
    }
}
