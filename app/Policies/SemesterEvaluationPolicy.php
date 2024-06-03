<?php

namespace App\Policies;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SemesterEvaluationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can fill the semester evaluation form.
     *
     * @param User $user
     * @return Response|bool
     */
    public function fill(User $user): Response|bool
    {
        if(!$user->isCollegist(alumni: false)) {
            return false;
        }
        if(!app(SemesterEvaluationController::class)->isActive()) {
            return Response::deny('Lejárt a határidő a kérdőív kitöltésére.');
        }
        return true;
    }

    /**
     * Determine whether the user can manage the semester evaluations and see the results.
     *
     * @param User $user
     * @return Response|bool
     */
    public function manage(User $user): Response|bool
    {
        return $user->hasRole([
            Role::SYS_ADMIN,
            Role::DIRECTOR,
            Role::SECRETARY,
            Role::STUDENT_COUNCIL => Role::PRESIDENT,
            Role::STUDENT_COUNCIL_SECRETARY
        ]);
    }

    /**
     * Determine whether the user can fill the semester evaluation form.
     *
     * @param User $user
     * @return Response|bool
     */
    public function fillOrManage(User $user): Response|bool
    {
        //Beware: `fill` may return a Response object, the order is important.
        return $this->manage($user) || $this->fill($user);
    }
}
