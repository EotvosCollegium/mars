<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

/**
 * Contains authorization policies
 * related to anonymous questions.
 */
class AnswerSheetPolicy
{
    public function administer(User $user): bool
    {
        return $user->isAdmin()
          || $user->hasRole([Role::STUDENT_COUNCIL => Role::PRESIDENT])
          || $user->hasRole([Role::STUDENT_COUNCIL => Role::SCIENCE_VICE_PRESIDENT]);
    }
}
