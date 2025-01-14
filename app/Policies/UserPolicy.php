<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\RoleObject;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Cache;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * We let admins do anything here.
     */
    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }


    /**
     * @param User $user
     * @return bool
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole([
            Role::STAFF,
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::STUDENT_COUNCIL_SECRETARY,
            Role::STUDENT_COUNCIL => array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS),
        ]);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewSome(User $user): bool
    {
        return $this->viewAll($user)
            || $user->hasRole([
                Role::WORKSHOP_ADMINISTRATOR,
                Role::WORKSHOP_LEADER,
            ]);
    }

    /**
     * @param User $user
     * @return bool
     *
     * @deprecated use viewAll or viewSome instead
     */
    public function viewAny(User $user): bool
    {
        return
            $user->hasRole([
                Role::STAFF,
                Role::SECRETARY,
                Role::DIRECTOR,
                Role::WORKSHOP_ADMINISTRATOR,
                Role::WORKSHOP_LEADER,
                Role::STUDENT_COUNCIL_SECRETARY,
                Role::STUDENT_COUNCIL => array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS),
            ]);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewSemesterEvaluation(User $user): bool
    {
        return $user->hasRole([
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::WORKSHOP_LEADER,
            Role::STUDENT_COUNCIL_SECRETARY,
            Role::STUDENT_COUNCIL => [Role::PRESIDENT, Role::SCIENCE_VICE_PRESIDENT]
        ]);
    }

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function view(User $user, User $target): bool
    {
        if ($user->id == $target->id) {
            return true;
        }
        if ($target->isCollegist()) {
            return (Cache::remember($user->id . '_is_secretary/director/s_council', 60, function () use ($user) {
                return $user->hasRole([
                    Role::SECRETARY,
                    Role::DIRECTOR,
                    Role::STUDENT_COUNCIL => array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS),
                    Role::STUDENT_COUNCIL_SECRETARY,
                ]);
            })) || $target->workshops
                    ->intersect($user->roleWorkshops)
                    ->count() > 0;
        } elseif ($target->hasRole(Role::TENANT)) {
            return $user->hasRole([Role::STAFF, Role::STUDENT_COUNCIL => Role::PRESIDENT]);
        }
        return false;
    }


    /** Permission related policies */

    /**
     * @param User $user
     * @param User $target
     * @param Role|null $role
     * @return bool
     */
    public function updateAnyPermission(User $user, User $target, Role $role = null): bool
    {
        if (!isset($role)) {
            return $user->hasRole([
                Role::SECRETARY,
                Role::STUDENT_COUNCIL => array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS),
                Role::STUDENT_COUNCIL_SECRETARY,
                Role::WORKSHOP_ADMINISTRATOR,
                Role::WORKSHOP_LEADER
            ]);
        }

        if ($role->name == Role::TENANT) {
            return $user->hasRole([Role::STAFF]);
        }

        if ($role->name == Role::COLLEGIST || $role->name == Role::ALUMNI || $role->name == Role::SENIOR) {
            return $user->hasRole([Role::SECRETARY, Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS]);
        }

        if ($role->name == Role::WORKSHOP_LEADER) {
            return $user->hasRole([Role::SECRETARY, Role::DIRECTOR]);
        }

        if ($role->name == Role::WORKSHOP_ADMINISTRATOR) {
            return $user->hasRole([
                Role::WORKSHOP_LEADER,
                Role::STUDENT_COUNCIL_SECRETARY,
                Role::SECRETARY,
                Role::STUDENT_COUNCIL => Role::SCIENCE_VICE_PRESIDENT
            ]);
        }

        if ($role->name == Role::STUDENT_COUNCIL_SECRETARY) {
            return $user->hasRole(Role::STUDENT_COUNCIL_SECRETARY);
        }

        if ($role->name == Role::BOARD_OF_TRUSTEES_MEMBER) {
            return $user->hasRole(Role::STUDENT_COUNCIL_SECRETARY);
        }

        if ($role->name == Role::ETHICS_COMMISSIONER) {
            return $user->hasRole(Role::STUDENT_COUNCIL_SECRETARY);
        }

        if ($role->name == Role::APPLICATION_COMMITTEE_MEMBER) {
            return $user->hasRole([
                Role::WORKSHOP_LEADER,
                Role::WORKSHOP_ADMINISTRATOR,
                Role::STUDENT_COUNCIL => [Role::PRESIDENT, Role::SCIENCE_VICE_PRESIDENT]
            ]);
        }

        if ($role->name == Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER) {
            return $user->hasRole([Role::STUDENT_COUNCIL_SECRETARY, Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS]);
        }

        if ($role->name == Role::STUDENT_COUNCIL) {
            return $user->hasRole([
                Role::STUDENT_COUNCIL => array_merge(Role::STUDENT_COUNCIL_LEADERS, Role::COMMITTEE_LEADERS),
                Role::STUDENT_COUNCIL_SECRETARY
            ]);
        }

        return false;
    }

    /**
     * @param User $user
     * @param User $target
     * @param Role $role
     * @param RoleObject|Workshop|null $object
     * @return bool
     */
    public function updatePermission(User $user, User $target, Role $role, Workshop|RoleObject $object = null): bool
    {
        if ($role->name == Role::TENANT) {
            return $user->hasRole([Role::STAFF]);
        }

        if ($role->name == Role::COLLEGIST || $role->name == Role::ALUMNI || $role->name == Role::SENIOR) {
            return $user->hasRole([Role::SECRETARY, Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS]);
        }

        if ($role->name == Role::APPLICATION_COMMITTEE_MEMBER) {
            return $user->roleWorkshops->contains($object->id)
                    || $user->hasRole([
                        Role::STUDENT_COUNCIL => [Role::PRESIDENT, Role::SCIENCE_VICE_PRESIDENT]
                    ]);
        }

        if ($role->name == Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER) {
            return $user->hasRole([Role::STUDENT_COUNCIL_SECRETARY, Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS]);
        }

        if ($role->name == Role::WORKSHOP_LEADER) {
            return $user->hasRole([Role::SECRETARY, Role::DIRECTOR]);
        }

        if ($role->name == Role::WORKSHOP_ADMINISTRATOR) {
            return ($user->hasRole(Role::WORKSHOP_LEADER)
                    && $user->roleWorkshops->contains($object->id)
            ) || $user->hasRole([
                Role::STUDENT_COUNCIL_SECRETARY,
                Role::SECRETARY,
                Role::STUDENT_COUNCIL => Role::SCIENCE_VICE_PRESIDENT
            ]);
        }

        if ($role->name == Role::STUDENT_COUNCIL_SECRETARY) {
            return $user->hasRole(Role::STUDENT_COUNCIL_SECRETARY);
        }

        if ($role->name == Role::BOARD_OF_TRUSTEES_MEMBER) {
            return $user->hasRole(Role::STUDENT_COUNCIL_SECRETARY);
        }

        if ($role->name == Role::ETHICS_COMMISSIONER) {
            return $user->hasRole(Role::STUDENT_COUNCIL_SECRETARY);
        }

        if ($role->name == Role::STUDENT_COUNCIL) {
            if ($user->hasRole(Role::STUDENT_COUNCIL_SECRETARY)) {
                return true;
            }
            if ($object?->name == Role::PRESIDENT) {
                return false;
            }
            if ($user->hasRole([Role::STUDENT_COUNCIL => Role::PRESIDENT])) {
                return true;
            }
            if ($object?->name == Role::KKT_HANDLER) {
                return $user->hasRole([Role::STUDENT_COUNCIL => Role::ECONOMIC_VICE_PRESIDENT]);
            }
            if (in_array($object?->name, Role::COMMITTEE_MEMBERS) || in_array($object?->name, Role::COMMITTEE_REFERENTS)) {
                $committee = preg_split("~-~", $object->name)[0];
                return $user->hasRole([Role::STUDENT_COUNCIL => $committee . "-leader"]);
            }
        }
        return false;
    }

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function updateStatus(User $user, User $target): bool
    {
        if (!$target->isCollegist()) {
            return false;
        }
        if ($user->hasRole(Role::SECRETARY) || $user->hasRole([Role::STUDENT_COUNCIL => Role::SCIENCE_VICE_PRESIDENT])) {
            return true;
        }
        return $user->roleWorkshops->intersect($target->workshops)->count() > 0;
    }

    /**
     * @param User $user
     * @param User $target
     * @param Workshop $workshop
     * @return bool
     */
    public function updateWorkshop(User $user, User $target, Workshop $workshop): bool
    {
        if ($user->hasRole(Role::SECRETARY)) {
            return true;
        }
        return $user->roleWorkshops->has($workshop->id);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function handleGuests(User $user): bool
    {
        return $user->hasRole(Role::STAFF);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function invite(User $user): bool
    {
        return $user->hasRole(Role::SECRETARY);
    }
}
