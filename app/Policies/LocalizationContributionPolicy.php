<?php

namespace App\Policies;

use App\Models\LocalizationContribution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocalizationContributionPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::LOCALE_ADMIN);
    }

    /**
     * @param User $user
     * @param LocalizationContribution $contribution
     * @return bool
     */
    public function approve(User $user, LocalizationContribution $contribution): bool
    {
        return $user->hasRole([Role::LOCALE_ADMIN => $contribution->language]);
    }
}
