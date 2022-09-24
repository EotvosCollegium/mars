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

    public function viewAny(User $user): bool
    {
        return $user->isCollegist();
    }

    public function addPayment(User $user, Checkout $checkout): bool
    {
        if ($checkout->name === Checkout::STUDENTS_COUNCIL) {
            return $user->hasRole([Role::STUDENT_COUNCIL => Role::ECONOMIC_VICE_PRESIDENT]);
        }
        if ($checkout->name === Checkout::ADMIN) {
            return $user->is_admin();
        }

        return false;
    }

    public function addKKTNetreg(User $user): bool
    {
        return $user->hasRole([Role::STUDENT_COUNCIL => Role::ECONOMIC_VICE_PRESIDENT]);
    }

    public function administrate(User $user, Checkout $checkout): bool
    {
        if ($checkout->name === Checkout::STUDENTS_COUNCIL) {
            return $user->hasRole([Role::STUDENT_COUNCIL => Role::ECONOMIC_VICE_PRESIDENT]);
        }
        if ($checkout->name === Checkout::ADMIN) {
            return $user->is_admin();
        }

        return false;
    }

    public function handleAny(User $user): bool
    {
        $checkouts = Checkout::all();
        foreach ($checkouts as $checkout) {
            if ($this->addPayment($user, $checkout)) {
                return true;
            }
        }

        return false;
    }
}
