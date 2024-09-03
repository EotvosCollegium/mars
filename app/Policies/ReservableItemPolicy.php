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
     * Returns whether the user can request a reservation
     * for the given item
     * (but that might need to be approved by someone).
     */
    public function requestReservation(User $user, ReservableItem $item): bool
    {
        if ($item->isOutOfOrder()) {
            return false;
        } elseif ($this->administer($user)) {
            return true;
        } elseif ($item->isWashingMachine()) {
            return $user->hasRole([Role::COLLEGIST, Role::TENANT]);
        } else {
            return config('custom.room_reservation_open')
                && $user->hasRole([Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR, Role::STUDENT_COUNCIL]);
        }
    }

    /**
     * Returns whether the reservation becomes automatically verified.
     */
    public function autoVerify(User $user, ReservableItem $item): bool
    {
        if ($item->isWashingMachine()) {
            return $user->hasRole([Role::COLLEGIST, Role::TENANT]);
        } else {
            // admins not!
            return $user->hasRole([
                Role::SECRETARY,
                Role::STAFF,
                Role::DIRECTOR
            ]);
        }
    }

    /**
     * Returns whether someone is allowed to send a fault report
     * (or a "fixed" report).
     */
    public function reportFault(User $user, ReservableItem $item): bool
    {
        if ($this->administer($user)) {
            return true;
        } else {
            return $user->hasRole([Role::COLLEGIST, Role::TENANT]);
        }
    }
}
