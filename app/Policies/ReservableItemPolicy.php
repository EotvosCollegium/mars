<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\ReservableItem;
use App\Enums\ReservableItemType;
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
     * for a given type of items in general
     * (not counting if it is out of order etc.).
     */
    public function canRequestReservationForType(User $user, ReservableItemType $type): bool
    {
        if ($this->administer($user)) {
            return true;
        } else {
            switch ($type) {
                case ReservableItemType::WASHING_MACHINE:
                    return $user->hasRole([Role::COLLEGIST, Role::TENANT]);
                case ReservableItemType::ROOM:
                    return config('custom.room_reservation_open')
                        && $user->hasRole([Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR, Role::STUDENT_COUNCIL]);
                default:
                    throw new \Exception("unknown ReservableItemType");
            }
        }
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
        } else {
            return self::canRequestReservationForType($user, ReservableItemType::from($item->type));
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
