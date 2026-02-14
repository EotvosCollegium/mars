<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\ConfigurableValue;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConfigurableValuePolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function editAny(User $user)
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS]);
    }

    public function edit(User $user, ConfigurableValue $configurableValue)
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS]);
    }
}
