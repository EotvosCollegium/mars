<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([Role::NETWORK_ADMIN, Role::SECRETARY, Role::PERMISSION_HANDLER])
            || $user->hasRoleBase(Role::WORKSHOP_LEADER);
    }

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function view(User $user, User $target): bool
    {
        return $user->id == $target->id
            || $user->hasAnyRole([Role::NETWORK_ADMIN, Role::SECRETARY, Role::PERMISSION_HANDLER])
            || $user->roles()
                ->whereIn('name', [Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
                ->get(['object_id'])->pluck('object_id')
                ->intersect($target->workshops()->pluck('id'))->count() > 0;
    }

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function viewPersonalInformation(User $user, User $target): bool
    {
        // TODO: later internet admins should be removed
        return $user->hasRole(Role::NETWORK_ADMIN)
            || ($target->hasRole(Role::COLLEGIST) && $user->hasRole(Role::SECRETARY))
            || $user->id == $target->id
            || ($target->hasRole(Role::TENANT) && $user->hasRole(Role::STAFF))
            || $user->roles()
                ->whereIn('name', [Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
                ->get(['object_id'])->pluck('object_id')
                ->intersect($target->workshops()->pluck('id'))->count() > 0;
        ;
    }

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function viewEducationalInformation(User $user, User $target): bool
    {
        // TODO: later internet admins should be removed
        return $user->hasAnyRole([Role::NETWORK_ADMIN, Role::SECRETARY])
            || $user->id == $target->id
            || $user->roles()
                ->whereIn('name', [Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
                ->get(['object_id'])->pluck('object_id')
                ->intersect($target->workshops()->pluck('id'))->count() > 0;
        ;
    }

    /** Application related policies */

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function viewApplication(User $user, User $target): bool
    {
        return (isset($target->application))
            && ($user->hasAnyRole([Role::NETWORK_ADMIN, Role::SECRETARY, Role::DIRECTOR])
            || $user->id == $target->id
            || $user->roles()
                    ->whereIn('name', [Role::APPLICATION_COMMITTEE_MEMBER, Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR])
                    ->get(['object_id'])->pluck('object_id')
                    ->intersect($target->workshops()->pluck('id'))->count() > 0);
        //has common workshop
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAnyApplication(User $user): bool
    {
        return $user->hasAnyRole([
            Role::NETWORK_ADMIN,
            Role::SECRETARY,
            Role::DIRECTOR])
            || $user->hasRoleBase(Role::WORKSHOP_ADMINISTRATOR)
            || $user->hasRoleBase(Role::WORKSHOP_LEADER)
            || $user->hasRoleBase(Role::APPLICATION_COMMITTEE_MEMBER);
    }

    /** Permission related policies */

    /**
     * @param User $user
     * @param User $target
     * @return bool
     */
    public function viewPermissionFor(User $user, User $target): bool
    {
        return $user->hasRole(Role::PERMISSION_HANDLER) && $user->id !== $target->id;
    }

    /**
     * @param User $user
     * @param User $target
     * @param int $role_id
     * @return bool
     */
    public function updatePermission(User $user, User $target, int $role_id): bool
    {
        $role = Role::find($role_id);

        return $user->hasRole(Role::PERMISSION_HANDLER) && $user->id !== $target->id;
    }

    /**
     * @param User $user
     * @param User $target
     * @param int $role_id
     * @return bool
     */
    public function deletePermission(User $user, User $target, int $role_id): bool
    {
        $role = Role::find($role_id);

        return $user->hasRole(Role::PERMISSION_HANDLER) && $user->id !== $target->id;
    }
}
