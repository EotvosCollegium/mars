<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\RoleObject;
use App\Models\User;
use App\Models\Workshop;
use http\Exception\InvalidArgumentException;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if($user->hasRole(Role::SYS_ADMIN)) return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return
            $user->hasAnyRoleBase([
                Role::STAFF, Role::SECRETARY, Role::DIRECTOR, Role::WORKSHOP_ADMINISTRATOR, Role::WORKSHOP_LEADER
            ])
            || $user->isPresident();
    }

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function view(User $user, User $target): bool
    {
        if($user->id == $target->id) return true;
        if($target->isCollegist())
        {
            if($user->hasAnyRoleBase([Role::SECRETARY, Role::DIRECTOR]))
                return true;
            if($user->isPresident())
                return true;
            return $target->workshops
                    ->intersect($user->roleWorkshops())
                    ->count()>0;
        } else if($target->hasRole(Role::TENANT)){
            return $user->hasRole(Role::STAFF);
        }
        return false;
    }

    /** Application related policies */

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function viewApplication(User $user, User $target): bool
    {
        return $target->workshops
                ->intersect($user->applicationWorkshops())
                ->count()>0;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAnyApplication(User $user): bool
    {
        return $user->hasAnyRoleBase([
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::WORKSHOP_ADMINISTRATOR,
            Role::WORKSHOP_LEADER,
            Role::APPLICATION_COMMITTEE_MEMBER
        ]);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAllApplications(User $user): bool
    {
        return $user->hasAnyRoleBase([
                Role::SECRETARY,
                Role::DIRECTOR,
            ]) || $user->isPresident();
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewUnfinishedApplications(User $user): bool
    {
        return $this->viewAllApplications($user);
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
        if(!isset($role))
            return $user->hasRole(Role::SECRETARY)
            || $user->isInStudentsCouncil()
            || $user->hasAnyRoleBase([Role::WORKSHOP_ADMINISTRATOR, Role::WORKSHOP_LEADER]);

        if($role->name == Role::COLLEGIST)
            return $user->hasRole(Role::SECRETARY);

        if($role->name == Role::APPLICATION_COMMITTEE_MEMBER)
            return $user->hasAnyRoleBase([Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR]);

        if($role->name == Role::STUDENT_COUNCIL)
        {
            return $user->isInStudentsCouncil();
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
        if($role->name == Role::COLLEGIST)
            return $user->hasRole(Role::SECRETARY);

        if($role->name == Role::APPLICATION_COMMITTEE_MEMBER)
            return $user->roleWorkshops()->has($object->id);

        if($role->name == Role::STUDENT_COUNCIL)
        {
            if($object->name == Role::PRESIDENT)
            {
                return false;
            }
            if($user->hasRole(Role::STUDENT_COUNCIL, Role::PRESIDENT))
            {
                return true;
            }
            if(in_array($object->name, Role::COMMITTEE_MEMBERS)){
                $committee = preg_split("~-~", $object->name)[0];
                return $user->hasRole(Role::STUDENT_COUNCIL, $committee . "-leader");
            }
        }
        return false;
    }

    /**
     * @param User $user
     * @param User $target
     * @param string $roleName
     * @param string|null $roleObjectName
     * @return bool
     */
    public function updateStatus(User $user, User $target): bool
    {

        if($user->hasRole(Role::SECRETARY)) return true;
        return $user->roleWorkshops()->intersect($target->workshops)->count() > 0;
    }

    /**
     * @param User $user
     * @param User $target
     * @param Workshop $workshop
     * @return bool
     */
    public function updateWorkshop(User $user, User $target, Workshop $workshop): bool
    {
        if($user->hasRole(Role::SECRETARY)) return true;
        return $user->roleWorkshops()->has($workshop->id);
    }

}
