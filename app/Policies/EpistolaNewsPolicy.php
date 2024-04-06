<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpistolaNewsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any epistola news.
     * @param User $user
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can create an epistola news.
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can edit an epistola news.
     * @param User $user
     * @return bool
     */
    public function edit(User $user): bool
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNICATION_LEADER])
            || $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNICATION_MEMBER]);
    }

    /**
     * Determine whether the user can send the epistola.
     * @param User $user
     * @return bool
     */
    public function send(User $user): bool
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNICATION_LEADER])
            || $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNICATION_MEMBER]);
    }
}
