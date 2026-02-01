<?php

namespace App\Policies;

use App\Models\SemesterSetting;
use App\Models\User;

class SemesterSettingPolicy
{
    /**
     * Determine whether the user can view any semester settings.
     */
    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the semester setting.
     */
    public function view(User $user, SemesterSetting $semesterSetting)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the semester setting.
     */
    public function update(User $user, SemesterSetting $semesterSetting)
    {
        return $user->isAdmin();
    }
}