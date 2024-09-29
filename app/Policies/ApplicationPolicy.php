<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\Role;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * bypass for admins
     *
     * @param User $user
     * @return bool|void
     */
    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * @param User $user
     * @param Application $target
     * @return bool
     */
    public function view(User $user, Application $target): bool
    {
        if ($user->id == $target->user_id || $user->can('viewAll', Application::class)) {
            return true;
        } else {
            return $target->appliedWorkshops
                ->intersect($user->applicationCommitteWorkshops)
                ->count() > 0
            || $target->appliedWorkshops
                ->intersect($user->roleWorkshops)
                ->count() > 0;
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewSome(User $user): bool
    {
        return $user->hasRole([
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::WORKSHOP_ADMINISTRATOR,
            Role::WORKSHOP_LEADER,
            Role::APPLICATION_COMMITTEE_MEMBER,
            Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS,
            Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER
        ]);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function editStatus(User $user, ?Workshop $workshop = null): bool
    {
        if ($workshop) {
            if($user->hasRole([
                Role::SECRETARY,
                Role::DIRECTOR,
                Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS
            ])) {
                return true;
            }
            if($user->hasRole(Role::WORKSHOP_LEADER)) {
                return $user->roleWorkshops->contains($workshop);
            }
        }
        return $user->hasRole([
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::WORKSHOP_LEADER,
            Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS
        ]);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAll(User $user): bool
    {
        return $user->hasRole([
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS,
            Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER
        ]);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewUnfinished(User $user): bool
    {
        return $user->hasRole([
            Role::SECRETARY,
            Role::DIRECTOR,
            Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS,
        ]);
    }

    /**
     * Returns true if the user can finalize the application process.
     * @param User $user
     * @return bool
     */
    public function finalize(User $user): bool
    {
        return $user->hasRole([Role::SYS_ADMIN, Role::SECRETARY]);
    }

}
