<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\CommunityService;

use Illuminate\Auth\Access\HandlesAuthorization;

class CommunityServicePolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can view any community services.
     * @param User $user
     * @return bool
     */
    public function view(User $user)
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can create new community service.
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can be an approver for any community service.
     * @param User $user
     * @return bool
     */
    public function approveAny(User $user)
    {
        return $user->hasRole([Role::STUDENT_COUNCIL]);
    }

    /**
     * Determine whether the user is the approver for the given community service.
     * @param User $user
     * @param CommunityService $communityService
     * @return bool
     */
    public function approve(User $user, CommunityService $communityService)
    {
        if ($communityService->approved !== null || !$communityService->semester->isCurrent()) {
            return false;
        }

        return $communityService->approver->id === $user->id;
    }
}
