<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Reservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user has administrative rights
     * (e.g. can approve reservations or delete any of them).
     */
    public function administer(User $user): bool {
        return $user->hasRole([
            Role::SYS_ADMIN,
            Role::STAFF,
            Role::SECRETARY,
            Role::DIRECTOR
        ]);
    }

    /**
     * Determine whether the user can view the details
     * of a given reservation.
     */
    public function view(User $user, Reservation $reservation): bool {
        return $this->modify($user, $reservation)
            || $user->isCollegist()
            || $user->hasRole(Role::WORKSHOP_LEADER);
    }

    /**
     * Determine if a given reservation can be
     * modified or deleted by the user.
     */
    public function modify(User $user, Reservation $reservation): bool {
        return $this->administer($user)
            || ($user->id == $reservation->user_id);
    }
}
