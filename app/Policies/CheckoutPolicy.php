<?php

namespace App\Policies;

use App\Models\Checkout;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CheckoutPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the checkout.
     */
    public function view(User $user, Checkout $checkout): bool
    {
        return $user->isCollegist();
    }


    /**
     * Determine whether the user can create a transaction in the given checkout.
     */
    public function createTransaction(User $user, Checkout $checkout): bool
    {
        if ($checkout->name === Checkout::STUDENTS_COUNCIL) {
            //everyone can create transactions that is not in checkout
            //the checkout administrator can handle these later
            return $user->isCollegist();
        }
        if ($checkout->name === Checkout::ADMIN) {
            return $user->isAdmin();
        }

        return false;
    }

    /**
     * Determine whether the user can make kkt/netreg transactions.
     */
    public function addKKTNetreg(User $user): bool
    {
        return $user->hasRole([
            Role::SYS_ADMIN,
            Role::STUDENT_COUNCIL => [
                Role::ECONOMIC_VICE_PRESIDENT,
                Role::KKT_HANDLER,
            ],
        ]);
    }

    /**
     * Determine whether the user can calculate the workshop balance.
     */
    public function calculateWorkshopBalance(User $user): bool
    {
        return $user->hasRole([
            Role::SYS_ADMIN,
            Role::STUDENT_COUNCIL => [
                Role::ECONOMIC_VICE_PRESIDENT,
            ]
        ]);
    }

    /**
     * Determine whether the user can administrate the given checkout.
     */
    public function administrate(User $user, Checkout $checkout): bool
    {
        return $checkout->handler?->id == $user->id;
    }
}
