<?php

namespace App\Policies;

use App\Http\Controllers\StudentsCouncil\MrAndMissController;
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
        if (!($user->isCollegist(alumni: false))) {
            return Response::deny('Csak collegisták szavazhatnak.');
        }
        if(!app(MrAndMissController::class)->isActive()) {
            return Response::deny('A szavazás jelenleg nem elérhető.');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can manage the categories and see the results.
     *
     * @param User $user
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNITY_LEADER]);
    }


    /**
     * Determine whether the user can vote or manage the categories and see the results.
     *
     * @param User $user
     * @return bool|Response
     */
    public function voteOrManage(User $user)
    {
        if(!$this->manage($user)) {
            return $this->vote($user);
        }
        return true;
    }
}
