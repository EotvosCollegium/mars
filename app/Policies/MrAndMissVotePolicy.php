<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * Policy for MrAndMissVote Model.
 */
class MrAndMissVotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can vote.
     *
     * @param User $user
     * @return Response
     */
    public function vote(User $user)
    {
        if (!($user->isCollegist())) {
            return Response::deny('Csak Collegisták szavazhatnak');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can manage the categories and see the results.
     *
     * @param User $user
     * @return mixed
     */
    public function manage(User $user): bool
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNITY_LEADER]);
    }
}
