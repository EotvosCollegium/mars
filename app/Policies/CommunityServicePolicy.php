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
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::STUDENT_COUNCIL_LEADERS_AND_COMMITTEE_LEADERS]);
    }

    /**
     * Determine whether the user can approve the given community service
     * (they are the approver, they haven't approved or rejected it before,
     * and the service was in the current semester).
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

    public function generateCertificate(User $user, CommunityService $communityService)
    {
        return 1 == $communityService->approved && $communityService->approver->id === $user->id;
    }
}
