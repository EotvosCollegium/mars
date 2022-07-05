<?php

namespace App\Policies;

use App\Models\LocalizationContribution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocalePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRoleBase(Role::LOCALE_ADMIN);
    }

    public function approve(User $user, LocalizationContribution $contribution): bool
    {
        return $user->hasRole(Role::LOCALE_ADMIN, $contribution->language);
    }
}
