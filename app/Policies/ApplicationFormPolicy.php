<?php

namespace App\Policies;

use App\Models\ApplicationForm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationFormPolicy
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
     * @param ApplicationForm $target
     * @return bool
     */
    public function view(User $user, ApplicationForm $target): bool
    {
        if ($user->id == $target->user_id || $user->can('viewAll', ApplicationForm::class)) {
            return true;
        } else {
            return $target->user->workshops
                ->intersect($user->applicationCommitteWorkshops)
                ->count() > 0
            || $target->user->workshops
                ->intersect($user->roleWorkshops)
                ->count() > 0;
        }
    }

    /**
     * Whether one can edit the note attached to the application.
     *
     * @param User $user
     * @param ApplicationForm $target
     * @return bool
     */
    public function editNote(User $user, ApplicationForm $target): bool
    {
        return $this->view($user, $target)
        && $user->id != $target->user_id;
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
    public function editStatus(User $user): bool
    {
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
