<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\ReservableItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservableItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true; // anyone logged in
    }

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
     * Returns whether someone can reserve the item
     * without verification.
     */
    public function reserveImmediately(User $user, ReservableItem $item): bool
    {
        return $this->administer($user)
            || $item->isWashingMachine();
    }

    /**
     * Returns whether someone request a reservation.
     * True if reserveImmediately is also true.
     */
    public function requestReservation(User $user, ReservableItem $item): bool
    {
        return $this->reserveImmediately($user, $item)
            || $user->isCollegist()
            || $user->hasRole(Role::WORKSHOP_LEADER);
    }
}
