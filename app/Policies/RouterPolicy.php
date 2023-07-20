<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Internet\Router;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RouterPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any routers.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can view the router.
     *
     * @param User $user
     * @param Router $router
     * @return mixed
     */
    public function view(User $user, Router $router)
    {
        return $user->isCollegist();
    }

    /**
     * Determine whether the user can create routers.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the router.
     *
     * @param User $user
     * @param Router $router
     * @return mixed
     */
    public function update(User $user, Router $router)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the router.
     *
     * @param User $user
     * @param Router $router
     * @return mixed
     */
    public function delete(User $user, Router $router)
    {
        return $user->isAdmin();
    }
}
