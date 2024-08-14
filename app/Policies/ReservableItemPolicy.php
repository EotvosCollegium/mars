<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\ReservableItem;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservableItemPolicy
{
    use HandlesAuthorization;

    /**
     * Returns whether the user can view any reservable items.
     */
    public function viewAny(User $user): bool
    {
        return true; // anyone logged in
    }

    /**
     * Returns whether the user has administrative rights
     * (e.g. can create, modify or delete reservable items).
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
     * Returns whether someone can reserve the item
     * without verification.
     */
    public function reserveImmediately(User $user, ReservableItem $item): bool
    {
        return $this->administer($user)
            || $item->isWashingMachine();
    }

    /**
     * Returns whether the user can request a reservation
     * for the given item
     * (but that might need to be approved by someone).
     */
    public function requestReservation(User $user, ReservableItem $item): bool
    {
        return $this->administer($user)
            || $item->isWashingMachine()
            || $user->isCollegist()
            || $user->hasRole([Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR]);
    }
}
