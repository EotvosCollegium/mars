<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use App\Models\Reservations\ReservableItem;
use App\Enums\ReservableItemType;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReservableItemPolicy
{
    use HandlesAuthorization;

    /**
     * Returns whether the user has administrative rights
     * (e.g. can create, modify or delete reservable items).
     */
    public static function administer(User $user): bool
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
     * Note: `canViewType` calls this too.
     */
    public static function canRequestReservationForType(User $user, ReservableItemType $type): bool
    {
        if (self::administer($user)) {
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
     * Returns whether the user can view
     * a given type of reservable items.
     */
    public static function canViewType(User $user, ReservableItemType $type): bool
    {
        if (self::canRequestReservationForType($user, $type)) {
            return true;
        } else {
            switch ($type) {
                case ReservableItemType::WASHING_MACHINE:
                    return $user->hasRole([Role::COLLEGIST, Role::TENANT, Role::RECEPTIONIST]);
                case ReservableItemType::ROOM:
                    return $user->hasRole([Role::COLLEGIST, Role::RECEPTIONIST]);
                default:
                    throw new \Exception("unknown ReservableItemType");
            }
        }
    }

    /**
     * Returns whether the user can view a given item.
     */
    public function view(User $user, ReservableItem $item): bool
    {
        return self::canViewType($user, ReservableItemType::from($item->type));
    }
}
