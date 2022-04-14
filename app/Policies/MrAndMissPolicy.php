<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MrAndMissPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can vote.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function vote(User $user)
    {
        if(!($user->isCollegist() && $user->isActive()))
            return Response::deny('Csak a félévben aktív státuszú Collegisták szavazhatnak. Ha te az vagy, írj a rendszergazdáknak!');
        if(config('custom.mr_and_miss_deadline') < now())
            return Response::deny('A szavazás már lejárt!');
        return Response::allow();
    }

    /**
     * Determine whether the user can manage the categories and see the results.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function manage(User $user)
    {
        return $user->hasRoleWithObjectNames(Role::STUDENT_COUNCIL, [Role::COMMUNITY_LEADER]);
    }
}
