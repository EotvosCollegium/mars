<?php

namespace App\Policies;

use App\Http\Controllers\StudentsCouncil\MrAndMissController;
use App\Models\User;
use App\Models\Role;
use App\Models\MrAndMissOptOut;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * Policy for MrAndMissVote Model.
 */
class MrAndMissVotePolicy
{
    use HandlesAuthorization;

    /*
     * Determine whether the Mr and Miss landing page should be accessible to the user.
     */
    public function access(User $user): Response
    {
        if (app(MrAndMissController::class)->isOptOutPeriod()) {
            return $this->optOut($user);
        }

        return $this->vote($user);
    }

    /**
    * Determine whether the user can opt out.
    */
    public function optOut(User $user): Response
    {
        if (!($user->isCollegist(alumni: false))) {
            return Response::deny('Csak collegisták vehetnek részt.');
        }
        if (!app(MrAndMissController::class)->isOptOutPeriod()) {
            return Response::deny('Jelenleg nem tudsz a Mr. és Missben való részvételről nyilatkozni.');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can vote.
     */
    public function vote(User $user): Response
    {
        if (!($user->isCollegist(alumni: false))) {
            return Response::deny('Csak collegisták szavazhatnak.');
        }
        if (!app(MrAndMissController::class)->isActive()) {
            return Response::deny('A szavazás jelenleg nem elérhető.');
        }
        if (app(MrAndMissController::class)->optedOut($user)) {
            return Response::deny('Lemondtál a szavazásban való részvételről.');
        }
        return Response::allow();
    }

    /**
     * Determine whether the user can manage the categories and see the results.
     */
    public function manage(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole([Role::STUDENT_COUNCIL => Role::COMMUNITY_LEADER]);
    }

    /**
     * Determine whether the user can manage the vote, or participate/opt out.
     */
    public function accessOrManage(User $user): Response|bool
    {
        if (!$this->manage($user)) {
            return $this->access($user);
        }
        return true;
    }
}
