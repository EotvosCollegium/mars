<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Reservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    public function administer(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasRole([
                Role::SECRETARY,
                Role::STAFF,
                Role::DIRECTOR
            ]);
    }

    public function view(User $user, Reservation $reservation): bool
    {
        return $this->administer($user)
            || $user->isCollegist()
            || $user->hasRole(Role::WORKSHOP_LEADER)
            || $reservation->reservableItem->type == 'washing_machine';
    }

    public function modify(User $user, Reservation $reservation): bool
    {
        return $this->administer($user)
            || (isset($reservation->user) && $reservation->user->id == $user->id);
    }
}
