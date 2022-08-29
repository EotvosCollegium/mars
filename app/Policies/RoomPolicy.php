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
        return $user->hasAnyRoleBase([
            Role::DIRECTOR,
            Role::SECRETARY,
            Role::STAFF,
            Role::COLLEGIST
        ]);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateAny(User $user)
    {
        return $user->hasAnyRoleBase([
            Role::SECRETARY,
            Role::STAFF,
        ]) || $user->isPresident();
    }
}
