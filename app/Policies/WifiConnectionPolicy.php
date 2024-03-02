<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Feature;
use App\Models\Internet\WifiConnection;
use Illuminate\Auth\Access\HandlesAuthorization;

class WifiConnectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        if(! Feature::isFeatureEnabled("internet.wireless.connections")) return false;
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param WifiConnection $wifiConnection
     * @return mixed
     */
    public function view(User $user, WifiConnection $wifiConnection)
    {
        if(! Feature::isFeatureEnabled("internet.wireless.connections")) return false;
        return $user->isAdmin()
            || $user->wifiConnections->contains($wifiConnection);
    }

    public function approveAny(User $user): bool
    {
        if(! Feature::isFeatureEnabled("internet.wireless.connections")) return false;
        return $user->isAdmin();
    }
}
