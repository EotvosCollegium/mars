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
    public function viewAny(User $user): bool {
        return true; // all authenticated users
    }

    /**
     * Returns whether the user has administrative rights
     * (e.g. can create, modify or delete reservable items).
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
     * Returns whether the user can create a reservation
     * for the given item
     * that is automatically accepted
     * without manual approval.
     */
    public function reserveImmediately(User $user, ReservableItem $item): bool {
        return $this->administer($user)
            || $item->type == 'washing_machine'; // this can be reserved by anyone
    }

    /**
     * Returns whether the user can request a reservation
     * for the given item
     * that needs to be approved by someone
     * with administrative rights.
     * True if reserveImmediately is also true.
     */
    public function requestReservation(User $user, ReservableItem $item): bool {
        return $this->reserveImmediately($user, $item)
            || $user->isCollegist()
            || $user->hasRole(Role::WORKSHOP_LEADER);
    }
}
