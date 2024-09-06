<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Reservations\Reservation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

class ReservationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user has administrative rights
     * (e.g. can approve reservations or delete any of them).
     */
    public function administer(User $user): bool
    {
        return $user->isAdmin()
            || $user->hasRole([
                Role::SECRETARY,
                Role::STAFF,
                Role::DIRECTOR
            ]);
    }

    /**
     * Determine whether the user can view a given reservation
     * (either the block in the timetable or the details).
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $this->administer($user)
            || $user->id == $reservation->user->id
            || ($reservation->verified && $user->can('view', $reservation->reservableItem));
    }

    /**
     * Determine if a given reservation can be
     * modified or deleted by the user.
     */
    public function modify(User $user, Reservation $reservation): bool
    {
        if (!$this->view($user, $reservation)) {
            return false;
        }
        // no one should be able to modify reservations
        // that are in the past
        // or have already begun
        elseif (Carbon::make($reservation->reserved_from) < Carbon::now()) {
            return false;
        } else {
            return $this->administer($user)
            || (config('custom.room_reservation_open') && $reservation->user->id == $user->id);
        }
    }
}
